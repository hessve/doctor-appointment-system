<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

requireAuth();

$user = currentUser();
if ($user['role'] !== 'doctor') {
    redirect('dashboard.php');
}

$siteTitle = 'Doctor schedule';
$pdo = getDb();
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dayOfWeek = $_POST['day_of_week'] ?? '';
    $startTime = $_POST['start_time'] ?? '';
    $endTime = $_POST['end_time'] ?? '';
    $slotDuration = (int) ($_POST['slot_duration'] ?? 30);

    if ($dayOfWeek === '' || $startTime === '' || $endTime === '') {
        $errors[] = 'Please complete all schedule fields.';
    }

    if ($slotDuration < 15 || $slotDuration > 120) {
        $errors[] = 'Slot duration must be between 15 and 120 minutes.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('INSERT INTO doctor_schedules (doctor_id, day_of_week, start_time, end_time, slot_duration, created_at) VALUES (:doctor_id, :day_of_week, :start_time, :end_time, :slot_duration, :created_at)');
        $stmt->execute([
            ':doctor_id' => $user['id'],
            ':day_of_week' => $dayOfWeek,
            ':start_time' => $startTime,
            ':end_time' => $endTime,
            ':slot_duration' => $slotDuration,
            ':created_at' => date('Y-m-d H:i:s'),
        ]);

        $success = 'Schedule added successfully.';
    }
}

$schedules = $pdo->prepare('SELECT * FROM doctor_schedules WHERE doctor_id = :doctor_id ORDER BY field(day_of_week, "Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday")');
$schedules->execute([':doctor_id' => $user['id']]);
$doctorSchedules = $schedules->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/templates/header.php';
?>

<div class="dashboard-layout">
    <aside class="sidebar card">
        <h3><?php echo htmlspecialchars(APP_NAME); ?></h3>
        <nav class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="appointments.php">Appointments</a>
            <a href="doctor_schedule.php" class="active">My schedule</a>
            <a href="logout.php">Logout</a>
        </nav>
    </aside>

    <main class="content-area">
        <div class="panel-header">
            <div>
                <span class="eyebrow">Availability</span>
                <h2>Set your working hours</h2>
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
            <form method="post" action="doctor_schedule.php" class="schedule-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="day_of_week">Day</label>
                        <select name="day_of_week" id="day_of_week" required>
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Thursday">Thursday</option>
                            <option value="Friday">Friday</option>
                            <option value="Saturday">Saturday</option>
                            <option value="Sunday">Sunday</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="start_time">Start time</label>
                        <input type="time" name="start_time" id="start_time" required>
                    </div>

                    <div class="form-group">
                        <label for="end_time">End time</label>
                        <input type="time" name="end_time" id="end_time" required>
                    </div>

                    <div class="form-group">
                        <label for="slot_duration">Slot duration (min)</label>
                        <input type="number" name="slot_duration" id="slot_duration" min="15" max="120" value="30" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Add availability</button>
            </form>
        </div>

        <div class="card section-block">
            <h3>Current schedule</h3>
            <?php if ($doctorSchedules): ?>
                <div class="schedule-list">
                    <?php foreach ($doctorSchedules as $schedule): ?>
                        <div class="schedule-item">
                            <strong><?php echo htmlspecialchars($schedule['day_of_week']); ?></strong>
                            <span><?php echo htmlspecialchars($schedule['start_time']); ?> - <?php echo htmlspecialchars($schedule['end_time']); ?></span>
                            <span class="muted"><?php echo (int) $schedule['slot_duration']; ?> min slots</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="muted">No schedule created yet.</p>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php include __DIR__ . '/templates/footer.php'; ?>
