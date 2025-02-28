<?php
$onSimulation = false;
$number_of_days = "";
$averageClimateFactor = 0;

$actual_grass = "";
$grass_growth_base_rate = ""; //%
$maximun_grass_capacity = "";

$herbivores = "";
$herbivores_consumption = "";
$herbivores_reproduction_rate = ""; //%

$carnivores = "";
$carnivores_hunting_rate = ""; //%
$carnivores_food_reproduction_rate = ""; //%
$carnivores_food_requeriment = "";

$RESULTS = "
    <table class='min-w-full divide-y divide-gray-700 text-sm'>
        <thead>
            <tr class='text-center '>
                <th class='px-4 py-2'>Day</th>
                <th class='px-4 py-2'>Grass</th>
                <th class='px-4 py-2'>Grass comsuptiom</th>
                <th class='px-4 py-2'>Climate factor</th>
                <th class='px-4 py-2'>Herbivores</th>
                <th class='px-4 py-2'>Hervivores hunted</th>
                <th class='px-4 py-2'>Hervivores reproduction</th>
                <th class='px-4 py-2'>Carnivores</th>
                <th class='px-4 py-2'>Carnivores Dead</th>
                <th class='px-4 py-2'>Carnivores reproduction</th>
            </tr>
        </thead>
        <tbody>";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    setVariables();
    $onSimulation = true;

    startSimulation(
        $actual_grass,
        $maximun_grass_capacity,
        $herbivores,
        $herbivores_consumption,
        $herbivores_reproduction_rate,
        $carnivores,
        $carnivores_hunting_rate,
        $carnivores_food_reproduction_rate,
        $carnivores_food_requeriment
    );

    $RESULTS .= "</tbody></table>";
}

function setVariables()
{
    global $number_of_days;
    global $actual_grass, $grass_growth_base_rate, $maximun_grass_capacity; //Grass parameters
    global $herbivores, $herbivores_consumption, $herbivores_reproduction_rate; //Herbivores parameters
    global $carnivores, $carnivores_hunting_rate, $carnivores_food_reproduction_rate, $carnivores_food_requeriment; //Carnivores parameters

    $number_of_days = isset($_POST['number_of_days']) ? max(10, intval($_POST['number_of_days'])) : 10;

    $actual_grass = isset($_POST['actual_grass']) ? max(1000, intval($_POST['actual_grass'])) : 1000;
    $grass_growth_base_rate = isset($_POST['grass_growth_base_rate']) ? max(10, floatval($_POST['grass_growth_base_rate'])) : 10;
    $maximun_grass_capacity = isset($_POST['maximun_grass_capacity']) ? max(10000, intval($_POST['maximun_grass_capacity'])) : 10000;

    $herbivores = isset($_POST['herbivores']) ? max(10, intval($_POST['herbivores'])) : 10;
    $herbivores_consumption = isset($_POST['herbivores_consumption']) ? max(5, floatval($_POST['herbivores_consumption'])) : 5;
    $herbivores_reproduction_rate = isset($_POST['herbivores_reproduction_rate']) ? max(10, floatval($_POST['herbivores_reproduction_rate'])) : 10;

    $carnivores = isset($_POST['carnivores']) ? max(2, intval($_POST['carnivores'])) : 2;
    $carnivores_hunting_rate = isset($_POST['carnivores_hunting_rate']) ? max(0.5, floatval($_POST['carnivores_hunting_rate'])) : 0.5;
    $carnivores_food_reproduction_rate = isset($_POST['carnivores_food_reproduction_rate']) ? max(30, floatval($_POST['carnivores_food_reproduction_rate'])) : 30;
    $carnivores_food_requeriment = isset($_POST['carnivores_food_requeriment']) ? max(0.2, floatval($_POST['carnivores_food_requeriment'])) : 0.2;
}

function startSimulation(
    $actual_grass, //Grass parameters
    $maximun_grass_capacity,  
    $herbivores, //Herbivores parameters
    $herbivores_consumption,
    $herbivores_reproduction_rate, 
    $carnivores, //Carnivores parameters
    $carnivores_hunting_rate,
    $carnivores_food_reproduction_rate,
    $carnivores_food_requeriment 
) {
    global $RESULTS, $number_of_days, $grass_growth_base_rate, $climate_factor, $averageClimateFactor, $day;
    $herbivores_reproduction_rate /= 100;
    $carnivores_food_reproduction_rate /= 100;

    $day = 1;
    while (
        $day <= $number_of_days &&
        intval($actual_grass) > 0 &&
        intval($herbivores) > 0 &&
        intval($carnivores) > 0
        ) {
        //Dialy climate factor calculation
        $climate_factor = rand(70, 130) / 100;
        $grass_growth_final_value = ($grass_growth_base_rate / 100) * $climate_factor;
        $averageClimateFactor += $climate_factor;

        //Grass parameters calculation
        $actual_grass = $actual_grass + ($grass_growth_final_value * $actual_grass);
        $actual_grass = min($actual_grass, $maximun_grass_capacity);


        //Herbivores parameters calculation
        $total_comsumption = $herbivores * $herbivores_consumption;
        $actual_grass = (max(0, $actual_grass - $total_comsumption)); //intval
        $herbivoresReproduction = ($herbivores * $herbivores_reproduction_rate * ($actual_grass / $maximun_grass_capacity));
        $huntedHerbivores = min($herbivores, $carnivores * $carnivores_hunting_rate);
        $herbivores = ($herbivores + $herbivoresReproduction - $huntedHerbivores); //intval

        //Carnivores parameters calculation
        $carnivoresReproduction = $huntedHerbivores * $carnivores_food_reproduction_rate;
        $deadCarnivores = $carnivores * (1 - ($huntedHerbivores / ($carnivores * $carnivores_food_requeriment)));
        $carnivores = ($carnivores + $carnivoresReproduction - $deadCarnivores); //intval

        $RESULTS .= "
        <tr class='hover:bg-gray-700/30 transition-colors'>
            <td class='px-4 py-2'>$day</td>
            <td class='px-4 py-2 bg-gray-700'>" . number_format($actual_grass, 2) . "</td>
            <td class='px-4 py-2'>" . number_format($total_comsumption, 2) . "</td>
            <td class='px-4 py-2'>" . number_format($climate_factor, 2) . "</td>
            <td class='px-4 py-2 bg-gray-700'>" . number_format($herbivores, 0) . "</td>
            <td class='px-4 py-2'>" . number_format($huntedHerbivores, 2) . "</td>
            <td class='px-4 py-2'>" . number_format($herbivoresReproduction, 2) . "</td>
            <td class='px-4 py-2 bg-gray-700'>" . number_format($carnivores, 0) . "</td>
            <td class='px-4 py-2'>" . number_format($deadCarnivores, 2) . "</td>
            <td class='px-4 py-2'>" . number_format($carnivoresReproduction, 2) . "</td>
        </tr>";
        $day++;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" type="text/css" href="style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grazing Ecosystem</title>
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script> <!-- Tailwind CSS -->
</head>

<body class="bg-[#010409] text-white min-h-screen py-4 px-4">
    <main class="<?= $onSimulation ? "" : "max-w-5xl" ?> mx-auto">
        <section class="text-center mb-8">
            <h1 class="text-sky-400 font-bold text-4xl mb-2">Grazing Ecosystem</h1>
            <h2 class="text-xl text-gray-300 font-medium">
                <?= $onSimulation ? "Simulation" : "Initial parameters"; ?>
            </h2>
        </section>

        <section class="grid <?= $onSimulation ? "grid-cols-2" : "grid-cols-1"; ?>">
            <article class="w-6/7 mx-auto bg-gray-800/50 rounded-xl p-6 shadow-lg border border-gray-700">
                <form method="post" action="" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">

                        <!-- Simulation Parameters -->
                        <div class="col-span-1 md:col-span-2">
                            <h3 class="text-sky-400 font-semibold text-lg mb-3 border-b border-gray-700 pb-2">World Parameters</h3>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-gray-300 font-medium" for="actual_grass">Number of days:</label>
                            <input type="number" id="number_of_days" name="number_of_days" value="<?= $number_of_days ?>"
                                class="w-full bg-gray-900 border border-gray-700 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                        </div>

                        <div class="space-y-2">
                            <label class="block text-gray-300 font-medium" for="actual_grass">Average climate factor (random):</label>
                            <input type="number" id="climate_factor" name="climate_factor" value="<?= $onSimulation == false ? "" : ($averageClimateFactor / ($day - 1)) ?>"
                                class="w-full bg-gray-900 border border-gray-700 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent"
                                readonly>
                        </div>

                        <!-- Grass Parameters -->
                        <div class="col-span-1 md:col-span-2">
                            <h3 class="text-sky-400 font-semibold text-lg mb-3 border-b border-gray-700 pb-2">Grass Parameters</h3>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-gray-300 font-medium" for="actual_grass">Actual Grass:</label>
                            <input type="number" id="actual_grass" name="actual_grass" value="<?= $actual_grass ?>"
                                class="w-full bg-gray-900 border border-gray-700 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-gray-300 font-medium" for="grass_growth_base_rate">Grass Growth Base Rate (%):</label>
                            <input type="number" id="grass_growth_base_rate" name="grass_growth_base_rate" value="<?= $grass_growth_base_rate ?>"
                                class="w-full bg-gray-900 border border-gray-700 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-gray-300 font-medium" for="maximun_grass_capacity">Maximum Grass Capacity:</label>
                            <input type="number" id="maximun_grass_capacity" name="maximun_grass_capacity" value="<?= $maximun_grass_capacity ?>"
                                class="w-full bg-gray-900 border border-gray-700 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                        </div>

                        <!-- Herbivores Parameters -->
                        <div class="col-span-1 md:col-span-2 mt-2">
                            <h3 class="text-sky-400 font-semibold text-lg mb-3 border-b border-gray-700 pb-2">Herbivores Parameters</h3>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-gray-300 font-medium" for="herbivores">Herbivores:</label>
                            <input type="number" id="herbivores" name="herbivores" value="<?= $herbivores ?>"
                                class="w-full bg-gray-900 border border-gray-700 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-gray-300 font-medium" for="herbivores_consumption">Herbivores Consumption:</label>
                            <input type="number" id="herbivores_consumption" name="herbivores_consumption" value="<?= $herbivores_consumption ?>"
                                class="w-full bg-gray-900 border border-gray-700 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-gray-300 font-medium" for="herbivores_reproduction_rate">Herbivores Reproduction Rate (%):</label>
                            <input type="number" id="herbivores_reproduction_rate" name="herbivores_reproduction_rate" value="<?= $herbivores_reproduction_rate ?>"
                                class="w-full bg-gray-900 border border-gray-700 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                        </div>

                        <!-- Carnivores Parameters -->
                        <div class="col-span-1 md:col-span-2 mt-2">
                            <h3 class="text-sky-400 font-semibold text-lg mb-3 border-b border-gray-700 pb-2">Carnivores Parameters</h3>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-gray-300 font-medium" for="carnivores">Carnivores:</label>
                            <input type="number" id="carnivores" name="carnivores" value="<?= $carnivores ?>"
                                class="w-full bg-gray-900 border border-gray-700 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-gray-300 font-medium" for="carnivores_hunting_rate">Carnivores Hunting efficiency:</label>
                            <input type="number" step="0.01" id="carnivores_hunting_rate" name="carnivores_hunting_rate" value="<?= $carnivores_hunting_rate ?>"
                                class="w-full bg-gray-900 border border-gray-700 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-gray-300 font-medium" for="carnivores_food_reproduction_rate">Carnivores Food Reproduction Rate (%):</label>
                            <input type="number" id="carnivores_food_reproduction_rate" name="carnivores_food_reproduction_rate" value="<?= $carnivores_food_reproduction_rate ?>"
                                class="w-full bg-gray-900 border border-gray-700 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-gray-300 font-medium" for="carnivores_food_requeriment">Carnivores Food Requirement:</label>
                            <input type="number" step="0.01" id="carnivores_food_requeriment" name="carnivores_food_requeriment" value="<?= $carnivores_food_requeriment ?>"
                                class="w-full bg-gray-900 border border-gray-700 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                        </div>
                    </div>

                    <div class="pt-2 text-center">
                        <button type="submit" class="bg-sky-600 hover:bg-sky-700 text-white font-bold py-3 px-8 rounded-md transition-colors duration-200 shadow-lg focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-opacity-50">
                            Run Simulation
                        </button>
                    </div>
                </form>
            </article>

            <article class="<?= $onSimulation == false ? "" :  "w-6/7 mx-auto bg-gray-800/50 rounded-xl p-6 shadow-lg border border-gray-700 overflow-auto" ?>">
                <?= $onSimulation == false ? "" :  $RESULTS ?>
            </article>

        </section>

    </main>
</body>