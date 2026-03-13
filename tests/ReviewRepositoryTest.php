<?php

declare(strict_types=1);

namespace Review\Tests;

use PDO;
use PHPUnit\Framework\TestCase;
use Review\ReviewRepository;

class ReviewRepositoryTest extends TestCase
{
    private PDO $pdo;
    private ReviewRepository $repo;

    protected function setUp(): void
    {
        if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('PDO SQLite driver is not available.');
        }

        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $this->pdo->exec(
            'CREATE TABLE reviews (
                id         INTEGER PRIMARY KEY AUTOINCREMENT,
                client_id  INTEGER NOT NULL,
                rating     INTEGER NOT NULL,
                comment    TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )'
        );

        $this->repo = new ReviewRepository($this->pdo);
    }

    public function testSaveReturnsInsertedId(): void
    {
        $id = $this->repo->save(1, 5, 'Great!');
        $this->assertSame(1, $id);
    }

    public function testSaveWithNullComment(): void
    {
        $id = $this->repo->save(1, 3, null);
        $this->assertGreaterThan(0, $id);

        $rows = $this->repo->findByClientId(1);
        $this->assertNull($rows[0]['comment']);
    }

    public function testSaveWithEmptyStringTreatedAsNull(): void
    {
        $this->repo->save(1, 4, '');
        $rows = $this->repo->findByClientId(1);
        $this->assertNull($rows[0]['comment']);
    }

    public function testFindByClientIdReturnsAllReviewsForClient(): void
    {
        $this->repo->save(1, 5, 'Excellent');
        $this->repo->save(1, 3, null);
        $this->repo->save(2, 4, 'Good');

        $rows = $this->repo->findByClientId(1);
        $this->assertCount(2, $rows);
    }

    public function testFindByClientIdReturnsEmptyForNoReviews(): void
    {
        $rows = $this->repo->findByClientId(42);
        $this->assertSame([], $rows);
    }

    public function testSavedReviewContainsCorrectFields(): void
    {
        $this->repo->save(5, 4, 'Nice');

        $rows = $this->repo->findByClientId(5);
        $this->assertSame('5', (string) $rows[0]['client_id']);
        $this->assertSame('4', (string) $rows[0]['rating']);
        $this->assertSame('Nice', $rows[0]['comment']);
    }
}
