// Smoke tests for the vanilla extract calculator, ported from tests/smoke.php.
// The PHP version asserted on rendered HTML; here we assert on the exact
// strings the formatters produce (the same strings the UI renders).
import { loadLogic } from "./_logic.mjs";

const C = loadLogic();

function p(formula, strength) {
  return { g: C.FORMULA_G_PER_10ML[formula], s: C.STRENGTH_FACTORS[strength] };
}

function containerText(amount, unit, formula, strength) {
  const { g, s } = p(formula, strength);
  return C.FORMAT.containerBreakdown(C.calculateByContainer(parseFloat(amount), unit, g, s));
}

function alcoholText(amount, unit, formula, strength) {
  const { g, s } = p(formula, strength);
  const r = C.calculateByAlcohol(parseFloat(amount), unit, g, s);
  return C.FORMAT.alcoholHeadline(r) + "\n" + C.FORMAT.alcoholSub(r);
}

function vanillaRows(amount, unit, formula, strength) {
  const { g, s } = p(formula, strength);
  return C.FORMAT.vanillaRows(C.calculateByVanilla(parseFloat(amount), unit, g, s));
}

function contains(text, needles) {
  const problems = [];
  for (const needle of needles) {
    if (!text.includes(needle)) problems.push("missing expected " + JSON.stringify(needle));
  }
  return problems;
}

const scenarios = [
  {
    name: "byContainer, COOP single fold (explicit formula)",
    check: () => contains(containerText("750", "ml", "COOP", "Single Fold"), ["Use at least 72.48 g / 2.56 oz", "about 605 ml of alcohol"])
  },
  {
    name: "byContainer, FDA single fold",
    check: () => contains(containerText("750", "ml", "FDA", "Single Fold"), ["Use at least 62.5 g / 2.2 oz", "about 625 ml of alcohol"])
  },
  {
    name: "byContainer, no formula param (defaults to COOP)",
    check: () => contains(containerText("750", "ml", "COOP", "Single Fold"), ["Use at least 72.48 g / 2.56 oz", "about 605 ml of alcohol"])
  },
  {
    name: "byContainer, COOP double fold",
    check: () => contains(containerText("750", "ml", "COOP", "Double Fold"), ["Use at least 121.48 g / 4.29 oz", "about 507 ml of alcohol"])
  },
  {
    name: "byAlcohol, COOP single fold",
    check: () => contains(alcoholText("750", "ml", "COOP", "Single Fold"), ["Use at least 3.2 oz of vanilla beans", "3.2 oz is 91 grams"])
  },
  {
    name: "byVanilla, 4 oz (single oz row, no duplicate fluid oz)",
    check: () => {
      const rows = vanillaRows("4", "oz", "COOP", "Single Fold");
      const problems = [];
      if (!rows.some(r => r.amount === 946.56 && r.unit === "ml")) problems.push("missing 946.56 ml row");
      if (!rows.some(r => r.amount === 32.01 && r.unit === "oz")) problems.push("missing 32.01 oz row");
      if (rows.some(r => r.unit === "fluid oz")) problems.push("unexpectedly present 'fluid oz'");
      return problems;
    }
  }
];

let failures = 0;

for (const scenario of scenarios) {
  const problems = scenario.check();
  if (problems.length) {
    failures++;
    console.log("FAIL  " + scenario.name);
    for (const problem of problems) console.log("      - " + problem);
  } else {
    console.log("ok    " + scenario.name);
  }
}

console.log("\n" + scenarios.length + " scenarios, " + failures + " failed");
process.exit(failures === 0 ? 0 : 1);
