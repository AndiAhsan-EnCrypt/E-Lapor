<?php
// includes/auth.php
// Helper autentikasi & otorisasi.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function is_admin(): bool {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
}

// Wajib login; jika belum, redirect ke login.php
function require_login(): void {
    if (!is_logged_in()) {
        header("Location: ../login.php");
        exit;
    }
}

// Wajib admin
function require_admin(): void {
    require_login();
    if (!is_admin()) {
        http_response_code(403);
        exit("Akses ditolak: halaman ini hanya untuk admin.");
    }
}

function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
