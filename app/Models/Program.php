<?php

class Program extends Model
{
    protected string $table = 'programs';

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
        return $this->db->query("
            SELECT p.*, c.name AS college_name 
            FROM programs p 
            JOIN colleges c ON c.id = p.college_id 
            ORDER BY c.name ASC, p.name ASC
        ")->fetchAll();
    }

    public function getByCollege(int $collegeId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM programs WHERE college_id = ? ORDER BY name ASC");
        $stmt->execute([$collegeId]);
        return $stmt->fetchAll();
    }

    public function getByCollegeName(string $collegeName): array
    {
        $stmt = $this->db->prepare("
            SELECT p.* 
            FROM programs p 
            JOIN colleges c ON c.id = p.college_id 
            WHERE c.name = ? 
            ORDER BY p.name ASC
        ");
        $stmt->execute([$collegeName]);
        return $stmt->fetchAll();
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO programs (college_id, name, code) 
            VALUES (:college_id, :name, :code)
        ");
        return $stmt->execute([
            ':college_id' => $data['college_id'],
            ':name'       => trim($data['name']),
            ':code'       => trim($data['code'])
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE programs 
            SET college_id = :college_id, name = :name, code = :code 
            WHERE id = :id
        ");
        return $stmt->execute([
            ':college_id' => $data['college_id'],
            ':name'       => trim($data['name']),
            ':code'       => trim($data['code']),
            ':id'         => $id
        ]);
    }

    public function delete(int $id): bool
    {
        return $this->db->prepare("DELETE FROM programs WHERE id = ?")->execute([$id]);
    }
}

