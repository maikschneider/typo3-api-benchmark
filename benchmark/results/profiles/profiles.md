| API | Wall ms | Queries | Peak MB | Included files | Largest unit |
|---|---:|---:|---:|---:|---|
| tca_api | 38 | 5 | 20.6 | 746 | `middleware: maikschneider/tca-api/middleware` 6 ms, 5 queries |
| t3api | 89 | 66 | 30.0 | 1246 | `middleware: sourcebroker/t3api/process-api-request` 51 ms, 62 queries |
| sg_apicore | 133 | 38 | 20.6 | 730 | `middleware: sgalinski/sg-apicore/api-request` 6 ms, 37 queries |
| nnrestapi | 72 | 69 | 20.7 | 1098 | `middleware: nnrestapi/resolver` 28 ms, 64 queries |
| Record API | 54 | 65 | 20.6 | 772 | `middleware: typo3-api-benchmark/recordapi/api` 24 ms, 65 queries |
| Extbase | 51 | 67 | 20.6 | 1145 | `typoscript: Page generation / Page generation PAGE object / 10 [EXTBASEPLUGIN]` 17 ms, 62 queries |

Findings:

- **t3api** (warning): page repeats 3 statements 60 times (5.5 ms)
- **t3api** (notice): 12 deprecation(s) triggered, 1 distinct
- **sg_apicore** (notice): page repeats 3 statements 35 times (1.2 ms)
- **nnrestapi** (warning): page repeats 3 statements 60 times (3.3 ms)
- **Record API** (warning): page repeats 3 statements 60 times (3.0 ms)
- **Extbase** (warning): page repeats 3 statements 60 times (3.2 ms)

Wall time includes the sampling overhead of the profiler; compare it between APIs, not with the load test.
