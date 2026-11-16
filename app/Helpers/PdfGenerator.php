<?php
require_once ROOT_PATH . '/vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

class PdfGenerator
{
    public static function generateClearanceFormPdf(int $clearanceId, int $studentId): string|false
    {
        require_once ROOT_PATH . '/app/Models/ClearanceStatus.php';
        $statusModel = new ClearanceStatus();
        $data = $statusModel->getFormData($clearanceId, $studentId);
        if (!$data) return false;

        $student     = $data['student'];
        $clearance   = $data['clearance'];
        $signatories = $data['signatories'];

        usort($signatories, function($a, $b) {
            $order = [
                'Library' => 1,
                'Cashier' => 2,
                'Guidance Office' => 3,
                'guidance' => 3,
                'Department Head' => 4,
                'Department Office' => 4,
                'Dean' => 5,
                'Dean Office' => 5,
                'Director of Student Affairs' => 6,
                'Registrar' => 7
            ];
            $oa = $order[$a['office']] ?? 50;
            $ob = $order[$b['office']] ?? 50;
            return $oa === $ob ? strcmp($a['office'], $b['office']) : ($oa <=> $ob);
        });

        $clearanceName = strtolower($clearance['name']);
        $semester      = '___';
        if (str_contains($clearanceName, 'summer')) {
            $semester = 'Summer';
        } elseif (str_contains($clearanceName, '2nd') || str_contains($clearanceName, 'second')) {
            $semester = '2nd';
        } elseif (str_contains($clearanceName, '1st') || str_contains($clearanceName, 'first')) {
            $semester = '1st';
        }

        $yearLabels = [1 => '1st Year', 2 => '2nd Year', 3 => '3rd Year', 4 => '4th Year', 5 => '5th Year'];
        $yearLabel  = $yearLabels[(int)$student['year_level']] ?? $student['year_level'] . 'th Year';

        $isPdfExport = true;
        ob_start();
        if (($clearance['type'] ?? 'regular') === 'exit') {
            include ROOT_PATH . '/app/Views/exit_clearance_form.php';
        } else {
            include ROOT_PATH . '/app/Views/clearance_form.php';
        }
        $html = ob_get_clean();

        $dir = ROOT_PATH . '/storage/clearances/' . $clearanceId;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $filepath = $dir . '/' . $studentId . '.pdf';

        try {
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $options->set('defaultFont', 'Helvetica');

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('letter', 'portrait');
            $dompdf->render();

            file_put_contents($filepath, $dompdf->output());
            return $filepath;
        } catch (\Throwable $e) {
            error_log("PDF generation error: " . $e->getMessage());
            return false;
        }
    }
}
