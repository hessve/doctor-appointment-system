<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

requireAuth();

$siteTitle = 'Dashboard';
$user = currentUser();
$pdo = getDb();

if ($user['role'] === 'patient') {
    $doctorList = $pdo->query("SELECT * FROM users WHERE role = 'doctor' ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
    $userAppointments = $pdo->prepare('SELECT a.*, u.name AS doctor_name, u.specialty FROM appointments a JOIN users u ON u.id = a.doctor_id WHERE a.patient_id = :patient_id ORDER BY a.appointment_date DESC, a.appointment_time DESC');
    $userAppointments->execute([':patient_id' => $user['id']]);
    $appointments = $userAppointments->fetchAll(PDO::FETCH_ASSOC);
}

if ($user['role'] === 'doctor') {
    $doctorAppointments = $pdo->prepare('SELECT a.*, u.name AS patient_name FROM appointments a JOIN users u ON u.id = a.patient_id WHERE a.doctor_id = :doctor_id ORDER BY a.appointment_date DESC, a.appointment_time DESC');
    $doctorAppointments->execute([':doctor_id' => $user['id']]);
    $appointments = $doctorAppointments->fetchAll(PDO::FETCH_ASSOC);
}

if ($user['role'] === 'admin') {
    $stats = [
        'patients' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'patient'")->fetchColumn(),
        'doctors' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'doctor'")->fetchColumn(),
        'appointments' => $pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn(),
    ];
    $appointments = $pdo->query('SELECT a.*, p.name AS patient_name, d.name AS doctor_name FROM appointments a JOIN users p ON p.id = a.patient_id JOIN users d ON d.id = a.doctor_id ORDER BY a.appointment_date DESC, a.appointment_time DESC LIMIT 20')->fetchAll(PDO::FETCH_ASSOC);
}

include __DIR__ . '/templates/header.php';
?>

<div class="dashboard-layout">
    <aside class="sidebar card">
        <h3><?php echo htmlspecialchars(APP_NAME); ?></h3>
        <nav class="nav-links">
            <a href="dashboard.php" class="active">Dashboard</a>
            <a href="appointments.php">Appointments</a>
            <?php if ($user['role'] === 'doctor'): ?>
                <a href="doctor_schedule.php">My schedule</a>
            <?php endif; ?>
            <?php if ($user['role'] === 'admin'): ?>
                <a href="admin.php">Admin</a>
            <?php endif; ?>
            <a href="logout.php">Logout</a>
        </nav>
    </aside>

    <main class="content-area">
        <div class="panel-header">
            <div>
                <span class="eyebrow">Welcome back</span>
                <h2><?php echo htmlspecialchars($user['name']); ?></h2>
            </div>
            <span class="role-badge"><?php echo ucfirst(htmlspecialchars($user['role'])); ?></span>
        </div>

        <?php if ($user['role'] === 'patient'): ?>
            <div class="stats-grid">
                <div class="stat-box card">
                    <span class="stat-label">Doctors</span>
                    <strong><?php echo count($doctorList); ?></strong>
                </div>
                <div class="stat-box card">
                    <span class="stat-label">Appointments</span>
                    <strong><?php echo count($appointments); ?></strong>
                </div>
            </div>

            <div class="card section-block">
                <h3>Available doctors</h3>
                <div class="doctor-list">
                    <?php if ($doctorList): ?>
                        <?php foreach ($doctorList as $doctor): ?>
                            <div class="doctor-item">
                                <div>
                                    <strong><?php echo htmlspecialchars($doctor['name']); ?></strong>
                                    <div class="muted"><?php echo htmlspecialchars($doctor['specialty'] ?: 'General practitioner'); ?></div>
                                </div>
                                <a href="appointments.php?doctor_id=<?php echo (int) $doctor['id']; ?>" class="btn btn-small btn-primary">Book</a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="muted">No doctors are currently available.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($user['role'] === 'doctor'): ?>
            <div class="card section-block">
                <h3>Upcoming appointments</h3>
                <?php if ($appointments): ?>
                    <div class="appointments-list">
                        <?php foreach ($appointments as $appointment): ?>
                            <div class="appointment-item">
                                <div>
                                    <strong><?php echo htmlspecialchars($appointment['patient_name']); ?></strong>
                                    <div class="muted"><?php echo htmlspecialchars($appointment['appointment_date']); ?> at <?php echo htmlspecialchars($appointment['appointment_time']); ?></div>
                                </div>
                                <span class="status-pill status-<?php echo strtolower(htmlspecialchars($appointment['status'])); ?>"><?php echo htmlspecialchars($appointment['status']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="muted">No appointments scheduled yet.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($user['role'] === 'admin'): ?>
            <div class="stats-grid">
                <div class="stat-box card">
                    <span class="stat-label">Patients</span>
                    <strong><?php echo (int) $stats['patients']; ?></strong>
                </div>
                <div class="stat-box card">
                    <span class="stat-label">Doctors</span>
                    <strong><?php echo (int) $stats['doctors']; ?></strong>
                </div>
                <div class="stat-box card">
                    <span class="stat-label">Appointments</span>
                    <strong><?php echo (int) $stats['appointments']; ?></strong>
                </div>
            </div>
        <?php endif; ?>

        <div class="card section-block">
            <h3>Recent activity</h3>
            <?php if ($appointments): ?>
                <div class="appointments-list">
                    <?php foreach (array_slice($appointments, 0, 5) as $appointment): ?>
                        <div class="appointment-item">
                            <div>
                                <?php if ($user['role'] === 'patient'): ?>
                                    <strong><?php echo htmlspecialchars($appointment['doctor_name']); ?></strong>
                                <?php elseif ($user['role'] === 'doctor'): ?>
                                    <strong><?php echo htmlspecialchars($appointment['patient_name']); ?></strong>
                                <?php else: ?>
                                    <strong><?php echo htmlspecialchars($appointment['patient_name']); ?> with <?php echo htmlspecialchars($appointment['doctor_name']); ?></strong>
                                <?php endif; ?>
                                <div class="muted"><?php echo htmlspecialchars($appointment['appointment_date']); ?> at <?php echo htmlspecialchars($appointment['appointment_time']); ?></div>
                            </div>
                            <span class="status-pill status-<?php echo strtolower(htmlspecialchars($appointment['status'])); ?>"><?php echo htmlspecialchars($appointment['status']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="muted">No activity available yet.</p>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php include __DIR__ . '/templates/footer.php'; ?>
