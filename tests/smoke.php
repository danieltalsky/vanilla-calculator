<?php

/**
 * Smoke tests for the vanilla extract calculator.
 *
 * Runs the real index.php in a fresh PHP process for each scenario and
 * asserts that the rendered output contains the expected results and no PHP
 * diagnostics (warnings/notices/errors). index.php declares functions, so it
 * can only be included once per process — hence one subprocess per scenario.
 *
 * Run from the project root:
 *
 *     mise x -- php tests/smoke.php
 */

$root  = dirname(__DIR__);
$index = $root . DIRECTORY_SEPARATOR . 'index.php';

$scenarios = [
	[
		'name'  => 'byContainer, COOP single fold (explicit formula)',
		'get'   => ['byContainerSize' => '1', 'containerAmount' => '750', 'containerUnit' => 'ml', 'formula' => 'COOP'],
		'expect' => ['Use at least 72.48 g / 2.56 oz', 'about 605 ml of alcohol'],
	],
	[
		'name'  => 'byContainer, FDA single fold',
		'get'   => ['byContainerSize' => '1', 'containerAmount' => '750', 'containerUnit' => 'ml', 'formula' => 'FDA'],
		'expect' => ['Use at least 62.5 g / 2.2 oz', 'about 625 ml of alcohol'],
	],
	[
		'name'  => 'byContainer, no formula param (defaults to COOP)',
		'get'   => ['byContainerSize' => '1', 'containerAmount' => '750', 'containerUnit' => 'ml'],
		'expect' => ['Use at least 72.48 g / 2.56 oz', 'about 605 ml of alcohol'],
	],
	[
		'name'  => 'byContainer, COOP double fold',
		'get'   => ['byContainerSize' => '1', 'containerAmount' => '750', 'containerUnit' => 'ml', 'formula' => 'COOP', 'strengthFactorName' => 'Double Fold'],
		'expect' => ['Use at least 121.48 g / 4.29 oz', 'about 507 ml of alcohol'],
	],
	[
		'name'  => 'byAlcohol, COOP single fold',
		'get'   => ['byAlcoholAmount' => '1', 'alcoholAmount' => '750', 'alcoholUnit' => 'ml'],
		'expect' => ['Use at least 3.2 oz of vanilla beans', '3.2 oz is 91 grams'],
	],
	[
		'name'  => 'byVanilla, 4 oz (single oz row, no duplicate fluid oz)',
		'get'   => ['byVanillaAmount' => '1', 'vanillaAmount' => '4', 'vanillaUnit' => 'oz'],
		'expect' => ['<td>946.56</td>', '<td>32.01</td><td>oz</td>'],
		'notExpect' => ['fluid oz'],
	],
];

$failures = 0;

foreach ($scenarios as $scenario) {
	$output   = run_scenario($index, $scenario['get']);
	$problems = [];

	if (preg_match('/(Warning|Notice|Deprecated|Fatal error|Parse error):/', $output, $m)) {
		$problems[] = 'PHP diagnostic: ' . $m[1];
	}

	foreach ($scenario['expect'] as $needle) {
		if (strpos($output, $needle) === false) {
			$problems[] = 'missing expected ' . var_export($needle, true);
		}
	}

	foreach ($scenario['notExpect'] ?? [] as $needle) {
		if (strpos($output, $needle) !== false) {
			$problems[] = 'unexpectedly present ' . var_export($needle, true);
		}
	}

	if ($problems) {
		$failures++;
		echo "FAIL  {$scenario['name']}\n";
		foreach ($problems as $problem) {
			echo "      - $problem\n";
		}
	} else {
		echo "ok    {$scenario['name']}\n";
	}
}

echo "\n" . count($scenarios) . " scenarios, $failures failed\n";
exit($failures === 0 ? 0 : 1);

/**
 * Run index.php in a fresh PHP process with the given $_GET and return the
 * combined stdout + stderr.
 */
function run_scenario(string $index, array $get): string {
	$code  = '$_GET = ' . var_export($get, true) . '; require ' . var_export($index, true) . ';';
	$cmd   = [PHP_BINARY, '-d', 'error_reporting=E_ALL', '-d', 'display_errors=1', '-r', $code];
	$pipes = [];

	$proc = proc_open($cmd, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
	if (!is_resource($proc)) {
		throw new RuntimeException('Failed to launch PHP subprocess');
	}

	$stdout = stream_get_contents($pipes[1]);
	$stderr = stream_get_contents($pipes[2]);
	fclose($pipes[0]);
	fclose($pipes[1]);
	fclose($pipes[2]);
	proc_close($proc);

	return $stdout . "\n" . $stderr;
}
