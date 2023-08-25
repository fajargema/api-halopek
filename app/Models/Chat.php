<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'konselor_id', 'message'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->select(['id', 'name', 'email']);
    }

    public function konselor()
    {
        return $this->belongsTo(User::class, 'konselor_id')->select(['id', 'name', 'email']);
    }
}
