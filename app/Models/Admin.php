<?php

class Admin extends Model
{
    protected string $table = 'admins';

    /**
     * Find an admin by their email address.
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

    public function updatePassword(int $id, string $hashedPassword): bool
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET password = :password WHERE id = :id");
        return $stmt->execute([
            ':password' => $hashedPassword,
            ':id'       => $id,
        ]);
    }
}
