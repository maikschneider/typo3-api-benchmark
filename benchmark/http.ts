import { lookup } from 'node:dns/promises';
import { BASE_URL } from './contenders';

// *.ddev.site resolves to 127.0.0.1 through public DNS, but routers with DNS rebinding protection
// drop such answers. Then requests go to 127.0.0.1 with the site's Host header instead.

const host = new URL(BASE_URL).hostname;

/** IP to pin the benchmark host to, or null when it resolves normally. */
export const pinnedAddress: string | null = await lookup(host).then(
  () => null,
  () => {
    console.error(`${host} does not resolve, sending requests to 127.0.0.1 with Host: ${host}`);
    return '127.0.0.1';
  },
);

export function get(path: string): Promise<Response> {
  if (pinnedAddress === null) {
    return fetch(BASE_URL + path, { tls: { rejectUnauthorized: false } });
  }
  const url = new URL(path, BASE_URL);
  url.hostname = pinnedAddress;
  return fetch(url, { headers: { Host: host }, tls: { rejectUnauthorized: false } });
}

/** Extra k6 arguments that pin the host when it does not resolve. */
export const k6HostArgs = (): string[] => (pinnedAddress === null ? [] : ['-e', `PIN_HOST=${host}`, '-e', `PIN_ADDRESS=${pinnedAddress}`]);
