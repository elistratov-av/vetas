<?

namespace app\commands\cron;

use app\models\ArchivePet;
use app\models\ArchivePetOwner;
use app\models\db\ActiveRecord;
use app\models\db\Brood;
use app\models\db\elk\ElkOwners;
use app\models\db\elk\ElkPets;
use app\models\db\FiasAddresses;
use app\models\db\IdentificationTypes;
use app\models\db\PetDehelmintization;
use app\models\db\PetEctoparasites;
use app\models\db\PetIdentification;
use app\models\db\PetOtherVaccinations;
use app\models\db\PetOwners;
use app\models\db\PetRabiesVaccination;
use app\models\db\Pets;
use app\models\db\PetsToOwner;
use app\models\db\RegCertificates;
use app\models\db\Violation;
use app\models\db\VisitDescriptions;
use app\models\db\VisitPets;
use app\models\db\Visits;
use app\models\DuplicatesGroups;
use app\modules\admin\models\FiasAddress;
use app\modules\admin\models\Owners;
use app\modules\mdm\models\Pet;
use app\modules\v1\models\FileResource;
use Exception;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\db\ArrayExpression;
use yii\debug\models\search\Log;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;


/**
 * Class AutoDuplicatesController
 *
 * Этот класс управляет процессами обработки дубликатов владельцев питомцев.
 * Консольная команда, которая выполняется по расписанию (cron).
 *
 * Доступные команды:
 * - search-owners: Ищет владельцев питомцев, нуждающихся в обработке.
 * - delete-unauthorized: Удаляет неавторизованных владельцев.
 * - merge-owners-group: Объединяет группу дублирующихся владельцев в одного.
 * - merge-pets-group  Объединяет группу дублирующихся питомцев в одного
 * - full-cycle  Полный цикл поиска и склейки дублей
 * 
 * @package app\commands\cron
 */

class AutoDuplicatesController extends Controller
{

    const FIO_ERROR = "fio_error";
    const SSO_ID_ERROR = "sso_id_error";
    const PRECISION_ERROR = "precision_error";
    const STATUSES = ['in_process' => 'in_process', 'fail' => 'fail', 'manual_control' => 'manual_control'];
    const INCOGNITO_OWNER = 1;
    const PET_OWNER_TYPE = 1;
    const WORK_TIME = 3600 * 6;
    const BATCH_SIZE = 1000;

    protected $identTypes;

    public function init()
    {
        $this->identTypes = IdentificationTypes::find()->select(['id'])->column();
    }

    public function getHelp()
    {
        return "Доступные команды:\n" .
            "search-owners  Ищет владельцев питомцев, нуждающихся в обработке.\n
             delete-unauthorized  Удаляет неавторизованных владельцев.\n
             merge-owners-group  Объединяет группу дублирующихся владельцев в одного.\n
             merge-pets-group  Объединяет группу дублирующихся питомцев в одного.\n
             full-cycle  Полный цикл поиска и склейки дублей.\n";
    }


    /**
     * Ищет владельцев питомцев, требующих обработки, и вызывает соответствующие методы.
     *
     * @return int Код завершения процесса.
     */
    public function actionSearchOwners($fromConsole = true)
    {
        global $argv;
        Console::output("Старт алгоритма по поиску и склейке дубликатов");
        $begin = time();
        $ids =  isset($argv[2]) ? explode(',', $argv[2]) : null;
        $contactDuplicates = [];
        $addressDuplicates = [];
        $addressDict = [];
        $duplicates = [];
        $finalDuplicatesGroups = [];
        $mergedGroups = [];
        $excludedIds = [];
        $processedCount = 0;
        $total = PetOwners::find()->count();
        $petOwnersQuery[0] = PetOwners::find()->select(
            [
                'pet_owners.id as id',
                'pet_owners.fullname',
                'elk.owners.sso_id',
                'pet_owners.id_fias_address'
            ]
        )
            ->leftJoin('elk.owners', 'public.pet_owners.id = elk.owners.id_owner')
            ->where(['pet_owners.is_deleted' => false])
            ->andWhere(['pet_owners.is_legal' => false])
            ->orderBy(['pet_owners.id' => SORT_DESC]);
        if ($ids) {
            if (sizeof($ids) == 1) {
                Console::output("Используется режим ограниченной выборки. Проверяем владельцев с ID больше  {$ids[0]}");
                $petOwnersQuery[0]->andWhere(['>=', 'pet_owners.id',  $ids[0]]);
            } else {
                Console::output("Используется режим ограниченной выборки. Проверяем владельцев с ID " . $argv[2]);
                $petOwnersQuery[0]->andWhere(['pet_owners.id' => $ids]);
            }
        }
        $filterDuplicates = function ($array, $duplicateCondition = 1) {
            return array_values(
                array_filter($array, function ($arrayColumn) use ($duplicateCondition) {
                    return sizeof($arrayColumn) > $duplicateCondition;
                })
            );
        };
        $filterAddressDuplicates = function ($array, $duplicateCondition = 1) {
            return array_values(
                array_filter($array, function ($arrayColumn) use ($duplicateCondition) {
                    return sizeof($arrayColumn['duplicates']) > $duplicateCondition;
                })
            );
        };
        $getCollectionIds = function ($array) {
            return array_map(function ($duplicates) {
                return array_map(function ($record) {
                    return $record->id;
                }, $duplicates);
            }, $array);
        };
        $getAddressCollectionIds = function ($array) {
            return array_map(function ($duplicates) {
                return array_map(function ($record) {
                    return $record->id;
                }, $duplicates['duplicates']);
            }, $array);
        };
        $petOwnersQuery[1] = clone $petOwnersQuery[0];
        $petOwnersQuery[1]->addSelect('id_fact_fias_address')->with(['fact_fias_addresses'])->with(['fias_addresses']);
        $petOwnersQuery[0]->with([
            'contacts' => function ($query) {
                $query->andWhere(['not in', 'contacts.id_contact_type', [6, 20]]);
            },
            'visits'
        ]);
        Console::output("I этап: Поиск дубликатов по номеру телефона");
        foreach ($petOwnersQuery[0]->batch(static::BATCH_SIZE) as $records) {
            foreach ($records as $record) {
                $currentContacts = $record->extractContacts();
                if ($record->sso_id == 'unauthorized' && empty($currentContacts)) $excludedIds[] = $record->id;
                foreach ($currentContacts as $contact) {
                    if (!isset($contactDuplicates[$contact])) $contactDuplicates[$contact] = [];
                    $contactDuplicates[$contact][] = (object)[
                        'id' => $record->id,
                        'sso_id' => $record->sso_id == 'unauthorized' ? null : $record->sso_id,
                        'fullname' => $record->fullname
                    ];
                }
            }
            $processedCount += static::BATCH_SIZE;
            echo (($processedCount == static::BATCH_SIZE ? '' : "\r\033[K") . "Обработано записей $processedCount (" . ($processedCount / $total > 1 ? 100 : round($processedCount / $total * 100, 2)) . "%)");
        }
        echo "\n";
        $contactDuplicates = $filterDuplicates($contactDuplicates);
        $processedCount = 0;
        Console::output("I этап завершен. Общее время работы: " . (string)(time() - $begin) . " секунд. Дубликатов на этапе найдено: " . sizeof($getCollectionIds($contactDuplicates)));
        Console::output("II этап: Поиск дубликатов по адресам");
        foreach ($petOwnersQuery[1]->batch(static::BATCH_SIZE) as $records) {
            foreach ($records as $record) {
                if (in_array($record->id, $excludedIds)) continue;
                if (!($record->fact_fias_addresses && $record->fact_fias_addresses->cityguid && $record->fact_fias_addresses->streetguid) && !($record->fias_addresses && $record->fias_addresses->cityguid && $record->fias_addresses->streetguid)) continue;
                $factAddressKey = implode('-', [
                    $record->fact_fias_addresses->cityguid ?? '',
                    $record->fact_fias_addresses->streetguid ?? '',
                    $record->fact_fias_addresses->houseguid ?? '',
                    $record->fact_fias_addresses->roomguid ?? ''
                ]);
                $addressKey = implode('-', [
                    $record->fias_addresses->cityguid ?? '',
                    $record->fias_addresses->streetguid ?? '',
                    $record->fias_addresses->houseguid ?? '',
                    $record->fias_addresses->roomguid ?? ''
                ]);
                if ($record->fias_addresses && $record->fias_addresses->cityguid && $record->fias_addresses->streetguid) {
                    if (!isset($addressDuplicates[$addressKey])) {
                        $addressDuplicates[$addressKey] = [
                            'status' => $record->fias_addresses->houseguid ? static::STATUSES['in_process'] : static::STATUSES['manual_control'],
                            'comment' => !$record->fias_addresses->houseguid ? static::PRECISION_ERROR : null,
                            'duplicates' => []
                        ];
                    }
                    $addressDuplicates[$addressKey]['duplicates'][] = (object)[
                        'id' => $record->id,
                        'sso_id' => $record->sso_id == 'unauthorized' ? null : $record->sso_id,
                        'fullname' => $record->fullname
                    ];
                }
                if ($record->fact_fias_addresses && $record->fact_fias_addresses->cityguid && $record->fact_fias_addresses->streetguid) {
                    if (!isset($addressDuplicates[$factAddressKey])) {
                        $addressDuplicates[$factAddressKey] = [
                            'status' => $record->fact_fias_addresses->houseguid ? static::STATUSES['in_process'] : static::STATUSES['manual_control'],
                            'comment' => !$record->fact_fias_addresses->houseguid ? static::PRECISION_ERROR : null,
                            'duplicates' => []
                        ];
                    }
                    if ($addressKey != $factAddressKey) $addressDuplicates[$factAddressKey]['duplicates'][] = (object)[
                        'id' => $record->id,
                        'sso_id' => $record->sso_id == 'unauthorized' ? null : $record->sso_id,
                        'fullname' => $record->fullname
                    ];
                }
            }
            $processedCount += static::BATCH_SIZE;
            echo (($processedCount == static::BATCH_SIZE ? '' : "\r\033[K") . "Обработано записей $processedCount (" . ($processedCount / $total > 1 ? 100 : round($processedCount / $total * 100, 2)) . "%)");
        }
        echo "\n";
        $processedCount = 0;
        $addressDuplicates = $filterAddressDuplicates($addressDuplicates, 1);
        Console::output("II этап завершен. Общее время работы: " . (string)(time() - $begin) . " секунд. Дубликатов на этапе найдено: " . sizeof($getAddressCollectionIds($addressDuplicates)));
        Console::output("III этап: Проверка ФИО дубликатов");
        foreach ($contactDuplicates as $duplicateGroup) {
            $incognitos = [];
            $devidedGroups = [];
            foreach ($duplicateGroup as $duplicate) {
                $fioKey = trim(str_replace('инкогнито', '', mb_strtolower($duplicate->fullname)));
                if (!$fioKey) $incognitos[] = $duplicate;
                else {
                    if (!isset($devidedGroups[$fioKey])) $devidedGroups[$fioKey] = [];
                    $devidedGroups[$fioKey][] = $duplicate;
                }
            }
            $devidedGroups = array_values($devidedGroups);
            if (isset($devidedGroups[0])) $devidedGroups[0] = array_merge($devidedGroups[0], $incognitos);
            foreach($devidedGroups as $group) {
                if (sizeof($group) < 2) continue;
                $comment = null;
                $finalDuplicatesGroups[] = [
                    'status' => static::STATUSES['in_process'],
                    'comment' => '',
                    'data' => $group
                ];
            }
        }
        foreach ($addressDuplicates as $duplicateGroup) {
            $devidedGroup = [];
            foreach ($duplicateGroup['duplicates'] as $duplicate) {
                $devidedGroup[trim(str_replace('Инкогнито', '', mb_strtolower($duplicate->fullname)))][] = $duplicate;
            }
            foreach ($devidedGroup as $group) {
                if (sizeof($group) < 2) continue;
                $comment = null;
                $finalDuplicatesGroups[] = [
                    'status' => $duplicateGroup['status'],
                    'comment' => $duplicateGroup['comment'],
                    'data' => $group
                ];
            }
        }
        Console::output("III этап завершен. Общее время работы: " . (string)(time() - $begin) . " секунд. Всего групп дубликатов на этапе найдено: " . sizeof($finalDuplicatesGroups));
        Console::output("IV этап: Объединение групп и подготовка к записи");
        $groupsCount = sizeof($finalDuplicatesGroups);
        echo "Обработано групп 0 (0.00%)";
        while (count($finalDuplicatesGroups) > 0) {
            $processedCount = $groupsCount - sizeof($finalDuplicatesGroups);
            echo ("\r\033[K" . "Обработано групп $processedCount (" . ($processedCount / $groupsCount > 1 ? 100 : round($processedCount / $groupsCount * 100, 2)) . "%)");
            $currentGroup = array_pop($finalDuplicatesGroups);
            $merged = false;
            foreach ($mergedGroups as &$mergedGroup) {
                if (array_intersect(array_column($currentGroup['data'], 'id'), array_column($mergedGroup['data'], 'id'))) {
                    $mergedGroup['data'] = array_unique(array_merge($mergedGroup['data'], $currentGroup['data']), SORT_REGULAR);
                    if ($currentGroup['status'] == static::STATUSES['manual_control']) $mergedGroup['status'] = static::STATUSES['manual_control'];
                    $merged = true;
                    break;
                }
            }

            if (!$merged) {
                $mergedGroups[] = $currentGroup;
            }
        }
        foreach ($mergedGroups as &$group) {
            $ssoIds = array_filter(array_column($group['data'], 'sso_id'));
            if (count(array_unique($ssoIds)) > 1) {
                $group['status'] = static::STATUSES['fail'];
                $group['comment'] = static::SSO_ID_ERROR;
            }
        }
        $finalDuplicatesGroups = array_map(function ($group) {
            $groupDuplicates = array_column($group['data'], 'id');
            sort($groupDuplicates);
            return [
                'duplicates' => $groupDuplicates,
                'type' => 'owner',
                'status' => $group['status'],
                'comment' => $group['comment']
            ];
        }, $mergedGroups);
        echo "\n";
        Console::output("IV этап завершен. Общее время работы: " . (string)(time() - $begin) . " секунд. Всего групп дубликатов на этапе найдено: " . sizeof($finalDuplicatesGroups));
        Console::output("V этап: Запись в базу данных");
        $res = [];
        foreach ($finalDuplicatesGroups as $group) {
            if ($group['status'] == static::STATUSES['manual_control']) continue;
            $duplicatesGroup = new DuplicatesGroups($group);
            if ($duplicatesGroup->save()) $res[] = $duplicatesGroup;
        }
        Console::output("Скрипт завершен. Общее время работы: " . (string)(time() - $begin));
        return $fromConsole ? ExitCode::OK : $res;
    }

    /**
     * Удаляет неавторизованных владельцев питомцев и переносит данные питомцев к инкогнито владельцу.
     *
     * @return int Код завершения процесса.
     */
    public function actionDeleteUnauthorized()
    {
        $startTime = time();
        $owners = PetOwners::find()->with('pets.visits')
            ->select(['pet_owners.*', 'elk.owners.sso_id'])
            ->innerJoin('elk.owners', 'public.pet_owners.id = elk.owners.id_owner')
            ->where(['pet_owners.is_deleted' => false])
            ->andWhere([
                'or',
                ['in', 'elk.owners.sso_id', ['unauthorized', '']],
                ['elk.owners.sso_id' => null]
            ])
            ->orderBy(['pet_owners.id' => SORT_DESC])
            ->asArray()->all();
        $incognitoOwner = PetOwners::find()
            ->where(['id' => static::INCOGNITO_OWNER])
            ->with('pets.visits')
            ->one();
        $ownersForDel = [];
        $petsForDel = [];
        $petsForTransfer = [];
        foreach ($owners as $owner) {
            if (empty($owner['pets'])) {
                $ownersForDel[] = $owner['id'];
                continue;
            }
            $allPetsDeleted = true;
            foreach ($owner['pets'] as $idx => $pet) {
                if (empty($pet['visits'])) {
                    $petsForDel[] = $pet['id'];
                    continue;
                }
                $allPetsDeleted = false;
                $noNewVisits = (bool)array_reduce($pet['visits'], function ($res, $visit) {
                    return (bool)($res || time() - strtotime($visit['fact_start_dttm']) > 3600 * 24 * 3);
                }, false);
                if ($noNewVisits) {
                    $owner['pets'][$idx]['id_owner'] = $owner['id'];
                    $petsForTransfer[] = $pet;
                    continue;
                }
            }
            if ($allPetsDeleted) {
                $ownersForDel[] = $owner['id'];
                continue;
            }
        }
        foreach ($petsForTransfer as $petForTransfer) {
            $pet = array_filter($incognitoOwner->pets, function ($pet) use ($petForTransfer) {
                return $pet->id_species == $petForTransfer['id_species'];
            });
            $pet = empty($pet) ? null : array_values($pet)[0];
            if (!$pet) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $pet = new Pets();
                    $petToOwner = new PetsToOwner();
                    $pet->id_owner = static::INCOGNITO_OWNER;
                    $pet->id_species = $petForTransfer['id_species'];
                    $pet->id_breed = $petForTransfer['id_breed'];
                    if (!$pet->save()) continue;
                    $petToOwner->id_owner = static::INCOGNITO_OWNER;
                    $petToOwner->id_pet = $petForTransfer['id'];
                    $petToOwner->id_owner_type = static::PET_OWNER_TYPE;
                    if (!$petToOwner->save()) continue;
                    $transaction->commit();
                } catch (Exception $e) {
                    $transaction->rollBack();
                }
            }
            $visitsPets = VisitPets::find()->where(['id_pet' => $petForTransfer['id']])->all();
            $visits = Visits::find()->where(['id_pet' => $petForTransfer['id']])->all();
            foreach ($visitsPets as $visitPets) {
                $visitPets->id_pet = $pet->id;
                $visitPets->save();
            }
            foreach ($visits as $visit) {
                $visit->id_pet = $pet->id;
                $visit->id_owner = static::INCOGNITO_OWNER;
                $visit->save();
            }
        }
        foreach ($ownersForDel as $owner) {
            PetsToOwner::deleteAll(['id_owner' => $owner]);
            PetOwners::deleteAll(['id' => $owner]);
            ElkOwners::deleteAll(['id_owner' => $owner]);
            if (time() - $startTime > static::WORK_TIME) return ExitCode::OK;
            Console::log("Владелец $owner удален. Время работы скрипта: " . (time() - $startTime));
        }
        foreach ($petsForDel as $pet) {
            Pets::deleteAll(['id' => $pet]);
            if (time() - $startTime > static::WORK_TIME) return ExitCode::OK;
            Console::log("Питомец $pet удален. Время работы скрипта: " . (time() - $startTime));
        }
        return ExitCode::OK;
    }

    /**
     * Объединяет группу дублирующихся владельцев в одного.
     * При необходимости архивирует старые записи.
     *
     * @param DuplicatesGroups|null $ownersGroup Группа владельцев, которые нужно объединить. Если не указана, выбирается из базы данных.
     * @return int Код завершения процесса.
     */
    public function actionMergeOwnersGroup($ownersGroup =  null)
    {
        $ownersGroup = isset($argv[2]) ? $argv[2] : $ownersGroup;
        if (!($ownersGroup instanceof DuplicatesGroups)) {
            $query = DuplicatesGroups::find()->where(['status' => 'in_process'])->andWhere(['type' => 'owner']);
            if ((int)$ownersGroup) $query->andWhere(['id' => $ownersGroup]);
            $ownersGroup = $query->one();
        }
        Console::output("Склейка группы: $ownersGroup->id");
        $duplicateOwners = PetOwners::find()
            ->select('public.pet_owners.*, elk.owners.sso_id')
            ->leftJoin('elk.owners', 'public.pet_owners.id = elk.owners.id_owner')
            ->where(['public.pet_owners.id' => $ownersGroup->duplicates])
            ->with('fias_addresses', 'fact_fias_addresses')
            ->with('contacts')
            ->orderBy(['pet_owners.id' => SORT_DESC])
            ->all();
        $visits = Visits::find()->where(['id_owner' => $ownersGroup->duplicates])->all();
        $petsToOwners = PetsToOwner::find()->where(['id_owner' => $ownersGroup->duplicates])->all();
        $violations = Violation::find()->where(['id_owner' => $ownersGroup->duplicates])->all();
        $elkOwners = ElkOwners::find()->where(['id_owner' => $ownersGroup->duplicates])->all();
        $elkPets = ElkPets::find()->where(['id_pet_owner' => $ownersGroup->duplicates])->all();
        $fileResources = FileResource::find()->where(['entity_id' => $ownersGroup->duplicates])->andWhere(['entity_type' => 'owner_agreement'])->all();
        $broods = Brood::find()->where(['id_owner' => $ownersGroup->duplicates])->all();
        $regCerts = RegCertificates::find()->where(['id_owner' => $ownersGroup->duplicates])->all();
        $newData = [
            'f_fio' => [
                '-' => -1
            ],
            'i_fio' => [
                '-' => -1
            ],
            'o_fio' => [
                null => -1
            ],
            'birthday' => [
                null => -1
            ],
            'snils' => [
                null => -1
            ],
            'sso_id' => [
                null => -1
            ],
            'passport_series' => [
                null => -1
            ],
            'passport_number' => [
                null => -1
            ],
            'passport_issue_date' => [
                null => -1
            ],
            'passport_issuer' => [
                null => -1
            ],
            'is_veteran_infosoc' => [
                false => 1,
                true => 0
            ],
            'is_disabled_infosoc' => [
                false => 1,
                true => 0
            ],
            'is_family_disabled_children_infosoc' => [
                false => 1,
                true => 0
            ],
            'is_blind_infosoc' => [
                false => 1,
                true => 0
            ],
            'description' => ''
        ];
        $newFiasAddress = [
            'full_address' => [
                null => -1
            ]
        ];
        $newFactFiasAddress = [
            'full_address' => [
                null => -1
            ]
        ];
        $contacts = [];
        foreach ($duplicateOwners as $owner) {
            if (!empty($owner->contacts)) {
                foreach ($owner->contacts as $contact) {
                    $contacts[] = $contact;
                }
            }
            if ($owner->fact_fias_addresses) {
                foreach ($owner->fact_fias_addresses->attributes as $key => $value) {
                    if ($key != 'id' && !empty($value)) {
                        if ($value instanceof ArrayExpression) {
                            $value = implode(',', $value->getValue());
                        }
                        if ($key == 'description') {
                            if (!isset($newFactFiasAddress[$key]['description'])) $newFactFiasAddress[$key]['description'] = '';
                            $newFactFiasAddress[$key]['description'] .= "$value ";
                        }
                        if (!isset($newFactFiasAddress[$key])) $newFactFiasAddress[$key] = [null => -1];
                        if (!isset($newFactFiasAddress[$key][$value])) $newFactFiasAddress[$key][$value] = 0;
                        $newFactFiasAddress[$key][$value]++;
                    }
                }
            }
            if ($owner->fias_addresses) {
                foreach ($owner->fias_addresses->attributes as $key => $value) {
                    if ($key !== 'id' && !empty($value)) {
                        if ($value instanceof ArrayExpression) {
                            $value = implode(',', $value->getValue());
                        }
                        if ($key == 'description') {
                            if (!isset($newFiasAddress[$key]['description'])) $newFiasAddress[$key]['description'] = '';
                            $newFiasAddress[$key]['description'] .= "$value ";
                        }
                        if (!isset($newFiasAddress[$key])) $newFiasAddress[$key] = [null => -1];
                        if (!isset($newFiasAddress[$key][$value])) $newFiasAddress[$key][$value] = 0;
                        $newFiasAddress[$key][$value]++;
                    }
                }
            }
            $hasSso = $owner->sso_id && $owner->sso_id != 'unauthorized';
            foreach (['f_fio', 'i_fio', 'o_fio'] as $key) {
                if ($owner->$key && mb_stripos(trim($owner->$key), 'инкогнито') === false && mb_stripos(trim($owner->sso_id), 'unauthorized') === false) {
                    if (!isset($newData[$key][$owner->$key])) $newData[$key][$owner->$key] = 0;
                    $newData[$key][$owner->$key] += $hasSso ? sizeof($duplicateOwners) : 1;
                }
            }
            if ($owner->birthday) {
                if (!isset($newData['birthday'][$owner->birthday])) $newData['birthday'][$owner->birthday] = 0;
                $newData['birthday'][$owner->birthday]++;
            }
            if ($owner->snils) {
                if (!isset($newData['snils'][$owner->snils])) $newData['snils'][$owner->snils] = 0;
                $newData['snils'][$owner->snils] += $hasSso ? sizeof($duplicateOwners) : 1;
            }
            if ($hasSso) {
                if (!isset($newData['sso_id'][$owner->sso_id])) $newData['sso_id'][$owner->sso_id] = 0;
                $newData['sso_id'][$owner->sso_id] += $owner->sso_id ? sizeof($duplicateOwners) : 1;
            }
            foreach (['passport_series', 'passport_number', 'passport_issue_date', 'passport_issuer'] as $key) {
                if ($owner->$key) {
                    $oldValue = $newData[$key][$owner->$key] ?? 0;
                    $newData[$key][$owner->$key] = max($oldValue, strtotime($owner->updated_at));
                }
            }
            if ($owner->description) $newData['description'] .= "$owner->description\n";
        }
        $contactStats = [];
        $finalFiasAddress = [
            'full_address' => null
        ];
        $finalFactFiasAddress = [
            'full_address' => null
        ];
        $finalContacts = [];
        foreach ($newFactFiasAddress as $key => $values) {
            if ($key == 'description') {
                $finalFactFiasAddress[$key] = $values['description'];
                continue;
            }
            $finalFactFiasAddress[$key] = array_search(max($values), $values);
            if ($key == 'oktmo') $finalFactFiasAddress[$key] = (string)$finalFactFiasAddress[$key];
            if ($key == 'bti_city_area_code')  $finalFactFiasAddress[$key] = new ArrayExpression(explode(',', $finalFactFiasAddress[$key]), 'varchar');
        }
        foreach ($newFiasAddress as $key => $values) {
            if ($key == 'description') {
                $finalFiasAddress[$key] = $values['description'];
                continue;
            }
            $finalFiasAddress[$key] = array_search(max($values), $values);
            if ($key == 'oktmo') $finalFiasAddress[$key] = (string)$finalFiasAddress[$key];
            if ($key == 'bti_city_area_code')  $finalFiasAddress[$key] = new ArrayExpression(explode(',', $finalFiasAddress[$key]), 'varchar');
        }
        foreach ($contacts as $contact) {
            $name = $contact->name;
            if (!isset($contactStats[$name])) {
                $contactStats[$name] = [
                    'count_main_flag' => 0,
                    'last_updated' => strtotime($contact->updated_at),
                    'contact' => $contact
                ];
            }

            if ($contact->main_flag == 1) {
                $contactStats[$name]['count_main_flag']++;
            }
            $updated_at = strtotime($contact->updated_at);
            if ($updated_at > $contactStats[$name]['last_updated']) {
                $contactStats[$name]['last_updated'] = $updated_at;
                $contactStats[$name]['contact'] = $contact;
            }
        }
        $finalContact = null;
        $maxMainFlag = -1;
        $latestUpdate = -1;
        foreach ($contactStats as $stats) {
            if ($stats['count_main_flag'] > $maxMainFlag) {
                $maxMainFlag = $stats['count_main_flag'];
                $finalContact = $stats['contact'];
                $latestUpdate = $stats['last_updated'];
            } elseif ($stats['count_main_flag'] == $maxMainFlag && $stats['last_updated'] > $latestUpdate) {
                $finalContact = $stats['contact'];
                $latestUpdate = $stats['last_updated'];
            }
        }
        foreach ($contacts as $contact) {
            if ($contact->id == $finalContact->id)  $contact->main_flag = 1;
            else $contact->main_flag = 0;
            if (!isset($finalContacts[$contact->name])) $finalContacts[$contact->name] = $contact;
            else $finalContacts[$contact->name] = $contact->main_flag ? $contact : $finalContacts[$contact->name];
        }
        $finalContacts = array_values($finalContacts);
        $finalData = [
            'f_fio' => array_search(max($newData['f_fio']), $newData['f_fio']),
            'i_fio' => array_search(max($newData['i_fio']), $newData['i_fio']),
            'o_fio' => array_search(max($newData['o_fio']), $newData['o_fio']),
            'birthday' => array_search(max($newData['birthday']), $newData['birthday']),
            'is_veteran_infosoc' => array_search(max($newData['is_veteran_infosoc']), $newData['is_veteran_infosoc']),
            'is_disabled_infosoc' => array_search(max($newData['is_disabled_infosoc']), $newData['is_disabled_infosoc']),
            'is_family_disabled_children_infosoc' => array_search(max($newData['is_family_disabled_children_infosoc']), $newData['is_family_disabled_children_infosoc']),
            'is_blind_infosoc' => array_search(max($newData['is_blind_infosoc']), $newData['is_blind_infosoc']),
            'description' => $newData['description']
        ];
        $finalSnils = (string)array_search(max($newData['snils']), $newData['snils']);
        $finalSeries = array_search(max($newData['passport_series']), $newData['passport_series']);
        $finalNumber = array_search(max($newData['passport_number']), $newData['passport_number']);
        $finalIssue = array_search(max($newData['passport_issue_date']), $newData['passport_issue_date']);
        $finalIssuer = array_search(max($newData['passport_issuer']), $newData['passport_issuer']);
        $printDate = function ($dates) {
            $response = [];
            foreach ($dates as $date => $val) {
                if (!$date) {
                    $response[$date] = $val;
                } else $response[date('d.m.Y', strtotime($date))] = $val;
            }
            return $response;
        };
        $additionalDescription = '';
        static::addDescription($printDate($newData['birthday']), date('d.m.Y', strtotime($finalData['birthday'])), $additionalDescription, 'День рождения');
        static::addDescription($newData['snils'], $finalSnils, $additionalDescription, 'СНИЛС');
        static::addDescription($newData['passport_series'], $finalSeries, $additionalDescription, 'Паспорт/серия');
        static::addDescription($newData['passport_number'], $finalNumber, $additionalDescription, 'Паспорт/номер');
        static::addDescription($printDate($newData['passport_issue_date']),  date('d.m.Y', strtotime($finalIssue)), $additionalDescription, 'Паспорт/дата выдачи');
        static::addDescription($newData['passport_issuer'], $finalIssuer, $additionalDescription, 'Паспорт/выдан');
        static::addDescription($newFiasAddress['full_address'], $finalFiasAddress['full_address'], $additionalDescription, 'Адрес регистрации');
        static::addDescription($newFactFiasAddress['full_address'], $finalFactFiasAddress['full_address'], $additionalDescription, 'Фактический адрес');
        $finalData['description'] .= $additionalDescription;
        $finalData['description'] = mb_substr($finalData['description'], 0, 400);
        $transaction = \Yii::$app->db->beginTransaction();
        $err = null;
        try {
            $newOwner = new PetOwners($finalData);
            $newOwner->sso_id = array_search(max($newData['sso_id']), $newData['sso_id']);
            if (!$newOwner->validate() || !$newOwner->save()) {
                $err = $err ?? 'new_owner_save_error';
                Console::error("Новый владелец не создан");
                Console::error("Ошибки: " . print_r($newOwner->getErrors(), true));
            }
            foreach ($petsToOwners as $petToOwner) {
                $petToOwner->id_owner = $newOwner->id;
                if (!$petToOwner->save()) {
                    $err = $err ?? 'pets_transfer_error';
                    Console::error("Связь питомца $petToOwner->id с новым владельцем не сохранена. Откат транзакции.");
                    Console::error("Ошибки: " . print_r($petToOwner->getErrors(), true));
                }
            }
            foreach ($visits as $visit) {
                $visit->id_owner = $newOwner->id;
                if (!$visit->save()) {
                    $err = $err ?? 'visits_transfer_error';
                    Console::error("Связь приема $visit->id с новым владельцем не сохранена. Откат транзакции.");
                    Console::error("Ошибки: " . print_r($visit->getErrors(), true));
                }
            }
            foreach ($finalContacts as $finalContact) {
                $finalContact->entity_id = $newOwner->id;
                if (!$finalContact->save()) {
                    $err = $err ?? 'visits_transfer_error';
                    Console::error("Контакт $finalContact->id не изменен. Откат транзакции.");
                    Console::error("Ошибки: " . print_r($finalContact->getErrors(), true));
                }
            }
            foreach ($violations as $violation) {
                $violation->id_owner = $newOwner->id;
                if (!$violation->save()) {
                    $err = $err ?? 'violations_transfer_error';
                    Console::error("Нарушение $violation->id_violation не изменено. Откат транзакции.");
                    Console::error("Ошибки: " . print_r($violation->getErrors(), true));
                }
            }
            foreach ($fileResources as $fileResource) {
                $fileResource->entity_id = $newOwner->id;
                if (!$fileResource->save()) {
                    $err = $err ?? 'violations_transfer_error';
                    Console::error("Ресурс $fileResource->id не изменен. Откат транзакции.");
                    Console::error("Ошибки: " . print_r($fileResource->getErrors(), true));
                }
            }
            foreach ($broods as $brood) {
                $brood->id_owner = $newOwner->id;
                if (!$brood->save()) {
                    $err = $err ?? 'broods_transfer_error';
                    Console::error("Выводок $brood->id не изменен. Откат транзакции.");
                    Console::error("Ошибки: " . print_r($brood->getErrors(), true));
                }
            }
            /**
             * @var RegCertificates $cert
             */
            foreach ($regCerts as $cert) {
                $cert->id_owner = $newOwner->id;
                $cert->detachBehavior('timestamp');
                if (!$cert->save()) {
                    $err = $err ?? 'certs_transfer_error';
                    Console::error("Сертификат $cert->id не изменен. Откат транзакции.");
                    Console::error("Ошибки: " . print_r($cert->getErrors(), true));
                }
            }
            $newFiasAddress = new FiasAddress($finalFiasAddress);
            $newFactFiasAdress = new FiasAddress($finalFactFiasAddress);
            foreach (['full_address', 'description', 'region', 'city', 'street', 'house', 'room'] as $field) {
                $newFiasAddress->{$field} = (string)$newFiasAddress->{$field};
                $newFactFiasAdress->{$field} = (string)$newFactFiasAdress->{$field};
            }
            if (!$newFiasAddress->save() || !$newFactFiasAdress->save()) {
                $err = $err ?? 'fias_create_error';
                Console::error("Адреса не сохранены. Откат транзакции.");
                Console::error("Ошибки адреса регистрации: " . print_r($newFiasAddress->getErrors(), true));
                Console::error("Ошибки фактического адреса: " . print_r($newFactFiasAdress->getErrors(), true));
            }
            $newOwner->id_fias_address = $newFiasAddress->id;
            $newOwner->id_fact_fias_address = $newFactFiasAdress->id;
            $newOwner->save();
            if (mb_strlen($err)) {
                Console::error("Отмена транзакции из-за ошибки.");
                throw new Exception();
            }
            foreach ($elkOwners as $elkOwner) {
                $elkOwner->id_owner = $newOwner->id;
                if ($elkOwner->sso_id == 'unauthorized') {
                    if (!$elkOwner->delete()) {
                        $err = $err ?? 'elk_owner_delete_error';
                        Console::error("ELKOwner $elkOwner->id не удален. Откат транзакции.");
                        Console::error("Ошибки: " . print_r($elkOwner->getErrors(), true));
                    }
                } else {
                    if (!$elkOwner->save()) {
                        $err = $err ?? 'elk_owner_save_error';
                        Console::error("ELKOwner $elkOwner->id не сохранен. Откат транзакции.");
                        Console::error("Ошибки: " . print_r($elkOwner->getErrors(), true));
                    }
                }
            }
            foreach ($duplicateOwners as $owner) {
                if (!ArchivePetOwner::archivePetOwner($owner, $newOwner)) {
                    $err = $err ?? 'archive_owner_error';
                    Console::error("Владельцы не архивированы. Откат миграции.");
                }
            }
            if ($finalSeries && $finalNumber) {
                $newOwner->passport_series = (string)$finalSeries;
                $newOwner->passport_number = (string)$finalNumber;
                $newOwner->passport_issue_date = $finalIssue;
                $newOwner->passport_issuer = $finalIssuer;
                $newOwner->snils =  $finalSnils;
                if (!$newOwner->save()) {
                    $err = $err ?? 'passport_save_error';
                    Console::error("Паспортные данные владельца не сохранены. Откат миграции.");
                    Console::error("Ошибки: " . print_r($newOwner->getErrors(), true));
                }
            }
            foreach ($elkPets as $elkPet) {
                $elkPet->id_pet_owner = $newOwner->id;
                if (!$elkPet->save()) {
                    $err = $err ?? 'elk_pet_save_error';
                    Console::error("ELKPet $elkPet->id, $elkPet->id_elk_owner не сохранен. Откат транзакции.");
                    Console::error("Ошибки: " . print_r($elkPet->getErrors(), true));
                }
            }
        } catch (Exception $e) {
            Console::error($e->getLine() . " " . $e->getMessage());
            $transaction->rollBack();
            $ownersGroup->status = 'fail';
            $ownersGroup->comment = $err ?? 'transaction_error';
            $ownersGroup->save();
            Console::output("Объединение группы дублей $ownersGroup->id завершено с ошибкой");
            return ExitCode::DATAERR;
        }
        if (mb_strlen($err)) {
            $transaction->rollBack();
            $ownersGroup->status = 'fail';
            $ownersGroup->comment = $err;
            $ownersGroup->save();
            Console::output("Объединение группы дублей $ownersGroup->id завершено с ошибкой");
            return ExitCode::DATAERR;
        }
        Console::output("Объединение группы дублей $ownersGroup->id завершено успешно. Новый владелец $newOwner->id. Группа из таблицы удалена.");
        $ownersGroup->delete();
        $transaction->commit();
        $this->actionSearchPets($newOwner);
        return ExitCode::OK;
    }

    /**
     * Ищет питомцев, требующих обработки, и вызывает соответствующие методы.
     *
     * @return int Код завершения процесса.
     */
    public function actionSearchPets($owner = null)
    {
        $owner = isset($argv[2]) ? $argv[2] : $owner;
        if (!($owner instanceof PetOwners)) $owner = PetOwners::findOne($owner);
        Console::output("Поиск дублей питомцев у владельца $owner->id");
        $petsGroup = Pets::find()
            ->joinWith(['owner o'])
            ->with('pet_identification')
            ->where(['o.id' => $owner->id])
            ->andWhere(['pets.id_reg_expire_reason' => null])
            ->all();
        $isDuplicate = function ($pet1, $pet2) {
            return $pet1->id_species === $pet2->id_species &&
                $pet1->id_breed === $pet2->id_breed &&
                $pet1->name === $pet2->name;
        };
        $duplicates = [];
        $duplicatesPets = [];
        foreach ($petsGroup as $pet) {
            if (in_array($pet->id, $duplicates)) continue;
            $dupes = array_filter($petsGroup, function ($otherPet) use ($pet, $isDuplicate) {
                return $pet->id !== $otherPet->id && $isDuplicate($pet, $otherPet);
            });
            if (!empty($dupes)) {
                $duplicateIds = array_map(function ($d) {
                    return $d->id;
                }, $dupes);
                $duplicates = array_merge($duplicates, array_merge([$pet->id], $duplicateIds));
                $duplicatesPets[] = array_merge([$pet], $dupes);
            }
        }
        foreach ($duplicatesPets as $group) {
            $continueCond = false;
            foreach ($this->identTypes as $type) {
                if (count(array_filter($group, function ($pet) use ($type) {
                    $typedIdent = array_filter($pet->pet_identification, function ($ident) use ($type) {
                        return $ident->id_ident_type == $type;
                    });
                    return count($typedIdent) > 0;
                })) > 1) {
                    Console::output("Группа с несколькими чипами пропущена");
                    $continueCond = true;
                    break;
                }
            }
            if ($continueCond) continue;
            $grIds = array_map(function ($item) {
                return $item->id;
            }, $group);
            sort($grIds);
            $duplicatesGroup = new DuplicatesGroups([
                'duplicates' => $grIds,
                'type' => 'pet',
                'status' => 'in_process',
                'comment' => ''
            ]);
            if ($duplicatesGroup->save()) $this->actionMergePetsGroup($duplicatesGroup);
        }
        Console::output("Поиск и склейка дублей питомцев у нового владельца $owner->id завершена");
    }

    /**
     * Объединяет группу дублирующихся питомцев в одного.
     * При необходимости архивирует старые записи.
     *
     * @param DuplicatesGroups|null $ownersGroup Группа питомцев, которые нужно объединить. Если не указана, выбирается из базы данных.
     * @return int Код завершения процесса.
     */
    public function actionMergePetsGroup($petsGroup =  null)
    {
        global $argv;
        if (isset($argv[2]) && (!$petsGroup || is_string($petsGroup))) $petsGroup = DuplicatesGroups::find()->where(['status' => 'in_process'])->andWhere(['type' => 'pet'])->andWhere(['id' => $argv[2]])->one();
        if (!$petsGroup) $petsGroup = DuplicatesGroups::find()->where(['status' => 'in_process'])->andWhere(['type' => 'pet'])->one();
        $duplicatePets = Pets::find()
            ->with('shelter_records')
            ->with('elk_pet')
            ->with('health')
            ->with('fias_address')
            ->where(['id' => $petsGroup->duplicates])
            ->all();
        $visits = Visits::find()->joinWith('pets', false)->where(['pets.id' => $petsGroup->duplicates])->all();
        $visitsPets = VisitPets::find()->where(['id_pet' => $petsGroup->duplicates])->all();
        $petIdentifications = PetIdentification::find()->where(['id_pet' => $petsGroup->duplicates])->all();
        $petDehelmintizations = PetDehelmintization::find()->where(['id_pet' => $petsGroup->duplicates])->all();
        $petEctoparasites = PetEctoparasites::find()->where(['id_pet' => $petsGroup->duplicates])->all();
        $petRabies = PetRabiesVaccination::find()->where(['id_pet' => $petsGroup->duplicates])->all();
        $petOtherVac = PetOtherVaccinations::find()->where(['id_pet' => $petsGroup->duplicates])->all();
        $owners = PetsToOwner::find()->where(['id_pet' => $petsGroup->duplicates])->all();
        $elkPets = ElkPets::find()->where(['id_pet' => $petsGroup->duplicates])->all();
        $violations = Violation::find()->where(['id_pet' => $petsGroup->duplicates])->all();
        $visitDescriptions = VisitDescriptions::find()->where(['id_pet' => $petsGroup->duplicates])->all();
        $fileResources = FileResource::find()->where(['entity_id' => $petsGroup->duplicates])->andWhere(['entity_type' => 'reg_application'])->all();
        $regCerts = RegCertificates::find()->where(['id_pet' => $petsGroup->duplicates])->orderBy(['updated_at' => SORT_DESC])->all();
        $newData = [
            'id_species' => [
                null => -1
            ],
            'id_breed' => [
                null => -1
            ],
            'name' => [
                null => -1
            ],
            'sex' => [
                null => -1
            ],
            'birthday' => [
                null => -1
            ],
            'guide_dog' => [
                true => 0,
                false => 0
            ],
            'castrated' => [
                true => 0,
                false => 0
            ],
            'is_address_pet_owners' => [
                true => 0,
                false => 0,
                null => 0
            ],
            'characteristics' => '',
            'description' => ''
        ];
        $newFiasAddress = [];
        $finalFiasAddress = [];
        foreach ($duplicatePets as $pet) {
            if ($pet->id_species) {
                if (!isset($newData['id_species'][$pet->id_species])) $newData['id_species'][$pet->id_species] = 0;
                $newData['id_species'][$pet->id_species] += $pet->elk_pet ? 1000 : 1;
            }
            if ($pet->id_breed) {
                if (!isset($newData['id_breed'][$pet->id_breed])) $newData['id_breed'][$pet->id_breed] = 0;
                $newData['id_breed'][$pet->id_breed] += $pet->elk_pet ? 1000 : 1;
            }
            if ($pet->name) {
                if (!isset($newData['name'][$pet->name])) $newData['name'][$pet->name] = 0;
                $newData['name'][$pet->name] += $pet->elk_pet ? 1000 : 1;
            }
            if ($pet->sex) {
                if (!isset($newData['sex'][$pet->sex])) $newData['sex'][$pet->sex] = 0;
                $newData['sex'][$pet->sex] += $pet->elk_pet ? 1000 : 1;
            }
            if ($pet->birthday) {
                if (!isset($newData['birthday'][$pet->birthday])) $newData['birthday'][$pet->birthday] = 0;
                $newData['birthday'][$pet->birthday] += $pet->elk_pet ? 1000 : 1;
            }
            $newData['guide_dog'][$pet->guide_dog]++;
            $newData['castrated'][$pet->castrated]++;
            $newData['is_address_pet_owners'][$pet->is_address_pet_owners]++;
            $newData['description'] .= "$pet->description\n";
            $newData['characteristics'] .= "$pet->characteristics\n";
            if ($pet->fias_address) {
                foreach ($pet->fias_address->attributes as $key => $value) {
                    if ($key != 'id' && !empty($value)) {
                        if ($value instanceof ArrayExpression) {
                            $value = implode(',', $value->getValue());
                        }
                        if ($key == 'description') {
                            if (!isset($newFiasAddress[$key]['description'])) $newFiasAddress[$key]['description'] = '';
                            $newFiasAddress[$key]['description'] .= "$value ";
                        }
                        if (!isset($newFiasAddress[$key])) $newFiasAddress[$key] = [null => -1];
                        if (!isset($newFiasAddress[$key][$value])) $newFiasAddress[$key][$value] = 0;
                        $newFiasAddress[$key][$value] += $pet->is_address_pet_owners ? 1000 : 1;
                    }
                }
            }
        }

        foreach ($newFiasAddress as $key => $values) {
            if ($key == 'description') {
                $finalFiasAddress[$key] = $values['description'];
                continue;
            }
            $finalFiasAddress[$key] = array_search(max($values), $values);
            if ($key == 'oktmo') $finalFiasAddress[$key] = (string)$finalFiasAddress[$key];
            if ($key == 'bti_city_area_code')  $finalFiasAddress[$key] = new ArrayExpression(explode(',', $finalFiasAddress[$key]), 'varchar');
        }

        foreach (['full_address', 'description', 'region', 'city', 'street', 'house', 'room'] as $field) {
            if (isset($finalFiasAddress[$field])) $finalFiasAddress[$field] = (string)$finalFiasAddress[$field];
        }

        $finalData = [
            'id_species' => array_search(max($newData['id_species']), $newData['id_species']),
            'id_breed' => array_search(max($newData['id_breed']), $newData['id_breed']),
            'name' => array_search(max($newData['name']), $newData['name']),
            'sex' => array_search(max($newData['sex']), $newData['sex']),
            'birthday' => array_search(max($newData['birthday']), $newData['birthday']),
            'guide_dog' => array_search(max($newData['guide_dog']), $newData['guide_dog']),
            'castrated' => array_search(max($newData['castrated']), $newData['castrated']),
            'is_address_pet_owners' => array_search(max($newData['is_address_pet_owners']), $newData['is_address_pet_owners']),
            'description' => $newData['description']
        ];
        $printDate = function ($dates) {
            $response = [];
            foreach ($dates as $date => $val) {
                if (!$date) {
                    $response[$date] = $val;
                } else $response[date('d.m.Y', strtotime($date))] = $val;
            }
            return $response;
        };
        $printBool = function ($values) {
            $res = [];
            foreach ($values as $value => $val) {
                $res[$value ? 'Да' : 'Нет'] = $val;
            }
            return $res;
        };
        $printRegCerts = [
            '' => -1
        ];
        $finalRegCert = '';
        if ($regCerts && sizeof($regCerts) > 1) {
            foreach ($regCerts as $idx => $crt) {
                if (!$idx) $finalRegCert = $crt->number;
                if (!isset($printRegCerts[$crt->number])) $printRegCerts[$crt->number] = 0;
                $printRegCerts[$crt->number] += ($idx ? 1 : 100);
            }
        }
        $additionalDescription = '';
        static::addDescription($newData['sex'], $finalData['sex'], $additionalDescription, 'Пол');
        static::addDescription($printDate($newData['birthday']), date('d.m.Y', strtotime($finalData['birthday'])), $additionalDescription, 'Дата рождения');
        static::addDescription($printBool($newData['guide_dog']), $finalData['guide_dog'] ? 'Да' : 'Нет', $additionalDescription, 'Поводырь');
        static::addDescription($printBool($newData['castrated']), $finalData['castrated'] ? 'Да' : 'Нет', $additionalDescription, 'Кастрация/стерилизация');
        if ($newFiasAddress && $finalFiasAddress) static::addDescription($newFiasAddress['full_address'], $finalFiasAddress['full_address'], $additionalDescription, 'Фактический адрес');
        if (sizeof($regCerts) > 1) static::addDescription($printRegCerts , $finalRegCert, $additionalDescription, 'Данные о регистрации');
        $finalData['description'] .= $additionalDescription;
        $finalData['description'] = mb_substr($finalData['description'], 0, 400);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $newPet = new Pets($finalData);
            if (!$newPet->save()) {
                Console::error("Ошибка сохранения питомца");
                Console::error("Ошибки: " . print_r($newPet->getErrors(), true));
                $petsGroup->status = 'fail';
                $petsGroup->comment = 'pet_save_error';
                throw new Exception("Save error");
            }
            if ($regCerts && sizeof($regCerts) > 1) {
                foreach ($regCerts as $idx => $crt) {
                    if ($idx) $crt->delete();
                }
                $regCerts = [$regCerts[0]];
            }
            static::changeEntityRelations($newPet, 'id_pet', $visits, $petsGroup);
            static::changeEntityRelations($newPet, 'id_pet', $visitsPets, $petsGroup);
            static::changeEntityRelations($newPet, 'id_pet', $petIdentifications, $petsGroup);
            static::changeEntityRelations($newPet, 'id_pet', $petDehelmintizations, $petsGroup);
            static::changeEntityRelations($newPet, 'id_pet', $petEctoparasites, $petsGroup);
            static::changeEntityRelations($newPet, 'id_pet', $petRabies, $petsGroup);
            static::changeEntityRelations($newPet, 'id_pet', $petOtherVac, $petsGroup);
            static::changeEntityRelations($newPet, 'id_pet', $owners, $petsGroup);
            static::changeEntityRelations($newPet, 'id_pet', $elkPets, $petsGroup);
            static::changeEntityRelations($newPet, 'id_pet', $violations, $petsGroup);
            static::changeEntityRelations($newPet, 'id_pet', $visitDescriptions, $petsGroup);
            static::changeEntityRelations($newPet, 'entity_id', $fileResources, $petsGroup);
            static::changeEntityRelations($newPet, 'id_pet', $regCerts, $petsGroup);
            $newFiasAddress = new FiasAddress($finalFiasAddress);
            if (!$newFiasAddress->save()) {
                //$petsGroup->comment = $petsGroup->comment ? $petsGroup->comment : 'fias_create_error';
                Console::error("Адреса не сохранены.");
                Console::error("Ошибки: " . print_r($newFiasAddress->getErrors(), true));
                Yii::warning("Адрес не создан", __METHOD__);
                //throw new Exception("Save error");
            }
            if ($newFiasAddress) $newPet->id_fias_address = $newFiasAddress->id;
            foreach ($duplicatePets as $pet) {
                if (!ArchivePet::archivePet($pet, $newPet)) {
                    $err = $err ?? 'archive_pet_error';
                    Console::error("Питомцы не архивированы. Откат миграции.");
                    throw new Exception("Save error");
                }
            }
        } catch (Exception $e) {
            Console::error($e->getLine() . " " . $e->getMessage());
            $transaction->rollBack();
            $petsGroup->status = 'fail';
            $petsGroup->comment = $petsGroup->comment ? $petsGroup->comment : 'transaction_error';
            $petsGroup->save();
            Console::output("Объединение группы дублей питомцев $petsGroup->id завершено с ошибкой");
            return ExitCode::DATAERR;
        }
        Console::output("Объединение группы дублей питомцев $petsGroup->id завершено успешно. Группа из таблицы удалена. Новый питомец - $newPet->id");
        $petsGroup->delete();
        $transaction->commit();
        return ExitCode::OK;
    }

    /**
     * Запускает полный обход по дублям.
     * При необходимости находит новые дубли.
     *
     * @return int Код завершения процесса.
     */
    public function actionFullCycle()
    {
        global $argv;
        $startTime = time();
        $duplicatesGroups = isset($argv[2]) ? null : DuplicatesGroups::find()->where(['status' => 'in_process'])->andWhere(['type' => 'owner'])->all();
        if (!$duplicatesGroups) $duplicatesGroups = $this->actionSearchOwners(false);
        if (!$duplicatesGroups) {
            Console::output("Дубликатов нет. Время работы скрипта: " . (time() - $startTime));
            return ExitCode::OK;
        }
        Console::output("Дубликаты получены. Время работы скрипта: " . (time() - $startTime));
        foreach ($duplicatesGroups as $duplicateGroup) {
            $this->actionMergeOwnersGroup($duplicateGroup);
            Console::output("Полный цикл склейки владельца и питомцев над группой дублей $duplicateGroup->id проведен. Время работы скрипта: " . (time() - $startTime));
            if (time() - $startTime > static::WORK_TIME) return ExitCode::OK;
        }
        return ExitCode::OK;
    }

    /**
     * @var ActiveRecord $entity
     * @var string $field
     * @var ActiveRecord[] $relations
     * @var DuplicatesGroups $duplicatesGroup
     */
    private static function changeEntityRelations($entity, $field, $relations, &$duplicatesGroup)
    {
        /**
         * @var ActiveRecord $relation
         */
        foreach ($relations as $relation) {
            $relation->detachBehavior('timestamp');
            if (get_class($relation) === PetsToOwner::class) {
                $exists = PetsToOwner::find()->where(['id_pet' => $entity->id, 'id_owner' => $relation->id_owner])->exists();
                if ($exists) {
                    Console::output("Связь уже существует для питомца $entity->id и владельца $relation->id_owner. Удаляем связь с дублем.");
                    $relation->delete();
                    continue;
                }
            }
            $relation->{$field} = $entity->id;
            if (!$relation->save()) {
                Console::error("Ошибка сохранения" . get_class($relation) .  " $relation->id");
                Console::error("Ошибки: " . print_r($relation->getErrors(), true));
                $duplicatesGroup->status = 'fail';
                $duplicatesGroup->comment = get_class($relation) . '_save_error';
                throw new Exception("Save error");
            }
        }
        return true;
    }

    private static function addDescription($field, $mainValue, &$description, $label)
    {
        $getAnotherItemsString = function ($field, $mainValue) {
            $filteredKeys = array_reduce(array_keys($field), function ($carry, $key) use ($field, $mainValue) {
                if ($key != $mainValue && !in_array($key, ['-', '', null])) {
                    $carry[] = $key;
                }
                return $carry;
            }, []);
            return implode(', ', $filteredKeys);
        };
        if (sizeof($field) > 2) {
            if (!mb_strlen($description)) $description .= "\nЗначения из архивных карт-дублей:\n";
            $description .= "$label: {$getAnotherItemsString($field,$mainValue)}\n";
        }
        return $description;
    }
}
