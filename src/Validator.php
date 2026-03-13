<?php

declare(strict_types=1);

namespace Review;

class Validator
{
    private array $errors = [];

    public function validateClientId(mixed $value): int
    {
        $this->errors = [];

        if ($value === null || $value === '') {
            $this->errors['client_id'] = 'Параметр client_id обязателен.';
            return 0;
        }

        if (!ctype_digit((string) $value)) {
            $this->errors['client_id'] = 'Параметр client_id должен быть целым числом.';
            return 0;
        }

        $id = (int) $value;

        if ($id <= 0) {
            $this->errors['client_id'] = 'Параметр client_id должен быть положительным числом.';
            return 0;
        }

        return $id;
    }

    public function validateRating(mixed $value): int
    {
        if ($value === null || $value === '') {
            $this->errors['rating'] = 'Оценка обязательна.';
            return 0;
        }

        if (!ctype_digit((string) $value)) {
            $this->errors['rating'] = 'Оценка должна быть целым числом.';
            return 0;
        }

        $rating = (int) $value;

        if ($rating < 1 || $rating > 5) {
            $this->errors['rating'] = 'Оценка должна быть от 1 до 5.';
            return 0;
        }

        return $rating;
    }

    public function validateComment(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $comment = trim((string) $value);

        if (mb_strlen($comment) > 2000) {
            $this->errors['comment'] = 'Комментарий не должен превышать 2000 символов.';
            return null;
        }

        return $comment !== '' ? $comment : null;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }
}
