<?php

use app\common\migrate\RbacMigration;

/**
 * Class m210402_084239_new_rbac_permissions
 */
class m210402_084239_new_rbac_permissions extends RbacMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // add role
        $rolesToAdd = [
            [
                'role'            => 'technic_mto',
                'description'     => 'Техник МТО',
                'ext_description' => '',
            ],
        ];
        $this->addRbacRoles($rolesToAdd);

        //2. link permissions
        $permissionData = [];
        foreach ($this->getPermissionNames() as $permissionName) {
            $permissionData[$permissionName] = [
                'roles' => [
                    'technic_mto',
                ]
            ];
        }

        $this->grantPermissions($permissionData);
    }

    /**
     * @return array
     */
    private function getPermissionNames()
    {
        return [
            'activity.visits.manage.menu',
            'data.change_requests.create',
            'data.change_requests.manage',
            'data.change_requests.process',
            'data.classificators.active_substances',
            'data.classificators.active_substances.menu',
            'data.classificators.active_substances.W',
            'data.classificators.breeds',
            'data.classificators.breeds.W',
            'data.classificators.categories',
            'data.classificators.categories.menu',
            'data.classificators.categories.W',
            'data.classificators.diseases',
            'data.classificators.diseases.menu',
            'data.classificators.diseases.W',
            'data.classificators.drugs',
            'data.classificators.drugs.menu',
            'data.classificators.drugs.W',
            'data.classificators.equipments',
            'data.classificators.equipments.menu',
            'data.classificators.equipments.W',
            'data.classificators.exp_materials',
            'data.classificators.exp_materials.menu',
            'data.classificators.exp_materials.W',
            'data.classificators.measures',
            'data.classificators.measures.menu',
            'data.classificators.measures.W',
            'data.classificators.org_types',
            'data.classificators.org_types.menu',
            'data.classificators.org_types.W',
            'data.classificators.reg_expire_reasons',
            'data.classificators.reg_expire_reasons.menu',
            'data.classificators.reg_expire_reasons.W',
            'data.classificators.specializations',
            'data.classificators.specializations.menu',
            'data.classificators.specializations.W',
            'data.classificators.species',
            'data.classificators.species-breeds.menu',
            'data.classificators.species.W',
            'data.classificators.vaccines',
            'data.classificators.vaccines.menu',
            'data.classificators.vaccines.W',
            'data.faqs.manage',
            'data.faqs.manage.menu',
            'data.journals.manage.menu',
            'data.organizations.balance',
            'data.organizations.balance-tmc-disposal',
            'data.organizations.balance.menu',
            'data.organizations.balance.W',
            'data.organizations.manage',
            'data.organizations.manage.menu',
            'data.owners.manage.menu',
            'data.pets.manage.menu',
            'data.pricelist.categories',
            'data.pricelist.categories.menu',
            'data.pricelist.categories.W',
            'data.pricelist.discount.menu',
            'data.pricelist.services',
            'data.pricelist.services.menu',
            'data.pricelist.services.W',
            'data.production_form.edit.menu',
            'data.production_form.tab.menu',
            'data.specialists.manage',
            'data.specialists.manage.menu',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->removeRbacRoles([
            [
                'role' => 'technic_mto',
            ]
        ]);
    }

}
