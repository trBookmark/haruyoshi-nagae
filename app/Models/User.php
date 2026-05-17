<?php

namespace App\Models;

use App\Enums\UserRole;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
  use HasFactory, Notifiable;

  protected $fillable = [
    'name',
    'email',
    'password',
    'role',   // デフォルト値はマイグレーション側で設定
    'avatar', // nullable, string
  ];

  protected $hidden = [
    'password',
    'remember_token',
  ];

  protected function casts(): array
  {
    return [
      'email_verified_at' => 'datetime',
      'password'          => 'hashed',
      'role'              => UserRole::class,
    ];
  }

  // ──────────── Filament ────────────
  /**
   * canAccessPanel
   *
   * User が Filament Admin Panel にアクセスできるかどうかを判定
   * 現在は管理者/編集者ともに Admin Panel へのアクセス可
   * 操作権限の制御は Policy で実施
   *
   * @param \Filament\Panel $panel
   * @return boolean
   */
  public function canAccessPanel(Panel $panel): bool
  {
    return in_array($this->role, [UserRole::ADMIN, UserRole::EDITOR], strict: true);
  }

  // ──────────── Helper ────────────

  /**
   * isAdmin
   * User が管理者かどうかを判定
   *
   * @return boolean
   */
  public function isAdmin(): bool
  {
    return $this->role === UserRole::ADMIN;
  }

  /**
   * isEditor
   * User が編集者かどうかを判定
   *
   * @return boolean
   */
  public function isEditor(): bool
  {
    return $this->role === UserRole::EDITOR;
  }
}
