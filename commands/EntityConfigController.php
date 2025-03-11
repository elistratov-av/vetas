<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\helpers\Console;

class EntityConfigController extends Controller
{

    //сохраняет файл в корневую директорию проекта
    public function actionDump(string $filename)
    {
        $entities = \Yii::$container->get('entityManager')->getRawConfig();
        $output = var_export($entities, true);
        $output = preg_replace('#(?:\A|\n)([ ]*)array \(#i', '[', $output); // Starts
        $output = preg_replace('#\n([ ]*)\),#', "\n$1],", $output); // Ends
        $output = preg_replace('#=> \[\n\s+\],\n#', "=> [],\n", $output); // Empties
        $output = preg_replace('#\)$#', "]", $output);
        $output = preg_replace('#(\d+ => )#', "", $output);
        $output = "<?php\nreturn " . $output . ';';

        file_put_contents($filename . '.php', $output);
        Console::output(Console::ansiFormat("Файл $filename.php сохранен сохранен в текущей директории"));
    }

    public function actionSplit()
    {
        try {
            $output_dir = \Yii::getAlias('@app') . '/config/entities';

            $data = require $config_file;

            $names = array_keys($data);

            foreach ($names as $name) {
                $arr = [$name => $data[$name]];
                $output = var_export($arr, true);
                $output = preg_replace('#(?:\A|\n)([ ]*)array \(#i', '[', $output); // Starts
                $output = preg_replace('#\n([ ]*)\),#', "\n$1],", $output); // Ends
                $output = preg_replace('#=> \[\n\s+\],\n#', "=> [],\n", $output); // Empties
                $output = preg_replace('#\)$#', "]", $output);
                $output = preg_replace('#(\d+ => )#', "", $output);
                $output = preg_replace('#(  )#', "    ", $output);
                $output = "<?php\nreturn " . $output . ';';
                $res = file_put_contents($output_dir . DIRECTORY_SEPARATOR . $name . '.php', $output);
            }

            if ($res !== false) {
                Console::output(Console::ansiFormat("Конфиг сущностей разделен. Файлы результата созданы в $output_dir"));
            }
        } catch (\Exception $e) {
            Console::output(Console::ansiFormat($e->getMessage()));
        }
    }

    public function actionPrint()
    {
        /** @var \app\common\components\entity\EntityManager $em */
        $em = \Yii::$container->get('entityManager');
        $attrs = [];
        $rels = [];
        foreach ($em->getAllEntitiesNames()  as $name) {
            /** @var \app\common\components\entity\EntityInstance $ent */
            $ent = $em->getEntity($name);
            Console::output("\n".$name);
            foreach ((array)$ent->getAttributes() as $attr ){
                Console::output("  ".$attr['name']."\t\t".$attr['type']);
            }
            foreach ((array)$ent->getRelationships() as $rels ){
                Console::output("  ".$rels['link']."\t\t".$rels['property']);
            }

        }
    }
}
