<?php
require_once ROOT_PATH . '/app/Models/ClearanceStatus.php';

class AdviserController extends Controller
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
        $this->requireLogin('adviser');
        $rows       = $this->statusModel->getClearancesForAdviser($_SESSION['user_id']);
        $clearances = $this->groupClearances($rows);

        $data = [
            'clearances' => $clearances,
            'flash'      => $this->getFlash(),
            'userName'   => $_SESSION['user_name'],
        ];
        $this->view('layouts/main', array_merge($data, ['content' => 'adviser/dashboard']));
    }

    // ----------------------------------------------------------------
    // Clearance view (main student list)
    // ----------------------------------------------------------------

    public function clearances(): void
    {
        $this->requireLogin('adviser');

        // Filters
        $search       = trim($this->getGet('search', ''));
        $filterStatus = $this->getGet('status', 'all'); // all|flagged|cleared|pending
        $filterCourse = $this->getGet('course', '');
        $filterYear   = $this->getGet('year', '');

        $rows       = $this->statusModel->getClearancesForAdviser($_SESSION['user_id']);
        $clearances = $this->groupClearances($rows);

        // Apply filters and attach signatory detail
        $courses    = [];
        $yearLevels = [];

        foreach ($clearances as &$c) {
            foreach ($c['students'] as &$s) {
                // Determine effective display status
                $s['display_status'] = $this->resolveDisplayStatus($s);
                // Attach per-signatory breakdown
                $s['signatory_detail'] = $this->statusModel->getSignatoryDetailForStudent(
                    $c['clearance_id'],
                    $s['id']
                );
                // Collect unique values for filter dropdowns
                $courses[]    = $s['course'];
                $yearLevels[] = (string) $s['year_level'];
            }
            unset($s);

            // Apply filters
            $c['students'] = array_values(array_filter($c['students'], function ($s) use ($search, $filterStatus, $filterCourse, $filterYear) {
                if ($search !== '') {
                    $haystack = strtolower($s['full_name'] . ' ' . $s['student_number']);
                    if (strpos($haystack, strtolower($search)) === false) return false;
                }
                if ($filterStatus !== 'all' && $s['display_status'] !== $filterStatus) return false;
                if ($filterCourse !== '' && $s['course'] !== $filterCourse) return false;
                if ($filterYear   !== '' && (string)$s['year_level'] !== $filterYear) return false;
                return true;
            }));
        }
        unset($c);

        $courses    = array_unique($courses);
        $yearLevels = array_unique($yearLevels);
        sort($courses);
        sort($yearLevels);

        $data = [
            'clearances'   => $clearances,
            'flash'        => $this->getFlash(),
            'userName'     => $_SESSION['user_name'],
            'search'       => $search,
            'filterStatus' => $filterStatus,
            'filterCourse' => $filterCourse,
            'filterYear'   => $filterYear,
            'courses'      => $courses,
            'yearLevels'   => $yearLevels,
        ];
        $this->view('layouts/main', array_merge($data, ['content' => 'adviser/clearances']));
    }

    // ----------------------------------------------------------------
    // Enrollment committee view
    // ----------------------------------------------------------------

    public function enrollment(): void
    {
        $this->requireLogin('adviser');

        $search       = trim($this->getGet('search', ''));
        $filterStatus = $this->getGet('status', 'all');
        $filterCourse = $this->getGet('course', '');
        $filterYear   = $this->getGet('year', '');

        $rows       = $this->statusModel->getClearancesForAdviser($_SESSION['user_id']);
        $clearances = $this->groupClearances($rows);

        $courses    = [];
        $yearLevels = [];

        foreach ($clearances as &$c) {
            foreach ($c['students'] as &$s) {
                $s['display_status']   = $this->resolveDisplayStatus($s);
                $s['signatory_detail'] = $this->statusModel->getSignatoryDetailForStudent(
                    $c['clearance_id'],
                    $s['id']
                );
                $courses[]    = $s['course'];
                $yearLevels[] = (string) $s['year_level'];
            }
            unset($s);

            $c['students'] = array_values(array_filter($c['students'], function ($s) use ($search, $filterStatus, $filterCourse, $filterYear) {
                if ($search !== '') {
                    $haystack = strtolower($s['full_name'] . ' ' . $s['student_number']);
                    if (strpos($haystack, strtolower($search)) === false) return false;
                }
                if ($filterStatus !== 'all' && $s['display_status'] !== $filterStatus) return false;
                if ($filterCourse !== '' && $s['course'] !== $filterCourse) return false;
                if ($filterYear   !== '' && (string)$s['year_level'] !== $filterYear) return false;
                return true;
            }));
        }
        unset($c);

        $courses    = array_unique($courses);
        $yearLevels = array_unique($yearLevels);
        sort($courses);
        sort($yearLevels);

        $data = [
            'clearances'   => $clearances,
            'flash'        => $this->getFlash(),
            'userName'     => $_SESSION['user_name'],
            'search'       => $search,
            'filterStatus' => $filterStatus,
            'filterCourse' => $filterCourse,
            'filterYear'   => $filterYear,
            'courses'      => $courses,
            'yearLevels'   => $yearLevels,
        ];
        $this->view('layouts/main', array_merge($data, ['content' => 'adviser/enrollment']));
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
     * Resolve a student's display status for adviser view.
     *   flagged   → has at least one flagged signatory row
     *   cleared   → all signatories cleared (no flags, no pending)
     *   pending   → mix of cleared and pending, nothing flagged
     */
    private function resolveDisplayStatus(array $s): string
    {
        if ((int) $s['flagged_count'] > 0) return 'flagged';
        $total   = (int) $s['total_count'];
        $cleared = (int) $s['cleared_count'];
        if ($total > 0 && $cleared === $total) return 'cleared';
        return 'pending';
    }
}
