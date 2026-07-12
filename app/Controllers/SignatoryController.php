<?php
require_once ROOT_PATH . '/app/Models/ClearanceStatus.php';
require_once ROOT_PATH . '/app/Models/Signatory.php';
require_once ROOT_PATH . '/app/Helpers/Mailer.php';

class SignatoryController extends Controller
{
    private ClearanceStatus $statusModel;
    private Signatory       $signatoryModel;

    public function __construct()
    {
        $this->statusModel     = new ClearanceStatus();
        $this->signatoryModel  = new Signatory();
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
    // My Clearances — two-phase view
    //   Phase 1 (no ?cid): show clearance selection cards
    //   Phase 2 (?cid=N) : show student list for that clearance
    // ----------------------------------------------------------------

    public function clearances(): void
    {
        $this->requireLogin('signatory');
        $signatoryId     = (int) $_SESSION['user_id'];
        $selectedCid     = (int) $this->getGet('cid', 0);

        // Always load clearance summaries (for cards / back nav)
        $clearanceSummaries = $this->statusModel->getClearancesForSignatory($signatoryId);

        // Phase 2: a specific clearance is selected
        if ($selectedCid > 0) {
            // Verify this signatory is actually assigned to this clearance
            $validCids = array_column($clearanceSummaries, 'clearance_id');
            if (!in_array($selectedCid, $validCids)) {
                $this->setFlash('error', 'You are not assigned to that clearance.');
                $this->redirect('signatory/clearances');
                return;
            }

            // Filters from GET
            $search       = trim($this->getGet('search', ''));
            $filterStatus = $this->getGet('status', 'all');
            $filterCollege= $this->getGet('college', '');
            $filterCourse = $this->getGet('course', '');
            $filterYear   = $this->getGet('year', '');

            $students = $this->statusModel->getStudentsForSignatory($selectedCid, $signatoryId);

            // Collect unique colleges / courses / year levels before filtering
            $colleges   = array_unique(array_column($students, 'college'));
            $courses    = array_unique(array_column($students, 'course'));
            $yearLevels = array_unique(array_column($students, 'year_level'));
            sort($colleges);
            sort($courses);
            sort($yearLevels);

            // Apply filters
            $students = $this->applyFilters($students, $search, $filterStatus, $filterCollege, $filterCourse, $filterYear);

            // Find the selected clearance summary record
            $selectedClearance = null;
            foreach ($clearanceSummaries as $c) {
                if ((int)$c['clearance_id'] === $selectedCid) {
                    $selectedClearance = $c;
                    break;
                }
            }

            $data = [
                'phase'             => 'detail',
                'selectedCid'       => $selectedCid,
                'selectedClearance' => $selectedClearance,
                'students'          => $students,
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
            $this->view('layouts/main', array_merge($data, ['content' => 'signatory/clearances']));
            return;
        }

        // Phase 1: clearance selection cards
        $data = [
            'phase'      => 'select',
            'clearances' => $clearanceSummaries,
            'flash'      => $this->getFlash(),
            'userName'   => $_SESSION['user_name'],
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
            
            // Send deficiency email immediately
            $signatoryRecord = $this->signatoryModel->findById($signatoryId);
            $officeName      = $signatoryRecord ? $signatoryRecord['office'] : 'Office';
            $info            = $this->statusModel->getStudentClearanceInfo($clearanceId, $studentId);
            
            if ($info) {
                if (!isset($_SESSION['bg_emails'])) {
                    $_SESSION['bg_emails'] = [];
                }
                $_SESSION['bg_emails'][] = [
                    'type' => 'deficiency',
                    'officeName' => $officeName,
                    'students' => [[
                        'email' => $info['email'],
                        'full_name' => $info['full_name'],
                        'clearance_name' => $info['clearance_name'],
                        'flag_note' => $note
                    ]]
                ];
            }

            $this->setFlash('warning', 'Student has been flagged. Email sending in the background.');
        } else {
            $this->setFlash('error', 'Please provide a reason for flagging.');
        }

        $this->redirect("signatory/clearances?cid={$clearanceId}");
    }

    // ----------------------------------------------------------------
    // Clear (sign off) a single student
    // ----------------------------------------------------------------

    public function clearStudent(): void
    {
        $this->requireLogin('signatory');
        $clearanceId = (int) $this->getPost('clearance_id');
        $studentId   = (int) $this->getPost('student_id');
        $signatoryId = (int) $_SESSION['user_id'];

        if ($clearanceId && $studentId) {
            $this->statusModel->clearStudent($clearanceId, $studentId, $signatoryId);

            // Fire "fully cleared" email if student is cleared by ALL signatories
            if ($this->statusModel->isStudentFullyCleared($clearanceId, $studentId)) {
                $info = $this->statusModel->getStudentClearanceInfo($clearanceId, $studentId);
                if ($info) {
                    if (!isset($_SESSION['bg_emails'])) {
                        $_SESSION['bg_emails'] = [];
                    }
                    $_SESSION['bg_emails'][] = [
                        'type' => 'cleared',
                        'clearance_id' => $clearanceId,
                        'students' => [$info]
                    ];
                }
            }

            $this->setFlash('success', 'Student has been cleared.');
        }

        $this->redirect("signatory/clearances?cid={$clearanceId}");
    }

    // ----------------------------------------------------------------
    // Clear ALL pending (non-flagged) students in bulk
    // ----------------------------------------------------------------

    public function clearAll(): void
    {
        $this->requireLogin('signatory');
        $clearanceId = (int) $this->getPost('clearance_id');
        $signatoryId = (int) $_SESSION['user_id'];

        if ($clearanceId) {
            $clearedIds = $this->statusModel->clearAllPending($clearanceId, $signatoryId);
            $count = count($clearedIds);

            if ($count > 0) {
                // Check each student to see if they are now fully cleared
                $fullyClearedCount = 0;
                $infosToBg = [];
                foreach ($clearedIds as $studentId) {
                    if ($this->statusModel->isStudentFullyCleared($clearanceId, $studentId)) {
                        $info = $this->statusModel->getStudentClearanceInfo($clearanceId, $studentId);
                        if ($info) {
                            $infosToBg[] = $info;
                            $fullyClearedCount++;
                        }
                    }
                }

                if (!empty($infosToBg)) {
                    if (!isset($_SESSION['bg_emails'])) {
                        $_SESSION['bg_emails'] = [];
                    }
                    $_SESSION['bg_emails'][] = [
                        'type' => 'cleared',
                        'clearance_id' => $clearanceId,
                        'students' => $infosToBg
                    ];
                }

                $msg = "{$count} student(s) have been cleared.";
                if ($fullyClearedCount > 0) {
                    $msg .= " {$fullyClearedCount} student(s) fully cleared. Emails sending in the background.";
                }
                $this->setFlash('success', $msg);
            } else {
                $this->setFlash('info', 'No pending students to clear (all are either already cleared or flagged).');
            }
        }

        $this->redirect("signatory/clearances?cid={$clearanceId}");
    }

    // ----------------------------------------------------------------
    // Confirm All — bulk clear pending + send deficiency emails to flagged
    // ----------------------------------------------------------------

    public function confirmAll(): void
    {
        $this->requireLogin('signatory');
        $signatoryId = (int) $_SESSION['user_id'];
        $clearanceId = (int) $this->getPost('clearance_id', 0);

        if (!$clearanceId) {
            $this->setFlash('error', 'Invalid clearance.');
            $this->redirect('signatory/clearances');
            return;
        }

        // 1) Clear all pending students
        $clearedIds = $this->statusModel->clearAllPending($clearanceId, $signatoryId);
        $clearCount = count($clearedIds);

        $fullyClearedCount = 0;
        if ($clearCount > 0) {
            $fullyClearedIds = $this->statusModel->getFullyClearedStudents($clearanceId, $clearedIds);
            if (!empty($fullyClearedIds)) {
                $infos = $this->statusModel->getBulkStudentClearanceInfo($clearanceId, $fullyClearedIds);
                if (!empty($infos)) {
                    if (!isset($_SESSION['bg_emails'])) {
                        $_SESSION['bg_emails'] = [];
                    }
                    $_SESSION['bg_emails'][] = [
                        'type' => 'cleared',
                        'clearance_id' => $clearanceId,
                        'students' => $infos
                    ];
                    $fullyClearedCount += count($infos);
                }
            }
        }

        // 2) Send deficiency emails to all flagged students
        $signatoryRecord = $this->signatoryModel->findById($signatoryId);
        $officeName      = $signatoryRecord ? $signatoryRecord['office'] : 'Office';
        $flagged         = $this->statusModel->getFlaggedStudentsForConfirmation($signatoryId, $clearanceId);
        $sent = 0;
        $errors = 0;

        if (!empty($flagged)) {
            if (!isset($_SESSION['bg_emails'])) {
                $_SESSION['bg_emails'] = [];
            }
            $_SESSION['bg_emails'][] = [
                'type' => 'deficiency',
                'officeName' => $officeName,
                'students' => $flagged
            ];
            $sent = count($flagged);
        }

        // Build flash message
        $parts = [];
        if ($clearCount > 0) {
            $parts[] = "{$clearCount} student(s) cleared.";
        }
        if ($fullyClearedCount > 0) {
            $parts[] = "{$fullyClearedCount} student(s) fully cleared. Emails are sending in the background.";
        }
        if ($sent > 0) {
            $parts[] = "Deficiency emails for {$sent} student(s) are sending in the background.";
        }
        if ($errors > 0) {
            $parts[] = "{$errors} email(s) failed — check mail config.";
        }

        $msg = implode(' ', $parts) ?: 'No pending or flagged students to process.';
        $type = $errors > 0 ? 'warning' : 'success';
        $this->setFlash($type, $msg);

        $this->redirect("signatory/clearances?cid={$clearanceId}");
    }

    // ----------------------------------------------------------------
    // Background email processor (called via fetch() from layout)
    // ----------------------------------------------------------------

    public function processBgEmails(): void
    {
        // This endpoint is called silently from the browser after each page load
        // when bg_emails are queued in the session. No login check needed since
        // it only reads/clears the session and sends mail — no sensitive data exposed.
        header('Content-Type: application/json');

        if (empty($_SESSION['bg_emails'])) {
            echo json_encode(['status' => 'nothing']);
            return;
        }

        $queue = $_SESSION['bg_emails'];
        unset($_SESSION['bg_emails']); // Clear queue immediately before sending

        $sent   = 0;
        $errors = 0;

        foreach ($queue as $job) {
            if ($job['type'] === 'deficiency') {
                $ok = Mailer::sendBulkDeficiencyEmail($job['students'], $job['officeName'] ?? 'Office');
                $ok ? $sent++ : $errors++;
            } elseif ($job['type'] === 'cleared') {
                $ok = Mailer::sendBulkClearedEmail($job['students']);
                $ok ? $sent++ : $errors++;
            }
        }

        echo json_encode(['status' => 'done', 'sent' => $sent, 'errors' => $errors]);
    }

    // ----------------------------------------------------------------
    // Confirmation screen (inline confirm — kept for email sending)
    // ----------------------------------------------------------------

    public function submitConfirm(): void
    {
        $this->requireLogin('signatory');
        $signatoryId = (int) $_SESSION['user_id'];

        $signatoryRecord = $this->signatoryModel->findById($signatoryId);
        $officeName      = $signatoryRecord ? $signatoryRecord['office'] : 'Office';

        $clearanceId = (int) $this->getPost('clearance_id', 0);
        $flagged     = $this->statusModel->getFlaggedStudentsForConfirmation($signatoryId, $clearanceId);
        $sent = 0;
        $errors = 0;

        if (!empty($flagged)) {
            if (!isset($_SESSION['bg_emails'])) {
                $_SESSION['bg_emails'] = [];
            }
            $_SESSION['bg_emails'][] = [
                'type' => 'deficiency',
                'officeName' => $officeName,
                'students' => $flagged
            ];
            $sent = count($flagged);
        }

        if ($sent > 0) {
            $this->setFlash('success', "Deficiency emails for {$sent} student(s) sending in background.");
        } else {
            $this->setFlash('warning', "No flagged students to email.");
        }

        $redirect = $clearanceId > 0 ? "signatory/clearances?cid={$clearanceId}" : 'signatory/clearances';
        $this->redirect($redirect);
    }

    // ----------------------------------------------------------------
    // Helper: filter student rows
    // ----------------------------------------------------------------

    private function applyFilters(
        array  $students,
        string $search,
        string $status,
        string $college,
        string $course,
        string $year
    ): array {
        return array_values(array_filter($students, function ($s) use ($search, $status, $college, $course, $year) {
            if ($search !== '') {
                $haystack = strtolower($s['full_name'] . ' ' . $s['student_number']);
                if (strpos($haystack, strtolower($search)) === false) {
                    return false;
                }
            }
            if ($status !== 'all' && $s['status'] !== $status) {
                return false;
            }
            if ($college !== '' && $s['college'] !== $college) {
                return false;
            }
            if ($course !== '' && $s['course'] !== $course) {
                return false;
            }
            if ($year !== '' && (string)$s['year_level'] !== $year) {
                return false;
            }
            return true;
        }));
    }
}
