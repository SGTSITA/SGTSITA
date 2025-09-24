<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RastreoIntervals extends Model
{

    protected $table = 'rastreo_intervals';
    protected $fillable = ['task_name', 'interval'];
    
}