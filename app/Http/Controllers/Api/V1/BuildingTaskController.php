<?php

namespace App\Http\Controllers\Api\V1;

use App\Filters\TaskFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\TaskFilterRequest;
use App\Http\Resources\TaskResource;
use App\Models\Building;
use App\Traits\HandlesPerPage;

class BuildingTaskController extends Controller
{
    use HandlesPerPage;

    /**
     * Get all tasks for a building including comments.
     */
    public function index(Building $building, TaskFilterRequest $request)
    {
        $filters = $request->validated();
        $query = $building->tasks()->with(['creator', 'assignee', 'comments.creator']);
        $filtered = (new TaskFilter($filters))->apply($query);
        $tasks = $filtered->paginate($this->getPerPage($request))->appends($filters);
        return TaskResource::collection($tasks);
    }
}
