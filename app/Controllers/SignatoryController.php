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
            'clearances' => $this->statusModel->getClearancesForSignatory($_SESSION['user_id']),
            'flash'      => $this->getFlash(),
            'userName'   => $_SESSION['user_name'],
        ];
        $this->view('layouts/main', array_merge($data, ['content' => 'signatory/dashboard']));
    }

    public function clearances(): void
    {
        $this->requireLogin('signatory');
        $signatoryId   = (int) $_SESSION['user_id'];
        $clearanceSummaries = $this->statusModel->getClearancesForSignatory($signatoryId);

        // Attach student list to each clearance
        $clearances = [];
        foreach ($clearanceSummaries as $c) {
            $cid = (int) $c['clearance_id'];
            $c['students'] = $this->statusModel->getStudentsForSignatory($cid, $signatoryId);
            $clearances[]  = $c;
        }

        $data = [
            'clearances' => $clearances,
            'flash'      => $this->getFlash(),
            'userName'   => $_SESSION['user_name'],
        ];
        $this->view('layouts/main', array_merge($data, ['content' => 'signatory/clearances']));
    }

    public function signClearance(): void
    {
        $this->requireLogin('signatory');
        $clearanceId = (int) $this->getPost('clearance_id');
        $studentId   = (int) $this->getPost('student_id');
        $this->statusModel->sign($clearanceId, $studentId, $_SESSION['user_id']);
        $this->setFlash('success', 'Clearance signed successfully.');
        $this->redirect('signatory/clearances');
    }
}
