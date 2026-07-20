<?php

class Signatory extends Model
{
    protected string $table = 'signatories';

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO signatories (full_name, email, office, password, temp_password, scope_type, scope_value)
            VALUES (:full_name, :email, :office, :password, :temp_password, :scope_type, :scope_value)
        ");
        return $stmt->execute([
            ':full_name'      => $data['full_name'],
            ':email'          => $data['email'],
            ':office'         => $data['office'],
            ':password'       => password_hash($data['password'], PASSWORD_DEFAULT),
            ':temp_password'  => $data['password'],
            ':scope_type'     => $data['scope_type'] ?? null,
            ':scope_value'    => $data['scope_value'] ?? null,
        ]);
    }

    public function update(int $id, array $data): bool
    {
        if (!empty($data['password'])) {
            $stmt = $this->db->prepare("
                UPDATE signatories
                SET full_name = :full_name, email = :email, office = :office,
                    password = :password, temp_password = :temp_password,
                    scope_type = :scope_type, scope_value = :scope_value
                WHERE id = :id
            ");
            return $stmt->execute([
                ':full_name'      => $data['full_name'],
                ':email'          => $data['email'],
                ':office'         => $data['office'],
                ':password'       => password_hash($data['password'], PASSWORD_DEFAULT),
                ':temp_password'  => $data['password'],
                ':scope_type'     => $data['scope_type'] ?? null,
                ':scope_value'    => $data['scope_value'] ?? null,
                ':id'             => $id,
            ]);
        } else {
            $stmt = $this->db->prepare("
                UPDATE signatories
                SET full_name = :full_name, email = :email, office = :office,
                    scope_type = :scope_type, scope_value = :scope_value
                WHERE id = :id
            ");
            return $stmt->execute([
                ':full_name'   => $data['full_name'],
                ':email'       => $data['email'],
                ':office'      => $data['office'],
                ':scope_type'  => $data['scope_type'] ?? null,
                ':scope_value' => $data['scope_value'] ?? null,
                ':id'          => $id,
            ]);
        }
    }

    public function updatePassword(int $id, string $hashedPassword): bool
    {
        $stmt = $this->db->prepare("UPDATE signatories SET password = :password, temp_password = NULL WHERE id = :id");
        return $stmt->execute([
            ':password' => $hashedPassword,
            ':id'       => $id,
        ]);
    }


    /**
     * Get all signatories NOT yet assigned to the given clearance.
     */
    public function findUnassigned(int $clearanceId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM signatories
            WHERE id NOT IN (
                SELECT signatory_id FROM clearance_signatories WHERE clearance_id = ?
            )
            ORDER BY full_name ASC
        ");
        $stmt->execute([$clearanceId]);
        return $stmt->fetchAll();
    }

    /**
     * Find a signatory by their email address.
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
