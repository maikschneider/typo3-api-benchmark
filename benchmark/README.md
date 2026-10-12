# API benchmark

Five ways to serve the same TYPO3 data as JSON, each in its own package, measured four ways.

| API | Package | Version |
|---|---|---|
| [tca_api](https://github.com/maikschneider/tca-api) | [`camp_tcaapi`](../packages/camp_tcaapi) | 2.1.0 |
| [t3api](https://github.com/sourcebroker/t3api) | [`camp_t3api`](../packages/camp_t3api) | `release/5.0.0` @ `4b96399` (v14 support is unreleased) |
| [sg_apicore](https://extensions.typo3.org/extension/sg_apicore) | [`camp_sgapicore`](../packages/camp_sgapicore) | 3.1.7 |
| Core Record API, hand-written | [`camp_recordapi`](../packages/camp_recordapi) | TYPO3 14.3.8 |
| Extbase plugin, hand-written | [`camp_extbase`](../packages/camp_extbase) | TYPO3 14.3.8 |

[`camp_demo`](../packages/camp_demo) holds what they share: the TCA of the article domain, the seeder (`camp:seed`, 10,000 articles), `camp:edit` for DataHandler edits, and the request metrics middleware.

## The task

Every package implements the same spec, as idiomatically as its API allows:

- `GET` a collection of articles, paginated, with total count, ordered by uid
- every article with `uid`, `title`, `teaser`, `published`, `reading_time` and embedded `author {uid, name}`, `tags [{uid, title}]`, `categories [{uid, title}]`
- `GET` a single article
- production setup: the API's own response cache switched on, invalidated when an editor changes a record, plus every query optimization the API offers

Nothing was hand-rolled around an extension. Where an extension cannot do part of the spec, the gap is documented below instead of patched.

## Measurements

| Command | Measures | Mode |
|---|---|---|
| `bin/bench load` | k6 load test: throughput, latency percentiles, queries, app time, memory and payload per request | `bin/env prod` |
| `bin/bench invalidation` | Whether item and list are fresh after a backend edit of the article and of its author | either |
| `bin/bench profile` | One [T3Flare](https://github.com/maikschneider/t3flare) profile per API: TYPO3 units, repeated queries, loaded files, findings | `bin/env dev` |
| `bin/bench footprint` | Files and lines each package needs, plus configuration outside the package | either |

Results are written to `benchmark/results/`.

### Load test method

For every API and scenario (`list-20`, `list-100`, `item`):

1. Flush all caches, then send warm-up requests to URLs **outside** the measured range, so PHP, DI and TCA caches are warm but no measured response is cached.
2. **miss** pass: k6 requests every URL of the scenario exactly once (200 pages of 20, 80 pages of 100, or 500 articles), spread over 8 virtual users. Each request is the first for its URL, so it is a cache miss.
3. **hit** pass: the same URLs again, so every request can be a cache hit.

The whole matrix runs `--repeat` times, round-robin over the APIs, so noise from the host spreads evenly. The report shows the median over the repetitions and the p50 range across them.

Every response carries `X-Query-Count`, `Server-Timing: app;dur=…` and `X-Memory-Peak` from a middleware that runs first in the frontend stack, so all five APIs are measured from the same point and include whatever their middlewares, routing and bootstrap cost.

### Production mode

`bin/env prod` installs without dev dependencies (no T3Flare), with an authoritative classmap, and turns off OPcache timestamp validation. TYPO3 runs in `Production` context in both modes. All API response caches, and the page cache Extbase relies on, use the same `FileBackend` (`config/system/additional.php`), so a cache hit compares the APIs and not the storage.

`bin/env dev` switches back; the live demo (`bin/step`) and T3Flare need it.

### Limits

- DDEV on a laptop: PHP, MariaDB and k6 share one machine, the database has no network latency, PHP-FPM has 8 workers. Absolute numbers will differ on a server; the differences between the APIs are what this measures. On a remote database every additional query adds a round trip, so the gap between batched and per-row loading grows.
- Payloads differ: tca_api and t3api return Hydra JSON-LD with `@id`/`@type` and pagination links, sg_apicore leaves out `categories`.
- One dataset shape: 10,000 articles, 10 authors, 15 tags, 8 categories, 2–4 tags and 1–3 categories per article.

## What each API needs and does

| | tca_api | t3api | sg_apicore | Record API | Extbase |
|---|---|---|---|---|---|
| Files / lines in the package¹ | 2 / 30 | 6 / 180 | 1 / 26 | 6 / 192 | 12 / 248 |
| PHP classes | 0 | 4 models | 0 | 3 | 6 |
| Configuration outside the package | site set | site config `imports` | `config/system/additional.php`² | – | site set, site config `imports` |
| Embedded author and tags | ✅ | ✅ | ✅ | ✅ | ✅ |
| Embedded categories (`type=category`) | ✅³ | ✅ | ❌ not supported by its mapper | ✅ | ✅ |
| Ordered by uid without a client parameter | ✅ | ❌ needs `order[uid]=asc` | ❌ needs `sort=uid` | ✅ | ✅ |
| Relations loaded | batched, 1 query per relation | per row (Extbase) | per row, memoized | per row | per row (Extbase) |
| Response cache | native, opt-in | native, opt-in | native, on by default | hand-written | TYPO3 page cache |
| Fresh after a backend edit of the article / of its author | ✅ / ✅ | ✅ / ✅ | ❌ / ❌ stale until TTL or flush | ✅ / ✅ | ✅ / ✅ |
| Output | Hydra JSON-LD | Hydra JSON-LD | JSON with `data`/`meta` | plain JSON | plain JSON |
| Released for TYPO3 v14 | ✅ | ❌ branch only | ✅ | – | – |

¹ Without `composer.json`, non-empty lines (`bin/bench footprint`). ² sg_apicore reads its extension configuration while its own `ext_localconf.php` loads, so the path prefix cannot be set from the package. ³ Needs `Categories.php` to keep `sys_category.items` out of the response ([#204](https://github.com/maikschneider/tca-api/issues/204)); the other related tables need no configuration.

## Results

Full tables: [`results/2026-10-12T10-33-32-prod/report.md`](results/2026-10-12T10-33-32-prod/report.md) (load), [`results/invalidation.md`](results/invalidation.md), [`results/profiles/profiles.md`](results/profiles/profiles.md), [`results/footprint.md`](results/footprint.md).

Collection of 100 articles, median of 5 repetitions, 8 concurrent users:

| API | Queries (miss) | App ms (miss) | req/s (miss) | p95 ms (miss) | req/s (hit) | p50 ms (hit) |
|---|---:|---:|---:|---:|---:|---:|
| tca_api | **5** | **13.0** | 110 | **84** | 190 | 37.6 |
| t3api | 216 | 222.9 | 27 | 404 | 225 | 28.4 |
| sg_apicore | 128 | 19.0 | **113** | 91 | 204 | 34.3 |
| Record API | 305 | 70.3 | 60 | 182 | **494** | **12.6** |
| Extbase | 217 | 111.0 | 38 | 325 | 367 | 19.6 |

What the numbers say:

- **Cold requests scale with the query count.** tca_api stays at 5 queries for 20 or 100 articles; every other API grows with the page size. t3api needs 17× the app time of tca_api at 100 articles, Extbase 8.5×, the Record API 5.4×.
- **sg_apicore is close to tca_api on cold requests** despite 128 queries, because it memoizes related rows and runs few, cheap statements. It also does less work: no categories.
- **Warm requests are cheap everywhere.** Once cached, the differences come from what still runs: t3api and Extbase bootstrap the TypoScript frontend (4 queries, ~8 ms app time), the others answer from a middleware. tca_api's hit at 100 articles is held back by its 82 KB payload (JSON-LD plus every author column in default mode); its app time on a hit is 1.4 ms.
- **Code loaded per cold request** (T3Flare, included files): Record API 800, tca_api 830, sg_apicore 832, Extbase 1344, t3api 1445.
- **A fast cache is only half of production-ready.** sg_apicore serves stale data after every backend edit until its TTL expires or someone flushes all caches.

## Bugs found while building this

| Extension | Bug |
|---|---|
| tca_api | [#204](https://github.com/maikschneider/tca-api/issues/204): reverse MM columns like `sys_category.items` are resolved per row |
| tca_api | `CacheInvalidationHook::clearCachePostProc()` passes integer tags to `flushByTags()` when DataHandler clears caches via `TCEMAIN.clearCacheCmd` (`tags` is then a list, not a map): every such save throws a `TypeError` |
| tca_api | Embedded records of tables without their own resource get an `@id` like `/_api/tx_campdemo_author/4` that no endpoint serves |
| tca_api | `MaikSchneider\TcaApi\Testing\EventCollector` is registered as an event listener in production |
| t3api | The same `TypeError` in `ResponseCacheService::clearCachePostProc()` (`array_keys($params['tags'])`) |
| t3api | 12 deprecations per request on TYPO3 v14; v14 support is unreleased |
| sg_apicore | No invalidation on DataHandler edits; `type=category` and `type=datetime` (emitted as integer) not handled by its mapper |
