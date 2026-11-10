# AGENTS.md

Guidance for AI coding agents working in this repository.

## Project overview

A single-page vanilla extract calculator. The entire application lives in one
file, `index.php`, which contains inline PHP, HTML, CSS, and JavaScript. There
is no framework, no package manager, and no build step.

## Hard constraints

- Keep the application a single `index.php` file with inline code. Do not split
  it into multiple files, add a framework, or introduce a build step without
  explicit approval.
- Do not change the existing calculation logic without approval. The numbers it
  produces are relied upon by a large user base.
- Preserve public-facing behavior (query-string parameters, defaults, and
  rounding) unless asked to change it.

## Conventions

- PHP indentation uses tabs; match the surrounding style.
- Target the PHP version pinned in `.mise.toml` (currently 8.5).
- No external dependencies.

## Running and testing

- Run: `mise x -- php -S localhost:8000`, then open http://localhost:8000.
- Smoke tests: `mise x -- php tests/smoke.php` (asserts key rendered outputs).

## Key details

- Calculator mode is selected by query parameter:
  `?byContainerSize`, `?byAlcoholAmount`, or `?byVanillaAmount`.
- Advanced options map to the `$strengthFactors` and `$formula_g_per_10ml`
  arrays at the top of `index.php`.
- User input is read from `$_GET` and sanitized with `floatval()` and
  `in_array()` whitelisting.
