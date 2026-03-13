<?php

declare(strict_types=1);

namespace Review\Tests;

use PDO;
use PHPUnit\Framework\TestCase;
use Review\ClientRepository;

class ClientRepositoryTest extends TestCase
{
    private PDO $pdo;
    private ClientRepository $repo;

    protected function setUp(): void
    {
        if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('PDO SQLite driver is not available.');
        }

        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $this->pdo->exec(
            'CREATE TABLE clients (
                id         INTEGER PRIMARY KEY,
                name       VARCHAR(255) NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )'
        );

        $this->pdo->exec("INSERT INTO clients (id, name) VALUES (1, 'Alice'), (2, 'Bob')");

        $this->repo = new ClientRepository($this->pdo);
    }

    public function testFindByIdReturnsClientWhenExists(): void
    {
        $client = $this->repo->findById(1);

        $this->assertNotNull($client);
        $this->assertSame('1', (string) $client['id']);
        $this->assertSame('Alice', $client['name']);
    }

    public function testFindByIdReturnsNullWhenNotFound(): void
    {
        $client = $this->repo->findById(999);
        $this->assertNull($client);
    }

    public function testExistsReturnsTrueForKnownClient(): void
    {
        $this->assertTrue($this->repo->exists(2));
    }

    public function testExistsReturnsFalseForUnknownClient(): void
    {
        $this->assertFalse($this->repo->exists(500));
    }
}
