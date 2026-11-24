<?php

class Office extends Model
{
    protected string $table = 'offices';

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
        return $this->db->query("SELECT * FROM offices ORDER BY name ASC")->fetchAll();
    }

    public function create(string $name): bool
    {
        $stmt = $this->db->prepare("INSERT INTO offices (name) VALUES (?)");
        return $stmt->execute([trim($name)]);
    }

    public function update(int $id, string $name): bool
    {
        $stmt = $this->db->prepare("UPDATE offices SET name = ? WHERE id = ?");
        return $stmt->execute([trim($name), $id]);
    }

    public function delete(int $id): bool
    {
        return $this->db->prepare("DELETE FROM offices WHERE id = ?")->execute([$id]);
    }
}

