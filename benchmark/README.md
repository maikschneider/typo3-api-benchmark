# Benchmark tooling

| Command | What it does | Writes | Mode |
|---|---|---|---|
| `bin/bench load [--suites base,size,depth,concurrency] [--repeat 3] [--vus 8] [--contenders a,b]` | k6 load test, cold and cached, per suite | `results/<date>-<mode>/` | `bin/env prod` |
| `bin/bench features` | Probes every feature in the report against the running APIs and the database | `results/features.json` | either |
| `bin/bench footprint` | Counts files and lines per package | `results/footprint.json` | either |
| `bin/bench profile` | One [T3Flare](https://github.com/maikschneider/t3flare) profile per API for a cold collection request | `results/profiles/` | `bin/env dev` |
| `bin/bench site` | Builds `docs/index.html` from the newest prod run and the other results | `docs/index.html` | either |

`bun benchmark/report.ts [results/<run>]` prints the load results of a run as Markdown tables.

The APIs, their URLs and the scenarios are defined in [`contenders.ts`](contenders.ts).

## Load test

The suites are defined in [`contenders.ts`](contenders.ts). Each one is a series of steps, and every step stands for a common use case:

| Suite | Steps | Fixed |
|---|---|---|
| `base` | collection of 20 (news list), collection of 100 (sync or export), single article (detail page) | 8 users |
| `size` | 1, 5, 20, 50, 100 articles per request (detail page, teaser box, news list, archive, sync) | author, tags and categories embedded, 8 users |
| `depth` | relation levels 0–3: title list, + author, + organization, + country | 20 articles, 8 users |
| `concurrency` | 1, 4, 8, 16, 32 concurrent users (editor preview to campaign peak) | 20 articles with author, tags and categories |

For every step and API:

1. Flush all caches, then request URLs **outside** the measured range, so PHP, DI and TCA caches are warm but no measured response is cached.
2. **Cold** pass: k6 requests every URL of the step exactly once (200 pages, 80 pages or 500 articles in `base`, 50 pages per step in `size` and `depth`, 200 pages in `concurrency`). Every request is the first for its URL, so the API builds it from the database.
3. **Cached** pass: the same URLs again.

The matrix runs `--repeat` times; within each step the APIs run one after another, so noise from the host spreads evenly. Reports show the median over the repetitions, and for the response time also the range across them.

Queries, server time and memory come from [`QueryCountMiddleware`](../packages/bench/Classes/Middleware/QueryCountMiddleware.php), the first middleware of the frontend stack, and a DBAL driver middleware that counts every executed statement. All five APIs are measured from the same point, including whatever their own middlewares, routing and bootstrap cost.

## Feature probes

[`features.ts`](features.ts) picks an article with four tags and three categories and checks, per API:

- collection with total count, single item
- author, tag and category titles in the response, compared with the database
- query count for 20 and for 100 articles (cold)
- fewer queries on the second request (response cache)
- item and list fresh after a DataHandler edit of the article, and after an edit of its author, while the cache is warm (`bench:edit` makes the same DataHandler call as a backend save)
- a public OpenAPI document

## Production setup

`bin/env prod` installs without dev dependencies, with an authoritative classmap, and turns off OPcache timestamp validation ([`php/zz-bench-prod.ini`](php/zz-bench-prod.ini)). TYPO3 runs in `Production` context in both modes. Every API response cache and the page cache, which the Extbase variant relies on, use the same `FileBackend` ([`config/system/additional.php`](../config/system/additional.php)), so a cached request compares the APIs, not the cache storage.

## Limits

- PHP, the database and k6 share one machine, the database has no network latency, PHP-FPM has 8 workers. Absolute numbers will differ on a server; compare the APIs, not the values. On a remote database every additional query costs a round trip, so per-row loading falls further behind.
- Payloads differ: tca_api and t3api return Hydra JSON-LD with `@id`, `@type` and pagination links; sg_apicore cannot embed categories.
- One dataset: 10,000 articles by 2,000 authors in 300 organizations in 25 countries, 15 tags and 8 categories; 2–4 tags and 1–3 categories per article. Related records rarely repeat within a page, so identity maps cannot hide per-row loading.
- The depth variants are extra configuration in every package; the footprint counts them separately. Two API-specific effects show up in the depth sweep: the core Record API loads a whole table of to-one relations at once (`GreedyDatabaseBackend`), and an Extbase lazy proxy loads the next level eagerly (`LazyLoadingProxy::_loadRealInstance()`).
- Profiles include the profiler's sampling overhead; compare them between APIs, not with the load test. Cached requests are not profiled, because the profiler's request does not share the cache key of a normal request.
