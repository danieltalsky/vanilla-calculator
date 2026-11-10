<?php

/**
 * Unit tests for the calculation functions extracted from index.php.
 *
 * index.php declares its functions at top level and renders the page when
 * included, so we load it once with output buffering and discard the page
 * output, then exercise the pure calculation functions directly.
 *
 * Run from the project root:
 *
 *     mise x -- php tests/unit.php
 */

$root = dirname(__DIR__);
ob_start();
require $root . DIRECTORY_SEPARATOR . 'index.php';
ob_end_clean();

$failures = 0;

function assertClose($expected, $actual, $label, $tolerance = 0.005) {
	global $failures;
	if (!is_numeric($actual) || abs((float) $expected - (float) $actual) > $tolerance) {
		$failures++;
		echo "FAIL  $label\n      expected ~$expected, got " . var_export($actual, true) . "\n";
	} else {
		echo "ok    $label\n";
	}
}

function assertTrue($condition, $label) {
	global $failures;
	if (!$condition) {
		$failures++;
		echo "FAIL  $label\n";
	} else {
		echo "ok    $label\n";
	}
}

// --- calculateByContainer -------------------------------------------------

$r = calculateByContainer(750, 'ml', 1.198, 1); // COOP, single fold
assertClose(750, $r['containerAmountMl'], 'container: containerAmountMl (ml)');
assertClose(605, $r['alcoholMl'], 'container: alcoholMl (COOP single)');
assertClose(605, $r['alcoholInContainerUnit'], 'container: alcoholInContainerUnit (ml)');
assertClose(144.96, $r['vanillaMl'], 'container: vanillaMl (COOP single)');
assertClose(72.48, $r['vanillaG'], 'container: vanillaG (COOP single)');
assertClose(2.56, $r['vanillaOz'], 'container: vanillaOz (COOP single)');

$r = calculateByContainer(750, 'ml', 1, 1); // FDA, single fold
assertClose(625, $r['alcoholMl'], 'container: alcoholMl (FDA single)');
assertClose(62.5, $r['vanillaG'], 'container: vanillaG (FDA single)');
assertClose(2.2, $r['vanillaOz'], 'container: vanillaOz (FDA single)');

$r = calculateByContainer(750, 'ml', 1.198, 2); // COOP, double fold
assertClose(507, $r['alcoholMl'], 'container: alcoholMl (COOP double)');
assertClose(121.48, $r['vanillaG'], 'container: vanillaG (COOP double)');
assertClose(4.29, $r['vanillaOz'], 'container: vanillaOz (COOP double)');

// Invariant: alcohol + vanilla displacement fills the container.
foreach ([[750, 'ml', 1.198, 1], [750, 'ml', 1, 1], [1, 'gallons', 1.198, 1], [16, 'oz', 1.05, 3]] as $args) {
	$r = calculateByContainer($args[0], $args[1], $args[2], $args[3]);
	$filled = $r['alcoholMl'] + $r['vanillaMl'];
	assertTrue(
		abs($filled - $r['containerAmountMl']) < 0.05,
		"container fills (alcohol+vanilla ~= capacity) for " . implode(',', $args)
	);
}

// --- calculateByAlcohol ---------------------------------------------------

$r = calculateByAlcohol(750, 'ml', 1.198, 1); // COOP, single fold
assertClose(3.2, $r['vanillaOz'], 'alcohol: vanillaOz (COOP single)');
assertClose(91, $r['vanillaG'], 'alcohol: vanillaG (COOP single)');

$r = calculateByAlcohol(500, 'ml', 1.198, 1);
assertClose(2.1, $r['vanillaOz'], 'alcohol: vanillaOz (500 ml)');
assertClose(60, $r['vanillaG'], 'alcohol: vanillaG (500 ml)');

// Invariant: grams are derived from the (rounded) ounces.
foreach ([[750, 'ml', 1.198, 1], [500, 'ml', 1, 2], [2, 'cups', 1.05, 1]] as $args) {
	$r = calculateByAlcohol($args[0], $args[1], $args[2], $args[3]);
	assertTrue(
		$r['vanillaG'] === round($r['vanillaOz'] * G_PER_OZ),
		'alcohol: grams consistent with oz for ' . implode(',', $args)
	);
}

// --- calculateByVanilla ---------------------------------------------------

$r = calculateByVanilla(4, 'oz', 1.198, 1); // COOP, single fold
assertClose(946.56, $r['alcoholByUnit']['ml'], 'vanilla: alcohol in ml (4 oz)');
assertClose(32.01, $r['alcoholByUnit']['oz'], 'vanilla: alcohol in oz (4 oz)');
assertClose(0.25, $r['alcoholByUnit']['gallons'], 'vanilla: alcohol in gallons (4 oz)');
assertTrue(count($r['alcoholByUnit']) === 7, 'vanilla: exactly 7 units returned');
assertTrue(!array_key_exists('fluid oz', $r['alcoholByUnit']), 'vanilla: no duplicate fluid oz unit');

// Same weight in grams should match the oz input.
$rOunces = calculateByVanilla(4, 'oz', 1.198, 1);
$rGrams  = calculateByVanilla(4 * G_PER_OZ, 'g', 1.198, 1);
assertClose($rOunces['alcoholByUnit']['ml'], $rGrams['alcoholByUnit']['ml'], 'vanilla: 4 oz equals 113.398 g');

echo "\n" . ($failures === 0 ? 'All unit tests passed.' : "$failures unit test(s) failed.") . "\n";
exit($failures === 0 ? 0 : 1);
