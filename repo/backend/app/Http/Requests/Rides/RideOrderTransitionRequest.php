<?php

namespace App\Http\Requests\Rides;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class RideOrderTransitionRequest extends FormRequest
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
            'action' => ['required', 'in:cancel,accept,start,complete,flag_exception'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->input('action') === 'flag_exception' && ! $this->filled('reason')) {
                $validator->errors()->add('reason', 'Reason is required when flagging an exception.');
            }
        });
    }
}
