<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'due_time',
        'completed',
    ];

    protected $guarded = [];

    protected $casts = [
        'completed' => 'boolean',
        'due_time' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}