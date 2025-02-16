<?php

namespace App\Http\Requests\Api\User;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property string $name
 * @property string $password
 */
class LoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'string',
            'password' => 'string',
        ];
    }
}
