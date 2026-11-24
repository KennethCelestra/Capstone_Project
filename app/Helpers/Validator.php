<?php

class Validator
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getInstance();
    }

    public static function sanitize(string $value): string
    {
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }

    public function isEmailTaken(string $email, ?int $excludeId = null, string $table = 'students'): bool
    {
        $email = trim($email);
        $sql = "SELECT COUNT(*) FROM {$table} WHERE email = ?";
        $params = [$email];
        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function isStudentIdTaken(string $studentId, ?int $excludeId = null): bool
    {
        $studentId = trim($studentId);
        $sql = "SELECT COUNT(*) FROM students WHERE student_id = ?";
        $params = [$studentId];
        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Cross-table email uniqueness check across admins, signatories, enrollment_committees, students
     */
    public function isEmailTakenAnyTable(string $email, ?int $excludeId = null, string $excludeTable = ''): bool
    {
        $tables = ['admins', 'signatories', 'enrollment_committees', 'students'];
        $email = trim($email);
        foreach ($tables as $table) {
            $sql = "SELECT COUNT(*) FROM {$table} WHERE email = ?";
            $params = [$email];
            if ($excludeTable === $table && $excludeId !== null) {
                $sql .= " AND id != ?";
                $params[] = $excludeId;
            }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            if ((int)$stmt->fetchColumn() > 0) {
                return true;
            }
        }
        return false;
    }
}

