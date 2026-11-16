<?php

class ClearanceApproval extends Model
{
    protected string $table = 'clearance_approvals';

    public function createRequest(int $clearanceId, int $approverId, string $approverName): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO clearance_approvals (clearance_id, approver_id, approver_name, status)
            VALUES (?, ?, ?, 'pending')
        ");
        $stmt->execute([$clearanceId, $approverId, $approverName]);
        return (int)$this->db->lastInsertId();
    }

    public function getPendingForSignatory(int $signatoryId): array
    {
        $stmt = $this->db->prepare("
            SELECT ca.*, c.name AS clearance_name, c.school_year, c.type AS clearance_type,
                   (SELECT COUNT(*) FROM clearance_students WHERE clearance_id = c.id) AS student_count
            FROM clearance_approvals ca
            JOIN clearances c ON c.id = ca.clearance_id
            WHERE ca.approver_id = ? AND ca.status = 'pending' AND c.archived = 0
            ORDER BY ca.created_at DESC
        ");
        $stmt->execute([$signatoryId]);
        return $stmt->fetchAll();
    }

    public function getLatestForClearance(int $clearanceId): array|false
    {
        $stmt = $this->db->prepare("
            SELECT * FROM clearance_approvals
            WHERE clearance_id = ?
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute([$clearanceId]);
        return $stmt->fetch();
    }

    public function approve(int $approvalId, ?string $note = null): bool
    {
        $stmt = $this->db->prepare("
            UPDATE clearance_approvals
            SET status = 'approved', note = ?, decided_at = NOW()
            WHERE id = ? AND status = 'pending'
        ");
        return $stmt->execute([$note, $approvalId]);
    }

    public function reject(int $approvalId, string $note): bool
    {
        $stmt = $this->db->prepare("
            UPDATE clearance_approvals
            SET status = 'rejected', note = ?, decided_at = NOW()
            WHERE id = ? AND status = 'pending'
        ");
        return $stmt->execute([$note, $approvalId]);
    }
}
