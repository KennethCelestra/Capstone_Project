<?php
require_once ROOT_PATH . '/app/Models/Student.php';
require_once ROOT_PATH . '/app/Models/Adviser.php';
require_once ROOT_PATH . '/app/Models/Signatory.php';
require_once ROOT_PATH . '/app/Models/Clearance.php';
require_once ROOT_PATH . '/app/Models/ClearanceStatus.php';

class AdminController extends Controller
{
    private Student   $studentModel;
    private Adviser   $adviserModel;
    private Signatory $signatoryModel;
    private Clearance $clearanceModel;

    public function __construct()
    {
        $this->studentModel   = new Student();
        $this->adviserModel   = new Adviser();
        $this->signatoryModel = new Signatory();
        $this->clearanceModel = new Clearance();
    }

    // ================================================================
    //  DASHBOARD
    // ================================================================

    public function dashboard(): void
    {
        $this->requireLogin('admin');
        $data = [
            'studentCount'   => $this->studentModel->count(),
            'adviserCount'   => $this->adviserModel->count(),
            'signatoryCount' => $this->signatoryModel->count(),
            'clearanceCount' => $this->clearanceModel->count(),
            'flash'          => $this->getFlash(),
            'userName'       => $_SESSION['user_name'],
        ];
        $this->view('layouts/main', array_merge($data, ['content' => 'admin/dashboard']));
    }

    // ================================================================
    //  STUDENTS (global list + CSV upload + dummies)
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
            'full_name'  => $this->getPost('full_name'),
            'email'      => $this->getPost('email'),
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
            $this->redirect("admin/clearances/{$clearanceId}");
            return;
        }

        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, 'r');
        if (!$handle) {
            $this->setFlash('error', 'Could not read the uploaded file.');
            $this->redirect("admin/clearances/{$clearanceId}");
            return;
        }

        $rows   = [];
        $header = null;
        while (($line = fgetcsv($handle)) !== false) {
            if ($header === null) {
                // Normalize header keys
                $header = array_map(fn($h) => strtolower(trim($h)), $line);
                continue;
            }
            if (count($line) < 2) continue;
            $rows[] = array_combine($header, array_map('trim', $line));
        }
        fclose($handle);

        [$inserted, $skipped] = $this->studentModel->bulkInsertFromCSV($rows);

        // Enroll newly inserted students into the clearance
        if ($clearanceId > 0 && $inserted > 0) {
            // Re-fetch all students that are now in DB but not yet in this clearance,
            // using the student_ids that came from the CSV
            $unEnrolled = $this->studentModel->findNotInClearance($clearanceId);
            // Filter to only those whose student_id appeared in the CSV
            $csvIds = array_column($rows, 'student_id');
            foreach ($unEnrolled as $st) {
                if (in_array($st['student_id'], $csvIds, true)) {
                    $this->clearanceModel->enrollStudent($clearanceId, (int) $st['id']);
                }
            }
        }

        $this->setFlash('success', "Import complete: {$inserted} inserted, {$skipped} skipped.");
        $this->redirect("admin/clearances/{$clearanceId}");
    }

    public function insertDummies(): void
    {
        $this->requireLogin('admin');
        $clearanceId = (int) $this->getPost('clearance_id');

        [$inserted, $skipped] = $this->studentModel->insertDummies();

        // Enroll dummy students into the clearance
        if ($clearanceId > 0) {
            $dummyIds = ['2024-00001','2024-00002','2024-00003','2024-00004','2024-00005',
                         '2024-00006','2024-00007','2024-00008','2024-00009','2024-00010'];
            foreach ($dummyIds as $sid) {
                $student = $this->studentModel->findByStudentId($sid);
                if ($student) {
                    $this->clearanceModel->enrollStudent($clearanceId, (int) $student['id']);
                }
            }
        }

        $this->setFlash('success', "Dummy students: {$inserted} inserted, {$skipped} already existed. All enrolled in clearance.");
        $this->redirect("admin/clearances/{$clearanceId}");
    }

    // ================================================================
    //  ADVISERS
    // ================================================================

    public function advisers(): void
    {
        $this->requireLogin('admin');
        $data = [
            'advisers' => $this->adviserModel->findAll(),
            'flash'    => $this->getFlash(),
            'userName' => $_SESSION['user_name'],
        ];
        $this->view('layouts/main', array_merge($data, ['content' => 'admin/advisers']));
    }

    public function addAdviser(): void
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
            $this->adviserModel->create($data);
            $this->setFlash('success', 'Adviser added. Credentials: ' . $data['email'] . ' / ' . $data['password']);
        }
        $this->redirect('admin/advisers');
    }

    public function editAdviser(): void
    {
        $this->requireLogin('admin');
        $id = (int) $this->getPost('id');
        $data = [
            'full_name'  => $this->getPost('full_name'),
            'email'      => $this->getPost('email'),
            'department' => $this->getPost('department'),
            'password'   => $this->getPost('password', ''),
        ];
        $this->adviserModel->update($id, $data);
        $this->setFlash('success', 'Adviser updated successfully.');
        $this->redirect('admin/advisers');
    }

    public function deleteAdviser(): void
    {
        $this->requireLogin('admin');
        $id = (int) $this->getPost('id');
        $this->adviserModel->delete($id);
        $this->setFlash('success', 'Adviser deleted.');
        $this->redirect('admin/advisers');
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
        $data = [
            'full_name' => $this->getPost('full_name'),
            'email'     => $this->getPost('email'),
            'office'    => $this->getPost('office'),
            'password'  => $this->getPost('password'),
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
        $data = [
            'full_name' => $this->getPost('full_name'),
            'email'     => $this->getPost('email'),
            'office'    => $this->getPost('office'),
            'password'  => $this->getPost('password', ''),
        ];
        $this->signatoryModel->update($id, $data);
        $this->setFlash('success', 'Signatory updated successfully.');
        $this->redirect('admin/signatories');
    }

    public function deleteSignatory(): void
    {
        $this->requireLogin('admin');
        $id = (int) $this->getPost('id');
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
        $this->redirect("admin/clearances/{$newId}");
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
        $this->clearanceModel->update($id, $data);
        $this->setFlash('success', 'Clearance updated.');
        $this->redirect("admin/clearances/{$id}");
    }

    public function deleteClearance(): void
    {
        $this->requireLogin('admin');
        $id = (int) $this->getPost('id');
        $this->clearanceModel->delete($id);
        $this->setFlash('success', 'Clearance deleted.');
        $this->redirect('admin/clearances');
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
            'assignedAdvisers'    => $this->clearanceModel->getAdvisers($id),
            'unassignedAdvisers'  => $this->adviserModel->findUnassigned($id),
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
        $clearanceId  = (int) $this->getPost('clearance_id');
        $signatoryId  = (int) $this->getPost('signatory_id');
        $this->clearanceModel->assignSignatory($clearanceId, $signatoryId);
        $this->setFlash('success', 'Signatory assigned to clearance.');
        $this->redirect("admin/clearances/{$clearanceId}");
    }

    public function removeSignatory(): void
    {
        $this->requireLogin('admin');
        $clearanceId  = (int) $this->getPost('clearance_id');
        $signatoryId  = (int) $this->getPost('signatory_id');
        $this->clearanceModel->removeSignatory($clearanceId, $signatoryId);
        $this->setFlash('success', 'Signatory removed from clearance.');
        $this->redirect("admin/clearances/{$clearanceId}");
    }

    // ---- Adviser assignment ----

    public function assignAdviser(): void
    {
        $this->requireLogin('admin');
        $clearanceId = (int) $this->getPost('clearance_id');
        $adviserId   = (int) $this->getPost('adviser_id');
        $this->clearanceModel->assignAdviser($clearanceId, $adviserId);
        $this->setFlash('success', 'Adviser assigned to clearance.');
        $this->redirect("admin/clearances/{$clearanceId}");
    }

    public function removeAdviser(): void
    {
        $this->requireLogin('admin');
        $clearanceId = (int) $this->getPost('clearance_id');
        $adviserId   = (int) $this->getPost('adviser_id');
        $this->clearanceModel->removeAdviser($clearanceId, $adviserId);
        $this->setFlash('success', 'Adviser removed from clearance.');
        $this->redirect("admin/clearances/{$clearanceId}");
    }

    // ---- Student removal from clearance ----

    public function removeStudentFromClearance(): void
    {
        $this->requireLogin('admin');
        $clearanceId = (int) $this->getPost('clearance_id');
        $studentId   = (int) $this->getPost('student_id');
        $this->clearanceModel->removeStudent($clearanceId, $studentId);
        $this->setFlash('success', 'Student removed from clearance.');
        $this->redirect("admin/clearances/{$clearanceId}");
    }
}
