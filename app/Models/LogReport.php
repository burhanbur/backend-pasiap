<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogReport extends Model
{
    use HasFactory;

    protected $table = 'log_reports';
    protected $guarded = [];

    public function getReport()
    {
        return $this->belongsTo(Report::class, 'report_id');
    }

    public function getUser()
    {
        return $this->belongsTo(User::class, 'taken_by');
    }

    public function getStatus()
    {
        return $this->belongsTo(RefReportStatus::class, 'status');
    }
}
