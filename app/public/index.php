<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Database;
use App\Logger;
use App\Model\Demo;

// 1. Eloquent
Database::bootEloquent();

// 2. Логгер
$logger = Logger::get();

// 3. Логируем входящий HTTP-запрос
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$requestUri    = $_SERVER['REQUEST_URI']    ?? '/';
$requestBody   = file_get_contents('php://input') ?: '';

$logger->info('Incoming HTTP request', [
    'method'  => $requestMethod,
    'uri'     => $requestUri,
    'body'    => $requestBody,
    'headers' => function_exists('getallheaders') ? getallheaders() : [],
]);

// 4. POST — вставка через Eloquent, редирект
if ($requestMethod === 'POST' && isset($_POST['note'])) {
    try {
        Demo::create(['note' => (string) $_POST['note']]);
    } catch (\Throwable $e) {
        $logger->error('Insert failed', ['exception' => $e->getMessage()]);
    }
    header('Location: /');
    exit;
}

// 5. Читаем данные через Eloquent
$dbVersion = null;
$dbError   = null;
$rows      = [];

try {
    $dbVersion = (string) Database::connect()->query('SELECT version()')->fetchColumn();
    $rows = Demo::query()->orderByDesc('id')->get()->toArray();
} catch (\Throwable $e) {
    $dbError = $e->getMessage();
}

$phpVersion  = PHP_VERSION;
$hasPdoPgsql = extension_loaded('pdo_pgsql');

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// 6. Собираем тело ответа в буфер
ob_start();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Local Zoo — Eloquent + Monolog</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 720px; margin: 2rem auto; padding: 0 1rem; }
        table { border-collapse: collapse; width: 100%; margin: 1rem 0; }
        th, td { border: 1px solid #ddd; padding: .4rem .6rem; }
        th { background: #f0f0f0; }
        .ok { color: #0a7a2f; } .fail { color: #b00020; }
    </style>
</head>
<body>
    <h1>Local Zoo — Eloquent + Monolog</h1>

    <ul>
        <li>PHP: <?= h($phpVersion) ?></li>
        <li>pdo_pgsql: <span class="<?= $hasPdoPgsql ? 'ok' : 'fail' ?>"><?= $hasPdoPgsql ? 'ok' : 'нет' ?></span></li>
        <li>DB: <span class="<?= $dbError ? 'fail' : 'ok' ?>"><?= h($dbError ?? $dbVersion ?? '') ?></span></li>
    </ul>

    <h2>demo (Eloquent)</h2>
    <?php if ($rows): ?>
        <table>
            <tr><th>id</th><th>создано</th><th>note</th></tr>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= h((string) $row['id']) ?></td>
                    <td><?= h((string) $row['created_at']) ?></td>
                    <td><?= h((string) $row['note']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Нет данных.</p>
    <?php endif; ?>

    <form method="post" action="/">
        <input type="text" name="note" required maxlength="500" placeholder="Новая запись">
        <button type="submit">Добавить</button>
    </form>
</body>
</html>
<?php
$responseBody = ob_get_clean();

// 7. Логируем исходящий ответ
$logger->info('Outgoing HTTP response', [
    'status' => http_response_code(),
    'body'   => $responseBody,
]);

// 8. Отдаём клиенту
echo $responseBody;