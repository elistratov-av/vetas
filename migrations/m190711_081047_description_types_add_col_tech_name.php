<?php

use app\commands\migrate\Migration;
use yii\helpers\Console;

/**
 * Class m190711_081047_description_types_add_col_tech_name
 */
class m190711_081047_description_types_add_col_tech_name extends Migration
{
    protected $from_prod = [
        ['Симпотмы','visit','','VISIT_SIMPOTMY'],
        ['Предварительнвй диагноз','visit','','VISIT_PREDVARITELNVJ_DIAGNOZ'],
        ['Рекомендации','visit','','VISIT_REKOMENDATSII'],
        ['Анамнез','visit','1','VISIT_ANAMNEZ_1'],
        ['Симптомы','visit','2','VISIT_SIMPTOMY_2'],
        ['Предварительный диагноз','visit','3','VISIT_PREDVARITELNYJ_DIAGNOZ_3'],
        ['Заключительный диагноз','visit','4','VISIT_ZAKLYUCHITELNYJ_DIAGNOZ_4'],
        ['Схема лечения','visit','5','VISIT_SKHEMA_LECHENIYA_5'],
        ['Рекомендации ','visit','6','VISIT_REKOMENDATSII_6'],
        ['Этиопатогенез и особенности','disease','1','DISEASE_ETIOPATOGENEZ_I_OSOBENNOSTI_1'],
        ['Этиопатогенез','disease','2','DISEASE_ETIOPATOGENEZ_2'],
        ['Особенности','disease','3','DISEASE_OSOBENNOSTI_3'],
        ['Патогенез','disease','4','DISEASE_PATOGENEZ_4'],
        ['Этиология','disease','5','DISEASE_ETIOLOGIYA_5'],
        ['Суммарная клиника','disease','6','DISEASE_SUMMARNAYA_KLINIKA_6'],
        ['Клиника','disease','7','DISEASE_KLINIKA_7'],
        ['Симптомы','disease','8','DISEASE_SIMPTOMY_8'],
        ['Клинические признаки','disease','9','DISEASE_KLINICHESKIE_PRIZNAKI_9'],
        ['Клиническая лаборатория','disease','10','DISEASE_KLINICHESKAYA_LABORATORIYA_10'],
        ['Визуализация','disease','11','DISEASE_VIZUALIZATSIYA_11'],
        ['Диагноз','disease','12','DISEASE_DIAGNOZ_12'],
        ['Общий анализ крови / биохимия крови / анализ мочи','disease','13','DISEASE_OBSHCHIJ_ANALIZ_KROVI_BIOKHIMIYA_KROVI_ANALIZ_MOCHI_13'],
        ['Признаки','disease','14','DISEASE_PRIZNAKI_14'],
        ['Хирургические учёты','disease','15','DISEASE_KHIRURGICHESKIE_UCHJOTY_15'],
        ['Диагностические исследования','disease','16','DISEASE_DIAGNOSTICHESKIE_ISSLEDOVANIYA_16'],
        ['Факторы риска','disease','17','DISEASE_FAKTORY_RISKA_17'],
        ['Причины','disease','18','DISEASE_PRICHINY_18'],
        ['Другие лабораторные тесты','disease','19','DISEASE_DRUGIE_LABORATORNYE_TESTY_19'],
        ['Возможные взаимодействия','disease','20','DISEASE_VOZMOZHNYE_VZAIMODEJSTVIYA_20'],
        ['Возможные осложнения','disease','21','DISEASE_VOZMOZHNYE_OSLOZHNENIYA_21'],
        ['Двигательная активность','disease','22','DISEASE_DVIGATELNAYA_AKTIVNOST_22'],
        ['Дифференциальный диагноз','disease','23','DISEASE_DIFFERENTSIALNYJ_DIAGNOZ_23'],
        ['Затронутые системы','disease','24','DISEASE_ZATRONUTYE_SISTEMY_24'],
        ['Медикаменты выбора','disease','25','DISEASE_MEDIKAMENTY_VYBORA_25'],
        ['Патологические изменения','disease','26','DISEASE_PATOLOGICHESKIE_IZMENENIYA_26'],
        ['Частота проявления / Преваленция','disease','27','DISEASE_CHASTOTA_PROYAVLENIYA_PREVALENTSIYA_27'],
        ['Патоанатомические изменения','disease','28','DISEASE_PATOANATOMICHESKIE_IZMENENIYA_28'],
        ['Превенция','disease','29','DISEASE_PREVENTSIYA_29'],
        ['Лечение, развитие и прогноз','disease','30','DISEASE_LECHENIE_RAZVITIE_I_PROGNOZ_30'],
        ['Лечение','disease','31','DISEASE_LECHENIE_31'],
        ['Развитие','disease','32','DISEASE_RAZVITIE_32'],
        ['Прогноз','disease','33','DISEASE_PROGNOZ_33'],
        ['Развитие и прогноз','disease','34','DISEASE_RAZVITIE_I_PROGNOZ_34'],
        ['Ожидаемое развитие и прогноз','disease','35','DISEASE_OZHIDAEMOE_RAZVITIE_I_PROGNOZ_35'],
        ['Диета','disease','36','DISEASE_DIETA_36'],
        ['Профилактика','disease','37','DISEASE_PROFILAKTIKA_37'],
        ['Стационарный уход','disease','38','DISEASE_STATSIONARNYJ_UKHOD_38'],
        ['Мониторинг пациента','disease','39','DISEASE_MONITORING_PATSIENTA_39'],
        ['Особые указания','disease','40','DISEASE_OSOBYE_UKAZANIYA_40'],
        ['Патофизиология','disease','41','DISEASE_PATOFIZIOLOGIYA_41'],
        ['Породная предрасположенность','disease','42','DISEASE_PORODNAYA_PREDRASPOLOZHENNOST_42'],
        ['Породы','disease','43','DISEASE_PORODY_43'],
        ['Генетика, наследственность','disease','44','DISEASE_GENETIKA_NASLEDSTVENNOST_44'],
        ['Восприимчивость','disease','45','DISEASE_VOSPRIIMCHIVOST_45'],
        ['Противопоказания','disease','46','DISEASE_PROTIVOPOKAZANIYA_46'],
        ['Соответствующий уход','disease','47','DISEASE_SOOTVETSTVUYUSHCHIJ_UKHOD_47'],
        ['Альтернативные медикаменты','disease','48','DISEASE_ALTERNATIVNYE_MEDIKAMENTY_48'],
        ['Предосторожности','disease','49','DISEASE_PREDOSTOROZHNOSTI_49'],
        ['Сопровождающие состояния (патологические осложнения)','disease','50','DISEASE_SOPROVOZHDAYUSHCHIE_SOSTOYANIYA_PATOLOGICHESKIE_OSLOZHNENIYA_50'],
        ['Сопровождающие факторы и заболевания','disease','51','DISEASE_SOPROVOZHDAYUSHCHIE_FAKTORY_I_ZABOLEVANIYA_51'],
        ['Сопровождающие состояния','disease','52','DISEASE_SOPROVOZHDAYUSHCHIE_SOSTOYANIYA_52'],
        ['Причины и факторы риска','disease','53','DISEASE_PRICHINY_I_FAKTORY_RISKA_53'],
        ['Беременность','disease','54','DISEASE_BEREMENNOST_54'],
        ['Информирование клиента','disease','55','DISEASE_INFORMIROVANIE_KLIENTA_55'],
        ['Смотри также','disease','56','DISEASE_SMOTRI_TAKZHE_56'],
        ['Сокращения','disease','57','DISEASE_SOKRASHCHENIYA_57'],
        ['Синонимы','disease','58','DISEASE_SINONIMY_58'],
        ['Библиография','disease','59','DISEASE_BIBLIOGRAFIYA_59'],
        ['Показания к применению','drug','','DRUG_POKAZANIYA_K_PRIMENENIYU'],
        ['Порядок применения','drug','','DRUG_PORYADOK_PRIMENENIYA'],
        ['Побочные эффекты','drug','','DRUG_POBOCHNYE_EFFEKTY'],
        ['Противопоказания к применению','drug','','DRUG_PROTIVOPOKAZANIYA_K_PRIMENENIYU'],
        ['Особые указания и меры личной профилактики','drug','','DRUG_OSOBYE_UKAZANIYA_I_MERY_LICHNOJ_PROFILAKTIKI'],
        ['Условия хранения и сроки годности','drug','','DRUG_USLOVIYA_KHRANENIYA_I_SROKI_GODNOSTI'],
        ['Показания к применению','vaccine','','VACCINE_POKAZANIYA_K_PRIMENENIYU'],
        ['Порядок применения','vaccine','','VACCINE_PORYADOK_PRIMENENIYA'],
        ['Побочные эффекты','vaccine','','VACCINE_POBOCHNYE_EFFEKTY'],
        ['Противопоказания к применению','vaccine','','VACCINE_PROTIVOPOKAZANIYA_K_PRIMENENIYU'],
        ['Особые указания и меры личной профилактики','vaccine','','VACCINE_OSOBYE_UKAZANIYA_I_MERY_LICHNOJ_PROFILAKTIKI'],
        ['Условия хранения и сроки годности','vaccine','','VACCINE_USLOVIYA_KHRANENIYA_I_SROKI_GODNOSTI'],
        ['Дата заболевания','visit','','VISIT_DATA_ZABOLEVANIYA'],
        ['Дополнительные исследования','visit','','VISIT_DOPOLNITELNYE_ISSLEDOVANIYA'],
        ['Клинические признаки','visit','','VISIT_KLINICHESKIE_PRIZNAKI'],
        ['Лечебная помощь','visit','','VISIT_LECHEBNAYA_POMOSHCH'],
        ['Заключение','visit','','VISIT_ZAKLYUCHENIE']
    ];


    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        /*
         * Новый столбец
         */
        $this->addColumn(
            'description_types',
            'tech_name',
            $this ->string(128)
        );
        $this->addCommentOnColumn(
            'description_types',
            'tech_name',
            'Текстовая константа'
        );

        /*
         * Для известных проставляем из массива
         */
        foreach ($this->from_prod as $row){
             $this->update(
                 'description_types',
                 ['tech_name' => $row[3]],
                 [
                     'name' => $row[0],
                     'entity_type' => $row[1]
                 ]
             );
        }

        /*
         * Для неизвестных - генерируем
         */
        $without_tech_name = (new \yii\db\Query())
            ->select(['id', 'entity_type', 'name'])
            ->from('description_types')
            ->where([
                'tech_name' => null
            ])->all();

        foreach ($without_tech_name as $row) {
            $tech_name = $this->generateTechName($row);
            Console::output('New tech_name: ' . $tech_name);

            $this->update(
                'description_types',
                ['tech_name' => $tech_name],
                ['id' => $row['id']]
            );
        }

        /*
         * Уникальность и not null
         */
        $this->execute('ALTER TABLE description_types ALTER COLUMN tech_name SET NOT NULL');

        $this->createIndex(
            'uniq_description_types_tech_name',
            'description_types',
            'tech_name',
            true
        );

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex(
            'uniq_description_types_tech_name',
            'description_types'
        );
        $this->dropColumn(
            'description_types',
            'tech_name'
        );
    }


    /**
     * @param array $row
     * @return string
     */
    protected function generateTechName($row)
    {
        $description  = mb_strtoupper(
            $this->clearString(
                $this->transliterate($row['name']
                )
            )
        );
        $type = mb_strtoupper(
            $this->clearString($row['entity_type'])
        );

        $max_len = 128 - (strlen($row['id']) + strlen($row['entity_type']) + 3); //три подчеркивания

        return $type . '_' . substr($description,0, $max_len) . '_' .$row['id'];

    }

    /**
     * @param string $str
     * @return string|string[]|null
     */
    protected function clearString($str)
    {
        return preg_replace("/[^a-zA-Z0-9]/", "_", $str);
    }

    /**
     * @param string $str
     * @return string
     */
    protected function transliterate($str)
    {
        $str = mb_strtolower($str);

        $transliteration = [
            'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Ґ' => 'G', 'Д' => 'D', 'Е' => 'E', 'Є' => 'E', 'Э' => 'E',
            'Ё' => 'JO', 'Ж' => 'ZH', 'З' => 'Z', 'И' => 'I', 'Ї' => 'JI', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M',
            'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'KH',
            'Ц' => 'TS', 'Ч' => 'CH', 'Ш' => 'SH', 'Щ' => 'SHCH', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Ю' => 'YU',
            'Я' => 'YA',
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'ґ' => 'g', 'д' => 'd', 'е' => 'e', 'є' => 'e', 'э' => 'e',
            'ё' => 'jo', 'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'ї' => 'ji', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm',
            'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'kh',
            'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'ю' => 'yu',
            'я' => 'ya',
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'Ç' => 'C',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ő' => 'O',
            'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U', 'Ý' => 'Y', 'Þ' => 'TH',
            'ß' => 'ss',
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ő' => 'o',
            'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y', 'þ' => 'th',
            'ÿ' => 'y',
        ];

        return strtr($str, $transliteration);
    }



    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190711_081047_description_types_add_col_tech_name cannot be reverted.\n";

        return false;
    }
    */
}
