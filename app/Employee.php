<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $guarded = [];

    public function months()
    {
        return $this->hasMany('App\Month');
    }
}
