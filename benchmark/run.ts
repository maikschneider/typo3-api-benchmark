import { mkdirSync, writeFileSync, existsSync, readFileSync } from 'node:fs';
import { join } from 'node:path';
import { parseArgs } from 'node:util';
import { BASE_URL, contenders, scenarios } from './contenders';
import { renderReport } from './report';

// Load test: per contender and scenario, flush all caches, warm up outside the measured
// range, then run one pass over unique URLs (all cache misses) and a second pass over the
// same URLs (all cache hits).
// Every combination runs --repeat times, round-robin over the APIs, so noise from the host spreads evenly.
// Usage: bun benchmark/run.ts [--contenders tcaapi,t3api] [--scenarios list-20,item] [--vus 8] [--repeat 3]

const root = join(import.meta.dir, '..');
const { values: args } = parseArgs({
  options: {
    contenders: { type: 'string' },
    scenarios: { type: 'string' },
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

console.log(`Mode: ${mode}, VUs: ${args.vus}, results: ${outDir}`);
if (mode !== 'prod') {
  console.log('Warning: not in prod mode, run `bin/env prod` for publishable numbers.');
}

const runs: Array<{ contender: string; scenario: string; pass: string; repetition: number; file: string }> = [];
const repetitions = Number(args.repeat);

for (let repetition = 1; repetition <= repetitions; repetition++) {
  for (const contender of pick(contenders, args.contenders)) {
    for (const scenario of pick(scenarios, args.scenarios)) {
      run(['ddev', 'exec', 'vendor/bin/typo3', 'cache:flush']);
      for (const path of scenario.warmup(contender)) {
        await fetch(BASE_URL + path, { tls: { rejectUnauthorized: false } });
      }

      const urlsFile = join(outDir, `${contender.key}-${scenario.key}.urls.json`);
      writeFileSync(urlsFile, JSON.stringify(scenario.urls(contender).map((path) => BASE_URL + path)));

      for (const pass of ['miss', 'hit']) {
        const file = join(outDir, `${contender.key}-${scenario.key}-${pass}-${repetition}.json`);
        process.stdout.write(`[${repetition}/${repetitions}] ${contender.label} / ${scenario.label} / ${pass} … `);
        run(['k6', 'run', '--quiet', '--no-color', '-e', `URLS_FILE=${urlsFile}`, '-e', `SUMMARY_FILE=${file}`, '-e', `VUS=${args.vus}`, 'benchmark/k6/pass.ts']);
        runs.push({ contender: contender.key, scenario: scenario.key, pass, repetition, file });
        console.log('done');
      }
    }
  }
}

writeFileSync(join(outDir, 'meta.json'), JSON.stringify({ mode, vus: Number(args.vus), repetitions, versions, date: new Date().toISOString(), runs }, null, 2));
writeFileSync(join(outDir, 'report.md'), renderReport(outDir));
console.log(`Report: ${join(outDir, 'report.md')}`);
