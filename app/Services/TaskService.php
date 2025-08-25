<?php

namespace App\Services;

use App\Repositories\TaskRepository;
use App\Models\Task;

class TaskService
{
    protected $taskRepository;

    public function __construct(TaskRepository $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    public function getUserTasks(int $userId)
    {
        return $this->taskRepository->getUserTasks($userId);
    }

    public function getUserTask(int $taskId, int $userId): ?Task
    {
        return $this->taskRepository->findUserTask($taskId, $userId);
    }

    public function createTask(array $data, int $userId): Task
    {
        $data['user_id'] = $userId;
        return $this->taskRepository->create($data);
    }

    public function updateTask(Task $task, array $data): bool
    {
        return $this->taskRepository->update($task, $data);
    }

    public function deleteTask(Task $task): bool
    {
        return $this->taskRepository->delete($task);
    }
}