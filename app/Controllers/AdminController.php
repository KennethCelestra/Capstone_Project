<?php
require_once ROOT_PATH . '/app/Models/Student.php';
require_once ROOT_PATH . '/app/Models/Enrollment_Committee.php';
require_once ROOT_PATH . '/app/Models/Signatory.php';
require_once ROOT_PATH . '/app/Models/Clearance.php';
require_once ROOT_PATH . '/app/Models/ClearanceStatus.php';
require_once ROOT_PATH . '/app/Models/Admin.php';
require_once ROOT_PATH . '/app/Models/College.php';
require_once ROOT_PATH . '/app/Models/Program.php';
require_once ROOT_PATH . '/app/Models/Office.php';
require_once ROOT_PATH . '/app/Helpers/Validator.php';

class AdminController extends Controller
{
    private Student              $studentModel;
    private Enrollment_Committee $enrollmentCommitteeModel;
    private Signatory            $signatoryModel;
    private Clearance            $clearanceModel;
    private Admin                $adminModel;
    private College              $collegeModel;
    private Program              $programModel;
    private Office               $officeModel;
    private Validator            $validator;

    public function __construct()
    {
        $this->studentModel             = new Student();
        $this->enrollmentCommitteeModel = new Enrollment_Committee();
        $this->signatoryModel           = new Signatory();
        $this->clearanceModel           = new Clearance();
        $this->adminModel               = new Admin();
        $this->collegeModel             = new College();
        $this->programModel             = new Program();
        $this->officeModel              = new Office();
        $this->validator                = new Validator();
    }

    // ================================================================
    //  DASHBOARD
    // ================================================================

    public function dashboard(): void
    {
        $this->requireLogin('admin');
        $activeClearances = $this->clearanceModel->getActiveClearancesWithProgress();
        $data = [
            'studentCount'             => $this->studentModel->count(),
            'enrollmentCommitteeCount' => $this->enrollmentCommitteeModel->count(),
            'signatoryCount'           => $this->signatoryModel->count(),
            'clearanceCount'           => $this->clearanceModel->count(),
            'overallProgress'          => $this->clearanceModel->getOverallProgress($activeClearances),
            'clearances'               => $activeClearances,
            'flash'                    => $this->getFlash(),
            'userName'                 => $_SESSION['user_name'],
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
            'students'  => $this->studentModel->findAll(),
            'colleges'  => $this->collegeModel->getAll(),
            'programs'  => $this->programModel->getAll(),
            'courses'   => $this->studentModel->getDistinctCourses(),
            'flash'     => $this->getFlash(),
            'userName'  => $_SESSION['user_name'],
        ];
        $this->view('layouts/main', array_merge($data, ['content' => 'admin/students']));
    }

    public function addStudent(): void
    {
        $this->requireLogin('admin');
        if (!$this->validateCsrfToken()) { $this->redirect('admin/students'); return; }

        $studentId = Validator::sanitize((string)$this->getPost('student_id', ''));
        $lastName  = Validator::sanitize((string)$this->getPost('last_name', ''));
        $firstName = Validator::sanitize((string)$this->getPost('first_name', ''));
        $email     = Validator::sanitize((string)$this->getPost('email', ''));
        $college   = Validator::sanitize((string)$this->getPost('college', ''));
        $course    = Validator::sanitize((string)$this->getPost('course', ''));
        $yearLevel = (int) $this->getPost('year_level', 1);
        $section   = Validator::sanitize((string)$this->getPost('section', ''));

        if (empty($studentId) || empty($lastName) || empty($firstName) || empty($email) || empty($college) || empty($course)) {
            $this->setFlash('error', 'All required fields must be filled.');
            $this->redirect('admin/students');
            return;
        }

        if ($this->validator->isEmailTakenAnyTable($email)) {
            $this->setFlash('error', 'This email address is already registered in the system.');
            $this->redirect('admin/students');
            return;
        }

        if ($this->validator->isStudentIdTaken($studentId)) {
            $this->setFlash('error', 'A student with this ID already exists.');
            $this->redirect('admin/students');
            return;
        }

        $data = [
            'student_id' => $studentId,
            'last_name'  => $lastName,
            'first_name' => $firstName,
            'email'      => $email,
            'college'    => $college,
            'course'     => $course,
            'year_level' => $yearLevel,
            'section'    => $section,
        ];

        $this->studentModel->create($data);
        $this->setFlash('success', 'Student added successfully.');
        $this->redirect('admin/students');
    }

    public function deleteStudent(): void
    {
        $this->requireLogin('admin');
        if (!$this->validateCsrfToken()) { $this->redirect('admin/students'); return; }
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

    public function updateStudent(): void
    {
        $this->requireLogin('admin');
        if (!$this->validateCsrfToken()) { $this->redirect('admin/students'); return; }
        $id = (int) $this->getPost('id');
        if ($id <= 0) {
            $this->setFlash('error', 'Invalid student ID.');
            $this->redirect('admin/students');
            return;
        }
        $data = [
            'student_id' => Validator::sanitize((string)$this->getPost('student_id', '')),
            'last_name'  => Validator::sanitize((string)$this->getPost('last_name',  '')),
            'first_name' => Validator::sanitize((string)$this->getPost('first_name', '')),
            'email'      => Validator::sanitize((string)$this->getPost('email',      '')),
            'college'    => Validator::sanitize((string)$this->getPost('college',    '')),
            'course'     => Validator::sanitize((string)$this->getPost('course',     '')),
            'year_level' => (int) $this->getPost('year_level', 1),
            'section'    => Validator::sanitize((string)$this->getPost('section',    '')),
            'status'     => $this->getPost('status', 'active'),
        ];
        if (empty($data['student_id']) || empty($data['last_name']) || empty($data['first_name']) || empty($data['email'])) {
            $this->setFlash('error', 'Required fields are missing.');
            $this->redirect('admin/students');
            return;
        }

        if ($this->validator->isEmailTakenAnyTable($data['email'], $id, 'students')) {
            $this->setFlash('error', 'This email address is already registered to another account.');
            $this->redirect('admin/students');
            return;
        }

        if ($this->validator->isStudentIdTaken($data['student_id'], $id)) {
            $this->setFlash('error', 'A student with this ID already exists.');
            $this->redirect('admin/students');
            return;
        }

        $this->studentModel->update($id, $data);
        $this->setFlash('success', 'Student updated successfully.');
        $this->redirect('admin/students');
    }

    public function promoteStudents(): void
    {
        $this->requireLogin('admin');
        if (!$this->validateCsrfToken()) { $this->redirect('admin/students'); return; }
        $result = $this->studentModel->promoteAll();
        $this->setFlash(
            'success',
            "Year-end promotion complete: {$result['promoted']} student(s) moved up one year level, {$result['graduated']} 4th-year student(s) marked as Graduated."
        );
        $this->redirect('admin/students');
    }

    public function enrollAllStudents(): void
    {
        $this->requireLogin('admin');
        if (!$this->validateCsrfToken()) { $this->redirect('admin/clearances'); return; }
        $clearanceId = (int) $this->getPost('clearance_id');
        if ($clearanceId <= 0) {
            $this->setFlash('error', 'Invalid clearance.');
            $this->redirect('admin/clearances');
            return;
        }

        $unEnrolledActive = $this->studentModel->findNotInClearanceActive($clearanceId);
        if (empty($unEnrolledActive)) {
            $this->setFlash('success', 'All active students are already enrolled in this clearance.');
            $this->redirect("admin/clearances/detail?id={$clearanceId}#tab-stu");
            return;
        }

        $ids = array_column($unEnrolledActive, 'id');
        $this->clearanceModel->bulkEnrollStudents($clearanceId, $ids);
        $count = count($ids);
        $this->setFlash('success', "{$count} active student(s) enrolled into this clearance.");
        $this->redirect("admin/clearances/detail?id={$clearanceId}#tab-stu");
    }

    public function enrollFourthYearStudents(): void
    {
        $this->requireLogin('admin');
        if (!$this->validateCsrfToken()) { $this->redirect('admin/clearances'); return; }
        $clearanceId = (int) $this->getPost('clearance_id');
        if ($clearanceId <= 0) {
            $this->setFlash('error', 'Invalid clearance.');
            $this->redirect('admin/clearances');
            return;
        }

        $unEnrolled = $this->studentModel->findFourthYearNotInClearanceActive($clearanceId);
        if (empty($unEnrolled)) {
            $this->setFlash('success', 'All active 4th-year students are already enrolled in this exit clearance.');
            $this->redirect("admin/clearances/detail?id={$clearanceId}#tab-stu");
            return;
        }

        $ids = array_column($unEnrolled, 'id');
        $this->clearanceModel->bulkEnrollStudents($clearanceId, $ids);
        $count = count($ids);
        $this->setFlash('success', "{$count} graduating student(s) enrolled into this exit clearance.");
        $this->redirect("admin/clearances/detail?id={$clearanceId}#tab-stu");
    }

    public function uploadStudents(): void
    {
        $this->requireLogin('admin');
        if (!$this->validateCsrfToken()) { $this->redirect('admin/clearances'); return; }
        $clearanceId = (int) $this->getPost('clearance_id');

        $tab = $this->getPost('tab', 'tab-stu');
        $hash = $tab ? "#{$tab}" : '#tab-stu';

        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            $this->setFlash('error', 'Please select a valid CSV file.');
            $this->redirect("admin/clearances/detail?id={$clearanceId}{$hash}");
            return;
        }

        $file   = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, 'r');
        if (!$handle) {
            $this->setFlash('error', 'Could not read the uploaded file.');
            $this->redirect("admin/clearances/detail?id={$clearanceId}{$hash}");
            return;
        }

        $rows   = [];
        $header = null;
        while (($line = fgetcsv($handle)) !== false) {
            if ($header === null) {
                // Strip UTF-8 BOM from the first column header if present
                if (!empty($line[0])) {
                    $line[0] = preg_replace('/^\x{EF}\x{BB}\x{BF}/u', '', $line[0]);
                    $line[0] = str_replace("\xEF\xBB\xBF", '', $line[0]);
                }
                $header = array_map(fn($h) => strtolower(trim(preg_replace('/^\x{EF}\x{BB}\x{BF}/u', '', $h))), $line);
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
            $csvStudentIds = array_column($rows, 'student_id');
            $csvEmails     = array_column($rows, 'email');
            $unEnrolled    = $this->studentModel->findNotInClearance($clearanceId);
            
            $studentIdsToEnroll = [];
            foreach ($unEnrolled as $st) {
                if (in_array($st['student_id'], $csvStudentIds, true) || in_array($st['email'], $csvEmails, true)) {
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
            $this->redirect("admin/clearances/detail?id={$clearanceId}{$hash}");
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
        if (!$this->validateCsrfToken()) { $this->redirect('admin/enrollment-committees'); return; }

        $fullName   = Validator::sanitize((string)$this->getPost('full_name', ''));
        $email      = Validator::sanitize((string)$this->getPost('email', ''));
        $department = Validator::sanitize((string)$this->getPost('department', ''));
        $password   = (string)$this->getPost('password', '');

        if (empty($fullName) || empty($email) || empty($department) || empty($password)) {
            $this->setFlash('error', 'All fields are required.');
            $this->redirect('admin/enrollment-committees');
            return;
        }

        if ($this->validator->isEmailTakenAnyTable($email)) {
            $this->setFlash('error', 'This email is already used by another account in the system.');
            $this->redirect('admin/enrollment-committees');
            return;
        }

        $data = [
            'full_name'  => $fullName,
            'email'      => $email,
            'department' => $department,
            'password'   => $password,
        ];
        $this->enrollmentCommitteeModel->create($data);
        $this->setFlash('success', 'Enrollment Committee member added. Credentials: ' . $data['email'] . ' / ' . $data['password']);
        $this->redirect('admin/enrollment-committees');
    }

    public function editEnrollmentCommittee(): void
    {
        $this->requireLogin('admin');
        if (!$this->validateCsrfToken()) { $this->redirect('admin/enrollment-committees'); return; }
        $id = (int) $this->getPost('id');
        if ($id <= 0) {
            $this->setFlash('error', 'Invalid member ID.');
            $this->redirect('admin/enrollment-committees');
            return;
        }

        $fullName   = Validator::sanitize((string)$this->getPost('full_name', ''));
        $email      = Validator::sanitize((string)$this->getPost('email', ''));
        $department = Validator::sanitize((string)$this->getPost('department', ''));

        if (empty($fullName) || empty($email) || empty($department)) {
            $this->setFlash('error', 'All fields are required.');
            $this->redirect('admin/enrollment-committees');
            return;
        }

        if ($this->validator->isEmailTakenAnyTable($email, $id, 'enrollment_committees')) {
            $this->setFlash('error', 'This email is already used by another account in the system.');
            $this->redirect('admin/enrollment-committees');
            return;
        }

        $data = [
            'full_name'  => $fullName,
            'email'      => $email,
            'department' => $department,
        ];
        
        $this->enrollmentCommitteeModel->update($id, $data);
        $this->setFlash('success', 'Enrollment Committee member updated successfully.');
        $this->redirect('admin/enrollment-committees');
    }

    public function deleteEnrollmentCommittee(): void
    {
        $this->requireLogin('admin');
        if (!$this->validateCsrfToken()) { $this->redirect('admin/enrollment-committees'); return; }
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
            'colleges'    => $this->collegeModel->getAll(),
            'programs'    => $this->programModel->getAll(),
            'offices'     => $this->officeModel->getAll(),
            'courses'     => $this->studentModel->getDistinctCourses(),
            'flash'       => $this->getFlash(),
            'userName'    => $_SESSION['user_name'],
        ];
        $this->view('layouts/main', array_merge($data, ['content' => 'admin/signatories']));
    }

    public function addSignatory(): void
    {
        $this->requireLogin('admin');
        if (!$this->validateCsrfToken()) { $this->redirect('admin/signatories'); return; }
        
        $fullName   = Validator::sanitize((string)$this->getPost('full_name', ''));
        $email      = Validator::sanitize((string)$this->getPost('email', ''));
        $office     = Validator::sanitize((string)$this->getPost('office', ''));
        $password   = (string)$this->getPost('password', '');

        $scopeType = $this->getPost('scope_type', '');
        $scopeValue = null;
        if ($scopeType === 'college') {
            $scopeValue = Validator::sanitize((string)$this->getPost('scope_college', ''));
        } elseif ($scopeType === 'course') {
            $scopeValue = Validator::sanitize((string)$this->getPost('scope_course', ''));
        } else {
            $scopeType = null;
        }

        if (empty($fullName) || empty($email) || empty($office) || empty($password)) {
            $this->setFlash('error', 'All fields are required.');
            $this->redirect('admin/signatories');
            return;
        }

        // A8: 1:1 Signatory enforcement
        if ($this->validator->isEmailTakenAnyTable($email)) {
            $this->setFlash('error', 'This email is already used by another account in the system.');
            $this->redirect('admin/signatories');
            return;
        }

        $stmt = Database::getInstance()->prepare("SELECT COUNT(*) FROM signatories WHERE full_name = ? AND office = ?");
        $stmt->execute([$fullName, $office]);
        if ((int)$stmt->fetchColumn() > 0) {
            $this->setFlash('error', 'A signatory with this name and office already exists.');
            $this->redirect('admin/signatories');
            return;
        }

        $data = [
            'full_name'   => $fullName,
            'email'       => $email,
            'office'      => $office,
            'password'    => $password,
            'scope_type'  => $scopeType,
            'scope_value' => $scopeValue,
        ];

        $this->signatoryModel->create($data);
        $this->setFlash('success', 'Signatory added. Credentials: ' . $data['email'] . ' / ' . $data['password']);
        $this->redirect('admin/signatories');
    }

    public function editSignatory(): void
    {
        $this->requireLogin('admin');
        if (!$this->validateCsrfToken()) { $this->redirect('admin/signatories'); return; }
        $id = (int) $this->getPost('id');
        if ($id <= 0) {
            $this->setFlash('error', 'Invalid signatory ID.');
            $this->redirect('admin/signatories');
            return;
        }

        $fullName   = Validator::sanitize((string)$this->getPost('full_name', ''));
        $email      = Validator::sanitize((string)$this->getPost('email', ''));
        $office     = Validator::sanitize((string)$this->getPost('office', ''));

        $scopeType = $this->getPost('scope_type', '');
        $scopeValue = null;
        if ($scopeType === 'college') {
            $scopeValue = Validator::sanitize((string)$this->getPost('scope_college', ''));
        } elseif ($scopeType === 'course') {
            $scopeValue = Validator::sanitize((string)$this->getPost('scope_course', ''));
        } else {
            $scopeType = null;
        }

        if (empty($fullName) || empty($email) || empty($office)) {
            $this->setFlash('error', 'All fields are required.');
            $this->redirect('admin/signatories');
            return;
        }

        // A8: 1:1 Signatory enforcement
        if ($this->validator->isEmailTakenAnyTable($email, $id, 'signatories')) {
            $this->setFlash('error', 'This email is already used by another account in the system.');
            $this->redirect('admin/signatories');
            return;
        }

        $stmt = Database::getInstance()->prepare("SELECT COUNT(*) FROM signatories WHERE full_name = ? AND office = ? AND id != ?");
        $stmt->execute([$fullName, $office, $id]);
        if ((int)$stmt->fetchColumn() > 0) {
            $this->setFlash('error', 'A signatory with this name and office already exists.');
            $this->redirect('admin/signatories');
            return;
        }

        $data = [
            'full_name'   => $fullName,
            'email'       => $email,
            'office'      => $office,
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
        if (!$this->validateCsrfToken()) { $this->redirect('admin/signatories'); return; }
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
    //  DEPARTMENTS, PROGRAMS & OFFICES (A3)
    // ================================================================

    public function departments(): void
    {
        $this->requireLogin('admin');
        $data = [
            'colleges' => $this->collegeModel->getAll(),
            'programs' => $this->programModel->getAll(),
            'offices'  => $this->officeModel->getAll(),
            'flash'    => $this->getFlash(),
            'userName' => $_SESSION['user_name'],
        ];
        $this->view('layouts/main', array_merge($data, ['content' => 'admin/departments_programs']));
    }

    public function addCollege(): void
    {
        $this->requireLogin('admin');
        if (!$this->validateCsrfToken()) { $this->redirect('admin/departments'); return; }
        $name = strtoupper(trim((string)$this->getPost('name', '')));
        if ($name === '') {
            $this->setFlash('error', 'College name/code is required.');
        } else {
            try {
                $this->collegeModel->create($name);
                $this->setFlash('success', 'College added successfully.');
            } catch (\Exception $e) {
                $this->setFlash('error', 'Error adding college: it may already exist.');
            }
        }
        $this->redirect('admin/departments');
    }

    public function deleteCollege(): void
    {
        $this->requireLogin('admin');
        if (!$this->validateCsrfToken()) { $this->redirect('admin/departments'); return; }
        $id = (int) $this->getPost('id', 0);
        if ($id > 0) {
            $this->collegeModel->delete($id);
            $this->setFlash('success', 'College deleted.');
        }
        $this->redirect('admin/departments');
    }

    public function addProgram(): void
    {
        $this->requireLogin('admin');
        if (!$this->validateCsrfToken()) { $this->redirect('admin/departments'); return; }
        $collegeId = (int) $this->getPost('college_id', 0);
        $name      = trim((string)$this->getPost('name', ''));
        $code      = strtoupper(trim((string)$this->getPost('code', '')));

        if ($collegeId <= 0 || empty($name) || empty($code)) {
            $this->setFlash('error', 'All program fields are required.');
        } else {
            try {
                $this->programModel->create(['college_id' => $collegeId, 'name' => $name, 'code' => $code]);
                $this->setFlash('success', 'Program added successfully.');
            } catch (\Exception $e) {
                $this->setFlash('error', 'Error adding program: code may already exist for this college.');
            }
        }
        $this->redirect('admin/departments');
    }

    public function editProgram(): void
    {
        $this->requireLogin('admin');
        if (!$this->validateCsrfToken()) { $this->redirect('admin/departments'); return; }
        $id        = (int) $this->getPost('id', 0);
        $collegeId = (int) $this->getPost('college_id', 0);
        $name      = trim((string)$this->getPost('name', ''));
        $code      = strtoupper(trim((string)$this->getPost('code', '')));

        if ($id <= 0 || $collegeId <= 0 || empty($name) || empty($code)) {
            $this->setFlash('error', 'All program fields are required.');
        } else {
            $this->programModel->update($id, ['college_id' => $collegeId, 'name' => $name, 'code' => $code]);
            $this->setFlash('success', 'Program updated successfully.');
        }
        $this->redirect('admin/departments');
    }

    public function deleteProgram(): void
    {
        $this->requireLogin('admin');
        if (!$this->validateCsrfToken()) { $this->redirect('admin/departments'); return; }
        $id = (int) $this->getPost('id', 0);
        if ($id > 0) {
            $this->programModel->delete($id);
            $this->setFlash('success', 'Program deleted.');
        }
        $this->redirect('admin/departments');
    }

    public function addOffice(): void
    {
        $this->requireLogin('admin');
        if (!$this->validateCsrfToken()) { $this->redirect('admin/departments'); return; }
        $name = trim((string)$this->getPost('name', ''));
        if ($name === '') {
            $this->setFlash('error', 'Office name is required.');
        } else {
            try {
                $this->officeModel->create($name);
                $this->setFlash('success', 'Office added successfully.');
            } catch (\Exception $e) {
                $this->setFlash('error', 'Error adding office: it may already exist.');
            }
        }
        $this->redirect('admin/departments');
    }

    public function editOffice(): void
    {
        $this->requireLogin('admin');
        if (!$this->validateCsrfToken()) { $this->redirect('admin/departments'); return; }
        $id   = (int) $this->getPost('id', 0);
        $name = trim((string)$this->getPost('name', ''));
        if ($id <= 0 || $name === '') {
            $this->setFlash('error', 'Office name is required.');
        } else {
            $this->officeModel->update($id, $name);
            $this->setFlash('success', 'Office updated.');
        }
        $this->redirect('admin/departments');
    }

    public function deleteOffice(): void
    {
        $this->requireLogin('admin');
        if (!$this->validateCsrfToken()) { $this->redirect('admin/departments'); return; }
        $id = (int) $this->getPost('id', 0);
        if ($id > 0) {
            $this->officeModel->delete($id);
            $this->setFlash('success', 'Office deleted.');
        }
        $this->redirect('admin/departments');
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
        if (!$this->validateCsrfToken()) { $this->redirect('admin/clearances'); return; }
        $data = [
            'name'        => trim($this->getPost('name', '')),
            'description' => trim($this->getPost('description', '')),
            'school_year' => trim($this->getPost('school_year', '')),
            'type'        => $this->getPost('type', 'regular') === 'exit' ? 'exit' : 'regular',
        ];
        if (empty($data['name']) || empty($data['school_year'])) {
            $this->setFlash('error', 'Clearance name and academic year are required.');
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
        if (!$this->validateCsrfToken()) { $this->redirect('admin/clearances'); return; }
        $id = (int) $this->getPost('id');
        $clearance = $this->clearanceModel->findById($id);
        if (!$clearance) {
            $this->setFlash('error', 'Clearance not found.');
            $this->redirect('admin/clearances');
            return;
        }
        $data = [
            'name'        => trim($this->getPost('name', '')),
            'description' => trim($this->getPost('description', '')),
            'school_year' => trim($this->getPost('school_year', '')),
            'type'        => $this->getPost('type', 'regular') === 'exit' ? 'exit' : 'regular',
        ];
        if (empty($data['name']) || empty($data['school_year'])) {
            $this->setFlash('error', 'Clearance name and academic year are required.');
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
        $redirectTo = $this->getPost('redirect_to', '');
        if (empty($redirectTo)) {
            $referer = $_SERVER['HTTP_REFERER'] ?? '';
            $redirectTo = (strpos($referer, 'archived-clearances') !== false) ? 'admin/archived-clearances' : 'admin/clearances';
        }
        if (!$this->validateCsrfToken()) { $this->redirect($redirectTo); return; }
        $id = (int) $this->getPost('id');
        $this->clearanceModel->delete($id);
        $this->setFlash('success', 'Clearance deleted.');
        $this->redirect($redirectTo);
    }

    public function archiveClearance(): void
    {
        $this->requireLogin('admin');
        if (!$this->validateCsrfToken()) { $this->redirect('admin/clearances'); return; }
        $id = (int) $this->getPost('id');
        $this->clearanceModel->archive($id);
        $this->setFlash('success', 'Clearance archived. You can restore it from Archived Clearances.');
        $this->redirect('admin/clearances');
    }

    public function unarchiveClearance(): void
    {
        $this->requireLogin('admin');
        if (!$this->validateCsrfToken()) { $this->redirect('admin/archived-clearances'); return; }
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

    public function startClearance(): void
    {
        $this->requireLogin('admin');
        if (!$this->validateCsrfToken()) { $this->redirect('admin/clearances'); return; }
        $id        = (int) $this->getPost('clearance_id');
        $clearance = $this->clearanceModel->findById($id);
        $back      = "admin/clearances/detail?id={$id}";

        if (!$clearance) {
            $this->setFlash('error', 'Clearance not found.');
            $this->redirect('admin/clearances');
            return;
        }

        $students = $this->clearanceModel->getEnrolledStudentsForEmail($id);
        if (empty($students)) {
            $this->setFlash('error', 'Enroll students first before starting the clearance.');
            $this->redirect($back);
            return;
        }

        // Atomic: only the first call succeeds, so emails are never queued twice.
        if (!$this->clearanceModel->markStarted($id)) {
            $this->setFlash('info', 'This clearance has already been started.');
            $this->redirect($back);
            return;
        }

        if (!isset($_SESSION['bg_emails'])) {
            $_SESSION['bg_emails'] = [];
        }
        $_SESSION['bg_emails'][] = ['type' => 'started', 'students' => $students];

        $this->setFlash('success', 'Clearance started. Emails to ' . count($students) . ' student(s) are sending in the background.');
        $this->redirect($back);
    }

    /** JSON: log history of one student in a clearance. */
    public function studentLogs(): void
    {
        $this->requireLogin('admin');
        $cid = (int) $this->getGet('clearance_id');
        $sid = (int) $this->getGet('student_id');
        header('Content-Type: application/json');
        if (!$cid || !$sid) {
            echo json_encode(['logs' => []]);
            return;
        }
        echo json_encode(['logs' => (new ClearanceStatus())->getLogsForStudent($cid, $sid)]);
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
            'clearance'                      => $clearance,
            'cid'                            => $id,
            'assignedSignatories'            => $this->clearanceModel->getSignatories($id),
            'unassignedSignatories'          => $this->signatoryModel->findUnassigned($id),
            'assignedEnrollmentCommittees'   => $this->clearanceModel->getEnrollmentCommittees($id),
            'unassignedEnrollmentCommittees' => $this->enrollmentCommitteeModel->findUnassigned($id),
            'students'                       => $this->clearanceModel->getStudentsWithStatus($id),
            'flash'                          => $this->getFlash(),
            'userName'                       => $_SESSION['user_name'],
        ];
        $this->view('layouts/main', array_merge($data, ['content' => 'admin/clearance_detail']));
    }

    // ---- Signatory assignment ----

    public function assignSignatory(): void
    {
        $this->requireLogin('admin');
        if (!$this->validateCsrfToken()) { $this->redirect('admin/clearances'); return; }
        $clearanceId  = (int) $this->getPost('clearance_id');
        $signatoryVal = $this->getPost('signatory_id');
        $tab = $this->getPost('tab', 'tab-sig');
        $hash = $tab ? "#{$tab}" : '#tab-sig';

        if ($signatoryVal === 'all') {
            $unassigned = $this->signatoryModel->findUnassigned($clearanceId);
            foreach ($unassigned as $s) {
                $this->clearanceModel->assignSignatory($clearanceId, (int) $s['id']);
            }
            $count = count($unassigned);
            $this->setFlash('success', "All unassigned signatories ({$count}) assigned to clearance.");
        } else {
            $signatoryId = (int) $signatoryVal;
            if ($signatoryId > 0) {
                $this->clearanceModel->assignSignatory($clearanceId, $signatoryId);
                $this->setFlash('success', 'Signatory assigned to clearance.');
            }
        }
        $tab = $this->getPost('tab', 'tab-sig');
        $hash = $tab ? "#{$tab}" : '#tab-sig';
        $this->redirect("admin/clearances/detail?id={$clearanceId}{$hash}");
    }

    public function bulkAssignSignatories(): void
    {
        $this->requireLogin('admin');
        if (!$this->validateCsrfToken()) { $this->redirect('admin/clearances'); return; }
        $clearanceId  = (int) $this->getPost('clearance_id');
        $signatoryIds = $this->getPost('signatory_ids', []);
        $tab = $this->getPost('tab', 'tab-sig');
        $hash = $tab ? "#{$tab}" : '#tab-sig';

        if (!empty($signatoryIds)) {
            foreach ((array) $signatoryIds as $sid) {
                $this->clearanceModel->assignSignatory($clearanceId, (int) $sid);
            }
            $count = count($signatoryIds);
            $this->setFlash('success', "{$count} signator" . ($count === 1 ? 'y' : 'ies') . " assigned.");
        } else {
            $this->setFlash('error', 'No signatories selected.');
        }
        $tab = $this->getPost('tab', 'tab-sig');
        $hash = $tab ? "#{$tab}" : '#tab-sig';
        $this->redirect("admin/clearances/detail?id={$clearanceId}{$hash}");
    }

    public function removeSignatory(): void
    {
        $this->requireLogin('admin');
        if (!$this->validateCsrfToken()) { $this->redirect('admin/clearances'); return; }
        $clearanceId = (int) $this->getPost('clearance_id');
        $signatoryId = (int) $this->getPost('signatory_id');
        $tab = $this->getPost('tab', 'tab-sig');
        $hash = $tab ? "#{$tab}" : '#tab-sig';

        $this->clearanceModel->removeSignatory($clearanceId, $signatoryId);
        Database::getInstance()->prepare("DELETE FROM clearance_status WHERE clearance_id = ? AND signatory_id = ?")->execute([$clearanceId, $signatoryId]);

        $this->setFlash('success', 'Signatory removed from clearance.');
        $tab = $this->getPost('tab', 'tab-sig');
        $hash = $tab ? "#{$tab}" : '#tab-sig';
        $this->redirect("admin/clearances/detail?id={$clearanceId}{$hash}");
    }

    // ---- Enrollment Committee assignment ----

    public function assignEnrollmentCommittee(): void
    {
        $this->requireLogin('admin');
        if (!$this->validateCsrfToken()) { $this->redirect('admin/clearances'); return; }
        $clearanceId  = (int) $this->getPost('clearance_id');
        $committeeVal = $this->getPost('enrollment_committee_id');
        $tab = $this->getPost('tab', 'tab-adv');
        $hash = $tab ? "#{$tab}" : '#tab-adv';

        if ($committeeVal === 'all') {
            $unassigned = $this->enrollmentCommitteeModel->findUnassigned($clearanceId);
            foreach ($unassigned as $c) {
                $this->clearanceModel->assignEnrollmentCommittee($clearanceId, (int) $c['id']);
            }
            $count = count($unassigned);
            $this->setFlash('success', "All unassigned enrollment committee members ({$count}) assigned to clearance.");
        } else {
            $enrollmentCommitteeId = (int) $committeeVal;
            if ($enrollmentCommitteeId > 0) {
                $this->clearanceModel->assignEnrollmentCommittee($clearanceId, $enrollmentCommitteeId);
                $this->setFlash('success', 'Enrollment Committee member assigned to clearance.');
            }
        }
        $tab = $this->getPost('tab', 'tab-adv');
        $hash = $tab ? "#{$tab}" : '#tab-adv';
        $this->redirect("admin/clearances/detail?id={$clearanceId}{$hash}");
    }

    public function bulkAssignEnrollmentCommittees(): void
    {
        $this->requireLogin('admin');
        if (!$this->validateCsrfToken()) { $this->redirect('admin/clearances'); return; }
        $clearanceId = (int) $this->getPost('clearance_id');
        $enrollmentCommitteeIds = $this->getPost('enrollment_committee_ids', []);
        $tab = $this->getPost('tab', 'tab-adv');
        $hash = $tab ? "#{$tab}" : '#tab-adv';

        if (!empty($enrollmentCommitteeIds)) {
            foreach ((array) $enrollmentCommitteeIds as $aid) {
                $this->clearanceModel->assignEnrollmentCommittee($clearanceId, (int) $aid);
            }
            $count = count($enrollmentCommitteeIds);
            $this->setFlash('success', "{$count} enrollment committee member" . ($count === 1 ? '' : 's') . " assigned.");
        } else {
            $this->setFlash('success', 'Clearance setup complete. No enrollment committee assigned.');
        }
        $tab = $this->getPost('tab', 'tab-adv');
        $hash = $tab ? "#{$tab}" : '#tab-adv';
        $this->redirect("admin/clearances/detail?id={$clearanceId}{$hash}");
    }

    public function removeEnrollmentCommittee(): void
    {
        $this->requireLogin('admin');
        if (!$this->validateCsrfToken()) { $this->redirect('admin/clearances'); return; }
        $clearanceId = (int) $this->getPost('clearance_id');
        $enrollmentCommitteeId = (int) $this->getPost('enrollment_committee_id');
        $tab = $this->getPost('tab', 'tab-adv');
        $hash = $tab ? "#{$tab}" : '#tab-adv';

        $this->clearanceModel->removeEnrollmentCommittee($clearanceId, $enrollmentCommitteeId);
        $this->setFlash('success', 'Enrollment Committee member removed from clearance.');
        $tab = $this->getPost('tab', 'tab-adv');
        $hash = $tab ? "#{$tab}" : '#tab-adv';
        $this->redirect("admin/clearances/detail?id={$clearanceId}{$hash}");
    }

    // ---- Student removal from clearance ----

    public function removeStudentFromClearance(): void
    {
        $this->requireLogin('admin');
        if (!$this->validateCsrfToken()) { $this->redirect('admin/clearances'); return; }
        $clearanceId = (int) $this->getPost('clearance_id');
        $studentId   = (int) $this->getPost('student_id');
        $tab = $this->getPost('tab', 'tab-stu');
        $hash = $tab ? "#{$tab}" : '#tab-stu';

        $this->clearanceModel->removeStudent($clearanceId, $studentId);
        Database::getInstance()->prepare("DELETE FROM clearance_status WHERE clearance_id = ? AND student_id = ?")->execute([$clearanceId, $studentId]);

        $this->setFlash('success', 'Student removed from clearance.');
        $this->redirect("admin/clearances/detail?id={$clearanceId}{$hash}");
    }

    /**
     * Check if a clearance has already been started.
     */
    private function isClearanceStarted(int $clearanceId): bool
    {
        $clearance = $this->clearanceModel->findById($clearanceId);
        return $clearance && !empty($clearance['started_at']);
    }
}

