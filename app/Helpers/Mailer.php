<?php

/**
 * Simple Mailer helper using Brevo API.
 */
class Mailer
{
    public static function sendEmail(string $toEmail, string $toName, string $subject, string $htmlContent): bool
    {
        $apiKey = defined('BREVO_API_KEY') ? BREVO_API_KEY : '';
        
        if (empty($apiKey) || strtolower($apiKey) === 'your_brevo_api_key_here') {
            // Fallback to logging if no API key is set
            $logFile = ROOT_PATH . '/storage/logs/emails.txt';
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

        // Give the script more time when sending emails
        set_time_limit(300);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 201) {
            return true;
        } else {
            // Log API errors for debugging
            $logFile = ROOT_PATH . '/storage/logs/emails.txt';
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
    private static function sendDeficiencyEmail(
        string $studentEmail,
        string $studentName,
        string $officeName,
        string $note,
        string $clearanceName
    ): bool {
        $subject = "Clearance Deficiency Notice — {$clearanceName}";

        $body = "
            <p>Dear <strong>" . htmlspecialchars($studentName) . "</strong>,</p>
            <p>You have been flagged for a deficiency by the <strong>" . htmlspecialchars($officeName) . "</strong> office in relation to your <em>" . htmlspecialchars($clearanceName) . "</em> clearance.</p>
            <p><strong>Deficiency Reason:</strong><br>" . nl2br(htmlspecialchars($note)) . "</p>
            <p>Please visit the <strong>" . htmlspecialchars($officeName) . "</strong> office at your earliest convenience to resolve this deficiency and have it removed before your clearance can be completed.</p>
            <br>
            <p><small>This is an automated message from the AutoClear Clearance System. Do not reply to this email.</small></p>
        ";

        return self::sendEmail($studentEmail, $studentName, $subject, $body);
    }

    /**
     * Send a "clearance complete" notification with a link to the printable form.
     */
    private static function sendClearedEmail(
        string $studentEmail,
        string $studentName,
        string $clearanceName,
        int    $studentDbId,
        int    $clearanceId
    ): bool {
        $subject = "Clearance Complete — {$clearanceName}";

        // Build secure HMAC token for the printable form link
        $token   = hash_hmac('sha256', "cid={$clearanceId}&sid={$studentDbId}", APP_SECRET);
        $formUrl = BASE_URL . "clearance/form?cid={$clearanceId}&sid={$studentDbId}&token={$token}";

        $body = "
            <div style=\"font-family: Arial, Helvetica, sans-serif; max-width: 600px; margin: 0 auto; color: #333;\">
                <p>Dear <strong>" . htmlspecialchars($studentName) . "</strong>,</p>
                <p>Congratulations! You have been fully cleared for the <strong>" . htmlspecialchars($clearanceName) . "</strong> clearance. All required offices have signed off on your record.</p>
                <p>You may now view and print your official clearance form using the button below:</p>
                <p style=\"text-align: center; margin: 28px 0;\">
                    <a href=\"" . htmlspecialchars($formUrl) . "\"
                       style=\"background-color: #1a56a0; color: #ffffff; text-decoration: none;
                              padding: 14px 32px; border-radius: 6px; font-size: 15px;
                              font-weight: bold; display: inline-block;\">
                        📄 View &amp; Print Clearance Form
                    </a>
                </p>
                <p style=\"font-size: 13px; color: #777;\">If the button does not work, copy and paste this link into your browser:<br>
                    <a href=\"" . htmlspecialchars($formUrl) . "\" style=\"color: #1a56a0;\">" . htmlspecialchars($formUrl) . "</a>
                </p>
                <br>
                <p><small>This is an automated message from the AutoClear Clearance System. Do not reply to this email.</small></p>
            </div>
        ";

        return self::sendEmail($studentEmail, $studentName, $subject, $body);
    }
    public static function sendBulkDeficiencyEmail(array $flaggedStudents, string $officeName): bool
    {
        if (empty($flaggedStudents)) return true;

        $allOk = true;
        foreach ($flaggedStudents as $f) {
            $ok = self::sendDeficiencyEmail(
                $f['email'],
                $f['full_name'],
                $officeName,
                $f['flag_note'],
                $f['clearance_name']
            );
            if (!$ok) $allOk = false;
        }

        return $allOk;
    }

    public static function sendBulkClearedEmail(array $studentsData): bool
    {
        if (empty($studentsData)) return true;

        $allOk = true;
        foreach ($studentsData as $student) {
            $ok = self::sendClearedEmail(
                $student['email'],
                $student['full_name'],
                $student['clearance_name'],
                (int) ($student['student_id'] ?? 0),
                (int) ($student['clearance_id'] ?? 0)
            );
            if (!$ok) $allOk = false;
        }

        return $allOk;
    }
}
