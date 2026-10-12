import exec from 'k6/execution';
import http from 'k6/http';
import { check } from 'k6';
import { Trend } from 'k6/metrics';

// One pass over a list of URLs: every URL is requested exactly once, spread over the VUs.

const urls: string[] = JSON.parse(open(__ENV.URLS_FILE));

const queries = new Trend('api_queries');
const appTime = new Trend('api_app_time', true);
const memory = new Trend('api_memory_mb');
const payload = new Trend('api_payload_kb');

export const options = {
  insecureSkipTLSVerify: true,
  discardResponseBodies: false,
  scenarios: {
    pass: {
      executor: 'shared-iterations',
      vus: Number(__ENV.VUS ?? 8),
      iterations: urls.length,
      maxDuration: '15m',
    },
  },
  summaryTrendStats: ['avg', 'min', 'med', 'p(90)', 'p(95)', 'p(99)', 'max'],
};

function header(headers: Record<string, string>, name: string): string | undefined {
  const key = Object.keys(headers).find((k) => k.toLowerCase() === name);
  return key ? headers[key] : undefined;
}

export default function (): void {
  const res = http.get(urls[exec.scenario.iterationInTest]);
  check(res, { 'status is 200': (r) => r.status === 200 });

  const queryCount = header(res.headers, 'x-query-count');
  if (queryCount !== undefined) queries.add(Number(queryCount));

  const timing = header(res.headers, 'server-timing')?.match(/dur=([\d.]+)/);
  if (timing) appTime.add(Number(timing[1]));

  const memoryPeak = header(res.headers, 'x-memory-peak');
  if (memoryPeak !== undefined) memory.add(Number(memoryPeak) / 1048576);

  payload.add((res.body?.length ?? 0) / 1024);
}

export function handleSummary(data: unknown): Record<string, string> {
  return { [__ENV.SUMMARY_FILE]: JSON.stringify(data) };
}
