// Lint: syntax-check every inline <script> block in index.html.
// Replaces the old `php -l index.php` lint step.
import vm from "node:vm";
import { readFileSync } from "node:fs";

const html = readFileSync(new URL("../index.html", import.meta.url), "utf8");

// Match inline <script> blocks (skip any that load an external src).
const re = /<script\b(?![^>]*\bsrc=)[^>]*>([\s\S]*?)<\/script>/gi;

let match;
let count = 0;
let failures = 0;

while ((match = re.exec(html)) !== null) {
  count++;
  try {
    new vm.Script(match[1], { filename: "index.html (inline script #" + count + ")" });
    console.log("ok    inline script #" + count);
  } catch (err) {
    failures++;
    console.log("FAIL  inline script #" + count + ": " + err.message);
  }
}

console.log("\n" + count + " inline scripts checked, " + failures + " failed");
process.exit(failures === 0 ? 0 : 1);
