/**
 * Prueba de Carga y Concurrencia k6 — Fase A9 (sys-POS)
 * Ejecutar contra entorno de staging (MySQL/PostgreSQL):
 *   k6 run --vus 15 --duration 30s tests/Load/k6-pos-load.js
 */
import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
    stages: [
        { duration: '10s', target: 10 },
        { duration: '15s', target: 20 },
        { duration: '5s', target: 0 },
    ],
    thresholds: {
        http_req_duration: ['p(95)<500'],
        http_req_failed: ['rate<0.01'],
    },
};

const BASE_URL = __ENV.BASE_URL || 'http://127.0.0.1:8000';

export default function () {
    const res = http.get(`${BASE_URL}/login`);
    check(res, {
        'login status is 200': (r) => r.status === 200,
    });
    sleep(1);
}
