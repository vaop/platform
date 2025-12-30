# Contributing to VAOP

Thank you for your interest in contributing to the Virtual Airline Operations Platform.

## Development Setup

We recommend using [Laravel Sail](https://laravel.com/docs/sail) for development. Sail provides a Docker-based environment with all dependencies pre-configured, ensuring consistency across all contributors.

### Prerequisites

- Docker Desktop (or Docker Engine + Docker Compose)

### Getting Started with Sail

```bash
# Clone the repository
git clone https://github.com/vaop/platform.git
cd platform

# Copy environment file
cp .env.example .env

# Install dependencies and start Sail
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs

# Start the environment
./vendor/bin/sail up -d

# Run setup (migrations, key generation, etc.)
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
./vendor/bin/sail npm install
./vendor/bin/sail npm run build
```

The application will be available at `http://localhost`.

### Sail Commands

```bash
# Start/stop the environment
./vendor/bin/sail up -d
./vendor/bin/sail down

# Run artisan commands
./vendor/bin/sail artisan <command>

# Run composer commands
./vendor/bin/sail composer <command>

# Run npm commands
./vendor/bin/sail npm <command>

# Run tests
./vendor/bin/sail test
```

**Tip:** Add an alias to your shell profile for convenience:
```bash
alias sail='./vendor/bin/sail'
```

### Alternative: Native Setup

If you prefer not to use Docker, you can run the application natively:

**Requirements:**
- PHP 8.4+, Node.js 25+, MariaDB 10+, Composer 2.x

```bash
git clone https://github.com/vaop/platform.git
cd platform
composer setup
composer dev
```

This starts the Laravel dev server at `http://localhost:8000` with queue worker, log viewer, and Vite.

## Code Style

This project uses [Laravel Pint](https://laravel.com/docs/pint) for code formatting.

```bash
# Check code style
./vendor/bin/sail pint --test

# Fix code style
./vendor/bin/sail pint
```

Code style is enforced via CI - PRs with style violations will fail checks.

## Testing

```bash
# Run unit and feature tests
./vendor/bin/sail test

# Run browser tests
./vendor/bin/sail dusk
```

All tests must pass before a PR can be merged.

## Commit Messages

Use [Conventional Commits](https://www.conventionalcommits.org/) format:

```
<type>[optional scope]: <description>
```

**Rules:**
- Start the description with a lowercase letter
- Do not end with a period
- Use imperative mood ("add feature" not "added feature")

**Types:**
| Type | Description |
|------|-------------|
| `feat` | New feature |
| `fix` | Bug fix |
| `docs` | Documentation only |
| `style` | Formatting, missing semicolons, etc. |
| `refactor` | Code change that neither fixes a bug nor adds a feature |
| `perf` | Performance improvement |
| `test` | Adding or correcting tests |
| `build` | Changes to build system or dependencies |
| `ci` | Changes to CI configuration |
| `chore` | Other changes that don't modify src or test files |
| `revert` | Reverts a previous commit |

**Examples:**
```
feat: add user authentication
fix(api): resolve null pointer exception
docs: update installation instructions
```

## Pull Requests

1. Create a feature branch from `main`
2. Make your changes with clear, atomic commits
3. Ensure tests pass and code style is correct
4. Open a PR with a title following conventional commit format
5. Wait for review

PR titles are validated by CI to ensure they follow the conventional commit format.

## Project Structure

```
src/
├── App/          # Application layer (controllers, commands)
├── Domain/       # Core business logic and models
├── System/       # Infrastructure, providers, and support
└── Services/     # Application services

tests/
├── Unit/         # Unit tests
├── Feature/      # Feature/integration tests
└── Browser/      # Dusk browser tests
```
