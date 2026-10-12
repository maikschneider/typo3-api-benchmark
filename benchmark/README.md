# Benchmark tooling

| Command | What it does | Writes | Mode |
|---|---|---|---|
| `bin/bench load [--repeat 5] [--vus 8] [--contenders a,b] [--scenarios list-20,list-100,item]` | k6 load test, cold and cached | `results/<date>-<mode>/` | `bin/env prod` |
| `bin/bench features` | Probes every feature in the report against the running APIs and the database | `results/features.json` | either |
| `bin/bench footprint` | Counts files and lines per package | `results/footprint.json` | either |
| `bin/bench profile` | One [T3Flare](https://github.com/maikschneider/t3flare) profile per API for a cold collection request | `results/profiles/` | `bin/env dev` |
| `bin/bench site` | Builds `docs/index.html` from the newest prod run and the other results | `docs/index.html` | either |

`bun benchmark/report.ts [results/<run>]` prints the load results of a run as Markdown tables.

The APIs, their URLs and the scenarios are defined in [`contenders.ts`](contenders.ts).

## Load test

For every API and scenario (`list-20`, `list-100`, `item`):

1. Flush all caches, then request URLs **outside** the measured range, so PHP, DI and TCA caches are warm but no measured response is cached.
2. **Cold** pass: k6 requests every URL of the scenario exactly once (200 pages of 20 articles, 80 pages of 100, or 500 single articles) with 8 virtual users. Every request is the first for its URL, so the API builds it from the database.
3. **Cached** pass: the same URLs again.

The matrix runs `--repeat` times, rotating over the APIs, so noise from the host spreads evenly. Reports show the median over the repetitions, and for the response time also the range across them.

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
- One dataset: 10,000 articles with 10 authors, 15 tags and 8 categories, 2–4 tags and 1–3 categories per article.
- Profiles include the profiler's sampling overhead; compare them between APIs, not with the load test. Cached requests are not profiled, because the profiler's request does not share the cache key of a normal request.
