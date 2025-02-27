# Grazing Ecosystem

Develop a simulator that models the growth and consumption of resources in a grazing ecosystem using only scalar variables (without using data structures such as arrays, lists, tuples, or dictionaries).

This academic project aims to learn PHP and is for the first exam of Programming Languages at the University of Santander (UDES), developed by Santiago Ospina.

## Problem Description

In a simplified ecosystem, there are three main components:
1. **Grass** (renewable resource)
2. **Herbivores** (primary consumers)
3. **Predators** (secondary consumers)

The system operates with the following rules:
- Grass grows at a variable rate depending on climatic factors.
- Herbivores reproduce based on the amount of available food.
- Herbivores die from old age and predation.
- Predators reproduce based on the number of herbivores captured.
- Predators die from old age and lack of food.

### Specific Equations and Calculations

#### Grass Growth:
- `New_Grass = Current_Grass + (Growth_Rate * Current_Grass)`
- Where `Growth_Rate = Base_Rate * Climate_Factor`
- `Climate_Factor` is a random value between 0.7 and 1.3

#### Herbivore Dynamics:
- `Total_Consumption = Number_of_Herbivores * Individual_Consumption`
- `Grass_After_Consumption = max(0, Current_Grass - Total_Consumption)`
- `Herbivore_Reproduction = (Number_of_Herbivores * Reproduction_Rate * (Current_Grass / Maximum_Grass))`
- `Herbivores_Caught = min(Number_of_Herbivores, Number_of_Predators * Hunting_Efficiency)`
- `New_Number_of_Herbivores = Number_of_Herbivores + Herbivore_Reproduction - Herbivores_Caught`

#### Predator Dynamics:
- `Predator_Reproduction = Herbivores_Caught * Food_Conversion_Rate`
- `Predator_Deaths = Number_of_Predators * (1 - (Herbivores_Caught / (Number_of_Predators * Food_Need)))`
- `New_Number_of_Predators = Number_of_Predators + Predator_Reproduction - Predator_Deaths`

## Functional Requirements

1. **Initialization**: The user must input:
    - Initial amount of grass (units)
    - Base growth rate of grass (0.1 = 10% daily)
    - Maximum grass capacity (ecosystem capacity)
    - Initial number of herbivores
    - Individual consumption of each herbivore
    - Herbivore reproduction rate (0.05 = 5% daily)
    - Initial number of predators
    - Hunting efficiency (how many herbivores each predator can catch)
    - Food-to-reproduction conversion rate (0.2 = 20% of prey converts to new predators)
    - Food requirement (how many herbivores each predator needs to catch to maintain its population)

2. **Simulation**: Simulate the day-to-day evolution of the ecosystem over a specified number of days.

3. **Output**: Display the final state of the ecosystem and indicate if any extinction occurred.

## Technical Constraints

- Use only scalar variables (do not use data structures such as arrays, lists, tuples, or dictionaries)
- Do not use additional libraries for calculations
- Properly implement conditional control structures and loops
- Include explanatory comments in the code
