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
                'sessions' => $sessions
            ];
            $this->save();
        }
    }

    public function removeTask(int $index): void
    {
        if (isset($this->taskQueue[$index])) {
            array_splice($this->taskQueue, $index, 1);
            $this->save();
        }
    }

    public function clearQueue(): void
    {
        $this->taskQueue = [];
        $this->save();
    }

    public function getTaskQueue(): array
    {
        return $this->taskQueue;
    }

    public function buildSchedule(): array
    {
        $schedule = [];
        $tasks = $this->taskQueue;
        $totalTasks = count($tasks);

        for ($ti = 0; $ti < $totalTasks; $ti++) {
            $task = $tasks[$ti];
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