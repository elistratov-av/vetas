<?php
require __DIR__ . '/../../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

return [
    'debug' => '0',
    'title' => 'Комитет ветеринарии',
    'version' => '1.0',
    'host' => $_ENV['DB_HOST'],
    'port' => $_ENV['DB_PORT'],
    'dbname' => $_ENV['DB_NAME'],
    'user' => $_ENV['DB_USER'],
    'password' => $_ENV['DB_PASSWORD'],
    'token_time' => '3600',
    'path_yii' => '/../../vendor/yiisoft/yii2/Yii.php',
    'path_autoload' => '/../../vendor/autoload.php',
    'path_yii_config' => '/../../config/config.php',
    'apidatamos_key' => '5536906602ea1c2f2d4ecbcdcce3e582',
    'api_url' => $_ENV['API_URL'],
    'api_mos' => $_ENV['API_MOS'],
    'api_mos_key' => $_ENV['API_MOS_KEY'],
    'env' => 'PROD',
];

?>
