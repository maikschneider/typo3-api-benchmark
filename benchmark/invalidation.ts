import { join } from 'node:path';
import { BASE_URL, contenders } from './contenders';

// Cache correctness: warm item and list, change a record through the DataHandler (as a
// backend editor would), then check whether item and list show the new value.
// Usage: bun benchmark/invalidation.ts

const root = join(import.meta.dir, '..');
const ARTICLE = 1042;
const PAGE = 53;

const ddev = (...cmd: string[]): string => {
  const result = Bun.spawnSync(['ddev', ...cmd], { cwd: root, stderr: 'pipe' });
  if (result.exitCode !== 0) throw new Error(`ddev ${cmd.join(' ')}: ${result.stderr}`);
  return result.stdout.toString().trim();
};
const sql = (query: string): string => ddev('mysql', '-N', '-B', '-e', query);
const edit = (table: string, uid: string | number, field: string, value: string): string =>
  ddev('exec', 'vendor/bin/typo3', 'camp:edit', table, String(uid), field, value);
const fetchPath = (path: string): Promise<Response> => fetch(BASE_URL + path, { tls: { rejectUnauthorized: false } });
const get = async (path: string): Promise<string> => (await fetchPath(path)).text();
const queryCount = async (path: string): Promise<number> => Number((await fetchPath(path)).headers.get('x-query-count'));

const authorUid = sql(`SELECT author FROM tx_campdemo_article WHERE uid = ${ARTICLE}`);
const originalTitle = sql(`SELECT title FROM tx_campdemo_article WHERE uid = ${ARTICLE}`);
const originalName = sql(`SELECT name FROM tx_campdemo_author WHERE uid = ${authorUid}`);

const mark = (fresh: boolean): string => (fresh ? '✅ fresh' : '❌ stale');
const rows: string[] = [];

for (const contender of contenders) {
  ddev('exec', 'vendor/bin/typo3', 'cache:flush');
  const item = contender.item(ARTICLE);
  const list = contender.list(PAGE, 20);
  const coldQueries = await queryCount(list);
  const warmQueries = await queryCount(list);
  await get(item);
  await get(item);

  const title = `Edited ${contender.key} ${Date.now()}`;
  edit('tx_campdemo_article', ARTICLE, 'title', title);
  const articleItem = (await get(item)).includes(title);
  const articleList = (await get(list)).includes(title);

  const name = `Renamed ${contender.key} ${Date.now()}`;
  edit('tx_campdemo_author', authorUid, 'name', name);
  const authorItem = (await get(item)).includes(name);
  const authorList = (await get(list)).includes(name);

  rows.push(`| ${contender.label} | ${coldQueries} → ${warmQueries} | ${mark(articleItem)} | ${mark(articleList)} | ${mark(authorItem)} | ${mark(authorList)} |`);
  console.error(`${contender.label} checked`);
}

edit('tx_campdemo_article', ARTICLE, 'title', originalTitle);
edit('tx_campdemo_author', authorUid, 'name', originalName);
ddev('exec', 'vendor/bin/typo3', 'cache:flush');

console.log('| API | List queries, 1st → 2nd request | Article edited → item | Article edited → list | Author edited → item | Author edited → list |');
console.log('|---|---|---|---|---|---|');
console.log(rows.join('\n'));
