import { setupDrawer } from './menu';
import { setupReveal } from './reveal';

// 静的アセット（Blade の Vite::asset() から参照する画像）をビルド対象に含める
// 遅延形式だと Vite 8 で未使用と判定され出力されないため eager を指定
import.meta.glob(['../images/**'], { eager: true, query: '?url', import: 'default' });

// Vite の script は type="module"（deferred）のため DOM 構築後に実行される
setupDrawer();
setupReveal();
