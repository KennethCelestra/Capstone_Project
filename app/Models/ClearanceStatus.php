<?php

class ClearanceStatus extends Model
{
    protected string $table = 'clearance_status';

    // ----------------------------------------------------------------
    // SIGNATORY: Student listing
    // ----------------------------------------------------------------

    /**
     * Get all students in a clearance for a given signatory,
     * including their email, flag note, and updated_at.
     */
    public function getStudentsForSignatory(int $clearanceId, int $signatoryId): array
    {
        $stmt = $this->db->prepare("
            SELECT st.id, st.student_id AS student_number, st.full_name,
                   st.email, st.course, st.year_level, st.section,
                   COALESCE(cs.status, 'pending') AS status,
                   cs.flag_note, cs.signed_at, cs.updated_at
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

    /**
     * Get all clearances a signatory is assigned to, with summary counts.
     */
    public function getClearancesForSignatory(int $signatoryId): array
    {
        $stmt = $this->db->prepare("
            SELECT c.id AS clearance_id, c.name AS clearance_name, c.school_year,
                   COUNT(DISTINCT cst.student_id)                              AS total_students,
                   COALESCE(SUM(cs.status = 'flagged'),  0)                    AS flagged_count,
                   COALESCE(SUM(cs.status = 'cleared'),  0)                    AS cleared_count,
                   COALESCE(SUM(cs.status = 'pending' OR cs.status IS NULL), 0) AS pending_count
            FROM clearance_signatories csig
            JOIN clearances c   ON c.id  = csig.clearance_id
            JOIN clearance_students cst ON cst.clearance_id = c.id
            LEFT JOIN clearance_status cs
                ON cs.clearance_id = c.id
               AND cs.student_id   = cst.student_id
               AND cs.signatory_id = ?
            WHERE csig.signatory_id = ?
            GROUP BY c.id, c.name, c.school_year
            ORDER BY c.name ASC
        ");
        $stmt->execute([$signatoryId, $signatoryId]);
        return $stmt->fetchAll();
    }

    /**
     * Get all flagged students for the confirmation process.
     * Optionally filtered by clearance.
     */
    public function getFlaggedStudentsForConfirmation(int $signatoryId, int $clearanceId = 0): array
    {
        $sql = "
            SELECT cs.clearance_id, c.name AS clearance_name,
                   st.id AS student_db_id, st.student_id AS student_number,
                   st.full_name, st.email, st.course, st.year_level, st.section,
                   cs.flag_note, cs.updated_at
            FROM clearance_status cs
            JOIN clearances c  ON c.id  = cs.clearance_id
            JOIN students   st ON st.id = cs.student_id
            WHERE cs.signatory_id = ? AND cs.status = 'flagged'
        ";
        $params = [$signatoryId];

        if ($clearanceId > 0) {
            $sql .= " AND cs.clearance_id = ?";
            $params[] = $clearanceId;
        }

        $sql .= " ORDER BY c.name ASC, st.full_name ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ----------------------------------------------------------------
    // SIGNATORY: Actions
    // ----------------------------------------------------------------

    /**
     * Flag a student with a deficiency note.
     */
    public function flagStudent(int $clearanceId, int $studentId, int $signatoryId, string $note): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO clearance_status (clearance_id, student_id, signatory_id, status, flag_note, signed_at)
            VALUES (?, ?, ?, 'flagged', ?, NULL)
            ON DUPLICATE KEY UPDATE status = 'flagged', flag_note = VALUES(flag_note), signed_at = NULL
        ");
        return $stmt->execute([$clearanceId, $studentId, $signatoryId, $note]);
    }

    /**
     * Clear (unflag) a student — sets status to 'cleared'.
     */
    public function clearStudent(int $clearanceId, int $studentId, int $signatoryId): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO clearance_status (clearance_id, student_id, signatory_id, status, flag_note, signed_at)
            VALUES (?, ?, ?, 'cleared', NULL, NOW())
            ON DUPLICATE KEY UPDATE status = 'cleared', flag_note = NULL, signed_at = NOW()
        ");
        return $stmt->execute([$clearanceId, $studentId, $signatoryId]);
    }


    // ----------------------------------------------------------------
    // CLEARED CHECK: Used to fire the "all cleared" email
    // ----------------------------------------------------------------

    /**
     * Returns true if the student has been marked 'cleared' by ALL signatories
     * assigned to this clearance.
     */
    public function isStudentFullyCleared(int $clearanceId, int $studentId): bool
    {
        // Count assigned signatories
        $stmtSig = $this->db->prepare("SELECT COUNT(*) FROM clearance_signatories WHERE clearance_id = ?");
        $stmtSig->execute([$clearanceId]);
        $totalAssigned = (int)$stmtSig->fetchColumn();

        if ($totalAssigned === 0) return false;

        // Count cleared statuses for this student
        $stmtCleared = $this->db->prepare("
            SELECT COUNT(*) FROM clearance_status
            WHERE clearance_id = ? AND student_id = ? AND status = 'cleared'
        ");
        $stmtCleared->execute([$clearanceId, $studentId]);
        $totalCleared = (int)$stmtCleared->fetchColumn();

        return ($totalCleared === $totalAssigned);
    }

    /**
     * Get student email + name + clearance name for the "fully cleared" email.
     */
    public function getStudentClearanceInfo(int $clearanceId, int $studentId): array|false
    {
        $stmt = $this->db->prepare("
            SELECT st.full_name, st.email, c.name AS clearance_name
            FROM students   st
            JOIN clearances c  ON c.id = ?
            WHERE st.id = ?
        ");
        $stmt->execute([$clearanceId, $studentId]);
        return $stmt->fetch();
    }

    // ----------------------------------------------------------------
    // ADVISER: Student listing with full signatory detail
    // ----------------------------------------------------------------

    /**
     * Get clearances an adviser is assigned to, with per-student flag detail.
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
                st.email,
                st.course,
                st.year_level,
                st.section,
                COALESCE(SUM(cs.status = 'cleared'), 0)              AS cleared_count,
                COALESCE(SUM(cs.status = 'flagged'), 0)              AS flagged_count,
                COUNT(DISTINCT csig.signatory_id)                     AS total_signatories
            FROM clearance_advisers ca
            JOIN clearances c  ON c.id  = ca.clearance_id
            JOIN clearance_students cst ON cst.clearance_id = c.id
            JOIN students   st ON st.id = cst.student_id
            JOIN clearance_signatories csig ON csig.clearance_id = c.id
            LEFT JOIN clearance_status cs
                ON cs.clearance_id = c.id
               AND cs.student_id   = st.id
               AND cs.signatory_id = csig.signatory_id
            WHERE ca.adviser_id = ?
            GROUP BY c.id, c.name, c.school_year,
                     st.id, st.student_id, st.full_name, st.email,
                     st.course, st.year_level, st.section
            ORDER BY c.name ASC, st.full_name ASC
        ");
        $stmt->execute([$adviserId]);
        return $stmt->fetchAll();
    }

    /**
     * Get per-signatory flag detail for a specific student in a clearance.
     * Used by adviser to see exactly who flagged and why.
     */
    public function getSignatoryDetailForStudent(int $clearanceId, int $studentId): array
    {
        $stmt = $this->db->prepare("
            SELECT sg.full_name AS signatory_name, sg.office,
                   COALESCE(cs.status, 'pending') AS status,
                   cs.flag_note, cs.signed_at
            FROM clearance_signatories csig
            JOIN signatories sg ON sg.id = csig.signatory_id
            LEFT JOIN clearance_status cs
                ON cs.signatory_id = sg.id
               AND cs.clearance_id  = csig.clearance_id
               AND cs.student_id    = ?
            WHERE csig.clearance_id = ?
            ORDER BY sg.office ASC
        ");
        $stmt->execute([$studentId, $clearanceId]);
        return $stmt->fetchAll();
    }


    // ----------------------------------------------------------------
    // SIGNATORY: Bulk clear all pending (non-flagged) students
    // ----------------------------------------------------------------

    /**
     * Mark every student who is currently 'pending' (no row, or status=pending)
     * as 'cleared' for this signatory within the given clearance.
     * Students with status='flagged' are intentionally skipped.
     * Returns an array of student IDs that were successfully cleared.
     */
    public function clearAllPending(int $clearanceId, int $signatoryId): array
    {
        // Step 1: Get all students in this clearance that are NOT flagged for this signatory
        $stmt = $this->db->prepare("
            SELECT st.id
            FROM clearance_students cst
            JOIN students st ON st.id = cst.student_id
            LEFT JOIN clearance_status cs
                ON cs.student_id   = st.id
               AND cs.clearance_id = ?
               AND cs.signatory_id = ?
            WHERE cst.clearance_id = ?
              AND (cs.status IS NULL OR cs.status = 'pending')
        ");
        $stmt->execute([$clearanceId, $signatoryId, $clearanceId]);
        $studentIds = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        if (empty($studentIds)) {
            return [];
        }

        // Step 2: Bulk upsert to 'cleared'
        $clearStmt = $this->db->prepare("
            INSERT INTO clearance_status (clearance_id, student_id, signatory_id, status, flag_note, signed_at)
            VALUES (?, ?, ?, 'cleared', NULL, NOW())
            ON DUPLICATE KEY UPDATE status = 'cleared', flag_note = NULL, signed_at = NOW()
        ");

        $clearedIds = [];
        foreach ($studentIds as $sId) {
            if ($clearStmt->execute([$clearanceId, (int)$sId, $signatoryId])) {
                $clearedIds[] = (int)$sId;
            }
        }
        return $clearedIds;
    }
}
