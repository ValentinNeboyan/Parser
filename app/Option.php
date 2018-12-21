<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    protected $fillable=['name'];

    public function rents()
    {
       return $this->belongsToMany(Rent::class);
    }
}
