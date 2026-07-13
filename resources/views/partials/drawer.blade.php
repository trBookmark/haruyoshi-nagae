{{-- 開閉ドロワー（<dialog>）：中身とボタンの表示制御は使用するページの機能ブランチで実装 --}}
<button type="button" class="l-drawer-toggle" data-drawer-open aria-label="メニューを開く">
  <span class="l-drawer-toggle__bar"></span>
  <span class="l-drawer-toggle__bar"></span>
  <span class="l-drawer-toggle__bar"></span>
</button>

<dialog class="l-drawer" data-drawer>
  <div class="l-drawer__inner">
    @stack('drawer')
  </div>
</dialog>
