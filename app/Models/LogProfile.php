<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogProfile extends Model
{
    use HasFactory;

    protected $table = 'log_profiles';
    protected $guarded = [];

    public function getUser()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getUpdatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
