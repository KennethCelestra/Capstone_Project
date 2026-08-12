<?php
require_once ROOT_PATH . '/app/Models/ClearanceStatus.php';

class ClearanceFormController extends Controller
{
    private ClearanceStatus $statusModel;

    public function __construct()
    {
        $this->statusModel = new ClearanceStatus();
    }

    // ----------------------------------------------------------------
    // Public printable clearance form
    // GET /clearance/form?cid=N&sid=N&token=HASH
    // ----------------------------------------------------------------

    public function show(): void
    {
        $cid   = (int) ($_GET['cid']   ?? 0);
        $sid   = (int) ($_GET['sid']   ?? 0);
        $token = trim($_GET['token']   ?? '');

        // 1. Basic param validation
        if ($cid <= 0 || $sid <= 0 || $token === '') {
            $this->renderError(400, 'Invalid clearance form link. Please use the link provided in your email.');
            return;
        }

        // 2. HMAC token verification (constant-time compare)
        $expected = hash_hmac('sha256', "cid={$cid}&sid={$sid}", APP_SECRET);
        if (!hash_equals($expected, $token)) {
            $this->renderError(403, 'This link is invalid or has been tampered with. Please use the original link from your email.');
            return;
        }

        // 3. Fetch all form data (student, clearance, signatories) — single query
        $data = $this->statusModel->getFormData($cid, $sid);
        if (!$data) {
            $this->renderError(404, 'Student or clearance record not found.');
            return;
        }

        // 4. Guard: student must be fully cleared by all signatories
        foreach ($data['signatories'] as $sig) {
            if ($sig['status'] !== 'cleared') {
                $this->renderError(403, 'Your clearance is not yet complete. The form will be available once all offices have signed off.');
                return;
            }
        }

        // 5. Derive semester from clearance name
        $clearanceName = strtolower($data['clearance']['name']);
        $semester      = '___';
        if (str_contains($clearanceName, 'summer')) {
            $semester = 'Summer';
        } elseif (str_contains($clearanceName, '2nd') || str_contains($clearanceName, 'second')) {
            $semester = '2nd';
        } elseif (str_contains($clearanceName, '1st') || str_contains($clearanceName, 'first')) {
            $semester = '1st';
        }

        // 6. Derive year level label for Curriculum Year & Section
        $yearLabels = [1 => '1st Year', 2 => '2nd Year', 3 => '3rd Year', 4 => '4th Year', 5 => '5th Year'];
        $yearLabel  = $yearLabels[(int)$data['student']['year_level']] ?? $data['student']['year_level'] . 'th Year';

        // 7. Render standalone printable form (no layout)
        $student     = $data['student'];
        $clearance   = $data['clearance'];
        $signatories = $data['signatories'];

        require_once ROOT_PATH . '/app/Views/clearance_form.php';
    }

    // ----------------------------------------------------------------
    // Helper: render a styled error page
    // ----------------------------------------------------------------

    private function renderError(int $httpCode, string $message): void
    {
        http_response_code($httpCode);
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Clearance Form — Error</title>
            <style>
                * { box-sizing: border-box; margin: 0; padding: 0; }
                body {
                    font-family: 'Segoe UI', Arial, sans-serif;
                    background: #f4f6f9;
                    display: flex; align-items: center; justify-content: center;
                    min-height: 100vh; padding: 20px;
                }
                .card {
                    background: #fff;
                    border-radius: 10px;
                    padding: 48px 40px;
                    max-width: 520px;
                    width: 100%;
                    text-align: center;
                    box-shadow: 0 4px 24px rgba(0,0,0,.10);
                }
                .icon { font-size: 52px; margin-bottom: 16px; }
                h2 { color: #c0392b; font-size: 1.4rem; margin-bottom: 14px; }
                p  { color: #555; font-size: 0.97rem; line-height: 1.6; }
            </style>
        </head>
        <body>
            <div class="card">
                <div class="icon">&#9888;&#65039;</div>
                <h2>Cannot Display Clearance Form</h2>
                <p><?= htmlspecialchars($message) ?></p>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}
