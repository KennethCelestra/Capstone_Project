<?php

class ClearanceApproval extends Model
{
    protected string $table = 'clearance_approvals';

    /**
     * Create or reset a per-college approval request to 'pending'.
     */
    public function createOrResetRequest(int $clearanceId, string $college, int $approverId, string $approverName): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO clearance_approvals (clearance_id, college, approver_id, approver_name, status, note, decided_at)
            VALUES (?, ?, ?, ?, 'pending', NULL, NULL)
            ON DUPLICATE KEY UPDATE
                approver_id   = VALUES(approver_id),
                approver_name = VALUES(approver_name),
                status        = 'pending',
                note          = NULL,
                decided_at    = NULL,
                created_at    = CURRENT_TIMESTAMP
        ");
        return $stmt->execute([$clearanceId, $college, $approverId, $approverName]);
    }

    /**
     * Pending approval requests for a specific Dean (signatory).
     * Includes per-college student count.
     */
    public function getPendingForSignatory(int $signatoryId): array
    {
        $stmt = $this->db->prepare("
            SELECT ca.*, c.name AS clearance_name, c.school_year, c.type AS clearance_type,
                   (
                       SELECT COUNT(*)
                       FROM clearance_students cst
                       JOIN students st ON st.id = cst.student_id
                       WHERE cst.clearance_id = c.id
                         AND (ca.college = 'ALL' OR st.college = ca.college)
                   ) AS student_count
            FROM clearance_approvals ca
            JOIN clearances c ON c.id = ca.clearance_id
            WHERE ca.approver_id = ? AND ca.status = 'pending' AND c.archived = 0
            ORDER BY ca.created_at DESC, ca.college ASC
        ");
        $stmt->execute([$signatoryId]);
        return $stmt->fetchAll();
    }

    /**
     * Get all per-college approval rows for a clearance.
     */
    public function getForClearance(int $clearanceId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM clearance_approvals
            WHERE clearance_id = ?
            ORDER BY college ASC
        ");
        $stmt->execute([$clearanceId]);
        return $stmt->fetchAll();
    }

    /**
     * Approve a pending request owned by the given signatory (Dean).
     */
    public function approveForSignatory(int $approvalId, int $signatoryId, ?string $note = null): bool
    {
        $stmt = $this->db->prepare("
            UPDATE clearance_approvals
            SET status = 'approved', note = ?, decided_at = NOW()
            WHERE id = ? AND approver_id = ? AND status = 'pending'
        ");
        $stmt->execute([$note, $approvalId, $signatoryId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Reject a pending request owned by the given signatory (Dean).
     */
    public function rejectForSignatory(int $approvalId, int $signatoryId, string $note): bool
    {
        $stmt = $this->db->prepare("
            UPDATE clearance_approvals
            SET status = 'rejected', note = ?, decided_at = NOW()
            WHERE id = ? AND approver_id = ? AND status = 'pending'
        ");
        $stmt->execute([$note, $approvalId, $signatoryId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Check if a specific student's college is approved for a clearance.
     * Returns true if no approval records exist (legacy direct-start) or if the student's college is approved.
     */
    public function isCollegeApproved(int $clearanceId, string $college): bool
    {
        $stmt = $this->db->prepare("
            SELECT
                (SELECT COUNT(*) FROM clearance_approvals WHERE clearance_id = ?) AS total_approvals,
                (SELECT COUNT(*) FROM clearance_approvals WHERE clearance_id = ? AND status = 'approved' AND (college = 'ALL' OR college = ?)) AS approved_match
        ");
        $stmt->execute([$clearanceId, $clearanceId, $college]);
        $row = $stmt->fetch();
        if (!$row) return false;
        return ((int)$row['total_approvals'] === 0) || ((int)$row['approved_match'] > 0);
    }
}
