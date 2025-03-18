<?php
$onSimulation = false;
$number_of_days = "";
$averageClimateFactor = 0;
$simulationResults = []; // Array para almacenar los resultados diarios

// Parámetros de entrada
$actual_grass = "";
$grass_growth_base_rate = "";
$maximun_grass_capacity = "";

$herbivores = "";
$herbivores_consumption = "";
$herbivores_reproduction_rate = "";

$carnivores = "";
$carnivores_hunting_rate = "";
$carnivores_food_reproduction_rate = "";
$carnivores_food_requeriment = "";

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
}

function setVariables()
{
    global $number_of_days;
    global $actual_grass, $grass_growth_base_rate, $maximun_grass_capacity; // Parámetros de la hierba
    global $herbivores, $herbivores_consumption, $herbivores_reproduction_rate; // Parámetros de herbívoros
    global $carnivores, $carnivores_hunting_rate, $carnivores_food_reproduction_rate, $carnivores_food_requeriment; // Parámetros de carnívoros

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
    $actual_grass, // Parámetros de la hierba
    $maximun_grass_capacity,
    $herbivores, // Parámetros de herbívoros
    $herbivores_consumption,
    $herbivores_reproduction_rate,
    $carnivores, // Parámetros de carnívoros
    $carnivores_hunting_rate,
    $carnivores_food_reproduction_rate,
    $carnivores_food_requeriment
) {
    global $number_of_days, $grass_growth_base_rate, $averageClimateFactor, $simulationResults;

    // Convertir porcentajes a decimales
    $herbivores_reproduction_rate /= 100;
    $carnivores_food_reproduction_rate /= 100;

    $day = 1;
    while (
        $day <= $number_of_days &&
        intval($actual_grass) > 0 &&
        intval($herbivores) > 0 &&
        intval($carnivores) > 0
    ) {
        // Cálculo diario del factor climático
        $climate_factor = rand(70, 130) / 100;
        $grass_growth_final_value = ($grass_growth_base_rate / 100) * $climate_factor;
        $averageClimateFactor += $climate_factor;

        // Cálculo de la hierba
        $actual_grass = $actual_grass + ($grass_growth_final_value * $actual_grass);
        $actual_grass = min($actual_grass, $maximun_grass_capacity);

        // Cálculo de herbívoros
        $total_consumption = $herbivores * $herbivores_consumption;
        $actual_grass = max(0, $actual_grass - $total_consumption);
        $herbivoresReproduction = $herbivores * $herbivores_reproduction_rate * ($actual_grass / $maximun_grass_capacity);
        $huntedHerbivores = min($herbivores, $carnivores * $carnivores_hunting_rate);
        $herbivores = $herbivores + $herbivoresReproduction - $huntedHerbivores;

        // Cálculo de carnívoros
        $carnivoresReproduction = $huntedHerbivores * $carnivores_food_reproduction_rate;
        $deadCarnivores = max(0, $carnivores * (1 - ($huntedHerbivores / ($carnivores * $carnivores_food_requeriment))));
        $carnivores = $carnivores + $carnivoresReproduction - $deadCarnivores;

        // Almacenar los resultados del día en el array
        $simulationResults[] = [
            "day" => $day,
            "grass" => ($actual_grass),
            "grassConsumption" => ($total_consumption),
            "climateFactor" => ($climate_factor),
            "herbivores" => ($herbivores),
            "herbivoresHunted" => ($huntedHerbivores),
            "herbivoresReproduction" => ($herbivoresReproduction),
            "carnivores" => ($carnivores),
            "carnivoresDead" => ($deadCarnivores),
            "carnivoresReproduction" => ($carnivoresReproduction)
        ];
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
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
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
                        <!-- World Parameters -->
                        <div class="col-span-1 md:col-span-2">
                            <h3 class="text-sky-400 font-semibold text-lg mb-3 border-b border-gray-700 pb-2">World Parameters</h3>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-gray-300 font-medium" for="number_of_days">Number of days:</label>
                            <input type="number" id="number_of_days" name="number_of_days" value="<?= $number_of_days ?>"
                                class="w-full bg-gray-900 border border-gray-700 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-gray-300 font-medium" for="climate_factor">Average climate factor (random):</label>
                            <input type="number" id="climate_factor" name="climate_factor" value="<?= $onSimulation ? ($averageClimateFactor / count($simulationResults)) : "" ?>"
                                class="w-full bg-gray-900 border border-gray-700 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent" readonly>
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
                            <input type="number" step="0.01" id="grass_growth_base_rate" name="grass_growth_base_rate" value="<?= $grass_growth_base_rate ?>"
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
                            <input type="number" step="0.01" id="herbivores_consumption" name="herbivores_consumption" value="<?= $herbivores_consumption ?>"
                                class="w-full bg-gray-900 border border-gray-700 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-gray-300 font-medium" for="herbivores_reproduction_rate">Herbivores Reproduction Rate (%):</label>
                            <input type="number" step="0.01" id="herbivores_reproduction_rate" name="herbivores_reproduction_rate" value="<?= $herbivores_reproduction_rate ?>"
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
                            <label class="block text-gray-300 font-medium" for="carnivores_hunting_rate">Carnivores Hunting Efficiency:</label>
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

            <article class="<?= $onSimulation ? "w-6/7 mx-auto bg-gray-800/50 rounded-xl p-6 shadow-lg border border-gray-700 overflow-auto" : "" ?>">
                <?php if ($onSimulation): ?>
                    <table class="min-w-full divide-y divide-gray-700 text-sm">
                        <thead>
                            <tr class="text-center">
                                <th class="px-4 py-2">Day</th>
                                <th class="px-4 py-2">Grass</th>
                                <th class="px-4 py-2">Grass Consumption</th>
                                <th class="px-4 py-2">Climate Factor</th>
                                <th class="px-4 py-2">Herbivores</th>
                                <th class="px-4 py-2">Herbivores Hunted</th>
                                <th class="px-4 py-2">Herbivores Reproduction</th>
                                <th class="px-4 py-2">Carnivores</th>
                                <th class="px-4 py-2">Carnivores Dead</th>
                                <th class="px-4 py-2">Carnivores Reproduction</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($simulationResults as $row): ?>
                                <tr class="hover:bg-gray-700/30 transition-colors text-center">
                                    <td class="px-4 py-2"><?= number_format($row['day'], 0) ?></td>
                                    <td class="px-4 py-2 bg-gray-700"><?= number_format($row['grass'], 2) ?></td>
                                    <td class="px-4 py-2"><?= number_format($row['grassConsumption'], 2) ?></td>
                                    <td class="px-4 py-2"><?= number_format($row['climateFactor'], 2) ?></td>
                                    <td class="px-4 py-2 bg-gray-700"><?= number_format($row['herbivores'], 0) ?></td>
                                    <td class="px-4 py-2"><?= number_format($row['herbivoresHunted'], 2) ?></td>
                                    <td class="px-4 py-2"><?= number_format($row['herbivoresReproduction'], 2) ?></td>
                                    <td class="px-4 py-2 bg-gray-700"><?= number_format($row['carnivores'], 0) ?></td>
                                    <td class="px-4 py-2"><?= number_format($row['carnivoresDead'], 2) ?></td>
                                    <td class="px-4 py-2"><?= number_format($row['carnivoresReproduction'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </article>
        </section>
    </main>
</body>

</html>