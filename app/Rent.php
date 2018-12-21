<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Rent extends Model
{
    protected $fillable=[
            'name',
            'location',
            'site_id',
            'rent_type',
            'price',
            'description',
            'web_link',
            'status',
    ];


    public function options()
    {
        return $this->belongsToMany(Option::class);
    }
}
