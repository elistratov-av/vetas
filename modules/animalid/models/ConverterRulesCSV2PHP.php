<?php


namespace app\modules\animalid\models;


use yii\base\DynamicModel;
use yii\helpers\Console;

class ConverterRulesCSV2PHP
{
    const
        COL_VETAS_SPECIES_ID = 0,
        COL_VETAS_SPECIES_NAME = 1,

        COL_VETAS_BREED_ID = 2,
        COL_VETAS_BREED_NAME = 3,

        COL_ANIMALID_SPECIES_ID = 5,
        COL_ANIMALID_SPECIES_NAME = 6,

        COL_ANIMALID_BREED_ID = 7,
        COL_ANIMALID_BREED_NAME = 8,

        COL_FILTER_FLAG = 9,
        COL_EXCLUDE_ON_PROCESS_FLAG = 10 // пропустить при заливке правил
    ;

    const FIELD_KIND = 'Kind';
    const FIELD_KIND_ID = 'KindId';
    const FIELD_BREED = 'Breed';
    const FIELD_BREED_ID = 'BreedId';

    const DIRECTION_IN = 'in';
    const DIRECTION_OUT = 'out';

    protected $rules_in = 0;
    protected $rules_out = 0;

    protected $filters_in = 0;
    protected $filters_out = 0;

    public function convert($file)
    {
        $data = $this->parseCsv($file);

        Console::output(Console::ansiFormat('------- CONVERT RULES -------', [Console::FG_GREEN]));
        $convert_rules = $this->parseConvertRules($data);
        Console::output(Console::ansiFormat('------- FILTER RULES -------', [Console::FG_GREEN]));
        $filter_rules = $this->parseFilterRules($data);

        Console::output(Console::ansiFormat('========== RESULT ==========', [Console::FG_GREEN]));

        Console::output('Convert rules IN: ' . $this->rules_in);
        Console::output('Convert rules OUT: ' . $this->rules_out);

        Console::output('Filters IN: ' . $this->filters_in);
        Console::output('Filters OUT: ' . $this->filters_out);

        return [
            'filter' => $filter_rules,
            'convert' => $convert_rules,
        ];
    }

    /**
     * Парсит правила конвертации
     * @param $data
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    protected function parseFilterRules($data)
    {
        $filter_in = [
            'species' => [],
            'breeds' => [],
        ];
        $filter_out = [
            'species' => [],
            'breeds' => [],
        ];

        /**
         * Convert
         */
        foreach ($data as $key => $row) {
            $row = $this->checkAndFormatRow($key, $row);
            if (!$row) {
                continue;
            }

            if ($row[self::COL_EXCLUDE_ON_PROCESS_FLAG] == 't') {
                $this->msgAboutSkipRow($key, 'Skipped by flag COL_EXCLUDE_ON_PROCESS_FLAG');
                continue;
            }

            if ($row[self::COL_FILTER_FLAG] == 'f') {
                continue;
            }

            /*
             * Разбираемся, это правило фильтрации на вход или выход
             */
            if ((!empty($row[self::COL_VETAS_BREED_ID]) || !empty($row[self::COL_VETAS_SPECIES_ID]))
                && (!empty($row[self::COL_ANIMALID_BREED_ID]) || !empty($row[self::COL_ANIMALID_SPECIES_ID]))) {
                /*
                 *  непонятный фильтр у которого указаны
                 *   оба вида
                 *      или
                 *  обе породы
                 *      или
                 * порода и вид, но по разные стороны (animalid и vetas)
                 */
                $this->msgAboutSkipRow($key, 'Unknown direction for filter rule');
                continue;

            } elseif (!empty($row[self::COL_VETAS_BREED_ID]) || !empty($row[self::COL_VETAS_SPECIES_ID])) {
                $direction = self::DIRECTION_OUT;
            } elseif (!empty($row[self::COL_ANIMALID_BREED_ID]) || !empty($row[self::COL_ANIMALID_SPECIES_ID])) {
                $direction = self::DIRECTION_IN;
            } else {
                $this->msgAboutSkipRow($key, 'Unknown direction for filter rule');
                continue;
            }


            switch ($direction) {
                case self::DIRECTION_OUT:
                    if (!empty($row[self::COL_VETAS_BREED_ID]) && !empty($row[self::COL_VETAS_SPECIES_ID])) {
                        $species_key = $row[self::COL_VETAS_SPECIES_ID];
                        $breeds_key = $row[self::COL_VETAS_BREED_ID];
                        $filter_out[$species_key][$breeds_key] = true;
                        $this->filters_out++;
                    } elseif (!empty($row[self::COL_VETAS_BREED_ID])) {
                        $filter_out['breeds'][] = $row[self::COL_VETAS_BREED_ID];
                        $this->filters_out++;
                    } elseif (!empty($row[self::COL_VETAS_SPECIES_ID])) {
                        $filter_out['species'][] = $row[self::COL_VETAS_SPECIES_ID];
                        $this->filters_out++;
                    }
                    break;
                case self::DIRECTION_IN:
                    if (!empty($row[self::COL_ANIMALID_BREED_ID]) && !empty($row[self::COL_ANIMALID_SPECIES_ID])) {
                        $species_key = $row[self::COL_ANIMALID_SPECIES_ID];
                        $breeds_key = $row[self::COL_ANIMALID_BREED_ID];
                        $filter_in[$species_key][$breeds_key] = true;
                        $this->filters_in++;
                    } elseif (!empty($row[self::COL_ANIMALID_BREED_ID])) {
                        $filter_in['breeds'][] = $row[self::COL_ANIMALID_BREED_ID];
                        $this->filters_in++;
                    } elseif (!empty($row[self::COL_ANIMALID_SPECIES_ID])) {
                        $filter_in['species'][] = $row[self::COL_ANIMALID_SPECIES_ID];
                        $this->filters_in++;
                    }
                    break;
            }
        }

        return [
            self::DIRECTION_IN => $filter_in,
            self::DIRECTION_OUT => $filter_out,
        ];
    }

    /**
     * Парсит правила конвертации
     * @param $data
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    protected function parseConvertRules($data)
    {
        $convert_in = [];
        $convert_out = [];

        /**
         * Convert
         */
        foreach ($data as $key => $row) {
            $row = $this->checkAndFormatRow($key, $row);
            if (!$row) {
                continue;
            }

            if ($row[self::COL_EXCLUDE_ON_PROCESS_FLAG] == 't') {
                $this->msgAboutSkipRow($key, 'Skipped by flag COL_EXCLUDE_ON_PROCESS_FLAG');
                continue;
            }

            if ($row[self::COL_FILTER_FLAG] == 't') {
                $this->msgAboutSkipRow($key, 'Skipped by flag COL_FILTER_FLAG');
                continue;
            }

            /*
             * IN (от них к нам)
             */
            $species_key = $row[self::COL_ANIMALID_SPECIES_ID];
            $breeds_key = $row[self::COL_ANIMALID_BREED_ID];

            // Дубликат или совпадает по ключам
            if (!empty($convert_in[$species_key][$breeds_key])) {
                $msg = 'Duplicate[IN]: Species : ' . $species_key . ' Breeds: ' . $breeds_key
                    . PHP_EOL
                    . var_export($convert_in[$species_key][$breeds_key], true)
                    . PHP_EOL
                    . var_export($row, true);
                $this->msgAboutSkipRow($key, $msg);
            } else {
                $convert_in[$species_key][$breeds_key] = [
                    self::FIELD_KIND_ID => $row[self::COL_VETAS_SPECIES_ID],
                    self::FIELD_KIND => $row[self::COL_VETAS_SPECIES_NAME],
                    self::FIELD_BREED_ID => $row[self::COL_VETAS_BREED_ID],
                    self::FIELD_BREED => $row[self::COL_VETAS_BREED_NAME]
                ];
                $this->rules_in++;
            }

            /*
             * OUT (от нас к ним)
             */
            $species_key = $row[self::COL_VETAS_SPECIES_ID];
            $breeds_key = $row[self::COL_VETAS_BREED_ID];
            // Дубликат или совпадает по ключам
            if (!empty($convert_out[$species_key][$breeds_key])) {
                $msg = 'Duplicate[OUT]: Species: ' . $species_key . ' Breeds: ' . $breeds_key
                    . PHP_EOL
                    . var_export($convert_out[$species_key][$breeds_key], true)
                    . PHP_EOL
                    . var_export($row, true);
                $this->msgAboutSkipRow($key, $msg);
            } else {
                $convert_out[$species_key][$breeds_key] = [
                    self::FIELD_KIND_ID => $row[self::COL_ANIMALID_SPECIES_ID],
                    self::FIELD_KIND => $row[self::COL_ANIMALID_SPECIES_NAME],
                    self::FIELD_BREED_ID => $row[self::COL_ANIMALID_BREED_ID],
                    self::FIELD_BREED => $row[self::COL_ANIMALID_BREED_NAME]
                ];
                $this->rules_out++;
            }
        }

        return [
            self::DIRECTION_IN => $convert_in,
            self::DIRECTION_OUT => $convert_out
        ];
    }

    /**
     * @param $key
     * @param $row
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    protected function checkAndFormatRow($key, $row)
    {
        /*
         * To INT
         */
        $row = $this->arrayValuesTextToInt($row);

        $data = [
            'COL_VETAS_SPECIES_ID' => $row[self::COL_VETAS_SPECIES_ID],
            'COL_VETAS_SPECIES_NAME' => $row[self::COL_VETAS_SPECIES_NAME],

            'COL_VETAS_BREED_ID' => $row[self::COL_VETAS_BREED_ID],
            'COL_VETAS_BREED_NAME' => $row[self::COL_VETAS_BREED_NAME],

            'COL_ANIMALID_SPECIES_ID' => $row[self::COL_ANIMALID_SPECIES_ID],
            'COL_ANIMALID_SPECIES_NAME' => $row[self::COL_ANIMALID_SPECIES_NAME],

            'COL_ANIMALID_BREED_ID' => $row[self::COL_ANIMALID_BREED_ID],
            'COL_ANIMALID_BREED_NAME' => $row[self::COL_ANIMALID_BREED_NAME],

            'COL_FILTER_FLAG' => $row[self::COL_FILTER_FLAG],
            'COL_EXCLUDE_ON_PROCESS_FLAG' => $row[self::COL_EXCLUDE_ON_PROCESS_FLAG],
        ];

        /*
         * Сначала флаги
         */
        $model = DynamicModel::validateData($data, [
            [
                [
                    'COL_FILTER_FLAG', 'COL_EXCLUDE_ON_PROCESS_FLAG',
                ],
                'in', 'range' => ['t', 'f'], 'strict' => true,
            ],
        ]);

        if ($model->hasErrors()) {
            $errors = $model->getErrorSummary(true);
            return $this->msgAboutSkipRow($key, empty($errors) ? 'Ошибка:' : implode("\n", array_values($errors)));
        }


        $validate_rules = ($row[self::COL_FILTER_FLAG] == 't') ?
            [ // правило фильтрации
                [
                    [
                        'COL_VETAS_SPECIES_ID',
                        'COL_VETAS_SPECIES_NAME',
                    ],
                    'required',
                    'when' => function ($model) {
                        return !empty($model[self::COL_VETAS_SPECIES_ID]);
                    },
                ],
                [
                    [
                        'COL_VETAS_BREED_ID',
                        'COL_VETAS_BREED_NAME',
                    ],
                    'required',
                    'when' => function ($model) {
                        return !empty($model[self::COL_VETAS_BREED_ID]);
                    },
                ],
                [
                    ['COL_VETAS_SPECIES_ID'],
                    'integer',
                    'when' => function ($model) {
                        return !empty($model[self::COL_VETAS_SPECIES_ID]);
                    },
                ],
                [
                    ['COL_VETAS_BREED_ID'],
                    'integer',
                    'when' => function ($model) {
                        return !empty($model[self::COL_VETAS_BREED_ID]);
                    },
                ],

            ]
            :
            [   // Правило конвертации
                [
                    [
                        'COL_VETAS_SPECIES_ID', 'COL_VETAS_BREED_ID',
                        'COL_ANIMALID_SPECIES_ID', 'COL_ANIMALID_BREED_ID',
                    ],
                    'integer'
                ],
                [
                    [
                        'COL_VETAS_SPECIES_ID', 'COL_VETAS_SPECIES_NAME',
                        'COL_VETAS_BREED_ID', 'COL_VETAS_BREED_NAME',
                        'COL_ANIMALID_SPECIES_ID', 'COL_ANIMALID_SPECIES_NAME',
                        'COL_ANIMALID_BREED_ID', 'COL_ANIMALID_BREED_NAME',
                        'COL_FILTER_FLAG', 'COL_EXCLUDE_ON_PROCESS_FLAG',
                    ],
                    'required',
                ]
            ];


        $model = DynamicModel::validateData($data, $validate_rules);

        if ($model->hasErrors()) {
            $errors = $model->getErrorSummary(true);
            return $this->msgAboutSkipRow($key, empty($errors) ? 'Ошибка:' : implode("\n", array_values($errors)));
        }

        return $row;
    }

    /**
     * После парсинга CSV int оказывается строкой. Исправляем это
     * @param $row
     * @return mixed
     */
    protected function arrayValuesTextToInt($row)
    {
        foreach ($row as $col_key => $col) {
            if (preg_match('~^\d{1,10}$~', $row[$col_key])) {
                $row[$col_key] = (int)$row[$col_key];
            }
        }

        return $row;
    }

    /**
     * Выводит сообщение в консоль о пропуске строки и причине
     * @param $key
     * @param $msg
     * @return bool
     */
    protected function msgAboutSkipRow($key, $msg)
    {
        Console::output(Console::ansiFormat('Row #' . $key . ' skipped. ', [Console::FG_RED]));
        Console::output($msg);
        return false;
    }

    /**
     * Разбирает CSV
     * @param $file
     * @return array
     */
    protected function parseCsv($file)
    {
        $parseCsv = function ($handle) {
            $data = [];
            while (($row = fgetcsv($handle, 1000, ",")) !== false) {
                if (!empty($row[0])) {
                    $data[] = $row;
                }
            }

            return $data;
        };

        $file = "{$file}";
        if (($handle = fopen($file, "r")) !== false) {
            $result = $parseCsv($handle);
        } else {
            Console::output(Console::ansiFormat("Файл {$file} не существует", [
                Console::FG_RED, Console::BOLD
            ]));
        }
        fclose($handle);

        return $result;
    }
}