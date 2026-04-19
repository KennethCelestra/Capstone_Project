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

    // count(), findAll(), findById(), delete() are all inherited from Model
}
