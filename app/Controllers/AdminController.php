<?php
require_once ROOT_PATH . '/app/Models/Student.php';
require_once ROOT_PATH . '/app/Models/Enrollment_Committee.php';
require_once ROOT_PATH . '/app/Models/Signatory.php';
require_once ROOT_PATH . '/app/Models/Clearance.php';
require_once ROOT_PATH . '/app/Models/Admin.php';

class AdminController extends Controller
{
    private Student              $studentModel;
    private Enrollment_Committee $enrollmentCommitteeModel;
    private Signatory            $signatoryModel;
    private Clearance            $clearanceModel;
    private Admin                $adminModel;

    public function __construct()
    {
        $this->studentModel             = new Student();
        $this->enrollmentCommitteeModel = new Enrollment_Committee();
        $this->signatoryModel           = new Signatory();
        $this->clearanceModel           = new Clearance();
        $this->adminModel               = new Admin();
    }

    // ================================================================
    //  DASHBOARD
    // ================================================================

    public function dashboard(): void
    {
        $this->requireLogin('admin');
        $data = [
            'studentCount'             => $this->studentModel->count(),
            'enrollmentCommitteeCount' => $this->enrollmentCommitteeModel->count(),
            'signatoryCount'  => $this->signatoryModel->count(),
            'clearanceCount'  => $this->clearanceModel->count(),
            'overallProgress' => $this->clearanceModel->getOverallProgress(),
            'clearances'      => $this->clearanceModel->getActiveClearancesWithProgress(),
            'flash'           => $this->getFlash(),
            'userName'        => $_SESSION['user_name'],
        ];
        $this->view('layouts/main', array_merge($data, ['content' => 'admin/dashboard']));
    }

    public function verifyPassword(): void
    {
        $this->requireLogin('admin');
        header('Content-Type: application/json');
        $adminPass = $this->getPost('admin_password', '');
        $adminUser = $this->adminModel->findById((int) $_SESSION['user_id']);
        if ($adminUser && password_verify($adminPass, $adminUser['password'])) {
            echo json_encode(['valid' => true]);
        } else {
            echo json_encode(['valid' => false]);
        }
        exit;
    }

    // ================================================================
    //  STUDENTS
    // ================================================================

    public function students(): void
    {
        $this->requireLogin('admin');
        $data = [
            'students' => $this->studentModel->findAll(),
            'flash'    => $this->getFlash(),
            'userName' => $_SESSION['user_name'],
        ];
        $this->view('layouts/main', array_merge($data, ['content' => 'admin/students']));
    }

    public function addStudent(): void
    {
        $this->requireLogin('admin');
        $data = [
            'student_id' => $this->getPost('student_id'),
            'last_name'  => $this->getPost('last_name'),
            'first_name' => $this->getPost('first_name'),
            'email'      => $this->getPost('email'),
            'college'    => $this->getPost('college'),
            'course'     => $this->getPost('course'),
            'year_level' => $this->getPost('year_level'),
            'section'    => $this->getPost('section'),
            'password'   => $this->getPost('password'),
        ];
        if (in_array('', $data, true)) {
            $this->setFlash('error', 'All fields are required.');
        } else {
            $this->studentModel->create($data);
            $this->setFlash('success', 'Student added successfully.');
        }
        $this->redirect('admin/students');
    }

    public function deleteStudent(): void
    {
        $this->requireLogin('admin');
        $id = (int) $this->getPost('id');
        if ($id <= 0) {
            $this->setFlash('error', 'Invalid student ID.');
            $this->redirect('admin/students');
            return;
        }
        $this->studentModel->delete($id);
        $this->setFlash('success', 'Student deleted.');
        $this->redirect('admin/students');
    }

    public function uploadStudents(): void
    {
        $this->requireLogin('admin');
        $clearanceId = (int) $this->getPost('clearance_id');

        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            $this->setFlash('error', 'Please select a valid CSV file.');
            $this->redirect("admin/clearances/detail?id={$clearanceId}");
            return;
        }

        $file   = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, 'r');
        if (!$handle) {
            $this->setFlash('error', 'Could not read the uploaded file.');
            $this->redirect("admin/clearances/detail?id={$clearanceId}");
            return;
        }

        $rows   = [];
        $header = null;
        while (($line = fgetcsv($handle)) !== false) {
            if ($header === null) {
                $header = array_map(fn($h) => strtolower(trim($h)), $line);
                continue;
            }
            if (count($line) < count($header)) continue; // skip malformed rows
            $combined = array_combine($header, array_map('trim', $line));
            if ($combined === false) continue;
            $rows[] = $combined;
        }
        fclose($handle);

        [$inserted, $skipped] = $this->studentModel->bulkInsertFromCSV($rows);

        $enrolledCount = 0;
        if ($clearanceId > 0) {
            $csvIds     = array_column($rows, 'student_id');
            $unEnrolled = $this->studentModel->findNotInClearance($clearanceId);
            
            $studentIdsToEnroll = [];
            foreach ($unEnrolled as $st) {
                if (in_array($st['student_id'], $csvIds)) {
                    $studentIdsToEnroll[] = (int) $st['id'];
                }
            }
            
            if (!empty($studentIdsToEnroll)) {
                $this->clearanceModel->bulkEnrollStudents($clearanceId, $studentIdsToEnroll);
                $enrolledCount = count($studentIdsToEnroll);
            }
        }

        $this->setFlash('success', "Import complete: {$inserted} new student accounts created, {$skipped} existing accounts skipped. Enrolled {$enrolledCount} students into the clearance.");
        if ($clearanceId > 0) {
            $this->redirect("admin/clearances/detail?id={$clearanceId}");
        } else {
            $this->redirect('admin/students');
        }
    }


    // ================================================================
    //  ENROLLMENT COMMITTEE
    // ================================================================

    public function enrollmentCommittees(): void
    {
        $this->requireLogin('admin');
        $data = [
            'enrollment_committees' => $this->enrollmentCommitteeModel->findAll(),
            'flash'                 => $this->getFlash(),
            'userName'              => $_SESSION['user_name'],
        ];
        $this->view('layouts/main', array_merge($data, ['content' => 'admin/enrollment_committees']));
    }

    public function addEnrollmentCommittee(): void
    {
        $this->requireLogin('admin');
        $data = [
            'full_name'  => $this->getPost('full_name'),
            'email'      => $this->getPost('email'),
            'department' => $this->getPost('department'),
            'password'   => $this->getPost('password'),
        ];
        if (in_array('', $data, true)) {
            $this->setFlash('error', 'All fields are required.');
        } else {
            $this->enrollmentCommitteeModel->create($data);
            $this->setFlash('success', 'Enrollment Committee member added. Credentials: ' . $data['email'] . ' / ' . $data['password']);
        }
        $this->redirect('admin/enrollment-committees');
    }

    public function editEnrollmentCommittee(): void
    {
        $this->requireLogin('admin');
        $id = (int) $this->getPost('id');
        $data = [
            'full_name'  => $this->getPost('full_name'),
            'email'      => $this->getPost('email'),
            'department' => $this->getPost('department'),
        ];
        
        $this->enrollmentCommitteeModel->update($id, $data);
        $this->setFlash('success', 'Enrollment Committee member updated successfully.');
        $this->redirect('admin/enrollment-committees');
    }

    public function deleteEnrollmentCommittee(): void
    {
        $this->requireLogin('admin');
        $id = (int) $this->getPost('id');
        if ($id <= 0) {
            $this->setFlash('error', 'Invalid member ID.');
            $this->redirect('admin/enrollment-committees');
            return;
        }
        $this->enrollmentCommitteeModel->delete($id);
        $this->setFlash('success', 'Enrollment Committee member deleted.');
        $this->redirect('admin/enrollment-committees');
    }

    // ================================================================
    //  SIGNATORIES
    // ================================================================

    public function signatories(): void
    {
        $this->requireLogin('admin');
        $data = [
            'signatories' => $this->signatoryModel->findAll(),
            'flash'       => $this->getFlash(),
            'userName'    => $_SESSION['user_name'],
        ];
        $this->view('layouts/main', array_merge($data, ['content' => 'admin/signatories']));
    }

    public function addSignatory(): void
    {
        $this->requireLogin('admin');
        
        $scopeType = $this->getPost('scope_type', '');
        $scopeValue = null;
        if ($scopeType === 'college') {
            $scopeValue = $this->getPost('scope_college', '');
        } elseif ($scopeType === 'course') {
            $scopeValue = $this->getPost('scope_course', '');
        } else {
            $scopeType = null;
        }

        $data = [
            'full_name'   => $this->getPost('full_name'),
            'email'       => $this->getPost('email'),
            'office'      => $this->getPost('office'),
            'password'    => $this->getPost('password'),
            'scope_type'  => $scopeType,
            'scope_value' => $scopeValue,
        ];
        if (in_array('', $data, true)) {
            $this->setFlash('error', 'All fields are required.');
        } else {
            $this->signatoryModel->create($data);
            $this->setFlash('success', 'Signatory added. Credentials: ' . $data['email'] . ' / ' . $data['password']);
        }
        $this->redirect('admin/signatories');
    }

    public function editSignatory(): void
    {
        $this->requireLogin('admin');
        $id = (int) $this->getPost('id');

        $scopeType = $this->getPost('scope_type', '');
        $scopeValue = null;
        if ($scopeType === 'college') {
            $scopeValue = $this->getPost('scope_college', '');
        } elseif ($scopeType === 'course') {
            $scopeValue = $this->getPost('scope_course', '');
        } else {
            $scopeType = null;
        }

        $data = [
            'full_name'   => $this->getPost('full_name'),
            'email'       => $this->getPost('email'),
            'office'      => $this->getPost('office'),
            'scope_type'  => $scopeType,
            'scope_value' => $scopeValue,
        ];
        
        $this->signatoryModel->update($id, $data);
        $this->setFlash('success', 'Signatory updated successfully.');
        $this->redirect('admin/signatories');
    }

    public function deleteSignatory(): void
    {
        $this->requireLogin('admin');
        $id = (int) $this->getPost('id');
        if ($id <= 0) {
            $this->setFlash('error', 'Invalid signatory ID.');
            $this->redirect('admin/signatories');
            return;
        }
        $this->signatoryModel->delete($id);
        $this->setFlash('success', 'Signatory deleted.');
        $this->redirect('admin/signatories');
    }

    // ================================================================
    //  CLEARANCES
    // ================================================================

    public function clearances(): void
    {
        $this->requireLogin('admin');
        $data = [
            'clearances' => $this->clearanceModel->findAllWithCounts(),
            'flash'      => $this->getFlash(),
            'userName'   => $_SESSION['user_name'],
        ];
        $this->view('layouts/main', array_merge($data, ['content' => 'admin/clearances']));
    }

    public function createClearance(): void
    {
        $this->requireLogin('admin');
        $data = [
            'name'        => $this->getPost('name'),
            'description' => $this->getPost('description', ''),
            'school_year' => $this->getPost('school_year', ''),
        ];
        if (empty($data['name'])) {
            $this->setFlash('error', 'Clearance name is required.');
            $this->redirect('admin/clearances');
            return;
        }
        $this->clearanceModel->create($data);
        $newId = $this->clearanceModel->getLastInsertId();
        $this->setFlash('success', 'Clearance created successfully.');
        $this->redirect("admin/clearances/detail?id={$newId}");
    }

    public function editClearance(): void
    {
        $this->requireLogin('admin');
        $id = (int) $this->getPost('id');
        $data = [
            'name'        => $this->getPost('name'),
            'description' => $this->getPost('description', ''),
            'school_year' => $this->getPost('school_year', ''),
        ];
        if (empty($data['name'])) {
            $this->setFlash('error', 'Clearance name is required.');
            $this->redirect("admin/clearances/detail?id={$id}");
            return;
        }
        $this->clearanceModel->update($id, $data);
        $this->setFlash('success', 'Clearance updated.');
        $this->redirect("admin/clearances/detail?id={$id}");
    }

    public function deleteClearance(): void
    {
        $this->requireLogin('admin');
        $id = (int) $this->getPost('id');
        $this->clearanceModel->delete($id);
        $this->setFlash('success', 'Clearance deleted.');
        $this->redirect('admin/clearances');
    }

    public function archiveClearance(): void
    {
        $this->requireLogin('admin');
        $id = (int) $this->getPost('id');
        $this->clearanceModel->archive($id);
        $this->setFlash('success', 'Clearance archived. You can restore it from Archived Clearances.');
        $this->redirect('admin/clearances');
    }

    public function unarchiveClearance(): void
    {
        $this->requireLogin('admin');
        $id = (int) $this->getPost('id');
        $this->clearanceModel->unarchive($id);
        $this->setFlash('success', 'Clearance restored to active.');
        $this->redirect('admin/archived-clearances');
    }

    public function archivedClearances(): void
    {
        $this->requireLogin('admin');
        $data = [
            'clearances' => $this->clearanceModel->findAllArchived(),
            'flash'      => $this->getFlash(),
            'userName'   => $_SESSION['user_name'],
        ];
        $this->view('layouts/main', array_merge($data, ['content' => 'admin/archived_clearances']));
    }

    public function clearanceDetail(): void
    {
        $this->requireLogin('admin');
        $id = (int) $this->getGet('id');
        $clearance = $this->clearanceModel->findById($id);
        if (!$clearance) {
            $this->setFlash('error', 'Clearance not found.');
            $this->redirect('admin/clearances');
            return;
        }

        $data = [
            'clearance'           => $clearance,
            'assignedSignatories' => $this->clearanceModel->getSignatories($id),
            'unassignedSignatories' => $this->signatoryModel->findUnassigned($id),
            'assignedEnrollmentCommittees' => $this->clearanceModel->getEnrollmentCommittees($id),
            'unassignedEnrollmentCommittees' => $this->enrollmentCommitteeModel->findUnassigned($id),
            'students'            => $this->clearanceModel->getStudentsWithStatus($id),
            'flash'               => $this->getFlash(),
            'userName'            => $_SESSION['user_name'],
        ];
        $this->view('layouts/main', array_merge($data, ['content' => 'admin/clearance_detail']));
    }

    // ---- Signatory assignment ----

    public function assignSignatory(): void
    {
        $this->requireLogin('admin');
        $clearanceId = (int) $this->getPost('clearance_id');
        $signatoryId = (int) $this->getPost('signatory_id');

        $this->clearanceModel->assignSignatory($clearanceId, $signatoryId);
        $this->setFlash('success', 'Signatory assigned to clearance.');
        $this->redirect("admin/clearances/detail?id={$clearanceId}");
    }

    public function bulkAssignSignatories(): void
    {
        $this->requireLogin('admin');
        $clearanceId  = (int) $this->getPost('clearance_id');
        $signatoryIds = $this->getPost('signatory_ids', []);

        if (!empty($signatoryIds)) {
            foreach ((array) $signatoryIds as $sid) {
                $this->clearanceModel->assignSignatory($clearanceId, (int) $sid);
            }
            $count = count($signatoryIds);
            $this->setFlash('success', "{$count} signator" . ($count === 1 ? 'y' : 'ies') . " assigned.");
        } else {
            $this->setFlash('error', 'No signatories selected.');
        }
        $this->redirect("admin/clearances/detail?id={$clearanceId}");
    }

    public function removeSignatory(): void
    {
        $this->requireLogin('admin');
        $clearanceId = (int) $this->getPost('clearance_id');
        $signatoryId = (int) $this->getPost('signatory_id');
        $this->clearanceModel->removeSignatory($clearanceId, $signatoryId);
        $this->setFlash('success', 'Signatory removed from clearance.');
        $this->redirect("admin/clearances/detail?id={$clearanceId}");
    }

    // ---- Enrollment Committee assignment ----

    public function assignEnrollmentCommittee(): void
    {
        $this->requireLogin('admin');
        $clearanceId = (int) $this->getPost('clearance_id');
        $enrollmentCommitteeId = (int) $this->getPost('enrollment_committee_id');
        $this->clearanceModel->assignEnrollmentCommittee($clearanceId, $enrollmentCommitteeId);
        $this->setFlash('success', 'Enrollment Committee member assigned to clearance.');
        $this->redirect("admin/clearances/detail?id={$clearanceId}");
    }

    public function bulkAssignEnrollmentCommittees(): void
    {
        $this->requireLogin('admin');
        $clearanceId = (int) $this->getPost('clearance_id');
        $enrollmentCommitteeIds = $this->getPost('enrollment_committee_ids', []);
        
        if (!empty($enrollmentCommitteeIds)) {
            foreach ((array) $enrollmentCommitteeIds as $aid) {
                $this->clearanceModel->assignEnrollmentCommittee($clearanceId, (int) $aid);
            }
            $count = count($enrollmentCommitteeIds);
            $this->setFlash('success', "{$count} enrollment committee member" . ($count === 1 ? '' : 's') . " assigned.");
        } else {
            $this->setFlash('success', 'Clearance setup complete. No enrollment committee assigned.');
        }
        $this->redirect("admin/clearances/detail?id={$clearanceId}");
    }

    public function removeEnrollmentCommittee(): void
    {
        $this->requireLogin('admin');
        $clearanceId = (int) $this->getPost('clearance_id');
        $enrollmentCommitteeId = (int) $this->getPost('enrollment_committee_id');
        $this->clearanceModel->removeEnrollmentCommittee($clearanceId, $enrollmentCommitteeId);
        $this->setFlash('success', 'Enrollment Committee member removed from clearance.');
        $this->redirect("admin/clearances/detail?id={$clearanceId}");
    }

    // ---- Student removal from clearance ----

    public function removeStudentFromClearance(): void
    {
        $this->requireLogin('admin');
        $clearanceId = (int) $this->getPost('clearance_id');
        $studentId   = (int) $this->getPost('student_id');
        $this->clearanceModel->removeStudent($clearanceId, $studentId);
        $this->setFlash('success', 'Student removed from clearance.');
        $this->redirect("admin/clearances/detail?id={$clearanceId}");
    }
}
