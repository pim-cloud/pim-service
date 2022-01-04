<?php

declare(strict_types=1);

namespace App\Request;

use Hyperf\Validation\Request\FormRequest;

class LoginRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'username' => 'required|max:25',
            'password' => 'required|max:25',
            'scene' => 'required|max:3',
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'username is required',
            'username.max' => 'username is max:25',
            'password.required' => 'password is required',
            'password.max' => 'password is max:25',
            'scene.required' => 'scene is required',
            'scene.max' => 'scene is max:25',
        ];
    }
}
