<?php

class Signatory extends Model
{
    protected string $table = 'signatories';

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO signatories (full_name, email, office, password)
            VALUES (:full_name, :email, :office, :password)
        ");
        return $stmt->execute([
            ':full_name' => $data['full_name'],
            ':email' => $data['email'],
            ':office' => $data['office'],
            ':password' => password_hash($data['password'], PASSWORD_DEFAULT),
        ]);
    }

    public function findByEmail(string $email): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM signatories WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
}
