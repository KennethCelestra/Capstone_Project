<?php

require_once ROOT_PATH . '/vendor/autoload.php';

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
    public static function sendDeficiencyEmail(
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
            <p><small>This is an automated message from the ISAT U Clearance System. Do not reply to this email.</small></p>
        ";

        return self::sendEmail($studentEmail, $studentName, $subject, $body);
    }

    /**
     * Send a "clearance complete" notification once all signatories have cleared the student.
     */
    public static function sendClearedEmail(
        string $studentEmail,
        string $studentName,
        string $clearanceName,
        array $signatoryDetails = []
    ): bool {
        $subject = "Clearance Complete — {$clearanceName}";

        $sigListHtml = "";
        if (!empty($signatoryDetails)) {
            $sigListHtml .= "<br><p><strong>Signatory Approvals:</strong></p><ul>";
            foreach ($signatoryDetails as $sig) {
                $office = htmlspecialchars($sig['office'] ?? 'Unknown Office');
                $date = !empty($sig['signed_at']) ? date('M j, Y g:i A', strtotime($sig['signed_at'])) : 'Unknown Date';
                $sigListHtml .= "<li>{$office}: Signed ({$date})</li>";
            }
            $sigListHtml .= "</ul><br>";
        }

        $body = "
            <p>Dear <strong>" . htmlspecialchars($studentName) . "</strong>,</p>
            <p>Congratulations! All signatories have cleared you for the <strong>" . htmlspecialchars($clearanceName) . "</strong> clearance. Your clearance is now <strong>complete</strong>.</p>
            {$sigListHtml}
            <p>No further action is required on your part.</p>
            <p>Please contact your enrollment committee or the administration office if you have any questions.</p>
            <br>
            <p><small>This is an automated message from the ISAT U Clearance System. Do not reply to this email.</small></p>
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
            $signatoryDetails = isset($student['signatory_details']) ? $student['signatory_details'] : [];
            $ok = self::sendClearedEmail(
                $student['email'],
                $student['full_name'],
                $student['clearance_name'],
                $signatoryDetails
            );
            if (!$ok) $allOk = false;
        }

        return $allOk;
    }
}
