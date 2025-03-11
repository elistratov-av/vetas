<?php

return [
    'class' => \yii\queue\db\Queue::class,
    'as log' => \yii\queue\LogBehavior::class,
    'db' => 'db', // DB connection component or its config
    'tableName' => '{{%queue}}', // Table name
    'channel' => 'soap_v2', // Queue channel key
    'mutex' => \yii\mutex\PgsqlMutex::class, // Mutex used to sync queries
    'mutexTimeout' => 0,
    'serializer' => \app\modules\soap\v2\queue\ETPJobSerializer::class,
    'strictJobType' => false
];
