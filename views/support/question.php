<?php
$this->title = 'Вопросы пользователей';
?>
<h1>Поиск вопросов</h1>

<div class="alert alert-danger" id="info" style="display: none;"></div>
<form id="form_search_quest" onsubmit="searchQuests(); return false;">
    <div class="row my-3">
        <div class="col-4">
            <label for="date1">Дата создания</label>
            <div class="d-flex justify-content-between">
                <input type="date" id="date1" name="date1" class="w-auto" style="min-width: 145px;"> <span class="mx-1">-</span> <input type="date" id="date2" name="date2" class="w-auto" style="min-width: 145px;">
            </div>
        </div>
        <div class="col-4">
            <label for="status">Состояние вопроса</label>
            <select id="status" name="status">
                <option value="">Все вопросы</option>
                <option value="G">Предоставлен ответ</option>
                <option value="W">Ожидается ответ</option>
            </select>
        </div>
        <div class="col-4">
            <label for="login">Пользователь</label>
            <input type="text" id="login" name="login" class="w-100" placeholder="Логин пользователя. Например: UserUU.">
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-4">
            <label for="keyword">Ключевые слова</label>
            <input type="text" id="keyword" name="keyword" class="w-100" placeholder="Ключевое слово.">
        </div>
        <div class="col-4 d-flex">
            <div class="form-check align-self-end">
                <label><input type="checkbox" class="styled-checkbox" id="use_search" name="use_search"> Использовать для поиска</label>
            </div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="offset-8 col-4">
            <div class="d-flex justify-content-between">
                <button type="reset">Сбросить</button>
                <button type="submit" class="ml-3">Применить</button>
            </div>
        </div>
    </div>
</form>

<div class="table" id="ListRecs">
    <div class="p-3">...</div>
</div>

<!-- Карточка ответа на вопрос пользователя -->
<template id="response_user_card">
    <div id="response_user_window" class="window main_row col-xl-6 col-lg-10 col-12">
        <div class="close" onclick="closeInformingWindow();"></div>

        <div class="top">Ответ на вопрос пользователя</div>
        <div class="col-xl-12 col-lg-12 mt-2">
            <div class="error" id="response_user_message" style="display: none;"></div>
        </div>

        <form class="form-window main_row" id="response_user_form">
            <div class="col-xl-12 col-lg-12 main_row">
                <input type="hidden" id="id" name="id">
                <div class="col-12 required">Ответ:</div>
                <div class="col-12 mb-3">
                    <textarea id="answer" name="answer" rows="5"></textarea>
                </div>
                <div class="col-12">Ключевые слова:</div>
                <div class="col-12 mb-3">
                    <input type="text" id="keywords" name="keywords" autocomplete="off">
                </div>

                <div class="col-12">Предоставлен ответ:</div>
                <div class="col-12 mb-3">
                    <input type="checkbox" class="styled-checkbox" id="give_answer" name="give_answer">
                </div>

                <div class="col-12">Использовать для поиска:</div>
                <div class="col-12 mb-3">
                    <input type="checkbox" class="styled-checkbox" id="use_search" name="use_search">
                </div>
            </div>

            <div class="controls">
                <button type="reset" class="add cancel mr-2" id="" style="width: 200px;">Сбросить</button>
                <button class="appointment" style="width: 200px;" id="save_response" onclick="return saveResponseToUserForm()">Сохранить</button>
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

    function checkBox(val) {
        const checked = val ? ' checked' : '';
        return '<input type="checkbox" class="styled-checkbox" onclick="return false"' + checked + ' class="">';
    }

    function fileLink(hasFile, id) {
        if (hasFile)
            return '<a href="download-file?id=' + id + '" download>Файл</a>';
        return '';
    }

    function userName(fio, login) {
        if (!login) return fio;
        return fio + '<br>Логин:<br> ' + login
    }

    function searchQuests() {
        const $listRecs = $('#ListRecs');
        const formData = $('#form_search_quest').serializeArray();

        jQuery.ajax({
            'async': true,
            'global': false,
            'cache': false,
            'type': 'GET',
            'data': formData,
            'url': 'search-questions',
            'beforeSend': function() {
                // показать загрузку
                $listRecs.addClass('loading');
            },
            'success': function (data) {
                $('#info').hide().empty();
                if (data.message == 'token_invalid') {
                    window.location.href = 'index.php';
                } else {
                    let html = buildResponseTable(data);
                    $listRecs.html(html);
                }
            }
        }).always(() => {
            $listRecs.removeClass('loading');
        }).fail((xhr) => {
            const err = getReponseError(xhr.responseJSON, 'Произошла ошибка при получении списка вопросов');
            $('#info').html(err).show();
        });
    }

    function buildResponseTable(data) {
        let html = '';
        const count = data.length;

        if (count > 0) {
            const enc = getHtmlEncoder();
            html += '<table class="w-100">';

            html += '<tr>';
            html += '<th>Дата обращения</th>';
            html += '<th>Пользователь</th>';
            html += '<th>Вопрос</th>';
            html += '<th>Приложение к вопросу</th>';
            html += '<th>Ответ службы поддержки</th>';
            html += '<th>Ключевые слова</th>';
            html += '<th>Предоставлен ответ</th>';
            html += '<th>Использовать для поиска</th>';
            html += '<th></th>';
            html += '</tr>';

            for (let i = 0; i < count; ++i) {
                let d = data[i];
                let id = d.id;

                let row = '<tr>';
                row += '<td>' + enc(d.create_date) + '</td>';
                row += '<td>' + userName(enc(d.fullname), enc(d.login)) + '</td>';
                row += '<td>' + enc(d.question) + '</td>';
                row += '<td>' + fileLink(d.has_file, id) + '</td>';
                row += '<td>' + enc(d.answer) + '</td>';
                row += '<td>' + enc(d.keywords) + '</td>';
                row += '<td>' + checkBox(d.answer_status) + '</td>';
                row += '<td>' + checkBox(d.search_status) + '</td>';

                row += '<td style="width: 32px;"><div class="edit" onclick="showResponseToUserDlg(' + id + ');"></div></td>';
                row += '</tr>';

                html += row;
            }

            html += '</table>';
        }

        if (count == 0) {
            html = '<div class="message">По данному запросу не найдено записей.</div>';
        }
        return html;
    }

    function showResponseToUserDlg(id) {
        jQuery.ajax({
            'async': true,
            'global': false,
            'cache': false,
            'type': 'GET',
            //'dataType': 'json',
            'data': '&id=' + id,
            'url': 'answer',
            'beforeSend': function() {
                // показать загрузку
                showTopLoading();
            },
            'success': function (data) {
                closeTopLoading();
                renderResponseToUserDlg(data);
            }
        }).always(() => {
            closeTopLoading();
        });
    }

    function closeResponseToUserDlg() {
        $("#background").fadeOut();
        $("#container").hide();
        $('#sub_container').empty();
    }

    function renderResponseToUserDlg(data) {
        const cardTemplate = document.querySelector('#response_user_card').content;
        const card = cardTemplate.querySelector('#response_user_window').cloneNode(true);
        const $subContainer = $('#sub_container');

        if (data) {
            card.querySelector('#id').value = data.id;
            card.querySelector('#answer').value = data.answer;
            card.querySelector('#keywords').value = data.keywords;
            card.querySelector('#give_answer').checked = data.answer_status;
            card.querySelector('#use_search').checked = data.search_status;
        }

        $subContainer.empty();
        $subContainer.append(card);

        $('#background').fadeIn();
        $('#container').show();
    }

    function getReponseError(err, defaultMessage) {
        if (err && err.errors && err.errors.length) {
            const e = err.errors[0];
            if (e.detail) return e.detail;
        }
        return defaultMessage;
    }

    function saveResponseToUserForm() {
        if (validateResponseToUserForm()) {
            saveResponseToUser((data) => {
                closeResponseToUserDlg();
                showMessageWindow('Ответ на вопрос пользователя успешно сохранен.');
                searchQuests();
            }).fail((xhr/*, textStatus, errorThrown*/) => {
                const err = getReponseError(xhr.responseJSON, 'Произошла ошибка при сохранении ответа пользователя');
                $('#response_user_window #response_user_message').html(err).show();
                $('#response_user_window #save_response').prop('disabled', false);
            });
        }
        return false;
    }

    function validateResponseToUserForm() {
        const $responseUserWin = $('#response_user_window');
        const $responseUserMessage = $responseUserWin.find('#response_user_message');
        const $responseUserForm = $responseUserWin.find('#response_user_form');
        const $answer = $responseUserForm.find('#answer');
        const $keywords = $responseUserForm.find('#keywords');
        const $giveAnswer = $responseUserForm.find('#give_answer');
        const $useSearch = $responseUserForm.find('#use_search');

        $responseUserMessage.hide();
        $responseUserMessage.empty();

        if ($answer.val() === '') {
            $responseUserMessage.html('Не заполнены необходимые поля.');
            $responseUserMessage.show();
            return false;
        }

        $keywords.val($keywords.val().trim());
        if ($useSearch.prop('checked') && $keywords.val() === '') {
            $responseUserMessage.html('Флажок «Использовать для поиска» не может быть выбран если не заполнено поле «Ключевые слова»');
            $responseUserMessage.show();
            return false;
        }

        return true;
    }

    function saveResponseToUser(cb) {
        const formData = $('#response_user_window #response_user_form').serialize();

        return jQuery.ajax({
            'async': true,
            'global': false,
            'cache': false,
            'type': 'POST',
            //'dataType': 'json',
            'data': formData,
            'url': "save-answer",
            'beforeSend': function() {
                $('#response_user_window #save_response').prop('disabled', true);
            },
            'success': function (data) {
                if (cb) {
                    cb(data);
                } else {
                    closeResponseToUserDlg();
                    searchQuests();
                }
            }
        });
    }
</script>