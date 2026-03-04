<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class SubmitMissionAnswersRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Session-based authorization check
        return session()->has('player');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'answers' => ['required', 'array'],
            'answers.*' => ['required'],
            'quiz_ids' => ['required', 'array'],
            'quiz_ids.*' => ['required', 'string', 'uuid'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'answers.required' => 'Jawaban tidak boleh kosong',
            'answers.array' => 'Format jawaban tidak valid',
            'quiz_ids.required' => 'ID kuis tidak boleh kosong',
            'quiz_ids.array' => 'Format ID kuis tidak valid',
        ];
    }
}
