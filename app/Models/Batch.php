<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Batch extends Model
{
    use HasFactory, SoftDeletes;

    public function bootcamp()
    {
        return $this->hasOne(Bootcamp::class, 'id', 'bootcamp_id');
    }
}
