# Copilot Instructions

## Commit Messages

Use conventional commit format for all commit messages:

```
<type>[optional scope]: <description>
```

- Start the description with a lowercase letter
- Do not end with a period
- Use imperative mood ("add feature" not "added feature")

Valid types:
- `feat` - New feature
- `fix` - Bug fix
- `docs` - Documentation only
- `style` - Formatting, missing semicolons, etc.
- `refactor` - Code change that neither fixes a bug nor adds a feature
- `perf` - Performance improvement
- `test` - Adding or correcting tests
- `build` - Changes to build system or dependencies
- `ci` - Changes to CI configuration
- `chore` - Other changes that don't modify src or test files
- `revert` - Reverts a previous commit

Examples:
- `feat: add user authentication`
- `fix(api): resolve null pointer exception`
- `docs: update installation instructions`

## Pull Request Titles

Follow the same conventional commit format for PR titles.
