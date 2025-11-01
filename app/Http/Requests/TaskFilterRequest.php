<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\TaskStatus;

class TaskFilterRequest extends FormRequest
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
            'status' => ['sometimes', Rule::enum(TaskStatus::class)],
            'assigned_to' => ['sometimes', 'integer', 'exists:users,id'],
            'created_from' => ['sometimes', 'date'],
            'created_to' => ['sometimes', 'date', 'after_or_equal:created_from'],
            'timezone' => ['sometimes', 'timezone'],
        ];
    }
}
