<?php
require_once ROOT_PATH . '/app/Models/ClearanceStatus.php';

class AdviserController extends Controller
{
    private ClearanceStatus $statusModel;

    public function __construct()
    {
        $this->statusModel = new ClearanceStatus();
    }

    public function dashboard(): void
    {
        $this->requireLogin('adviser');
        $rows = $this->statusModel->getClearancesForAdviser($_SESSION['user_id']);
        // Group rows by clearance
        $clearances = [];
        foreach ($rows as $r) {
            $cid = $r['clearance_id'];
            if (!isset($clearances[$cid])) {
                $clearances[$cid] = [
                    'clearance_id'   => $cid,
                    'clearance_name' => $r['clearance_name'],
                    'school_year'    => $r['school_year'],
                    'students'       => [],
                ];
            }
            $clearances[$cid]['students'][] = $r;
        }
        $data = [
            'clearances' => array_values($clearances),
            'flash'      => $this->getFlash(),
            'userName'   => $_SESSION['user_name'],
        ];
        $this->view('layouts/main', array_merge($data, ['content' => 'adviser/dashboard']));
    }

    public function clearances(): void
    {
        $this->requireLogin('adviser');
        $rows = $this->statusModel->getClearancesForAdviser($_SESSION['user_id']);
        $clearances = [];
        foreach ($rows as $r) {
            $cid = $r['clearance_id'];
            if (!isset($clearances[$cid])) {
                $clearances[$cid] = [
                    'clearance_id'   => $cid,
                    'clearance_name' => $r['clearance_name'],
                    'school_year'    => $r['school_year'],
                    'students'       => [],
                ];
            }
            $clearances[$cid]['students'][] = $r;
        }
        $data = [
            'clearances' => array_values($clearances),
            'flash'      => $this->getFlash(),
            'userName'   => $_SESSION['user_name'],
        ];
        $this->view('layouts/main', array_merge($data, ['content' => 'adviser/clearances']));
    }
}
