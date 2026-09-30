<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

requireAuth();

$user = currentUser();
$siteTitle = 'Appointments';
$pdo = getDb();

$errors = [];
$success = '';

$doctorId = (int) ($_GET['doctor_id'] ?? 0);
$doctorOptions = $pdo->query("SELECT * FROM users WHERE role = 'doctor' ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patientId = (int) ($user['role'] === 'patient' ? $user['id'] : ($_POST['patient_id'] ?? 0));
    $doctorId = (int) ($_POST['doctor_id'] ?? 0);
    $appointmentDate = trim($_POST['appointment_date'] ?? '');
    $appointmentTime = trim($_POST['appointment_time'] ?? '');
    $reason = trim($_POST['reason'] ?? '');

    if ($doctorId <= 0 || $appointmentDate === '' || $appointmentTime === '') {
        $errors[] = 'Please select a doctor, date, and time.';
    }

    if ($user['role'] === 'patient' && $patientId <= 0) {
        $errors[] = 'Patient record not found.';
    }

    if ($doctorId > 0 && $appointmentDate !== '' && $appointmentTime !== '') {
        $slotTaken = $pdo->prepare('SELECT id FROM appointments WHERE doctor_id = :doctor_id AND appointment_date = :appointment_date AND appointment_time = :appointment_time AND status != :status LIMIT 1');
        $slotTaken->execute([
            ':doctor_id' => $doctorId,
            ':appointment_date' => $appointmentDate,
            ':appointment_time' => $appointmentTime,
            ':status' => 'Cancelled',
        ]);

        if ($slotTaken->fetch()) {
            $errors[] = 'This time slot is already booked.';
        }
    }

    if (!$errors) {
        $insert = $pdo->prepare('INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, reason, status, created_at) VALUES (:patient_id, :doctor_id, :appointment_date, :appointment_time, :reason, :status, :created_at)');
        $insert->execute([
            ':patient_id' => $patientId,
            ':doctor_id' => $doctorId,
            ':appointment_date' => $appointmentDate,
            ':appointment_time' => $appointmentTime,
            ':reason' => $reason,
            ':status' => 'Pending',
            ':created_at' => date('Y-m-d H:i:s'),
        ]);

        $success = 'Appointment booked successfully.';
        $doctorId = 0;
    }
}

if ($user['role'] === 'admin') {
    $listQuery = $pdo->query('SELECT a.*, p.name AS patient_name, d.name AS doctor_name FROM appointments a JOIN users p ON p.id = a.patient_id JOIN users d ON d.id = a.doctor_id ORDER BY a.appointment_date DESC, a.appointment_time DESC');
    $appointments = $listQuery->fetchAll(PDO::FETCH_ASSOC);
} else {
    $appointmentQuery = $pdo->prepare('SELECT a.*, u.name AS doctor_name, u.specialty FROM appointments a JOIN users u ON u.id = a.doctor_id WHERE a.patient_id = :patient_id ORDER BY a.appointment_date DESC, a.appointment_time DESC');
    $appointmentQuery->execute([':patient_id' => $user['id']]);
    $appointments = $appointmentQuery->fetchAll(PDO::FETCH_ASSOC);
}

include __DIR__ . '/templates/header.php';
?>

<div class="dashboard-layout">
    <aside class="sidebar card">
        <h3><?php echo htmlspecialchars(APP_NAME); ?></h3>
        <nav class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="appointments.php" class="active">Appointments</a>
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
                <span class="eyebrow">Appointment management</span>
                <h2>Book a visit</h2>
            </div>
        </div>

        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <div><?php echo htmlspecialchars($error); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="card section-block">
            <form method="post" action="appointments.php" class="booking-form">
                <?php if ($user['role'] !== 'patient'): ?>
                    <div class="form-group">
                        <label for="patient_id">Patient</label>
                        <select name="patient_id" id="patient_id" required>
                            <?php
                            $patients = $pdo->query("SELECT id, name FROM users WHERE role = 'patient' ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($patients as $patient):
                                echo '<option value="' . (int) $patient['id'] . '">' . htmlspecialchars($patient['name']) . '</option>';
                            endforeach;
                            ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="doctor_id">Doctor</label>
                    <select name="doctor_id" id="doctor_id" required>
                        <option value="">Select doctor</option>
                        <?php foreach ($doctorOptions as $doctor): ?>
                            <option value="<?php echo (int) $doctor['id']; ?>" <?php echo ($doctorId === (int) $doctor['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($doctor['name']); ?> - <?php echo htmlspecialchars($doctor['specialty'] ?: 'General practice'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="appointment_date">Date</label>
                        <input type="date" name="appointment_date" id="appointment_date" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="appointment_time">Time</label>
                        <input type="time" name="appointment_time" id="appointment_time" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="reason">Reason for visit</label>
                    <textarea name="reason" id="reason" rows="3" placeholder="Describe the problem or concern"></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Confirm appointment</button>
            </form>
        </div>

        <div class="card section-block">
            <h3>Your appointments</h3>
            <?php if ($appointments): ?>
                <div class="appointments-list">
                    <?php foreach ($appointments as $appointment): ?>
                        <div class="appointment-item">
                            <div>
                                <strong>
                                    <?php if ($user['role'] === 'admin'): ?>
                                        <?php echo htmlspecialchars($appointment['patient_name']); ?> with <?php echo htmlspecialchars($appointment['doctor_name']); ?>
                                    <?php else: ?>
                                        <?php echo htmlspecialchars($appointment['doctor_name']); ?>
                                    <?php endif; ?>
                                </strong>
                                <div class="muted"><?php echo htmlspecialchars($appointment['appointment_date']); ?> at <?php echo htmlspecialchars($appointment['appointment_time']); ?></div>
                            </div>
                            <span class="status-pill status-<?php echo strtolower(htmlspecialchars($appointment['status'])); ?>"><?php echo htmlspecialchars($appointment['status']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="muted">No appointments found.</p>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php include __DIR__ . '/templates/footer.php'; ?>
