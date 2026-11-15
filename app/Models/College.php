<?php

class College extends Model
{
    protected string $table = 'colleges';

    public function __construct(?PDO $db = null)
    {
        if ($db !== null) {
            $this->db = $db;
        } else {
            parent::__construct();
        }
    }

    public function getAll(): array
    {
        return $this->db->query("SELECT * FROM colleges ORDER BY name ASC")->fetchAll();
    }

    public function create(string $name): bool
    {
        $stmt = $this->db->prepare("INSERT INTO colleges (name) VALUES (?)");
        return $stmt->execute([trim($name)]);
    }

    public function delete(int $id): bool
    {
        return $this->db->prepare("DELETE FROM colleges WHERE id = ?")->execute([$id]);
    }
}

