<?php

namespace App\Http\Requests;

use App\Models\Friendship;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateDocumentShareRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
    return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
public function rules(): array
    {
        return [
        'user_id' => [ 'required','exists:users,id',
    function ($attribute, $value, $fail) {
        $targetUserId = (int) $value;

        if ($targetUserId === (int) auth()->id()) {
            $fail('You cannot share a document with yourself.');
            return;
        }

        $target = User::find($targetUserId);

        if (! $target) {
            $fail('You cannot share a document with this user.');
            return;
        }

        if ($target->status !== 'active') {
            $fail('You cannot share a document with an inactive user.');
            return;
        }

        $isFriend = Friendship::query()
            ->where(function ($query) use ($targetUserId) {
                $query->where('requester_id', (int) auth()->id())
                    ->where('addressee_id', $targetUserId)
                    ->where('status', 'accepted')
                    ->orWhere(function ($query) use ($targetUserId) {
                        $query->where('requester_id', $targetUserId)
                            ->where('addressee_id', (int) auth()->id())
                            ->where('status', 'accepted');
                    });
            })
            ->exists();

        if (! $isFriend) {
            $fail('You can only share documents with your accepted friends.');
        }
    },
],        'permission' => ['required', 'in:read,write'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ];
    }
}
