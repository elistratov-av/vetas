<?php

use yii\db\Migration;

/**
 * Class m180807_094208_update_rbac_data
 */
class m180807_094208_update_rbac_data extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        $permission = $auth->getPermission('referenceSpecializationsRead');
        $permission->description = '[R] Справочник специализаций';
        $auth->update('referenceSpecializationsRead', $permission);

        $permission = $auth->getPermission('referenceReasonsRemoveRegistrationWrite');
        $permission->name = 'referenceRegExpireReasonsWrite';
        $auth->update('referenceReasonsRemoveRegistrationWrite', $permission);
        $permission = $auth->getPermission('referenceReasonsRemoveRegistrationRead');
        $permission->name = 'referenceRegExpireReasonsRead';
        $auth->update('referenceReasonsRemoveRegistrationRead', $permission);

        $permission = $auth->getPermission('referenceTypesTmcWrite');
        $permission->name = 'referenceTmcTypesWrite';
        $auth->update('referenceTypesTmcWrite', $permission);
        $permission = $auth->getPermission('referenceTypesTmcRead');
        $permission->name = 'referenceTmcTypesRead';
        $auth->update('referenceTypesTmcRead', $permission);

        $permission = $auth->getPermission('referencePreparationsWrite');
        $permission->name = 'referenceDrugsWrite';
        $auth->update('referencePreparationsWrite', $permission);
        $permission = $auth->getPermission('referencePreparationsRead');
        $permission->name = 'referenceDrugsRead';
        $auth->update('referencePreparationsRead', $permission);

        $permission = $auth->getPermission('manageReceptionWrite');
        $permission->name = 'manageVisitsWrite';
        $auth->update('manageReceptionWrite', $permission);
        $permission = $auth->getPermission('manageReceptionRead');
        $permission->name = 'manageVisitsRead';
        $auth->update('manageReceptionRead', $permission);

        $permission = $auth->getPermission('doReceptionWrite');
        $permission->name = 'doVisitsWrite';
        $auth->update('doReceptionWrite', $permission);
        $permission = $auth->getPermission('doReceptionRead');
        $permission->name = 'doVisitsRead';
        $auth->update('doReceptionRead', $permission);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return true;
    }
}
