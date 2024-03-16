<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public static function generateHexToken()
    {
        $confirmStr = random_bytes(16);
        $toHex = bin2hex($confirmStr);
        return $toHex;
    }

    public static function checkAuth($authClass)
    {
        $user = $authClass::guard('sanctum')->user();
        return $user;
    }

    public static function storeImage($request)
    {
        if ($request->hasFile('profile_picture')) {
            $newImageName = uniqid() . '-' . $request->title . '.' . $request->file('profile_picture')->getClientOriginalExtension();
            $request->file('profile_picture')->move(public_path('images'), $newImageName);
            return $newImageName;
        }
        return null;
    }

    public static function sendMail($mailModel, $to, $mailType, $subject, $content)
    {
        $mailModel::to($to)->send(new $mailType($subject, $content));
    }

    public static function createAuthToken($obj, $tokenName, $token = null)
    {
        if ($obj instanceof \App\Models\User) $token = $obj->createToken($tokenName)->plainTextToken;
        return $token;
    }

    public static function hashPassword($hashModel, $data)
    {
        $hashedPassword = $hashModel::make($data);
        return $hashedPassword;
    }

    public static function comparePassword($hashModel, $candidate, $currentPassword)
    {
        return $hashModel::check($candidate, $currentPassword);
    }

    public static function revokeSetToken($obj, $model, $name)
    {
        $obj->currentAccessToken()->delete();
        $refreshToken = $model::createAuthToken($obj, $name);
        return $refreshToken;
    }

    protected $fillable = [
        'username',
        'email',
        'password',
        'profile_picture',
        'confirm_email_token',
        'api_token'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'api_token',
        'confirm_email_token',
        'forgot_password_token'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    public function role()
    {
        return $this->hasOne(Role::class);
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class);
    }

    public function myQuizzes()
    {
        return $this->belongsToMany(Quiz::class, 'my_quizzes', 'user_id', 'quiz_id');
    }

    public function savedQuizzes()
    {
        return $this->belongsToMany(Quiz::class, 'saved_quizzes', 'user_id', 'quiz_id');
    }
}
