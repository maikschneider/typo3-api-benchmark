# TYPO3 API Benchmark

Five ways to serve the same TYPO3 records as JSON, side by side: three API extensions and two hand-written endpoints on TYPO3 core, each in its own package, load-tested cold and cached, probed for correct and fresh data, and counted in lines of configuration.

**Results: [`docs/index.html`](docs/index.html)**, a self-contained page with charts, the feature matrix and every number in table form.

| API | Package | Version |
|---|---|---|
| [tca_api](https://github.com/maikschneider/tca-api) | [`packages/bench_tcaapi`](packages/bench_tcaapi) | see `composer.lock` |
| [t3api](https://github.com/sourcebroker/t3api) | [`packages/bench_t3api`](packages/bench_t3api) | `release/5.0.0` branch (TYPO3 v14 support is not released yet) |
| [sg_apicore](https://extensions.typo3.org/extension/sg_apicore) | [`packages/bench_sgapicore`](packages/bench_sgapicore) | see `composer.lock` |
| Record API, hand-written | [`packages/bench_recordapi`](packages/bench_recordapi) | TYPO3 core |
| Extbase, hand-written | [`packages/bench_extbase`](packages/bench_extbase) | TYPO3 core |

[`packages/bench`](packages/bench) holds what all of them share: the TCA of the article domain, the seeder, a command for DataHandler edits, and the middleware that counts queries, time and memory for every request.

## The task

Every package implements the same thing, in the way its API documents it:

- a paginated collection of articles with total count, ordered by uid
- every article with `title`, `teaser`, `published`, `reading_time` and its embedded author, tags and categories
- a single article
- the API's own response cache switched on and invalidated when an editor changes a record, plus every query optimization the API offers

Nothing is hand-rolled around an extension. Where an API cannot do part of the task, the report shows the gap instead of a workaround.

## Disclosure

This benchmark is maintained by the author of tca_api. To keep it fair, every claim in the report is measured or probed by code in this repository, the packages for the other APIs follow their own documentation, and the raw results are committed. **Maintainers and users of the other APIs are invited to improve their package**: a pull request that makes an API faster or more idiomatic is welcome, as is one that points out where the setup favours one API.

## Run it

Requirements: [DDEV](https://ddev.com), [k6](https://k6.io), [Bun](https://bun.sh).

```bash
ddev start
ddev composer install
ddev exec vendor/bin/typo3 setup -n --force --driver=mysqli --host=db --port=3306 \
    --dbname=db --username=db --password=db \
    --admin-username=admin --admin-user-password='Bench.Mark.2026' --admin-email=admin@example.com \
    --project-name='TYPO3 API benchmark' --create-site='https://typo3-api-benchmark.ddev.site/' --server-type=apache
git checkout -- config/sites/main/config.yaml    # setup overwrites the site config
ddev exec vendor/bin/typo3 extension:setup
ddev exec vendor/bin/typo3 bench:seed            # 10,000 articles

bin/env prod                                     # production-like setup for the load test
bin/bench load                                   # all suites, 3 repetitions, ~1 hour
bin/bench features
bin/bench footprint
bin/env dev
bin/bench profile                                # optional, needs T3Flare
bin/bench site                                   # docs/index.html
```

How each measurement works, the production setup and the limits of a laptop benchmark: [`benchmark/README.md`](benchmark/README.md).

The endpoints, for trying them by hand:

| API | Collection | Item |
|---|---|---|
| tca_api | `/_api/articles?page=1&itemsPerPage=20` | `/_api/articles/1` |
| t3api | `/_t3api/articles?order[uid]=asc&page=1&itemsPerPage=20` | `/_t3api/articles/1` |
| sg_apicore | `/_sgapi/bench/v1/articles?sort=uid&page=1&perPage=20` | `/_sgapi/bench/v1/articles/1` |
| Record API | `/_recordapi/articles?page=1&itemsPerPage=20` | `/_recordapi/articles/1` |
| Extbase | `/_extbase/articles/1/20.json` | `/_extbase/articles/1.json` |

Every response carries `X-Query-Count`, `Server-Timing` and `X-Memory-Peak`. Send `X-Debug-Queries: 1` to write the executed SQL to `var/log/queries.log`.

## Profiles

`bin/bench profile` uses [T3Flare](https://github.com/maikschneider/t3flare), a TYPO3 profiler that is not public yet; it is installed as a dev dependency from its repository. Without access, remove it from `require-dev` and `repositories` in `composer.json`; everything except the profile section of the report works without it.
