<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'picture', 'description', 'category_id',
        'is_quiz_locked', 'starts_at', 'ends_at', 'scoreboard_id'
    ];

    public static function storeImage($request)
    {
        if ($request->hasFile('profile_picture')) {
            $newImageName = uniqid() . '-' . $request->title . '.' . $request->file('profile_picture')->getClientOriginalExtension();
            $request->file('profile_picture')->move(public_path('images'), $newImageName);
            return $newImageName;
        }
        return null;
    }


    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function userQuizzes()
    {
        return $this->belongsToMany(User::class, 'my_quizzes', 'quiz_id', 'user_id');
    }

    public function savedQuizzes()
    {
        return $this->belongsToMany(User::class, 'saved_quizzes', 'quiz_id', 'user_id');
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function scoreboard()
    {
        return $this->belongsTo(Scoreboard::class);
    }

    public function teams()
    {
        return $this->hasMany(Team::class);
    }
}
