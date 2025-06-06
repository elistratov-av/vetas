<?php
?>
<div class="row main_row search">
    <div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>
        <div class="col-xl-8 col-lg-10">
        <form id="form" onsubmit="ListShowSlots(); return false;">
            <div class="row search_sub">

                <div class="d-inline-flex">
                    <div class="label required" style="width: 45px;">Дата</div>
                    <div class="input">
                        <label for="date-from" class="label-from">с&nbsp;</label>
                        <input type="datetime-local" id="date-from" name="date-from" class="w-auto" value="" autocomplete="off">
                        <label for="date-to" class="label-to">по&nbsp;</label>
                        <input type="datetime-local" id="date-to" name="date-to" class="w-auto" value="" autocomplete="off">
                        <div class="unselect" onclick="resetForm('date')"></div>
                    </div>
                </div>

                <div class="d-inline-flex">
                    <label for="label" class="label">Оператор</label>
                    <div class="input">
                        <select class="metro" name="operator" id="operator" multiple="multiple">
                            <option value="id" area="area">station</option>
                        </select>
                        <div class="unselect" onclick="resetForm('operator')"></div>
                    </div>
                </div>

                <div class="d-inline-flex">
                    <label for="label" class="label">Клиника</label>
                    <div class="input">
                        <select class="metro" name="clinic" id="clinic" multiple="multiple">
                            <option value="id" area="area">station</option>
                        </select>
                        <div class="unselect" onclick="resetForm('clinic')"></div>
                    </div>
                </div>

                <div class="d-inline-flex">
                    <label for="" class="text-nowrap label">Тип звонка 1 ур.</label>
                    <div class="input">
                        <select class="metro" name="first_call_type" id="first_call_type" multiple="multiple">
                            <option value="id" area="area">station</option>
                        </select>
                        <div class="unselect" onclick="resetForm('metro')"></div>
                    </div>
                </div>

                <div class="d-inline-flex">
                    <label for="" class="text-nowrap label">Тип звонка 2 ур.</label>
                    <div class="input">
                        <select class="metro" name="second_call_type" id="second_call_type" multiple="multiple">
                            <option value="id" area="area">station</option>
                        </select>
                        <div class="unselect" onclick="resetForm('metro')"></div>
                    </div>
                </div>

                <div class="d-inline-flex">
                    <label><input type="checkbox" class="styled-checkbox" id="use_search" name="use_search" checked> Мои звонки</label>
                </div>

                <div class="d-inline-flex buttons">
                    <button type="reset" onclick="resetForm();" style="width: 100px;">Сброс</button>
                    <button type="submit" style="margin-left: 5px; width: 120px;">Поиск</button>
                </div>

            </div>
        </form>
    </div>
</div>

<div class="row main_row">
    <div class="info"></div>
    <div id="ListRecs" class="loading"></div>
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
        if (values && values.length) {
            $('option', $target).prop('disabled', true).prop('selected', false);
            for (let i = 0; i < values.length; ++i) {
                const v = values[i];
                $('option[' + field + '=' + v + ']', $target).prop('disabled', false);
            }
            $target.val('').multiselect('refresh');
        } else {
            $('option', $target).prop('disabled', false);
            $target.multiselect('refresh');
        }
    }

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
            $('.second_call_type').val('').multiselect('refresh');
        }
/*
        if (!name || name === 'use_search') {
            $('#use_search').prop('checked', true);
        }
*/
    }

    function onFirstCallTypeChange() {
        const $firstCallType = $('#first_call_type');
        const $secondCallType = $('#second_call_type');
        cascadeSelect($secondCallType, $firstCallType.val(), 'pid');
    }

    $(document).ready(function () {
        const defaults = {
            //enableClickableOptGroups: true,
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
            onDropdownHide: function(ev) {
                alert('Dropdown hide.');
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
