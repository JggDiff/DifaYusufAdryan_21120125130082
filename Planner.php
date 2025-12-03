<?php

class Planner
{
    private $taskQueue;

    public function __construct()
    {
        session_start();
        $this->taskQueue = $_SESSION['task_queue'] ?? [];
    }

    public function addTask(string $name, int $sessions): void
    {
        $name = trim($name);
        if ($name !== '' && $sessions > 0) {
            $this->taskQueue[] = [
                'name' => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
                'sessions' => $sessions,
                'deleted' => false
            ];
            $this->save();
        }
    }

    public function editTask(int $index, string $name, int $sessions): void
    {
        if (!isset($this->taskQueue[$index])) {
            return;
        }

        $name = trim($name);
        if ($name !== '' && $sessions > 0) {
            $this->taskQueue[$index]['name'] = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
            $this->taskQueue[$index]['sessions'] = $sessions;
            $this->taskQueue[$index]['deleted'] = false;
            $this->save();
        }
    }

    public function softDeleteTask(int $index): void
    {
        if (isset($this->taskQueue[$index])) {
            $this->taskQueue[$index]['deleted'] = true;
            $this->save();
        }
    }

    public function restoreTask(int $index): void
    {
        if (isset($this->taskQueue[$index])) {
            $this->taskQueue[$index]['deleted'] = false;
            $this->save();
        }
    }

    public function clearQueue(): void
    {
        $this->taskQueue = [];
        $this->save();
    }

    public function getTaskQueue(bool $includeDeleted = true): array
    {
        if ($includeDeleted) {
            return $this->taskQueue;
        }

        return array_filter($this->taskQueue, fn($task) => !$task['deleted']);
    }

    public function buildSchedule(): array
    {
        $schedule = [];
        $activeTasks = array_filter($this->taskQueue, fn($task) => !$task['deleted']);
        $activeTasks = array_values($activeTasks); 
        $totalTasks = count($activeTasks);

        for ($ti = 0; $ti < $totalTasks; $ti++) {
            $task = $activeTasks[$ti];
            $isLastTask = ($ti === $totalTasks - 1);

            for ($si = 1; $si <= $task['sessions']; $si++) {
                $schedule[] = [
                    'type' => 'work',
                    'task' => $task['name'],
                    'duration' => 25,
                    'label' => "Tugas: {$task['name']} — Sesi $si"
                ];

                $isLastSession = $isLastTask && ($si === $task['sessions']);
                if (!$isLastSession) {
                    if ($si % 4 == 0) {
                        $schedule[] = ['type' => 'long_break', 'duration' => 15, 'label' => 'Istirahat Panjang'];
                    } else {
                        $schedule[] = ['type' => 'short_break', 'duration' => 5, 'label' => 'Istirahat Singkat'];
                    }
                }
            }
        }
        return $schedule;
    }

    private function save(): void
    {
        $_SESSION['task_queue'] = $this->taskQueue;
    }
}