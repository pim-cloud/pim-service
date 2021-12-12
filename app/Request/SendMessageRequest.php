<?php

declare(strict_types=1);

namespace App\Request;

use Hyperf\Validation\Request\FormRequest;

class SendMessageRequest extends FormRequest
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
            'accept_type' => 'required',//接收者类型
            'accept_code' => 'required',//接收人uid
            'content' => 'required',//content 内容
            'content_type' => 'required',//消息类型
        ];
    }
}
