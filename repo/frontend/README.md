# RoadLink Frontend (Vue + Vite)

## What this app includes

- Vue 3 SPA with Pinia state management
- Role-based navigation/route guards
- PWA service worker + offline mutation queue
- Vitest suite for stores, services, and routing behavior

## Local Non-Docker Run

From `repo/frontend`:

```bash
npm install
npm run dev -- --host 0.0.0.0 --port 3000
```

Environment override (optional):

```bash
VITE_API_URL=http://localhost:8000/api/v1
```

## Build & Test

From `repo/frontend`:

```bash
npm run test
npm run build
```

## Docker Run

From `repo`:

```bash
docker compose up --build frontend
```

## Known Boundaries

- Full stack end-to-end verification depends on backend + MySQL running.
- Offline queue replay and service worker behavior are integration-tested at service/unit level in Vitest; browser-level SW assertions are not fully simulated in jsdom.

## Security Note (Token Storage)

- Current implementation stores the access token in `localStorage` for compatibility with existing SPA auth flow.
- Session clear/logout now removes token, cached user, unread counters, toast state, and queued offline mutations.
- Residual risk: token remains accessible to same-origin JavaScript if XSS exists.
- Recommended future migration: move to HttpOnly secure cookie/session-based auth with CSRF protection to reduce token exposure surface.
