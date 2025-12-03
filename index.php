<?php
require_once 'Planner.php';

$planner = new Planner();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_task') {
    $planner->addTask($_POST['task_name'] ?? '', (int)($_POST['session_count'] ?? 0));
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_GET['delete'])) {
    $planner->removeTask((int)$_GET['delete']);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_GET['clear'])) {
    $planner->clearQueue();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$tasks = $planner->getTaskQueue();
$schedule = $planner->buildSchedule();
$totalTime = array_sum(array_column($schedule, 'duration'));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pomodoro Planner (OOP)</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>🍅 Pomodoro Planner</h1>

        <form method="POST">
            <input type="hidden" name="action" value="add_task">
            <div class="form-group">
                <label for="task_name">Nama Tugas:</label>
                <input type="text" name="task_name" id="task_name" placeholder="Input Nama" required>
            </div>
            <div class="form-group">
                <label for="session_count">Jumlah Sesi (25 menit per sesi):</label>
                <input type="number" name="session_count" id="session_count" min="1" placeholder="0" required>
            </div>
            <button type="submit">Tambah ke Antrian</button>
        </form>

        <div style="margin: 20px 0; text-align: center;">
            <?php if (!empty($tasks)): ?>
                <a href="?clear" class="btn btn-clear" onclick="return confirm('Kosongkan semua tugas?')">Kosongkan Antrian</a>
            <?php endif; ?>
        </div>

        <h2>Antrian Tugas</h2>
        <?php if (empty($tasks)): ?>
            <p style="text-align: center; color: #777;">Belum ada tugas dalam antrian.</p>
        <?php else: ?>
            <?php foreach ($tasks as $index => $task): ?>
                <div class="queue-item">
                    <div>
                        <strong><?= $index + 1 ?>. <?= $task['name'] ?></strong><br>
                        <small><?= $task['sessions'] ?> sesi</small>
                    </div>
                    <a href="?delete=<?= $index ?>" class="btn btn-delete" onclick="return confirm('Hapus tugas ini?')">Hapus</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (!empty($schedule)): ?>
            <h2>Jadwal Pomodoro</h2>
            <?php foreach ($schedule as $item): ?>
                <div class="schedule-item <?= $item['type'] ?>">
                    <?= $item['label'] ?> — <strong><?= $item['duration'] ?> menit</strong>
                </div>
            <?php endforeach; ?>
            <div style="margin-top: 15px; padding: 12px; background: #f1f8e9; border-radius: 8px;">
                <strong>Total Perkiraan Waktu:</strong> <?= $totalTime ?> menit (≈ <?= round($totalTime / 60, 1) ?> jam)
            </div>
        <?php endif; ?>
    </div>
</body>
</html>