// スクロール出現演出
// .js-reveal に .is-waiting を付与し IntersectionObserver で監視
// 画面に入ったら .is-revealed へ切り替える（一度だけ発火）
// 状態スタイルは components/_reveal.scss（.c-reveal）が担当
// JS 無効環境では .is-waiting が付与されず、最初から表示される

/**
 * setupReveal
 * スクロール出現演出を初期化
 *
 * @returns {void}
 */
export const setupReveal = () => {
  const targets = document.querySelectorAll('.js-reveal');

  if (targets.length === 0) {
    return;
  }

  // 「視差効果を減らす」設定時は演出を行わない（最初から表示）
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    return;
  }

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) {
          return;
        }
        entry.target.classList.replace('is-waiting', 'is-revealed');
        observer.unobserve(entry.target); // 一度だけ発火
      });
    },
    {
      threshold: 0.2, // 2割見えたら発火（旧 ScrollReveal の viewFactor）
    }
  );

  targets.forEach((target) => {
    target.classList.add('is-waiting');
    observer.observe(target);
  });
};
