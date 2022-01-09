<?php

namespace App\Models;

use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'verification_code',
        'code_expired_at',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'code_expired_at'   => 'datetime',
    ];



    ########################## Accessors & Mutators ##########################

        // prepare password before register
        public function setPasswordAttribute($value)
        {
            $this->attributes['password'] = Hash::make($value);
        }

        // prepare avatar before store
        public function setAvatarAttribute($file)
        {
            $fileName = time() . '.' . $file->extension();

            $file->move(public_path('uploads/images'), $fileName);

            $this->attributes['avatar'] = $fileName;
        }


        public function getAvatarAttribute()
        {
            return $this->attributes['avatar'] ? asset('uploads/images/' . $this->attributes['avatar']) : null;
        }



}
