<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    public function index()
    {
        $userId = auth()->id();
        $tasks = $this->taskService->getUserTasks($userId);
        
        return response()->json($tasks);
    }

    public function show($id)
    {
        $userId = auth()->id();
        $task = $this->taskService->getUserTask($id, $userId);
        
        if (!$task) {
            return response()->json(['error' => 'Task not found'], 404);
        }
        
        return response()->json($task);
    }

    public function store(Request $request)
    {
        \Log::info('Task creation attempt', [
            'data' => $request->all(),
            'user' => auth()->user() ? auth()->user()->id : 'not authenticated'
        ]);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            \Log::error('Task validation failed', ['errors' => $validator->errors()]);
            return response()->json($validator->errors(), 422);
        }

        try {
            $userId = auth()->id();
            \Log::info('Creating task via service', ['user_id' => $userId]);
            $task = $this->taskService->createTask($request->all(), $userId);
            \Log::info('Task created successfully', ['task_id' => $task->id]);

            // Fire TaskCreated event for real-time notifications
            event(new \App\Events\TaskCreated($task));
            \Log::info('TaskCreated event fired', ['task_id' => $task->id, 'user_id' => $task->user_id]);

            return response()->json($task, 201);
        } catch (\Exception $e) {
            \Log::error('Task creation failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create task',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'completed' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $userId = auth()->id();
        $task = $this->taskService->getUserTask($id, $userId);
        
        if (!$task) {
            return response()->json(['error' => 'Task not found'], 404);
        }

        $this->taskService->updateTask($task, $request->all());

        // Fire TaskUpdated event for real-time notifications
        event(new \App\Events\TaskUpdated($task));
        \Log::info('TaskUpdated event fired', ['task_id' => $task->id, 'user_id' => $task->user_id]);

        return response()->json($task);
    }

    public function destroy($id)
    {
        $userId = auth()->id();
        $task = $this->taskService->getUserTask($id, $userId);
        
        if (!$task) {
            return response()->json(['error' => 'Task not found'], 404);
        }

        $this->taskService->deleteTask($task);

        return response()->json(null, 204);
    }
}