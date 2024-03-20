<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'team_leader',
        'num_of_members',
        'capacity'
    ];

    public function scoreboards()
    {
        return $this->belongsToMany(Scoreboard::class, 'scoreboards_teams', 'team_id', 'scoreboard_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'users_teams', 'team_id', 'user_id');
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }
}
