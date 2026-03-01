<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

if (is_readable(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

if (class_exists(\Dotenv\Dotenv::class) && is_readable(__DIR__ . '/.env')) {
    \Dotenv\Dotenv::createUnsafeImmutable(__DIR__)->safeLoad();
}

$env = static function (string $key, ?string $default = null): ?string {
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

    if ($value === false || $value === null || $value === '') {
        return $default;
    }

    return (string) $value;
};

$secret = (string) ($env('GITHUB_WEBHOOK_SECRET', '') ?? '');
$secretFile = __DIR__ . '/storage/app/github-webhook-secret.txt';
$repoName = (string) ($env('GITHUB_WEBHOOK_REPO', 'newukmtv') ?? 'newukmtv');
$branch = (string) ($env('GITHUB_WEBHOOK_BRANCH', 'master') ?? 'master');
$runBuild = filter_var((string) ($env('GITHUB_WEBHOOK_RUN_BUILD', '0') ?? '0'), FILTER_VALIDATE_BOOLEAN);
$composerBin = (string) ($env('GITHUB_WEBHOOK_COMPOSER_BIN', 'composer') ?? 'composer');
$phpBin = (string) ($env('GITHUB_WEBHOOK_PHP_BIN', 'php') ?? 'php');
$npmBin = (string) ($env('GITHUB_WEBHOOK_NPM_BIN', 'npm') ?? 'npm');

if ($secret === '' && is_readable($secretFile)) {
    $secret = trim((string) file_get_contents($secretFile));
}

$repoDir = __DIR__;
$logFile = __DIR__ . '/storage/logs/webhook.log';
$deployQueueFile = __DIR__ . '/storage/app/deploy.pending';
$deployLockFile = __DIR__ . '/storage/app/deploy.lock';

$responded = false;

$respond = static function (int $statusCode, array $payload) use (&$responded): void {
    if ($responded) {
        return;
    }

    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $responded = true;
};

$log = static function (string $message) use ($logFile): void {
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
    @file_put_contents($logFile, $line, FILE_APPEND);
};

$isExecAvailable = static function (): bool {
    if (!function_exists('exec')) {
        return false;
    }

    $disabled = (string) ini_get('disable_functions');
    $blacklist = (string) ini_get('suhosin.executor.func.blacklist');
    $blocked = strtolower($disabled . ',' . $blacklist);
    $blocked = array_filter(array_map('trim', explode(',', $blocked)));

    return !in_array('exec', $blocked, true);
};

set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

register_shutdown_function(static function () use (&$responded, $respond, $log): void {
    $error = error_get_last();
    if (!$error) {
        return;
    }

    $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
    if (!in_array($error['type'] ?? null, $fatalTypes, true)) {
        return;
    }

    $log('FATAL: ' . ($error['message'] ?? 'unknown') . ' @ ' . ($error['file'] ?? '-') . ':' . ($error['line'] ?? '-'));

    if (!$responded) {
        $respond(500, [
            'ok' => false,
            'error' => 'Fatal error',
            'message' => (string) ($error['message'] ?? 'unknown'),
        ]);
    }
});

try {
    if ($secret === '') {
        $respond(500, [
            'ok' => false,
            'error' => 'Webhook secret is not configured',
        ]);
        exit;
    }

    $payload = file_get_contents('php://input') ?: '';
    $signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
    $event = $_SERVER['HTTP_X_GITHUB_EVENT'] ?? '';

    if ($payload === '') {
        $respond(400, ['ok' => false, 'error' => 'Empty payload']);
        exit;
    }

    if (!str_starts_with($signature, 'sha256=')) {
        $respond(401, ['ok' => false, 'error' => 'Missing signature']);
        exit;
    }

    $expected = 'sha256=' . hash_hmac('sha256', $payload, $secret);

    if (!hash_equals($expected, $signature)) {
        $respond(401, ['ok' => false, 'error' => 'Invalid signature']);
        exit;
    }

    $data = json_decode($payload, true);

    if (!is_array($data)) {
        parse_str($payload, $parsed);
        if (isset($parsed['payload']) && is_string($parsed['payload'])) {
            $data = json_decode($parsed['payload'], true);
        }
    }

    if (!is_array($data)) {
        $respond(400, ['ok' => false, 'error' => 'Invalid JSON payload']);
        exit;
    }

    if ($event === 'ping') {
        $respond(200, ['ok' => true, 'message' => 'pong']);
        exit;
    }

    $repo = (string) ($data['repository']['name'] ?? '');
    $ref = (string) ($data['ref'] ?? '');

    if ($repo !== $repoName) {
        $respond(200, ['ok' => true, 'message' => 'Ignored repo', 'repo' => $repo]);
        exit;
    }

    if ($ref !== 'refs/heads/' . $branch) {
        $respond(200, ['ok' => true, 'message' => 'Ignored branch', 'ref' => $ref]);
        exit;
    }

    if (!$isExecAvailable()) {
        @file_put_contents($deployQueueFile, json_encode([
            'queued_at' => date('c'),
            'event' => $event,
            'repo' => $repo,
            'ref' => $ref,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $log('event=' . $event . ' repo=' . $repo . ' ref=' . $ref . ' queued=1 reason=exec_disabled');

        $respond(202, [
            'ok' => true,
            'queued' => true,
            'message' => 'Deploy queued (exec disabled in web PHP). Run via cron/CLI.',
        ]);
        exit;
    }

    $lockHandle = @fopen($deployLockFile, 'c+');
    if (!$lockHandle) {
        $respond(500, ['ok' => false, 'error' => 'Could not open deploy lock file']);
        exit;
    }

    if (!flock($lockHandle, LOCK_EX | LOCK_NB)) {
        fclose($lockHandle);
        $respond(409, ['ok' => false, 'error' => 'Deploy already in progress']);
        exit;
    }

    @ftruncate($lockHandle, 0);
    @fwrite($lockHandle, date('c') . PHP_EOL);

    $commands = [];
    $commands[] = 'cd ' . escapeshellarg($repoDir);
    $commands[] = 'git fetch origin ' . escapeshellarg($branch);
    $commands[] = 'git reset --hard origin/' . escapeshellarg($branch);
    $commands[] = escapeshellcmd($composerBin) . ' install --no-dev --prefer-dist --no-interaction --optimize-autoloader';
    if ($runBuild) {
        $commands[] = escapeshellcmd($npmBin) . ' ci';
        $commands[] = escapeshellcmd($npmBin) . ' run build';
    }
    $commands[] = escapeshellcmd($phpBin) . ' artisan optimize:clear';
    $commands[] = escapeshellcmd($phpBin) . ' artisan config:cache';
    $commands[] = escapeshellcmd($phpBin) . ' artisan route:cache';
    $commands[] = escapeshellcmd($phpBin) . ' artisan view:cache';

    $output = [];
    $exitCode = 0;
    exec(implode(' && ', $commands) . ' 2>&1', $output, $exitCode);

    flock($lockHandle, LOCK_UN);
    fclose($lockHandle);

    $log('event=' . $event . ' repo=' . $repo . ' ref=' . $ref . ' exit=' . $exitCode . ' output=' . implode(' | ', $output));

    $respond($exitCode === 0 ? 200 : 500, [
        'ok' => $exitCode === 0,
        'exit_code' => $exitCode,
        'output' => $output,
    ]);
} catch (Throwable $exception) {
    if (isset($lockHandle) && is_resource($lockHandle)) {
        @flock($lockHandle, LOCK_UN);
        @fclose($lockHandle);
    }
    $log('EXCEPTION: ' . $exception->getMessage() . ' @ ' . $exception->getFile() . ':' . $exception->getLine());
    $respond(500, [
        'ok' => false,
        'error' => 'Unhandled exception',
        'message' => $exception->getMessage(),
    ]);
}