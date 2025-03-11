<?php

namespace app\modules\v2\modules\visit\services;

use app\models\db\GovServices;
use app\models\db\Visits;
use yii\base\Exception;

class VisitDurationsService
{

    /**
     * @param string $variety
     * @param array  $services
     * @param array  $govServices <int, GovServices>
     * @param int    $countPets
     *
     * @return array ($duration, $cooldown)
     *
     * @throws Exception
     */
    public static function calculated(string $variety, array $services, array $govServices, int $countPets): array
    {
        // рассчитываем duration и cooldown услуг
        $duration = 0;
        $cooldown = 0;
        if ($variety === Visits::VISIT_BROOD) {
            // прием выводка:
            // До 5 голов – 20 мин.
            // От 5 до 10 – 30 мин.
            // От 10 до 15 – 40 мин.
            // Между услугами нет промежутка на отдых.
            // Вместо голов используем количество каждой услуги, так как они закономерно влияют на хронометраж
            $distinguishedServices = self::distinguishServices($services, $govServices, 'for_broods');

            foreach ($distinguishedServices as $serviceVariety => $combinedServices) {
                foreach ($combinedServices as $serviceId => $servicePets) {
                    $govService = $govServices[$serviceId];
                    $count = $servicePets['count'];
                    $serviceCooldown = $govService['cooldown'] ? (int)$govService['cooldown'] : 0;
                    unset($servicePets['count']);

                    switch($serviceVariety) {
                        case GovServices::FOR_HEAD:
                            $duration += (int)$govService['duration'];
                            break;
                        case GovServices::FOR_ALL:
                        case 'NULL':
                            $duration += self::getTimeByCount($count);
                            break;
                    }

                    $cooldown = $cooldown < $serviceCooldown ? $serviceCooldown : $cooldown;
                }
            }
        } elseif ($variety === Visits::VISIT_MULTIPLE) {
            $distinguishedServices = self::distinguishServices($services, $govServices, 'for_multiple');
            // прием с несколькими животными:
            // Общая длительность приема = (длительность выбранной услуги - кулдаун) * количество голов * 0,75) + кулдаун

            foreach ($distinguishedServices as $serviceVariety => $combinedServices) {
                foreach ($combinedServices as $serviceId => $servicePets) {
                    $govService = $govServices[$serviceId];
                    $count = $servicePets['count'];
                    $serviceCooldown = $govService['cooldown'] ? (int)$govService['cooldown'] : 0;
                    unset($servicePets['count']);

                    switch($serviceVariety) {
                        case GovServices::FOR_HEAD:
                            $duration += (int)$govService['duration'] * $count * 0.75;
                            break;
                        case GovServices::FOR_ALL:
                            $duration += (int)$govService['duration'];
                            break;
                        case 'NULL':
                            $duration += (int)$govService['duration'] * $count;
                            break;
                    }

                    $cooldown = $cooldown < $serviceCooldown ? $serviceCooldown : $cooldown;
                }
            }
        } else {
            // прием с одним животным
            foreach ($services as $service) {
                $govService = $govServices[$service['id_service'] ?? $service['id']];
                // @see https://jira.altarix.ru/browse/VETAIS-1039
                $count = $service['count'] ?? 1;
                $duration += (int)$govService['duration'] * $count;
                $cooldown = $cooldown < (int)$govService['cooldown'] ? (int)$govService['cooldown'] : $cooldown;
            }
        }

        return [$duration, $cooldown];
    }

    /**
     * Разделяет услуги типа ALL, HEAD, null (на всю группу и на голову) и группирует идентичные услуги в массивы
     *
     * @param array $services
     * @param array $govServices
     * @param string $variety
     * @return array
     * @throws Exception
     */
    private static function distinguishServices(array $services, array $govServices, string $variety): array
    {
        if (!in_array($variety, ['for_broods', 'for_multiple'])) {
            throw new Exception("Неверный тип variety: '$variety', доступные значения: 'for_broods', 'for_multiple' ");
        }

        $combinedServices = [];
        foreach ($services as $service) {
            $id = $service['id_service'] ?? $service['id'];
            $govService = $govServices[$id];
            $serviceVariety = $govService->$variety ?? 'NULL';

            if (!isset($combinedServices[$serviceVariety][$id]['count'])) {
                $combinedServices[$serviceVariety][$id]['count'] = 0;
            }

            $combinedServices[$serviceVariety][$id][] = $service;
            if (isset($service['count'])){
                $combinedServices[$serviceVariety][$id]['count'] += $service['count'];
            }

        }
        return $combinedServices;
    }

    /**
     * @param int $serviceCount
     * @return int
     */
    private static function getTimeByCount(int $serviceCount): int
    {
        switch($serviceCount) {
            case $serviceCount < 5:
                return 20;
            case $serviceCount > 10:
                return 40;
            default:
                return 30;
        }
    }
}
