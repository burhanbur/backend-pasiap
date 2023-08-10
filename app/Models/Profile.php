<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $table = 'profiles';
    protected $guarded = [];

    public function getUser()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getReligion()
    {
        return $this->belongsTo(RefReligion::class, 'religion');
    }
}
