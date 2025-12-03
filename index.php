<?php
require_once 'Planner.php';

$planner = new Planner();

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_task') {
        $planner->addTask($_POST['task_name'] ?? '', (int)($_POST['session_count'] ?? 0));
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    if ($action === 'edit_task' && isset($_POST['index'])) {
        $index = (int)$_POST['index'];
        $planner->editTask($index, $_POST['task_name'] ?? '', (int)($_POST['session_count'] ?? 0));
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Handle GET
if (isset($_GET['soft_delete'])) {
    $planner->softDeleteTask((int)$_GET['soft_delete']);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_GET['restore'])) {
    $planner->restoreTask((int)$_GET['restore']);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_GET['clear'])) {
    $planner->clearQueue();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Ambil semua tugas
$tasks = $planner->getTaskQueue(true);
$schedule = $planner->buildSchedule();
$totalTime = array_sum(array_column($schedule, 'duration'));

// Cek apakah sedang dalam mode edit
$editMode = isset($_GET['edit']) ? (int)$_GET['edit'] : null;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pomodoro Planner - Edit Toggle</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>🍅 Pomodoro Planner</h1>

        <!-- Form Tambah Tugas -->
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
            <?php if (!empty(array_filter($tasks, fn($t) => !$t['deleted']))): ?>
                <a href="?clear" class="btn btn-clear" onclick="return confirm('Kosongkan semua tugas?')">Kosongkan Antrian</a>
            <?php endif; ?>
        </div>

        <h2>Antrian Tugas</h2>
        <?php if (empty($tasks)): ?>
            <p style="text-align: center; color: #777;">Belum ada tugas dalam antrian.</p>
        <?php else: ?>
            <?php foreach ($tasks as $index => $task): ?>
                <?php if ($task['deleted']): ?>
                    <div class="queue-item deleted">
                        <div style="opacity: 0.6; text-decoration: line-through;">
                            <strong><?= $index + 1 ?>. <?= $task['name'] ?></strong><br>
                            <small><?= $task['sessions'] ?> sesi</small>
                        </div>
                        <a href="?restore=<?= $index ?>" class="btn btn-restore">Pulihkan</a>
                    </div>
                <?php else: ?>
                    <div class="queue-item">
                        <div>
                            <strong><?= $index + 1 ?>. <?= $task['name'] ?></strong><br>
                            <small><?= $task['sessions'] ?> sesi</small>
                        </div>

                        <!-- Tombol Edit & Hapus (Vertikal) -->
                        <div class="action-buttons">
                            <?php if ($editMode === $index): ?>
                                <!-- Form Edit Muncul Hanya Saat Mode Edit -->
                                <form method="POST" class="edit-form">
                                    <input type="hidden" name="action" value="edit_task">
                                    <input type="hidden" name="index" value="<?= $index ?>">
                                    <input type="text" name="task_name"
                                        value="<?= htmlspecialchars($task['name']) ?>"
                                        style="width:110px; font-size:13px; margin:2px;" required>
                                    <input type="number" name="session_count" value="<?= $task['sessions'] ?>"
                                        min="1" style="width:60px; font-size:13px; margin:2px;" required>
                                    <button type="submit" class="btn-edit-confirm"
                                        onclick="return confirm('Simpan perubahan?')">
                                        Simpan
                                    </button>
                                </form>
                            <?php else: ?>
                                <!-- Tombol Edit (toggle ke mode edit) -->
                                <a href="?edit=<?= $index ?>" class="btn btn-edit">Edit</a>
                            <?php endif; ?>
                            <!-- Tombol Hapus (selalu ada di bawah Edit) -->
                            <a href="?soft_delete=<?= $index ?>" class="btn btn-delete"
                                onclick="return confirm('Hapus sementara tugas ini?')">
                                Hapus
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
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