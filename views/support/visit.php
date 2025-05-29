<?php

use app\assets\AutocompleteAsset;

$this->title = 'Приемы';

AutocompleteAsset::register($this);
?>
<h1>Приёмы</h1>

<div class="info"></div>
<form id="form_search_recs" onsubmit="searchVisits(); return false;">
    <h2>Поиск приёмов</h2>
    <br/>
    <div class="row">
        <div class="col">
            <label for="id">№ приема</label>
            <input type="text" id="id" name="id" class="w-100"
                   placeholder="ID приёма. Например: 153289." value=""/>
        </div>
        <div class="col">
            <label for="status">Статус приема</label>
            <select id="status" name="status">
                <option value="">Все</option>
                <option value="W">В работе</option>
                <option value="N">Новый</option>
                <option value="F">Завершенный</option>
                <option value="O">Завершенный (неоплачен)</option>
                <option value="A">Отмененный</option>
                <option value="T">Перенесён</option>
                <option value="B">Бронирован</option>
            </select>
        </div>
        <div class="col">
            <label for="channel">Тип записи</label>
            <select id="channel" name="channel">
                <option value="">Все</option>
                <option value="4">Живая очередь</option>
                <option value="3">Запись по телефону</option>
                <option value="2">mos.ru</option>
                <option value="1">По направлению</option>
                <option value="10">Неотложная помощь</option>
            </select>
        </div>
    </div>
    <br/>
    <div class="row">
        <div class="col">
            <label for="owner">Владелец животного</label>
            <input type="text" id="owner" name="owner" class="w-100" placeholder="" value=""/>
        </div>
        <div class="col">
            <label for="specialist">Специалист</label>
            <input type="text" id="specialist" name="specialist" class="w-100" placeholder="" value=""/>
        </div>
        <div class="col">
            <label for="organization">Организация</label>
            <input type="text" id="organization" name="organization" class="w-100" placeholder="" value=""/>
        </div>
    </div>
    <br/>

    <div class="row mb-4 align-items-end">
        <div class="col-md-9">
            <div class="row g-3">
                <div class="col-md-5 mr-4">
                    <label for="creation_from" class="form-label">Дата создания</label>
                    <div class="d-flex align-items-center">
                        <input type="date" class="form-control w-auto" name="creation_from"
                               id="creation_from"/>
                        <span class="mx-2">-</span>
                        <input type="date" class="form-control w-auto" name="creation_to"
                               id="creation_to"/>
                    </div>
                </div>

                <div class="col-md-5">
                    <label for="visit_from" class="form-label">Время приема</label>
                    <div class="d-flex align-items-center">
                        <input type="datetime-local" class="form-control" name="visit_from"
                               id="visit_from"/>
                        <span class="mx-2">-</span>
                        <input type="datetime-local" class="form-control" name="visit_to"
                               id="visit_to"/>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 d-flex justify-content-end gap-2">
            <button type="reset" class="btn btn-secondary mr-3">Сбросить</button>
            <button type="submit" class="btn btn-primary">Поиск</button>
        </div>
    </div>
</form>
<div class="border-top py-1 my-4 border-custom-teal"></div>

<button type="button" class="cancel-button" id="cancelVisitsBtn" onclick="confirmCancelCheckedVisits();">Отменить
    приемы
</button>

<div style="margin-top: 30px;" id="ListRecs" class="recs row main_row"></div>

<!-- Форма подтверждения отмены визитов -->
<template id="cancel-visits-card">
    <div id="cancel-visits-window" class="window main_row col-xl-6 col-lg-10 col-12">
        <div class="close" onclick="closeInformingWindow(true);"></div>

        <div class="top">Отмена приемов</div>
        <div class="col-12 mt-2">
            <div class="error" id="error-message" style="display: none;"></div>
        </div>

        <form class="form-window main_row" id="cancel-visits-form">
            <div class="main_row">
                <input type="hidden" id="ids" name="ids">
                <div class="col-12 mb-4">
                    Выбрано <span id="count-visit">2 приёма</span>. Опишите причину и подтвердите отмену.
                </div>

                <div class="col-12 mb-4">
                    <label for="reason" class="form-label mb-2 required">Причина отмены</label>
                    <textarea class="form-control" placeholder="Причина" id="reason" name="reason" rows="4" cols="50" style="height: 80px; resize: none;"></textarea>
                </div>
            </div>

            <div class="controls">
                <button type="reset" class="btn btn-secondary w-auto mr-2" onclick="closeInformingWindow(true);">Отмена</button>
                <button type="submit" class="btn btn-danger w-auto" id="cancel-visits" onclick="return saveCancelVisitsForm()">Подтвердить</button>
            </div>
        </form>
    </div>
</template>

<script>
    $(function() {
        $("#owner").autocomplete({
            source: "autocomplete-pet-owner",
            minLength: 2,
        });
        $("#specialist").autocomplete({
            source: "autocomplete-specialist",
            minLength: 2,
        });
        $("#organization").autocomplete({
            source: "autocomplete-organization",
            minLength: 2,
        });
    });

    function getReponseError(err, defaultMessage) {
        if (err && err.errors && err.errors.length) {
            const e = err.errors[0];
            if (e.detail) return e.detail;
        }
        return defaultMessage;
    }

    function searchVisits() {
        const formData = $('#form_search_recs').serializeArray();

        $('.info').empty();
        jQuery.ajax({
            'async': true,
            'global': false,
            'cache': false,
            'type': 'GET',
            //'dataType': 'xml',
            'data': formData,
            'url': "search-visits",
            'beforeSend': function() {
                forceLoading('ListRecs');
            },
            'success': function (Recs) {
                if ($(Recs).find('message').text() == 'token_invalid') {
                    window.location.href = 'index.php';
                } else {
                    const html = buildResponseTable(Recs);
                    $('#ListRecs').html(html);
                }
            }
        }).always(() => {
            closeLoading('ListRecs');
        }).fail((xhr) => {
            const err = getReponseError(xhr.responseJSON, 'Произошла ошибка при получении списка визитов');
            $('.info').html(err).show();
        });
    }

    function buildResponseTable(data) {
        let TempContent='';
        let count = 0;

        if (data && data.length) {
            for (let i = 0; i < data.length; ++i) {
                const d = data[i];
                const id = d.id;

                let html = '<div class="rec col-12 main_row row">';
                html += checkBoxColumn(d);
                html += visitColumn(d, id);
                html += contactColumn(d);
                html += serviceColumn(d);
                html += actionColumn(d, id);

                html += '</div>';

                ++count;
                TempContent += html;
            }
        }

        if (count == 0) {
            TempContent = '<div class="message">По данному запросу не найдено записей.</div>';
        }
        return TempContent;
    }

    function checkBoxColumn(d) {
        let html = '<div class="col-xl-1 col-lg-1 col-12 d-flex justify-content-center pt-4">';
        html += '<input type="checkbox" class="styled-checkbox" data-id="' + d.id + '"/>';
        html += '</div>';
        return html;
    }

    function visitColumn(d, id) {
        let html = '<div class="col-xl-3 col-lg-3 col-md-12 col-sm-12 col-xs-12 col-12 text-reset info-text">';
        html += 'Приём <strong>№ ' + d.id + '</strong><br/>';
        html += 'Канал: <strong>' + getChannel(+d.channel) + '</strong><br/>';
        html += 'Статус: <strong>' + getStatus(d.status) + '</strong><br/>';
        const date = d.start_date;
        if (date) {
            const time1 = d.start_time;
            const time2 = d.end_time;
            html += '<strong>' + getDateInterval(date, time1, time2) + '</strong><br/>';
        }
        html += '<strong class="fs-14px">' + d.specialist + '</strong><br/>';
        html += '<span class="org_name">' + d.org_name + '</span><br/>';
        const orgAddress = d.org_address;
        if (orgAddress) {
            html += '<span class="org_area">' + orgAddress + '</span><br/>';
        }
        html += '<a href="index.php?action=visits_logs&id=' + id + '">Логи приёма</a></div>';
        return html;
    }

    function contactColumn(d) {
        let html = '<div class="col-xl-3 col-lg-3 col-md-12 col-sm-12 col-xs-12 col-12 contacts">';
        html += '<strong class="fs-14px">' + d.owner_name + '</strong>';
        html += '<div class="info-text p-0">';
        if (d.contacts && d.contacts.length) {
            html += '<span>Контакты</span><br/>';
            for (let i = 0; i < d.contacts.length; ++i) {
                const c = d.contacts[i];
                const typeId = +c.type_id;
                const typeTitle = c.type_title;
                const name = c.name;
                html += getContact(typeId, typeTitle, name) + '<br/>';
            }
        }
        html += '<span>Животные:</span><br/>';
        if (d.pets && d.pets.length) {
            for (let i = 0; i < d.pets.length; ++i) {
                const p = d.pets[i];
                const id = p.id;
                const name = p.name;
                html += getPet(id, name) + '<br/>';
            }
        }
        html += '</div></div>';
        return html;
    }

    function serviceColumn(d) {
        let html = '<div class="col-xl-3 col-lg-3 col-md-12 col-sm-12 col-xs-12 col-12">';
        html += 'Услуги приёма: ';
        if (d.services && d.services.length) {
            for (let i = 0; i < d.services.length; ++i) {
                const s = d.services[i];
                html += '<div class="service_item p-1">' + s + '</div>';
            }
        }
        html += '</div>';
        return html;
    }

    function actionColumn(d, id) {
        let html = '<div class="col-xl-2 col-lg-2 col-12 p-2">';
        html += '<div class="d-inline-flex flex-column">';
        const status = d.status;
        if (status == 'N' || status == 'W') {
            html += '<button class="button btn-mini mb-3" onclick="closeVisit(' + id + ');">Закрыть приём</button>';
            html += '<button class="button btn-mini mb-3" onclick="cancelVisit(' + id + ');" style="margin-left: 10px;">Отменить приём</button>';
        } else if (status == 'F' || status == 'A' || status == 'O') {
            html += '<button class="button btn-mini  mb-3" onclick="workVisit(' + id + ');">Вернуть приём в работу</button>';
        }
        const paid = d.paid;
        if (paid) {
            html += '<button class="btn-secondary btn-mini mb-3" onclick="cancelVisitPaid(' + id + ');" style="margin-left: 10px;">Отменить оплату</button>';
        }
        html += '</div></div>';
        return html;
    }

    function getChannel(v) {
        switch (v) {
            case 1:
                return 'По направлению';
            case 2:
                return 'mos.ru';
            case 3:
                return 'Запись по телефону';
            case 4:
                return 'Живая очередь';
            case 10:
                return 'Неотложная помощь';
            default:
                return 'Не известен';
        }
    }

    function getStatus(v) {
        switch (v) {
            case 'N':
                return 'Новый';
            case 'F':
                return 'Завершен';
            case 'O':
                return 'Завершен (неоплачен)';
            case 'A':
                return 'Отменен';
            case 'W':
                return 'В работе';
            case 'T':
                return 'Перенесен';
            case 'C':
                return 'Изменен';
            case 'D':
                return 'Пациент не явился';
            case 'B':
                return 'Бронирован';
        }
    }

    function getDateInterval(date, time1, time2) {
        return date.toLocaleString('ru', {year: 'numeric', month: 'long', day: 'numeric'}) + ' с ' +
            time1 + ' по ' + time2;
    }

    function getContact(typeId, typeTitle, name) {
        let html = '<strong title="' + typeTitle + '"';
        if (typeId == 1) {
            html+=' class="ico mobiletelephone"';
        } else if (typeId == 6) {
            html+=' class="ico mail"';
        }
        html += '>' + name + '</strong>';
        return html;
    }

    function getPet(id, name) {
        return '<a href="https://vetas.mos.ru/animals/' + id + '/edit/main-info/" target="_blank"><strong>' +
            name + '</strong></a> (' + id + ')';
    }

    function updateVisitStatus(id, action) {
        if (id) {
            jQuery.ajax({
                'async': true,
                'global': false,
                'cache': false,
                'type': 'GET',
                'url': action + '/' + id,
                'beforeSend': function () {
                    forceLoading('ListRecs');
                },
                'success': function (Data) {
                    searchVisits();
                }
            }).fail((xhr) => {
                closeLoading('ListRecs');
                const err = getReponseError(xhr.responseJSON, 'Произошла ошибка при изменении статуса приема');
                $('.info').html(err).show();
            });
        }
    }

    function closeVisit(id) {
        updateVisitStatus(id, 'close-visit');
    }

    function workVisit(id) {
        updateVisitStatus(id, 'work-visit');
    }

    function cancelVisit(id) {
        updateVisitStatus(id, 'cancel-visit');
    }

    function cancelVisitPaid(id) {
        updateVisitStatus(id, 'cancel-visit-paid');
    }

    function confirmCancelCheckedVisits() {
        let ids = [];
        $('#ListRecs input:checkbox:checked').each(function() {
            ids.push(+$(this).attr('data-id'));
        });
        console.log(ids);

        if (ids && ids.length) {
            showCancelVisitsForm(ids);
        }
    }

    function showCancelVisitsForm(ids) {
        const cardTemplate = document.querySelector('#cancel-visits-card').content;
        const card = cardTemplate.querySelector('#cancel-visits-window').cloneNode(true);
        const $subContainer = $('#sub_container');

        card.querySelector('#error-message').textContent = '';
        if (ids) {
            card.querySelector('#ids').value = ids;
            card.querySelector('#reason').value = '';
            card.querySelector('#count-visit').textContent = fmtCountVisit(ids.length);
        }

        $subContainer.empty();
        $subContainer.append(card);

        $('#background').fadeIn();
        $('#container').show();
    }

    function fmtCountVisit(count) {
        return count + ' приема';
    }

    function saveCancelVisitsForm() {
        if (validateCancelVisitsForm()) {
            saveCancelVisits((data) => {
                if (data.failed && data.failed.length) {
                    showFailedAlert(data.failed);
                } else {
                    searchVisits();
                }
            });
            closeInformingWindow(true);
        }
        return false;
    }

    function validateCancelVisitsForm() {
        const $cancelVisitsWin = $('#cancel-visits-window');
        const $errorMessage = $cancelVisitsWin.find('#error-message');
        const $cancelVisitsForm = $cancelVisitsWin.find('#cancel-visits-form');
        const $reason = $cancelVisitsForm.find('#reason');

        $errorMessage.hide();
        $errorMessage.empty();

        if ($reason.val() === '') {
            $errorMessage.html('Не заполнены необходимые поля.');
            $errorMessage.show();
            return false;
        }

        return true;
    }

    function saveCancelVisits(cb) {
        const formData = $('#cancel-visits-window #cancel-visits-form').serialize();

        return jQuery.ajax({
            'async': true,
            'global': false,
            'cache': false,
            'type': 'POST',
            'data': formData,
            'url': "cancel-visit-list",
            'beforeSend': function() {
                showTopLoading();
            },
            'success': function (data) {
                if (cb) {
                    cb(data);
                }
            }
        }).always(() => {
            closeTopLoading();
        });
    }

    function showFailedAlert(failed) {
        let message = '<div style="max-height: 300px; overflow-y: auto; overflow-x: hidden;">';
        for (let i  = 0; i < failed.length; ++i) {
            const f = failed[i];
            if (i != 0) {
                message += ', ';
            }
            message += formatCancelFailedReason(f.id, f.status);
        }
        message += '</div>';
        showMessageWindow(message, 'Понятно', 'Отмена приемов');
    }

    function formatCancelFailedReason(id, status) {
        return 'Приём № <strong>' + id + '</strong> не может быть отменен, так как имеет статус <strong>' + getStatus(status) + '</strong>.';
    }
</script>
