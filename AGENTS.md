# AGENTS.md

Guidance for AI coding agents working in this repository.

## Project overview

A single-page vanilla extract calculator. The entire application lives in one
file, `index.html`, which contains inline HTML, CSS, and JavaScript. There is no
framework, no package manager, and no build step — it runs entirely in the
browser.

## Hard constraints

- Keep the application a single `index.html` file with inline code. Do not split
  it into multiple files, add a framework, or introduce a build step without
  explicit approval.
- Do not change the existing calculation logic without approval. The numbers it
  produces are relied upon by a large user base.
- Preserve public-facing behavior (query-string parameters, defaults, and
  rounding) unless asked to change it.

## Conventions

- Match the surrounding indentation and style.
- Target the Node version pinned in `.mise.toml` (currently 22).
- No external dependencies.

## Workflow

- Use a pull-request-based workflow: create a feature branch, commit there, push,
  and open a PR against `main` rather than committing directly to `main`.
- Merge the PR once CI passes. CI runs `mise run test` via
  `.github/workflows/test.yml`.

## Running and testing

- Run: open `index.html` directly in a browser (static; no server required).
- Test: `mise run test` (runs lint + smoke + unit; defined in `.mise.toml`).

## Key details

- Calculator mode is selected by query parameter (read client-side from
  `URLSearchParams`): `?byContainerSize`, `?byAlcoholAmount`, or
  `?byVanillaAmount`.
- The three calculations live in pure functions in the `<script data-logic>`
  block of `index.html`: `calculateByContainer`, `calculateByAlcohol`, and
  `calculateByVanilla`. They take explicit inputs and return plain objects (no
  DOM access, no side effects). They are exposed on `globalThis.VanillaCalc` so
  Node can load them for tests.
- Conversion data lives in the constants `ML_PER`, `PER_ML`, `OZ_PER_G`,
  `G_PER_OZ`, `STRENGTH_FACTORS`, `FORMULA_G_PER_10ML`, and
  `APPROX_ML_OF_VANILLA_DISPLACEMENT_PER_G`.
- The `<script data-ui>` block does all DOM wiring (reads inputs, renders
  results, and writes the URL via `history.replaceState`).
- User input is sanitized: numbers via `Number()`, units via whitelisting
  (`ML_PER` keys for volume, `["g", "oz"]` for vanilla).

## Structure, controls, and behavior

- `index.html` is the entire production app (HTML + CSS + JS inline):
  - `<script data-logic>` — pure constants, calculations, and formatters.
  - `<script data-ui>` — browser DOM wiring (state, rendering, URL).
- `original-vanilla-calculator.php` is a pre-version-control reference copy.
  `design-explorations.html` is a superseded design mockup — not production.
- Flow: on load the UI script parses `URLSearchParams`, runs the pure
  calculation function for the active mode, and renders results live (no page
  reload). Interacting with the controls re-renders and updates the URL via
  `history.replaceState`.
- Controls:
  - Mode tabs (3): by Container Size, by Vanilla Amount, by Alcohol Amount.
  - Each mode has a numeric amount input plus a unit `<select>`.
  - Strength & formula (always visible): a strength select (`Single Fold`=1 …
    `Triple Fold`=3) and a formula select (`FDA`=1, `Budget`=1.05,
    `Co-Op`=1.198 g per 10 ml), plus a "What are strength and formula?" link
    that opens a modal `<dialog>`.
- Results by mode:
  - Container: a vertical vessel visual (vanilla + alcohol fill) + stat cards +
    a text breakdown.
  - Alcohol: "use at least X oz of vanilla beans" (+ grams).
  - Vanilla: a list of alcohol amounts across units (only rows > 0.1 shown).
- Defaults: container 750 ml, alcohol 750 ml, vanilla 4 oz, Single Fold, Co-Op.
  If both `byAlcoholAmount` and `byVanillaAmount` are present, the vanilla mode
  wins. Rounding uses `phpRound()` (mimics PHP half-up); `calculateByAlcohol`
  rounds oz first and derives grams from the rounded oz.

## Tests

- Tests are Node `.mjs` scripts run via `mise run test`.
- `tests/_logic.mjs` loads the `<script data-logic>` block into a vm (helper).
- `tests/lint.mjs` syntax-checks every inline `<script>` block.
- `tests/unit.mjs` asserts the calculation functions (ported from the old PHP).
- `tests/smoke.mjs` asserts the formatter output strings (ported from the old
  PHP smoke test).
- Keep tests in sync when changing calculation or formatting logic.
