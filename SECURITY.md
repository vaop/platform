# Security Policy

## Reporting a Vulnerability

If you discover a security vulnerability in VAOP, please report it to **security@vaop.io** rather than opening a public issue.

**Do not disclose security vulnerabilities publicly until they have been addressed.**

For sensitive reports, PGP encryption is available:
- Key: [keys.openpgp.org](https://keys.openpgp.org/search?q=security@vaop.io)
- Fingerprint: `C32E 8916 1951 B0EF 9838 21F7 55BB D16B E5FF 0F4E`

When reporting, please include:

- A description of the vulnerability
- Steps to reproduce the issue
- Potential impact
- Any suggested fixes (optional)

## Response Timeline

- We will acknowledge receipt within 48 hours
- We will provide an initial assessment within 7 days
- We aim to release patches for critical vulnerabilities as quickly as possible

## Supported Versions

Security updates are provided for the latest release only. We recommend always running the most recent version.

## Security Best Practices

When deploying VAOP:

- Keep your installation updated to the latest version
- Use HTTPS for all traffic
- Use strong, unique database passwords
- Regularly back up your database
- Restrict database access to only the application server
