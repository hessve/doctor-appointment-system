<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

$siteTitle = APP_NAME;
include __DIR__ . '/templates/header.php';
?>

<section class="hero">
    <div class="container hero-content">
        <div class="hero-copy">
            <span class="eyebrow">Healthcare scheduling</span>
            <h1>Book a doctor appointment in minutes</h1>
            <p>
                A clean and modern doctor appointment management system built with PHP, HTML, and JavaScript.
                Patients can book visits, doctors manage availability, and administrators monitor schedules.
            </p>
            <div class="hero-actions">
                <a class="btn btn-primary" href="register.php">Create account</a>
                <a class="btn btn-secondary" href="login.php">Login</a>
            </div>
        </div>

        <div class="hero-panel card">
            <h3>Why choose this system?</h3>
            <ul class="feature-list">
                <li>Quick booking and calendar overview</li>
                <li>Doctor availability management</li>
                <li>Appointment history and status tracking</li>
                <li>Admin dashboard with summaries</li>
            </ul>
        </div>
    </div>
</section>

<section class="features">
    <div class="container cards-grid">
        <div class="card feature-item">
            <h3>Patients</h3>
            <p>Find available doctors, choose a date and time, and track upcoming visits.</p>
        </div>
        <div class="card feature-item">
            <h3>Doctors</h3>
            <p>Set weekly schedules, review appointments, and manage patient visits efficiently.</p>
        </div>
        <div class="card feature-item">
            <h3>Admins</h3>
            <p>Monitor all doctors, patients, and appointments from a central dashboard.</p>
        </div>
    </div>
</section>

<?php include __DIR__ . '/templates/footer.php'; ?>
