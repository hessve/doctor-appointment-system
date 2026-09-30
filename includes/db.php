<?php
require_once __DIR__ . '/includes/config.php';

$pdo = new PDO('sqlite:' . DB_PATH);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec('PRAGMA foreign_keys = ON;');

$pdo->exec('CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    role TEXT NOT NULL DEFAULT "patient",
    specialty TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)');

$pdo->exec('CREATE TABLE IF NOT EXISTS doctor_schedules (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    doctor_id INTEGER NOT NULL,
    day_of_week TEXT NOT NULL,
    start_time TEXT NOT NULL,
    end_time TEXT NOT NULL,
    slot_duration INTEGER NOT NULL DEFAULT 30,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE
)');

$pdo->exec('CREATE TABLE IF NOT EXISTS appointments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    patient_id INTEGER NOT NULL,
    doctor_id INTEGER NOT NULL,
    appointment_date TEXT NOT NULL,
    appointment_time TEXT NOT NULL,
    reason TEXT,
    status TEXT NOT NULL DEFAULT "Pending",
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE
)');

$adminExists = $pdo->query("SELECT COUNT(*) FROM users WHERE email = 'admin@clinic.local'")->fetchColumn();
if (!$adminExists) {
    $pdo->prepare('INSERT INTO users (name, email, password_hash, role, specialty) VALUES (?, ?, ?, ?, ?)')->execute([
        'Clinic Admin',
        'admin@clinic.local',
        password_hash('admin123', PASSWORD_DEFAULT),
        'admin',
        null,
    ]);
}

return $pdo;
