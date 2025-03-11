<?php

use app\models\db\VisitPets;
use app\models\db\Visits;
use app\models\db\Pets;
use app\models\db\Breeds;
use app\models\db\PetOwners;
use app\models\db\ContactTypes;
use app\models\db\Contacts;

$visit = Visits::find()->where(['id' => $data['idVisit']])->one();
$visitPet = VisitPets::find()->where(['id_visit' => $visit->id])->orderBy(['id' => SORT_DESC])->one();
$pet = Pets::find()->where(['id' => $data['pet_id']])->one();

if (empty($pet) || empty($data['pet_id']) || $data['pet_id'] == 0){
    $pet = Pets::find()->where(['id' => $visitPet->id_pet])->one();
}

$breed = Breeds::find()->where(['id' => $pet->id_breed])->one();
$owner = $pet->owner;

$contact = Contacts::find()
    ->where(['entity_type' => 'pet_owner'])
    ->andWhere(['entity_id' => $owner->id])
    ->andWhere(['id_contact_type' => ContactTypes::findOne(['type' => ContactTypes::TYPE_EMAIL])->id])
    ->one();

$org = \app\models\db\Organizations::find()->where(['id' => $visit->id_organization])->one();

$visitSpec = $visit->getVisitsSpecialist()->one();
if (!empty($visitSpec)) {
    $spec = \app\models\db\Specialists::find()->where(['id' => $visitSpec->id_specialist])->one();

    if (!empty($spec)) {
        $spec = \app\models\db\Users::find()->where(['id' => $spec->id_user])->one();
    }
}else{
    $spec = '';
}

?>
<div style="
        background-image: url('<?php echo Yii::$app->basePath . '/web/img/tomogrpahy_report.jpg' ?>');
        background-position: top left;
        background-repeat: no-repeat;
        background-image-resize: 4;
        background-image-resolution: from-image;
        width: 100%;
        height: 100%;
        ">
    <table class="pt-145">
        <tr>
            <td style="width: 361px; padding-left: 97px;">
                <?php echo $pet->name;?>
            </td>
            <td style="width: 123px;">
                <?php echo date_diff(date_create($pet->birthday), date_create('now'))->y;?>
            </td>
            <td style="width: 148px;">
                <?php if ($pet->sex == Pets::SEX_FEMALE):?>
                    Ж
                <?php else:?>
                    М
                <?php endif;?>
            </td>
            <td>
                <?php if (!empty($pet->castrated)):?>
                    Да
                <?php else:?>
                    Нет
                <?php endif;?>
            </td>
        </tr>
    </table>
    <table style="padding-top: 3px;">
        <tr>
            <td style="width: 361px; padding-left: 97px;">
                <?php echo $breed->name;?>
            </td>
            <td style="width: 203px;">
                <?php if (!empty($data['weight'])):?>
                    <?php echo $data['weight'];?>
                <?php else:?>
                    <span class="tw">-</span>
                <?php endif;?>
            </td>
            <td>
                <?php echo date('d.m.y');?>
            </td>
        </tr>
    </table>
    <table style="padding-top: 27px;">
        <tr>
            <td class="pl-218">
                <?php echo $owner['f_fio'] . ' ' . $owner['i_fio'] . ' ' . $owner['o_fio'];?>
            </td>
        </tr>
        <tr>
            <td class="pl-218 pt-6">
                <?php if (!empty($contact)):?>
                    <?php echo $contact->name;?>
                <?php else:?>
                    <span class="tw">-</span>
                <?php endif;?>
            </td>
        </tr>
        <tr>
            <td class="pl-218 pt-6">
                <?php if (!empty($org)):?>
                    <?php echo mb_substr($org->name,0,53);?>
                <?php else:?>
                    <span class="tw">-</span>
                <?php endif;?>
            </td>
        </tr>
        <tr>
            <td class="pl-218 pt-6">
                <?php if (!empty($spec)):?>
                    <?php echo $spec->fullname;?>
                <?php else:?>
                    <span class="tw">-</span>
                <?php endif;?>
            </td>
        </tr>
        <tr>
            <td class="pl-218 pt-6">
                <span class="tw">-</span>
            </td>
        </tr>
    </table>
    <table class="pt-88">
        <tr>
            <td class="h-20 w-241 pl-47 pt-5">
                <?php if (!empty($data['head']) && $data['head'] == 1): ?>
                    <img src="<?php echo Yii::$app->basePath . '/web/img/check1.png' ?>" class="w-20">
                <?php else:?>
                    <img src="<?php echo Yii::$app->basePath . '/web/img/check2.png' ?>" class="w-20">
                <?php endif;?>
            </td>
            <td class="h-20 pt-5">
                <?php if (!empty($data['shoulder']) && $data['shoulder'] == 1): ?>
                    <img src="<?php echo Yii::$app->basePath . '/web/img/check1.png' ?>" class="w-20">
                <?php else:?>
                    <img src="<?php echo Yii::$app->basePath . '/web/img/check2.png' ?>" class="w-20">
                <?php endif;?>
            </td>
        </tr>
        <tr>
            <td class="h-20 w-241 pl-47 pt-5">
                <?php if (!empty($data['breast']) && $data['breast'] == 1): ?>
                    <img src="<?php echo Yii::$app->basePath . '/web/img/check1.png' ?>" class="w-20">
                <?php else:?>
                    <img src="<?php echo Yii::$app->basePath . '/web/img/check2.png' ?>" class="w-20">
                <?php endif;?>
            </td>
            <td class="h-20 pt-5">
                <?php if (!empty($data['elbow']) && $data['elbow'] == 1): ?>
                    <img src="<?php echo Yii::$app->basePath . '/web/img/check1.png' ?>" class="w-20">
                <?php else:?>
                    <img src="<?php echo Yii::$app->basePath . '/web/img/check2.png' ?>" class="w-20">
                <?php endif;?>
            </td>
        </tr>
        <tr>
            <td class="h-20 w-241 pl-47 pt-5">
                <?php if (!empty($data['belly']) && $data['belly'] == 1): ?>
                    <img src="<?php echo Yii::$app->basePath . '/web/img/check1.png' ?>" class="w-20">
                <?php else:?>
                    <img src="<?php echo Yii::$app->basePath . '/web/img/check2.png' ?>" class="w-20">
                <?php endif;?>
            </td>
            <td class="h-20 pt-5">
                <?php if (!empty($data['wrist']) && $data['wrist'] == 1): ?>
                    <img src="<?php echo Yii::$app->basePath . '/web/img/check1.png' ?>" class="w-20">
                <?php else:?>
                    <img src="<?php echo Yii::$app->basePath . '/web/img/check2.png' ?>" class="w-20">
                <?php endif;?>
            </td>
        </tr>
        <tr>
            <td class="h-20 w-241 pl-47 pt-5">
                <?php if (!empty($data['neck']) && $data['neck'] == 1): ?>
                    <img src="<?php echo Yii::$app->basePath . '/web/img/check1.png' ?>" class="w-20">
                <?php else:?>
                    <img src="<?php echo Yii::$app->basePath . '/web/img/check2.png' ?>" class="w-20">
                <?php endif;?>
            </td>
            <td class="h-20 pt-5">
                <?php if (!empty($data['hip']) && $data['hip'] == 1): ?>
                    <img src="<?php echo Yii::$app->basePath . '/web/img/check1.png' ?>" class="w-20">
                <?php else:?>
                    <img src="<?php echo Yii::$app->basePath . '/web/img/check2.png' ?>" class="w-20">
                <?php endif;?>
            </td>
        </tr>
        <tr>
            <td class="h-20 w-241 pl-47 pt-5">
                <?php if (!empty($data['thoracolumbar']) && $data['thoracolumbar'] == 1): ?>
                    <img src="<?php echo Yii::$app->basePath . '/web/img/check1.png' ?>" class="w-20">
                <?php else:?>
                    <img src="<?php echo Yii::$app->basePath . '/web/img/check2.png' ?>" class="w-20">
                <?php endif;?>
            </td>
            <td class="h-20 pt-5">
                <?php if (!empty($data['knee']) && $data['knee'] == 1): ?>
                    <img src="<?php echo Yii::$app->basePath . '/web/img/check1.png' ?>" class="w-20">
                <?php else:?>
                    <img src="<?php echo Yii::$app->basePath . '/web/img/check2.png' ?>" class="w-20">
                <?php endif;?>
            </td>
        </tr>
        <tr>
            <td class="h-20 w-241 pl-47 pt-5">
                <?php if (!empty($data['loin']) && $data['loin'] == 1): ?>
                    <img src="<?php echo Yii::$app->basePath . '/web/img/check1.png' ?>" class="w-20">
                <?php else:?>
                    <img src="<?php echo Yii::$app->basePath . '/web/img/check2.png' ?>" class="w-20">
                <?php endif;?>
            </td>
            <td class="h-20 pt-5">
                <?php if (!empty($data['block']) && $data['block'] == 1): ?>
                    <img src="<?php echo Yii::$app->basePath . '/web/img/check1.png' ?>" class="w-20">
                <?php else:?>
                    <img src="<?php echo Yii::$app->basePath . '/web/img/check2.png' ?>" class="w-20">
                <?php endif;?>
            </td>
        </tr>
    </table>
    <table>
        <tr>
            <td class="h-20" style="width: 512px; padding-left: 243px;">
                <?php if (!empty($data['onkosearch']) && $data['onkosearch'] == 1): ?>
                    <img src="<?php echo Yii::$app->basePath . '/web/img/check1.png' ?>" class="w-20">
                <?php else:?>
                    <img src="<?php echo Yii::$app->basePath . '/web/img/check2.png' ?>" class="w-20">
                <?php endif;?>
            </td>
            <td class="h-20">
                <?php if (!empty($data['contrast']) && $data['contrast'] == 1): ?>
                    <img src="<?php echo Yii::$app->basePath . '/web/img/check1.png' ?>" class="w-20">
                <?php else:?>
                    <img src="<?php echo Yii::$app->basePath . '/web/img/check2.png' ?>" class="w-20">
                <?php endif;?>
            </td>
        </tr>
    </table>
    <table>
        <tr>
            <td class="h-20" style="width: 242px; padding-left: 45px; padding-top: 4px;">
                <?php if (!empty($data['thoracicLimbs']) && $data['thoracicLimbs'] == 1): ?>
                    <img src="<?php echo Yii::$app->basePath . '/web/img/check1.png' ?>" class="w-20">
                <?php else:?>
                    <img src="<?php echo Yii::$app->basePath . '/web/img/check2.png' ?>" class="w-20">
                <?php endif;?>
            </td>
            <td class="h-20" style="width: 147px;  padding-top: 4px;">
                <?php if (!empty($data['lymphan']) && $data['lymphan'] == 1): ?>
                    <img src="<?php echo Yii::$app->basePath . '/web/img/check1.png' ?>" class="w-20">
                <?php else:?>
                    <img src="<?php echo Yii::$app->basePath . '/web/img/check2.png' ?>" class="w-20">
                <?php endif;?>
            </td>
            <td class="h-20" style="width: 123px; padding-top: 4px;">
                <?php if (!empty($data['mielography']) && $data['mielography'] == 1): ?>
                    <img src="<?php echo Yii::$app->basePath . '/web/img/check1.png' ?>" class="w-20">
                <?php else:?>
                    <img src="<?php echo Yii::$app->basePath . '/web/img/check2.png' ?>" class="w-20">
                <?php endif;?>
            </td>
            <td class="h-20" style="width: 90px; padding-top: 4px;">
                <?php if (!empty($data['biopsie']) && $data['biopsie'] == 1): ?>
                    <img src="<?php echo Yii::$app->basePath . '/web/img/check1.png' ?>" class="w-20">
                <?php else:?>
                    <img src="<?php echo Yii::$app->basePath . '/web/img/check2.png' ?>" class="w-20">
                <?php endif;?>
            </td>
            <td class="h-20" style="width: 30px; padding-top: 4px;">
                <?php if (!empty($data['bal']) && $data['bal'] == 1): ?>
                    <img src="<?php echo Yii::$app->basePath . '/web/img/check1.png' ?>" class="w-20">
                <?php else:?>
                    <img src="<?php echo Yii::$app->basePath . '/web/img/check2.png' ?>" class="w-20">
                <?php endif;?>
            </td>
        </tr>
    </table>
    <table>
        <tr>
            <td class="h-20 w-241 pl-47 pt-4">
                <?php if (!empty($data['pelvicLimbs']) && $data['pelvicLimbs'] == 1): ?>
                    <img src="<?php echo Yii::$app->basePath . '/web/img/check1.png' ?>" class="w-20">
                <?php else:?>
                    <img src="<?php echo Yii::$app->basePath . '/web/img/check2.png' ?>" class="w-20">
                <?php endif;?>
            </td>
            <td class="h-20 pt-4">
                <?php if (!empty($data['urography']) && $data['urography'] == 1): ?>
                    <img src="<?php echo Yii::$app->basePath . '/web/img/check1.png' ?>" class="w-20">
                <?php else:?>
                    <img src="<?php echo Yii::$app->basePath . '/web/img/check2.png' ?>" class="w-20">
                <?php endif;?>
            </td>
        </tr>
    </table>
    <table>
        <tr>
            <td class="h-20" style="padding-left: 244px;">
                <?php if (!empty($data['addit_proc'])): ?>
                    <?php echo $data['addit_proc'];?>
                <?php else:?>
                    <span class="tw">-</span>
                <?php endif;?>
            </td>
        </tr>
    </table>
    <table>
        <tr>
            <td class="h-20" style="padding-left: 244px; padding-top: 31px;">
                <?php if (!empty($data['int_area'])): ?>
                    <?php echo $data['int_area'];?>
                <?php else:?>
                    <span class="tw">-</span>
                <?php endif;?>
            </td>
        </tr>
    </table>
    <table>
        <tr>
            <td style="padding-left: 244px; height: 45px;">
                <?php if (!empty($data['anamnez'])): ?>
                    <?php echo $data['anamnez'];?>
                <?php else:?>
                    <span class="tw">-</span>
                <?php endif;?>
            </td>
        </tr>
        <tr>
            <td class="h-20" style="padding-left: 244px;">
                <?php if (!empty($data['simptoms'])): ?>
                    <?php echo $data['simptoms'];?>
                <?php else:?>
                    <span class="tw">-</span>
                <?php endif;?>
            </td>
        </tr>
        <tr>
            <td class="h-20" style="padding-left: 244px;">
                <?php if (!empty($data['pred_diagnoz'])): ?>
                    <?php echo $data['pred_diagnoz'];?>
                <?php else:?>
                    <span class="tw">-</span>
                <?php endif;?>
            </td>
        </tr>
    </table>
    <table>
        <tr>
            <td class="h-20" style="padding-left: 248px; width: 70px;">
                <?php if (!empty($data['hirurg']) && $data['hirurg'] == 1): ?>x<?php endif;?>
            </td>
            <td class="h-20" style="padding-left: 88px; width: 30px;">
                <?php if (!empty($data['hirurg']) && $data['hirurg'] == 0): ?>x<?php endif;?>
            </td>
            <td class="h-20">
                <span class="tw">-</span>
            </td>
        </tr>
        <tr>
            <td class="h-20" style="padding-left: 248px; padding-top:4px; width: 70px;">
                <?php if (!empty($data['implants']) && $data['implants'] == 1): ?>x<?php endif;?>
            </td>
            <td class="h-20" style="padding-left: 88px; padding-top:4px; width: 110px;">
                <?php if (!empty($data['implants']) && $data['implants'] == 0): ?>x<?php endif;?>
            </td>
            <td class="h-20" style="padding-left: 90px; padding-top:8px; font-size: 12px;">
                <?php if (!empty($data['implants_text'])): ?>
                    <?php echo $data['implants_text'];?>
                <?php endif;?>
            </td>
        </tr>
        <tr>
            <td class="h-20" style="padding-left: 248px; padding-top:3px; width: 70px;">
                <?php if (!empty($data['contr']) && $data['contr'] == 1): ?>x<?php endif;?>
            </td>
            <td class="h-20" style="padding-left: 88px; padding-top:3px; width: 110px;">
                <?php if (!empty($data['contr']) && $data['contr'] == 0): ?>x<?php endif;?>
            </td>
            <td class="h-20" style="padding-left: 90px; padding-top:3px; font-size: 12px;">
                <?php if (!empty($data['implants_text'])): ?>
                    <?php echo $data['contr_text'];?>
                <?php endif;?>
            </td>
        </tr>
    </table>
</div>

<style>
    .pt-145 {
        padding-top: 143px;
    }
    .pl-218 {
        padding-left: 218px;
    }
    .pt-6 {
        padding-top: 6px;
    }
    .pt-88 {
        padding-top: 88px;
    }
    .w-241 {
        width: 241px;
    }
    .pl-47 {
        padding-left: 47px;
    }
    .pt-5 {
        padding-top: 5px;
    }
    .pt-4 {
        padding-top: 4px;
    }
    .w-20 {
        width: 20px;
    }
    .h-20 {
        height: 24px;
    }
    .h-21 {
        height: 15px;
    }
    .tw {
        color: white;
    }
</style>
