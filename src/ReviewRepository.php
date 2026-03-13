<?php

declare(strict_types=1);

namespace Review;

use PDO;

class ReviewRepository
{
    public function __construct(private readonly PDO $pdo) {}

    public function save(int $clientId, int $rating, ?string $comment): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO reviews (client_id, rating, comment) VALUES (:client_id, :rating, :comment)'
        );

        $stmt->execute([
            ':client_id' => $clientId,
            ':rating'    => $rating,
            ':comment'   => $comment !== '' ? $comment : null,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findByClientId(int $clientId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, client_id, rating, comment, created_at FROM reviews WHERE client_id = :client_id ORDER BY created_at DESC'
        );
        $stmt->execute([':client_id' => $clientId]);

        return $stmt->fetchAll();
    }
}
