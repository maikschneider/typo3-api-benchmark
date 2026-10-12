| API | Wall ms | Queries | Peak MB | Included files | Largest unit |
|---|---:|---:|---:|---:|---|
| tca_api | 49 | 5 | 20.6 | 746 | `middleware: maikschneider/tca-api/middleware` 9 ms, 5 queries |
| t3api | 208 | 55 | 20.6 | 1243 | `middleware: sourcebroker/t3api/process-api-request` 102 ms, 51 queries |
| sg_apicore | 55 | 33 | 20.6 | 729 | `middleware: sgalinski/sg-apicore/api-request` 9 ms, 32 queries |
| Record API (hand-written) | 59 | 65 | 20.6 | 771 | `middleware: typo3-api-benchmark/recordapi/api` 24 ms, 65 queries |
| Extbase (hand-written) | 61 | 56 | 20.6 | 1138 | `typoscript: Page generation / Page generation PAGE object / 10 [EXTBASEPLUGIN]` 20 ms, 51 queries |

Findings:

- **t3api** (notice): page repeats 2 statements 40 times (14.9 ms)
- **t3api** (notice): 12 deprecation(s) triggered, 1 distinct
- **sg_apicore** (notice): page repeats 2 statements 23 times (1.7 ms)
- **Record API (hand-written)** (warning): page repeats 3 statements 60 times (4.9 ms)
- **Extbase (hand-written)** (notice): page repeats 2 statements 40 times (4.1 ms)

Wall time includes the sampling overhead of the profiler; compare it between APIs, not with the load test.
