import { mkdirSync, readFileSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';
import { BASE_URL, contenders } from './contenders';

// Feature checks: every claim in the report is probed against the running API and the database
// instead of being taken from documentation.
// Usage: bun benchmark/features.ts

export type Status = 'yes' | 'partial' | 'no';

export interface Check {
  status: Status;
  detail: string;
}

export const FEATURES = [
  { key: 'pagination', label: 'Paginated collection with total count', group: 'Read' },
  { key: 'item', label: 'Single item endpoint', group: 'Read' },
  { key: 'author', label: 'Embedded author (select)', group: 'Relations' },
  { key: 'tags', label: 'Embedded tags (MM)', group: 'Relations' },
  { key: 'categories', label: 'Embedded categories (type=category)', group: 'Relations' },
  { key: 'batched', label: 'Query count independent of page size', group: 'Performance' },
  { key: 'cache', label: 'Response cache', group: 'Performance' },
  { key: 'freshRecord', label: 'Fresh after editing the record', group: 'Cache correctness' },
  { key: 'freshRelated', label: 'Fresh after editing a related record', group: 'Cache correctness' },
  { key: 'openApi', label: 'OpenAPI document', group: 'Developer experience' },
] as const;

export type FeatureKey = (typeof FEATURES)[number]['key'];

const root = join(import.meta.dir, '..');
const ddev = (...cmd: string[]): string => {
  const result = Bun.spawnSync(['ddev', ...cmd], { cwd: root, stderr: 'pipe' });
  if (result.exitCode !== 0) throw new Error(`ddev ${cmd.join(' ')}: ${result.stderr}`);
  return result.stdout.toString().trim();
};
const sql = (query: string): string[] => ddev('mysql', '-N', '-B', '-e', query).split('\n').filter(Boolean);
const flush = (): string => ddev('exec', 'vendor/bin/typo3', 'cache:flush');
const edit = (table: string, uid: string | number, field: string, value: string): string =>
  ddev('exec', 'vendor/bin/typo3', 'bench:edit', table, String(uid), field, value);

const request = async (path: string): Promise<{ status: number; body: string; queries: number }> => {
  const response = await fetch(BASE_URL + path, { tls: { rejectUnauthorized: false } });
  return { status: response.status, body: await response.text(), queries: Number(response.headers.get('x-query-count')) };
};

const yes = (detail: string): Check => ({ status: 'yes', detail });
const no = (detail: string): Check => ({ status: 'no', detail });
const containsAll = (body: string, values: string[]): boolean => values.every((value) => body.includes(JSON.stringify(value).slice(1, -1)));

async function probe(contender: (typeof contenders)[number], articleUid: number): Promise<Record<FeatureKey, Check>> {
  const page = Math.ceil(articleUid / 20);
  const checks = {} as Record<FeatureKey, Check>;
  const authorUid = sql(`SELECT author FROM tx_bench_article WHERE uid = ${articleUid}`)[0];
  const originalTitle = sql(`SELECT title FROM tx_bench_article WHERE uid = ${articleUid}`)[0];
  const authorName = sql(`SELECT name FROM tx_bench_author WHERE uid = ${authorUid}`)[0];
  const tagTitles = sql(`SELECT t.title FROM tx_bench_tag t JOIN tx_bench_article_tag_mm mm ON mm.uid_foreign = t.uid WHERE mm.uid_local = ${articleUid}`);
  const categoryTitles = sql(`SELECT c.title FROM sys_category c JOIN sys_category_record_mm mm ON mm.uid_local = c.uid WHERE mm.uid_foreign = ${articleUid} AND mm.tablenames = 'tx_bench_article' AND mm.fieldname = 'categories'`);

  flush();
  await request(contender.list(400, 20));

  const list = await request(contender.list(page, 20));
  const total = /"[^"]*total[^"]*"\s*:\s*10000\b/i.test(list.body);
  checks.pagination = list.status === 200 && total ? yes('total of 10,000 reported') : no(list.status === 200 ? 'no total count in the response' : `HTTP ${list.status}`);

  const item = await request(contender.item(articleUid));
  checks.item = item.status === 200 && item.body.includes(`${articleUid}`) ? yes(`HTTP 200`) : no(`HTTP ${item.status}`);
  checks.author = item.body.includes(authorName) ? yes(`name "${authorName}" embedded`) : no('author name missing');
  checks.tags = containsAll(item.body, tagTitles) ? yes(`${tagTitles.length} of ${tagTitles.length} titles embedded`) : no(`titles missing (${tagTitles.join(', ')})`);
  checks.categories = containsAll(item.body, categoryTitles) ? yes(`${categoryTitles.length} of ${categoryTitles.length} titles embedded`) : no('category titles missing');

  flush();
  await request(contender.list(400, 20));
  const small = await request(contender.list(10, 20));
  const large = await request(contender.list(10, 100));
  checks.batched = large.queries <= small.queries + 1
    ? yes(`${small.queries} queries for 20 items, ${large.queries} for 100`)
    : no(`${small.queries} queries for 20 items, ${large.queries} for 100`);

  flush();
  await request(contender.list(400, 20));
  const cold = await request(contender.list(page, 20));
  const warm = await request(contender.list(page, 20));
  await request(contender.item(articleUid));
  await request(contender.item(articleUid));
  checks.cache = warm.queries < cold.queries ? yes(`${cold.queries} → ${warm.queries} queries on the second request`) : no(`${cold.queries} → ${warm.queries} queries`);

  const title = `Edited ${contender.key} ${Date.now()}`;
  edit('tx_bench_article', articleUid, 'title', title);
  const recordItem = (await request(contender.item(articleUid))).body.includes(title);
  const recordList = (await request(contender.list(page, 20))).body.includes(title);
  checks.freshRecord = recordItem && recordList ? yes('item and list updated') : no(`stale: ${[!recordItem && 'item', !recordList && 'list'].filter(Boolean).join(' and ')}`);

  const name = `Renamed ${contender.key} ${Date.now()}`;
  edit('tx_bench_author', authorUid, 'name', name);
  const relatedItem = (await request(contender.item(articleUid))).body.includes(name);
  const relatedList = (await request(contender.list(page, 20))).body.includes(name);
  checks.freshRelated = relatedItem && relatedList ? yes('item and list updated') : no(`stale: ${[!relatedItem && 'item', !relatedList && 'list'].filter(Boolean).join(' and ')}`);

  edit('tx_bench_article', articleUid, 'title', originalTitle);
  edit('tx_bench_author', authorUid, 'name', authorName);

  if (contender.openApi === null) {
    checks.openApi = no('not provided');
  } else if ('note' in contender.openApi) {
    checks.openApi = { status: 'partial', detail: contender.openApi.note };
  } else {
    const document = await request(contender.openApi.url);
    checks.openApi = document.status === 200 && /"openapi"\s*:\s*"3/.test(document.body) ? yes(contender.openApi.url) : no(`HTTP ${document.status} at ${contender.openApi.url}`);
  }

  return checks;
}

if (import.meta.main) {
  // An article with the most relations, so a partially embedded list cannot pass by accident.
  const article = Number(sql('SELECT uid FROM tx_bench_article WHERE tags = 4 AND categories = 3 ORDER BY uid LIMIT 1')[0]);
  const results: Record<string, Record<FeatureKey, Check>> = {};
  for (const contender of contenders) {
    results[contender.key] = await probe(contender, article);
    console.error(`${contender.label} probed`);
  }
  flush();

  const lock = JSON.parse(readFileSync(join(root, 'composer.lock'), 'utf8'));
  const outDir = join(import.meta.dir, 'results');
  mkdirSync(outDir, { recursive: true });
  writeFileSync(join(outDir, 'features.json'), JSON.stringify({ date: new Date().toISOString(), article, lockHash: lock['content-hash'], results }, null, 2));

  const mark = { yes: '✅', partial: '◐', no: '❌' };
  console.log(`| Feature | ${contenders.map((c) => c.label).join(' | ')} |`);
  console.log(`|---|${contenders.map(() => '---').join('|')}|`);
  for (const feature of FEATURES) {
    console.log(`| ${feature.label} | ${contenders.map((c) => `${mark[results[c.key][feature.key].status]} ${results[c.key][feature.key].detail}`).join(' | ')} |`);
  }
}
