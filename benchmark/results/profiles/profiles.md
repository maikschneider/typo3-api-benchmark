| API | Wall ms | Queries | Peak MB | Included files | Largest unit |
|---|---:|---:|---:|---:|---|
| tca_api | 45 | 5 | 20.6 | 745 | `middleware: maikschneider/tca-api/middleware` 9 ms, 5 queries |
| t3api | 70 | 55 | 22.0 | 1243 | `middleware: sourcebroker/t3api/process-api-request` 27 ms, 51 queries |
| sg_apicore | 146 | 33 | 20.6 | 729 | `middleware: sgalinski/sg-apicore/api-request` 18 ms, 32 queries |
| Record API | 57 | 65 | 20.6 | 771 | `middleware: typo3-api-benchmark/recordapi/api` 25 ms, 65 queries |
| Extbase | 76 | 56 | 20.6 | 1138 | `typoscript: Page generation / Page generation PAGE object / 10 [EXTBASEPLUGIN]` 24 ms, 51 queries |

Findings:

- **t3api** (notice): page repeats 2 statements 40 times (2.7 ms)
- **t3api** (notice): 12 deprecation(s) triggered, 1 distinct
- **sg_apicore** (notice): page repeats 2 statements 23 times (5.1 ms)
- **Record API** (warning): page repeats 3 statements 60 times (4.8 ms)
- **Extbase** (notice): page repeats 2 statements 40 times (3.6 ms)

Wall time includes the sampling overhead of the profiler; compare it between APIs, not with the load test.
