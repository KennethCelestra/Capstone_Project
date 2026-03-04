<?php
require_once ROOT_PATH . '/app/Models/ClearanceStatus.php';

class SignatoryController extends Controller
{

    private ClearanceStatus $statusModel;

    public function __construct()
    {
        $this->statusModel = new ClearanceStatus();
    }

    public function dashboard(): void
    {
        $this->requireLogin('signatory');
        $data = [
            'clearances' => $this->statusModel->getPendingBySignatory($_SESSION['user_id']),
            'flash' => $this->getFlash(),
            'userName' => $_SESSION['user_name'],
        ];
        $this->view('layouts/main', array_merge($data, ['content' => 'signatory/dashboard']));
    }

    public function clearances(): void
    {
        $this->requireLogin('signatory');
        $data = [
            'clearances' => $this->statusModel->getPendingBySignatory($_SESSION['user_id']),
            'flash' => $this->getFlash(),
            'userName' => $_SESSION['user_name'],
        ];
        $this->view('layouts/main', array_merge($data, ['content' => 'signatory/clearances']));
    }

    public function signClearance(): void
    {
        $this->requireLogin('signatory');
        $studentId = (int) $this->getPost('student_id');
        $this->statusModel->sign($studentId, $_SESSION['user_id']);
        $this->setFlash('success', 'Clearance signed successfully.');
        $this->redirect('signatory/clearances');
    }
}
