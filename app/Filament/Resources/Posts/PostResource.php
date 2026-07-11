<?php

namespace App\Filament\Resources\Posts;

use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Filament\Resources\Posts\Schemas\PostForm;
use App\Filament\Resources\Posts\Tables\PostsTable;
use App\Models\Post;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PostResource extends Resource
{
  protected static ?string $model = Post::class;

  protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedDocumentText;
  protected static ?string $navigationLabel                   = 'ブログ';
  protected static ?string $modelLabel                        = 'ブログ';
  protected static string | UnitEnum | null $navigationGroup  = '作品管理';
  protected static ?int    $navigationSort                    = 10;

  // 削除確認モーダルやパンくずに記事タイトルを表示する
  protected static ?string $recordTitleAttribute = 'title';

  /**
   * form
   *
   * @param  \Filament\Schemas\Schema $schema
   * @return \Filament\Schemas\Schema
   */
  public static function form(Schema $schema): Schema
  {
    return PostForm::configure($schema);
  }

  /**
   * table
   *
   * @param  \Filament\Tables\Table $table
   * @return \Filament\Tables\Table
   */
  public static function table(Table $table): Table
  {
    return PostsTable::configure($table);
  }

  /**
   * getPages
   *
   * @return array
   */
  public static function getPages(): array
  {
    return [
      'index'  => ListPosts::route('/'),
      'create' => CreatePost::route('/create'),
      'edit'   => EditPost::route('/{record}/edit'),
    ];
  }
}
