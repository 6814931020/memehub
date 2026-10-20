<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(): void
{
    $posted = (string)($_POST['csrf_token'] ?? '');
    $stored = (string)($_SESSION['csrf_token'] ?? '');
    if ($stored === '' || !hash_equals($stored, $posted)) {
        http_response_code(403);
        exit('คำขอไม่ถูกต้อง กรุณาย้อนกลับและลองใหม่อีกครั้ง');
    }
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function consumeFlash(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $message = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $message;
}

function safeImageUrl(string $url): string
{
    // Permit local project assets and regular http(s) image URLs only.
    if (str_starts_with($url, 'assets/')) {
        return $url;
    }
    $scheme = parse_url($url, PHP_URL_SCHEME);
    return in_array(strtolower((string)$scheme), ['http', 'https'], true) ? $url : 'assets/memes/meme-default.svg';
}
