<?php

declare(strict_types=1);

namespace Review;

use PDO;

class ClientRepository
{
    public function __construct(private readonly PDO $pdo) {}

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, name, created_at FROM clients WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    public function exists(int $id): bool
    {
        return $this->findById($id) !== null;
    }
}
