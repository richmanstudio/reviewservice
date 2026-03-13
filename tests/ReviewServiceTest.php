<?php

declare(strict_types=1);

namespace Review\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Review\ClientRepository;
use Review\ReviewRepository;
use Review\ReviewService;
use Review\Validator;

class ReviewServiceTest extends TestCase
{
    private ClientRepository&MockObject $clientRepo;
    private ReviewRepository&MockObject $reviewRepo;
    private ReviewService $service;

    protected function setUp(): void
    {
        $this->clientRepo = $this->createMock(ClientRepository::class);
        $this->reviewRepo = $this->createMock(ReviewRepository::class);
        $this->service    = new ReviewService($this->clientRepo, $this->reviewRepo, new Validator());
    }

    public function testResolveClientReturnsValidForExistingClient(): void
    {
        $this->clientRepo->method('findById')->with(1)->willReturn(['id' => 1, 'name' => 'Test']);

        $result = $this->service->resolveClient('1');

        $this->assertTrue($result['valid']);
        $this->assertSame(1, $result['clientId']);
        $this->assertNotNull($result['client']);
    }

    public function testResolveClientReturnsFalseForNonExistingClient(): void
    {
        $this->clientRepo->method('findById')->with(99)->willReturn(null);

        $result = $this->service->resolveClient('99');

        $this->assertFalse($result['valid']);
        $this->assertNull($result['client']);
    }

    public function testResolveClientReturnsFalseForInvalidId(): void
    {
        $result = $this->service->resolveClient('abc');

        $this->assertFalse($result['valid']);
    }

    public function testResolveClientReturnsFalseForNullId(): void
    {
        $result = $this->service->resolveClient(null);

        $this->assertFalse($result['valid']);
    }

    public function testSubmitReviewSuccessfully(): void
    {
        $this->clientRepo->method('exists')->with(1)->willReturn(true);
        $this->reviewRepo->method('save')->with(1, 5, 'Excellent!')->willReturn(42);

        $result = $this->service->submitReview(1, '5', 'Excellent!');

        $this->assertTrue($result['success']);
        $this->assertSame(42, $result['reviewId']);
        $this->assertEmpty($result['errors']);
    }

    public function testSubmitReviewWithoutComment(): void
    {
        $this->clientRepo->method('exists')->willReturn(true);
        $this->reviewRepo->method('save')->willReturn(1);

        $result = $this->service->submitReview(1, '3', null);

        $this->assertTrue($result['success']);
    }

    public function testSubmitReviewWithInvalidRating(): void
    {
        $result = $this->service->submitReview(1, '6', null);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('rating', $result['errors']);
    }

    public function testSubmitReviewWithMissingRating(): void
    {
        $result = $this->service->submitReview(1, null, null);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('rating', $result['errors']);
    }

    public function testSubmitReviewFailsWhenClientNotFound(): void
    {
        $this->clientRepo->method('exists')->with(999)->willReturn(false);

        $result = $this->service->submitReview(999, '4', null);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('client_id', $result['errors']);
    }

    public function testSubmitReviewSavesCorrectClientId(): void
    {
        $this->clientRepo->method('exists')->with(7)->willReturn(true);
        $this->reviewRepo
            ->expects($this->once())
            ->method('save')
            ->with(7, 4, null)
            ->willReturn(10);

        $result = $this->service->submitReview(7, '4', '');

        $this->assertTrue($result['success']);
    }
}
