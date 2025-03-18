import random

def set_variables():

    grass = float(input("Cantidad inicial de pasto: "))
    base_growth_rate = float(input("Tasa de crecimiento base del pasto (por ejemplo, 0.1 para 10%): "))
    max_grass = float(input("Pasto máximo que puede existir: "))
    
    herbivores = int(input("Número inicial de herbívoros: "))
    herb_consumption = float(input("Consumo individual de cada herbívoro: "))
    herb_reproduction_rate = float(input("Tasa de reproducción de herbívoros (ej. 0.05 para 5%): "))
    
    predators = int(input("Número inicial de depredadores: "))
    hunting_efficiency = float(input("Eficiencia de caza (número de herbívoros que puede cazar cada depredador): "))
    conversion_rate = float(input("Tasa de conversión de alimento a reproducción (ej. 0.2 para 20%): "))
    food_need = float(input("Necesidad de alimento (número de herbívoros que necesita cazar cada depredador para mantenerse): "))
    
    number_of_days = int(input("Número de días a simular: "))
    
    return (grass, base_growth_rate, max_grass,
            herbivores, herb_consumption, herb_reproduction_rate,
            predators, hunting_efficiency, conversion_rate, food_need,
            number_of_days)

def start_simulation(grass, base_growth_rate, max_grass,
                     herbivores, herb_consumption, herb_reproduction_rate,
                     predators, hunting_efficiency, conversion_rate, food_need,
                     number_of_days):
    simulation_results = []
    day = 0
    
    while day < number_of_days and int(grass) > 0 and int(herbivores) > 0 and int(predators) > 0:
        day += 1
        print(f"\nDía {day}:")
        print(f"  Inicio -> Pasto: {grass:.2f}, Herbívoros: {herbivores}, Depredadores: {predators}")
        
        # 1. Pasto
        climate_factor = random.uniform(0.7, 1.3)
        effective_growth_rate = base_growth_rate * climate_factor
        new_grass = grass + (effective_growth_rate * grass)
        if new_grass > max_grass:
            new_grass = max_grass
        
        # 2. Herbívoros
        consumption_total = herbivores * herb_consumption
        grass_after_consumption = new_grass - consumption_total
        if grass_after_consumption < 0:
            grass_after_consumption = 0
        
        reproduction_herbivores = herbivores * herb_reproduction_rate * (grass_after_consumption / max_grass)
        herbivores_hunted = min(herbivores, (predators * hunting_efficiency))
        new_herbivores = herbivores + reproduction_herbivores - herbivores_hunted
        new_herbivores = (new_herbivores) if new_herbivores > 0 else 0
        
        # 3. Depredadores
        reproduction_predators = herbivores_hunted * conversion_rate
        if predators > 0 and (predators * food_need) > 0:
            death_predators = predators * (1 - (herbivores_hunted / (predators * food_need)))
            death_predators = max(0, death_predators)
        else:
            death_predators = predators
        new_predators = predators + reproduction_predators - death_predators
        new_predators = (new_predators) if new_predators > 0 else 0
        
        daily_result = {
            "day": day,
            "initial": {"grass": grass, "herbivores": herbivores, "predators": predators},
            "climate_factor": climate_factor,
            "new_grass": new_grass,
            "grass_after_consumption": grass_after_consumption,
            "consumption_total": consumption_total,
            "herbivores_hunted": herbivores_hunted,
            "reproduction_herbivores": reproduction_herbivores,
            "new_herbivores": new_herbivores,
            "reproduction_predators": reproduction_predators,
            "death_predators": death_predators,
            "new_predators": new_predators,
        }
        simulation_results.append(daily_result)
        
        print(f"  Actualización -> Pasto: {grass_after_consumption:.2f}, Herbívoros: {new_herbivores}, Depredadores: {new_predators}")
        
        grass = grass_after_consumption
        herbivores = new_herbivores
        predators = new_predators
        
        if int(grass) == 0:
            print("         \n\n¡Extinción del pasto!")
            break
        if int(herbivores) == 0:
            print("         \n\n¡Extinción de los herbívoros!")
            break
        if int(predators) == 0:
            print("         \n\n¡Extinción de los depredadores!")
            break
    
    print("\n--- Estado final del ecosistema ---")
    print(f"Pasto: {grass:.2f}")
    print(f"Herbívoros: {herbivores}")
    print(f"Depredadores: {predators}")
    if int(grass) == 0 or int(herbivores) == 0 or int(predators) == 0:
        print("Se produjo al menos una extinción durante la simulación.")
    
    print("\nResultados diarios:")
    for result in simulation_results:
            print(
                f"Día {result['day']:.0f}: "
                f"Pasto: {result['grass_after_consumption']:.2f}, "
                f"Grass Consumption: {result['consumption_total']:.2f}, "
                f"Climate Factor: {result['climate_factor']:.2f}, "
                f"Herbívoros: {result['new_herbivores']:.0f}, "
                f"Herbivores Hunted: {result['herbivores_hunted']:.2f}, "
                f"Herbivores Reproduction: {result['reproduction_herbivores']:.2f}, "
                f"Carnivores: {result['new_predators']:.0f}, "
                f"Carnivores Dead: {result['death_predators']:.2f}, "
                f"Carnivores Reproduction: {result['reproduction_predators']:.2f}"
    )

def main():
    params = set_variables()
    start_simulation(*params)

if __name__ == "__main__":
    main()
