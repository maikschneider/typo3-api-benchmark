# API benchmark, 2026-10-12 13:28 UTC

Mode **prod**, 8 concurrent virtual users, 5 repetitions. maikschneider/tca-api 2.2.0, sgalinski/sg-apicore 3.1.7, sourcebroker/t3api dev-release/5.0.0, typo3/cms-core v14.3.8.

Each pass requests every URL exactly once. **miss**: first pass after flushing all caches. **hit**: the same URLs again.
Every value is the median over the repetitions; the p50 column adds the range across repetitions.
Latency is measured by k6 at the client; queries, app time and memory are reported by the server for the same requests.

## Collection, 20 items

| API | Cache | req/s | p50 ms (range) | p95 ms | p99 ms | Queries | App ms | Memory MB | Payload KB | Errors |
|---|---|---:|---:|---:|---:|---:|---:|---:|---:|---:|
| tca_api | miss | 172 | 40.3 (36–45) | 81.9 | 107.5 | 5 | 9.4 | 14.3 | 13.4 | 0% |
| tca_api | hit | 489 | 12.8 (11–37) | 40.3 | 58.9 | 0 | 1.1 | 3.0 | 13.4 | 0% |
| t3api | miss | 62 | 117.0 (98–211) | 235.3 | 272.6 | 55 | 72.7 | 8.8 | 40.4 | 0% |
| t3api | hit | 245 | 24.9 (21–37) | 71.8 | 94.5 | 4 | 10.5 | 3.8 | 40.4 | 0% |
| sg_apicore | miss | 146 | 49.4 (47–66) | 89.2 | 137.2 | 47 | 14.3 | 14.3 | 5.9 | 0% |
| sg_apicore | hit | 430 | 15.8 (13–17) | 28.3 | 39.9 | 1 | 3.6 | 3.1 | 5.9 | 0% |
| Record API | miss | 137 | 51.7 (46–54) | 123.0 | 154.2 | 65 | 23.6 | 14.3 | 7.6 | 0% |
| Record API | hit | 564 | 12.0 (11–14) | 20.9 | 33.5 | 0 | 1.1 | 3.1 | 7.6 | 0% |
| Extbase | miss | 71 | 90.3 (57–93) | 229.4 | 316.0 | 56 | 50.4 | 4.4 | 7.6 | 0% |
| Extbase | hit | 245 | 24.7 (18–74) | 70.5 | 92.1 | 4 | 10.8 | 3.4 | 7.6 | 0% |

## Collection, 100 items

| API | Cache | req/s | p50 ms (range) | p95 ms | p99 ms | Queries | App ms | Memory MB | Payload KB | Errors |
|---|---|---:|---:|---:|---:|---:|---:|---:|---:|---:|
| tca_api | miss | 87 | 77.8 (51–120) | 157.2 | 166.3 | 5 | 17.7 | 14.3 | 65.2 | 0% |
| tca_api | hit | 207 | 35.6 (16–45) | 50.2 | 68.9 | 0 | 1.9 | 14.3 | 65.2 | 0% |
| t3api | miss | 20 | 357.7 (273–427) | 639.7 | 672.8 | 216 | 262.1 | 16.9 | 48.1 | 0% |
| t3api | hit | 322 | 22.0 (18–26) | 33.4 | 41.1 | 4 | 9.6 | 3.8 | 48.1 | 0% |
| sg_apicore | miss | 71 | 86.0 (45–107) | 186.9 | 234.1 | 128 | 26.1 | 14.3 | 29.0 | 0% |
| sg_apicore | hit | 259 | 23.7 (16–36) | 53.6 | 59.8 | 1 | 3.8 | 3.2 | 29.0 | 0% |
| Record API | miss | 43 | 176.2 (108–381) | 261.6 | 323.8 | 305 | 94.7 | 14.3 | 37.7 | 0% |
| Record API | hit | 568 | 11.3 (11–13) | 17.4 | 20.4 | 0 | 1.2 | 3.5 | 37.7 | 0% |
| Extbase | miss | 30 | 219.5 (188–306) | 431.8 | 556.6 | 217 | 151.4 | 5.6 | 37.7 | 0% |
| Extbase | hit | 265 | 25.3 (21–35) | 40.2 | 55.1 | 4 | 11.1 | 3.5 | 37.7 | 0% |

## Single item

| API | Cache | req/s | p50 ms (range) | p95 ms | p99 ms | Queries | App ms | Memory MB | Payload KB | Errors |
|---|---|---:|---:|---:|---:|---:|---:|---:|---:|---:|
| tca_api | miss | 270 | 20.2 (15–41) | 66.4 | 95.0 | 4 | 5.2 | 3.0 | 0.6 | 0% |
| tca_api | hit | 403 | 15.1 (10–25) | 46.2 | 69.2 | 0 | 1.1 | 3.0 | 0.6 | 0% |
| t3api | miss | 160 | 42.2 (28–53) | 109.8 | 175.6 | 8 | 16.8 | 4.3 | 0.4 | 0% |
| t3api | hit | 262 | 25.4 (23–39) | 66.3 | 93.0 | 4 | 11.4 | 3.7 | 0.4 | 0% |
| sg_apicore | miss | 236 | 26.0 (22–41) | 73.6 | 103.9 | 7 | 5.8 | 3.1 | 0.3 | 0% |
| sg_apicore | hit | 409 | 16.1 (13–33) | 41.8 | 61.9 | 1 | 4.0 | 3.1 | 0.3 | 0% |
| Record API | miss | 167 | 31.6 (29–37) | 117.2 | 228.4 | 7 | 9.6 | 3.1 | 0.4 | 0% |
| Record API | hit | 545 | 11.5 (10–12) | 28.7 | 53.8 | 0 | 1.0 | 3.0 | 0.4 | 0% |
| Extbase | miss | 206 | 30.3 (28–56) | 99.0 | 145.3 | 10 | 14.6 | 4.1 | 0.4 | 0% |
| Extbase | hit | 275 | 22.8 (20–36) | 65.6 | 94.5 | 5 | 9.0 | 3.5 | 0.4 | 0% |
