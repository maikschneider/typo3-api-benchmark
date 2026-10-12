# tca_api demo — TYPO3 Camp Berlin 2026

TYPO3 14.3 project for the live demo of [`maikschneider/tca-api`](https://github.com/maikschneider/tca-api) and for a benchmark against t3api, sg_apicore, the core Record API and Extbase. One shared article domain, one package per API, so the configuration each API needs is visible at a glance.

```
packages/
  bench/        shared: TCA, seeder, DataHandler edit command, request metrics middleware
  bench_tcaapi/      tca_api        /_api/articles
  bench_t3api/       t3api          /_t3api/articles
  bench_sgapicore/   sg_apicore     /_sgapi/bench/v1/articles
  bench_recordapi/   Record API     /_recordapi/articles
  bench_extbase/     Extbase        /_extbase/articles/{page}/{size}.json
benchmark/          k6 load test, cache correctness, T3Flare profiles, footprint — see benchmark/README.md
demo-steps/         the tca_api configuration for each step of the live demo
```

## Setup

```bash
ddev start
ddev composer install          # T3Flare comes from a private repository, see below
ddev exec vendor/bin/typo3 setup -n --force --driver=mysqli --host=db --port=3306 \
    --dbname=db --username=db --password=db \
    --admin-username=admin --admin-user-password='Camp.Berlin.26' --admin-email=admin@example.com \
    --project-name='T3CB26 tca_api demo' --create-site='https://typo3-api-benchmark.ddev.site/' --server-type=apache
git checkout -- config/sites/main/config.yaml    # setup overwrites the site config
ddev exec vendor/bin/typo3 extension:setup
ddev exec vendor/bin/typo3 bench:seed             # 10,000 articles; --articles=N for another size
```

Backend: <https://typo3-api-benchmark.ddev.site/typo3/> — `admin` / `Camp.Berlin.26`

T3Flare (`maikschneider/t3flare`, dev dependency) is installed from a private GitHub repository over SSH. Without access, remove it from `require-dev` and the `repositories` entry; only `bin/bench profile` needs it.

## Live demo

`bin/step N` puts `packages/bench_tcaapi/Configuration/TcaApi/` and the site config into the state of step `N` (cumulative) and flushes the caches. `bin/step bench` goes back to the committed benchmark configuration. Needs `bin/env dev`.

| Step | What changes | Try |
|---|---|---|
| `0` | No tca_api site set | `/_api/articles` → 404 |
| `1` | Site set `maikschneider/tca-api` | `/_api/swagger-ui` |
| `2` | `Articles.php` with three keys | `/_api/articles` |
| `3` | Filters, sorting, resources for authors, tags, categories | `/_api/articles?filters[author.name]=Grace%20Hopper`, `?filters[categories.title]=Tutorial`, `?filters[reading_time][gte]=20`, `?filters[q]=Fluid`, `?order[title]=asc&fields[]=title` |
| `4` | `embed` for author, tags, categories | `X-Query-Count` stays at 5 for `?itemsPerPage=20` and `?itemsPerPage=100` |
| `5` | Response cache | `X-TCA-API-Cache: MISS` → `HIT` with 0 queries; edit an article in the backend → `MISS` |
| `6` | `create` / `update` for backend users | Swagger UI while logged in: POST without `title` and `reading_time: 99` → 422 |

## Request metrics

Every frontend response carries `X-Query-Count`, `Server-Timing: app;dur=…` (visible in the DevTools network tab) and `X-Memory-Peak`, measured from the first middleware of the frontend stack. Send `X-Debug-Queries: 1` to write the executed SQL to `var/log/queries.log`.

## Known issues found while building this

- tca_api [#204](https://github.com/maikschneider/tca-api/issues/204): `sys_category.items` is resolved per row. `bench_tcaapi` therefore exposes only `title` for categories.
- tca_api [#206](https://github.com/maikschneider/tca-api/issues/206) and t3api: their `clearCachePostProc` hooks pass integer tags to `flushByTags()` when DataHandler clears caches through `TCEMAIN.clearCacheCmd`, which turns every save into a TypeError. `bench_extbase` uses cache tags instead of `clearCacheCmd` for that reason.
