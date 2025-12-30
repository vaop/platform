# VAOP - Virtual Airline Operations Platform

[![Latest Release](https://img.shields.io/github/v/release/vaop/platform)](https://github.com/vaop/platform/releases)
[![Container Image](https://img.shields.io/badge/quay.io-vaop%2Fplatform-blue)](https://quay.io/repository/vaop/platform)
[![PHPUnit](https://github.com/vaop/platform/actions/workflows/phpunit.yml/badge.svg)](https://github.com/vaop/platform/actions/workflows/phpunit.yml)
[![Dusk](https://github.com/vaop/platform/actions/workflows/dusk.yml/badge.svg)](https://github.com/vaop/platform/actions/workflows/dusk.yml)
[![Pint](https://github.com/vaop/platform/actions/workflows/pint.yml/badge.svg)](https://github.com/vaop/platform/actions/workflows/pint.yml)
[![License: AGPL-3.0](https://img.shields.io/badge/License-AGPL%20v3-blue.svg)](LICENSE)

> **Status: Alpha** - Under active development. Not yet ready for production use.

A modern, open-source flight operations platform for virtual airlines. Own your data, customize everything, deploy anywhere.

## Why VAOP?

Virtual airline management has been dominated by two choices: self-hosted solutions that require significant technical overhead, or cloud platforms that limit customization and lock in your data.

VAOP takes a different approach:

- **Flexible without code** - Configure everything through the UI: scoring rules, ranks, awards, routes, branding. No modules or plugins required to unlock features
- **Your data, your servers** - Self-host on your own infrastructure with full database access
- **Deploy anywhere** - Run on Docker, Kubernetes, or traditional shared hosting. No vendor lock-in
- **Scales with you** - From a 10-pilot hobby VA to a 10,000-pilot organization, the modular architecture handles any size comfortably
- **Financial operations** - Built-in airline economics (pilot pay, expenses, revenue tracking)
- **Fully extensible** - When you do want to customize deeper, the full source is yours under AGPL-3.0

### How It Compares

|                        | VAOP       | [phpVMS]   | [VAMSYS]  | [vaBase]  | [VAM]     |
|------------------------|------------|------------|-----------|-----------|-----------|
| **Hosting**            |            |            |           |           |           |
| Self-hosted            | Yes        | Yes        | No        | Hybrid    | Yes       |
| Shared hosting         | Yes        | Yes        | N/A       | Yes       | Yes       |
| Container support      | Yes        | Community  | N/A       | No        | No        |
| **Licensing**          |            |            |           |           |           |
| Open source            | Yes        | Yes        | No        | No        | Yes       |
| Monthly cost           | Free       | Free       | £25/mo    | £14.99/mo | Free      |
| **Features**           |            |            |           |           |           |
| No plugins required    | Yes        | No         | Yes       | Yes       | Yes       |
| Financial system       | Yes        | Yes        | No        | Limited   | Limited   |
| PIREP auto-scoring     | Yes        | Limited    | Yes       | Yes       | No        |
| Events & tours         | Yes        | Plugin     | Yes       | Yes       | No        |
| Training system        | Yes        | Plugin     | Limited   | No        | No        |
| REST API               | Yes        | Yes        | Limited   | Limited   | No        |
| **Customization**      |            |            |           |           |           |
| Theme templates        | Yes        | Yes        | CSS only  | Yes       | Limited   |
| Custom branding        | Yes        | Yes        | Yes       | Yes       | Limited   |
| White-label            | Yes        | Yes        | No        | No        | Yes       |
| **Data**               |            |            |           |           |           |
| Full data ownership    | Yes        | Yes        | No        | No        | Yes       |
| Direct database access | Yes        | Yes        | No        | No        | Yes       |

[phpVMS]: https://phpvms.net
[VAMSYS]: https://vamsys.io
[vaBase]: https://vabase.com
[VAM]: https://virtualairlinesmanager.net

## Features

### Flight Operations
- Flight booking and dispatch
- PIREP filing with configurable scoring
- Live flight tracking via ACARS
- Route and schedule management
- Cargo operations and contracts

### Fleet & Pilots
- Aircraft fleet management with maintenance tracking
- Pilot ranks, awards, and type ratings
- Training courses, checkrides, and certifications
- Pilot statistics and performance analytics

### VA Management
- Multi-leg events and tours
- Leaderboards and statistics dashboards
- Announcements and content management
- Customizable themes and branding
- Financial tracking (revenue, expenses, pilot pay)

### Integrations
- REST API for external tools
- Webhook support for automation
- SimBrief flight planning
- Discord integration
- VATSIM/IVAO network stats

## Documentation

- [Requirements](docs/getting-started/requirements.md)
- [Installation](docs/getting-started/installation.md)
- [Configuration](docs/configuration/environment.md)
- [Docker Deployment](docs/deployment/docker.md)

## FAQ

**Who is VAOP for?**

VA owners and staff who want full control over their platform without the maintenance headaches of legacy systems or the limitations of hosted services.

**Is it production ready?**

Not yet. VAOP is in alpha and under active development. We're working toward a stable release, but expect breaking changes.

**Can I migrate from another platform?**

Migration tools are planned for popular platforms including phpVMS, VAMSYS, and others. We want switching to be as painless as possible.

**Can I use VAOP commercially?**

Yes. You can run VAOP for your VA, accept donations, or even charge membership fees. The AGPL only requires that if you modify the code and run it as a service, you share those modifications.

**How do I get help?**

Open an issue on GitHub for bugs and feature requests. Community support is available through GitHub Discussions.

## Contributing

VAOP is open source and we welcome contributions. See [CONTRIBUTING.md](CONTRIBUTING.md) for development setup and guidelines.

## License

VAOP is licensed under the [GNU Affero General Public License v3.0](LICENSE). This means:

- You can use, modify, and distribute the software freely
- If you run a modified version on a server, you must make the source available to users
- Derivative works must also be licensed under AGPL-3.0

This ensures VAOP remains open source and improvements benefit the entire community.
