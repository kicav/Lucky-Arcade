# Lucky Arcade security model

## Two-factor authentication

Lucky Arcade implements RFC 6238-style TOTP using HMAC-SHA1, a 30-second period and six digits. The server accepts the current period and one adjacent period on either side to tolerate small clock drift.

The TOTP secret uses Laravel's encrypted model cast. Recovery codes are displayed only after creation, stored as password hashes inside an encrypted array, and removed after use.

## Login flow

1. Validate email and password without authenticating the browser session.
2. Reject suspended player accounts.
3. When two-factor authentication is enabled, store only a temporary user ID and remember flag in the session.
4. Authenticate the session only after a valid TOTP or recovery code.
5. Regenerate the session identifier after authentication.

Login routes are rate-limited. Authentication events record user ID when known, IP address, user agent and limited metadata.

## Administrator roles

Authorization is checked by the `admin.area` middleware. Views hide unavailable links for usability, but every route is independently protected.

| Area | Super Admin | Operations | Support | Analyst |
|---|---:|---:|---:|---:|
| Dashboard | Yes | Yes | Yes | Yes |
| Analytics | Yes | Yes | No | Yes |
| Games | Yes | Yes | No | No |
| Player read access | Yes | Yes | Yes | No |
| Player suspension/credits | Yes | Yes | No | No |
| Play history | Yes | Yes | No | Yes |
| Audit log | Yes | Yes | No | Yes |
| Promotions/announcements | Yes | Yes | No | No |
| Support tickets | Yes | Yes | Yes | No |
| Weekly League settlement | Yes | Yes | No | No |
| System backups | Yes | Yes | No | No |
| Admin role assignment | Yes | No | No | No |

## HTTP response headers

Every response includes clickjacking, MIME-sniffing, referrer and browser-permission protections. HSTS is added only for HTTPS requests.

A strict Content Security Policy is intentionally not enabled in this demo because the current Blade views include inline behaviors. Add nonce-based CSP before production deployment.

## Production recommendations

- Change demo credentials immediately.
- Enable TOTP on every administrator account.
- Use PostgreSQL and Redis for a continuously hosted deployment.
- Store `APP_KEY` and all environment secrets outside source control.
- Send security logs to centralized monitoring.
- Add email verification, password reset delivery and alerting for suspicious login patterns.
