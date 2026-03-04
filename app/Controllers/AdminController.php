<?php
require_once ROOT_PATH . '/app/Models/Student.php';
require_once ROOT_PATH . '/app/Models/Adviser.php';
require_once ROOT_PATH . '/app/Models/Signatory.php';
require_once ROOT_PATH . '/app/Models/Clearance.php';

class AdminController extends Controller
{

    private Student $studentModel;
    private Adviser $adviserModel;
    private Signatory $signatoryModel;
    private Clearance $clearanceModel;

    public function __construct()
    {
        $this->studentModel = new Student();
        $this->adviserModel = new Adviser();
        $this->signatoryModel = new Signatory();
        $this->clearanceModel = new Clearance();
    }

    public function dashboard(): void
    {
        $this->requireLogin('admin');
        $data = [
            'studentCount' => $this->studentModel->count(),
            'adviserCount' => $this->adviserModel->count(),
            'signatoryCount' => $this->signatoryModel->count(),
            'flash' => $this->getFlash(),
            'userName' => $_SESSION['user_name'],
        ];
        $this->view('layouts/main', array_merge($data, ['content' => 'admin/dashboard']));
    }

    // ---- STUDENTS ----

    public function students(): void
    {
        $this->requireLogin('admin');
        $data = [
            'students' => $this->studentModel->findAllWithAdviser(),
            'advisers' => $this->adviserModel->findAll(),
            'flash' => $this->getFlash(),
            'userName' => $_SESSION['user_name'],
        ];
        $this->view('layouts/main', array_merge($data, ['content' => 'admin/students']));
    }

    public function addStudent(): void
    {
        $this->requireLogin('admin');
        $data = [
            'student_id' => $this->getPost('student_id'),
            'full_name' => $this->getPost('full_name'),
            'email' => $this->getPost('email'),
            'course' => $this->getPost('course'),
            'year_level' => $this->getPost('year_level'),
            'section' => $this->getPost('section'),
            'adviser_id' => $this->getPost('adviser_id'),
            'password' => $this->getPost('password'),
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

    // ---- ADVISERS ----

    public function advisers(): void
    {
        $this->requireLogin('admin');
        $data = [
            'advisers' => $this->adviserModel->findAll(),
            'flash' => $this->getFlash(),
            'userName' => $_SESSION['user_name'],
        ];
        $this->view('layouts/main', array_merge($data, ['content' => 'admin/advisers']));
    }

    public function addAdviser(): void
    {
        $this->requireLogin('admin');
        $data = [
            'full_name' => $this->getPost('full_name'),
            'email' => $this->getPost('email'),
            'department' => $this->getPost('department'),
            'password' => $this->getPost('password'),
        ];
        $this->adviserModel->create($data);
        $this->setFlash('success', 'Adviser added successfully.');
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

    // ---- SIGNATORIES ----

    public function signatories(): void
    {
        $this->requireLogin('admin');
        $data = [
            'signatories' => $this->signatoryModel->findAll(),
            'flash' => $this->getFlash(),
            'userName' => $_SESSION['user_name'],
        ];
        $this->view('layouts/main', array_merge($data, ['content' => 'admin/signatories']));
    }

    public function addSignatory(): void
    {
        $this->requireLogin('admin');
        $data = [
            'full_name' => $this->getPost('full_name'),
            'email' => $this->getPost('email'),
            'office' => $this->getPost('office'),
            'password' => $this->getPost('password'),
        ];
        $this->signatoryModel->create($data);
        $this->setFlash('success', 'Signatory added successfully.');
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

    // ---- CLEARANCES ----

    public function clearances(): void
    {
        $this->requireLogin('admin');
        $data = [
            'clearances' => $this->clearanceModel->getOverview(),
            'flash' => $this->getFlash(),
            'userName' => $_SESSION['user_name'],
        ];
        $this->view('layouts/main', array_merge($data, ['content' => 'admin/clearances']));
    }
}
