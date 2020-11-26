<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Month extends Model
{
    protected $fillable = ['employee_id','updated_at','created_at','date'];

    public function days()
    {
        return $this->hasMany(Day::class);
    }
}
