<?php
namespace App\Http\Requests\Student;
use Illuminate\Foundation\Http\FormRequest;
class SubmitMissionAnswersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return session()->has('player');
    }

    public function rules(): array
    {
        return [
            'answers'    => ['present', 'array'],
            'answers.*'  => ['present'],
            'quiz_ids'   => ['required', 'array'],
            'quiz_ids.*' => ['required', 'string', 'uuid'],
        ];
    }

    public function messages(): array
    {
        return [
            'answers.present'  => 'Format jawaban tidak valid',
            'answers.array'    => 'Format jawaban tidak valid',
            'quiz_ids.required' => 'ID kuis tidak boleh kosong',
            'quiz_ids.array'   => 'Format ID kuis tidak valid',
        ];
    }
}
