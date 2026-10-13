<?php

namespace App\Models;

use Database\Factories\DocumentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    /** @use HasFactory<DocumentFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'original_name',
        'disk',
        'storage_path',
        'mime_type',
        'size',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function shares()
    {
        return $this->hasMany(DocumentShare::class);
    }
}