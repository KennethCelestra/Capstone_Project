<?php

class Enrollment_Committee extends Model
{
    protected string $table = 'enrollment_committees';

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO enrollment_committees (full_name, email, department, password)
            VALUES (:full_name, :email, :department, :password)
        ");
        return $stmt->execute([
            ':full_name'      => $data['full_name'],
            ':email'          => $data['email'],
            ':department'     => $data['department'],
            ':password'       => password_hash($data['password'], PASSWORD_DEFAULT),
        ]);
    }

    public function update(int $id, array $data): bool
    {
        if (!empty($data['password'])) {
            $stmt = $this->db->prepare("
                UPDATE enrollment_committees
                SET full_name = :full_name, email = :email, department = :department,
                    password = :password
                WHERE id = :id
            ");
            return $stmt->execute([
                ':full_name'      => $data['full_name'],
                ':email'          => $data['email'],
                ':department'     => $data['department'],
                ':password'       => password_hash($data['password'], PASSWORD_DEFAULT),
                ':id'             => $id,
            ]);
        } else {
            $stmt = $this->db->prepare("
                UPDATE enrollment_committees
                SET full_name = :full_name, email = :email, department = :department
                WHERE id = :id
            ");
            return $stmt->execute([
                ':full_name'  => $data['full_name'],
                ':email'      => $data['email'],
                ':department' => $data['department'],
                ':id'         => $id,
            ]);
        }
    }

    public function updatePassword(int $id, string $hashedPassword): bool
    {
        $stmt = $this->db->prepare("UPDATE enrollment_committees SET password = :password WHERE id = :id");
        return $stmt->execute([
            ':password' => $hashedPassword,
            ':id'       => $id,
        ]);
    }


    /**
     * Get all enrollment committee members NOT yet assigned to the given clearance.
     */
    public function findUnassigned(int $clearanceId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM enrollment_committees
            WHERE id NOT IN (
                SELECT enrollment_committee_id FROM clearance_enrollment_committees WHERE clearance_id = ?
            )
            ORDER BY full_name ASC
        ");
        $stmt->execute([$clearanceId]);
        return $stmt->fetchAll();
    }

    /**
     * Find an enrollment committee member by their email address.
     *
     * @param string $email
     * @return array|false
     */
    public function findByEmail(string $email): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
}
