// ドロワー（<dialog>）の開閉制御
// フォーカストラップ・ESC で閉じる・inert 化はブラウザのネイティブ動作に任せる

/**
 * ドロワーの開閉イベントを登録する
 */
export const setupDrawer = () => {
  const drawer = document.querySelector('[data-drawer]');
  const openButton = document.querySelector('[data-drawer-open]');

  if (!(drawer instanceof HTMLDialogElement) || !openButton) {
    return;
  }

  openButton.addEventListener('click', () => {
    drawer.showModal();
  });

  // パネル外（backdrop）クリックで閉じる
  // パネル内は必ず .l-drawer__inner が受けるため、target が dialog 自身＝backdrop
  drawer.addEventListener('click', (event) => {
    if (event.target === drawer) {
      drawer.close();
    }
  });
};
