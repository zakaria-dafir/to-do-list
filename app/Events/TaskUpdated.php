<?php

namespace App\Events;

use App\Models\Task;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
        \Log::info('TaskUpdated event constructed', [
            'task_id' => $task->id,
            'user_id' => $task->user_id,
            'channel' => 'tasks.' . $task->user_id
        ]);
    }

    public function broadcastOn()
    {
        // Broadcast to all users for demo purposes
        return new Channel('global-tasks');
    }

    public function broadcastWith()
    {
        return [
            'task' => $this->task,
            'message' => 'Tâche mise à jour avec succès!',
            'user_id' => $this->task->user_id,
            'title' => $this->task->title,
            'description' => $this->task->description
        ];
    }
}
