export const BASE_URL = process.env.BENCH_BASE_URL ?? 'https://typo3-api-benchmark.ddev.site';

export interface Contender {
  key: string;
  label: string;
  package: string;
  list: (page: number, size: number) => string;
  item: (uid: number) => string;
  /** Articles with the relation chain author → organization → country resolved to `depth` (0–3). */
  depth: (depth: number, page: number, size: number) => string;
  /** Files of the package that exist only for the depth sweep, as globs relative to the package. */
  depthFiles: string[];
  /** Largest page the API can serve, when it is smaller than the largest step, with the reason. */
  maxPageSize?: { size: number; reason: string };
  /** Configuration the package cannot carry itself and the project has to add. */
  projectConfig: string[];
  /** Public URL of a generated OpenAPI document, or a note where the API documents itself otherwise. */
  openApi: { url: string } | { note: string } | null;
  /**
   * Installed only while this API is measured, because it changes every request of the install.
   * The runner adds it, measures it in a phase of its own and restores the install from git.
   */
  isolated?: {
    reason: string;
    /** Arguments for composer require. */
    packages: string[];
    siteImports: string[];
    siteDependencies: string[];
    /** Caches outside the caching framework that cache:flush does not clear. */
    cacheDirs: string[];
  };
}

export const contenders: Contender[] = [
  {
    key: 'tcaapi',
    label: 'tca_api',
    package: 'packages/bench_tcaapi',
    list: (page, size) => `/_api/articles?page=${page}&itemsPerPage=${size}`,
    item: (uid) => `/_api/articles/${uid}`,
    depth: (depth, page, size) => `/_api/articles-depth-${depth}?page=${page}&itemsPerPage=${size}`,
    depthFiles: ['Configuration/TcaApi/ArticlesDepth*.php'],
    projectConfig: ['site set `maikschneider/tca-api`'],
    openApi: { url: '/_api/openapi.json' },
  },
  {
    key: 't3api',
    label: 't3api',
    package: 'packages/bench_t3api',
    list: (page, size) => `/_t3api/articles?order%5Buid%5D=asc&page=${page}&itemsPerPage=${size}`,
    item: (uid) => `/_t3api/articles/${uid}`,
    depth: (depth, page, size) => `/_t3api/articles-depth-${depth}?order%5Buid%5D=asc&page=${page}&itemsPerPage=${size}`,
    depthFiles: ['Classes/Domain/Model/*Depth.php'],
    projectConfig: ['site config `imports` of the route enhancer'],
    openApi: { note: 'backend module only' },
  },
  {
    key: 'sgapicore',
    label: 'sg_apicore',
    package: 'packages/bench_sgapicore',
    list: (page, size) => `/_sgapi/bench/v1/articles?sort=uid&page=${page}&perPage=${size}`,
    item: (uid) => `/_sgapi/bench/v1/articles/${uid}`,
    depth: (depth, page, size) => `/_sgapi/bench-depth-${depth}/v1/articles?sort=uid&page=${page}&perPage=${size}`,
    depthFiles: ['Configuration/DepthResources.php'],
    maxPageSize: { size: 100, reason: 'PaginationService::MAX_LIMIT is a constant' },
    projectConfig: ['`apiPathPrefix` and `activateDemoApis` in `config/system/additional.php`'],
    openApi: { url: '/_sgapi/bench/v1/docs.json' },
  },
  {
    key: 'nnrestapi',
    label: 'nnrestapi',
    package: 'packages/bench_nnrestapi',
    list: (page, size) => `/api/articles?page=${page}&itemsPerPage=${size}`,
    item: (uid) => `/api/articles/${uid}`,
    depth: (depth, page, size) => `/api/articles-depth/${depth}?page=${page}&itemsPerPage=${size}`,
    depthFiles: ['Classes/Api/ArticlesDepth.php', 'Classes/Domain/Model/*Depth.php', 'Classes/Domain/Repository/ArticleDepthRepository.php'],
    projectConfig: ['site set `typo3-api-benchmark/nnrestapi`', 'site config `imports` of `EXT:nnrestapi/Configuration/Yaml/default.yaml`'],
    openApi: { note: 'backend module only' },
    isolated: {
      reason: 'adds a DELETE query on nnrestapi_sessions to every frontend request and XCLASSes the Extbase QueryFactory and HiddenRestriction',
      packages: [
        'nng/nnhelpers:14.x-dev#225b8ea2d4d6ce04397c12555c57f3d561613b98',
        'nng/nnrestapi:14.x-dev#1f11507baac8a9d6a8f5801dad6b746b1c70195a',
        'typo3-api-benchmark/nnrestapi:@dev',
      ],
      siteImports: ["{ resource: 'EXT:nnrestapi/Configuration/Yaml/default.yaml' }"],
      siteDependencies: ['typo3-api-benchmark/nnrestapi'],
      cacheDirs: ['var/cache/code/nnhelpers'],
    },
  },
  {
    key: 'recordapi',
    label: 'Record API',
    package: 'packages/bench_recordapi',
    list: (page, size) => `/_recordapi/articles?page=${page}&itemsPerPage=${size}`,
    item: (uid) => `/_recordapi/articles/${uid}`,
    depth: (depth, page, size) => `/_recordapi/articles-depth-${depth}?page=${page}&itemsPerPage=${size}`,
    depthFiles: ['Classes/Depth*.php'],
    projectConfig: [],
    openApi: null,
  },
  {
    key: 'extbase',
    label: 'Extbase',
    package: 'packages/bench_extbase',
    list: (page, size) => `/_extbase/articles/${page}/${size}.json`,
    item: (uid) => `/_extbase/articles/${uid}.json`,
    depth: (depth, page, size) => `/_extbase/articles-depth/${depth}/${page}/${size}.json`,
    depthFiles: ['Classes/Controller/DepthController.php', 'Classes/Domain/Model/Organization.php', 'Classes/Domain/Model/Country.php'],
    projectConfig: ['site set `typo3-api-benchmark/extbase`', 'site config `imports` of the route enhancer'],
    openApi: null,
  },
];

/** One measured setting: every URL is requested once per pass, so the first pass after a flush only sees cache misses. */
export interface Point {
  key: string;
  label: string;
  /** The use case this setting stands for. */
  useCase: string;
  urls: (c: Contender) => string[];
  /** Requests outside the measured range that warm PHP, TCA and DI caches after the flush. */
  warmup: (c: Contender) => string[];
  /** Concurrent virtual users; the runner's --vus when not set. */
  vus?: number;
  /** Records per request, for APIs with a maximum page size. */
  pageSize?: number;
}

export interface Suite {
  key: string;
  label: string;
  /** What the suite varies, as an axis title. */
  axis: string;
  question: string;
  points: Point[];
}

const range = (from: number, to: number): number[] => Array.from({ length: to - from + 1 }, (_, i) => from + i);
const ARTICLES = 10000;

// Up to 50 pages per pass; the last two pages of the data set are kept for the warm-up.
const sizePoint = (size: number, useCase: string): Point => {
  const pages = ARTICLES / size;
  return {
    key: String(size),
    label: size === 1 ? '1 record' : `${size} records`,
    useCase,
    pageSize: size,
    urls: (c) => range(1, Math.min(50, pages - 2)).map((page) => c.list(page, size)),
    warmup: (c) => range(pages - 1, pages).map((page) => c.list(page, size)),
  };
};

const depthPoint = (depth: number, label: string, useCase: string): Point => ({
  key: String(depth),
  label,
  useCase,
  urls: (c) => range(1, 50).map((page) => c.depth(depth, page, 20)),
  warmup: (c) => range(450, 454).map((page) => c.depth(depth, page, 20)),
});

const concurrencyPoint = (vus: number, useCase: string): Point => ({
  key: String(vus),
  label: vus === 1 ? '1 user' : `${vus} users`,
  useCase,
  vus,
  urls: (c) => range(1, 200).map((page) => c.list(page, 20)),
  warmup: (c) => range(480, 489).map((page) => c.list(page, 20)),
});

export const suites: Suite[] = [
  {
    key: 'base',
    label: 'Typical requests',
    axis: 'Request',
    question: 'How fast is a typical collection or item request, cold and cached?',
    points: [
      {
        key: 'list-20',
        label: 'Collection, 20 items',
        useCase: 'News list',
        urls: (c) => range(1, 200).map((page) => c.list(page, 20)),
        warmup: (c) => range(480, 489).map((page) => c.list(page, 20)),
      },
      {
        key: 'list-100',
        label: 'Collection, 100 items',
        useCase: 'Sync or export',
        urls: (c) => range(1, 80).map((page) => c.list(page, 100)),
        warmup: (c) => range(91, 95).map((page) => c.list(page, 100)),
      },
      {
        key: 'item',
        label: 'Single item',
        useCase: 'Detail page',
        urls: (c) => range(1001, 1500).map((uid) => c.item(uid)),
        warmup: (c) => range(9001, 9010).map((uid) => c.item(uid)),
      },
    ],
  },
  {
    key: 'size',
    label: 'Records per request',
    axis: 'Articles per request, each with author, tags and categories',
    question: 'What does every additional record cost?',
    points: [
      sizePoint(1, 'Detail page'),
      sizePoint(5, 'Teaser box'),
      sizePoint(20, 'News list'),
      sizePoint(50, 'Archive page'),
      sizePoint(100, 'Sync or export'),
      sizePoint(500, 'Bulk export'),
      sizePoint(1000, 'Search index feed'),
    ],
  },
  {
    key: 'depth',
    label: 'Relation depth',
    axis: 'Levels of embedded relations, 20 articles per request',
    question: 'What does every additional level of related records cost?',
    points: [
      depthPoint(0, 'Level 0', 'Title list, no relations'),
      depthPoint(1, 'Level 1', 'Byline: + author'),
      depthPoint(2, 'Level 2', 'Author card: + organization'),
      depthPoint(3, 'Level 3', 'Full graph: + country'),
    ],
  },
  {
    key: 'concurrency',
    label: 'Concurrent users',
    axis: 'Virtual users sending requests at the same time, 20 articles per request',
    question: 'How does each API hold up as traffic grows?',
    points: [
      concurrencyPoint(1, 'Editor preview'),
      concurrencyPoint(4, 'Quiet hour'),
      concurrencyPoint(8, 'Normal traffic'),
      concurrencyPoint(16, 'Newsletter sent'),
      concurrencyPoint(32, 'Campaign peak'),
    ],
  },
];
