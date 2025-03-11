<?php

namespace app\commands;

use app\common\models\UserModel;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

/**
 * Class AddSpecsController
 * @package app\commands
 */
class AddSpecsController extends Controller
{
    /**
     * Пример:
     * ```
     *  > php yii add-specs/add @runtime/upload/specs.csv
     * ```
     * Формат csv:
     * "Черкасова Надежда Владимировна";"f";"здесь были специализации";02.03.1978;"Рублевский ветеринарный участок"
     *
     * @param string $path
     * @return int
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionAdd($path)
    {
        $items = $this->loadData($path);

        if (empty($items)) {
            Console::output(Console::ansiFormat('Error parsing CSV', [Console::FG_RED]));

            return ExitCode::DATAERR;
        }

        $this->resetSequences();

        $organizations = $this->findOrganizations();

        foreach ($items as $item) {
            $fio = $item[0];
            $sex = empty($item[1]) ? 'f' : $item[1];
            $specs = $item[2];
            $birth = $item[3];
            $org = $item[4];

            $fio_arr = explode(' ', $fio);
            $f_fio = trim($fio_arr[0]);
            $i_fio = trim($fio_arr[1]);
            $o_fio = trim($fio_arr[2]);

            $login = ucfirst($this->transliterate($f_fio))
                . ucfirst(mb_substr($this->transliterate($i_fio), 0, 1))
                . ucfirst(mb_substr($this->transliterate($o_fio), 0, 1));

            $account = UserModel::find()
                ->where(['ilike', 'login', $login, false])->limit(1)->one();

            if (!empty($account)) {
                Console::output(Console::ansiFormat('User already exists [ID ' . $account->id . ' - ' . $login . ']', [Console::FG_RED]));

                continue;
            }

            $password = 1234;

            $hash = \Yii::$app->getSecurity()->generatePasswordHash($password);
            $account = new UserModel();
            $account->login = $login;
            $account->password = $hash;
            $account->created_at = date('Y-m-d H:i:s');

            if (!$account->save()) {
                Console::output(Console::ansiFormat('Could not create user [' . $login . ']', [Console::FG_RED]));

                return ExitCode::UNSPECIFIED_ERROR;
            }

            Console::output(Console::ansiFormat('Created user [ID ' . $account->id . ' - ' . $login . ']', [Console::FG_GREEN]));

            $id_user = $account->id;

            $birthday = empty($birth) ? '1977-02-23' : implode('-', array_reverse(explode('.', $birth)));
            $reg_date = date('Y-m-d');
            $id_organization = array_search($org, $organizations);
            $created_at = date('Y-m-d H:i:s');

            $columns = compact('f_fio', 'i_fio', 'o_fio', 'reg_date', 'birthday', 'sex', 'id_organization', 'id_user', 'created_at');

            Console::output(Console::ansiFormat('Creating specialist [' . $fio . ']', [Console::FG_YELLOW]));

            \Yii::$app->db
                ->createCommand()
                ->insert('specialists', $columns)
                ->execute();

            $id_specialist = \Yii::$app->db->getLastInsertID('specialists_id_seq');
        }

        return ExitCode::OK;
    }

    /**
     * @param string $path
     * @return array
     * @throws \Exception
     */
    private function loadData($path)
    {
        $path = \Yii::getAlias($path);

        if (!is_file($path)) {
            Console::output(Console::ansiFormat('CSV file not found [' . $path . ']', [Console::FG_RED]));

            return null;
        }

        $csv = file_get_contents($path, FILE_TEXT);
        if (empty($csv)) {
            Console::output(Console::ansiFormat('CSV file is invalid or empty [' . $path . ']', [Console::FG_RED]));

            return null;
        }

        return $this->parseCsv($csv, __FUNCTION__);
    }

    private function resetSequences()
    {
        $tables = [
            'users',
            'specialists',
        ];

        foreach ($tables as $table) {
            $max = (new Query())->from($table)->max('id');
            $max = (int)$max + 1;
            \Yii::$app->db->createCommand("SELECT pg_catalog.setval('public.{$table}_id_seq', {$max}, false);")->execute();
        }
    }

    /**
     * @param string $csv
     * @param string $function
     * @return array
     * @throws \Exception
     */
    private function parseCsv($csv, $function)
    {
        $arr = [];
        $data = str_getcsv($csv, "\n");
        foreach ($data as $row) {
            $arr[] = str_getcsv($row, ';', '"');
        }

        if (empty($arr)) {
            throw new \Exception($function . ': Failed to parse CSV');
        }

        return $arr;
    }

    private function findOrganizations()
    {
        $rows = (new Query())
            ->select(['id', 'short_name'])
            ->from('organizations')
            ->orderBy(['short_name' => SORT_ASC])
            ->all();

        return ArrayHelper::map($rows, 'id', 'short_name');
    }

    private function transliterate($str)
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
}
