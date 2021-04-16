<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitTime extends Model
{
    use HasFactory;

    protected $table = 'visit_time';

    protected $fillable = [
        'year',
        'month',
        'day',
        'hour',
        'doctor_id'
    ];
}
