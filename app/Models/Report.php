<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $table = 'reports';
    protected $guarded = [];

    public function getLogReport()
    {
        return $this->hasMany(LogReport::class, 'report_id', 'id');
    }

    public function getCategory()
    {
        return $this->belongsTo(Category::class, 'cat_id');
    }

    public function getReportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function getTakenBy()
    {
        return $this->belongsTo(User::class, 'taken_by');
    }

    public function getStatus()
    {
        return $this->belongsTo(RefReportStatus::class, 'status');
    }
}
