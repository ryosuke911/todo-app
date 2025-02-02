<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class User extends Authenticatable
{
    use HasFactory, Notifiable, CanResetPassword;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function todos()
    {
        return $this->hasMany(Todo::class);
    }

    public function tags()
    {
        return $this->hasMany(Tag::class);
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function verifyEmail()
    {
        $this->email_verified_at = now();
        $this->save();
    }

    public function sendPasswordResetNotification($token)
    {
        $url = route('password.reset', ['token' => $token]);

        $this->notify(new \Illuminate\Auth\Notifications\ResetPassword($token));
    }
}