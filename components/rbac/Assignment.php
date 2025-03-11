<?php

namespace app\common\components\rbac;

/**
 * Class Assignment
 * @package app\common\components\rbac
 */
class Assignment extends \yii\rbac\Assignment
{
    /**
     * @var int user ID (see [[\yii\web\User::id]])
     */
    public $userId;
    /**
     * @var int user ID (see [[\yii\web\User::id]])
     */
    public $specialistId;
    /**
     * @var string the role name
     */
    public $roleName;
    /**
     * @var int UNIX timestamp representing the assignment creation time
     */
    public $createdAt;
}
