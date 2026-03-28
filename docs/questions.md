# Business Logic Questions Log

1. [Authentication Lockout Flow]
   - **Question**: Should the 5th failed login return `401` or immediately return `429`?
   - **My Understanding**: The acceptance tests mention "After 5 failures, 6th attempt returns 429," which implies attempt 5 still returns `401` and activates lockout.
   - **Solution**: Implemented lock activation on the 5th failure while still returning `401`; subsequent attempts during lockout return `429` with `locked_until`.

2. [Role Middleware and Admin Access]
   - **Question**: Should admin bypass role checks automatically, or only when explicitly listed in allowed roles?
   - **My Understanding**: Middleware should enforce only explicitly allowed roles for predictability and explicit authorization.
   - **Solution**: Implemented strict role matching and explicitly included `admin` on routes where universal admin access is expected.
