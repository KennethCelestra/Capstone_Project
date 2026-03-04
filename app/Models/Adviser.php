<?php

class Adviser extends Model
{
    protected string $table = 'advisers';

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO advisers (full_name, email, department, password)
            VALUES (:full_name, :email, :department, :password)
        ");
        return $stmt->execute([
            ':full_name' => $data['full_name'],
            ':email' => $data['email'],
            ':department' => $data['department'],
            ':password' => password_hash($data['password'], PASSWORD_DEFAULT),
        ]);
    }

    public function findByEmail(string $email): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM advisers WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
}
