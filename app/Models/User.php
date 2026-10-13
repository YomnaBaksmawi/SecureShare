<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
#[Fillable(['name', 'email', 'password', 'role','status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{use HasApiTokens;
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable,SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
    public function documents()
{
    return $this->hasMany(Document::class);
}

public function documentShares()
{
    return $this->hasMany(DocumentShare::class);
}
public function sentFriendRequests()
{
    return $this->hasMany(Friendship::class, 'requester_id');
}

public function receivedFriendRequests()
{
    return $this->hasMany(Friendship::class, 'addressee_id');
}
}
