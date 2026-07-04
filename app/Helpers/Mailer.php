<?php

/**
 * Simple Mailer helper using PHP's built-in mail().
 * To use SMTP/Gmail, replace with PHPMailer.
 */
class Mailer
{
    private static function sendBrevoEmail(string $toEmail, string $toName, string $subject, string $htmlContent): bool
    {
        $apiKey = defined('BREVO_API_KEY') ? BREVO_API_KEY : '';
        
        if (empty($apiKey) || $apiKey === 'YOUR_BREVO_API_KEY_HERE') {
            // Fallback to logging if no API key is set
            $logFile = ROOT_PATH . '/logs/emails.txt';
            if (!is_dir(dirname($logFile))) {
                mkdir(dirname($logFile), 0777, true);
            }
            $logEntry = "[" . date('Y-m-d H:i:s') . "] MISSING_API_KEY | To: {$toEmail} | Subject: {$subject}\n";
            file_put_contents($logFile, $logEntry, FILE_APPEND);
            return false;
        }

        $data = [
            'sender' => ['name' => APP_NAME, 'email' => MAIL_FROM],
            'to' => [
                ['email' => $toEmail, 'name' => $toName]
            ],
            'subject' => $subject,
            'htmlContent' => $htmlContent
        ];

        $ch = curl_init('https://api.brevo.com/v3/smtp/email');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'accept: application/json',
            'api-key: ' . $apiKey,
            'content-type: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 201) {
            return true;
        } else {
            // Log API errors for debugging
            $logFile = ROOT_PATH . '/logs/emails.txt';
            if (!is_dir(dirname($logFile))) {
                mkdir(dirname($logFile), 0777, true);
            }
            $logEntry = "[" . date('Y-m-d H:i:s') . "] BREVO_ERROR | Code: {$httpCode} | Response: {$response}\n";
            file_put_contents($logFile, $logEntry, FILE_APPEND);
            return false;
        }
    }

    /**
     * Send a deficiency notification to a flagged student.
     */
    public static function sendDeficiencyEmail(
        string $studentEmail,
        string $studentName,
        string $officeName,
        string $note,
        string $clearanceName
    ): bool {
        $subject = "Clearance Deficiency Notice — {$clearanceName}";

        $body = "
        <div style=\"font-family: 'Inter', Arial, sans-serif; max-width:600px; margin:0 auto; background:#f9fafb; padding:32px;\">
          <div style=\"background:#1e293b; padding:24px; border-radius:12px 12px 0 0;\">
            <h1 style=\"color:#fff; margin:0; font-size:20px;\">🎓 Clearance Deficiency Notice</h1>
          </div>
          <div style=\"background:#fff; padding:28px; border-radius:0 0 12px 12px; border:1px solid #e2e8f0;\">
            <p style=\"color:#374151; font-size:16px;\">Dear <strong>" . htmlspecialchars($studentName) . "</strong>,</p>
            <p style=\"color:#374151;\">
              You have been flagged for a <strong>deficiency</strong> by the
              <strong>" . htmlspecialchars($officeName) . "</strong> office in relation to your
              <em>" . htmlspecialchars($clearanceName) . "</em> clearance.
            </p>
            <div style=\"background:#fef2f2; border-left:4px solid #ef4444; padding:16px; margin:20px 0; border-radius:4px;\">
              <p style=\"color:#991b1b; margin:0; font-weight:600;\">Deficiency Reason:</p>
              <p style=\"color:#7f1d1d; margin:8px 0 0;\">" . nl2br(htmlspecialchars($note)) . "</p>
            </div>
            <p style=\"color:#374151;\">
              Please visit the <strong>" . htmlspecialchars($officeName) . "</strong> office at your earliest convenience
              to resolve this deficiency and have it removed before your clearance can be completed.
            </p>
            <hr style=\"border:none; border-top:1px solid #e2e8f0; margin:24px 0;\">
            <p style=\"color:#9ca3af; font-size:13px; margin:0;\">
              This is an automated message from the Clearance System. Do not reply to this email.
            </p>
          </div>
        </div>";

        return self::sendBrevoEmail($studentEmail, $studentName, $subject, $body);
    }

    /**
     * Send a "clearance complete" notification once all signatories have cleared the student.
     */
    public static function sendClearedEmail(
        string $studentEmail,
        string $studentName,
        string $clearanceName
    ): bool {
        $subject = "Clearance Complete — {$clearanceName}";

        $body = "
        <div style=\"font-family: 'Inter', Arial, sans-serif; max-width:600px; margin:0 auto; background:#f9fafb; padding:32px;\">
          <div style=\"background:#065f46; padding:24px; border-radius:12px 12px 0 0;\">
            <h1 style=\"color:#fff; margin:0; font-size:20px;\">🎉 Clearance Complete!</h1>
          </div>
          <div style=\"background:#fff; padding:28px; border-radius:0 0 12px 12px; border:1px solid #e2e8f0;\">
            <p style=\"color:#374151; font-size:16px;\">Dear <strong>" . htmlspecialchars($studentName) . "</strong>,</p>
            <p style=\"color:#374151;\">
              Congratulations! All signatories have cleared you for the
              <strong>" . htmlspecialchars($clearanceName) . "</strong> clearance.
              Your clearance is now <strong>complete</strong>.
            </p>
            <div style=\"background:#f0fdf4; border-left:4px solid #22c55e; padding:16px; margin:20px 0; border-radius:4px;\">
              <p style=\"color:#14532d; margin:0; font-weight:600;\">✅ Your clearance has been fully processed.</p>
              <p style=\"color:#166534; margin:8px 0 0;\">No further action is required on your part.</p>
            </div>
            <p style=\"color:#374151;\">
              Please contact your adviser or the administration office if you have any questions.
            </p>
            <hr style=\"border:none; border-top:1px solid #e2e8f0; margin:24px 0;\">
            <p style=\"color:#9ca3af; font-size:13px; margin:0;\">
              This is an automated message from the Clearance System. Do not reply to this email.
            </p>
          </div>
        </div>";

        return self::sendBrevoEmail($studentEmail, $studentName, $subject, $body);
    }
}
