<?php

namespace App\Filament\Support;

/**
 * OverwriteWarning
 * 画像アップロード上書き警告の HTML を生成する共通クラス
 * CategoryForm / ImageForm / PlaylistForm の Placeholder で共用
 */
class OverwriteWarning
{
  /**
   * html
   * アップロードすると現在の画像が上書きされる旨の警告 HTML を返す
   *
   * @return string
   */
  public static function html(): string
  {
    return '<div style="display:flex; align-items:center; gap:8px; padding:10px 14px;'
      . ' background:#fff7ed; border:1px solid #fed7aa; border-radius:6px;'
      . ' color:#9a3412; font-size:0.875rem;">'
      . '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"'
      . ' stroke-width="1.5" stroke="currentColor"'
      . ' style="width:18px; height:18px; flex-shrink:0;">'
      . '<path stroke-linecap="round" stroke-linejoin="round"'
      . ' d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0'
      . ' 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697'
      . ' 16.126ZM12 15.75h.007v.008H12v-.008Z" />'
      . '</svg>'
      . '<span>下のアップロード欄にファイルをアップロードすると、'
      . '<strong>現在の画像は上書・削除されます</strong>。'
      . '変更しない場合は空のままにしてください。</span>'
      . '</div>';
  }
}
