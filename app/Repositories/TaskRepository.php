<?php

namespace App\Repositories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

class TaskRepository
{
    public function getUserTasks(int $userId): Collection
    {
        return Task::where('user_id', $userId)->get();
    }

    public function findUserTask(int $taskId, int $userId): ?Task
    {
        return Task::where('id', $taskId)->where('user_id', $userId)->first();
    }

    public function create(array $data): Task
    {
        return Task::create($data);
    }

    public function update(Task $task, array $data): bool
    {
        return $task->update($data);
    }

    public function delete(Task $task): bool
    {
        return $task->delete();
    }
}