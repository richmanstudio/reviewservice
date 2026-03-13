<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Review\Database;
use Review\ClientRepository;
use Review\ReviewRepository;
use Review\Validator;
use Review\ReviewService;

$dbConfig = require __DIR__ . '/../config/database.php';
Database::configure($dbConfig);

$pdo    = Database::getInstance();
$service = new ReviewService(
    new ClientRepository($pdo),
    new ReviewRepository($pdo),
    new Validator()
);

$migrationSql = file_get_contents(__DIR__ . '/../migrations/001_create_tables.sql');
foreach (array_filter(array_map('trim', explode(';', $migrationSql))) as $stmt) {
    $pdo->exec($stmt);
}

$rawClientId = $_GET['client_id'] ?? null;
$resolution  = $service->resolveClient($rawClientId);

$clientValid = $resolution['valid'];
$clientId    = $resolution['clientId'];
$client      = $resolution['client'];

$submitted   = false;
$submitErrors = [];

if ($clientValid && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $service->submitReview(
        $clientId,
        $_POST['rating']  ?? null,
        $_POST['comment'] ?? null
    );

    if ($result['success']) {
        $submitted = true;
    } else {
        $submitErrors = $result['errors'];
    }
}

function h(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оставьте отзыв</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<main class="screen">

<?php if (!$clientValid): ?>

    <section class="card card--stub" role="alert" aria-live="assertive">
        <div class="stub-icon">
            <?= svgIconWarning() ?>
        </div>
        <h1 class="stub-title">Ссылка на голосование недоступна</h1>
        <p class="stub-text">Свяжитесь с нами по телефону</p>
    </section>

<?php elseif ($submitted): ?>

    <section class="card card--thank" role="status" aria-live="polite">
        <div class="thank-icon">
            <?= svgIconCheck() ?>
        </div>
        <h1 class="thank-title">Спасибо за отзыв!</h1>
        <p class="thank-text">Ваша оценка принята и поможет нам стать лучше.</p>
    </section>

<?php else: ?>

    <section class="card card--form">
        <h1 class="form-title">Оцените качество&nbsp;обслуживания</h1>

        <form method="POST"
              action="?client_id=<?= h($clientId) ?>"
              class="review-form"
              novalidate>

            <fieldset class="rating-fieldset">
                <legend class="rating-legend">Ваша оценка</legend>
                <div class="rating-row" role="group" aria-label="Оценка от 1 до 5">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <label class="rating-label" title="<?= $i ?>">
                            <input
                                type="radio"
                                name="rating"
                                value="<?= $i ?>"
                                class="rating-input"
                                aria-label="Оценка <?= $i ?>"
                                <?= isset($_POST['rating']) && (int)$_POST['rating'] === $i ? 'checked' : '' ?>
                            >
                            <span class="rating-btn" data-value="<?= $i ?>">
                                <?= svgIconStar() ?>
                                <span class="rating-num"><?= $i ?></span>
                            </span>
                        </label>
                    <?php endfor; ?>
                </div>
                <?php if (isset($submitErrors['rating'])): ?>
                    <p class="field-error" role="alert"><?= h($submitErrors['rating']) ?></p>
                <?php endif; ?>
            </fieldset>

            <div class="comment-field">
                <label for="comment" class="comment-label">
                    <?= svgIconComment() ?>
                    Оставьте комментарий к отзыву
                    <span class="optional">(необязательно)</span>
                </label>
                <textarea
                    id="comment"
                    name="comment"
                    class="comment-textarea"
                    rows="4"
                    maxlength="2000"
                    placeholder="Напишите, что вам понравилось или что можно улучшить..."
                    aria-describedby="comment-counter"
                ><?= h($_POST['comment'] ?? '') ?></textarea>
                <div class="comment-footer">
                    <span id="comment-counter" class="char-counter" aria-live="polite">0 / 2000</span>
                </div>
                <?php if (isset($submitErrors['comment'])): ?>
                    <p class="field-error" role="alert"><?= h($submitErrors['comment']) ?></p>
                <?php endif; ?>
            </div>

            <button type="submit" class="submit-btn">
                Отправить отзыв
                <?= svgIconSend() ?>
            </button>

        </form>
    </section>

<?php endif; ?>

</main>

<script src="assets/app.js"></script>
</body>
</html>
<?php

function svgIconStar(): string
{
    return <<<SVG
    <svg class="icon icon-star" viewBox="0 0 24 24" fill="none"
         xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
        <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"
              stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
    SVG;
}

function svgIconCheck(): string
{
    return <<<SVG
    <svg class="icon icon-check" viewBox="0 0 24 24" fill="none"
         xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.8"/>
        <path d="M8 12.5L11 15.5L16 9.5" stroke="currentColor" stroke-width="1.8"
              stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
    SVG;
}

function svgIconWarning(): string
{
    return <<<SVG
    <svg class="icon icon-warning" viewBox="0 0 24 24" fill="none"
         xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"
              stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        <line x1="12" y1="9" x2="12" y2="13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
        <line x1="12" y1="17" x2="12.01" y2="17" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
    </svg>
    SVG;
}

function svgIconComment(): string
{
    return <<<SVG
    <svg class="icon icon-comment" viewBox="0 0 24 24" fill="none"
         xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"
              stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
    SVG;
}

function svgIconSend(): string
{
    return <<<SVG
    <svg class="icon icon-send" viewBox="0 0 24 24" fill="none"
         xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
        <line x1="22" y1="2" x2="11" y2="13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
        <polygon points="22 2 15 22 11 13 2 9 22 2" stroke="currentColor" stroke-width="1.8"
                 stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
    SVG;
}
