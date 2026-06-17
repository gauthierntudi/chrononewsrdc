<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;

class User extends Authenticatable
{
    use Notifiable;

    /** Table legacy (MyISAM) — colonnes d'origine conservées. */
    protected $table = 'users';

    const CREATED_AT = 'created_at';

    const UPDATED_AT = null;

    protected $fillable = [
        'nom',
        'mail',
        'role',
        'otp',
        'otp_expiration',
        'num_user',
        'cover',
        'Titre',
        'telephone',
        'bio',
        'Facebook',
        'Youtube',
        'Twitter',
        'Instagram',
        'status',
        'mdp',
        'connect',
    ];

    protected $hidden = [
        'otp',
        'mdp',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'role' => UserRole::class,
            'otp_expiration' => 'datetime',
            'status' => 'integer',
            'connect' => 'integer',
        ];
    }

    public function getNameAttribute(): ?string
    {
        return $this->attributes['nom'] ?? null;
    }

    public function setNameAttribute(?string $value): void
    {
        $this->attributes['nom'] = $value;
    }

    public function getEmailAttribute(): ?string
    {
        return $this->attributes['mail'] ?? null;
    }

    public function setEmailAttribute(?string $value): void
    {
        $this->attributes['mail'] = $value ?? '';
    }

    public function getJobTitleAttribute(): ?string
    {
        return $this->attributes['Titre'] ?? null;
    }

    public function getAvatarAttribute(): ?string
    {
        return $this->attributes['cover'] ?? null;
    }

    public function getPhoneAttribute(): ?string
    {
        return $this->attributes['telephone'] ?? null;
    }

    public function getUserNumberAttribute(): ?string
    {
        return $this->attributes['num_user'] ?? null;
    }

    public function isActive(): bool
    {
        return (int) ($this->attributes['status'] ?? 0) === 1;
    }

    public function routeNotificationForMail(): string
    {
        return (string) $this->mail;
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'user_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'user_id');
    }

    public function advertisements(): HasMany
    {
        return $this->hasMany(Advertisement::class, 'user_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class, 'user_id');
    }

    public function articlePurchases(): HasMany
    {
        return $this->hasMany(ArticlePurchase::class, 'user_id');
    }

    public function hasActiveSubscription(): bool
    {
        if (! Schema::hasTable('user_subscriptions')) {
            return false;
        }

        $query = $this->subscriptions()->where('status', 'active');

        if (Schema::hasColumn('user_subscriptions', 'ends_at')) {
            $query->where('ends_at', '>', now());
        } else {
            $query->where('end_date', '>', now());
        }

        return $query->exists();
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, [UserRole::SuperAdmin, UserRole::Admin], true);
    }

    public function isStaff(): bool
    {
        return $this->role?->isStaff() ?? false;
    }

    public function toAuthArray(): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->name,
            'email' => $this->email,
            'role' => $this->role?->value,
            'titre' => $this->job_title,
            'cover' => $this->avatar,
            'telephone' => $this->phone,
            'bio' => $this->bio,
            'facebook' => $this->Facebook,
            'youtube' => $this->Youtube,
            'twitter' => $this->Twitter,
            'instagram' => $this->Instagram,
            'num_user' => $this->user_number,
        ];
    }

    /** @return array<string, mixed> */
    public function toAdminArray(): array
    {
        return array_merge($this->toAuthArray(), [
            'status' => (int) ($this->attributes['status'] ?? 0),
            'connect' => (int) ($this->attributes['connect'] ?? 0),
            'created_at' => $this->created_at,
        ]);
    }
}
