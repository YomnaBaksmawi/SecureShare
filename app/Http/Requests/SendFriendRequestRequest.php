<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class SendFriendRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    $user = User::find($value);

                    if (! $user) {
                        $fail('You cannot send a friend request to this user.');

                        return;
                    }

                    if ((int) $value === (int) auth()->id()) {
                        $fail('You cannot send a friend request to yourself.');
                    }

                    if ($user->status !== 'active') {
                        $fail('You cannot send a friend request to this user.');
                    }
                },
            ],
        ];
    }
}
