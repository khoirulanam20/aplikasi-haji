<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Concerns\LogsModelActivity;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'username', 'email', 'password', 'avatar_path', 'google_id', 'is_active', 'preferences', 'two_factor_secret', 'two_factor_confirmed_at'])]
#[Hidden(['password', 'remember_token', 'two_factor_secret'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, LogsModelActivity, Notifiable;

    public function getActivitylogOptions(): LogOptions
    {
        return $this->baseActivitylogOptions()
            ->logExcept(['password', 'remember_token', 'two_factor_secret']);
    }

    protected string $guard_name = 'web';

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
            'is_active' => 'boolean',
            'preferences' => 'array',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function avatarUrl(): ?string
    {
        if ($this->avatar_path === null || $this->avatar_path === '') {
            return null;
        }

        if (filter_var($this->avatar_path, FILTER_VALIDATE_URL)) {
            return $this->avatar_path;
        }

        return Storage::disk('public')->url(ltrim($this->avatar_path, '/'));
    }

    public function avatarInitial(): string
    {
        return strtoupper(substr($this->name ?: 'U', 0, 1));
    }

    public function hajjParticipant(): HasOne
    {
        return $this->hasOne(HajjParticipant::class);
    }
}
