<?php

namespace App\Http\Controllers\Api\V1;

use App\Filters\TaskFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBuildingTaskRequest;
use App\Http\Requests\TaskFilterRequest;
use App\Http\Resources\TaskResource;
use App\Models\Building;
use App\Traits\HandlesPerPage;

class BuildingTaskController extends Controller
{
    use HandlesPerPage;

    /**
     * Display a paginated list of tasks for the given building,
     * including related creator, assignee, and comments.
     *
     * @param  \App\Models\Building  $building
     * @param  \App\Http\Requests\TaskFilterRequest  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection<TaskResource>
     */
    public function index(Building $building, TaskFilterRequest $request)
    {
        $filters = $request->validated();
        $query = $building->tasks()->with(['creator', 'assignee', 'comments.creator']);
        $filtered = (new TaskFilter($filters))->apply($query);
        $tasks = $filtered->paginate($this->getPerPage($request))->appends($filters);
        return TaskResource::collection($tasks);
    }

    /**
     * Store a newly created task associated with the given building.
     *
     * @param  \App\Models\Building  $building
     * @param  \App\Http\Requests\StoreBuildingTaskRequest  $request
     * @return \App\Http\Resources\TaskResource
     */
    public function store(Building $building, StoreBuildingTaskRequest $request)
    {
        $data =  $request->validated();
        $task = $building->tasks()->create([
            ...$data,
            'created_by' => $request->user()->id,
        ]);
        $task->load(['assignee', 'creator']);
        return new TaskResource($task);
    }
}
