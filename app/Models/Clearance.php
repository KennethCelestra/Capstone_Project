<?php

class Clearance extends Model
{
    protected string $table = 'clearances';

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO clearances (name, description, school_year)
            VALUES (:name, :description, :school_year)
        ");
        return $stmt->execute([
            ':name'        => $data['name'],
            ':description' => $data['description'] ?? '',
            ':school_year' => $data['school_year'] ?? '',
        ]);
    }

    public function getLastInsertId(): int
    {
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE clearances SET name = :name, description = :description, school_year = :school_year
            WHERE id = :id
        ");
        return $stmt->execute([
            ':name'        => $data['name'],
            ':description' => $data['description'] ?? '',
            ':school_year' => $data['school_year'] ?? '',
            ':id'          => $id,
        ]);
    }

    public function findAllWithCounts(): array
    {
        $stmt = $this->db->query("
            SELECT c.*,
                   COUNT(DISTINCT cs2.signatory_id) AS signatory_count,
                   COUNT(DISTINCT ca.adviser_id)    AS adviser_count,
                   COUNT(DISTINCT cst.student_id)   AS student_count
            FROM clearances c
            LEFT JOIN clearance_signatories cs2 ON cs2.clearance_id = c.id
            LEFT JOIN clearance_advisers    ca  ON ca.clearance_id  = c.id
            LEFT JOIN clearance_students    cst ON cst.clearance_id = c.id
            WHERE c.archived = 0
            GROUP BY c.id
            ORDER BY c.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    public function findAllArchived(): array
    {
        $stmt = $this->db->query("
            SELECT c.*,
                   COUNT(DISTINCT cs2.signatory_id) AS signatory_count,
                   COUNT(DISTINCT ca.adviser_id)    AS adviser_count,
                   COUNT(DISTINCT cst.student_id)   AS student_count
            FROM clearances c
            LEFT JOIN clearance_signatories cs2 ON cs2.clearance_id = c.id
            LEFT JOIN clearance_advisers    ca  ON ca.clearance_id  = c.id
            LEFT JOIN clearance_students    cst ON cst.clearance_id = c.id
            WHERE c.archived = 1
            GROUP BY c.id
            ORDER BY c.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    public function archive(int $id): bool
    {
        return $this->db->prepare("UPDATE clearances SET archived = 1 WHERE id = ?")
                        ->execute([$id]);
    }

    public function unarchive(int $id): bool
    {
        return $this->db->prepare("UPDATE clearances SET archived = 0 WHERE id = ?")
                        ->execute([$id]);
    }

    // ---- SIGNATORIES ----

    public function getSignatories(int $clearanceId): array
    {
        $stmt = $this->db->prepare("
            SELECT s.* FROM signatories s
            JOIN clearance_signatories cs ON cs.signatory_id = s.id
            WHERE cs.clearance_id = ?
            ORDER BY s.full_name ASC
        ");
        $stmt->execute([$clearanceId]);
        return $stmt->fetchAll();
    }

    public function assignSignatory(int $clearanceId, int $signatoryId): void
    {
        $stmt = $this->db->prepare("
            INSERT IGNORE INTO clearance_signatories (clearance_id, signatory_id) VALUES (?, ?)
        ");
        $stmt->execute([$clearanceId, $signatoryId]);

        // Initialize status rows for all enrolled students in this clearance
        $students = $this->getStudentIds($clearanceId);
        $statusStmt = $this->db->prepare("
            INSERT IGNORE INTO clearance_status (clearance_id, student_id, signatory_id)
            VALUES (?, ?, ?)
        ");
        foreach ($students as $sid) {
            $statusStmt->execute([$clearanceId, $sid, $signatoryId]);
        }
    }

    public function removeSignatory(int $clearanceId, int $signatoryId): void
    {
        $this->db->prepare("
            DELETE FROM clearance_signatories WHERE clearance_id = ? AND signatory_id = ?
        ")->execute([$clearanceId, $signatoryId]);
    }

    // ---- ADVISERS ----

    public function getAdvisers(int $clearanceId): array
    {
        $stmt = $this->db->prepare("
            SELECT a.* FROM advisers a
            JOIN clearance_advisers ca ON ca.adviser_id = a.id
            WHERE ca.clearance_id = ?
            ORDER BY a.full_name ASC
        ");
        $stmt->execute([$clearanceId]);
        return $stmt->fetchAll();
    }

    public function assignAdviser(int $clearanceId, int $adviserId): void
    {
        $stmt = $this->db->prepare("
            INSERT IGNORE INTO clearance_advisers (clearance_id, adviser_id) VALUES (?, ?)
        ");
        $stmt->execute([$clearanceId, $adviserId]);
    }

    public function removeAdviser(int $clearanceId, int $adviserId): void
    {
        $this->db->prepare("
            DELETE FROM clearance_advisers WHERE clearance_id = ? AND adviser_id = ?
        ")->execute([$clearanceId, $adviserId]);
    }

    // ---- STUDENTS ----

    private function getStudentIds(int $clearanceId): array
    {
        $stmt = $this->db->prepare("SELECT student_id FROM clearance_students WHERE clearance_id = ?");
        $stmt->execute([$clearanceId]);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function getStudentsWithStatus(int $clearanceId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                st.id,
                st.student_id AS student_number,
                st.full_name,
                st.course,
                st.year_level,
                st.section,
                COUNT(DISTINCT csig.signatory_id)                   AS total_signatories,
                COALESCE(SUM(cs.status = 'cleared'), 0)             AS cleared_count,
                COALESCE(SUM(cs.status = 'flagged'), 0)             AS flagged_count,
                COALESCE(SUM(cs.status = 'pending' OR cs.status IS NULL OR cs.status = 'signed'), 0) AS pending_count
            FROM clearance_students cst
            JOIN students st ON st.id = cst.student_id
            LEFT JOIN clearance_signatories csig ON csig.clearance_id = cst.clearance_id
            LEFT JOIN clearance_status cs
                ON cs.student_id   = st.id
               AND cs.clearance_id = cst.clearance_id
               AND cs.signatory_id = csig.signatory_id
            WHERE cst.clearance_id = ?
            GROUP BY st.id, st.student_id, st.full_name, st.course, st.year_level, st.section
            ORDER BY st.full_name ASC
        ");
        $stmt->execute([$clearanceId]);
        return $stmt->fetchAll();
    }

    public function enrollStudent(int $clearanceId, int $studentDbId): void
    {
        // Add to pivot
        $this->db->prepare("
            INSERT IGNORE INTO clearance_students (clearance_id, student_id) VALUES (?, ?)
        ")->execute([$clearanceId, $studentDbId]);

        // Create status rows for each signatory already in this clearance
        $signatories = $this->getSignatories($clearanceId);
        $statusStmt = $this->db->prepare("
            INSERT IGNORE INTO clearance_status (clearance_id, student_id, signatory_id)
            VALUES (?, ?, ?)
        ");
        foreach ($signatories as $sig) {
            $statusStmt->execute([$clearanceId, $studentDbId, $sig['id']]);
        }
    }

    public function removeStudent(int $clearanceId, int $studentDbId): void
    {
        $this->db->prepare("
            DELETE FROM clearance_students WHERE clearance_id = ? AND student_id = ?
        ")->execute([$clearanceId, $studentDbId]);
    }

    /**
     * Get clearance status per signatory for a specific student in a clearance.
     */
    public function getStudentDetail(int $clearanceId, int $studentDbId): array
    {
        $stmt = $this->db->prepare("
            SELECT cs.*, sg.full_name AS signatory_name, sg.office
            FROM clearance_status cs
            JOIN signatories sg ON cs.signatory_id = sg.id
            WHERE cs.clearance_id = ? AND cs.student_id = ?
            ORDER BY sg.office ASC
        ");
        $stmt->execute([$clearanceId, $studentDbId]);
        return $stmt->fetchAll();
    }
}
