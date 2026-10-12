# tca_api demo — TYPO3 Camp Berlin 2026

TYPO3 14.3 project for the live demo and benchmark of [`maikschneider/tca-api`](https://github.com/maikschneider/tca-api). It ships a small article domain (articles, authors, tags, `sys_category`), a deterministic seeder, the same article collection built with the core Record API and with Extbase for comparison, and a query counter on every response.

## Setup

```bash
ddev start
ddev composer install
ddev exec vendor/bin/typo3 setup -n --force --driver=mysqli --host=db --port=3306 \
    --dbname=db --username=db --password=db \
    --admin-username=admin --admin-user-password='Camp.Berlin.26' --admin-email=admin@example.com \
    --project-name='T3CB26 tca_api demo' --create-site='https://t3cb26.ddev.site/' --server-type=apache
ddev exec vendor/bin/typo3 extension:setup
ddev exec vendor/bin/typo3 camp:seed            # 500 articles; --articles=N for more
bin/step 0                                      # start of the live demo
```

Backend: <https://t3cb26.ddev.site/typo3/> — `admin` / `Camp.Berlin.26`

## Demo steps

`bin/step N` resets the site config and `packages/camp_demo/Configuration/TcaApi/` to the baseline, applies every step up to `N` from `demo-steps/`, and flushes the caches. Steps are cumulative, so any step can be jumped to directly.

| Step | What changes | Try |
|---|---|---|
| `0` | Baseline: no API | `/_api/articles` → 404 |
| `1` | Site set `maikschneider/tca-api` in `config/sites/main/config.yaml` | `/_api/swagger-ui` |
| `2` | `Articles.php` with three keys | `/_api/articles` |
| `3` | Filters, sorting, resources for authors, tags, categories | `/_api/articles?filters[author.name]=Grace%20Hopper`, `?filters[categories.title]=Tutorial`, `?filters[reading_time][gte]=20`, `?filters[q]=Fluid`, `?order[title]=asc&fields[]=title` |
| `4` | `embed` for author, tags, categories | Compare `X-Query-Count` at `?itemsPerPage=20` and `?itemsPerPage=100` |
| `5` | Response cache | `X-TCA-API-Cache: MISS` → `HIT` with 0 queries; edit an article in the backend → `MISS` |
| `6` | `create` / `update` for backend users | Swagger UI while logged in: POST without `title` and `reading_time: 99` → 422 |

## Measuring

Every frontend response carries:

- `X-Query-Count` — SQL statements executed after site resolution, counted by a DBAL driver middleware
- `Server-Timing: app;dur=…` — time spent from the same point, visible in the browser DevTools network tab

Send `X-Debug-Queries: 1` to write the executed SQL to `var/log/queries.log`.

Comparison endpoints, returning the same articles with embedded author, tags and categories:

| Endpoint | Built with |
|---|---|
| `/_api/articles` | tca_api (step 4) |
| `/_bench/record-api/articles` | `RecordFactory::createResolvedRecordFromDatabaseRow()` with a shared `RecordIdentityMap` |
| `/_bench/extbase/articles` | Extbase repository and domain models |

All three accept `itemsPerPage` and `page`, and include the total count query.

```bash
bin/bench                 # 10 runs each at 20, 50, 100 items
bin/bench 20 20 100 500   # 20 runs each at 20, 100, 500 items
```

`bin/bench` switches the project to step 4 first; run `bin/step N` afterwards to go back.

## Results

TYPO3 14.3.8, tca_api 2.1.0, PHP 8.4, MariaDB 10.11, DDEV on Apple Silicon, median of 20 runs:

| Strategy | Items | Queries | Median (ms) |
|---|---:|---:|---:|
| tca_api | 20 | 5 | 3.9 |
| Record API | 20 | 65 | 11.7 |
| Extbase | 20 | 51 | 13.1 |
| tca_api | 100 | 5 | 6.1 |
| Record API | 100 | 305 | 48.0 |
| Extbase | 100 | 212 | 48.4 |
| tca_api | 500 | 5 | 16.9 |
| Record API | 500 | 1505 | 230.3 |
| Extbase | 500 | 1012 | 227.8 |

The database runs next to PHP without network latency. On a remote database every additional query adds a round trip, so the gap widens.

## Known issue

`sys_category.items` (type `group`, MM with `tablenames`/`fieldname`) is resolved with one query per category instead of being preloaded — on `/_api/categories` and for every embedded category. With a three-key `Categories.php`, step 4 needs 45 queries for 20 articles instead of 5. The demo config therefore lists `items` for `show` only.
