<?php

use kartik\select2\Select2;
use yii\helpers\Html;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;

/* @var $this \yii\web\View */
/* @var $model \app\models\db\Organizations */
/* @var $contactForm \app\modules\adminv\models\forms\ShelterContactsForm */
/* @var $addressForm \app\modules\adminv\models\forms\OrganizationAddressForm */
/* @var $representativeForm \app\modules\adminv\models\forms\ShelterRepresentativeForm */
/* @var $organizationsOptions array */

//$fiasUrl = $isProd ? 'https://fias.vetas.mos.ru' : 'https://fias.pet.altarix.org';
$isProd = (strpos(Yii::$app->request->hostName, 'vetas.mos.ru') !== false);
$absoluteHomeUrl = \yii\helpers\Url::home($isProd ? 'https' : true);
$localhost = "$absoluteHomeUrl/shelters/";
$efspMethod = 'efsp-list';
$roomMethod = 'efsp-rooms';

$js = <<<JS
    $('#organizationaddressform-regionguid').on('select2:select', function (e) {
        var data = e.params.data;
        if (data.selected === true && data.text !== undefined) {
            $('#organizationaddressform-region').val(data.text).trigger('change');
            $('#organizationaddressform-regionfias').val(data.data.fias_id).trigger('change');
        } else {
            $('#organizationaddressform-region').val(null).trigger('change');
            $('#organizationaddressform-regionfias').val(null).trigger('change');
        }
        $('#organizationaddressform-id_fias_address').val(null).trigger('change');
    });
    $('#organizationaddressform-cityguid').on('select2:select', function (e) {
        var data = e.params.data;
        if (data.selected === true && data.text !== undefined) {
            $('#organizationaddressform-city').val(data.text).trigger('change');
            if (data.data.oktmo !== undefined) {
                $('#organizationaddressform-oktmo').val(data.data.oktmo).trigger('change');
            }
        } else {
            $('#organizationaddressform-city').val(null).trigger('change');
        }
        $('#organizationaddressform-id_fias_address').val(null).trigger('change');
    });
    $('#organizationaddressform-streetguid').on('select2:select', function (e) {
        var data = e.params.data;
        if (data.selected === true && data.text !== undefined) {
            $('#organizationaddressform-street').val(data.text).trigger('change');
            if (data.data.oktmo !== undefined) {
                $('#organizationaddressform-oktmo').val(data.data.oktmo).trigger('change');
            }
        } else {
            $('#organizationaddressform-street').val(null).trigger('change');
        }
        $('#organizationaddressform-id_fias_address').val(null).trigger('change');
    });
    $('#organizationaddressform-houseguid').on('select2:select', function (e) {
        var data = e.params.data;
        if (data.selected === true && data.text !== undefined) {
            $('#organizationaddressform-house').val(data.text).trigger('change');
            if (data.data.oktmo !== undefined) {
                $('#organizationaddressform-oktmo').val(data.data.oktmo).trigger('change');
            }
        } else {
            $('#organizationaddressform-house').val(null).trigger('change');
        }
        $('#organizationaddressform-id_fias_address').val(null).trigger('change');
    });
    $('#organizationaddressform-roomguid').on('select2:select', function (e) {
        var data = e.params.data;
        if (data.selected === true && data.text !== undefined) {
            $('#organizationaddressform-room').val(data.text).trigger('change');
        }
        $('#organizationaddressform-id_fias_address').val(null).trigger('change');
    });

    $('#organizationaddressform-regionguid').on('select2:unselect', function (e) {
        $('#organizationaddressform-region').val(null).trigger('change');
        $('#organizationaddressform-regionfias').val(null).trigger('change');
        $('#organizationaddressform-cityguid').val(null).trigger('change');
        $('#organizationaddressform-city').val(null).trigger('change');
        $('#organizationaddressform-streetguid').val(null).trigger('change');
        $('#organizationaddressform-street').val(null).trigger('change');
        $('#organizationaddressform-houseguid').val(null).trigger('change');
        $('#organizationaddressform-house').val(null).trigger('change');
        $('#organizationaddressform-roomguid').val(null).trigger('change');
        $('#organizationaddressform-room').val(null).trigger('change');
        $('#organizationaddressform-oktmo').val(null).trigger('change');
        $('#organizationaddressform-id_fias_address').val(null).trigger('change');
    });
    $('#organizationaddressform-regionguid').on('select2:clear', function (e) {
        $('#organizationaddressform-region').val(null).trigger('change');
        $('#organizationaddressform-regionfias').val(null).trigger('change');
        $('#organizationaddressform-cityguid').val(null).trigger('change');
        $('#organizationaddressform-city').val(null).trigger('change');
        $('#organizationaddressform-streetguid').val(null).trigger('change');
        $('#organizationaddressform-street').val(null).trigger('change');
        $('#organizationaddressform-houseguid').val(null).trigger('change');
        $('#organizationaddressform-house').val(null).trigger('change');
        $('#organizationaddressform-roomguid').val(null).trigger('change');
        $('#organizationaddressform-room').val(null).trigger('change');
        $('#organizationaddressform-oktmo').val(null).trigger('change');
        $('#organizationaddressform-id_fias_address').val(null).trigger('change');
    });
    $('#organizationaddressform-cityguid').on('select2:unselect', function (e) {
        $('#organizationaddressform-city').val(null).trigger('change');
        $('#organizationaddressform-streetguid').val(null).trigger('change');
        $('#organizationaddressform-street').val(null).trigger('change');
        $('#organizationaddressform-houseguid').val(null).trigger('change');
        $('#organizationaddressform-house').val(null).trigger('change');
        $('#organizationaddressform-roomguid').val(null).trigger('change');
        $('#organizationaddressform-room').val(null).trigger('change');
        $('#organizationaddressform-oktmo').val(null).trigger('change');
        $('#organizationaddressform-id_fias_address').val(null).trigger('change');
    });
    $('#organizationaddressform-cityguid').on('select2:clear', function (e) {
        console.log('there');
        $('#organizationaddressform-city').val(null).trigger('change');
        $('#organizationaddressform-streetguid').val(null).trigger('change');
        $('#organizationaddressform-street').val(null).trigger('change');
        $('#organizationaddressform-houseguid').val(null).trigger('change');
        $('#organizationaddressform-house').val(null).trigger('change');
        $('#organizationaddressform-roomguid').val(null).trigger('change');
        $('#organizationaddressform-room').val(null).trigger('change');
        $('#organizationaddressform-oktmo').val(null).trigger('change');
        $('#organizationaddressform-id_fias_address').val(null).trigger('change');
    });
    $('#organizationaddressform-streetguid').on('select2:unselect', function (e) {
        $('#organizationaddressform-street').val(null).trigger('change');
        $('#organizationaddressform-houseguid').val(null).trigger('change');
        $('#organizationaddressform-house').val(null).trigger('change');
        $('#organizationaddressform-roomguid').val(null).trigger('change');
        $('#organizationaddressform-room').val(null).trigger('change');
        $('#organizationaddressform-oktmo').val(null).trigger('change');
        $('#organizationaddressform-id_fias_address').val(null).trigger('change');
    });
    $('#organizationaddressform-streetguid').on('select2:clear', function (e) {
        $('#organizationaddressform-street').val(null).trigger('change');
        $('#organizationaddressform-houseguid').val(null).trigger('change');
        $('#organizationaddressform-house').val(null).trigger('change');
        $('#organizationaddressform-roomguid').val(null).trigger('change');
        $('#organizationaddressform-room').val(null).trigger('change');
        $('#organizationaddressform-oktmo').val(null).trigger('change');
        $('#organizationaddressform-id_fias_address').val(null).trigger('change');
    });
    $('#organizationaddressform-houseguid').on('select2:unselect', function (e) {
        $('#organizationaddressform-house').val(null).trigger('change');
        $('#organizationaddressform-roomguid').val(null).trigger('change');
        $('#organizationaddressform-room').val(null).trigger('change');
        $('#organizationaddressform-oktmo').val(null).trigger('change');
        $('#organizationaddressform-id_fias_address').val(null).trigger('change');
    });
    $('#organizationaddressform-houseguid').on('select2:clear', function (e) {
        $('#organizationaddressform-house').val(null).trigger('change');
        $('#organizationaddressform-roomguid').val(null).trigger('change');
        $('#organizationaddressform-room').val(null).trigger('change');
        $('#organizationaddressform-oktmo').val(null).trigger('change');
        $('#organizationaddressform-id_fias_address').val(null).trigger('change');
    });
    $('#organizationaddressform-roomguid').on('select2:unselect', function (e) {
        $('#organizationaddressform-room').val(null).trigger('change');
        $('#organizationaddressform-id_fias_address').val(null).trigger('change');
    });
    $('#organizationaddressform-roomguid').on('select2:clear', function (e) {
        $('#organizationaddressform-room').val(null).trigger('change');
        $('#organizationaddressform-id_fias_address').val(null).trigger('change');
    });
    $('body').on('change', '#organizationaddressform-regionguid, #organizationaddressform-cityguid, #organizationaddressform-streetguid, #organizationaddressform-houseguid', '#organizationaddressform-roomguid', function(e) {
        $('#organizationaddressform-id_fias_address').val(null).trigger('change');
    });
JS;

$this->registerJs($js);
?>
<div class="user-form col-md-4">
    <?php $form = ActiveForm::begin(); ?>
    <?php echo Html::activeHiddenInput($model, 'id'); ?>
    <?php echo $form->field($model, 'parent_id')
        ->dropDownList($organizationsOptions)
        ->label('Родительская организация'); ?>
    <?php echo $form->field($model, 'name')->label('Полное наименование') ?>
    <?php echo $form->field($model, 'short_name')->label('Сокращенное наименование') ?>
    <?php /*echo $form->field($model, 'kpp')->label('КПП (9 цифр)') */ ?>
    <?php /*echo $form->field($model, 'inn')->label('ИНН (10 цифр)') */ ?>
    <?php /*echo $form->field($model, 'ogrn')->label('ОГРН (13 цифр)') */ ?>
    <?php /*echo $form->field($model, 'reg_number')->label('Регистрационный номер') */ ?>
    <?php echo $form->field($contactForm, 'phone')->label('Телефон') ?>
    <?php echo $form->field($contactForm, 'email')->label('Email') ?>
    <?php echo Html::activeHiddenInput($contactForm, 'contact_id_phone') ?>
    <?php echo Html::activeHiddenInput($contactForm, 'contact_id_email') ?>
    <?php echo $form->field($model, 'inn')->label('ИНН (10 цифр)') ?>
    <?php echo $form->field($model, 'ogrn')->label('ОГРН (13 цифр)') ?>
    <?php echo $form->field($representativeForm, 'f_fio') ?>
    <?php echo $form->field($representativeForm, 'i_fio') ?>
    <?php echo $form->field($representativeForm, 'o_fio') ?>
    <hr>
    <?php echo $form->field($addressForm, 'regionguid')
        ->widget(Select2::class, [
            'data' => ((!empty($addressForm->regionguid) && !empty($addressForm->region)) ? [$addressForm->regionguid => $addressForm->region] : []),
            'language' => 'ru',
            'options' => ['placeholder' => '- выберите регион -'],
            'pluginOptions' => [
                'allowClear' => true,
                'ajax' => [
                    'url' => ($localhost.$efspMethod),
                    'dataType' => 'json',
                    'processResults' => new JsExpression('function(response, params) {
                        var data = [];
                        if (response.suggestions !== undefined) {
                            data = $.map(response.suggestions, function (obj) {
                                obj.id = obj.data.region_fns_code
                                obj.guid = obj.data.fias_id;
                                obj.text = obj.value;
                                return obj;
                            });
                        }
                        var total_count = data.length;
                        return {
                            results: data,
                            pagination: {
                                more: (params.page * 25) < total_count
                            }
                        };
                    }'),
                    'data' => new JsExpression('function(params) {
                         var query = {
                             bound: \'region\',
                             query: params.term
                         }
                         return query;
                    }'),
                ],
            ],
        ]); ?>
    <?php echo $form->field($addressForm, 'cityguid')
        ->widget(Select2::class, [
            'data' => ((!empty($addressForm->cityguid) && !empty($addressForm->city)) ? [$addressForm->cityguid => $addressForm->city] : []),
            'language' => 'ru',
            'options' => ['placeholder' => '- выберите город -'],
            'pluginOptions' => [
                'allowClear' => true,
                'ajax' => [
                    'url' => ($localhost.$efspMethod),
                    'dataType' => 'json',
                    'processResults' => new JsExpression('function(response, params) {
                        var data = [];
                        if (response.suggestions !== undefined) {
                            data = $.map(response.suggestions, function (obj) {
                                obj.id = obj.data.fias_id;
                                obj.text = obj.value;
                                return obj;
                            });
                        }
                        var total_count = data.length;
                        return {
                            results: data,
                            pagination: {
                                more: (params.page * 25) < total_count
                            }
                        };
                    }'),
                    'data' => new JsExpression('function(params) {
                         var query = {
                             bound: \'city\',
                             query: params.term,
                             locationFias: $("#organizationaddressform-regionfias").val(),
                             locationType: \'region_fias_id\'
                         }
                         return query;
                    }'),
                ],
            ],
        ]); ?>
    <?php echo $form->field($addressForm, 'streetguid')
        ->widget(Select2::class, [
            'data' => ((!empty($addressForm->streetguid) && !empty($addressForm->street)) ? [$addressForm->streetguid => $addressForm->street] : []),
            'language' => 'ru',
            'options' => ['placeholder' => '- выберите улицу -'],
            'pluginOptions' => [
                'allowClear' => true,
                'ajax' => [
                    'url' => ($localhost.$efspMethod),
                    'dataType' => 'json',
                    'processResults' => new JsExpression('function(response, params) {
                        var data = [];
                        if (response.suggestions !== undefined) {
                            data = $.map(response.suggestions, function (obj) {
                                obj.id = obj.data.fias_id;
                                obj.text = obj.value;
                                return obj;
                            });
                        }
                        var total_count = data.length;
                        return {
                            results: data,
                            pagination: {
                                more: (params.page * 25) < total_count
                            }
                        };
                    }'),
                    'data' => new JsExpression('function(params) {
                         var query = {
                             bound: \'street\',
                             query: params.term,
                             locationFias: $("#organizationaddressform-cityguid").val(),
                             locationType: \'city_fias_id\'
                         }
                         return query;
                    }'),
                ],
            ],
        ]); ?>
    <?php echo $form->field($addressForm, 'houseguid')
        ->widget(Select2::class, [
            'data' => ((!empty($addressForm->houseguid) && !empty($addressForm->house)) ? [$addressForm->houseguid => $addressForm->house] : []),
            'language' => 'ru',
            'options' => ['placeholder' => '- выберите дом -'],
            'pluginOptions' => [
                'allowClear' => true,
                'ajax' => [
                    'url' => ($localhost.$efspMethod),
                    'dataType' => 'json',
                    'processResults' => new JsExpression('function(response, params) {
                        var data = [];
                        if (response.suggestions !== undefined) {
                            data = $.map(response.suggestions, function (obj) {
                                obj.id = obj.data.fias_id;
                                obj.text = obj.value;
                                return obj;
                            });
                        }
                        var total_count = data.length;
                        return {
                            results: data,
                            pagination: {
                                more: (params.page * 20) < total_count
                            }
                        };
                    }'),
                    'data' => new JsExpression('function(params) {
                         var query = {
                             bound: \'house\',
                             query: params.term,
                             locationFias: $("#organizationaddressform-streetguid").val(),
                             locationType: \'street_fias_id\'
                         }
                         return query;
                    }'),
                ],
            ],
        ]); ?>
    <?php echo $form->field($addressForm, 'roomguid')
        ->widget(Select2::class, [
            'data' => ((!empty($addressForm->roomguid) && !empty($addressForm->room)) ? [$addressForm->roomguid => $addressForm->room] : []),
            'language' => 'ru',
            'options' => ['placeholder' => '- выберите квартиру -'],
            'pluginOptions' => [
                'allowClear' => true,
                'ajax' => [
                    'url' => ($localhost.$roomMethod),
                    'dataType' => 'json',
                    'processResults' => new JsExpression('function(response, params) {
                        var data = [];
                        if (response.suggestions !== undefined) {
                            data = $.map(response.suggestions, function (obj) {
                                obj.id = obj.data.room_fias_id;
                                obj.text = obj.value;
                                return obj;
                            });
                        }
                        var total_count = data.length;
                        return {
                            results: data,
                            pagination: {
                                more: (params.page * 20) < total_count
                            }
                        };
                    }'),
                    'data' => new JsExpression('function(params) {
                         var query = {
                             houseFias: $("#organizationaddressform-houseguid").val(),
                             query: params.term
                         }
                         return query;
                    }'),
                ],
            ],
        ]); ?>
    <?php echo Html::activeHiddenInput($addressForm, 'id_fias_address') ?>
    <?php echo Html::activeHiddenInput($addressForm, 'region') ?>
    <?php echo Html::activeHiddenInput($addressForm, 'city') ?>
    <?php echo Html::activeHiddenInput($addressForm, 'street') ?>
    <?php echo Html::activeHiddenInput($addressForm, 'house') ?>
    <?php echo Html::activeHiddenInput($addressForm, 'room') ?>
    <?php echo Html::activeHiddenInput($addressForm, 'oktmo') ?>

    <?php echo $form->field($addressForm, 'id_area')
        ->dropDownList($addressForm::areasOptions(), ['prompt' => '- выберите округ -']); ?>
    <?php echo $form->field($addressForm, 'id_district')
        ->dropDownList($addressForm::districtsOptions(), ['prompt' => '- выберите район -']); ?>
    <div class="form-group">
        <?php echo Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>
        <?php echo Html::a('Отмена', ['/adminv/shelters/index'], ['class' => 'btn btn-default']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
