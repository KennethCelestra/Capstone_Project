<?php

class ClearanceTemplate extends Model
{
    protected string $table = 'clearance_templates';

    public function getByType(string $type): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM clearance_templates WHERE type = ? LIMIT 1");
        $stmt->execute([$type]);
        return $stmt->fetch();
    }

    public function updateTemplate(string $type, string $headerTitle, string $bodyText, string $footerText): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO clearance_templates (type, header_title, body_text, footer_text)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                header_title = VALUES(header_title),
                body_text    = VALUES(body_text),
                footer_text  = VALUES(footer_text)
        ");
        return $stmt->execute([$type, $headerTitle, $bodyText, $footerText]);
    }
}
