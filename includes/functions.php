<?php
session_start();
require_once __DIR__ . '/db.php';

function redirect(string $path): void {
    header('Location: ' . $path);
    exit;
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && is_numeric($_SESSION['user_id']);
}

function currentUser(): ?array {
    if (!isLoggedIn()) {
        return null;
    }

    $pdo = getDb();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    return $user ?: null;
}

function requireAuth(): void {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

function redirectIfLoggedOut(): void {
    if (!isLoggedIn()) {
        return;
    }
}

function userRoleLabel(string $role): string {
    return ucfirst($role);
}
