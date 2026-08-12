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
                   COUNT(DISTINCT ca.enrollment_committee_id)    AS enrollment_committee_count,
                   COUNT(DISTINCT cst.student_id)   AS student_count
            FROM clearances c
            LEFT JOIN clearance_signatories cs2 ON cs2.clearance_id = c.id
            LEFT JOIN clearance_enrollment_committees    ca  ON ca.clearance_id  = c.id
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
                   COUNT(DISTINCT ca.enrollment_committee_id)    AS enrollment_committee_count,
                   COUNT(DISTINCT cst.student_id)   AS student_count
            FROM clearances c
            LEFT JOIN clearance_signatories cs2 ON cs2.clearance_id = c.id
            LEFT JOIN clearance_enrollment_committees    ca  ON ca.clearance_id  = c.id
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
            SELECT s.*, s.scope_type, s.scope_value 
            FROM signatories s
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
            INSERT IGNORE INTO clearance_signatories (clearance_id, signatory_id) 
            VALUES (?, ?)
        ");
        $stmt->execute([$clearanceId, $signatoryId]);

        $sigStmt = $this->db->prepare("SELECT scope_type, scope_value FROM signatories WHERE id = ?");
        $sigStmt->execute([$signatoryId]);
        $sig = $sigStmt->fetch();

        // Initialize status rows ONLY for students that match this scope
        $students = $this->getStudentIds($clearanceId, $sig['scope_type'], $sig['scope_value']);
        if (!empty($students)) {
            $statusStmt = $this->db->prepare("
                INSERT IGNORE INTO clearance_status (clearance_id, student_id, signatory_id)
                VALUES (?, ?, ?)
            ");
            foreach ($students as $sid) {
                $statusStmt->execute([$clearanceId, $sid, $signatoryId]);
            }
        }
    }

    public function removeSignatory(int $clearanceId, int $signatoryId): void
    {
        $this->db->prepare("
            DELETE FROM clearance_signatories WHERE clearance_id = ? AND signatory_id = ?
        ")->execute([$clearanceId, $signatoryId]);
    }

    // ---- ENROLLMENT COMMITTEE ----

    public function getEnrollmentCommittees(int $clearanceId): array
    {
        $stmt = $this->db->prepare("
            SELECT a.* FROM enrollment_committees a
            JOIN clearance_enrollment_committees ca ON ca.enrollment_committee_id = a.id
            WHERE ca.clearance_id = ?
            ORDER BY a.full_name ASC
        ");
        $stmt->execute([$clearanceId]);
        return $stmt->fetchAll();
    }

    public function assignEnrollmentCommittee(int $clearanceId, int $enrollmentCommitteeId): void
    {
        $stmt = $this->db->prepare("
            INSERT IGNORE INTO clearance_enrollment_committees (clearance_id, enrollment_committee_id) VALUES (?, ?)
        ");
        $stmt->execute([$clearanceId, $enrollmentCommitteeId]);
    }

    public function removeEnrollmentCommittee(int $clearanceId, int $enrollmentCommitteeId): void
    {
        $this->db->prepare("
            DELETE FROM clearance_enrollment_committees WHERE clearance_id = ? AND enrollment_committee_id = ?
        ")->execute([$clearanceId, $enrollmentCommitteeId]);
    }

    // ---- STUDENTS ----

    private function getStudentIds(int $clearanceId, ?string $scopeType = null, ?string $scopeValue = null): array
    {
        $query = "
            SELECT cs.student_id 
            FROM clearance_students cs
            JOIN students s ON s.id = cs.student_id
            WHERE cs.clearance_id = ?
        ";
        $params = [$clearanceId];

        if ($scopeType === 'college' && !empty($scopeValue)) {
            $query .= " AND s.college = ?";
            $params[] = $scopeValue;
        } elseif ($scopeType === 'course' && !empty($scopeValue)) {
            $query .= " AND s.course = ?";
            $params[] = $scopeValue;
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getStudentsWithStatus(int $clearanceId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                st.id,
                st.student_id AS student_number,
                st.last_name,
                st.first_name,
                st.email,
                st.college,
                st.course,
                st.year_level,
                st.section,
                COUNT(DISTINCT csig.signatory_id)                   AS total_signatories,
                COALESCE(SUM(cs.status = 'cleared'), 0)             AS cleared_count,
                COALESCE(SUM(cs.status = 'flagged'), 0)             AS flagged_count
            FROM clearance_students cst
            JOIN students st ON st.id = cst.student_id
            LEFT JOIN clearance_signatories csig ON csig.clearance_id = cst.clearance_id
            LEFT JOIN clearance_status cs
                ON cs.student_id   = st.id
               AND cs.clearance_id = cst.clearance_id
               AND cs.signatory_id = csig.signatory_id
            WHERE cst.clearance_id = ?
            GROUP BY st.id, st.student_id, st.last_name, st.first_name, st.email, st.college, st.course, st.year_level, st.section
            ORDER BY st.last_name ASC, st.first_name ASC
        ");
        $stmt->execute([$clearanceId]);
        $rows = $stmt->fetchAll();

        // Derive pending_count in PHP to avoid SQL ambiguity
        foreach ($rows as &$row) {
            $row['pending_count'] = (int)$row['total_signatories']
                                  - (int)$row['cleared_count']
                                  - (int)$row['flagged_count'];
        }
        unset($row);
        return $rows;
    }

    public function bulkEnrollStudents(int $clearanceId, array $studentDbIds): void
    {
        if (empty($studentDbIds)) return;

        $this->db->beginTransaction();
        try {
            // 1. Bulk insert into clearance_students
            $pivotStmt = $this->db->prepare("INSERT IGNORE INTO clearance_students (clearance_id, student_id) VALUES (?, ?)");
            foreach ($studentDbIds as $sid) {
                $pivotStmt->execute([$clearanceId, $sid]);
            }

            // 2. Bulk insert status rows matching scopes
            $placeholders = implode(',', array_fill(0, count($studentDbIds), '?'));
            $insertStatusSql = "
                INSERT IGNORE INTO clearance_status (clearance_id, student_id, signatory_id)
                SELECT ?, st.id, csig.signatory_id
                FROM students st
                JOIN clearance_signatories csig ON csig.clearance_id = ?
                JOIN signatories sg ON sg.id = csig.signatory_id
                WHERE st.id IN ($placeholders)
                  AND (
                      sg.scope_type IS NULL 
                      OR (sg.scope_type = 'college' AND sg.scope_value = st.college)
                      OR (sg.scope_type = 'course' AND sg.scope_value = st.course)
                  )
            ";
            $params = array_merge([$clearanceId, $clearanceId], $studentDbIds);
            $this->db->prepare($insertStatusSql)->execute($params);

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function removeStudent(int $clearanceId, int $studentDbId): void
    {
        $this->db->prepare("
            DELETE FROM clearance_students WHERE clearance_id = ? AND student_id = ?
        ")->execute([$clearanceId, $studentDbId]);
    }

    public function getActiveClearancesWithProgress(): array
    {
        $stmt = $this->db->query("
            SELECT
                c.id AS clearance_id,
                c.name AS clearance_name,
                c.school_year,
                (SELECT COUNT(*) FROM clearance_signatories WHERE clearance_id = c.id) AS signatory_count,
                (SELECT COUNT(*) FROM clearance_students WHERE clearance_id = c.id) AS student_count,
                COALESCE(st_stats.cleared_total, 0) AS cleared_total,
                COALESCE(st_stats.flagged_total, 0) AS flagged_total,
                (
                    (SELECT COUNT(*) FROM clearance_students WHERE clearance_id = c.id) 
                    - COALESCE(st_stats.cleared_total, 0) 
                    - COALESCE(st_stats.flagged_total, 0)
                ) AS pending_total
            FROM clearances c
            LEFT JOIN (
                SELECT 
                    sub.clearance_id,
                    SUM(CASE WHEN sub.flagged_cnt > 0 THEN 1 ELSE 0 END) AS flagged_total,
                    SUM(CASE WHEN sub.flagged_cnt = 0 AND sub.total_sig > 0 AND sub.cleared_cnt = sub.total_sig THEN 1 ELSE 0 END) AS cleared_total
                FROM (
                    SELECT 
                        cst.clearance_id,
                        cst.student_id,
                        (SELECT COUNT(*) FROM clearance_signatories WHERE clearance_id = cst.clearance_id) AS total_sig,
                        SUM(CASE WHEN cs.status = 'flagged' THEN 1 ELSE 0 END) AS flagged_cnt,
                        SUM(CASE WHEN cs.status = 'cleared' THEN 1 ELSE 0 END) AS cleared_cnt
                    FROM clearance_students cst
                    LEFT JOIN clearance_status cs ON cs.clearance_id = cst.clearance_id AND cs.student_id = cst.student_id
                    GROUP BY cst.clearance_id, cst.student_id
                ) sub
                GROUP BY sub.clearance_id
            ) st_stats ON st_stats.clearance_id = c.id
            WHERE c.archived = 0
            ORDER BY c.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    public function getOverallProgress(?array $clearances = null): array
    {
        if ($clearances === null) {
            $clearances = $this->getActiveClearancesWithProgress();
        }
        $totalStudents = 0;
        $totalCleared  = 0;
        $totalFlagged  = 0;
        $totalPending  = 0;

        foreach ($clearances as $c) {
            $totalStudents += (int) $c['student_count'];
            $totalCleared  += (int) $c['cleared_total'];
            $totalFlagged  += (int) $c['flagged_total'];
            $totalPending  += (int) $c['pending_total'];
        }

        return [
            'total_students' => $totalStudents,
            'cleared_total'  => $totalCleared,
            'flagged_total'  => $totalFlagged,
            'pending_total'  => $totalPending,
        ];
    }
}
