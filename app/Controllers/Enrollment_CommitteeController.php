<?php
require_once ROOT_PATH . '/app/Models/ClearanceStatus.php';

class Enrollment_CommitteeController extends Controller
{
    private ClearanceStatus $statusModel;

    public function __construct()
    {
        $this->statusModel = new ClearanceStatus();
    }

    // ----------------------------------------------------------------
    // Dashboard
    // ----------------------------------------------------------------

    public function dashboard(): void
    {
        $this->requireLogin('enrollment_committee');
        $rows       = $this->statusModel->getClearancesForEnrollmentCommittee($_SESSION['user_id']);
        $clearances = $this->groupClearances($rows);

        foreach ($clearances as &$c) {
            $flagged = $cleared = 0;
            foreach ($c['students'] as $s) {
                $status = $this->resolveDisplayStatus($s);
                if ($status === 'flagged') $flagged++;
                elseif ($status === 'cleared') $cleared++;
            }
            $c['flagged_total'] = $flagged;
            $c['cleared_total'] = $cleared;
            $c['pending_total'] = count($c['students']) - $flagged - $cleared;
        }
        unset($c);

        $data = [
            'clearances' => $clearances,
            'flash'      => $this->getFlash(),
            'userName'   => $_SESSION['user_name'],
        ];
        $this->view('layouts/main', array_merge($data, ['content' => 'enrollment_committee/dashboard']));
    }

    // ----------------------------------------------------------------
    // My Clearances — two-phase view
    //   Phase 1 (no ?cid): show clearance selection cards
    //   Phase 2 (?cid=N) : show student detail table
    // ----------------------------------------------------------------

    public function clearances(): void
    {
        $this->requireLogin('enrollment_committee');
        $selectedCid = (int) $this->getGet('cid', 0);

        $rows            = $this->statusModel->getClearancesForEnrollmentCommittee($_SESSION['user_id']);
        $allClearances   = $this->groupClearances($rows);

        // Phase 2: a specific clearance is selected
        if ($selectedCid > 0) {
            $validCids = array_column($allClearances, 'clearance_id');
            if (!in_array($selectedCid, $validCids)) {
                $this->setFlash('error', 'You are not assigned to that clearance.');
                $this->redirect('enrollment-committee/clearances');
                return;
            }

            // Filters
            $search       = trim($this->getGet('search', ''));
            $filterStatus = $this->getGet('status', 'all');
            $filterCollege= $this->getGet('college', '');
            $filterCourse = $this->getGet('course', '');
            $filterYear   = $this->getGet('year', '');

            // Find the selected clearance group
            $selectedClearance = null;
            foreach ($allClearances as $c) {
                if ((int)$c['clearance_id'] === $selectedCid) {
                    $selectedClearance = $c;
                    break;
                }
            }

            // Attach signatory detail + resolve display status
            $colleges   = [];
            $courses    = [];
            $yearLevels = [];
            foreach ($selectedClearance['students'] as &$s) {
                $s['display_status']   = $this->resolveDisplayStatus($s);
                $s['signatory_detail'] = $this->statusModel->getSignatoryDetailForStudent(
                    $selectedCid,
                    $s['id']
                );
                $colleges[]   = $s['college'];
                $courses[]    = $s['course'];
                $yearLevels[] = (string) $s['year_level'];
            }
            unset($s);

            // Filter students
            $selectedClearance['students'] = array_values(array_filter(
                $selectedClearance['students'],
                function ($s) use ($search, $filterStatus, $filterCollege, $filterCourse, $filterYear) {
                    if ($search !== '') {
                        $haystack = strtolower($s['last_name'] . ' ' . $s['first_name'] . ' ' . $s['student_number']);
                        if (strpos($haystack, strtolower($search)) === false) return false;
                    }
                    if ($filterStatus !== 'all' && $s['display_status'] !== $filterStatus) return false;
                    if ($filterCollege !== '' && $s['college'] !== $filterCollege) return false;
                    if ($filterCourse !== '' && $s['course'] !== $filterCourse) return false;
                    if ($filterYear !== '' && (string)$s['year_level'] !== $filterYear) return false;
                    return true;
                }
            ));

            $colleges   = array_unique($colleges);
            $courses    = array_unique($courses);
            $yearLevels = array_unique($yearLevels);
            sort($colleges);
            sort($courses);
            sort($yearLevels);

            $data = [
                'phase'             => 'detail',
                'selectedCid'       => $selectedCid,
                'selectedClearance' => $selectedClearance,
                'flash'             => $this->getFlash(),
                'userName'          => $_SESSION['user_name'],
                'search'            => $search,
                'filterStatus'      => $filterStatus,
                'filterCollege'     => $filterCollege,
                'filterCourse'      => $filterCourse,
                'filterYear'        => $filterYear,
                'colleges'          => $colleges,
                'courses'           => $courses,
                'yearLevels'        => $yearLevels,
            ];
            $this->view('layouts/main', array_merge($data, ['content' => 'enrollment_committee/clearances']));
            return;
        }

        // Phase 1: clearance selection cards
        // Compute summary stats for each clearance group
        foreach ($allClearances as &$c) {
            $flagged = $cleared = 0;
            foreach ($c['students'] as $s) {
                $status = $this->resolveDisplayStatus($s);
                if ($status === 'flagged') $flagged++;
                elseif ($status === 'cleared') $cleared++;
            }
            $c['flagged_total'] = $flagged;
            $c['cleared_total'] = $cleared;
            $c['pending_total'] = count($c['students']) - $flagged - $cleared;
        }
        unset($c);

        $data = [
            'phase'      => 'select',
            'clearances' => $allClearances,
            'flash'      => $this->getFlash(),
            'userName'   => $_SESSION['user_name'],
        ];
        $this->view('layouts/main', array_merge($data, ['content' => 'enrollment_committee/clearances']));
    }


    // ----------------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------------

    /**
     * Group flat rows into clearance => students[] structure.
     */
    private function groupClearances(array $rows): array
    {
        $clearances = [];
        foreach ($rows as $r) {
            $cid = $r['clearance_id'];
            if (!isset($clearances[$cid])) {
                $clearances[$cid] = [
                    'clearance_id'   => $cid,
                    'clearance_name' => $r['clearance_name'],
                    'school_year'    => $r['school_year'],
                    'students'       => [],
                ];
            }
            $clearances[$cid]['students'][] = $r;
        }
        return array_values($clearances);
    }

    /**
     * Resolve a student's display status for enrollment committee view.
     *   flagged → has at least one flagged signatory row
     *   cleared → all signatories cleared (no flags, no pending)
     *   pending → mix of cleared and pending, nothing flagged
     */
    private function resolveDisplayStatus(array $s): string
    {
        if ((int) $s['flagged_count'] > 0) return 'flagged';
        $total   = (int) $s['total_signatories'];
        $cleared = (int) $s['cleared_count'];
        if ($total > 0 && $cleared === $total) return 'cleared';
        return 'pending';
    }
}
