<?php

class ClearanceStatus extends Model
{
    protected string $table = 'clearance_status';

    /**
     * Get all clearance statuses for a specific student in a specific clearance.
     */
    public function getForStudentInClearance(int $clearanceId, int $studentId): array
    {
        $stmt = $this->db->prepare("
            SELECT cs.*, sg.full_name AS signatory_name, sg.office
            FROM clearance_status cs
            JOIN signatories sg ON cs.signatory_id = sg.id
            WHERE cs.clearance_id = ? AND cs.student_id = ?
            ORDER BY sg.office ASC
        ");
        $stmt->execute([$clearanceId, $studentId]);
        return $stmt->fetchAll();
    }

    /**
     * Get all pending students for a signatory in a clearance (for signatory view).
     */
    public function getForSignatory(int $clearanceId, int $signatoryId): array
    {
        $stmt = $this->db->prepare("
            SELECT cs.*, st.student_id AS student_number, st.full_name, st.course, st.year_level, st.section
            FROM clearance_status cs
            JOIN students st ON st.id = cs.student_id
            WHERE cs.clearance_id = ? AND cs.signatory_id = ?
            ORDER BY st.full_name ASC
        ");
        $stmt->execute([$clearanceId, $signatoryId]);
        return $stmt->fetchAll();
    }

    /**
     * Mark a specific status row as signed.
     */
    public function sign(int $clearanceId, int $studentId, int $signatoryId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE clearance_status
            SET status = 'signed', signed_at = NOW()
            WHERE clearance_id = ? AND student_id = ? AND signatory_id = ?
        ");
        return $stmt->execute([$clearanceId, $studentId, $signatoryId]);
    }

    /**
     * Get clearances an adviser is assigned to, with student progress.
     */
    public function getClearancesForAdviser(int $adviserId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                c.id AS clearance_id,
                c.name AS clearance_name,
                c.school_year,
                st.id,
                st.student_id AS student_number,
                st.full_name,
                st.course,
                st.year_level,
                st.section,
                COALESCE(SUM(cs.status = 'signed'), 0) AS signed_count,
                COALESCE(COUNT(cs.id), 0)              AS total_count
            FROM clearance_advisers ca
            JOIN clearances c ON c.id = ca.clearance_id
            JOIN clearance_students cst ON cst.clearance_id = c.id
            JOIN students st ON st.id = cst.student_id
            LEFT JOIN clearance_status cs ON cs.clearance_id = c.id AND cs.student_id = st.id
            WHERE ca.adviser_id = ?
            GROUP BY c.id, c.name, c.school_year, st.id, st.student_id, st.full_name, st.course, st.year_level, st.section
            ORDER BY c.name ASC, st.full_name ASC
        ");
        $stmt->execute([$adviserId]);
        return $stmt->fetchAll();
    }

    /**
     * Get clearances a signatory is assigned to.
     */
    public function getClearancesForSignatory(int $signatoryId): array
    {
        $stmt = $this->db->prepare("
            SELECT c.id AS clearance_id, c.name AS clearance_name, c.school_year,
                   COUNT(DISTINCT cs.student_id)                               AS total_students,
                   COALESCE(SUM(cs.status = 'signed'), 0)                      AS signed_count
            FROM clearance_signatories csig
            JOIN clearances c ON c.id = csig.clearance_id
            LEFT JOIN clearance_status cs ON cs.clearance_id = c.id AND cs.signatory_id = ?
            WHERE csig.signatory_id = ?
            GROUP BY c.id, c.name, c.school_year
            ORDER BY c.name ASC
        ");
        $stmt->execute([$signatoryId, $signatoryId]);
        return $stmt->fetchAll();
    }

    /**
     * Get all students in a clearance with their sign status for a specific signatory.
     */
    public function getStudentsForSignatory(int $clearanceId, int $signatoryId): array
    {
        $stmt = $this->db->prepare("
            SELECT st.id, st.student_id AS student_number, st.full_name, st.course,
                   st.year_level, st.section,
                   cs.status, cs.signed_at
            FROM clearance_students cst
            JOIN students st ON st.id = cst.student_id
            LEFT JOIN clearance_status cs
                ON cs.student_id   = st.id
               AND cs.clearance_id = cst.clearance_id
               AND cs.signatory_id = ?
            WHERE cst.clearance_id = ?
            ORDER BY cs.status ASC, st.full_name ASC
        ");
        $stmt->execute([$signatoryId, $clearanceId]);
        return $stmt->fetchAll();
    }
}
