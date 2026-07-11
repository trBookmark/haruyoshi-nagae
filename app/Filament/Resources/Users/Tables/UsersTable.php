<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\UserRole;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
  /**
   * configure
   *
   * @param  \Filament\Tables\Table $table
   * @return \Filament\Tables\Table
   */
  public static function configure(Table $table): Table
  {
    return $table
      // 自分自身は一覧に表示しない
      ->modifyQueryUsing(fn ($query) => $query->where('id', '!=', auth()->id()))
      ->columns([
        ImageColumn::make('avatar')
          ->label('アバター')
          ->disk(config('image.avatar.disk'))
          ->circular()
          // avatar フォールバック: ui-avatars.com（外部）
          // CSP を設定する場合は img-src に https://ui-avatars.com を追加すること
          ->defaultImageUrl(fn () => 'https://ui-avatars.com/api/?name=User&background=e5e7eb&color=6b7280'),

        TextColumn::make('name')
          ->label('ユーザー名')
          ->searchable()
          ->sortable(),

        TextColumn::make('email')
          ->label('メールアドレス')
          ->searchable()
          ->sortable(),

        TextColumn::make('role')
          ->label('権限')
          ->badge()
          ->formatStateUsing(fn (UserRole $state) => $state->label())
          ->color(fn (UserRole $state) => match ($state) {
            UserRole::ADMIN  => 'danger',
            UserRole::EDITOR => 'gray',
          })
          ->sortable(),

        TextColumn::make('created_at')
          ->label('作成日時')
          ->dateTime('Y/m/d H:i')
          ->sortable(),

      ])
      ->filters([

        SelectFilter::make('role')
          ->label('権限')
          ->options(UserRole::options()),

      ])
      ->recordActions([
        EditAction::make(),
        DeleteAction::make(),
      ])
      ->toolbarActions([
        BulkActionGroup::make([
          DeleteBulkAction::make(),
        ]),
      ])
      ->defaultSort('created_at', 'desc');
  }
}
