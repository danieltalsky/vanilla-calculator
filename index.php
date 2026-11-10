<?php

// Constants
$oz_per_g = 0.035274;
$g_per_oz = 28.3495;
$ml_per = [
	"ml" => 1,
	"oz" => 29.574,
	"cups" => 236.588,
	"pints" => 473.176,
	"quarts" => 946.353,
	"liters" => 1000,
	"gallons" => 3785
];
$per_ml = [
	"ml" => 1,
	"fluid oz" => 0.033814,
	"oz" => 0.033814,
	"cups" => 0.00422675,
	"pints" => 0.00211338,
	"quarts" => 0.00105669,
	"liters" => 0.001,
	"gallons" => 0.000264172
];
$strengthFactors = [
	"Single Fold" => 1,
	"One-and-a-half Fold" => 1.5,
	"Double Fold" => 2,
	"Triple Fold" => 3
];
$formula_g_per_10ml = [
	"FDA" => 1,
	"BUDGET" => 1.05,
    "COOP" => 1.198
];
// ml per g displacement rough estimate is from observation
$approx_ml_ofVanillaDisplacementPer_g = 2;

// Common Initialized Defaults (Advanced Options)
$defaultStrengthFactorName = "Single Fold";
$strengthFactorName = $defaultStrengthFactorName;
$defaultStrengthFactor = 1;
$strengthFactor = $defaultStrengthFactor;
$defaultFormula = "COOP";
$formula = $defaultFormula;

// Initialized Defaults for "by Container"
$defaultContainerAmount = "750";
$defaultContainerUnit = "ml";
$containerUnit = $defaultContainerUnit;

// Initialized Defaults for "by Alcohol Amount"
$defaultAlcoholAmount = "750";
$defaultAlcoholUnit = "ml";
$alcoholUnit = $defaultAlcoholUnit;

// Initialized Defaults for "by Vanilla Amount"
$defaultVanillaAmount = "4";
$defaultVanillaUnit = "oz";
$vanillaUnit = $defaultVanillaUnit;

// Set calculator
$defaultCalculator = "byContainerSize";
$currentCalculator = $defaultCalculator;
if (isset($_GET["byAlcoholAmount"])) {
	$currentCalculator = "byAlcoholAmount";
}
if (isset($_GET["byVanillaAmount"])) {
	$currentCalculator = "byVanillaAmount";
}

// Set Formula
if (isset($_GET['formula'])) {
	// FDA (1g to 10ml) or Co-op (1.198) Formula
	if (in_array($_GET['formula'], array_keys($formula_g_per_10ml))) {
		$g_per_10ml = $formula_g_per_10ml[$_GET['formula']];
		$formula = $_GET['formula'];
	}	
}

// Set Strength Factor
if (isset($_GET['strengthFactorName'])) {
	// single fold, double fold, triple fold
	if (in_array($_GET['strengthFactorName'], array_keys($strengthFactors))) {
		$strengthFactor = $strengthFactors[$_GET['strengthFactorName']];
		$strengthFactorName = $_GET['strengthFactorName'];
	}	
}

// Calculate "by Container" volume
if (isset($_GET['containerAmount'])) {
	
	// Get and sanitize user input
	$containerAmount = floatval($_GET['containerAmount']);
	$defaultContainerAmount = $containerAmount;
	if (in_array($_GET['containerUnit'], array_keys($ml_per))) {
		$containerUnit = $_GET['containerUnit'];
	}
	
	// Convert container amount into ml
	$containerAmount_ml = $containerAmount * $ml_per[$containerUnit];
	
	// Calculate "vanilla displacement unit"
	// This is 10mg of alcohol + the approx ml displacement for 
	// however many grams of vanilla are necessary for this strength
	// example: singlefold FDA extract is 1 g to 10ml alcohol
	// 1g = 2ml vanilla displacement
	// 10ml alcohol + 2ml 
	$singlefold_g_ofVanillaPer_10ml = $g_per_10ml; 
	$g_ofVanillaPer_10ml = $singlefold_g_ofVanillaPer_10ml * $strengthFactor;
	
	$vanillaDisplacementPer_10mg = $g_ofVanillaPer_10ml * $approx_ml_ofVanillaDisplacementPer_g;
	$vanillaDisplacementUnit = 10 + $vanillaDisplacementPer_10mg;
	
	// Divide each "vanilla displacement unit" into total ml to get the correct ratio
	$numberOfVanillaDisplacementUnits = round($containerAmount_ml / $vanillaDisplacementUnit, 2);
	
	// get alcohol ml
	$alcohol_ml = $numberOfVanillaDisplacementUnits * 10; // ml
	// convert alcohol amount back into original unit
	$alcoholInContainerUnit = round($alcohol_ml * $per_ml[$containerUnit], 2);
	
	$vanilla_ml = $numberOfVanillaDisplacementUnits * $vanillaDisplacementPer_10mg;
	$vanilla_g = $numberOfVanillaDisplacementUnits * $g_ofVanillaPer_10ml;
	$vanilla_oz = round($vanilla_g * $oz_per_g, 2);
}





// Calculate "by Alcohol Amount"
if (isset($_GET['alcoholAmount'])) {
	
	// Get and sanitize user input
	$alcoholAmount = floatval($_GET['alcoholAmount']);
	$defaultAlcoholAmount = $alcoholAmount;
	if (in_array($_GET['alcoholUnit'], array_keys($ml_per))) {
		$alcoholUnit = $_GET['alcoholUnit'];
		$defaultAlcoholUnit = $alcoholUnit;
	}
	// Convert alcohol amount into ml
	$alcoholAmount_ml = $alcoholAmount * $ml_per[$alcoholUnit];
	// Get vanilla
	$singlefold_g_ofVanillaPer_10ml = $g_per_10ml; 
	$g_ofVanillaPer_10ml = $singlefold_g_ofVanillaPer_10ml * $strengthFactor;	
	$g_ofVanilla = ($alcoholAmount_ml / 10) * $g_ofVanillaPer_10ml;
	
	// Calculate oz and round
	$roundedVanilla_oz = round($oz_per_g * $g_ofVanilla, 1);
	$roundedVanilla_g = round($g_ofVanilla);
}











// Calculate "by Vanilla Amount" weight
if (isset($_GET['vanillaAmount'])) {
	
	// Get and sanitize user input
	$vanillaAmount = floatval($_GET['vanillaAmount']);
	$defaultVanillaAmount = $vanillaAmount;
	if (in_array($_GET['vanillaUnit'], ["g", "oz"])) {
		$vanillaUnit = $_GET['vanillaUnit'];
		$defaultVanillaUnit = $vanillaUnit;
	}
	// convert to grams	if oz
	$g_ofVanilla = $vanillaAmount;
	if ($vanillaUnit == "oz") {
		$g_ofVanilla = $vanillaAmount * $g_per_oz;
	}
	
	// calculate g per 10ml for formula and strength
	$singlefold_g_ofVanillaPer_10ml = $g_per_10ml; 
	$g_ofVanillaPer_10ml = $singlefold_g_ofVanillaPer_10ml * $strengthFactor;

	// calculate the correct ml of alcohol
	$alcohol_ml_forVanilla = $g_ofVanilla / $g_ofVanillaPer_10ml * 10;

	// convert to each unit	
	$alcohol_forVanilla = [];
	foreach ($per_ml as $unit => $conversion) {
		$alcohol_forVanilla[$unit] = round($alcohol_ml_forVanilla * $conversion, 2);
	}
}






function selected_if($current, $selected) {
	if ($current == $selected) {
		print(" selected ");
	}
}
?><html>
<head>
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-LFQ9EQV3K2"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-LFQ9EQV3K2');
</script>

<title>Vanilla Extract Calculator</title></head>

<script>
window.onload = function() {
	var toolTipLink = document.getElementById("advancedToolTipLink");
	toolTipLink.addEventListener("click", function(e){
	  var x = document.getElementById("advancedToolTip");
	  if (x.style.display === "none") {
		x.style.display = "block";
	  } else {
		x.style.display = "none";
	  }
	},false);
}
</script>

<meta name="viewport" content="width=device-width, initial-scale=0.8">

<style>
@import url('https://fonts.googleapis.com/css2?family=Noto+Sans+JP&display=swap');
@import url('https://fonts.googleapis.com/css2?family=Roboto&display=swap');
body {
	font-family: 'Roboto', sans-serif;
	margin: 1em;
}
nav#main {
	display: flex;
	justify-content: flex-start;
	
	margin-bottom: 2em;
	
	font-size: 1.2em;
	font-family: 'Noto Sans JP', cursive;
}
nav#main div {
	font-size: 1.2em;
	padding: .3em;
}
nav#main a {
	border: 1px solid #777;
	background: #ccc;
	padding: .3em;
	margin: .1em;;
	
	font-size: 1.2em;
	font-family: 'Noto Sans JP', cursive;
	text-decoration: none;
	color: #777;

}
nav#main a.current {
	background: white;
	color: black;
} 
h1,h2,h3 {
	font-family: 'Noto Sans JP', cursive;
}


#containerAmount,#alcoholAmount,#vanillaAmount {
	font-family: monospace;
	width: 4em;
	font-size: 4em;
}
#containerVisual {
	position: relative;
	text-align: center;
	margin: 1em;
	width: 14em;
}
#containerVisualLabel {
	margin: 1em;
	font-size: 1.1em;
	text-align: center;
}
#containerVisualBottle {
	position: relative;
	text-align: center;
	height: 17em;
	border: 5px solid black;
	border-top-width: 1px;
	border-radius: 10px;
}
#containerVisual div.alcohol {
	padding-top: 1em;
	font-size: 1.1em;
}
#containerVisualVanilla {
	position: absolute;
	height: 7em;
	bottom: 0;
	right: 0;
	left: 0;
	background: #2F1009;
	color: white;
}
#containerVisualVanilla div.vanilla {
	padding-top: 1em;
	font-size: 1.1em;
}
select {
	font-size: 1.4em;
}
input[type=button], input[type=submit], input[type=reset] {
	font-size: 1.4em;
}
fieldset {
	opacity: .6;
	border: 0;
	margin: 0;
	padding:0;
}
legend {
	font-size: 1.4em;
	margin-bottom: .2em;
}
label {
	display: block;
}
section#advancedToolTip {
	border: 1px dotted #ccc;
	padding: 1em;
	margin-right: 4em;
	
	font-size: .9em;
	line-height: 130%;

}
section#advancedToolTip h1 {
	
}
section#advancedToolTip p {
	
}
table.results td{
	border: 1px solid #ccc;
	border-bottom-color: black;
	padding: .2em;
}
p#disclaimer {
	margin-top: 4em;
	max-width: 40em;
}
</style>
<body>

<h1>Vanilla Extract Calculator</h1>





<?php if($currentCalculator == "byContainerSize"): ?>
<nav id="main">
<div>Calculate by </div>
<a href="./?byContainerSize" class="current">Container Size</a>
<a href="./?byAlcoholAmount">Alcohol Amount</a>
<a href="./?byVanillaAmount">Vanilla Amount</a>
</nav>

<section id="byContainerSize">

	<section id="containerSizeForm">
		<form>
		<label>The container I want to use has a capacity of:</label>
		<input type="hidden" name="byContainerSize">
		<input type="text" name="containerAmount" id="containerAmount" 
			   value="<?php print($defaultContainerAmount); ?>">
		<select name="containerUnit" id="containerUnit">
		  <option value="ml" <?php selected_if("ml", $containerUnit); ?> >ml</option>
		  <option value="oz" <?php selected_if("oz", $containerUnit); ?> >fluid oz</option>
		  <option value="cups" <?php selected_if("cups", $containerUnit); ?> >cups</option>
		  <option value="pints" <?php selected_if("pints", $containerUnit); ?> >pints</option>
		  <option value="quarts" <?php selected_if("quarts", $containerUnit); ?> >quarts</option>
		  <option value="liters" <?php selected_if("liters", $containerUnit); ?> >liters</option>
		  <option value="gallons" <?php selected_if("gallons", $containerUnit); ?> >gallons</option>
		</select>
		<input type="submit" value="Calculate">
		
		<br><br>
		
		<fieldset>
		<legend>Advanced Options <span><a id="advancedToolTipLink" href="#advancedHelp">Help</a></span></legend>
		<section id="advancedToolTip" style="display: none;">
			<h1>What are the "folds" and "formulas"?</h1>
			<p>
				Vanilla extract is made by soaking vanilla beans in 70-100 proof alcohol for 4-12 months.
				How many vanilla beans you use depends on a few things.  The formulas are guidelines for
				how much to use.
			</p>
			<p>
				<strong>The FDA Formula</strong> is a legal formula created by the government for commercial
				vanilla producers using Grade B vanilla beans.  They say that if you use less than 1 gram of
				vanilla beans for 10ml of alcohol you can't legally call it vanilla extract.  The Co-Op considers
				it a good starting point.
			</p>
			<p>
				<strong>The Co-Op Formula</strong> is based on the FDA rule but is 20% stronger.  It was made
				to make conversion simpler for home cooks and Co-op members and is based on 1oz. of beans to 
				1 cup of alcohol.  It also accounts for the fact that the Grade A beans the Co-Op uses are 
				a little heavier, and also makes a richer extract a little more quickly.
			</p>
			<p>
				<strong>The Budget Formula</strong> is what you can use if you're trying to get the most extract possible
				from your Grade A vanilla beans.  This formula is 5% more vanilla beans than the FDA formula to account
				for using the heavier Grade A vanilla beans the Co-Op uses.
			</p>	
			<p>
				<strong>Single Fold</strong> is just regular strength vanilla.  When baking recipes call for 1 teaspoon
				of vanilla extract, they mean single fold.  Single fold is what's sold commercially.
			</p>
			<p>
				<strong>Double and Triple Fold</strong> just mean double or triple strength vanilla extract.  
				The only reason to use these is for baking recipes.  It allows you to get double or triple the
				vanilla flavor with less liquid.  Or, some people are just crazy about vanilla flavor and like
				the strongest extract possible.
			</p>				
		</section>
		<select name="strengthFactorName">
			<option value="Single Fold" <?php selected_if("Single Fold", $strengthFactorName); ?> >Single Fold</option>
			<option value="One-and-a-half Fold" <?php selected_if("One-and-a-half Fold", $strengthFactorName); ?> >One-and-a-half Fold</option>
			<option value="Double Fold" <?php selected_if("Double Fold", $strengthFactorName); ?> >Double Fold</option>
			<option value="Triple Fold" <?php selected_if("Triple Fold", $strengthFactorName); ?> >Triple Fold</option>
		</select>
		
		<select name="formula">
			<option value="COOP"<?php selected_if("COOP", $formula); ?> >Co-Op Formula</option>
			<option value="BUDGET"<?php selected_if("BUDGET", $formula); ?> >Budget Formula</option>
			<option value="FDA" <?php selected_if("FDA", $formula); ?> >FDA Formula</option>
		</select>		
		</fieldset>
		</form>
	</section>
	
	<?php if ($containerAmount > 0): ?>
	<section id="containerSizeResult">
		
		<h2>Results</h2>
		<div><?php print("$formula strength $strengthFactorName (X$strengthFactor)"); ?></div>
		
		<div id="containerVisual">
		
			<div id="containerVisualLabel">
			Container Total<br>
			<strong><?php print("$containerAmount $containerUnit"); ?></strong><br>
			<!--<strong>(<?php print("$containerAmount_ml ml"); ?>)</strong>-->
			</div>
		
			<div id="containerVisualBottle">
		
				<div class="alcohol">
				alcohol<br>
				<strong><?php print("$alcoholInContainerUnit $containerUnit"); ?></strong><br>
				<!--<strong>(<?php print("$alcohol_ml ml"); ?>)</strong>-->
				</div>
				<div id="containerVisualVanilla">
					<div class="vanilla">vanilla <br><strong><?php print("$vanilla_oz oz."); ?></strong></div>
				</div>
		
			</div>
		</div>
	
	
		<h3>Details:</h3>
<pre>
To fill a container with a capacity of <?php print("$containerAmount_ml ml"); ?>

Use at least <?php print("$vanilla_g g / $vanilla_oz oz"); ?> of vanilla beans
Which will take up <?php print("$vanilla_ml ml"); ?> of space
Then fill the rest of the container with about <?php print("$alcohol_ml ml"); ?> of alcohol
</pre>
		
	</section>
	<?php endif ?>
	
</section>
<?php endif ?>









<?php if($currentCalculator == "byAlcoholAmount"): ?>
<nav id="main">
<div>Calculate by </div>
<a href="./?byContainerSize">Container Size</a>
<a href="./?byAlcoholAmount" class="current">Alcohol Amount</a>
<a href="./?byVanillaAmount">Vanilla Amount</a>
</nav>

<section id="byAlcoholAmount">

	<section id="alcoholAmountForm">
		<form>
		<label>The amount of alcohol I have:</label>
		<input type="hidden" name="byAlcoholAmount">
		
		<input type="text" name="alcoholAmount" id="alcoholAmount" 
			   value="<?php print($defaultAlcoholAmount); ?>">
		<select name="alcoholUnit" id="alcoholUnit">
		  <option value="ml" <?php selected_if("ml", $alcoholUnit); ?> >ml</option>
		  <option value="oz" <?php selected_if("oz", $alcoholUnit); ?> >fluid oz</option>
		  <option value="cups" <?php selected_if("cups", $alcoholUnit); ?> >cups</option>
		  <option value="pints" <?php selected_if("pints", $alcoholUnit); ?> >pints</option>
		  <option value="quarts" <?php selected_if("quarts", $alcoholUnit); ?> >quarts</option>
		  <option value="liters" <?php selected_if("liters", $alcoholUnit); ?> >liters</option>
		  <option value="gallons" <?php selected_if("gallons", $alcoholUnit); ?> >gallons</option>
		</select>
		<input type="submit" value="Calculate">
		
		<br><br>
		
		<fieldset>
		<legend>Advanced Options <span><a id="advancedToolTipLink" href="#advancedHelp">Help</a></span></legend>
		<section id="advancedToolTip" style="display: none;">
			<h1>What are the "folds" and "formulas"?</h1>
			<p>
				Vanilla extract is made by soaking vanilla beans in 70-100 proof alcohol for 4-12 months.
				How many vanilla beans you use depends on a few things.  The formulas are guidelines for
				how much to use.
			</p>
			<p>
				<strong>The FDA Formula</strong> is a legal formula created by the government for commercial
				vanilla producers using Grade B vanilla beans.  They say that if you use less than 1 gram of
				vanilla beans for 10ml of alcohol you can't legally call it vanilla extract.  The Co-Op considers
				it a good starting point.
			</p>
			<p>
				<strong>The Co-Op Formula</strong> is based on the FDA rule but is 20% stronger.  It was made
				to make conversion simpler for home cooks and Co-op members and is based on 1oz. of beans to 
				1 cup of alcohol.  It also accounts for the fact that the Grade A beans the Co-Op uses are 
				a little heavier, and also makes a richer extract a little more quickly.
			</p>
			<p>
				<strong>The Budget Formula</strong> is what you can use if you're trying to get the most extract possible
				from your Grade A vanilla beans.  This formula is 5% more vanilla beans than the FDA formula to account
				for using the heavier Grade A vanilla beans the Co-Op uses.
			</p>	
			<p>
				<strong>Single Fold</strong> is just regular strength vanilla.  When baking recipes call for 1 teaspoon
				of vanilla extract, they mean single fold.  Single fold is what's sold commercially.
			</p>
			<p>
				<strong>Double and Triple Fold</strong> just mean double or triple strength vanilla extract.  
				The only reason to use these is for baking recipes.  It allows you to get double or triple the
				vanilla flavor with less liquid.  Or, some people are just crazy about vanilla flavor and like
				the strongest extract possible.
			</p>				
		</section>		
		<select name="strengthFactorName">
			<option value="Single Fold" <?php selected_if("Single Fold", $strengthFactorName); ?> >Single Fold</option>
			<option value="One-and-a-half Fold" <?php selected_if("One-and-a-half Fold", $strengthFactorName); ?> >One-and-a-half Fold</option>
			<option value="Double Fold" <?php selected_if("Double Fold", $strengthFactorName); ?> >Double Fold</option>
			<option value="Triple Fold" <?php selected_if("Triple Fold", $strengthFactorName); ?> >Triple Fold</option>
		</select>
		<select name="formula">
			<option value="COOP"<?php selected_if("COOP", $formula); ?> >Co-Op Formula</option>
			<option value="BUDGET"<?php selected_if("BUDGET", $formula); ?> >Budget Formula</option>
			<option value="FDA" <?php selected_if("FDA", $formula); ?> >FDA Formula</option>
		</select>		
		</fieldset>
		</form>
	</section>
	
	<?php if ($roundedVanilla_g > 0): ?>
	<section id="containerSizeResult">
		
		<h2>Results</h2>
		<div><?php print("$formula strength $strengthFactorName (X$strengthFactor)"); ?></div>
	
		<h1>Use at least <?php print("$roundedVanilla_oz oz of vanilla beans"); ?></h1>
		
		<p><?php print("$roundedVanilla_oz oz is $roundedVanilla_g grams of vanilla beans"); ?></p>
		
	</section>
	<?php endif ?>
	
</section>
<?php endif ?>










<?php if($currentCalculator == "byVanillaAmount"): ?>
<nav id="main">
<div>Calculate by </div>
<a href="./?byContainerSize">Container Size</a>
<a href="./?byAlcoholAmount">Alcohol Amount</a>
<a href="./?byVanillaAmount" class="current">Vanilla Amount</a>
</nav>

<section id="byVanillaAmount">

	<section id="vanillaAmountForm">
		<form>
		<label>The amount of vanilla I have:</label>
		<input type="hidden" name="byVanillaAmount">

		<input type="text" name="vanillaAmount" id="vanillaAmount" 
			   value="<?php print($defaultVanillaAmount); ?>">
		<select name="vanillaUnit" id="vanillaUnit">
		  <option value="g" <?php selected_if("g", $vanillaUnit); ?> >g</option>
		  <option value="oz" <?php selected_if("oz", $vanillaUnit); ?> >oz</option>
		</select>
		<input type="submit" value="Calculate">		
		
		<br><br>
		
		<fieldset>
		<legend>Advanced Options <span><a id="advancedToolTipLink" href="#advancedHelp">Help</a></span></legend>
		<section id="advancedToolTip" style="display: none;">
			<h1>What are the "folds" and "formulas"?</h1>
			<p>
				Vanilla extract is made by soaking vanilla beans in 70-100 proof alcohol for 4-12 months.
				How many vanilla beans you use depends on a few things.  The formulas are guidelines for
				how much to use.
			</p>
			<p>
				<strong>The FDA Formula</strong> is a legal formula created by the government for commercial
				vanilla producers using Grade B vanilla beans.  They say that if you use less than 1 gram of
				vanilla beans for 10ml of alcohol you can't legally call it vanilla extract.  The Co-Op considers
				it a good starting point.
			</p>
			<p>
				<strong>The Co-Op Formula</strong> is based on the FDA rule but is 20% stronger.  It was made
				to make conversion simpler for home cooks and Co-op members and is based on 1oz. of beans to 
				1 cup of alcohol.  It also accounts for the fact that the Grade A beans the Co-Op uses are 
				a little heavier, and also makes a richer extract a little more quickly.
			</p>
			<p>
				<strong>The Budget Formula</strong> is what you can use if you're trying to get the most extract possible
				from your Grade A vanilla beans.  This formula is 5% more vanilla beans than the FDA formula to account
				for using the heavier Grade A vanilla beans the Co-Op uses.
			</p>	
			<p>
				<strong>Single Fold</strong> is just regular strength vanilla.  When baking recipes call for 1 teaspoon
				of vanilla extract, they mean single fold.  Single fold is what's sold commercially.
			</p>
			<p>
				<strong>Double and Triple Fold</strong> just mean double or triple strength vanilla extract.  
				The only reason to use these is for baking recipes.  It allows you to get double or triple the
				vanilla flavor with less liquid.  Or, some people are just crazy about vanilla flavor and like
				the strongest extract possible.
			</p>				
		</section>		
		<select name="strengthFactorName">
			<option value="Single Fold" <?php selected_if("Single Fold", $strengthFactorName); ?> >Single Fold</option>
			<option value="One-and-a-half Fold" <?php selected_if("Double Fold", $strengthFactorName); ?> >One-and-a-half Fold</option>
			<option value="Double Fold" <?php selected_if("Double Fold", $strengthFactorName); ?> >Double Fold</option>
			<option value="Triple Fold" <?php selected_if("Triple Fold", $strengthFactorName); ?> >Triple Fold</option>
		</select>
		<select name="formula">
			<option value="COOP"<?php selected_if("COOP", $formula); ?> >Co-Op Formula</option>
			<option value="BUDGET"<?php selected_if("BUDGET", $formula); ?> >Budget Formula</option>
			<option value="FDA" <?php selected_if("FDA", $formula); ?> >FDA Formula</option>
		</select>		
		</fieldset>
		</form>
	</section>
	
	<?php if (is_array($alcohol_forVanilla)): ?>
	<section id="containerSizeResult">
		
		<h2>Results</h2>
		
		<div><?php print("$formula strength $strengthFactorName (X$strengthFactor)"); ?></div>
		
		<h3>Use up to this amount of 80-100 proof alcohol:</h3>
		
		<table class="results">
		<?php foreach ($alcohol_forVanilla as $unit => $amount): ?>
			
			<?php if ($amount > .1): ?>
			<tr><?php print("<td>$amount</td><td>$unit</td>"); ?></tr>
			<?php endif ?>
			
		<?php endforeach ?>
		</table>
	</section>
	<?php endif ?>
	
</section>
<?php endif ?>


<hr>
<p id="disclaimer">
I'm Daniel Talsky and I made this tool to help <!-- <a href="https://www.facebook.com/groups/848812811970139">Vanilla Bean Co-Op Facebook Group</a> members--> people calculate vanilla extract easily.  It comes with no warranty of any kind and is here for convenience.  I can't guarantee I can help you but if you have questions feel free to email me at danieltalsky@gmail.com.
</p>
</body>
</html>