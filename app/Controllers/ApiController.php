<?php
require_once ROOT_PATH . '/core/Model.php';
require_once ROOT_PATH . '/app/Models/College.php';
require_once ROOT_PATH . '/app/Models/Program.php';
require_once ROOT_PATH . '/app/Models/Office.php';

class ApiController extends Controller
{
    public function programs(): void
    {
        header('Content-Type: application/json');
        $collegeName = trim((string)$this->getGet('college', ''));
        $programModel = new Program();
        if ($collegeName !== '') {
            echo json_encode($programModel->getByCollegeName($collegeName));
        } else {
            echo json_encode($programModel->getAll());
        }
        exit;
    }

    public function offices(): void
    {
        header('Content-Type: application/json');
        $officeModel = new Office();
        echo json_encode($officeModel->getAll());
        exit;
    }

    public function colleges(): void
    {
        header('Content-Type: application/json');
        $collegeModel = new College();
        echo json_encode($collegeModel->getAll());
        exit;
    }
}
