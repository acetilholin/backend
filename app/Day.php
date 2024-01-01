<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Day extends Model
{
    protected $fillable = ['date','day_type','month_id','day_start','day_end','break_start','break_end'];
}
