<?php

class Student extends Model
{
    protected string $table = 'students';

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO students (student_id, full_name, email, course, year_level, section, adviser_id, password)
            VALUES (:student_id, :full_name, :email, :course, :year_level, :section, :adviser_id, :password)
        ");
        return $stmt->execute([
            ':student_id' => $data['student_id'],
            ':full_name' => $data['full_name'],
            ':email' => $data['email'],
            ':course' => $data['course'],
            ':year_level' => $data['year_level'],
            ':section' => $data['section'],
            ':adviser_id' => $data['adviser_id'],
            ':password' => password_hash($data['password'], PASSWORD_DEFAULT),
        ]);
    }

    public function findByStudentId(string $studentId): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM students WHERE student_id = ?");
        $stmt->execute([$studentId]);
        return $stmt->fetch();
    }

    public function findByEmail(string $email): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM students WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function findAllWithAdviser(): array
    {
        $stmt = $this->db->query("
            SELECT s.*, a.full_name AS adviser_name
            FROM students s
            LEFT JOIN advisers a ON s.adviser_id = a.id
            ORDER BY s.full_name ASC
        ");
        return $stmt->fetchAll();
    }
}
