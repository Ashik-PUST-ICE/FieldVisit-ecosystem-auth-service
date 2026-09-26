<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{

    protected $guarded = [];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }


    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }
}
