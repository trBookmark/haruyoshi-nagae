<?php

namespace App\Concerns;

trait EnumHasOptions
{

  /**
   * 全ケースをラベル付きの配列で返す（Filament の Select などで使用）
   *
   * @return array<string, string> ['value' => 'label'] の形式で返す
   */
  public static function options(): array
  {
    return array_column(
      array_map(
        fn(self $case) => ['value' => $case->value, 'label' => $case->label()],
        self::cases()
      ),
      'label',
      'value'
    );
  }
}
