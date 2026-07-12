<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once ROOT_PATH . '/vendor/autoload.php';

/**
 * Simple Mailer helper using PHPMailer + Gmail SMTP.
 */
class Mailer
{
    private static ?PHPMailer $mailInstance = null;

    private static function getMailer(): PHPMailer
    {
        if (self::$mailInstance === null) {
            self::$mailInstance = new PHPMailer(true);
            self::$mailInstance->isSMTP();
            self::$mailInstance->Host       = SMTP_HOST;
            self::$mailInstance->SMTPAuth   = true;
            self::$mailInstance->Username   = SMTP_USER;
            self::$mailInstance->Password   = SMTP_PASS;
            self::$mailInstance->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            self::$mailInstance->Port       = SMTP_PORT;
            self::$mailInstance->CharSet    = 'UTF-8';
            self::$mailInstance->setFrom(MAIL_FROM, APP_NAME);
            // Crucial for bulk sending: keep the connection open to avoid Gmail rate limits & timeouts
            self::$mailInstance->SMTPKeepAlive = true; 
        }
        return self::$mailInstance;
    }

    public static function sendEmail(string $toEmail, string $toName, string $subject, string $htmlContent): bool
    {
        // Give the script more time when sending emails
        set_time_limit(300);
        
        try {
            $mail = self::getMailer();
            
            // Reset state from previous emails
            $mail->clearAllRecipients();
            $mail->clearAttachments();
            
            // Recipients
            $mail->addAddress($toEmail, $toName);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlContent;

            $mail->send();
            return true;
        } catch (Exception $e) {
            // Log API errors for debugging
            $logFile = ROOT_PATH . '/storage/logs/emails.txt';
            if (!is_dir(dirname($logFile))) {
                mkdir(dirname($logFile), 0777, true);
            }
            $mail = self::getMailer();
            $logEntry = "[" . date('Y-m-d H:i:s') . "] SMTP_ERROR | " . $mail->ErrorInfo . "\n";
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
            <p>Please contact your adviser or the administration office if you have any questions.</p>
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
