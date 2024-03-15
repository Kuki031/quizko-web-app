<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateQuizRequest extends FormRequest
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
            "name" => ["required", "unique:quizzes,name"],
            "description" => ["nullable", "string", "max:255"],
            "picture" => ['nullable', 'mimes:jpg,png,jpeg', 'max:5048'],
            "is_quiz_locked" => ["nullable", "boolean"],
            "category_id" => ["required", "integer"],
            "starts_at" => ["required", "date"],
            "ends_at" => ["required", "date"]
        ];
    }
}
