# API benchmark, 2026-10-12 10:40 UTC

Mode **prod**, 8 concurrent virtual users, 5 repetitions. maikschneider/tca-api 2.1.0, sgalinski/sg-apicore 3.1.7, sourcebroker/t3api dev-release/5.0.0, typo3/cms-core v14.3.8.

Each pass requests every URL exactly once. **miss**: first pass after flushing all caches. **hit**: the same URLs again.
Every value is the median over the repetitions; the p50 column adds the range across repetitions.
Latency is measured by k6 at the client; queries, app time and memory are reported by the server for the same requests.

## Collection, 20 items

| API | Cache | req/s | p50 ms (range) | p95 ms | p99 ms | Queries | App ms | Memory MB | Payload KB | Errors |
|---|---|---:|---:|---:|---:|---:|---:|---:|---:|---:|
| tca_api | miss | 154 | 45.9 (36–63) | 85.8 | 106.1 | 5 | 10.6 | 14.3 | 16.7 | 0% |
| tca_api | hit | 469 | 13.8 (9–21) | 27.7 | 43.7 | 0 | 1.1 | 3.1 | 16.7 | 0% |
| t3api | miss | 81 | 98.0 (77–106) | 174.2 | 212.3 | 55 | 61.7 | 8.8 | 40.4 | 0% |
| t3api | hit | 387 | 17.9 (17–48) | 31.5 | 41.0 | 4 | 8.3 | 3.8 | 40.4 | 0% |
| sg_apicore | miss | 141 | 49.9 (39–71) | 94.6 | 124.5 | 47 | 13.5 | 14.3 | 5.9 | 0% |
| sg_apicore | hit | 454 | 14.9 (11–20) | 31.3 | 40.2 | 1 | 3.8 | 3.1 | 5.9 | 0% |
| Record API (hand-written) | miss | 123 | 58.4 (44–76) | 116.1 | 130.4 | 65 | 21.4 | 14.3 | 7.6 | 0% |
| Record API (hand-written) | hit | 444 | 15.6 (10–29) | 33.0 | 40.9 | 0 | 1.0 | 3.1 | 7.6 | 0% |
| Extbase (hand-written) | miss | 80 | 82.4 (59–98) | 169.6 | 193.2 | 56 | 42.3 | 4.4 | 7.6 | 0% |
| Extbase (hand-written) | hit | 358 | 18.9 (17–20) | 34.4 | 49.1 | 4 | 8.3 | 3.4 | 7.6 | 0% |

## Collection, 100 items

| API | Cache | req/s | p50 ms (range) | p95 ms | p99 ms | Queries | App ms | Memory MB | Payload KB | Errors |
|---|---|---:|---:|---:|---:|---:|---:|---:|---:|---:|
| tca_api | miss | 110 | 67.7 (44–99) | 84.0 | 89.7 | 5 | 13.0 | 14.3 | 81.7 | 0% |
| tca_api | hit | 190 | 37.6 (23–40) | 60.7 | 66.7 | 0 | 1.4 | 14.3 | 81.7 | 0% |
| t3api | miss | 27 | 278.6 (238–391) | 404.2 | 523.3 | 216 | 222.9 | 16.9 | 48.3 | 0% |
| t3api | hit | 225 | 28.4 (19–39) | 59.5 | 83.2 | 4 | 11.4 | 3.8 | 48.3 | 0% |
| sg_apicore | miss | 113 | 65.7 (50–69) | 90.7 | 94.8 | 128 | 19.0 | 14.3 | 29.0 | 0% |
| sg_apicore | hit | 204 | 34.3 (17–38) | 50.3 | 55.0 | 1 | 3.5 | 14.3 | 29.0 | 0% |
| Record API (hand-written) | miss | 60 | 126.7 (110–239) | 182.2 | 204.9 | 305 | 70.3 | 14.3 | 37.9 | 0% |
| Record API (hand-written) | hit | 494 | 12.6 (11–19) | 32.7 | 37.3 | 0 | 1.2 | 3.5 | 37.9 | 0% |
| Extbase (hand-written) | miss | 38 | 185.2 (152–203) | 324.5 | 366.4 | 217 | 111.0 | 14.3 | 37.9 | 0% |
| Extbase (hand-written) | hit | 367 | 19.6 (17–26) | 27.5 | 32.8 | 4 | 8.0 | 3.5 | 37.9 | 0% |

## Single item

| API | Cache | req/s | p50 ms (range) | p95 ms | p99 ms | Queries | App ms | Memory MB | Payload KB | Errors |
|---|---|---:|---:|---:|---:|---:|---:|---:|---:|---:|
| tca_api | miss | 253 | 29.0 (27–32) | 54.2 | 67.2 | 4 | 5.5 | 3.1 | 0.8 | 0% |
| tca_api | hit | 558 | 12.3 (9–14) | 25.3 | 61.4 | 0 | 1.1 | 3.0 | 0.8 | 0% |
| t3api | miss | 204 | 34.0 (28–49) | 74.8 | 134.3 | 8 | 15.4 | 4.3 | 0.4 | 0% |
| t3api | hit | 375 | 18.3 (16–59) | 36.6 | 57.3 | 4 | 8.4 | 3.7 | 0.4 | 0% |
| sg_apicore | miss | 290 | 22.0 (15–43) | 49.9 | 68.2 | 7 | 5.5 | 3.1 | 0.3 | 0% |
| sg_apicore | hit | 509 | 14.4 (11–18) | 25.4 | 36.1 | 1 | 3.6 | 3.1 | 0.3 | 0% |
| Record API (hand-written) | miss | 251 | 32.5 (19–35) | 60.0 | 103.5 | 7 | 5.9 | 3.1 | 0.4 | 0% |
| Record API (hand-written) | hit | 707 | 9.9 (9–24) | 17.3 | 25.5 | 0 | 0.8 | 3.0 | 0.4 | 0% |
| Extbase (hand-written) | miss | 227 | 30.1 (24–41) | 59.1 | 83.6 | 10 | 11.4 | 4.1 | 0.4 | 0% |
| Extbase (hand-written) | hit | 371 | 19.1 (16–25) | 35.7 | 51.0 | 5 | 8.2 | 3.5 | 0.4 | 0% |
