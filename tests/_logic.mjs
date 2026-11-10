// Loads the pure calculation logic out of index.html into a Node vm.
// No DOM, no dependencies — just the <script data-logic> block.
import vm from "node:vm";
import { readFileSync } from "node:fs";

export function loadLogic() {
  const html = readFileSync(new URL("../index.html", import.meta.url), "utf8");
  const match = html.match(/<script\b[^>]*data-logic>([\s\S]*?)<\/script>/i);
  if (!match) throw new Error("data-logic script not found in index.html");
  const ctx = {};
  vm.createContext(ctx);
  vm.runInContext(match[1], ctx, { filename: "index.html (data-logic)" });
  if (!ctx.VanillaCalc) throw new Error("VanillaCalc was not exposed by the data-logic script");
  return ctx.VanillaCalc;
}
