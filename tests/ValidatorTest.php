<?php

declare(strict_types=1);

namespace Review\Tests;

use PHPUnit\Framework\TestCase;
use Review\Validator;

class ValidatorTest extends TestCase
{
    private Validator $validator;

    protected function setUp(): void
    {
        $this->validator = new Validator();
    }

    public function testValidClientIdReturnsInt(): void
    {
        $result = $this->validator->validateClientId('42');
        $this->assertSame(42, $result);
        $this->assertFalse($this->validator->hasErrors());
    }

    public function testNullClientIdSetsError(): void
    {
        $this->validator->validateClientId(null);
        $this->assertTrue($this->validator->hasErrors());
        $this->assertArrayHasKey('client_id', $this->validator->getErrors());
    }

    public function testEmptyClientIdSetsError(): void
    {
        $this->validator->validateClientId('');
        $this->assertTrue($this->validator->hasErrors());
    }

    public function testNonNumericClientIdSetsError(): void
    {
        $this->validator->validateClientId('abc');
        $this->assertTrue($this->validator->hasErrors());
    }

    public function testZeroClientIdSetsError(): void
    {
        $this->validator->validateClientId('0');
        $this->assertTrue($this->validator->hasErrors());
    }

    public function testNegativeClientIdSetsError(): void
    {
        $this->validator->validateClientId('-5');
        $this->assertTrue($this->validator->hasErrors());
    }

    public function testValidRatingReturnsInt(): void
    {
        foreach ([1, 2, 3, 4, 5] as $rating) {
            $result = $this->validator->validateRating((string) $rating);
            $this->assertSame($rating, $result);
            $this->assertFalse($this->validator->hasErrors());
        }
    }

    public function testRatingBelowOneSetsError(): void
    {
        $this->validator->validateRating('0');
        $this->assertTrue($this->validator->hasErrors());
        $this->assertArrayHasKey('rating', $this->validator->getErrors());
    }

    public function testRatingAboveFiveSetsError(): void
    {
        $this->validator->validateRating('6');
        $this->assertTrue($this->validator->hasErrors());
    }

    public function testNullRatingSetsError(): void
    {
        $this->validator->validateRating(null);
        $this->assertTrue($this->validator->hasErrors());
    }

    public function testNonNumericRatingSetsError(): void
    {
        $this->validator->validateRating('three');
        $this->assertTrue($this->validator->hasErrors());
    }

    public function testValidCommentReturnsString(): void
    {
        $result = $this->validator->validateComment('Great service!');
        $this->assertSame('Great service!', $result);
        $this->assertFalse($this->validator->hasErrors());
    }

    public function testEmptyCommentReturnsNull(): void
    {
        $result = $this->validator->validateComment('');
        $this->assertNull($result);
        $this->assertFalse($this->validator->hasErrors());
    }

    public function testNullCommentReturnsNull(): void
    {
        $result = $this->validator->validateComment(null);
        $this->assertNull($result);
        $this->assertFalse($this->validator->hasErrors());
    }

    public function testWhitespaceOnlyCommentReturnsNull(): void
    {
        $result = $this->validator->validateComment('   ');
        $this->assertNull($result);
        $this->assertFalse($this->validator->hasErrors());
    }

    public function testCommentExceeding2000CharsSetsError(): void
    {
        $longComment = str_repeat('a', 2001);
        $this->validator->validateComment($longComment);
        $this->assertTrue($this->validator->hasErrors());
        $this->assertArrayHasKey('comment', $this->validator->getErrors());
    }

    public function testCommentOf2000CharsIsValid(): void
    {
        $comment = str_repeat('a', 2000);
        $result  = $this->validator->validateComment($comment);
        $this->assertSame($comment, $result);
        $this->assertFalse($this->validator->hasErrors());
    }
}
