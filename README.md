# Vanilla Extract Calculator

A single-page web application that helps people calculate the correct ratio of
vanilla beans and alcohol per container when making vanilla extract.

Used by hundreds of thousands of people and still in heavy use.

**Live site:** <https://danieltalsky.com/vanilla>

## Current state

This codebase has only recently been version controlled for the first time.
It is intentionally minimal: a **single HTML page with inline PHP**
(`index.php`). There is no framework, no package manager, no build step, and no
runtime dependencies beyond PHP itself.

## Requirements

- [mise](https://mise.jdx.dev) — for managing the PHP runtime

## Setup

1. Install mise (see the link above) if you don't already have it.
2. Install the pinned PHP version from `.mise.toml`:

   ```sh
   mise install
   ```

3. Make sure mise is activated in your shell (`mise activate` / `mise use`),
   so `php` resolves to the project's PHP.

## Running locally

From the project root:

```sh
mise x -- php -S localhost:8000
```

Then open <http://localhost:8000> in your browser.

If `php` is already on your PATH via mise, you can also just run:

```sh
php -S localhost:8000
```

## Testing

Two dependency-free test scripts, run from the project root:

```sh
mise x -- php tests/smoke.php   # integration: asserts key rendered outputs
mise x -- php tests/unit.php    # unit: exercises the calculation functions
```

Both exit non-zero on failure.

## How it works

The calculator has three modes, selected via the query string:

- `?byContainerSize` (default) — enter a container capacity; get the alcohol
  and vanilla amounts to fill it.
- `?byAlcoholAmount` — enter an amount of alcohol; get the vanilla needed.
- `?byVanillaAmount` — enter an amount of vanilla; get the alcohol needed.

Advanced options are also passed as query parameters:

- Fold strength (`strengthFactorName`): Single Fold, One-and-a-half Fold,
  Double Fold, or Triple Fold.
- Formula (`formula`): `FDA`, `COOP`, or `BUDGET`.

### The formulas

The three formulas are grams of vanilla beans per 10 ml of alcohol:

- **FDA** — 1 g per 10 ml. The legal minimum for calling it "vanilla extract"
  with Grade B beans.
- **Co-Op** (`COOP`) — 1.198 g per 10 ml. ~20% stronger than FDA, based on
  1 oz of beans to 1 cup of alcohol.
- **Budget** (`BUDGET`) — 1.05 g per 10 ml. 5% more than FDA, to account for
  heavier Grade A beans.

## Author

Built by Daniel Talsky (danieltalsky@gmail.com). Provided as-is with no
warranty of any kind.

## Project conventions

- Keep the application a single `index.php` file with inline
  HTML/CSS/JS/PHP.
- No framework, no package manager, no build step.
