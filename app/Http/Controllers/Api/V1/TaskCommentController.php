<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskCommentRequest;
use App\Http\Resources\TaskCommentResource;
use App\Models\Task;

class TaskCommentController extends Controller
{
    /**
     * Store a newly created comment for a given task.
     *
     * @param  \App\Models\Task  $task
     * @param  \App\Http\Requests\StoreTaskCommentRequest  $request
     * @return \App\Http\Resources\TaskCommentResource
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Task $task, StoreTaskCommentRequest $request)
    {
        $data =  $request->validated();
        $comment = $task->comments()->create([
            ...$data,
            'created_by' => $request->user()->id,
        ]);
        $comment->load('creator');
        return new TaskCommentResource($comment);
    }
}
