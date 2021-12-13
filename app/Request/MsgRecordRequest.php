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
            'acceptCode' => 'required|numeric',//接受方ID
            'sessionType' => 'required',//接收消息的类型
            'page' => 'required|numeric',//当前页
            'perPage' => 'required|numeric',//每页条数
        ];
    }

    public function messages(): array
    {
        return [
            'acceptCode.required' => 'acceptCode is required',
            'acceptCode.numeric' => 'acceptCode is numeric',
            'sessionType.required' => 'sessionType is required',
            'page.required' => 'page is required',
            'page.numeric' => 'page is numeric',
            'perPage.required' => 'perPage is required',
            'perPage.numeric' => 'perPage is numeric',
        ];
    }
}
