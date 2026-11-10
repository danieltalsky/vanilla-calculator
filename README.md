# Vanilla Extract Calculator

[![Test](https://github.com/danieltalsky/vanilla-calculator/actions/workflows/test.yml/badge.svg)](https://github.com/danieltalsky/vanilla-calculator/actions/workflows/test.yml)

A single-page web application that helps people calculate the correct ratio of
vanilla beans and alcohol per container when making vanilla extract.

Used by hundreds of thousands of people and still in heavy use.

**Live site:** <https://danieltalsky.com/vanilla>
**Repository:** <https://github.com/danieltalsky/vanilla-calculator>

## Current state

This codebase has only recently been version controlled for the first time.
It is intentionally minimal: a **single static HTML page with inline
JavaScript** (`index.html`). There is no framework, no package manager, no build
step, and no runtime dependencies — the app runs entirely in the browser.

## Requirements

- [mise](https://mise.jdx.dev) — for managing the Node runtime (used to run the
  tests; the app itself only needs a browser)

## Setup

1. Install mise (see the link above) if you don't already have it.
2. Install the pinned Node version from `.mise.toml`:

   ```sh
   mise install
   ```

3. Make sure mise is activated in your shell (`mise activate` / `mise use`),
   so `node` resolves to the project's Node.

## Running locally

The app is a single static file — just open `index.html` in a browser. Query
strings work from `file://` too, so shareable links behave the same as in
production. If you want a local HTTP server, any static server works (for
example `python -m http.server 8000`).

## Testing

Run the full suite (lint + smoke + unit) with:

```sh
mise run test
```

Or run the scripts individually:

```sh
mise x -- node tests/lint.mjs    # syntax-checks the inline scripts
mise x -- node tests/smoke.mjs   # integration: asserts the rendered strings
mise x -- node tests/unit.mjs    # unit: exercises the calculation functions
```

All exit non-zero on failure.

## How it works

The calculator runs entirely in the browser: `index.html` reads the query
string, computes the results in JavaScript, and updates the page live (no page
reload). The three modes are selected via the query string:

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

## Using the calculator

The calculator answers three questions. Pick the tab that matches what you
already know:

- **by Container Size** (default) — you know the bottle size. Enter its capacity
  and unit; it tells you how much vanilla and alcohol to add to fill it.
- **by Vanilla Amount** — you have a fixed amount of vanilla. Enter its weight
  (grams or ounces); it tells you how much alcohol you can turn into extract.
- **by Alcohol Amount** — you have a fixed amount of alcohol. Enter it; it tells
  you how much vanilla to use.

The **Strength & formula** settings tune the recipe:

- **Strength** — from Single Fold (regular strength) up to Triple Fold.
- **Formula** — which guideline to follow (FDA, Co-Op, or Budget).

Click **What are strength and formula?** for a plain-English explanation of what
the folds and formulas mean.

## Design explorations

`design-explorations.html` is the scratch mockup used to explore visual
directions before the rewrite. It is superseded by `index.html` and is not the
live calculator — kept only for reference.

## Author

Built by Daniel Talsky (danieltalsky@gmail.com). Provided as-is with no
warranty of any kind.

## Project conventions

- Keep the application a single `index.html` file with inline HTML/CSS/JS.
- No framework, no package manager, no build step.
- Keep the calculation functions (`calculateByContainer`, `calculateByAlcohol`,
  `calculateByVanilla`) pure and untouched unless the behavior is being changed
  on purpose.
