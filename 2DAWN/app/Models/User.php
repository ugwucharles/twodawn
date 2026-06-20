<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'is_organizer',
        'instagram_handle',
        'whatsapp_number',
        'twitter_handle',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_organizer' => 'boolean',
        ];
    }

    /**
     * Get the events organized by this user.
     */
    public function events()
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Get the wallet for this user.
     */
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    /**
     * Get or create wallet for this user.
     */
    public function getWallet()
    {
        return $this->wallet ?? $this->wallet()->create([
            'balance' => 0,
            'total_earnings' => 0,
            'total_withdrawn' => 0,
        ]);
    }
}
