<?php
require_once ROOT_PATH . '/app/Models/Student.php';
require_once ROOT_PATH . '/app/Models/ClearanceStatus.php';

class AdviserController extends Controller
{

    private ClearanceStatus $statusModel;
    private Student $studentModel;

    public function __construct()
    {
        $this->statusModel = new ClearanceStatus();
        $this->studentModel = new Student();
    }

    public function dashboard(): void
    {
        $this->requireLogin('adviser');
        $data = [
            'clearances' => $this->statusModel->getByAdviser($_SESSION['user_id']),
            'flash' => $this->getFlash(),
            'userName' => $_SESSION['user_name'],
        ];
        $this->view('layouts/main', array_merge($data, ['content' => 'adviser/dashboard']));
    }

    public function clearances(): void
    {
        $this->requireLogin('adviser');
        $data = [
            'clearances' => $this->statusModel->getByAdviser($_SESSION['user_id']),
            'flash' => $this->getFlash(),
            'userName' => $_SESSION['user_name'],
        ];
        $this->view('layouts/main', array_merge($data, ['content' => 'adviser/clearances']));
    }

    public function markClearance(): void
    {
        $this->requireLogin('adviser');
        $studentId = (int) $this->getPost('student_id');
        $this->statusModel->markCleared($studentId, $_SESSION['user_id']);
        $this->setFlash('success', 'Clearance marked.');
        $this->redirect('adviser/clearances');
    }
}
