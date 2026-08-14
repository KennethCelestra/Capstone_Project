<?php

class Student extends Model
{
    protected string $table = 'students';

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO students (student_id, last_name, first_name, email, college, course, year_level, section, status)
            VALUES (:student_id, :last_name, :first_name, :email, :college, :course, :year_level, :section, :status)
        ");
        return $stmt->execute([
            ':student_id' => $data['student_id'],
            ':last_name'  => $data['last_name'],
            ':first_name' => $data['first_name'],
            ':email'      => $data['email'],
            ':college'    => $data['college'],
            ':course'     => $data['course'],
            ':year_level' => $data['year_level'],
            ':section'    => $data['section'],
            ':status'     => $data['status'] ?? 'active',
        ]);
    }

    /**
     * Update an existing student's details (including status).
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE students
            SET student_id = :student_id,
                last_name  = :last_name,
                first_name = :first_name,
                email      = :email,
                college    = :college,
                course     = :course,
                year_level = :year_level,
                section    = :section,
                status     = :status
            WHERE id = :id
        ");
        return $stmt->execute([
            ':student_id' => $data['student_id'],
            ':last_name'  => $data['last_name'],
            ':first_name' => $data['first_name'],
            ':email'      => $data['email'],
            ':college'    => $data['college'],
            ':course'     => $data['course'],
            ':year_level' => $data['year_level'],
            ':section'    => $data['section'],
            ':status'     => $data['status'],
            ':id'         => $id,
        ]);
    }

    /**
     * Bulk insert students from a parsed CSV array.
     * Each row: [student_id, last_name, first_name, email, college, course, year_level, section]
     * Skips duplicates on student_id or email.
     * Returns [inserted, skipped].
     */
    public function bulkInsertFromCSV(array $rows): array
    {
        $stmt = $this->db->prepare("
            INSERT IGNORE INTO students (student_id, last_name, first_name, email, college, course, year_level, section, status)
            VALUES (:student_id, :last_name, :first_name, :email, :college, :course, :year_level, :section, 'active')
        ");
        $inserted = 0;
        $skipped  = 0;

        $this->db->beginTransaction();
        foreach ($rows as $row) {
            // Require at minimum student_id, last_name, first_name, email
            if (empty($row['student_id']) || empty($row['last_name']) || empty($row['first_name']) || empty($row['email'])) {
                $skipped++;
                continue;
            }
            $ok = $stmt->execute([
                ':student_id' => trim($row['student_id']),
                ':last_name'  => trim($row['last_name']),
                ':first_name' => trim($row['first_name']),
                ':email'      => trim($row['email']),
                ':college'    => trim($row['college'] ?? ''),
                ':course'     => trim($row['course'] ?? ''),
                ':year_level' => (int) ($row['year_level'] ?? 1),
                ':section'    => trim($row['section'] ?? ''),
            ]);
            if ($ok && $stmt->rowCount() > 0) {
                $inserted++;
            } else {
                $skipped++;
            }
        }
        $this->db->commit();
        return [$inserted, $skipped];
    }

    /**
     * All students visible on the Students management page (active + dropped).
     * Graduated students are hidden from this view but kept in the database.
     */
    public function findAll(): array
    {
        $stmt = $this->db->query("
            SELECT * FROM students
            WHERE status != 'graduated'
            ORDER BY last_name ASC, first_name ASC
        ");
        return $stmt->fetchAll();
    }

    /**
     * All active students only — used when enrolling into a clearance.
     */
    public function findAllActive(): array
    {
        $stmt = $this->db->query("
            SELECT * FROM students
            WHERE status = 'active'
            ORDER BY last_name ASC, first_name ASC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Active students NOT yet enrolled in a given clearance.
     * Used by "Enroll All Active Students".
     */
    public function findNotInClearanceActive(int $clearanceId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM students
            WHERE status = 'active'
              AND id NOT IN (
                SELECT student_id FROM clearance_students WHERE clearance_id = ?
              )
            ORDER BY last_name ASC, first_name ASC
        ");
        $stmt->execute([$clearanceId]);
        return $stmt->fetchAll();
    }

    /**
     * All students (any status) NOT yet enrolled in a given clearance.
     * Used by CSV upload flow.
     */
    public function findNotInClearance(int $clearanceId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM students
            WHERE id NOT IN (
                SELECT student_id FROM clearance_students WHERE clearance_id = ?
            )
            ORDER BY last_name ASC, first_name ASC
        ");
        $stmt->execute([$clearanceId]);
        return $stmt->fetchAll();
    }

    /**
     * Promote all active students at the end of a school year:
     *  1. Mark 4th-year actives as 'graduated'
     *  2. Increment year_level for remaining actives (1→2, 2→3, 3→4)
     * Returns ['promoted' => int, 'graduated' => int].
     */
    public function promoteAll(): array
    {
        $this->db->beginTransaction();
        try {
            // Step 1: graduate 4th-year students
            $gradStmt = $this->db->prepare("
                UPDATE students SET status = 'graduated'
                WHERE year_level = 4 AND status = 'active'
            ");
            $gradStmt->execute();
            $graduated = $gradStmt->rowCount();

            // Step 2: promote remaining active students up one year
            $promoteStmt = $this->db->prepare("
                UPDATE students SET year_level = year_level + 1
                WHERE status = 'active'
            ");
            $promoteStmt->execute();
            $promoted = $promoteStmt->rowCount();

            $this->db->commit();
            return ['promoted' => $promoted, 'graduated' => $graduated];
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Get all distinct, non-empty college values from the students table.
     */
    public function getDistinctColleges(): array
    {
        $stmt = $this->db->query("
            SELECT DISTINCT college FROM students
            WHERE college IS NOT NULL AND college != ''
            ORDER BY college ASC
        ");
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Get all distinct, non-empty course values from the students table.
     */
    public function getDistinctCourses(): array
    {
        $stmt = $this->db->query("
            SELECT DISTINCT course FROM students
            WHERE course IS NOT NULL AND course != ''
            ORDER BY course ASC
        ");
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }
}
