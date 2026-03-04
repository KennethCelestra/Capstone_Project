<?php

class Clearance extends Model
{
    protected string $table = 'clearances';

    /**
     * Initialize a clearance record for a student,
     * creating one row per signatory.
     */
    public function initializeForStudent(int $studentId): void
    {
        $signatories = $this->db->query("SELECT id FROM signatories")->fetchAll();
        $stmt = $this->db->prepare("
            INSERT IGNORE INTO clearance_status (student_id, signatory_id, status)
            VALUES (?, ?, 'pending')
        ");
        foreach ($signatories as $sig) {
            $stmt->execute([$studentId, $sig['id']]);
        }
    }

    /**
     * Get full clearance overview for the admin: one row per student,
     * with a count of signed vs total signatories.
     */
    public function getOverview(): array
    {
        $stmt = $this->db->query("
            SELECT
                s.id,
                s.full_name,
                s.student_id AS student_number,
                s.course,
                s.year_level,
                s.section,
                a.full_name AS adviser_name,
                cs_agg.signed,
                cs_agg.total,
                IF(cs_agg.signed = cs_agg.total AND cs_agg.total > 0, 'cleared', 'pending') AS clearance_status
            FROM students s
            LEFT JOIN advisers a ON s.adviser_id = a.id
            LEFT JOIN (
                SELECT student_id,
                       SUM(status = 'signed') AS signed,
                       COUNT(*) AS total
                FROM clearance_status
                GROUP BY student_id
            ) cs_agg ON cs_agg.student_id = s.id
            ORDER BY s.full_name ASC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Get detailed clearance status of a single student per signatory.
     */
    public function getStudentClearanceDetail(int $studentId): array
    {
        $stmt = $this->db->prepare("
            SELECT cs.*, sg.full_name AS signatory_name, sg.office
            FROM clearance_status cs
            JOIN signatories sg ON cs.signatory_id = sg.id
            WHERE cs.student_id = ?
            ORDER BY sg.office ASC
        ");
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }
}
