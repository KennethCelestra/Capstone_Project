<?php

class Adviser extends Model
{
    protected string $table = 'advisers';

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO advisers (full_name, email, department, password, plain_password)
            VALUES (:full_name, :email, :department, :password, :plain_password)
        ");
        return $stmt->execute([
            ':full_name'      => $data['full_name'],
            ':email'          => $data['email'],
            ':department'     => $data['department'],
            ':password'       => password_hash($data['password'], PASSWORD_DEFAULT),
            ':plain_password' => $data['password'],
        ]);
    }

    public function update(int $id, array $data): bool
    {
        if (!empty($data['password'])) {
            $stmt = $this->db->prepare("
                UPDATE advisers
                SET full_name = :full_name, email = :email, department = :department,
                    password = :password, plain_password = :plain_password
                WHERE id = :id
            ");
            return $stmt->execute([
                ':full_name'      => $data['full_name'],
                ':email'          => $data['email'],
                ':department'     => $data['department'],
                ':password'       => password_hash($data['password'], PASSWORD_DEFAULT),
                ':plain_password' => $data['password'],
                ':id'             => $id,
            ]);
        } else {
            $stmt = $this->db->prepare("
                UPDATE advisers
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

    public function findByEmail(string $email): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM advisers WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    /**
     * Get all advisers NOT yet assigned to the given clearance.
     */
    public function findUnassigned(int $clearanceId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM advisers
            WHERE id NOT IN (
                SELECT adviser_id FROM clearance_advisers WHERE clearance_id = ?
            )
            ORDER BY full_name ASC
        ");
        $stmt->execute([$clearanceId]);
        return $stmt->fetchAll();
    }
}
