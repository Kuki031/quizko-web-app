<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Scoreboard extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'scoreboards_teams', 'scoreboard_id', 'team_id');
    }

    public function quiz()
    {
        return $this->hasOne(Quiz::class);
    }
}
