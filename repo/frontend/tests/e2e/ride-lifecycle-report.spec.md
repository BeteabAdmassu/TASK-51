# E2E-Style Test Spec: Ride Lifecycle + Report Export

This repo does not currently include Playwright/Cypress runtime wiring, so this document defines an executable manual/automation-ready flow for critical path verification.

## Scenario

1. Rider logs in and creates a ride request.
2. Driver logs in and accepts the ride.
3. Driver starts the ride.
4. Driver completes the ride.
5. Rider and driver observe timeline/chat updates.
6. Fleet manager/admin opens reports and triggers export.

## Preconditions

- Backend + frontend + DB running.
- Seed users available (`rider01`, `driver01`, `fleet01`, `admin01`).

## Steps and Assertions

1. **Rider create ride**
   - Navigate to rider trip creation flow.
   - Submit valid payload.
   - Assert API `POST /api/v1/ride-orders` returns `201`.
   - Assert new ride appears in rider trip list.

2. **Driver accept/start/complete**
   - Login as driver.
   - Open available rides and accept the created ride.
   - Assert status transitions: `matching -> accepted -> in_progress -> completed`.
   - Assert timeline entries appear in order.

3. **Chat/timeline updates**
   - Open ride chat as rider and send message.
   - Assert message visible to driver.
   - On completion, assert chat is disbanded and completion notice appears.

4. **Report export trigger**
   - Login as fleet manager/admin.
   - Open `/reports`.
   - Trigger export in `CSV` and `XLSX` formats.
   - Assert export API returns signed URL and download succeeds.

## Security Boundary Checks

- Rider cannot access `/reports` route (redirect/denied).
- Driver cannot call report export endpoints (`403`).
- Signed report download still requires auth + role + ownership/allowed checks.
