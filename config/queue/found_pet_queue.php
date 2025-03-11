<?php

return [
    'class' => \app\modules\foundPet\queue\Queue::class,
    'as log' => \yii\queue\LogBehavior::class,
    'db' => 'db',
    'tableName' => '{{%found_pet.queue}}',
    'channel' => 'found_pet',
    'mutex' => \yii\mutex\PgsqlMutex::class,
    'mutexTimeout' => 0,
    'serializer' => \app\modules\foundPet\queue\JobSerializer::class,
    'strictJobType' => false
];
