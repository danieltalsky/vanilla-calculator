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

## Workflow

- Use a pull-request-based workflow: create a feature branch, commit there, push,
  and open a PR against `main` rather than committing directly to `main`.
- Merge the PR once CI passes. CI runs `mise run test` via
  `.github/workflows/test.yml`.

## Running and testing

- Run: `mise x -- php -S localhost:8000`, then open http://localhost:8000.
- Test: `mise run test` (runs lint + smoke + unit; defined in `.mise.toml`).

## Key details

- Calculator mode is selected by query parameter:
  `?byContainerSize`, `?byAlcoholAmount`, or `?byVanillaAmount`.
- The three calculations live in pure functions near the top of `index.php`:
  `calculateByContainer`, `calculateByAlcohol`, and `calculateByVanilla`.
  They take explicit inputs and return arrays (no globals or side effects).
- Conversion data lives in the constants `ML_PER`, `PER_ML`, `OZ_PER_G`,
  `G_PER_OZ`, `STRENGTH_FACTORS`, `FORMULA_G_PER_10ML`, and
  `APPROX_ML_OF_VANILLA_DISPLACEMENT_PER_G`.
- User input is read from `$_GET` and sanitized with `floatval()` and
  `in_array()` whitelisting.
