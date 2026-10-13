<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminUserIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'in:user,admin'],
            'trashed' => ['nullable', 'string', 'in:only,with'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}

