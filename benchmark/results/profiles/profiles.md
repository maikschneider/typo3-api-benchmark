| API | Cache | Wall ms | Queries | Peak MB | Included files | Largest unit | Profile |
|---|---|---:|---:|---:|---:|---|---|
| tca_api | miss | 143 | 5 | 20.6 | 830 | `maikschneider/tca-api/middleware` 25 ms, 5 queries | `20261012-124151-c4a8c5` |
| tca_api | hit | 45 | 0 | 18.6 | 633 | `typo3/cms-frontend/csp-report` 3 ms, 0 queries | `20261012-124152-a9d81e` |
| t3api | miss | 88 | 65 | 22.0 | 1445 | `sourcebroker/t3api/process-api-request` 39 ms, 51 queries | `20261012-124154-8a7b81` |
| t3api | hit | 35 | 4 | 20.6 | 990 | `typo3/cms-frontend/page-resolver` 3 ms, 2 queries | `20261012-124155-ee4071` |
| sg_apicore | miss | 49 | 41 | 20.6 | 832 | `sgalinski/sg-apicore/api-typoscript` 12 ms, 9 queries | `20261012-124157-5e3138` |
| sg_apicore | hit | 38 | 1 | 22.6 | 722 | `sgalinski/sg-apicore/api-typoscript` 3 ms, 1 queries | `20261012-124158-bf0276` |
| Record API (hand-written) | miss | 36 | 65 | 20.6 | 800 | `maikschneider/camp-recordapi/api` 15 ms, 65 queries | `20261012-124159-129303` |
| Record API (hand-written) | hit | 25 | 0 | 18.6 | 583 | `typo3/cms-core/request-token-middleware` 4 ms, 0 queries | `20261012-124200-69e40d` |
| Extbase (hand-written) | miss | 102 | 66 | 20.6 | 1344 | `typoscript: Page generation / Page generation PAGE object / 10 [EXTBASEPLUGIN]` 36 ms, 51 queries | `20261012-124203-a77c99` |
| Extbase (hand-written) | hit | 32 | 4 | 20.6 | 957 | `typoscript: Create PageInformation` 3 ms, 2 queries | `20261012-124204-92f97e` |

Findings on the cold request:

- **t3api** (notice): page repeats 2 statements 40 times (2.5 ms)
- **t3api** (notice): 12 deprecation(s) triggered, 1 distinct
- **sg_apicore** (notice): page repeats 2 statements 23 times (0.6 ms)
- **Record API (hand-written)** (warning): page repeats 3 statements 60 times (2.2 ms)
- **Extbase (hand-written)** (notice): page repeats 2 statements 40 times (5.3 ms)

Wall time includes the sampling overhead of the profiler; compare it between APIs, not with the load test.
Flame graphs: `var/t3flare/<profile>.collapsed.txt` opens in https://www.speedscope.app.
