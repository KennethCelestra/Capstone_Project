<?php
require_once ROOT_PATH . '/app/Models/ClearanceStatus.php';
require_once ROOT_PATH . '/app/Models/Signatory.php';
require_once ROOT_PATH . '/app/Helpers/Mailer.php';

class SignatoryController extends Controller
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
        $this->requireLogin('signatory');
        $signatoryId = (int) $_SESSION['user_id'];
        $clearances  = $this->statusModel->getClearancesForSignatory($signatoryId);

        $data = [
            'clearances' => $clearances,
            'flash'      => $this->getFlash(),
            'userName'   => $_SESSION['user_name'],
        ];
        $this->view('layouts/main', array_merge($data, ['content' => 'signatory/dashboard']));
    }

    // ----------------------------------------------------------------
    // Student list (main work screen)
    // ----------------------------------------------------------------

    public function clearances(): void
    {
        $this->requireLogin('signatory');
        $signatoryId = (int) $_SESSION['user_id'];

        // Filters from GET
        $search      = trim($this->getGet('search', ''));
        $filterStatus = $this->getGet('status', 'all');   // all|pending|cleared|flagged
        $filterCourse = $this->getGet('course', '');
        $filterYear   = $this->getGet('year', '');

        $clearanceSummaries = $this->statusModel->getClearancesForSignatory($signatoryId);

        $clearances = [];
        foreach ($clearanceSummaries as $c) {
            $cid = (int) $c['clearance_id'];
            $students = $this->statusModel->getStudentsForSignatory($cid, $signatoryId);

            // Apply filters client-side via PHP
            $students = $this->applyFilters($students, $search, $filterStatus, $filterCourse, $filterYear);

            $c['students'] = $students;
            $clearances[]  = $c;
        }

        // Collect unique courses and year levels for filter dropdowns
        $allStudents = [];
        foreach ($clearances as $c) {
            foreach ($c['students'] as $s) {
                $allStudents[] = $s;
            }
        }
        $courses    = array_unique(array_column($allStudents, 'course'));
        $yearLevels = array_unique(array_column($allStudents, 'year_level'));
        sort($courses);
        sort($yearLevels);

        $data = [
            'clearances'    => $clearances,
            'flash'         => $this->getFlash(),
            'userName'      => $_SESSION['user_name'],
            'search'        => $search,
            'filterStatus'  => $filterStatus,
            'filterCourse'  => $filterCourse,
            'filterYear'    => $filterYear,
            'courses'       => $courses,
            'yearLevels'    => $yearLevels,
        ];
        $this->view('layouts/main', array_merge($data, ['content' => 'signatory/clearances']));
    }

    // ----------------------------------------------------------------
    // Flag a student
    // ----------------------------------------------------------------

    public function flagStudent(): void
    {
        $this->requireLogin('signatory');
        $clearanceId = (int)  $this->getPost('clearance_id');
        $studentId   = (int)  $this->getPost('student_id');
        $note        = trim($this->getPost('flag_note', ''));
        $signatoryId = (int)  $_SESSION['user_id'];

        if ($clearanceId && $studentId && $note !== '') {
            $this->statusModel->flagStudent($clearanceId, $studentId, $signatoryId, $note);
            $this->setFlash('warning', 'Student has been flagged for a deficiency.');
        } else {
            $this->setFlash('error', 'Please provide a reason for flagging.');
        }

        $this->redirect('signatory/clearances');
    }

    // ----------------------------------------------------------------
    // Unflag (clear) a student
    // ----------------------------------------------------------------

    public function clearStudent(): void
    {
        $this->requireLogin('signatory');
        $clearanceId = (int) $this->getPost('clearance_id');
        $studentId   = (int) $this->getPost('student_id');
        $signatoryId = (int) $_SESSION['user_id'];

        if ($clearanceId && $studentId) {
            $this->statusModel->clearStudent($clearanceId, $studentId, $signatoryId);

            // Check if student is now fully cleared across ALL signatories
            if ($this->statusModel->isStudentFullyCleared($clearanceId, $studentId)) {
                $info = $this->statusModel->getStudentClearanceInfo($clearanceId, $studentId);
                if ($info) {
                    Mailer::sendClearedEmail(
                        $info['email'],
                        $info['full_name'],
                        $info['clearance_name']
                    );
                }
            }

            $this->setFlash('success', 'Student deficiency has been cleared.');
        }

        $this->redirect('signatory/clearances');
    }

    // ----------------------------------------------------------------
    // Confirmation screen
    // ----------------------------------------------------------------

    public function confirmFlags(): void
    {
        $this->requireLogin('signatory');
        $signatoryId = (int) $_SESSION['user_id'];
        $flagged     = $this->statusModel->getFlaggedStudentsForConfirmation($signatoryId);

        $data = [
            'flagged'  => $flagged,
            'flash'    => $this->getFlash(),
            'userName' => $_SESSION['user_name'],
        ];
        $this->view('layouts/main', array_merge($data, ['content' => 'signatory/confirm']));
    }

    public function submitConfirm(): void
    {
        $this->requireLogin('signatory');
        $signatoryId = (int) $_SESSION['user_id'];

        // Fetch signatory office name
        $signatoryModel  = new Signatory();
        $signatoryRecord = $signatoryModel->findById($signatoryId);
        $officeName      = $signatoryRecord ? $signatoryRecord['office'] : 'Office';

        $flagged = $this->statusModel->getFlaggedStudentsForConfirmation($signatoryId);
        $sent    = 0;
        $errors  = 0;

        foreach ($flagged as $f) {
            $ok = Mailer::sendDeficiencyEmail(
                $f['email'],
                $f['full_name'],
                $officeName,
                $f['flag_note'],
                $f['clearance_name']
            );
            $ok ? $sent++ : $errors++;
        }

        if ($errors === 0) {
            $this->setFlash('success', "Deficiency emails sent to {$sent} student(s).");
        } else {
            $this->setFlash('warning', "Emails sent: {$sent}. Failed: {$errors}. Check your mail configuration.");
        }

        $this->redirect('signatory/clearances');
    }

    // ----------------------------------------------------------------
    // Helper: filter student rows
    // ----------------------------------------------------------------

    private function applyFilters(
        array  $students,
        string $search,
        string $status,
        string $course,
        string $year
    ): array {
        return array_values(array_filter($students, function ($s) use ($search, $status, $course, $year) {
            // Search by name or student number
            if ($search !== '') {
                $haystack = strtolower($s['full_name'] . ' ' . $s['student_number']);
                if (strpos($haystack, strtolower($search)) === false) {
                    return false;
                }
            }
            // Status filter
            if ($status !== 'all' && $s['status'] !== $status) {
                return false;
            }
            // Course filter
            if ($course !== '' && $s['course'] !== $course) {
                return false;
            }
            // Year level filter
            if ($year !== '' && (string)$s['year_level'] !== $year) {
                return false;
            }
            return true;
        }));
    }
}
