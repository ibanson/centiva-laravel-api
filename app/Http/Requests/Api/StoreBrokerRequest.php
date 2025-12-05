<?php

namespace App\Http\Requests\Api;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBrokerRequest extends FormRequest
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
       // if ($this->isMethod('post')) {
            return [
                'name'    => ['required', 'string', 'max:255'],
                'email'   => [
                    'required',
                    'email',
                    'max:255',
                    'unique:brokers,email'
                ],
            ];
      //  }

        // UPDATE (PUT/PATCH)
/*         return [
            'team_id' => ['required', 'exists:teams,id'],
            'name'    => ['required', 'string', 'max:255'],
            'email'   => [
                'required',
                'email',
                'max:255',
                Rule::unique('brokers', 'email')->ignore($this->broker)
            ]
        ]; */
    }

    public function messages(): array
    {
        return [
           
            'name.required' => 'The broker name is required.',
            'name.string'   => 'The broker name must be a valid string.',
            'name.max'      => 'The broker name may not exceed 255 characters.',

            'email.required' => 'The email address is required.',
            'email.email'    => 'The email address must be valid.',
            'email.max'      => 'The email address may not exceed 255 characters.',
            'email.unique'   => 'This email address is already used.',
        ];
    }
}
