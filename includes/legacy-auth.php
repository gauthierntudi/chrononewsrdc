<?php

declare(strict_types=1);

/**
 * @return array<string, mixed>|null
 */
function cn_legacy_current_user(PDO $db): ?array
{
    if (class_exists(\App\Support\LegacySessionBridge::class)) {
        \App\Support\LegacySessionBridge::bootstrapStandalone();
    } elseif (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['logged_in']) || empty($_SESSION['user_id'])) {
        return null;
    }

    $stmt = $db->prepare(
        'SELECT id, nom, mail, role, Titre, cover, telephone, num_user, bio, Facebook, Youtube, Twitter, Instagram
         FROM users
         WHERE id = :id AND status = 1
         LIMIT 1'
    );
    $stmt->execute(['id' => (int) $_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    return $user ?: null;
}
