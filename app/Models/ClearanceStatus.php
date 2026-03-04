<?php

class ClearanceStatus extends Model
{
    protected string $table = 'clearance_status';

    /**
     * Get all pending clearances for a specific signatory.
     */
    public function getPendingBySignatory(int $signatoryId): array
    {
        $stmt = $this->db->prepare("
            SELECT cs.*, s.full_name AS student_name, s.student_id AS student_number,
                   s.course, s.year_level, s.section
            FROM clearance_status cs
            JOIN students s ON cs.student_id = s.id
            WHERE cs.signatory_id = ? AND cs.status = 'pending'
            ORDER BY s.full_name ASC
        ");
        $stmt->execute([$signatoryId]);
        return $stmt->fetchAll();
    }

    /**
     * Get all clearances for students under a specific adviser.
     */
    public function getByAdviser(int $adviserId): array
    {
        $stmt = $this->db->prepare("
            SELECT s.full_name AS student_name, s.student_id AS student_number,
                   s.course, s.year_level, s.section,
                   SUM(cs.status = 'signed') AS signed,
                   COUNT(*) AS total
            FROM clearance_status cs
            JOIN students s ON cs.student_id = s.id
            WHERE s.adviser_id = ?
            GROUP BY cs.student_id
            ORDER BY s.full_name ASC
        ");
        $stmt->execute([$adviserId]);
        return $stmt->fetchAll();
    }

    /**
     * Mark a student's clearance as signed by a signatory.
     */
    public function sign(int $studentId, int $signatoryId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE clearance_status
            SET status = 'signed', signed_at = NOW()
            WHERE student_id = ? AND signatory_id = ?
        ");
        return $stmt->execute([$studentId, $signatoryId]);
    }

    /**
     * Mark as cleared by adviser.
     */
    public function markCleared(int $studentId, int $adviserId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE clearance_status
            SET adviser_status = 'cleared', cleared_at = NOW()
            WHERE student_id = ? AND signatory_id IS NULL
        ");
        return $stmt->execute([$studentId]);
    }
}
