<?php
/**
 * @var $orgId
 * @var $operators
 * @var $clinics
 * @var $firstCallTypes
 * @var $secondCallTypes
 */
$startDate = new DateTime();
$startDate->setTime(0, 0);
$endDate = (clone $startDate)->add(new DateInterval('P1D'));
?>
<div class="row main_row search">
    <div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>

    <div class="col-xl-8 col-lg-10">
        <form id="logcall_form">
            <div class="search_sub" style="color:#748496;">
                <div class="row mt-3">
                    <div class="d-inline-flex">
                        <div class="label required" style="width: 45px;">Дата</div>
                        <div class="input">
                            <label for="date-from" class="label-from">с&nbsp;</label>
                            <input type="datetime-local" id="date-from" name="date-from" class="w-auto" value="<?= $startDate->format('Y-m-d\TH:i') ?>" autocomplete="off">
                            <label for="date-to" class="label-to">по&nbsp;</label>
                            <input type="datetime-local" id="date-to" name="date-to" class="w-auto" value="<?= $endDate->format('Y-m-d\TH:i') ?>" autocomplete="off">
                            <div class="unselect" onclick="resetForm('date')"></div>
                        </div>
                    </div>

                    <div class="d-inline-flex">
                        <label for="label" class="label">Оператор</label>
                        <div class="input">
                            <select name="operator" id="operator" multiple="multiple">
                                <?php foreach($operators as $o): ?>
                                    <option value="<?= $o['id'] ?>"><?= $o['fullname'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="unselect" onclick="resetForm('operator')"></div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="d-inline-flex">
                        <label for="label" class="label">Клиника</label>
                        <div class="input">
                            <select name="clinic" id="clinic" multiple="multiple">
                                <?php foreach($clinics as $c): ?>
                                    <option value="<?= $c['id'] ?>"<?php if ($orgId == $c['id']): ?>selected <?php endif ?>><?= $c['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="unselect" onclick="resetForm('clinic')"></div>
                        </div>
                    </div>

                    <div class="d-inline-flex">
                        <label for="" class="text-nowrap label">Тип звонка 1 ур.</label>
                        <div class="input">
                            <select name="first_call_type" id="first_call_type" multiple="multiple">
                                <?php foreach($firstCallTypes as $ct1): ?>
                                    <option value="<?= $ct1['id'] ?>"><?= $ct1['cname'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="unselect" onclick="resetForm('first_call_type')"></div>
                        </div>
                    </div>

                    <div class="d-inline-flex">
                        <label for="" class="text-nowrap label">Тип звонка 2 ур.</label>
                        <div class="input">
                            <select name="second_call_type" id="second_call_type" multiple="multiple">
                                <?php foreach($secondCallTypes as $ct2): ?>
                                    <option value="<?= $ct2['id'] ?>" pid="<?= $ct2['pid'] ?>" disabled><?= $ct2['cname'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="unselect" onclick="resetForm('second_call_type')"></div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="d-inline-flex">
                        <label><input type="checkbox" class="styled-checkbox" id="use_search" name="use_search" checked> Мои звонки</label>
                    </div>
                </div>

                <div class="row mt-3 buttons">
                    <button type="reset" style="width: 100px;">Сброс</button>
                    <button type="submit" style="margin-left: 5px; width: 120px;">Поиск</button>
                </div>

            </div>
        </form>
    </div>

    <div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>
</div>

<div class="row main_row">
    <div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>

    <div class="col-xl-8 col-lg-10 results">
        <div class="info"></div>
        <div id="ListRecs" class="loading"></div>
    </div>

    <div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>
</div>

<script>
    function toISOFormat(date) {
        function pad(number) {
            if (number < 10) {
                return '0' + number;
            }
            return number;
        }

        return (
            date.getFullYear() +
            '-' +
            pad(date.getMonth() + 1) +
            '-' +
            pad(date.getDate()) +
            'T' +
            pad(date.getHours()) +
            ':' +
            pad(date.getMinutes())
        );
    }

    function cascadeSelect($target, values, field) {
        console.log(values);
        if (values && values.length) {
            $('option', $target).prop('disabled', true).prop('selected', false);
            for (let i = 0; i < values.length; ++i) {
                const v = values[i];
                $('option[' + field + '=' + v + ']', $target).prop('disabled', false);
            }
            $target.val('').multiselect('refresh');
        } else {
            $('option', $target).prop('disabled', true);
            $target.multiselect('refresh');
        }
    }

    $('#logcall_form').on('reset', function (ev) {
        ev.preventDefault();
        resetForm();
    });

    $('#logcall_form').on('submit', function (ev) {
        ev.preventDefault();
        ;
    });

    function resetForm(name) {
        if (!name || name === 'date') {
            let date = new Date();
            date.setHours(0, 0, 0, 0);
            $('#date-from').val(toISOFormat(date));
            date.setDate(date.getDate() + 1);
            $('#date-to').val(toISOFormat(date));
        }
        if (!name || name === 'operator') {
            $('.operator').val('').multiselect('refresh');
        }
        if (!name || name === 'clinic') {
            $('.clinic').val('').multiselect('refresh');
        }
        if (!name || name === 'first_call_type') {
            $('.first_call_type').val('').multiselect('refresh');
        }
        if (!name || name === 'second_call_type') {
            onFirstCallTypeChange();
        }
        if (!name || name === 'use_search') {
            $('#use_search').prop('checked', true);
        }
    }

    function onFirstCallTypeChange() {
        const $firstCallType = $('#first_call_type');
        const $secondCallType = $('#second_call_type');
        cascadeSelect($secondCallType, $firstCallType.val(), 'pid');
    }

    $(document).ready(function () {
        const defaults = {
            nonSelectedText: 'Выберите...',
            allSelectedText: "Выбраны все",
            nSelectedText  : "выбрано",
            buttonWidth: '250',
            maxHeight: 200,
            enableFiltering: true,
            enableCaseInsensitiveFiltering : true,
            selectAllText: 'Выбрать все',
            includeSelectAllOption: true,
        };

        let options = $.extend({}, defaults, {
            filterPlaceholder: 'Выбрать оператора...',
        });
        $('#operator').multiselect(options);

        options = $.extend({}, defaults, {
            filterPlaceholder: 'Выбрать клинику...',
        });
        $('#clinic').multiselect(options);

        options = $.extend({}, defaults, {
            filterPlaceholder: 'Выбрать тип звонка 1 ур...',
            onChange: function(ev) {
                onFirstCallTypeChange();
            }
        });
        $('#first_call_type').multiselect(options);

        options = $.extend({}, defaults, {
            filterPlaceholder: 'Выбрать тип звонка 2 ур...',
        });
        $('#second_call_type').multiselect(options);
    });
</script>
