<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateDndRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'dnd_start' => ['required', 'date_format:H:i'],
            'dnd_end' => ['required', 'date_format:H:i'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->input('dnd_start') === $this->input('dnd_end')) {
                $validator->errors()->add('dnd_end', 'DND end must be different from DND start.');
            }
        });
    }
}
