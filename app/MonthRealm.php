<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MonthRealm extends Model
{
    protected $table = 'months_2';
    protected $fillable = ['employee_id','updated_at','created_at','date'];

    public function days()
    {
        return $this->hasMany(DayRealm::class, 'month_id');
    }
}
