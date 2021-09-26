<?php

declare(strict_types=1);

namespace App\Request;

use Hyperf\Validation\Request\FormRequest;

class MsgRecordRequest extends FormRequest
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
            'accept_uid' => 'required|numeric',//接受方ID
            'page' => 'required|numeric',//当前页
            'perPage' => 'required|numeric',//每页条数
        ];
    }

    public function messages(): array
    {
        return [
            'accept_uid.required' => 'password is required',
            'accept_uid.numeric' => 'password is numeric',
            'page.required' => 'page is required',
            'page.numeric' => 'page is numeric',
            'per_page.required' => 'per_page is required',
            'per_page.numeric' => 'per_page is numeric',
        ];
    }
}
