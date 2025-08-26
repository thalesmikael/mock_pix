<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use App\Domain\Enums\PixStatus;
use DateTimeImmutable;
use PDO;
use PDOException;

class PixModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function insert(array $data): Pix
    {
        $sql = "INSERT INTO pix (external_id, payer_email, amount, type, status, origin, created_at) 
                VALUES (:external_id, :payer_email, :amount, :type, :status, :origin, :created_at)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'external_id' => $data['external_id'],
            'payer_email' => $data['payer_email'],
            'amount' => $data['amount'],
            'type' => $data['type'],
            'status' => $data['status'],
            'origin' => $data['origin'],
            'created_at' => $data['created_at'],
        ]);

        $id = (int) $this->pdo->lastInsertId();
        $data['id'] = $id;

        return Pix::fromArray($data);
    }

    public function findById(int $id): ?Pix
    {
        $sql = "SELECT * FROM pix WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);

        $data = $stmt->fetch();
        if (!$data) {
            return null;
        }

        return Pix::fromArray($data);
    }

    public function findByExternalId(string $externalId): ?Pix
    {
        $sql = "SELECT * FROM pix WHERE external_id = :external_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['external_id' => $externalId]);

        $data = $stmt->fetch();
        if (!$data) {
            return null;
        }

        return Pix::fromArray($data);
    }

    public function update(Pix $pix): void
    {
        $sql = "UPDATE pix SET 
                status = :status, 
                approved_at = :approved_at, 
                expired_at = :expired_at 
                WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'id' => $pix->getId(),
            'status' => $pix->getStatus()->value,
            'approved_at' => $pix->getApprovedAt() ? $pix->getApprovedAt()->format('Y-m-d H:i:s') : null,
            'expired_at' => $pix->getExpiredAt() ? $pix->getExpiredAt()->format('Y-m-d H:i:s') : null,
        ]);
    }

    public function totalsForDate(DateTimeImmutable $date): array
    {
        $dateStr = $date->format('Y-m-d');

        // Totais aprovados
        $approvedSql = "SELECT COUNT(*) as count, SUM(amount) as total 
                       FROM pix 
                       WHERE status = :status AND DATE(approved_at) = :date";
        
        $stmt = $this->pdo->prepare($approvedSql);
        $stmt->execute([
            'status' => PixStatus::APROVADO->value,
            'date' => $dateStr
        ]);
        $approved = $stmt->fetch();

        // Totais expirados
        $expiredSql = "SELECT COUNT(*) as count 
                      FROM pix 
                      WHERE status = :status AND DATE(expired_at) = :date";
        
        $stmt = $this->pdo->prepare($expiredSql);
        $stmt->execute([
            'status' => PixStatus::EXPIRADO->value,
            'date' => $dateStr
        ]);
        $expired = $stmt->fetch();

        return [
            'approved' => [
                'count' => (int) ($approved['count'] ?? 0),
                'total' => (float) ($approved['total'] ?? 0.0)
            ],
            'expired' => [
                'count' => (int) ($expired['count'] ?? 0)
            ],
            'date' => $dateStr
        ];
    }
}
