<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogNotification extends Model
{
    use HasFactory;

    protected $table = 'log_notifications';
    protected $guarded = [];
}
