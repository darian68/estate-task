<?php

namespace App\Http\Requests;

use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBuildingTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'status' => ['sometimes', Rule::enum(TaskStatus::class)],
            'due_at' => ['nullable', 'date', 'after_or_equal:today'],
        ];
    }

    /**
     * Custom validation messages for task creation.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        $statuses = implode(', ', \App\Enums\TaskStatus::values());
        return [
            'title.required' => 'Please provide a title for the task.',
            'title.max' => 'The task title may not exceed 255 characters.',
            'description.string' => 'The description must be a valid text.',
            'assigned_to.exists' => 'The selected assignee does not exist.',
            'status' => 'The provided status is invalid. Accepted values: ' . $statuses,
            'due_at.date' => 'The due date must be a valid date.',
            'due_at.after_or_equal' => 'The due date cannot be in the past.',
        ];
    }
}
