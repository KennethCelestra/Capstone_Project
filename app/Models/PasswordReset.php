<?php

class PasswordReset extends Model
{
    protected string $table = 'password_resets';

    public function createToken(string $email, string $token): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE email = ?");
        $stmt->execute([$email]);

        $stmt = $this->db->prepare("INSERT INTO {$this->table} (email, token) VALUES (?, ?)");
        return $stmt->execute([$email, $token]);
    }

    public function verifyToken(string $token): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE token = ? AND created_at > (NOW() - INTERVAL 1 HOUR)");
        $stmt->execute([$token]);
        return $stmt->fetch();
    }

    public function deleteToken(string $token): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE token = ?");
        return $stmt->execute([$token]);
    }
}
