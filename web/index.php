<?php
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR);
require __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

defined('YII_DEBUG') or define('YII_DEBUG', $_ENV['YII_DEBUG']);
defined('YII_ENV') or define('YII_ENV', $_ENV['YII_ENV']);

require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../config/config.php';

(new yii\web\Application($config))->run();
