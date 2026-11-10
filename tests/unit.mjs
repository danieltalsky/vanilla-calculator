// Unit tests for the calculation functions, ported from tests/unit.php.
// The JS lives in index.html; we load it via a vm so the exact same functions
// the browser runs are exercised here.
import { loadLogic } from "./_logic.mjs";

const C = loadLogic();

let failures = 0;

function assertClose(expected, actual, label, tolerance = 0.005) {
  if (typeof actual === "number" && Math.abs(expected - actual) <= tolerance) {
    console.log("ok    " + label);
  } else {
    failures++;
    console.log("FAIL  " + label + "\n      expected ~" + expected + ", got " + actual);
  }
}

function assertTrue(condition, label) {
  if (condition) {
    console.log("ok    " + label);
  } else {
    failures++;
    console.log("FAIL  " + label);
  }
}

// --- calculateByContainer -------------------------------------------------

let r = C.calculateByContainer(750, "ml", 1.198, 1); // COOP, single fold
assertClose(750, r.containerAmountMl, "container: containerAmountMl (ml)");
assertClose(605, r.alcoholMl, "container: alcoholMl (COOP single)");
assertClose(605, r.alcoholInContainerUnit, "container: alcoholInContainerUnit (ml)");
assertClose(144.96, r.vanillaMl, "container: vanillaMl (COOP single)");
assertClose(72.48, r.vanillaG, "container: vanillaG (COOP single)");
assertClose(2.56, r.vanillaOz, "container: vanillaOz (COOP single)");

r = C.calculateByContainer(750, "ml", 1, 1); // FDA, single fold
assertClose(625, r.alcoholMl, "container: alcoholMl (FDA single)");
assertClose(62.5, r.vanillaG, "container: vanillaG (FDA single)");
assertClose(2.2, r.vanillaOz, "container: vanillaOz (FDA single)");

r = C.calculateByContainer(750, "ml", 1.198, 2); // COOP, double fold
assertClose(507, r.alcoholMl, "container: alcoholMl (COOP double)");
assertClose(121.48, r.vanillaG, "container: vanillaG (COOP double)");
assertClose(4.29, r.vanillaOz, "container: vanillaOz (COOP double)");

// Invariant: alcohol + vanilla displacement fills the container.
for (const args of [[750, "ml", 1.198, 1], [750, "ml", 1, 1], [1, "gallons", 1.198, 1], [16, "oz", 1.05, 3]]) {
  r = C.calculateByContainer(args[0], args[1], args[2], args[3]);
  const filled = r.alcoholMl + r.vanillaMl;
  assertTrue(
    Math.abs(filled - r.containerAmountMl) < 0.05,
    "container fills (alcohol+vanilla ~= capacity) for " + args.join(",")
  );
}

// --- calculateByAlcohol ---------------------------------------------------

r = C.calculateByAlcohol(750, "ml", 1.198, 1); // COOP, single fold
assertClose(3.2, r.vanillaOz, "alcohol: vanillaOz (COOP single)");
assertClose(91, r.vanillaG, "alcohol: vanillaG (COOP single)");

r = C.calculateByAlcohol(500, "ml", 1.198, 1);
assertClose(2.1, r.vanillaOz, "alcohol: vanillaOz (500 ml)");
assertClose(60, r.vanillaG, "alcohol: vanillaG (500 ml)");

// Invariant: grams are derived from the (rounded) ounces.
for (const args of [[750, "ml", 1.198, 1], [500, "ml", 1, 2], [2, "cups", 1.05, 1]]) {
  r = C.calculateByAlcohol(args[0], args[1], args[2], args[3]);
  assertTrue(
    r.vanillaG === Math.round(r.vanillaOz * C.G_PER_OZ),
    "alcohol: grams consistent with oz for " + args.join(",")
  );
}

// --- calculateByVanilla ---------------------------------------------------

r = C.calculateByVanilla(4, "oz", 1.198, 1); // COOP, single fold
assertClose(946.56, r.ml, "vanilla: alcohol in ml (4 oz)");
assertClose(32.01, r.oz, "vanilla: alcohol in oz (4 oz)");
assertClose(0.25, r.gallons, "vanilla: alcohol in gallons (4 oz)");
assertTrue(Object.keys(r).length === 7, "vanilla: exactly 7 units returned");
assertTrue(!("fluid oz" in r), "vanilla: no duplicate fluid oz unit");

// Same weight in grams should match the oz input.
const rOunces = C.calculateByVanilla(4, "oz", 1.198, 1);
const rGrams = C.calculateByVanilla(4 * C.G_PER_OZ, "g", 1.198, 1);
assertClose(rOunces.ml, rGrams.ml, "vanilla: 4 oz equals 113.398 g");

console.log("\n" + (failures === 0 ? "All unit tests passed." : failures + " unit test(s) failed."));
process.exit(failures === 0 ? 0 : 1);
