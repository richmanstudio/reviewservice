<?php

declare(strict_types=1);

namespace Review;

class ReviewService
{
    public function __construct(
        private readonly ClientRepository $clientRepository,
        private readonly ReviewRepository $reviewRepository,
        private readonly Validator        $validator,
    ) {}

    public function resolveClient(mixed $rawClientId): array
    {
        $clientId = $this->validator->validateClientId($rawClientId);

        if ($this->validator->hasErrors()) {
            return ['valid' => false, 'clientId' => 0, 'client' => null, 'error' => 'Неверный идентификатор клиента.'];
        }

        $client = $this->clientRepository->findById($clientId);

        if ($client === null) {
            return ['valid' => false, 'clientId' => $clientId, 'client' => null, 'error' => null];
        }

        return ['valid' => true, 'clientId' => $clientId, 'client' => $client, 'error' => null];
    }

    public function submitReview(int $clientId, mixed $rawRating, mixed $rawComment): array
    {
        $rating  = $this->validator->validateRating($rawRating);
        $comment = $this->validator->validateComment($rawComment);

        if ($this->validator->hasErrors()) {
            return ['success' => false, 'reviewId' => 0, 'errors' => $this->validator->getErrors()];
        }

        if (!$this->clientRepository->exists($clientId)) {
            return ['success' => false, 'reviewId' => 0, 'errors' => ['client_id' => 'Клиент не найден.']];
        }

        $reviewId = $this->reviewRepository->save($clientId, $rating, $comment);

        return ['success' => true, 'reviewId' => $reviewId, 'errors' => []];
    }
}
