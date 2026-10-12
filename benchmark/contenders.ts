export const BASE_URL = process.env.BENCH_BASE_URL ?? 'https://typo3-api-benchmark.ddev.site';

export interface Contender {
  key: string;
  label: string;
  package: string;
  list: (page: number, size: number) => string;
  item: (uid: number) => string;
  /** Configuration the package cannot carry itself and the project has to add. */
  projectConfig: string[];
}

export const contenders: Contender[] = [
  {
    key: 'tcaapi',
    label: 'tca_api',
    package: 'packages/bench_tcaapi',
    list: (page, size) => `/_api/articles?page=${page}&itemsPerPage=${size}`,
    item: (uid) => `/_api/articles/${uid}`,
    projectConfig: ['site set `maikschneider/tca-api`'],
  },
  {
    key: 't3api',
    label: 't3api',
    package: 'packages/bench_t3api',
    list: (page, size) => `/_t3api/articles?order%5Buid%5D=asc&page=${page}&itemsPerPage=${size}`,
    item: (uid) => `/_t3api/articles/${uid}`,
    projectConfig: ['site config `imports` of the route enhancer'],
  },
  {
    key: 'sgapicore',
    label: 'sg_apicore',
    package: 'packages/bench_sgapicore',
    list: (page, size) => `/_sgapi/bench/v1/articles?sort=uid&page=${page}&perPage=${size}`,
    item: (uid) => `/_sgapi/bench/v1/articles/${uid}`,
    projectConfig: ['`apiPathPrefix` and `activateDemoApis` in `config/system/additional.php`'],
  },
  {
    key: 'recordapi',
    label: 'Record API (hand-written)',
    package: 'packages/bench_recordapi',
    list: (page, size) => `/_recordapi/articles?page=${page}&itemsPerPage=${size}`,
    item: (uid) => `/_recordapi/articles/${uid}`,
    projectConfig: [],
  },
  {
    key: 'extbase',
    label: 'Extbase (hand-written)',
    package: 'packages/bench_extbase',
    list: (page, size) => `/_extbase/articles/${page}/${size}.json`,
    item: (uid) => `/_extbase/articles/${uid}.json`,
    projectConfig: ['site set `typo3-api-benchmark/extbase`', 'site config `imports` of the route enhancer'],
  },
];

export interface Scenario {
  key: string;
  label: string;
  /** Measured URLs: every one is requested once per pass, so the first pass after a flush only sees cache misses. */
  urls: (c: Contender) => string[];
  /** Requests outside the measured range that warm PHP, TCA and DI caches after the flush. */
  warmup: (c: Contender) => string[];
}

const range = (from: number, to: number): number[] => Array.from({ length: to - from + 1 }, (_, i) => from + i);

export const scenarios: Scenario[] = [
  {
    key: 'list-20',
    label: 'Collection, 20 items',
    urls: (c) => range(1, 200).map((page) => c.list(page, 20)),
    warmup: (c) => range(480, 489).map((page) => c.list(page, 20)),
  },
  {
    key: 'list-100',
    label: 'Collection, 100 items',
    urls: (c) => range(1, 80).map((page) => c.list(page, 100)),
    warmup: (c) => range(91, 95).map((page) => c.list(page, 100)),
  },
  {
    key: 'item',
    label: 'Single item',
    urls: (c) => range(1001, 1500).map((uid) => c.item(uid)),
    warmup: (c) => range(9001, 9010).map((uid) => c.item(uid)),
  },
];
