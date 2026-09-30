<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

requireAuth();
$siteTitle = 'Admin';
$user = currentUser();
if ($user['role'] !== 'admin') {
    redirect('dashboard.php');
}

$pdo = getDb();
$summary = [
    'patients' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'patient'")->fetchColumn(),
    'doctors' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'doctor'")->fetchColumn(),
    'appointments' => $pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn(),
];

$users = $pdo->query('SELECT * FROM users ORDER BY role, name')->fetchAll(PDO::FETCH_ASSOC);
$appointments = $pdo->query('SELECT a.*, p.name AS patient_name, d.name AS doctor_name FROM appointments a JOIN users p ON p.id = a.patient_id JOIN users d ON d.id = a.doctor_id ORDER BY a.appointment_date DESC, a.appointment_time DESC LIMIT 30')->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/templates/header.php';
?>

<div class="dashboard-layout">
    <aside class="sidebar card">
        <h3><?php echo htmlspecialchars(APP_NAME); ?></h3>
        <nav class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="appointments.php">Appointments</a>
            <a href="admin.php" class="active">Admin</a>
            <a href="logout.php">Logout</a>
        </nav>
    </aside>

    <main class="content-area">
        <div class="panel-header">
            <div>
                <span class="eyebrow">Administration</span>
                <h2>Clinic overview</h2>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-box card">
                <span class="stat-label">Patients</span>
                <strong><?php echo (int) $summary['patients']; ?></strong>
            </div>
            <div class="stat-box card">
                <span class="stat-label">Doctors</span>
                <strong><?php echo (int) $summary['doctors']; ?></strong>
            </div>
            <div class="stat-box card">
                <span class="stat-label">Appointments</span>
                <strong><?php echo (int) $summary['appointments']; ?></strong>
            </div>
        </div>

        <div class="card section-block">
            <h3>Users</h3>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Specialty</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $member): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($member['name']); ?></td>
                                <td><?php echo htmlspecialchars($member['email']); ?></td>
                                <td><?php echo htmlspecialchars($member['role']); ?></td>
                                <td><?php echo htmlspecialchars($member['specialty'] ?: '-'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card section-block">
            <h3>Appointments</h3>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Doctor</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $appointment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($appointment['patient_name']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['doctor_name']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['appointment_date']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['appointment_time']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['status']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php include __DIR__ . '/templates/footer.php'; ?>
