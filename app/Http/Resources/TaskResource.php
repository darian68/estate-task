<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'building' => $this->whenLoaded('building', function () {
                return [
                    'id' => $this->building->id,
                    'name' => $this->building->name,
                    'address' => $this->building->address,
                ];
            }),
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'due_at' => $this->due_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            // Creator of the task
            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                    'email' => $this->creator->email,
                ];
            }),
            // Assigned user
            'assignee' => $this->whenLoaded('assignee', function () {
                return [
                    'id' => $this->assignee->id,
                    'name' => $this->assignee->name,
                    'email' => $this->assignee->email,
                ];
            }),
            'comments' => TaskCommentResource::collection($this->whenLoaded('comments')),
        ];
    }
}
