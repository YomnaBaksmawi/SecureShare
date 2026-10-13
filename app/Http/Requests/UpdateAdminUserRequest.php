<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdminUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
'email' => [ 'required', 'email', 'exists:users,email', ],
            'role' => [
                'required',
                Rule::in(['user', 'admin']),
            ],
            'name' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }
}

