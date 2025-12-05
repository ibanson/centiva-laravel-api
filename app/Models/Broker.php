<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Broker extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'email', 'team_id'];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
