<?php

use yii\web\View;

/**
 * @var View $this
 * @var array $shelters
 */

$this->title = 'Животные, содержащиеся в приютах';
?>
<div class="info"></div>
<form id="form_search_recs" onsubmit="searchPets(); return false;">
    <div class="row">
        <div class="col-4">
            <label for="shelter_id">Приют</label>
            <select id="shelter_id" name="shelter_id">
                <option value="">Не выбран</option>
                <?php foreach ($shelters as $shelter): ?>
                    <option value="<?= $shelter['id'] ?>"><?= $shelter['value'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-4">
            <label for="idcode">№ чипа</label>
            <input type="text" id="idcode" name="idcode" class="w-100" value=""/>
        </div>
        <div class="col-4 align-self-center d-flex">
            <span class="flex-grow-1">Найдено животных</span> <strong id="pets-count"></strong>
        </div>
    </div>
    <br/>
    <div class="row">
        <div class="col-4">
            <label for="name">Кличка</label>
            <input type="text" id="name" name="name" class="w-100" value=""/>
        </div>
    </div>
    <br/>

    <div class="row justify-content-end">
        <div class="col-4 d-flex justify-content-end">
            <button type="reset" class="btn btn-secondary w-auto mr-3">Сбросить</button>
            <button type="submit" class="btn btn-primary w-auto">Поиск</button>
        </div>
    </div>
</form>
<div class="border-top py-1 my-4 border-custom-teal"></div>

<div style="margin-top: 30px;" class="recs row main_row">
    <div class="table" id="ListRecs">
        <div class="p-3">...</div>
    </div>
</div>

<!-- Форма перемещения животного в другой приют -->
<template id="change-shelter-card">
    <div id="change-shelter-window" class="window main_row col-xl-6 col-lg-10 col-12">
        <div class="close" onclick="closeInformingWindow(true);"></div>

        <div class="top">Перемещение животного в другой приют</div>
        <div class="col-12 mt-2">
            <div class="error" id="error-message" style="display: none;"></div>
        </div>

        <form class="form-window main_row" id="change-shelter-form">
            <div class="main_row">
                <input type="hidden" id="pet_id" name="pet_id">

                <div class="col-12 mb-4">
                    <label for="shelter_to_id" class="form-label mb-2 required">Приют</label>
                    <select id="shelter_to_id" name="shelter_to_id">
                        <?php foreach ($shelters as $shelter): ?>
                            <option value="<?= $shelter['id'] ?>"><?= $shelter['value'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="controls">
                <button type="reset" class="btn btn-secondary w-auto mr-2" onclick="closeInformingWindow(true);">Отмена</button>
                <button type="submit" class="btn btn-danger w-auto">Подтвердить</button>
            </div>
        </form>
    </div>
</template>

<!-- Форма изменения статуса животного -->
<template id="change-status-card">
    <div id="change-status-window" class="window main_row col-xl-6 col-lg-10 col-12">
        <div class="close" onclick="closeInformingWindow(true);"></div>

        <div class="top">Изменение статуса животного</div>
        <div class="col-12 mt-2">
            <div class="error" id="error-message" style="display: none;"></div>
        </div>

        <form class="form-window main_row" id="change-status-form">
            <div class="main_row">
                <input type="hidden" id="pet_id" name="pet_id">

                <div class="col-12 mb-4">
                    <label for="status_new" class="form-label mb-2 required">Статус</label>
                    <select id="status_new" name="status_new">
                        <option value="DEPARTURED">Выбыло</option>
                        <option value="IN_SHELTER">В приюте</option>
                        <option value="QUARANTINE">Карантин</option>
                        <option value="QUARANTINE_OTHER">Карантин (продлен)</option>
                        <option value="IN_ISOLATION">В изоляторе</option>
                        <option value="IN_HOSPITAL">В стационаре</option>
                    </select>
                </div>
            </div>

            <div class="controls">
                <button type="reset" class="btn btn-secondary w-auto mr-2" onclick="closeInformingWindow(true);">Отмена</button>
                <button type="submit" class="btn btn-danger w-auto">Подтвердить</button>
            </div>
        </form>
    </div>
</template>

<script>
    function getHtmlEncoder() {
        let $div = $('<div/>');
        return (v) => {
            return $div.text(v).html();
        };
    }

    function handleError(xhr) {
        if (xhr.status === 401) {
            window.location.href = 'index.php';
            return;
        }
        const err = getReponseError(xhr.responseJSON, 'Произошла ошибка при получении списка животных');
        $('.info').html(err).show();
    }

    function getReponseError(err, defaultMessage) {
        if (err && err.errors && err.errors.length) {
            const e = err.errors[0];
            if (e.detail) return e.detail;
        }
        return defaultMessage;
    }

    const DIR_ASC = 0;
    const DIR_DESC = 1;

    let curSort = '';
    let curDir = DIR_ASC;

    function searchPets(sort = null) {
        const formData = $('#form_search_recs').serializeArray();
        if (curSort === sort) {
            curDir = curDir === DIR_ASC ? DIR_DESC : DIR_ASC;
        }
        if ((curSort === null || curSort === '') || curSort !== sort) {
            curSort = sort;
        }

        $('.info').empty();
        jQuery.ajax({
            'async': true,
            'global': false,
            'cache': false,
            'type': 'GET',
            'data': formData,
            'url': 'search-pets?sort=' + curSort + '&dir=' + curDir,
            'beforeSend': function() {
                forceLoading('ListRecs');
            },
            'success': function (data) {
                const html = buildResponseTable(data);
                $('#ListRecs').html(html);
            }
        }).always(() => {
            closeLoading('ListRecs');
        }).fail(handleError);
    }

    function buildResponseTable(data) {
        const MAX_COUNT = 100;
        let html = '';
        let count = 0;

        if (data && data.length) {
            const enc = getHtmlEncoder();
            count = data.length;
            if (count > MAX_COUNT) {
                count = MAX_COUNT;
            }
            html = '<table class="w-100">';
            html += renderHeaderRow();
            for (let i = 0; i < count; ++i) {
                const d = data[i];

                html += renderDataRow(d, enc);
            }
            html += '</table>';
        }

        if (count == 0) {
            html = '<div class="message">По данному запросу не найдено записей.</div>';
        } else if (data.length > MAX_COUNT) {
            html += '<div class="message">Отображены первые ' + MAX_COUNT + ' записей согласно указанным критериям отбора</div>';
        }
        $('#pets-count').text(count);
        return html;
    }

    function sortMarker(col) {
        if (col === curSort) {
            return curDir === DIR_ASC ? ' up' : ' down';
        }
        return '';
    }

    function renderHeaderRow() {
        let html = '<tr><th><div id="shelter" class="sort' + sortMarker('shelter') + '" onclick="searchPets(\'shelter\')">Приют</div></th>';
        html += '<th><div id="idcode" class="sort' + sortMarker('idcode') + '" onclick="searchPets(\'idcode\')">№ чипа</div></th>';
        html += '<th><div id="name" class="sort' + sortMarker('name') + '" onclick="searchPets(\'name\')">Кличка</div></th>';
        html += '<th><div id="spec" class="sort' + sortMarker('spec') + '" onclick="searchPets(\'spec\')">Вид</div></th>';
        html += '<th><div id="status" class="sort' + sortMarker('status') + '" onclick="searchPets(\'status\')">Статус</div></th>';
        html += '<th></th></tr>';
        return html;
    }

    function renderDataRow(data, enc) {
        let html = '<tr><td>' + enc(data.shelter) + '</td>';
        html += '<td>' + enc(data.idcode) + '</td>';
        html += '<td>' + enc(data.name) + '</td>';
        html += '<td>' + enc(data.spec) + '</td>';
        html += '<td>' + enc(data.status) + '</td>';
        html += '<td><div class="d-inline-flex flex-column">';
        html += '<button class="button btn-mini mb-1" onclick="showChangeStatusForm(' + data.id + ', \'' + data.statuscode + '\');">Изменить статус</button>';
        html += '<button class="button btn-mini mb-1" onclick="showChangeShelterForm(' + data.id + ', ' + data.shelter_id + ');">Изменить приют</button>';
        html += '<button class="btn-secondary btn-mini mb-1" onclick="confirmDeletePet(' + data.id + ');">Удалить</button>';
        html += '</div></td></tr>';
        return html;
    }

    function showChangeShelterForm(petId, shelter_id) {
        if (!petId) return;
        const cardTemplate = document.querySelector('#change-shelter-card').content;
        const card = cardTemplate.querySelector('#change-shelter-window').cloneNode(true);
        const $subContainer = $('#sub_container');

        card.querySelector('#error-message').textContent = '';
        card.querySelector('#pet_id').value = petId;
        const curOption = card.querySelector('#shelter_to_id option[value="' + shelter_id + '"]');
        if (curOption) {
            curOption.disabled = true;
        }
        card.querySelector('#change-shelter-form').addEventListener('submit', (ev) => {
            ev.preventDefault();
            movePetToShelter(petId);
        });

        $subContainer.empty();
        $subContainer.append(card);

        $('#background').fadeIn();
        $('#container').show();
    }

    function movePetToShelter(petId) {
        const $form = $('#change-shelter-window #change-shelter-form');
        const formData = $form.serializeArray();

        const deferred = jQuery.ajax({
            'async': true,
            'global': false,
            'cache': false,
            'type': 'POST',
            'data': formData,
            'url': 'change-pet-shelter/' + petId,
            'beforeSend': function() {
                showTopLoading();
            },
            'success': function () {
                searchPets();
            }
        }).always(() => {
            closeTopLoading();
        });
        closeInformingWindow(true);
        return deferred;
    }

    function showChangeStatusForm(petId, statuscode) {
        if (!petId) return;
        const cardTemplate = document.querySelector('#change-status-card').content;
        const card = cardTemplate.querySelector('#change-status-window').cloneNode(true);
        const $subContainer = $('#sub_container');

        card.querySelector('#error-message').textContent = '';
        card.querySelector('#pet_id').value = petId;
        const curOption = card.querySelector('#status_new option[value="' + statuscode + '"]');
        if (curOption) {
            curOption.disabled = true;
        }
        card.querySelector('#change-status-form').addEventListener('submit', (ev) => {
            ev.preventDefault();
            changePetStatus(petId);
        });

        $subContainer.empty();
        $subContainer.append(card);

        $('#background').fadeIn();
        $('#container').show();
    }

    function changePetStatus(petId) {
        const $form = $('#change-status-window #change-status-form');
        const formData = $form.serializeArray();

        const deferred = jQuery.ajax({
            'async': true,
            'global': false,
            'cache': false,
            'type': 'POST',
            'data': formData,
            'url': 'change-pet-status/' + petId,
            'beforeSend': function() {
                showTopLoading();
            },
            'success': function () {
                searchPets();
            }
        }).always(() => {
            closeTopLoading();
        });
        closeInformingWindow(true);
        return deferred;
    }

    function confirmDeletePet(id){
        const text = 'Карточка животного будет удалена. Продолжить удаление?';
        const cssButtonClass = 'delete';
        showAcceptWindow('Удаление животного', text, 'deletePet(' + id + ');', 'Подтвердить удаление', cssButtonClass);
    }

    function deletePet(petId) {
        closeAcceptWindow();

        return jQuery.ajax({
            'async': true,
            'global': false,
            'cache': false,
            'type': 'POST',
            'url': 'delete-pet/' + petId,
            'beforeSend': function() {
                showTopLoading();
            },
            'success': function () {
                searchPets();
            }
        }).always(() => {
            closeTopLoading();
        });
    }
</script>
