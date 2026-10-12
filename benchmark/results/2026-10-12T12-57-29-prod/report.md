# API benchmark, 2026-10-12 13:06 UTC

Mode **prod**, 8 concurrent virtual users, 5 repetitions. maikschneider/tca-api 2.1.0, sgalinski/sg-apicore 3.1.7, sourcebroker/t3api dev-release/5.0.0, typo3/cms-core v14.3.8.

Each pass requests every URL exactly once. **miss**: first pass after flushing all caches. **hit**: the same URLs again.
Every value is the median over the repetitions; the p50 column adds the range across repetitions.
Latency is measured by k6 at the client; queries, app time and memory are reported by the server for the same requests.

## Collection, 20 items

| API | Cache | req/s | p50 ms (range) | p95 ms | p99 ms | Queries | App ms | Memory MB | Payload KB | Errors |
|---|---|---:|---:|---:|---:|---:|---:|---:|---:|---:|
| tca_api | miss | 133 | 50.6 (36–63) | 112.9 | 143.9 | 5 | 11.0 | 14.3 | 16.1 | 0% |
| tca_api | hit | 363 | 15.7 (11–57) | 47.5 | 70.2 | 0 | 1.2 | 3.0 | 16.1 | 0% |
| t3api | miss | 41 | 121.2 (88–185) | 448.2 | 574.0 | 55 | 89.3 | 8.8 | 40.4 | 0% |
| t3api | hit | 180 | 34.1 (21–35) | 93.0 | 146.1 | 4 | 13.8 | 3.8 | 40.4 | 0% |
| sg_apicore | miss | 134 | 53.5 (47–67) | 129.0 | 154.8 | 47 | 15.9 | 14.3 | 5.9 | 0% |
| sg_apicore | hit | 285 | 23.7 (15–41) | 56.5 | 120.1 | 1 | 6.1 | 3.1 | 5.9 | 0% |
| Record API | miss | 74 | 75.6 (59–134) | 309.1 | 366.9 | 65 | 38.0 | 3.4 | 7.6 | 0% |
| Record API | hit | 337 | 16.0 (14–19) | 53.4 | 83.4 | 0 | 1.9 | 3.1 | 7.6 | 0% |
| Extbase | miss | 67 | 87.3 (73–203) | 290.7 | 400.6 | 56 | 57.2 | 4.4 | 7.6 | 0% |
| Extbase | hit | 199 | 27.1 (22–52) | 100.9 | 145.7 | 4 | 10.3 | 3.4 | 7.6 | 0% |

## Collection, 100 items

| API | Cache | req/s | p50 ms (range) | p95 ms | p99 ms | Queries | App ms | Memory MB | Payload KB | Errors |
|---|---|---:|---:|---:|---:|---:|---:|---:|---:|---:|
| tca_api | miss | 95 | 71.6 (66–178) | 142.2 | 158.7 | 5 | 18.4 | 14.3 | 79.0 | 0% |
| tca_api | hit | 144 | 47.8 (13–59) | 86.5 | 98.7 | 0 | 1.4 | 3.2 | 79.0 | 0% |
| t3api | miss | 14 | 402.5 (284–791) | 1210.7 | 1485.2 | 216 | 357.8 | 16.9 | 48.1 | 0% |
| t3api | hit | 256 | 24.2 (20–97) | 58.0 | 70.7 | 4 | 10.8 | 3.8 | 48.1 | 0% |
| sg_apicore | miss | 90 | 90.0 (56–169) | 126.4 | 146.2 | 128 | 28.9 | 14.3 | 29.0 | 0% |
| sg_apicore | hit | 248 | 27.6 (21–58) | 50.8 | 68.4 | 1 | 5.2 | 3.2 | 29.0 | 0% |
| Record API | miss | 36 | 164.0 (107–252) | 383.6 | 485.2 | 305 | 120.0 | 13.8 | 37.7 | 0% |
| Record API | hit | 506 | 12.3 (12–46) | 22.7 | 28.6 | 0 | 1.8 | 3.5 | 37.7 | 0% |
| Extbase | miss | 35 | 198.2 (183–295) | 363.6 | 422.8 | 217 | 157.9 | 5.6 | 37.7 | 0% |
| Extbase | hit | 256 | 26.2 (21–70) | 56.9 | 67.2 | 4 | 12.0 | 3.5 | 37.7 | 0% |

## Single item

| API | Cache | req/s | p50 ms (range) | p95 ms | p99 ms | Queries | App ms | Memory MB | Payload KB | Errors |
|---|---|---:|---:|---:|---:|---:|---:|---:|---:|---:|
| tca_api | miss | 154 | 40.9 (21–54) | 140.9 | 186.1 | 4 | 7.3 | 3.1 | 0.8 | 0% |
| tca_api | hit | 395 | 13.8 (12–29) | 53.7 | 81.1 | 0 | 1.1 | 3.0 | 0.8 | 0% |
| t3api | miss | 138 | 44.0 (24–51) | 137.8 | 184.5 | 8 | 18.9 | 4.3 | 0.4 | 0% |
| t3api | hit | 226 | 24.5 (19–38) | 86.6 | 118.6 | 4 | 10.5 | 3.7 | 0.4 | 0% |
| sg_apicore | miss | 174 | 37.1 (25–40) | 110.8 | 185.6 | 7 | 7.3 | 3.1 | 0.3 | 0% |
| sg_apicore | hit | 204 | 24.5 (14–28) | 110.8 | 180.1 | 1 | 6.7 | 3.1 | 0.3 | 0% |
| Record API | miss | 203 | 29.9 (20–41) | 98.8 | 150.4 | 7 | 7.8 | 3.1 | 0.4 | 0% |
| Record API | hit | 477 | 12.6 (12–21) | 36.7 | 67.9 | 0 | 1.0 | 3.0 | 0.4 | 0% |
| Extbase | miss | 177 | 34.5 (29–61) | 104.2 | 166.4 | 10 | 16.1 | 4.1 | 0.4 | 0% |
| Extbase | hit | 273 | 21.7 (19–31) | 73.3 | 100.5 | 5 | 9.6 | 3.5 | 0.4 | 0% |
