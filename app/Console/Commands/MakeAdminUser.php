<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class MakeAdminUser extends Command
{
  protected $signature   = 'make:admin-user';
  protected $description = '管理者ユーザーを作成する';

  /**
   * handle
   *
   * @return int
   */
  public function handle(): int
  {
    $this->info('管理者ユーザーを作成します。');

    $name                 = $this->ask('ユーザー名');
    $email                = $this->ask('メールアドレス');
    $password             = $this->secret('パスワード');
    $passwordConfirmation = $this->secret('パスワード（確認）');

    $validator = Validator::make(
      [
        'name'                  => $name,
        'email'                 => $email,
        'password'              => $password,
        'password_confirmation' => $passwordConfirmation,
      ],
      [
        'name'     => ['required', 'string', 'max:190', 'unique:users,name'],
        'email'    => ['required', 'email', 'max:190', 'unique:users,email'],
        'password' => ['required', 'string', 'min:8', 'confirmed'],
      ],
      [
        'name.unique'   => '既に使用されているユーザー名です。',
        'email.unique'  => '既に使用されているメールアドレスです。',
        'password.min'  => 'パスワードは8文字以上で入力してください。',
        'password.confirmed' => 'パスワードと確認用パスワードが一致しません。',
      ]
    );

    if ($validator->fails()) {
      foreach ($validator->errors()->all() as $error) {
        $this->error($error);
      }
      return self::FAILURE;
    }

    User::create([
      'name'     => $name,
      'email'    => $email,
      'password' => $password, // User モデルの hashed キャストにより自動ハッシュ化される
      'role'     => UserRole::ADMIN,
    ]);

    $this->info("管理者ユーザー「{$name}」を作成しました。");

    return self::SUCCESS;
  }
}
