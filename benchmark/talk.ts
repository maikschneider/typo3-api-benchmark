import { mkdirSync, readFileSync, readdirSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';

// Builds docs/talk/index.html from talk/: every slide with its speaker notes as a readable page,
// plus a presentation mode. Images live in docs/talk/assets/ and are referenced as /_blob/<id> in the slides.
// Usage: bun benchmark/talk.ts

const root = join(import.meta.dir, '..');
const talkDir = join(root, 'talk');
const outDir = join(root, 'docs/talk');
const assets = Object.fromEntries(
  readdirSync(join(outDir, 'assets')).map((file) => [file.replace(/\.\w+$/, ''), `assets/${file}`]),
);

// The notes of these slides are preparation for the Q&A, not part of the talk.
const unpublishedNotes = new Set(['thanks']);

const { order } = JSON.parse(readFileSync(join(talkDir, 'deck.json'), 'utf8')) as { order: string[] };

const slides = order.map((id, index) => {
  const html = readFileSync(join(talkDir, 'slides', `${id}.html`), 'utf8').replace(
    /\/_blob\/([0-9a-f]{32})/g,
    (match, blob: string) => assets[blob] ?? match,
  );
  const missing = html.match(/\/_blob\/[0-9a-f]{32}/);
  if (missing) throw new Error(`${id}: no file in docs/talk/assets/ for ${missing[0]}`);

  const notes = unpublishedNotes.has(id)
    ? []
    : [...html.matchAll(/<aside[^>]*>([\s\S]*?)<\/aside>/g)]
        .flatMap((m) => m[1].split('\n'))
        .map((line) => line.replace(/^\[\d+:\d{2}\]\s*/, '').trim())
        .filter(Boolean);

  return `<article class="entry" id="folie-${index + 1}">
  <div class="frame"><div class="slide">${html.replace(/<aside[^>]*>[\s\S]*?<\/aside>/g, '').trim()}</div></div>
  <div class="notes">
    <a class="number" href="#folie-${index + 1}">${index + 1}</a>
    ${notes.map((p) => `<p>${p}</p>`).join('\n    ')}
  </div>
</article>`;
});

const page = `<!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>TCA rein, JSON-LD raus</title>
<meta name="description" content="Vortrag zu tca_api auf dem TYPO3 Camp Berlin 2026: alle Folien mit Sprechertext.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;700&display=swap">
<style>
:root {
  color-scheme: light;
  --page: #f4f2ec;
  --ink: #1b2028;
  --ink-2: #4a5260;
  --muted: #5f6672;
  --accent: #b45309;
  --border: #ddd8cc;
}
@media (prefers-color-scheme: dark) {
  :root:not([data-theme="light"]) {
    color-scheme: dark;
    --page: #12161d;
    --ink: #f4f2ec;
    --ink-2: #c5ccd6;
    --muted: #8a93a1;
    --accent: #f28c28;
    --border: #3a4150;
  }
}
:root[data-theme="dark"] {
  color-scheme: dark;
  --page: #12161d;
  --ink: #f4f2ec;
  --ink-2: #c5ccd6;
  --muted: #8a93a1;
  --accent: #f28c28;
  --border: #3a4150;
}
* { box-sizing: border-box; }
body { margin: 0; background: var(--page); color: var(--ink); font: 18px/1.6 'IBM Plex Sans', Arial, sans-serif; }
a { color: var(--accent); }
header, main { max-width: 1040px; margin: 0 auto; padding: 0 16px; }
header { padding-top: 56px; padding-bottom: 24px; }
header .eyebrow { margin: 0; font: 500 14px/1.4 'JetBrains Mono', monospace; letter-spacing: 2px; text-transform: uppercase; color: var(--accent); }
header h1 { margin: 8px 0 12px; font-size: clamp(32px, 6vw, 52px); line-height: 1.1; }
header p { margin: 0 0 8px; color: var(--ink-2); }
.actions { display: flex; flex-wrap: wrap; gap: 12px 24px; align-items: center; margin-top: 20px; }
button.present { font: 600 16px/1 'IBM Plex Sans', Arial, sans-serif; padding: 12px 20px; border: 0; border-radius: 6px; background: var(--accent); color: #12161d; cursor: pointer; }
.entry { padding: 40px 0; border-top: 1px solid var(--border); }
.frame { position: relative; outline: 1px solid var(--border); aspect-ratio: 16 / 9; overflow: hidden; border-radius: 8px; box-shadow: 0 8px 24px rgba(18, 22, 29, 0.12); }
.slide { position: absolute; top: 0; left: 0; width: 1920px; height: 1080px; transform-origin: 0 0; transform: scale(var(--scale, 0.5)); }
.slide > section { position: relative; width: 1920px; height: 1080px; overflow: hidden; }
.slide section * { margin: 0; }
.notes { display: grid; grid-template-columns: 40px 1fr; gap: 0 16px; margin-top: 20px; color: var(--ink-2); }
.notes .number { grid-row: 1 / 99; font: 500 15px/1.9 'JetBrains Mono', monospace; color: var(--muted); text-decoration: none; }
.notes p { grid-column: 2; margin: 0 0 12px; max-width: 68ch; }
@media (max-width: 520px) { .notes { grid-template-columns: 1fr; } .notes .number { grid-row: auto; } .notes p { grid-column: 1; } }
.notes { overflow-wrap: anywhere; }
footer { max-width: 1040px; margin: 0 auto; padding: 32px 16px 64px; border-top: 1px solid var(--border); color: var(--muted); font-size: 15px; }

body.presenting { overflow: hidden; background: #000; }
body.presenting > :not(main), body.presenting .entry:not(.current), body.presenting .notes { display: none; }
body.presenting main { max-width: none; padding: 0; }
body.presenting .entry.current { position: fixed; inset: 0; display: grid; place-items: center; padding: 0; border: 0; }
body.presenting .frame { width: min(100vw, calc(100vh * 16 / 9)); border-radius: 0; box-shadow: none; }
.hint { position: fixed; right: 16px; bottom: 12px; font: 13px 'JetBrains Mono', monospace; color: #8a93a1; display: none; }
body.presenting .hint { display: block; }
</style>
</head>
<body>
<header>
  <p class="eyebrow">TYPO3 Camp Berlin 2026 · Maik Schneider</p>
  <h1>TCA rein, JSON-LD raus.</h1>
  <p>Einfach konfiguriert, schnell ausgeliefert: eine REST-API für jede TCA-Tabelle mit <a href="https://github.com/maikschneider/tca-api">tca_api</a>. Alle Folien mit dem Sprechertext, die Messwerte stammen aus dem <a href="../">Benchmark</a> in diesem Repository.</p>
  <div class="actions">
    <button class="present" type="button">Präsentieren</button>
    <span>Pfeiltasten blättern, F für Vollbild, Esc beendet</span>
  </div>
</header>
<main>
${slides.join('\n')}
</main>
<footer>Folien und Benchmark: <a href="https://github.com/maikschneider/typo3-api-benchmark">github.com/maikschneider/typo3-api-benchmark</a> · Extension: <a href="https://github.com/maikschneider/tca-api">github.com/maikschneider/tca-api</a></footer>
<p class="hint">← → · F · Esc</p>
<script>
const entries = [...document.querySelectorAll('.entry')];
const resize = new ResizeObserver((items) => {
  for (const item of items) item.target.style.setProperty('--scale', item.contentRect.width / 1920);
});
document.querySelectorAll('.frame').forEach((frame) => resize.observe(frame));

let current = 0;
const show = (index) => {
  current = Math.max(0, Math.min(entries.length - 1, index));
  entries.forEach((entry, i) => entry.classList.toggle('current', i === current));
  history.replaceState(null, '', '#' + entries[current].id);
};
const start = () => {
  const fromHash = entries.findIndex((entry) => '#' + entry.id === location.hash);
  document.body.classList.add('presenting');
  show(fromHash >= 0 ? fromHash : 0);
};
const stop = () => {
  document.body.classList.remove('presenting');
  if (document.fullscreenElement) document.exitFullscreen();
  entries[current].scrollIntoView();
};
document.querySelector('.present').addEventListener('click', start);
document.addEventListener('keydown', (event) => {
  if (!document.body.classList.contains('presenting')) return;
  if (['ArrowRight', 'ArrowDown', 'PageDown', ' '].includes(event.key)) show(current + 1);
  else if (['ArrowLeft', 'ArrowUp', 'PageUp'].includes(event.key)) show(current - 1);
  else if (event.key === 'Home') show(0);
  else if (event.key === 'End') show(entries.length - 1);
  else if (event.key === 'Escape' && !document.fullscreenElement) stop();
  else if (event.key === 'f' || event.key === 'F') {
    document.fullscreenElement ? document.exitFullscreen() : document.documentElement.requestFullscreen();
  } else return;
  event.preventDefault();
});
document.addEventListener('click', (event) => {
  if (!document.body.classList.contains('presenting')) return;
  show(event.clientX > innerWidth / 2 ? current + 1 : current - 1);
});
</script>
</body>
</html>
`;

mkdirSync(outDir, { recursive: true });
writeFileSync(join(outDir, 'index.html'), page);
console.log(`docs/talk/index.html: ${slides.length} slides`);
