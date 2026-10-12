import { existsSync, readFileSync, rmSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';
import type { Contender } from './contenders';

// APIs that change every request of the install (global hooks, XCLASSes) are not installed by
// default. activate() adds one for its own measurement phase; deactivate() restores composer.json,
// composer.lock and the site config from git, so uncommitted changes to those files are lost.

const root = join(import.meta.dir, '..');
const siteConfig = join(root, 'config/sites/main/config.yaml');

const run = (...cmd: string[]): void => {
  const result = Bun.spawnSync(cmd, { cwd: root, stderr: 'pipe', stdout: 'pipe' });
  if (result.exitCode !== 0) {
    throw new Error(`${cmd.join(' ')} failed:\n${result.stderr.toString()}`);
  }
};

const isProd = (): boolean => existsSync(join(root, '.ddev/php/zz-bench-prod.ini'));
const composerFlags = (): string[] => (isProd() ? ['--update-no-dev', '--classmap-authoritative'] : []);

function refresh(contender: Contender): void {
  for (const dir of contender.isolated?.cacheDirs ?? []) {
    rmSync(join(root, dir), { recursive: true, force: true });
  }
  run('ddev', 'exec', 'vendor/bin/typo3', 'extension:setup');
  run('ddev', 'exec', 'vendor/bin/typo3', 'cache:flush');
  // OPcache does not revalidate files in prod mode, so added and removed classes need a fresh PHP.
  if (isProd()) {
    run('ddev', 'restart');
  }
}

function addUnder(yaml: string, key: string, lines: string[]): string {
  const missing = lines.filter((line) => !yaml.includes(line));
  if (missing.length === 0) return yaml;
  return yaml.replace(new RegExp(`^${key}:.*$`, 'm'), (match) => [match.replace(/\s*\{\s*\}\s*$/, ''), ...missing.map((line) => `  - ${line}`)].join('\n'));
}

export function activate(contender: Contender): void {
  if (!contender.isolated) return;
  console.error(`Installing ${contender.label} for its own phase`);
  run('ddev', 'composer', 'require', '--no-interaction', ...composerFlags(), ...contender.isolated.packages);
  let yaml = readFileSync(siteConfig, 'utf8');
  yaml = addUnder(yaml, 'imports', contender.isolated.siteImports);
  yaml = addUnder(yaml, 'dependencies', contender.isolated.siteDependencies);
  writeFileSync(siteConfig, yaml);
  refresh(contender);
}

export function deactivate(contender: Contender): void {
  if (!contender.isolated) return;
  console.error(`Removing ${contender.label} again`);
  run('git', 'checkout', '--', 'composer.json', 'composer.lock', 'config/sites/main/config.yaml');
  run('ddev', 'composer', 'install', '--no-interaction', ...(isProd() ? ['--no-dev', '--classmap-authoritative'] : []));
  refresh(contender);
}
