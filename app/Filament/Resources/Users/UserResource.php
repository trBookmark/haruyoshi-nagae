<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class UserResource extends Resource
{
  protected static ?string $model = User::class;

  protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedUserCircle;
  protected static ?string $navigationLabel                   = 'ユーザー';
  protected static ?string $modelLabel                        = 'ユーザー';
  protected static string | UnitEnum | null $navigationGroup  = 'システム管理';
  protected static ?int    $navigationSort                    = 100;

  /**
   * shouldRegisterNavigation
   * 管理者のみナビゲーションに表示する
   *
   * @return bool
   */
  public static function shouldRegisterNavigation(): bool
  {
    return auth()->user()?->isAdmin() ?? false;
  }

  /**
   * canViewAny
   * 一覧表示：管理者のみ
   * Policy（UserPolicy::viewAny）と対応
   *
   * @return bool
   */
  public static function canViewAny(): bool
  {
    return auth()->user()?->isAdmin() ?? false;
  }

  /**
   * form
   *
   * @param  \Filament\Schemas\Schema $schema
   * @return \Filament\Schemas\Schema
   */
  public static function form(Schema $schema): Schema
  {
    return UserForm::configure($schema);
  }

  /**
   * table
   *
   * @param  \Filament\Tables\Table $table
   * @return \Filament\Tables\Table
   */
  public static function table(Table $table): Table
  {
    return UsersTable::configure($table);
  }

  /**
   * getPages
   *
   * @return array
   */
  public static function getPages(): array
  {
    return [
      'index'  => ListUsers::route('/'),
      'create' => CreateUser::route('/create'),
      'edit'   => EditUser::route('/{record}/edit'),
    ];
  }
}
