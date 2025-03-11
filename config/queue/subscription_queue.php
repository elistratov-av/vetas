<?php

return [
    'class' => \yii\queue\db\Queue::class,
    'as log' => \yii\queue\LogBehavior::class,
    'db' => 'db', // DB connection component or its config
    'tableName' => '{{%subscription.queue}}', // Table name
    'channel' => 'subscription', // Queue channel key
    'mutex' => \yii\mutex\PgsqlMutex::class, // Mutex used to sync queries
    'mutexTimeout' => 0,
    //'serializer' => \app\modules\soap\queue\ETPJobSerializer::class,
    'strictJobType' => false
];
