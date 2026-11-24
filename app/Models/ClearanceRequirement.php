<?php

class ClearanceRequirement extends Model
{
    protected string $table = 'clearance_requirements';

    public function getForStudent(int $clearanceId, int $studentId): array
    {
        $stmt = $this->db->prepare("
            SELECT cr.*, sg.office, sg.full_name AS signatory_name
            FROM clearance_requirements cr
            JOIN signatories sg ON sg.id = cr.signatory_id
            WHERE cr.clearance_id = ? AND cr.student_id = ?
            ORDER BY cr.uploaded_at DESC
        ");
        $stmt->execute([$clearanceId, $studentId]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO clearance_requirements
                (clearance_id, student_id, signatory_id, file_name, file_path, mime_type, uploaded_by_role, uploaded_by_id, initials, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->execute([
            $data['clearance_id'],
            $data['student_id'],
            $data['signatory_id'],
            $data['file_name'],
            $data['file_path'],
            $data['mime_type'],
            $data['uploaded_by_role'] ?? 'signatory',
            $data['uploaded_by_id'],
            $data['initials']
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM clearance_requirements WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM clearance_requirements WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function extractInitials(string $name): string
    {
        $words = preg_split('/\s+/', trim($name));
        $initials = '';
        foreach ($words as $w) {
            if (!empty($w)) {
                $initials .= strtoupper(mb_substr($w, 0, 1));
            }
        }
        return substr($initials, 0, 4) ?: 'SIG';
    }
}
