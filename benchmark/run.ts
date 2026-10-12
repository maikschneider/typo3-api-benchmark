import { existsSync, mkdirSync, readFileSync, writeFileSync } from 'node:fs';
import { basename, join } from 'node:path';
import { parseArgs } from 'node:util';
import { BASE_URL, contenders, suites } from './contenders';
import { get, k6HostArgs } from './http';
import { renderReport } from './report';

// Load test: per suite point and API, flush all caches, warm up outside the measured range,
// then one pass over unique URLs (all cache misses) and a second pass over the same URLs
// (all cache hits). The whole matrix runs --repeat times, round-robin over the APIs, so noise
// from the host spreads evenly.
// Usage: bun benchmark/run.ts [--suites base,size,depth,concurrency] [--contenders tcaapi,t3api] [--vus 8] [--repeat 3]

const root = join(import.meta.dir, '..');
const { values: args } = parseArgs({
  options: {
    suites: { type: 'string' },
    contenders: { type: 'string' },
    vus: { type: 'string', default: '8' },
    repeat: { type: 'string', default: '3' },
  },
});

const pick = <T extends { key: string }>(all: T[], list?: string): T[] =>
  list ? all.filter((entry) => list.split(',').includes(entry.key)) : all;

const run = (cmd: string[]): string => {
  const result = Bun.spawnSync(cmd, { cwd: root, stderr: 'pipe' });
  if (result.exitCode !== 0) {
    throw new Error(`${cmd.join(' ')} failed:\n${result.stderr.toString()}`);
  }
  return result.stdout.toString();
};

const mode = existsSync(join(root, '.ddev/php/zz-bench-prod.ini')) ? 'prod' : 'dev';
const lock = JSON.parse(readFileSync(join(root, 'composer.lock'), 'utf8'));
const versions = Object.fromEntries(
  lock.packages
    .filter((p: { name: string }) => /^(typo3\/cms-core|maikschneider\/tca-api|sourcebroker\/t3api|sgalinski\/sg-apicore)$/.test(p.name))
    .map((p: { name: string; version: string }) => [p.name, p.version]),
);

const stamp = new Date().toISOString().replace(/[:.]/g, '-').slice(0, 19);
const outDir = join(import.meta.dir, 'results', `${stamp}-${mode}`);
mkdirSync(outDir, { recursive: true });

const selectedSuites = pick(suites, args.suites);
const selectedContenders = pick(contenders, args.contenders);
const repetitions = Number(args.repeat);

console.log(`Mode: ${mode}, suites: ${selectedSuites.map((s) => s.key).join(', ')}, repetitions: ${repetitions}, results: ${outDir}`);
if (mode !== 'prod') {
  console.log('Warning: not in prod mode, run `bin/env prod` for publishable numbers.');
}

const runs: Array<{ suite: string; point: string; contender: string; pass: string; repetition: number; vus: number; file: string }> = [];

for (let repetition = 1; repetition <= repetitions; repetition++) {
  for (const suite of selectedSuites) {
    for (const point of suite.points) {
      for (const contender of selectedContenders) {
        const vus = point.vus ?? Number(args.vus);
        run(['ddev', 'exec', 'vendor/bin/typo3', 'cache:flush']);
        for (const path of point.warmup(contender)) {
          await get(path);
        }

        const urlsFile = join(outDir, `${suite.key}-${point.key}-${contender.key}.urls.json`);
        writeFileSync(urlsFile, JSON.stringify(point.urls(contender).map((path) => BASE_URL + path)));

        for (const pass of ['miss', 'hit']) {
          const file = join(outDir, `${suite.key}-${point.key}-${contender.key}-${pass}-${repetition}.json`);
          process.stdout.write(`[${repetition}/${repetitions}] ${suite.label} / ${point.label} / ${contender.label} / ${pass} … `);
          run(['k6', 'run', '--quiet', '--no-color', '-e', `URLS_FILE=${urlsFile}`, '-e', `SUMMARY_FILE=${file}`, '-e', `VUS=${vus}`, ...k6HostArgs(), 'benchmark/k6/pass.ts']);
          runs.push({ suite: suite.key, point: point.key, contender: contender.key, pass, repetition, vus, file: basename(file) });
          console.log('done');
        }
      }
    }
  }
}

writeFileSync(join(outDir, 'meta.json'), JSON.stringify({ mode, vus: Number(args.vus), repetitions, versions, date: new Date().toISOString(), runs }, null, 2));
writeFileSync(join(outDir, 'report.md'), renderReport(outDir));
console.log(`Report: ${join(outDir, 'report.md')}`);
