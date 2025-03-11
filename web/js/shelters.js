var ArrayServiceTypesRecs = new Array();
var ArrayDocumentTypesRecs = new Array();
var ArrayDocumentRecs = new Array();
var ArrayAreasRecs = new Array();
var ArraySheltersRecs = new Array();
var ArraySpecs = new Array();
var ArrayDrugs = new Array();
var ArrayVaccines = new Array();
var ArrayOrganizations = new Array();

var fiasTokken = '';
var currentPage = '';
var currentSort = '';
var currentDirection = 0;
var currentLevel = 0;
var currentShelterTitle = '';
var currentOrganzationTitle = '';
var skill_input;

const deathReasons = []

fetch(`${window.API_URL}/v3/shelters/dictionary`)
    .then(res => res.json())
    .then(json => {
        deathReasons.push(...(json.is_success ? json.data.death_reasons : []))
    })


function showSheltersVacWindow() {
    let vaccinations = vetas_get_cookie("vaccinations");

    jQuery.ajax({
        'async': true,
        'global': false,
        'cache': false,
        'type': 'GET',
        'dataType': 'xml',
        'data': '&pets=' + vaccinations + '&mode=xml&action=vac_pets',
        'url': "index.php",
        'beforeSend': function () {
            //показать загрузку
            showTopLoading();
        },
        'success': function (Data) {
            closeTopLoading();

            currentOrganzationTitle = $(Data).find('organization').text();

            if ($(Data).find('specialists').text()) {
                $(Data).find('specialists').find('rec').each(function () {
                    ArraySpecs.push({name: $(this).find('name').text(), id: $(this).find('id').text()});
                });
            }

            if ($(Data).find('drugs').text()) {
                $(Data).find('drugs').find('rec').each(function () {
                    ArrayDrugs.push({
                        name: $(this).find('name').text(),
                        producer: $(this).find('producer').text(),
                        id: $(this).find('id').text()
                    });
                });
            }

            if ($(Data).find('vaccines').text()) {
                $(Data).find('vaccines').find('rec').each(function () {
                    ArrayVaccines.push({
                        name: $(this).find('name').text(),
                        producer: $(this).find('producer').text(),
                        id: $(this).find('id').text()
                    });
                });
            }

            if ($(Data).find('organizations').text()) {
                $(Data).find('organizations').find('rec').each(function () {
                    ArrayOrganizations.push({name: $(this).find('name').text(), id: $(this).find('id').text()});
                });
            }

            let TempContent = '';
            TempContent += '<div id="shelters_vac_window" class="window main_row col-xl-10 col-lg-10 col-12">';
            //
            TempContent += '<div class="close" onclick="closeSheltersVacWindow();"></div>';
            TempContent += '<div class="top">Список вакцинирования</div>';

            TempContent += '<div class="error" id="shelters_vac_message" style="display: none; margin: 15px;"></div>';

            TempContent += '<div class="col-12">';
            TempContent += '<p class="title" style="margin: 0px;">Вакцинация</p>';
            TempContent += '<form enctype="multipart/form-data" method="POST" class="form-window main_row" id="form_shelters_save_vac" style="padding: 0;">';

            TempContent += '<input type="hidden" name="pets" value="' + vaccinations + '">';
            //
            TempContent += '<div id="rabies_vaccinations_values" class="table inner">';
            TempContent += '<table class="w-100">';
            TempContent += '<tr>';
            TempContent += '<th>Дата вакцинации</th>';
            TempContent += '<th>Наименование вакцины</th>';
            TempContent += '<th>Производитель</th>';
            TempContent += '<th>Организация</th>';
            TempContent += '<th>ФИО врача</th>';
            TempContent += '<th>Номер партии/серии</th>';
            TempContent += '<th>Действительно до</th>';
            TempContent += '<th>Сторонняя организация</th>';
            TempContent += '</tr>';

            TempContent += '<tr>';
            TempContent += '<td class="date_">';
            TempContent += '<input type="date" name="date" id="date" value="" min="1950-01-01" max="' + get_current_date() + '">';
            TempContent += '</td>';

            TempContent += '<td class="drug_">';
            if (ArrayVaccines != undefined) {
                TempContent += '<select name="drug" id="drug" class="drug" onchange="changeDrug(this);">';
                TempContent += '<option value="">Не выбран</option>';
                if (ArrayVaccines.length > 0) {
                    for (let i = 0; i <= ArrayVaccines.length - 1; i++) {
                        TempContent += '<option value="' + ArrayVaccines[i].id + '" producer="' + ArrayVaccines[i].producer + '">' + ArrayVaccines[i].name + '</option>';
                    }
                }
                TempContent += '</select>';
            }
            TempContent += '</td>';

            TempContent += '<td class="producer_name">';
            TempContent += '<div class="producer"></div>';
            TempContent += '</td>';

            // Организация
            TempContent += '<td>';
            TempContent += '<div class="organization1"';
            TempContent += ' style="display: none;"';
            TempContent += '>';
            if (ArrayOrganizations != undefined) {
                TempContent += '<select name="organization" id="organization" class="organization">';
                TempContent += '<option value="">Не выбран</option>';
                if (ArrayOrganizations.length > 0) {
                    for (let i = 0; i <= ArrayOrganizations.length - 1; i++) {
                        TempContent += '<option value="' + ArrayOrganizations[i].id + '">' + ArrayOrganizations[i].name + '</option>';
                    }
                }
                TempContent += '</select>';
            }
            TempContent += '</div>';
            //
            TempContent += '<div class="organization2" style="display: block;">';
            TempContent += '' + currentOrganzationTitle + '';
            TempContent += '</div>';

            TempContent += '</td>';
            // Организация

            // ФИО врача
            TempContent += '<td>';
            TempContent += '<div class="specialist1" style="display: block;">';
            if (ArraySpecs != undefined) {
                TempContent += '<select name="specialist" id="specialist">';
                TempContent += '<option value="">Не выбран</option>';
                if (ArraySpecs.length > 0) {
                    for (let i = 0; i <= ArraySpecs.length - 1; i++) {
                        TempContent += '<option ';
                        TempContent += 'value="' + ArraySpecs[i].id + '">' + ArraySpecs[i].name + '</option>';
                    }
                }
                TempContent += '</select>';
            }
            TempContent += '</div>';
            TempContent += '</td>';
            // ФИО врача

            // Номер партии/серии
            TempContent += '<td>';
            TempContent += '<input type="text" name="batch" id="batch" class="" value="" autocomplete="off">';
            TempContent += '</td>';

            // Срок годности
            TempContent += '<td style="width: 150px;">';
            TempContent += '<input type="date" name="expiry_date" id="expiry_date" class="" value="" autocomplete="off">';
            TempContent += '</td>';

            // Действительно до
            TempContent += '<td style="width: 150px;">';
            TempContent += '<input type="date" name="valid_until" id="valid_until" class="" value="" autocomplete="off">';
            TempContent += '</td>';

            // Сторонняя организация
            TempContent += '<td style="padding-top: 0px;">';
            TempContent += '<label class="checkbox_container">';
            TempContent += '<input type="checkbox" name="is_out_org" id="is_out_org" ';
            TempContent += 'value="1" onclick="changeIsOutOrg(this);">';
            TempContent += '<span class="checkbox_checkmark"></span>';
            TempContent += '</label>';
            TempContent += '</td>';

            TempContent += '</table>';
            TempContent += '</div>';
            //

            TempContent += '<input type="hidden" name="action" id="action" value="save_vac">';
            TempContent += '<input type="hidden" name="mode" id="mode" value="xml">';

            TempContent += '</form>';
            TempContent += '</div>';

            TempContent += '<div class="col-12">';
            TempContent += '<p class="title" style="margin: 0px;">Список вакцинирования</p>';

            TempContent += '<div class="table inner" id="shelters_vac" style="overflow-y: overlay; overflow-x: hidden;">';
            TempContent += '<table class="w-100">';
            TempContent += '<tr>';
            TempContent += '<th>№ К/У</th>';
            TempContent += '<th>№ чипа</th>';
            TempContent += '<th>Кличка</th>';
            TempContent += '<th>Вид</th>';
            TempContent += '</tr>';

            $(Data).find('recs').find('rec').each(function () {
                TempContent += '<tr>';
                TempContent += '<td>' + $(this).find('rec_ku').text() + '</td>';
                TempContent += '<td>' + $(this).find('rec_chip').text() + '</td>';
                TempContent += '<td>' + $(this).find('rec_name').text() + '</td>';
                TempContent += '<td>' + $(this).find('rec_species').text() + '</td>';
                TempContent += '</tr>';
            });

            TempContent += '</table>';
            TempContent += '</div>';

            TempContent += '</div>';

            //Кнопки управления
            TempContent += '<div class="controls">';
            TempContent += '<button class="button" id="vac_pets" style="width: 250px;" onclick="vacSheltersPets();">Вакцинировать</button>';
            TempContent += '</div>';
            //Кнопки управления

            TempContent += '</div>';

            document.getElementById("sub_container_m").innerHTML = TempContent;

            $('#sub_container_m .drug').multiselect({
                enableClickableOptGroups: true,
                enableFiltering: true,
                enableCaseInsensitiveFiltering: true,
                nonSelectedText: 'Выберите...',
                allSelectedText: "Выбраны все",
                nSelectedText: "выбрано",
                filterPlaceholder: 'Выбрать запись...'
            });
            $('#sub_container_m .organization').multiselect({
                enableClickableOptGroups: true,
                enableFiltering: true,
                enableCaseInsensitiveFiltering: true,
                nonSelectedText: 'Выберите...',
                allSelectedText: "Выбраны все",
                nSelectedText: "выбрано",
                filterPlaceholder: 'Выбрать запись...'
            });

            $("#background_m").fadeIn();
            $("#container_m").show();

            if ($("#shelters_vac_window").height() - 500 >= 150) {
                $("#shelters_vac").height($("#shelters_vac_window").height() - 500);
            }
        }
    });
}

function closeSheltersVacWindow() {
    $("#background_m").fadeOut();
    $("#container_m").hide();
}

function vacSheltersPets() {
    let date_error = 0;
    $("#rabies_vaccinations_values tr").each(function (index) {
        if (index != 0) {
            if ($(this).find("input[type='date']").val() == '') {
                date_error = 1;
            }
        }
    });

    if (date_error == 1) {
        $("#shelters_vac_message").html('Дата обязательна для заполнения.');
        $("#shelters_vac_message").addClass('error');
        $("#shelters_vac_message").show();

        return true;
    }

    jQuery.ajax({
        'async': true,
        'global': false,
        'cache': false,
        'type': 'POST',
        'dataType': 'xml',
        'data': jQuery("#form_shelters_save_vac").serialize() + '&action=save_vac_pets&mode=xml',
        'url': "index.php",
        'beforeSend': function () {
            //показать загрузку
            showTopLoading();
        },
        'success': function (Data) {
            if ($(Data).find('message').text() == 'token_invalid') {
                window.location.href = 'index.php';
            } else {
                if ($(Data).find('message').text() == 'vac_pets_saved') {
                    closeTopLoading();
                    closeSheltersVacWindow();
                    listShowSheltersPets(currentPage, currentSort, currentDirection);
                }
            }
        }
    });
}

/* вольеры */
function listShowAviariesRecs() {
    jQuery.ajax({
        'async': true,
        'global': false,
        'cache': false,
        'type': 'GET',
        'dataType': 'xml',
        'data': jQuery("#form_search_recs").serialize() + '&action=aviaries&mode=xml',
        'url': "index.php",
        'beforeSend': function () {
            //показать загрузку
            showLoading('ListRecs');
        },
        'success': function (Recs) {
            if ($(Recs).find('message').text() == 'token_invalid') {
                window.location.href = 'index.php';
            } else {
                let TempContent = '';
                let TempRecsNum = 0;

                if ($(Recs).find('rec').text()) {
                    TempContent += '<table class="w-100">';
                    TempContent += '<tr>';
                    TempContent += '<th>Название вольера</th>';
                    TempContent += '<th>№ вольера</th>';
                    TempContent += '<th>Описание вольера</th>';
                    TempContent += '<th></th>';
                    TempContent += '<th></th>';
                    TempContent += '</tr>';

                    $(Recs).find('rec').each(function () {
                        let FLAG = 0;
                        let TempContentRec = '<tr>';
                        TempContentRec += '<td>' + $(this).find('rec_title').text() + '</td>';
                        TempContentRec += '<td>' + $(this).find('rec_number').text() + '</td>';
                        TempContentRec += '<td>' + $(this).find('rec_description').text() + '</td>';
                        if (window.canUserEdit) {
                            TempContentRec += '<td style="width: 32px;" onclick="showAviaryWindow(' + $(this).find('rec_id').text() + ');"><div class="edit"></div></td>';
                            TempContentRec += '<td style="width: 32px;" onclick="deleteAviaryAcceptWindow(\'' + $(this).find('rec_title').text() + '\', ' + $(this).find('rec_id').text() + ');"><div class="delete"></div></td>';
                        }
                        TempContentRec += '</tr>';

                        if (FLAG == 0) {
                            TempRecsNum++;
                            TempContent += TempContentRec;
                        }
                    });
                    TempContent += '</table>';

                }


                if (TempRecsNum == 0) {
                    TempContent = '<div class="message">По данному запросу не найдено записей.</div>';
                }

                document.getElementById('ListRecs').innerHTML = TempContent;
            }
        }
    });
}

function showAviaryWindow(Id = null) {
    jQuery.ajax({
        'async': true,
        'global': false,
        'cache': false,
        'type': 'GET',
        'dataType': 'xml',
        'data': '&id=' + Id + '&mode=xml&action=aviary',
        'url': "index.php",
        'beforeSend': function () {
            //показать загрузку
            showTopLoading();
        },
        'success': function (Data) {
            closeTopLoading();
            let TempContent = '';

            TempContent += '<div id="aviary_window" class="window main_row col-xl-6 col-lg-8 col-12">';
            TempContent += '<div class="close" onclick="closeAviaryWindow();"></div>';
            if (window.canUserEdit) {
                console.log(window.canUserEdit)
                if (Id) {
                    TempContent += '<div class="top">Редактировать вольер</div>';
                } else {
                    TempContent += '<div class="top">Создать вольер</div>';
                }
            }


            TempContent += '<div class="w-100" id="window_aviary" style="overflow-y: auto;">';
            TempContent += '<form class="form-window" id="form_aviary">';
            TempContent += '<div class="col-12 main_row row">';

            TempContent += '<div class="col-12 error" id="aviary_window_message" style="margin-bottom: 15px; display: none;"></div>';

            TempContent += '<div class="col-3 required row-center-align">Наименование:</div>';
            TempContent += '<div class="col-9" style="margin-top: 10px;">';
            TempContent += '<input type="text" name="title" id="title" value="' + $(Data).find('rec_title').text() + '">';
            TempContent += '</div>';

            TempContent += '<div class="col-3 row-center-align">Номер:</div>';
            TempContent += '<div class="col-9" style="margin-top: 10px;">';
            TempContent += '<input type="text" name="number" id="number" value="' + $(Data).find('rec_number').text() + '">';
            TempContent += '</div>';

            TempContent += '<div class="col-3 row-center-align">Описание:</div>';
            TempContent += '<div class="col-9" style="margin-top: 10px;">';
            TempContent += '<textarea name="description" id="description" rows="5">' + $(Data).find('rec_description').text() + '</textarea>';
            TempContent += '</div>';

            TempContent += '<input type="hidden" name="id" id="id" value="' + Id + '">';
            //
            TempContent += '</div>';
            TempContent += '</form>';


            //Кнопки управления
            TempContent += '<div class="controls">';
            if (window.canUserEdit) {
                if (Id) {
                    TempContent += '<button class="button" id="aviary" style="width: 220px;" onclick="saveAviary();">Сохранить</button>';
                } else {
                    TempContent += '<button class="button" id="aviary" style="width: 220px;" onclick="saveAviary();">Создать</button>';
                }
            }

            TempContent += '</div>';
            //Кнопки управления

            TempContent += '</div>';

            document.getElementById("sub_container").innerHTML = TempContent;
            $('.services').multiselect({
                enableClickableOptGroups: true,
                nonSelectedText: 'Выберите...',
                allSelectedText: "Выбраны все",
                nSelectedText: "выбрано",
                buttonWidth: '100%',
                maxHeight: 300,
                enableFiltering: true,
                enableCaseInsensitiveFiltering: true,
                selectAllText: 'Выбрать все',
                includeSelectAllOption: true,
                filterPlaceholder: 'Выбрать услугу...'
            });
            $("#background").fadeIn();
            $("#container").show();
        }
    });
}

function saveAviary() {
    if ($('#window_aviary #title').val() == '') {
        $("#aviary_window_message").html('Не заполнены необходимые поля.');
        $("#aviary_window_message").show();

        return true;
    }

    jQuery.ajax({
        'async': true,
        'global': false,
        'cache': false,
        'type': 'POST',
        'dataType': 'xml',
        'data': jQuery("#form_aviary").serialize() + '&action=save_aviary&mode=xml',
        'url': "index.php",
        'beforeSend': function () {
            //показать загрузку
            showTopLoading();
        },
        'success': function (Data) {
            if ($(Data).find('message').text() == 'token_invalid') {
                window.location.href = 'index.php';
            } else {
                if ($(Data).find('message').text() == 'aviary_saved') {
                    closeTopLoading();
                    closeAviaryWindow();
                    listShowAviariesRecs();
                }
            }
        }
    });
}

function closeAviaryWindow() {
    $("#background").fadeOut();
    $("#container").hide();
}

function deleteAviaryAcceptWindow(Aviary, Id) {
    let Text = `Вы уверены, что хотите удалить вольер <strong>${Aviary}</strong>?`;
    showAcceptWindow('Удаление вольера', Text, 'deleteAviary(' + Id + ');', 'Удалить', 'delete');
}

function deleteAviary(id) {
    closeAcceptWindow();
    if (id) {
        jQuery.ajax({
            'async': true,
            'global': false,
            'cache': false,
            'type': 'GET',
            'dataType': 'xml',
            'data': '&id=' + id + '&action=delete_aviary&xml=1',
            'url': "index.php",
            'beforeSend': function () {
                showLoading('ListRecs');
            },
            'success': function (Data) {
                if ($(Data).find('message').text() == 'aviary_deleted') {
                    listShowAviariesRecs();
                }
            }
        });
    }
}

/* вольеры*/

/* шаблоны анамнеза */
function listShowAnamnesissRecs() {
    jQuery.ajax({
        'async': true,
        'global': false,
        'cache': false,
        'type': 'GET',
        'dataType': 'xml',
        'data': jQuery("#form_search_recs").serialize() + '&action=anamnesiss&mode=xml',
        'url': "index.php",
        'beforeSend': function () {
            //показать загрузку
            showLoading('ListRecs');
        },
        'success': function (Recs) {
            if ($(Recs).find('message').text() == 'token_invalid') {
                window.location.href = 'index.php';
            } else {
                let TempContent = '';
                let TempRecsNum = 0;

                if ($(Recs).find('rec').text()) {
                    TempContent += '<table class="w-100">';
                    TempContent += '<tr>';
                    TempContent += '<th>Название шаблона анамнеза</th>';
                    TempContent += '<th></th>';
                    TempContent += '<th></th>';
                    TempContent += '</tr>';

                    $(Recs).find('rec').each(function () {
                        let FLAG = 0;
                        let TempContentRec = '<tr>';
                        TempContentRec += '<td>' + $(this).find('rec_title').text() + '</td>';
                        if (window.canUserEdit) {
                            TempContentRec += '<td style="width: 32px;" onclick="showAnamnesisWindow(' + $(this).find('rec_id').text() + ');"><div class="edit"></div></td>';
                            TempContentRec += '<td style="width: 32px;" onclick="deleteAnamnesisAcceptWindow(\'' + $(this).find('rec_title').text() + '\', ' + $(this).find('rec_id').text() + ');"><div class="delete"></div></td>';
                        }
                        TempContentRec += '</tr>';
                        if (FLAG == 0) {
                            TempRecsNum++;
                            TempContent += TempContentRec;
                        }
                    });
                    TempContent += '</table>';

                }


                if (TempRecsNum == 0) {
                    TempContent = '<div class="message">По данному запросу не найдено записей.</div>';
                }

                document.getElementById('ListRecs').innerHTML = TempContent;
            }
        }
    });
}

function showAnamnesisWindow(Id = null) {
    jQuery.ajax({
        'async': true,
        'global': false,
        'cache': false,
        'type': 'GET',
        'dataType': 'xml',
        'data': '&id=' + Id + '&mode=xml&action=anamnesis',
        'url': "index.php",
        'beforeSend': function () {
            //показать загрузку
            showTopLoading();
        },
        'success': function (Data) {
            closeTopLoading();
            let TempContent = '';

            TempContent += '<div id="aviary_window" class="window main_row col-xl-6 col-lg-8 col-12">';
            TempContent += '<div class="close" onclick="closeAnamnesisWindow();"></div>';
            if (window.canUserEdit) {
                if (Id) {
                    TempContent += '<div class="top">Редактировать шаблон анамнеза</div>';
                } else {
                    TempContent += '<div class="top">Создать шаблон анамнеза</div>';
                }
            }

            TempContent += '<div class="w-100" id="window_aviary" style="overflow-y: auto;">';
            TempContent += '<form class="form-window" id="form_aviary">';
            TempContent += '<div class="col-12 main_row row">';

            TempContent += '<div class="col-12 error" id="aviary_window_message" style="margin-bottom: 15px; display: none;"></div>';

            TempContent += '<div class="col-3 required row-center-align">Название:</div>';
            TempContent += '<div class="col-9" style="margin-top: 10px;">';
            TempContent += '<input type="text" name="title" id="title" value="' + $(Data).find('rec_title').text() + '">';
            TempContent += '</div>';

            TempContent += '<input type="hidden" name="id" id="id" value="' + Id + '">';
            //
            TempContent += '</div>';
            TempContent += '</form>';


            //Кнопки управления
            TempContent += '<div class="controls">';
            if (window.canUserEdit) {
                if (Id) {
                    TempContent += '<button class="button" id="aviary" style="width: 220px;" onclick="saveAnamnesis();">Сохранить</button>';
                } else {
                    TempContent += '<button class="button" id="aviary" style="width: 220px;" onclick="saveAnamnesis();">Создать</button>';
                }
            }
            TempContent += '</div>';
            //Кнопки управления

            TempContent += '</div>';

            document.getElementById("sub_container").innerHTML = TempContent;
            $('.services').multiselect({
                enableClickableOptGroups: true,
                nonSelectedText: 'Выберите...',
                allSelectedText: "Выбраны все",
                nSelectedText: "выбрано",
                buttonWidth: '100%',
                maxHeight: 300,
                enableFiltering: true,
                enableCaseInsensitiveFiltering: true,
                selectAllText: 'Выбрать все',
                includeSelectAllOption: true,
                filterPlaceholder: 'Выбрать услугу...'
            });
            $("#background").fadeIn();
            $("#container").show();
        }
    });
}

function saveAnamnesis() {
    if ($('#window_aviary #title').val() == '') {
        $("#aviary_window_message").html('Не заполнены необходимые поля.');
        $("#aviary_window_message").show();

        return true;
    }

    jQuery.ajax({
        'async': true,
        'global': false,
        'cache': false,
        'type': 'POST',
        'dataType': 'xml',
        'data': jQuery("#form_aviary").serialize() + '&action=save_anamnesis&mode=xml',
        'url': "index.php",
        'beforeSend': function () {
            //показать загрузку
            showTopLoading();
        },
        'success': function (Data) {
            if ($(Data).find('message').text() == 'token_invalid') {
                window.location.href = 'index.php';
            } else {
                if ($(Data).find('message').text() == 'anamnesis_saved') {
                    closeTopLoading();
                    closeAnamnesisWindow();
                    listShowAnamnesissRecs();
                }
            }
        }
    });
}

function closeAnamnesisWindow() {
    $("#background").fadeOut();
    $("#container").hide();
}

function deleteAnamnesisAcceptWindow(Anamnesis, Id) {
    let Text = `Вы уверены, что хотите удалить шаблон анамнеза <strong>${Anamnesis}</strong>?`;
    showAcceptWindow('Удаление шаблона анамнеза', Text, 'deleteAnamnesis(' + Id + ');', 'Удалить', 'delete');
}

function deleteAnamnesis(id) {
    closeAcceptWindow();
    if (id) {
        jQuery.ajax({
            'async': true,
            'global': false,
            'cache': false,
            'type': 'GET',
            'dataType': 'xml',
            'data': '&id=' + id + '&action=delete_anamnesis&xml=1',
            'url': "index.php",
            'beforeSend': function () {
                showLoading('ListRecs');
            },
            'success': function (Data) {
                if ($(Data).find('message').text() == 'anamnesis_deleted') {
                    listShowAnamnesissRecs();
                }
            }
        });
    }
}

/* шаблоны анамнеза */

/* навыки */
function listShowSkillsRecs() {
    jQuery.ajax({
        'async': true,
        'global': false,
        'cache': false,
        'type': 'GET',
        'dataType': 'xml',
        'data': jQuery("#form_search_recs").serialize() + '&action=skills&mode=xml',
        'url': "index.php",
        'beforeSend': function () {
            //показать загрузку
            showLoading('ListRecs');
        },
        'success': function (Recs) {
            if ($(Recs).find('message').text() == 'token_invalid') {
                window.location.href = 'index.php';
            } else {
                let TempContent = '';
                let TempRecsNum = 0;

                if ($(Recs).find('rec').text()) {
                    TempContent += '<table class="w-100">';
                    TempContent += '<tr>';
                    TempContent += '<th>Название навыка</th>';
                    TempContent += '<th></th>';
                    TempContent += '<th></th>';
                    TempContent += '</tr>';

                    $(Recs).find('rec').each(function () {
                        let FLAG = 0;
                        let TempContentRec = '<tr>';
                        TempContentRec += '<td>' + $(this).find('rec_title').text() + '</td>';
                        if (window.canUserEdit) {
                            TempContentRec += '<td style="width: 32px;" onclick="showSkillWindow(' + $(this).find('rec_id').text() + ');"><div class="edit"></div></td>';
                            TempContentRec += '<td style="width: 32px;" onclick="deleteSkillAcceptWindow(\'' + $(this).find('rec_title').text() + '\', ' + $(this).find('rec_id').text() + ');"><div class="delete"></div></td>';
                        }
                        TempContentRec += '</tr>';

                        if (FLAG == 0) {
                            TempRecsNum++;
                            TempContent += TempContentRec;
                        }
                    });
                    TempContent += '</table>';

                }


                if (TempRecsNum == 0) {
                    TempContent = '<div class="message">По данному запросу не найдено записей.</div>';
                }

                document.getElementById('ListRecs').innerHTML = TempContent;
            }
        }
    });
}

function showSkillWindow(Id = null) {
    jQuery.ajax({
        'async': true,
        'global': false,
        'cache': false,
        'type': 'GET',
        'dataType': 'xml',
        'data': '&id=' + Id + '&mode=xml&action=skill',
        'url': "index.php",
        'beforeSend': function () {
            //показать загрузку
            showTopLoading();
        },
        'success': function (Data) {
            closeTopLoading();
            let TempContent = '';

            TempContent += '<div id="aviary_window" class="window main_row col-xl-6 col-lg-8 col-12">';
            TempContent += '<div class="close" onclick="closeSkillWindow();"></div>';
            if (window.canUserEdit) {
                if (Id) {
                    TempContent += '<div class="top">Редактировать навык</div>';
                } else {
                    TempContent += '<div class="top">Создать навык</div>';
                }
            }
            TempContent += '<div class="w-100" id="window_aviary" style="overflow-y: auto;">';
            TempContent += '<form class="form-window" id="form_aviary">';
            TempContent += '<div class="col-12 main_row row">';

            TempContent += '<div class="col-12 error" id="aviary_window_message" style="margin-bottom: 15px; display: none;"></div>';

            TempContent += '<div class="col-3 required row-center-align">Название:</div>';
            TempContent += '<div class="col-9" style="margin-top: 10px;">';
            TempContent += '<input type="text" name="title" id="title" value="' + $(Data).find('rec_title').text() + '">';
            TempContent += '</div>';

            TempContent += '<input type="hidden" name="id" id="id" value="' + Id + '">';
            //
            TempContent += '</div>';
            TempContent += '</form>';


            //Кнопки управления
            TempContent += '<div class="controls">';
            if (window.canUserEdit) {
                if (Id) {
                    TempContent += '<button class="button" id="aviary" style="width: 220px;" onclick="saveSkill();">Сохранить</button>';
                } else {
                    TempContent += '<button class="button" id="aviary" style="width: 220px;" onclick="saveSkill();">Создать</button>';
                }
            }
            TempContent += '</div>';
            //Кнопки управления

            TempContent += '</div>';

            document.getElementById("sub_container").innerHTML = TempContent;
            $('.services').multiselect({
                enableClickableOptGroups: true,
                nonSelectedText: 'Выберите...',
                allSelectedText: "Выбраны все",
                nSelectedText: "выбрано",
                buttonWidth: '100%',
                maxHeight: 300,
                enableFiltering: true,
                enableCaseInsensitiveFiltering: true,
                selectAllText: 'Выбрать все',
                includeSelectAllOption: true,
                filterPlaceholder: 'Выбрать услугу...'
            });
            $("#background").fadeIn();
            $("#container").show();
        }
    });
}

function saveSkill() {
    if ($('#window_aviary #title').val() == '') {
        $("#aviary_window_message").html('Не заполнены необходимые поля.');
        $("#aviary_window_message").show();

        return true;
    }

    jQuery.ajax({
        'async': true,
        'global': false,
        'cache': false,
        'type': 'POST',
        'dataType': 'xml',
        'data': jQuery("#form_aviary").serialize() + '&action=save_skill&mode=xml',
        'url': "index.php",
        'beforeSend': function () {
            //показать загрузку
            showTopLoading();
        },
        'success': function (Data) {
            if ($(Data).find('message').text() == 'token_invalid') {
                window.location.href = 'index.php';
            } else {
                if ($(Data).find('message').text() == 'skill_saved') {
                    closeTopLoading();
                    closeSkillWindow();
                    listShowSkillsRecs();
                }
            }
        }
    });
}

function closeSkillWindow() {
    $("#background").fadeOut();
    $("#container").hide();
}

function deleteSkillAcceptWindow(Skill, Id) {
    let Text = `Вы уверены, что хотите удалить навык <strong>${Skill}</strong>?`;
    showAcceptWindow('Удаление навыка', Text, 'deleteSkill(' + Id + ');', 'Удалить', 'delete');
}

function deleteSkill(id) {
    closeAcceptWindow();
    if (id) {
        jQuery.ajax({
            'async': true,
            'global': false,
            'cache': false,
            'type': 'GET',
            'dataType': 'xml',
            'data': '&id=' + id + '&action=delete_skill&xml=1',
            'url': "index.php",
            'beforeSend': function () {
                showLoading('ListRecs');
            },
            'success': function (Data) {
                if ($(Data).find('message').text() == 'skill_deleted') {
                    listShowSkillsRecs();
                }
            }
        });
    }
}

/* навыки */

/* типы хвостов */
function listShowTailsRecs() {
    jQuery.ajax({
        'async': true,
        'global': false,
        'cache': false,
        'type': 'GET',
        'dataType': 'xml',
        'data': jQuery("#form_search_recs").serialize() + '&action=tails&mode=xml',
        'url': "index.php",
        'beforeSend': function () {
            //показать загрузку
            showLoading('ListRecs');
        },
        'success': function (Recs) {
            if ($(Recs).find('message').text() == 'token_invalid') {
                window.location.href = 'index.php';
            } else {
                let TempContent = '';
                let TempRecsNum = 0;

                if ($(Recs).find('rec').text()) {
                    TempContent += '<table class="w-100">';
                    TempContent += '<tr>';
                    TempContent += '<th>Название типа хвоста</th>';
                    TempContent += '<th></th>';
                    TempContent += '<th></th>';
                    TempContent += '</tr>';

                    $(Recs).find('rec').each(function () {
                        let FLAG = 0;
                        let TempContentRec = '<tr>';
                        TempContentRec += '<td>' + $(this).find('rec_title').text() + '</td>';
                        if (window.canUserEdit) {
                            TempContentRec += '<td style="width: 32px;" onclick="showTailWindow(' + $(this).find('rec_id').text() + ');"><div class="edit"></div></td>';
                            TempContentRec += '<td style="width: 32px;" onclick="deleteTailAcceptWindow(\'' + $(this).find('rec_title').text() + '\', ' + $(this).find('rec_id').text() + ');"><div class="delete"></div></td>';
                        }
                        TempContentRec += '</tr>';

                        if (FLAG == 0) {
                            TempRecsNum++;
                            TempContent += TempContentRec;
                        }
                    });
                    TempContent += '</table>';

                }


                if (TempRecsNum == 0) {
                    TempContent = '<div class="message">По данному запросу не найдено записей.</div>';
                }

                document.getElementById('ListRecs').innerHTML = TempContent;
            }
        }
    });
}

function showTailWindow(Id = null) {
    jQuery.ajax({
        'async': true,
        'global': false,
        'cache': false,
        'type': 'GET',
        'dataType': 'xml',
        'data': '&id=' + Id + '&mode=xml&action=tail',
        'url': "index.php",
        'beforeSend': function () {
            //показать загрузку
            showTopLoading();
        },
        'success': function (Data) {
            closeTopLoading();
            let TempContent = '';

            TempContent += '<div id="aviary_window" class="window main_row col-xl-6 col-lg-8 col-12">';
            TempContent += '<div class="close" onclick="closeTailWindow();"></div>';
            if (window.canUserEdit) {
                if (Id) {
                    TempContent += '<div class="top">Редактировать тип хвоста</div>';
                } else {
                    TempContent += '<div class="top">Создать тип хвоста</div>';
                }
            }

            TempContent += '<div class="w-100" id="window_aviary" style="overflow-y: auto;">';
            TempContent += '<form class="form-window" id="form_aviary">';
            TempContent += '<div class="col-12 main_row row">';

            TempContent += '<div class="col-12 error" id="aviary_window_message" style="margin-bottom: 15px; display: none;"></div>';

            TempContent += '<div class="col-3 required row-center-align">Название:</div>';
            TempContent += '<div class="col-9" style="margin-top: 10px;">';
            TempContent += '<input type="text" name="title" id="title" value="' + $(Data).find('rec_title').text() + '">';
            TempContent += '</div>';

            TempContent += '<input type="hidden" name="id" id="id" value="' + Id + '">';
            //
            TempContent += '</div>';
            TempContent += '</form>';


            //Кнопки управления
            TempContent += '<div class="controls">';
            if (window.canUserEdit) {
                if (Id) {
                    TempContent += '<button class="button" id="aviary" style="width: 220px;" onclick="saveTail();">Сохранить</button>';
                } else {
                    TempContent += '<button class="button" id="aviary" style="width: 220px;" onclick="saveTail();">Создать</button>';
                }
            }
            TempContent += '</div>';
            //Кнопки управления

            TempContent += '</div>';

            document.getElementById("sub_container").innerHTML = TempContent;
            $('.services').multiselect({
                enableClickableOptGroups: true,
                nonSelectedText: 'Выберите...',
                allSelectedText: "Выбраны все",
                nSelectedText: "выбрано",
                buttonWidth: '100%',
                maxHeight: 300,
                enableFiltering: true,
                enableCaseInsensitiveFiltering: true,
                selectAllText: 'Выбрать все',
                includeSelectAllOption: true,
                filterPlaceholder: 'Выбрать услугу...'
            });
            $("#background").fadeIn();
            $("#container").show();
        }
    });
}

function saveTail() {
    if ($('#window_aviary #title').val() == '') {
        $("#aviary_window_message").html('Не заполнены необходимые поля.');
        $("#aviary_window_message").show();

        return true;
    }

    jQuery.ajax({
        'async': true,
        'global': false,
        'cache': false,
        'type': 'POST',
        'dataType': 'xml',
        'data': jQuery("#form_aviary").serialize() + '&action=save_tail&mode=xml',
        'url': "index.php",
        'beforeSend': function () {
            //показать загрузку
            showTopLoading();
        },
        'success': function (Data) {
            if ($(Data).find('message').text() == 'token_invalid') {
                window.location.href = 'index.php';
            } else {
                if ($(Data).find('message').text() == 'tail_saved') {
                    closeTopLoading();
                    closeTailWindow();
                    listShowTailsRecs();
                }
            }
        }
    });
}

function closeTailWindow() {
    $("#background").fadeOut();
    $("#container").hide();
}

function deleteTailAcceptWindow(Tail, Id) {
    let Text = `Вы уверены, что хотите удалить тип хвоста <strong>${Tail}</strong>?`;
    showAcceptWindow('Удаление типа хвоста', Text, 'deleteTail(' + Id + ');', 'Удалить', 'delete');
}

function deleteTail(id) {
    closeAcceptWindow();
    if (id) {
        jQuery.ajax({
            'async': true,
            'global': false,
            'cache': false,
            'type': 'GET',
            'dataType': 'xml',
            'data': '&id=' + id + '&action=delete_tail&xml=1',
            'url': "index.php",
            'beforeSend': function () {
                showLoading('ListRecs');
            },
            'success': function (Data) {
                if ($(Data).find('message').text() == 'tail_deleted') {
                    listShowTailsRecs();
                }
            }
        });
    }
}

/* типы хвоста */

/* типы ушей */
function listShowEarsRecs() {
    jQuery.ajax({
        'async': true,
        'global': false,
        'cache': false,
        'type': 'GET',
        'dataType': 'xml',
        'data': jQuery("#form_search_recs").serialize() + '&action=ears&mode=xml',
        'url': "index.php",
        'beforeSend': function () {
            //показать загрузку
            showLoading('ListRecs');
        },
        'success': function (Recs) {
            if ($(Recs).find('message').text() == 'token_invalid') {
                window.location.href = 'index.php';
            } else {
                let TempContent = '';
                let TempRecsNum = 0;

                if ($(Recs).find('rec').text()) {
                    TempContent += '<table class="w-100">';
                    TempContent += '<tr>';
                    TempContent += '<th>Название типа ушей</th>';
                    TempContent += '<th></th>';
                    TempContent += '<th></th>';
                    TempContent += '</tr>';

                    $(Recs).find('rec').each(function () {
                        let FLAG = 0;
                        let TempContentRec = '<tr>';
                        TempContentRec += '<td>' + $(this).find('rec_title').text() + '</td>';
                        if (window.canUserEdit) {
                            TempContentRec += '<td style="width: 32px;" onclick="showEarWindow(' + $(this).find('rec_id').text() + ');"><div class="edit"></div></td>';
                            TempContentRec += '<td style="width: 32px;" onclick="deleteEarAcceptWindow(\'' + $(this).find('rec_title').text() + '\', ' + $(this).find('rec_id').text() + ');"><div class="delete"></div></td>';
                        }
                        TempContentRec += '</tr>';

                        if (FLAG == 0) {
                            TempRecsNum++;
                            TempContent += TempContentRec;
                        }
                    });
                    TempContent += '</table>';

                }


                if (TempRecsNum == 0) {
                    TempContent = '<div class="message">По данному запросу не найдено записей.</div>';
                }

                document.getElementById('ListRecs').innerHTML = TempContent;
            }
        }
    });
}

function showEarWindow(Id = null) {
    jQuery.ajax({
        'async': true,
        'global': false,
        'cache': false,
        'type': 'GET',
        'dataType': 'xml',
        'data': '&id=' + Id + '&mode=xml&action=ear',
        'url': "index.php",
        'beforeSend': function () {
            //показать загрузку
            showTopLoading();
        },
        'success': function (Data) {
            closeTopLoading();
            let TempContent = '';

            TempContent += '<div id="aviary_window" class="window main_row col-xl-6 col-lg-8 col-12">';
            TempContent += '<div class="close" onclick="closeEarWindow();"></div>';
            if (window.canUserEdit) {
                if (Id) {
                    TempContent += '<div class="top">Редактировать тип ушей</div>';
                } else {
                    TempContent += '<div class="top">Создать тип ушей</div>';
                }
            }

            TempContent += '<div class="w-100" id="window_aviary" style="overflow-y: auto;">';
            TempContent += '<form class="form-window" id="form_aviary">';
            TempContent += '<div class="col-12 main_row row">';

            TempContent += '<div class="col-12 error" id="aviary_window_message" style="margin-bottom: 15px; display: none;"></div>';

            TempContent += '<div class="col-3 required row-center-align">Название:</div>';
            TempContent += '<div class="col-9" style="margin-top: 10px;">';
            TempContent += '<input type="text" name="title" id="title" value="' + $(Data).find('rec_title').text() + '">';
            TempContent += '</div>';

            TempContent += '<input type="hidden" name="id" id="id" value="' + Id + '">';
            //
            TempContent += '</div>';
            TempContent += '</form>';


            //Кнопки управления
            TempContent += '<div class="controls">';
            if (window.canUserEdit) {
                if (Id) {
                    TempContent += '<button class="button" id="aviary" style="width: 220px;" onclick="saveEar();">Сохранить</button>';
                } else {
                    TempContent += '<button class="button" id="aviary" style="width: 220px;" onclick="saveEar();">Создать</button>';
                }
            }
            TempContent += '</div>';
            //Кнопки управления

            TempContent += '</div>';

            document.getElementById("sub_container").innerHTML = TempContent;
            $('.services').multiselect({
                enableClickableOptGroups: true,
                nonSelectedText: 'Выберите...',
                allSelectedText: "Выбраны все",
                nSelectedText: "выбрано",
                buttonWidth: '100%',
                maxHeight: 300,
                enableFiltering: true,
                enableCaseInsensitiveFiltering: true,
                selectAllText: 'Выбрать все',
                includeSelectAllOption: true,
                filterPlaceholder: 'Выбрать услугу...'
            });
            $("#background").fadeIn();
            $("#container").show();
        }
    });
}

function saveEar() {
    if ($('#window_aviary #title').val() == '') {
        $("#aviary_window_message").html('Не заполнены необходимые поля.');
        $("#aviary_window_message").show();

        return true;
    }

    jQuery.ajax({
        'async': true,
        'global': false,
        'cache': false,
        'type': 'POST',
        'dataType': 'xml',
        'data': jQuery("#form_aviary").serialize() + '&action=save_ear&mode=xml',
        'url': "index.php",
        'beforeSend': function () {
            //показать загрузку
            showTopLoading();
        },
        'success': function (Data) {
            if ($(Data).find('message').text() == 'token_invalid') {
                window.location.href = 'index.php';
            } else {
                if ($(Data).find('message').text() == 'ear_saved') {
                    closeTopLoading();
                    closeEarWindow();
                    listShowEarsRecs();
                }
            }
        }
    });
}

function closeEarWindow() {
    $("#background").fadeOut();
    $("#container").hide();
}

function deleteEarAcceptWindow(Ear, Id) {
    let Text = `Вы уверены, что хотите удалить тип ушей <strong>${Ear}</strong>?`;
    showAcceptWindow('Удаление типа ушей', Text, 'deleteEar(' + Id + ');', 'Удалить', 'delete');
}

function deleteEar(id) {
    closeAcceptWindow();
    if (id) {
        jQuery.ajax({
            'async': true,
            'global': false,
            'cache': false,
            'type': 'GET',
            'dataType': 'xml',
            'data': '&id=' + id + '&action=delete_ear&xml=1',
            'url': "index.php",
            'beforeSend': function () {
                showLoading('ListRecs');
            },
            'success': function (Data) {
                if ($(Data).find('message').text() == 'ear_deleted') {
                    listShowEarsRecs();
                }
            }
        });
    }
}

/* типы ушей */

/* типы шерсти */
function listShowWoolsRecs() {
    jQuery.ajax({
        'async': true,
        'global': false,
        'cache': false,
        'type': 'GET',
        'dataType': 'xml',
        'data': jQuery("#form_search_recs").serialize() + '&action=wools&mode=xml',
        'url': "index.php",
        'beforeSend': function () {
            //показать загрузку
            showLoading('ListRecs');
        },
        'success': function (Recs) {
            if ($(Recs).find('message').text() == 'token_invalid') {
                window.location.href = 'index.php';
            } else {
                let TempContent = '';
                let TempRecsNum = 0;

                if ($(Recs).find('rec').text()) {
                    TempContent += '<table class="w-100">';
                    TempContent += '<tr>';
                    TempContent += '<th>Название типа шерсти</th>';
                    TempContent += '<th></th>';
                    TempContent += '<th></th>';
                    TempContent += '</tr>';

                    $(Recs).find('rec').each(function () {
                        let FLAG = 0;
                        let TempContentRec = '<tr>';
                        TempContentRec += '<td>' + $(this).find('rec_title').text() + '</td>';
                        if (window.canUserEdit) {
                            TempContentRec += '<td style="width: 32px;" onclick="showWoolWindow(' + $(this).find('rec_id').text() + ');"><div class="edit"></div></td>';
                            TempContentRec += '<td style="width: 32px;" onclick="deleteWoolAcceptWindow(\'' + $(this).find('rec_title').text() + '\', ' + $(this).find('rec_id').text() + ');"><div class="delete"></div></td>';
                        }
                        TempContentRec += '</tr>';

                        if (FLAG == 0) {
                            TempRecsNum++;
                            TempContent += TempContentRec;
                        }
                    });
                    TempContent += '</table>';

                }


                if (TempRecsNum == 0) {
                    TempContent = '<div class="message">По данному запросу не найдено записей.</div>';
                }

                document.getElementById('ListRecs').innerHTML = TempContent;
            }
        }
    });
}

function showWoolWindow(Id = null) {
    jQuery.ajax({
        'async': true,
        'global': false,
        'cache': false,
        'type': 'GET',
        'dataType': 'xml',
        'data': '&id=' + Id + '&mode=xml&action=wool',
        'url': "index.php",
        'beforeSend': function () {
            //показать загрузку
            showTopLoading();
        },
        'success': function (Data) {
            closeTopLoading();
            let TempContent = '';

            TempContent += '<div id="aviary_window" class="window main_row col-xl-6 col-lg-8 col-12">';
            TempContent += '<div class="close" onclick="closeWoolWindow();"></div>';
            if (window.canUserEdit) {
                if (Id) {
                    TempContent += '<div class="top">Редактировать тип шерсти</div>';
                } else {
                    TempContent += '<div class="top">Создать тип шерсти</div>';
                }
            }

            TempContent += '<div class="w-100" id="window_aviary" style="overflow-y: auto;">';
            TempContent += '<form class="form-window" id="form_aviary">';
            TempContent += '<div class="col-12 main_row row">';

            TempContent += '<div class="col-12 error" id="aviary_window_message" style="margin-bottom: 15px; display: none;"></div>';

            TempContent += '<div class="col-3 required row-center-align">Название:</div>';
            TempContent += '<div class="col-9" style="margin-top: 10px;">';
            TempContent += '<input type="text" name="title" id="title" value="' + $(Data).find('rec_title').text() + '">';
            TempContent += '</div>';

            TempContent += '<input type="hidden" name="id" id="id" value="' + Id + '">';
            //
            TempContent += '</div>';
            TempContent += '</form>';


            //Кнопки управления
            TempContent += '<div class="controls">';
            if (window.canUserEdit) {
                if (Id) {
                    TempContent += '<button class="button" id="aviary" style="width: 220px;" onclick="saveWool();">Сохранить</button>';
                } else {
                    TempContent += '<button class="button" id="aviary" style="width: 220px;" onclick="saveWool();">Создать</button>';
                }
            }
            TempContent += '</div>';
            //Кнопки управления

            TempContent += '</div>';

            document.getElementById("sub_container").innerHTML = TempContent;
            $('.services').multiselect({
                enableClickableOptGroups: true,
                nonSelectedText: 'Выберите...',
                allSelectedText: "Выбраны все",
                nSelectedText: "выбрано",
                buttonWidth: '100%',
                maxHeight: 300,
                enableFiltering: true,
                enableCaseInsensitiveFiltering: true,
                selectAllText: 'Выбрать все',
                includeSelectAllOption: true,
                filterPlaceholder: 'Выбрать услугу...'
            });
            $("#background").fadeIn();
            $("#container").show();
        }
    });
}

function saveWool() {
    if ($('#window_aviary #title').val() == '') {
        $("#aviary_window_message").html('Не заполнены необходимые поля.');
        $("#aviary_window_message").show();

        return true;
    }

    jQuery.ajax({
        'async': true,
        'global': false,
        'cache': false,
        'type': 'POST',
        'dataType': 'xml',
        'data': jQuery("#form_aviary").serialize() + '&action=save_wool&mode=xml',
        'url': "index.php",
        'beforeSend': function () {
            //показать загрузку
            showTopLoading();
        },
        'success': function (Data) {
            if ($(Data).find('message').text() == 'token_invalid') {
                window.location.href = 'index.php';
            } else {
                if ($(Data).find('message').text() == 'wool_saved') {
                    closeTopLoading();
                    closeWoolWindow();
                    listShowWoolsRecs();
                }
            }
        }
    });
}

function closeWoolWindow() {
    $("#background").fadeOut();
    $("#container").hide();
}

function deleteWoolAcceptWindow(Wool, Id) {
    let Text = `Вы уверены, что хотите удалить тип шерсти <strong>${Wool}</strong>?`;
    showAcceptWindow('Удаление типа шерсти', Text, 'deleteWool(' + Id + ');', 'Удалить', 'delete');
}

function deleteWool(id) {
    closeAcceptWindow();
    if (id) {
        jQuery.ajax({
            'async': true,
            'global': false,
            'cache': false,
            'type': 'GET',
            'dataType': 'xml',
            'data': '&id=' + id + '&action=delete_wool&xml=1',
            'url': "index.php",
            'beforeSend': function () {
                showLoading('ListRecs');
            },
            'success': function (Data) {
                if ($(Data).find('message').text() == 'wool_deleted') {
                    listShowWoolsRecs();
                }
            }
        });
    }
}

/* типы шерсти */

/* окрасы */
function listShowColorsRecs() {
    jQuery.ajax({
        'async': true,
        'global': false,
        'cache': false,
        'type': 'GET',
        'dataType': 'xml',
        'data': jQuery("#form_search_recs").serialize() + '&action=colors&mode=xml',
        'url': "index.php",
        'beforeSend': function () {
            //показать загрузку
            showLoading('ListRecs');
        },
        'success': function (Recs) {
            if ($(Recs).find('message').text() == 'token_invalid') {
                window.location.href = 'index.php';
            } else {
                let TempContent = '';
                let TempRecsNum = 0;

                if ($(Recs).find('rec').text()) {
                    TempContent += '<table class="w-100">';
                    TempContent += '<tr>';
                    TempContent += '<th>Название окраса</th>';
                    TempContent += '<th></th>';
                    TempContent += '<th></th>';
                    TempContent += '</tr>';

                    $(Recs).find('rec').each(function () {
                        let FLAG = 0;
                        let TempContentRec = '<tr>';
                        TempContentRec += '<td>' + $(this).find('rec_title').text() + '</td>';
                        if (window.canUserEdit) {
                            TempContentRec += '<td style="width: 32px;" onclick="showColorWindow(' + $(this).find('rec_id').text() + ');"><div class="edit"></div></td>';
                            TempContentRec += '<td style="width: 32px;" onclick="deleteColorAcceptWindow(\'' + $(this).find('rec_title').text() + '\', ' + $(this).find('rec_id').text() + ');"><div class="delete"></div></td>';
                        }
                        TempContentRec += '</tr>';

                        if (FLAG == 0) {
                            TempRecsNum++;
                            TempContent += TempContentRec;
                        }
                    });
                    TempContent += '</table>';

                }


                if (TempRecsNum == 0) {
                    TempContent = '<div class="message">По данному запросу не найдено записей.</div>';
                }

                document.getElementById('ListRecs').innerHTML = TempContent;
            }
        }
    });
}

function showColorWindow(Id = null) {
    jQuery.ajax({
        'async': true,
        'global': false,
        'cache': false,
        'type': 'GET',
        'dataType': 'xml',
        'data': '&id=' + Id + '&mode=xml&action=color',
        'url': "index.php",
        'beforeSend': function () {
            //показать загрузку
            showTopLoading();
        },
        'success': function (Data) {
            closeTopLoading();
            let TempContent = '';

            TempContent += '<div id="aviary_window" class="window main_row col-xl-6 col-lg-8 col-12">';
            TempContent += '<div class="close" onclick="closeColorWindow();"></div>';
            if (window.canUserEdit) {
                if (Id) {
                    TempContent += '<div class="top">Редактировать окрас</div>';
                } else {
                    TempContent += '<div class="top">Создать окрас</div>';
                }
            }
            TempContent += '<div class="w-100" id="window_aviary" style="overflow-y: auto;">';
            TempContent += '<form class="form-window" id="form_aviary">';
            TempContent += '<div class="col-12 main_row row">';

            TempContent += '<div class="col-12 error" id="aviary_window_message" style="margin-bottom: 15px; display: none;"></div>';

            TempContent += '<div class="col-3 required row-center-align">Название:</div>';
            TempContent += '<div class="col-9" style="margin-top: 10px;">';
            TempContent += '<input type="text" name="title" id="title" value="' + $(Data).find('rec_title').text() + '">';
            TempContent += '</div>';

            TempContent += '<input type="hidden" name="id" id="id" value="' + Id + '">';
            //
            TempContent += '</div>';
            TempContent += '</form>';


            //Кнопки управления
            TempContent += '<div class="controls">';
            if (window.canUserEdit) {
                if (Id) {
                    TempContent += '<button class="button" id="aviary" style="width: 220px;" onclick="saveColor();">Сохранить</button>';
                } else {
                    TempContent += '<button class="button" id="aviary" style="width: 220px;" onclick="saveColor();">Создать</button>';
                }
            }
            TempContent += '</div>';
            //Кнопки управления

            TempContent += '</div>';

            document.getElementById("sub_container").innerHTML = TempContent;
            $('.services').multiselect({
                enableClickableOptGroups: true,
                nonSelectedText: 'Выберите...',
                allSelectedText: "Выбраны все",
                nSelectedText: "выбрано",
                buttonWidth: '100%',
                maxHeight: 300,
                enableFiltering: true,
                enableCaseInsensitiveFiltering: true,
                selectAllText: 'Выбрать все',
                includeSelectAllOption: true,
                filterPlaceholder: 'Выбрать услугу...'
            });
            $("#background").fadeIn();
            $("#container").show();
        }
    });
}

function saveColor() {
    if ($('#window_aviary #title').val() == '') {
        $("#aviary_window_message").html('Не заполнены необходимые поля.');
        $("#aviary_window_message").show();

        return true;
    }

    jQuery.ajax({
        'async': true,
        'global': false,
        'cache': false,
        'type': 'POST',
        'dataType': 'xml',
        'data': jQuery("#form_aviary").serialize() + '&action=save_color&mode=xml',
        'url': "index.php",
        'beforeSend': function () {
            //показать загрузку
            showTopLoading();
        },
        'success': function (Data) {
            if ($(Data).find('message').text() == 'token_invalid') {
                window.location.href = 'index.php';
            } else {
                if ($(Data).find('message').text() == 'color_saved') {
                    closeTopLoading();
                    closeColorWindow();
                    listShowColorsRecs();
                }
            }
        }
    });
}

function closeColorWindow() {
    $("#background").fadeOut();
    $("#container").hide();
}

function deleteColorAcceptWindow(Color, Id) {
    let Text = `Вы уверены, что хотите удалить окрас <strong>${Color}</strong>?`;
    showAcceptWindow('Удаление окраса', Text, 'deleteColor(' + Id + ');', 'Удалить', 'delete');
}

function deleteColor(id) {
    closeAcceptWindow();
    if (id) {
        jQuery.ajax({
            'async': true,
            'global': false,
            'cache': false,
            'type': 'GET',
            'dataType': 'xml',
            'data': '&id=' + id + '&action=delete_color&xml=1',
            'url': "index.php",
            'beforeSend': function () {
                showLoading('ListRecs');
            },
            'success': function (Data) {
                if ($(Data).find('message').text() == 'color_deleted') {
                    listShowColorsRecs();
                }
            }
        });
    }
}

/* окрасы */

/* размеры */
function listShowSizesRecs() {
    jQuery.ajax({
        'async': true,
        'global': false,
        'cache': false,
        'type': 'GET',
        'dataType': 'xml',
        'data': jQuery("#form_search_recs").serialize() + '&action=sizes&mode=xml',
        'url': "index.php",
        'beforeSend': function () {
            //показать загрузку
            showLoading('ListRecs');
        },
        'success': function (Recs) {
            if ($(Recs).find('message').text() == 'token_invalid') {
                window.location.href = 'index.php';
            } else {
                let TempContent = '';
                let TempRecsNum = 0;

                if ($(Recs).find('rec').text()) {
                    TempContent += '<table class="w-100">';
                    TempContent += '<tr>';
                    TempContent += '<th>Название размера</th>';
                    TempContent += '<th></th>';
                    TempContent += '<th></th>';
                    TempContent += '</tr>';

                    $(Recs).find('rec').each(function () {
                        let FLAG = 0;
                        let TempContentRec = '<tr>';
                        TempContentRec += '<td>' + $(this).find('rec_title').text() + '</td>';
                        if (window.canUserEdit) {
                            TempContentRec += '<td style="width: 32px;" onclick="showSizeWindow(' + $(this).find('rec_id').text() + ');"><div class="edit"></div></td>';
                            TempContentRec += '<td style="width: 32px;" onclick="deleteSizeAcceptWindow(\'' + $(this).find('rec_title').text() + '\', ' + $(this).find('rec_id').text() + ');"><div class="delete"></div></td>';
                        }
                        TempContentRec += '</tr>';

                        if (FLAG == 0) {
                            TempRecsNum++;
                            TempContent += TempContentRec;
                        }
                    });
                    TempContent += '</table>';

                }


                if (TempRecsNum == 0) {
                    TempContent = '<div class="message">По данному запросу не найдено записей.</div>';
                }

                document.getElementById('ListRecs').innerHTML = TempContent;
            }
        }
    });
}

function showSizeWindow(Id = null) {
    jQuery.ajax({
        'async': true,
        'global': false,
        'cache': false,
        'type': 'GET',
        'dataType': 'xml',
        'data': '&id=' + Id + '&mode=xml&action=size',
        'url': "index.php",
        'beforeSend': function () {
            //показать загрузку
            showTopLoading();
        },
        'success': function (Data) {
            closeTopLoading();
            let TempContent = '';

            TempContent += '<div id="aviary_window" class="window main_row col-xl-6 col-lg-8 col-12">';
            TempContent += '<div class="close" onclick="closeSizeWindow();"></div>';
            if (window.canUserEdit) {
                if (Id) {
                    TempContent += '<div class="top">Редактировать размер</div>';
                } else {
                    TempContent += '<div class="top">Создать размер</div>';
                }
            }

            TempContent += '<div class="w-100" id="window_aviary" style="overflow-y: auto;">';
            TempContent += '<form class="form-window" id="form_aviary">';
            TempContent += '<div class="col-12 main_row row">';

            TempContent += '<div class="col-12 error" id="aviary_window_message" style="margin-bottom: 15px; display: none;"></div>';

            TempContent += '<div class="col-3 required row-center-align">Название:</div>';
            TempContent += '<div class="col-9" style="margin-top: 10px;">';
            TempContent += '<input type="text" name="title" id="title" value="' + $(Data).find('rec_title').text() + '">';
            TempContent += '</div>';

            TempContent += '<input type="hidden" name="id" id="id" value="' + Id + '">';
            //
            TempContent += '</div>';
            TempContent += '</form>';


            //Кнопки управления
            TempContent += '<div class="controls">';
            if (window.canUserEdit) {
                if (Id) {
                    TempContent += '<button class="button" id="aviary" style="width: 220px;" onclick="saveSize();">Сохранить</button>';
                } else {
                    TempContent += '<button class="button" id="aviary" style="width: 220px;" onclick="saveSize();">Создать</button>';
                }
            }
            TempContent += '</div>';
            //Кнопки управления

            TempContent += '</div>';

            document.getElementById("sub_container").innerHTML = TempContent;
            $('.services').multiselect({
                enableClickableOptGroups: true,
                nonSelectedText: 'Выберите...',
                allSelectedText: "Выбраны все",
                nSelectedText: "выбрано",
                buttonWidth: '100%',
                maxHeight: 300,
                enableFiltering: true,
                enableCaseInsensitiveFiltering: true,
                selectAllText: 'Выбрать все',
                includeSelectAllOption: true,
                filterPlaceholder: 'Выбрать услугу...'
            });
            $("#background").fadeIn();
            $("#container").show();
        }
    });
}

function saveSize() {
    if ($('#window_aviary #title').val() == '') {
        $("#aviary_window_message").html('Не заполнены необходимые поля.');
        $("#aviary_window_message").show();

        return true;
    }

    jQuery.ajax({
        'async': true,
        'global': false,
        'cache': false,
        'type': 'POST',
        'dataType': 'xml',
        'data': jQuery("#form_aviary").serialize() + '&action=save_size&mode=xml',
        'url': "index.php",
        'beforeSend': function () {
            //показать загрузку
            showTopLoading();
        },
        'success': function (Data) {
            if ($(Data).find('message').text() == 'token_invalid') {
                window.location.href = 'index.php';
            } else {
                if ($(Data).find('message').text() == 'size_saved') {
                    closeTopLoading();
                    closeSizeWindow();
                    listShowSizesRecs();
                }
            }
        }
    });
}

function closeSizeWindow() {
    $("#background").fadeOut();
    $("#container").hide();
}

function deleteSizeAcceptWindow(Size, Id) {
    let Text = `Вы уверены, что хотите удалить размер <strong>${Size}</strong>?`;
    showAcceptWindow('Удаление размера', Text, 'deleteSize(' + Id + ');', 'Удалить', 'delete');
}

function deleteSize(id) {
    closeAcceptWindow();
    if (id) {
        jQuery.ajax({
            'async': true,
            'global': false,
            'cache': false,
            'type': 'GET',
            'dataType': 'xml',
            'data': '&id=' + id + '&action=delete_size&xml=1',
            'url': "index.php",
            'beforeSend': function () {
                showLoading('ListRecs');
            },
            'success': function (Data) {
                if ($(Data).find('message').text() == 'size_deleted') {
                    listShowSizesRecs();
                }
            }
        });
    }
}

/* размеры */

function listShowSheltersInfo() {
    jQuery.ajax({
        'async': true,
        'global': false,
        'cache': false,
        'type': 'GET',
        'dataType': 'xml',
        'data': jQuery("#form_search_recs").serialize() + '&action=pets_info&mode=xml',
        'url': "index.php",
        'beforeSend': function () {
            //показать загрузку
            $('#quarantine').addClass('text_loading');
            $('#vaccination').addClass('text_loading');
            $('#not_vaccination').addClass('text_loading');
        },
        'success': function (Data) {
            if ($(Data).find('message').text() == 'token_invalid') {
                window.location.href = 'index.php';
            } else {
                $('#quarantine').removeClass('text_loading');
                $('#vaccination').removeClass('text_loading');
                $('#not_vaccination').removeClass('text_loading');

                //информер
                //$("#all").html($(Data).find('quarantine').text());
                $("#quarantine").html($(Data).find('quarantine').text());
                $("#vaccination").html($(Data).find('vaccination').text());
                $("#vaccination_end").html($(Data).find('vaccination_end').text());
                $("#not_vaccination").html($(Data).find('not_vaccination').text());
                //информер
            }
        }
    });
}

function listShowSheltersPets(Page = 1, Sort = null, Direction = 0) {
    //xls
    let xls = 0;

    if (typeof event !== 'undefined') {
        event.preventDefault();
        if (event.submitter?.name) {
            if (event.submitter.name == 'xls') {
                xls = 1;
            }
        }
    }
    //xls

    if (currentSort == Sort && currentPage == Page) {
        if (currentDirection == 1) {
            currentDirection = 0;
        } else {
            currentDirection = 1;
        }
    }
    if ((currentSort == '' && Sort != null) || (currentSort != Sort)) {
        currentSort = Sort;
    }
    if ((currentPage == '' && Page != null) || (currentPage != Page)) {
        currentPage = Page;
    }

    if (xls) {
        $.ajax({
            'async': true,
            'global': false,
            'cache': false,
            'type': 'GET',
            'xhrFields': {
                responseType: 'blob'
            },
            'data': jQuery("#form_search_recs").serialize() + '&xls=' + xls + '&action=pets&mode=xml',
            'url': "index.php",
            'beforeSend': function () {
                //показать загрузку
                showLoading('ListRecs');
            },
            'success': function (Data) {
                closeLoading('ListRecs');

                if ($(Data).find('message').text() == 'token_invalid') {
                    window.location.href = 'index.php';
                } else {
                    let exportDate = new Date();
                    let exportDateStr = exportDate.toLocaleString('ru', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });

                    var a = document.createElement('a');
                    var url = window.URL.createObjectURL(Data);
                    a.href = url;
                    a.download = 'Выгрузка списка животных за ' + exportDateStr + '.xlsx';
                    document.body.append(a);
                    a.click();
                    a.remove();
                    window.URL.revokeObjectURL(url);

                    TempContent = '<div class="p-3">Загрузка файла началась...</div>';
                    document.getElementById('ListRecs').innerHTML = TempContent;
                }
            }
        });
    } else {
        let vaccinations = vetas_get_cookie("vaccinations");
        let arrayVaccinations = [];
        if (vaccinations !== undefined) {
            arrayVaccinations = vaccinations.split(';');
        }

        jQuery.ajax({
            'async': true,
            'global': false,
            'cache': false,
            'type': 'GET',
            'dataType': 'xml',
            'data': jQuery("#form_search_recs").serialize() + '&page=' + Page + '&sort=' + Sort + '&direction=' + currentDirection + '&action=pets&mode=xml',
            'url': "index.php",
            'beforeSend': function () {
                //показать загрузку
                showLoading('ListRecs');
            },
            'success': function (Recs) {
                if ($(Recs).find('message').text() == 'token_invalid') {
                    window.location.href = 'index.php';
                } else {
                    let TempContent = '';
                    let TempRecsNum = 0;
                    let TempLevels = parseInt($(Recs).find('levels').text());
                    $("#all").html(0);

                    if ($(Recs).find('rec').text()) {
                        TempContent += '<table class="w-100" id="ListRecsTable">';

                        TempContent += '<tr class="isSticky" id="shelters_header">';

                        if (TempLevels >= 2) {
                            TempContent += '<th><div id="area" title="Административный округ" class="sort" onclick="listShowSheltersPets(' + Page + ', \'area\');">Административный округ</div></th>';
                            TempContent += '<th><div id="managing" title="Управляющая организация" class="sort" onclick="listShowSheltersPets(' + Page + ', \'managing\');">Управляющая организация</div></th>';
                            TempContent += '<th><div id="shelter" title="Наименование приюта" class="sort" onclick="listShowSheltersPets(' + Page + ', \'shelter\');">Наименование приюта</div></th>';
                        }
                        if (TempLevels > 0 && TempLevels <= 1) {
                            TempContent += '<th><div id="shelter" title="Наименование приюта" class="sort" onclick="listShowSheltersPets(' + Page + ', \'shelter\');">Наименование приюта</div></th>';
                        }
                        currentLevel = TempLevels;

                        TempContent += '<th><div id="status" title="Статус" class="sort" onclick="listShowSheltersPets(' + Page + ', \'status\');">Статус</div></th>';
                        TempContent += '<th><div id="ku" title="№ К/У" class="sort" onclick="listShowSheltersPets(' + Page + ', \'ku\');">№ К/У</div></th>';
                        TempContent += '<th><div id="chip" title="№ чипа" class="sort" onclick="listShowSheltersPets(' + Page + ', \'chip\');">№ чипа</div></th>';
                        TempContent += '<th><div id="vac_spec" title="Кем вакцинировано животное" class="sort" onclick="listShowSheltersPets(' + Page + ', \'vac_spec\');">Кем вакцинировано животное</div></th>';
                        TempContent += '<th><div id="vac_date" title="Дата вакцинации" class="sort" onclick="listShowSheltersPets(' + Page + ', \'vac_date\');">Дата вакцинации</div></th>';
                        TempContent += '<th><div id="name" title="Кличка" class="sort" onclick="listShowSheltersPets(' + Page + ', \'name\');">Кличка</div></th>';
                        TempContent += '<th><div id="species" title="Вид" class="sort" onclick="listShowSheltersPets(' + Page + ', \'species\');">Вид</div></th>';
                        TempContent += '<th><div id="arrival_date" title="Дата поступления" class="sort" onclick="listShowSheltersPets(' + Page + ', \'arrival_date\');">Дата поступления</div></th>';
                        TempContent += '<th><div id="departure_date" title="Дата выбытия" class="sort" onclick="listShowSheltersPets(' + Page + ', \'departure_date\');">Дата выбытия</div></th>';
                        TempContent += '<th><div id="aviary" title="Вольер" class="sort" onclick="listShowSheltersPets(' + Page + ', \'aviary\');">Вольер</div></th>';
                        TempContent += '<th><div id="socialized" title="Соц." class="sort" onclick="listShowSheltersPets(' + Page + ', \'socialized\');">Соц.</div></th>';
                        if (TempLevels == 0) {
                            TempContent += '<th></th>';
                        }
                        TempContent += '<th></th>';
                        TempContent += '</tr>';

                        $(Recs).find('rec').each(function () {
                            let FLAG = 0;

                            currentShelterTitle = $(this).find('rec_shelter').text();

                            TempContentRec = '<tr>';

                            if (TempLevels >= 2) {
                                TempContentRec += '<td><div class="area_" title="' + jcms_format_for_json($(this).find('rec_area').text()) + '">' + $(this).find('rec_area').text() + '</div></td>';
                                TempContentRec += '<td><div class="managing_" title="' + jcms_format_for_json($(this).find('rec_managing').text()) + '">' + $(this).find('rec_managing').text() + '</div></td>';
                                TempContentRec += '<td><div class="shelter_" title="' + jcms_format_for_json($(this).find('rec_shelter').text()) + '">' + $(this).find('rec_shelter').text() + '</div></td>';
                            }
                            if (TempLevels > 0 && TempLevels <= 1) {
                                TempContentRec += '<td><div class="shelter_" title="' + jcms_format_for_json($(this).find('rec_shelter').text()) + '">' + $(this).find('rec_shelter').text() + '</div></td>';
                            }

                            if ($(this).find('rec_vac_date_until').text() == '') {
                                TempContentRec += '<td class="warning strong" title="Вакцинация не проводилась">';
                            } else {
                                //Действие вакцинации закончилось
                                let cur_date = new Date();
                                var pattern = /(\d{2})\.(\d{2})\.(\d{4})/;
                                let vac_date = new Date($(this).find('rec_vac_date_until').text().replace(pattern, '$3-$2-$1'));

                                let vac_30_date = new Date();
                                vac_30_date.setDate(vac_date.getDate() - 30);

                                if (cur_date > vac_date) {
                                    TempContentRec += '<td class="warning" title="Действие вакцинации закончилось (' + $(this).find('rec_vac_date_until').text() + ')">';
                                } else if (cur_date < vac_30_date) {
                                    //До вакцинации осталось менее 30 дней (Дата)
                                    TempContentRec += '<td class="attention" title="До вакцинации осталось менее 30 дней (' + $(this).find('rec_vac_date_until').text() + ')">';
                                } else {
                                    TempContentRec += '<td title="Вакцинация (' + $(this).find('rec_vac_date_until').text() + ')">';
                                }
                            }
                            TempContentRec += '<span>';

                            if ($(this).find('rec_status').text() == 'IN_SHELTER') {
                                TempContentRec += 'В приюте';
                            }
                            if ($(this).find('rec_status').text() == 'DEPARTURED') {
                                TempContentRec += 'Выбыло';
                            }
                            if ($(this).find('rec_status').text() == 'QUARANTINE') {
                                TempContentRec += 'Карантин';
                            }
                            if ($(this).find('rec_status').text() == 'QUARANTINE_OTHER') {
                                TempContentRec += 'Карантин (продлён)';
                            }
                            if ($(this).find('rec_status').text() == 'IN_ISOLATION') {
                                TempContentRec += 'В изоляторе';
                            }
                            if ($(this).find('rec_status').text() == 'IN_HOSPITAL') {
                                TempContentRec += 'В стационаре';
                            }

                            TempContentRec += '</span>';

                            TempContentRec += '</td>';

                            TempContentRec += '<td>' + $(this).find('rec_ku').text() + '</td>';

                            TempContentRec += '<td>' + $(this).find('rec_chip').text() + '</td>';

                            TempContentRec += '<td>';
                            if ($(this).find('rec_vac_spec').text()) {
                                TempContentRec += '<div class="specialist" title="Специалист">' + $(this).find('rec_vac_spec').text() + '</div>';
                            } else if ($(this).find('rec_vac_org').text()) {
                                TempContentRec += '<div class="organization" title="Организация">' + $(this).find('rec_vac_org').text() + '</div>';
                            }
                            TempContentRec += '</td>';

                            TempContentRec += '<td>';
                            TempContentRec += '' + $(this).find('rec_vac_date').text() + '';
                            TempContentRec += '</td>';

                            //
                            //
                            TempContentRec += '<td>' + $(this).find('rec_name').text() + '</td>';
                            //

                            TempContentRec += '<td>' + $(this).find('rec_species').text() + '</td>';
                            TempContentRec += '<td>' + $(this).find('rec_arrival_date').text() + '</td>';
                            TempContentRec += '<td>' + $(this).find('rec_departure_date').text() + '</td>';
                            if ($(this).find('rec_aviary').text()) {
                                TempContentRec += '<td>';
                                TempContentRec += '' + $(this).find('rec_aviary').text() + '';
                                TempContentRec += '</td>';
                            } else {
                                TempContentRec += '<td class="warning">';
                                TempContentRec += '<span>Нет данных</span>';
                                TempContentRec += '</td>';
                            }
                            TempContentRec += '<td>';
                            if ($(this).find('rec_socialized').text() == 't') {
                                TempContentRec += 'Да';
                            } else {
                                TempContentRec += 'Нет';
                            }
                            TempContentRec += '</td>';

                            if (TempLevels == 0 && window.canUserEdit) {
                                TempContentRec += '<td style="width: 32px;">';
                                if (arrayVaccinations.length > 0) {
                                    if (arrayVaccinations.includes($(this).find('rec_id').text())) {
                                        TempContentRec += '<div class="delete_vac" onclick="executeSheltersVac(' + $(this).find('rec_id').text() + ');" title="Удалить из списка вакцинирования" id="vac_' + $(this).find('rec_id').text() + '"></div>';
                                    } else {
                                        TempContentRec += '<div class="add_vac" onclick="executeSheltersVac(' + $(this).find('rec_id').text() + ');" title="Добавить к списку вакцинирования" id="vac_' + $(this).find('rec_id').text() + '"><div></div></div>';
                                    }
                                } else {
                                    TempContentRec += '<div class="add_vac" onclick="executeSheltersVac(' + $(this).find('rec_id').text() + ');" title="Добавить к списку вакцинирования" id="vac_' + $(this).find('rec_id').text() + '"></div>';
                                }
                                TempContentRec += '</td>';
                            }

                            TempContentRec += '<td style="width: 32px;">';
                            TempContentRec += '<a href="./?action=edit&id=' + $(this).find('rec_id').text() + '">';
                            TempContentRec += '<div class="edit"></div>';
                            // TempContentRec+='<button class="button" style="width: 150px;">Просмотр</button>';
                            TempContentRec += '</a>';
                            TempContentRec += '</td>';

                            TempContentRec += '</tr>';
                            if (FLAG == 0) {
                                TempRecsNum++;
                                TempContent += TempContentRec;
                            }
                        });
                        TempContent += '</table>';

                        ArraySheltersRecs = [];
                        $(Recs).find('shelter').each(function () {
                            ArraySheltersRecs.push({
                                id: $(this).find('id').text(),
                                title: $(this).find('title').text()
                            });
                        });

                        ArrayAreasRecs = [];
                        $(Recs).find('area').each(function () {
                            ArrayAreasRecs.push({id: $(this).find('id').text(), title: $(this).find('title').text()});
                        });

                        TempContent += '<div class="pages">';
                        if (parseInt($(Recs).find('counter').text()) > parseInt($(Recs).find('to').text())) {
                            TempContent += '<div class="info">' + $(Recs).find('from').text() + ' - ' + $(Recs).find('to').text() + ' из ' + $(Recs).find('counter').text() + ' записей</div>';
                        } else {
                            TempContent += '<div class="info">' + $(Recs).find('from').text() + ' - ' + $(Recs).find('counter').text() + ' из ' + $(Recs).find('counter').text() + ' записей</div>';
                        }
                        if (parseInt($(Recs).find('current').text()) > 1) {
                            TempContent += '<div class="first" onclick="listShowSheltersPets(1,\'' + $(Recs).find('sort').text() + '\')")"></div>';
                            TempContent += '<div class="previous" onclick="listShowSheltersPets(' + (parseInt($(Recs).find('current').text()) - 1) + ',\'' + $(Recs).find('sort').text() + '\')")"></div>';
                        }

                        //информер
                        $("#all").html($(Recs).find('counter').text());
                        //информер

                        //
                        if ($(Recs).find('current').text() > 3) {
                            for (let i = parseInt($(Recs).find('current').text()) - 2; i <= parseInt($(Recs).find('current').text()) + 2; i++) {
                                if (i <= (parseInt($(Recs).find('last').text()) + 1)) {
                                    TempContent += '<div ';
                                    if ($(Recs).find('current').text() == i) {
                                        TempContent += 'class="current" ';
                                    }
                                    TempContent += 'onclick="listShowSheltersPets(' + i + ',\'' + $(Recs).find('sort').text() + '\')")">' + i + '</div>';
                                }
                            }
                        } else {
                            for (let i = 1; i <= 5; i++) {
                                if (i <= (parseInt($(Recs).find('last').text()) + 1)) {
                                    TempContent += '<div ';
                                    if ($(Recs).find('current').text() == i) {
                                        TempContent += 'class="current" ';
                                    }
                                    TempContent += 'onclick="listShowSheltersPets(' + i + ',\'' + $(Recs).find('sort').text() + '\')">' + i + '</div>';
                                }
                            }
                        }

                        if (parseInt($(Recs).find('current').text()) < (parseInt($(Recs).find('last').text()) + 1)) {
                            TempContent += '<div class="next" onclick="listShowSheltersPets(' + (parseInt($(Recs).find('current').text()) + 1) + ',\'' + $(Recs).find('sort').text() + '\')")"></div>';
                            TempContent += '<div class="last" onclick="listShowSheltersPets(' + (parseInt($(Recs).find('last').text()) + 1) + ',\'' + $(Recs).find('sort').text() + '\')")"></div>';
                        }

                        TempContent += '</div>';
                    }

                    if (TempRecsNum == 0) {
                        TempContent = '<div class="message">По данному запросу не найдено записей.</div>';
                    }
                    document.getElementById('ListRecs').innerHTML = TempContent;

                    /////////////////
                    let SearchForm = document.getElementById("form_search_recs");
                    let ListRecsTable = document.getElementById("ListRecsTable");

					let stickySearch = document.getElementById("shelters_search");
					let stickyHeader = document.getElementById("shelters_header");
					let RootClass = document.querySelector(":root");
					RootClass.style.setProperty("--table-header-top", stickyHeader?.offsetHeight - 1 + 'px');
					if (stickyHeader) stickyHeader.style.top = stickySearch.offsetHeight - 1 + 'px';

                    // console.log((SearchForm.offsetWidth+30));
                    // console.log('ListRecsTable - '+ListRecsTable.clientWidth);
                    // console.log('ListRecsTable - '+ListRecsTable.offsetWidth);

					if (SearchForm && ListRecsTable && ((SearchForm.offsetWidth + 30) < ListRecsTable.offsetWidth)) {
						if (TempLevels >= 2) {
							$("#area").width("64");
							$("#managing").width("64");
							$("#shelter").width("64");
						}

                        $("#arrival_date").width("64");
                        $("#departure_date").width("64");

                        $("#vac_date").width("64");
                        $("#vac_spec").width("64");

                    }

					if (SearchForm && ListRecsTable && ((SearchForm.offsetWidth + 30) < ListRecsTable.offsetWidth)) {
						if (TempLevels >= 2) {
							$("#area").width("32");
							$("#managing").width("32");
							$("#shelter").width("32");

                            $(".area_").width("32");
                            $(".managing_").width("32");
                            $(".shelter_").width("32");

                            $(".area_").addClass("ams");
                            $(".managing_").addClass("ams");
                            $(".shelter_").addClass("ams");
                        }

                        $("#vac_date").width("32");
                        $("#arrival_date").width("32");
                        $("#departure_date").width("32");
                    }
                    /////////////////

                    //
                    if ($(Recs).find('direction').text() == '1') {
                        currentDirection = 1;
                        $('#' + $(Recs).find('sort').text()).addClass('down');
                    } else {
                        currentDirection = 0;
                        $('#' + $(Recs).find('sort').text()).addClass('up');
                    }
                    if (Sort == null && $(Recs).find('sort').text() != '') {
                        currentSort = $(Recs).find('sort').text();
                    }
                    //

                    ///Кнопки
                    TempContent = '';
                    if (TempLevels == 0) {
                        let vaccinations = vetas_get_cookie("vaccinations");
                        let arrayVaccinations = [];
                        if (vaccinations !== undefined && vaccinations != '') {
                            arrayVaccinations = vaccinations.split(';');
                        }
                        if (arrayVaccinations.length > 0) {
                            TempContent += '<button onclick="showSheltersVacWindow();" id="shelters_list_vac" style="margin-right: 10px;" class="list_">Список вакцинирования (' + (arrayVaccinations.length - 1) + ')</button>';
                        } else {
                            TempContent += '<button onclick="showSheltersVacWindow();" id="shelters_list_vac" style="display: none; margin-right: 10px;"></button>';
                        }
                    }

                    if (TempRecsNum > 0) {
                        TempContent += '<button onclick="showButtonMenu(\'shelters_export\');" class="export_">Печать</button>';
                        TempContent += '<div class="button_menu" id="menu_shelters_export" style="display: none;">';
                        TempContent += '<div onclick="showSheltersReportWindow(\'monitoring_report\');">Отчёт о мониторинге</div>';
                        TempContent += '<div onclick="showSheltersReportWindow(\'shelters_report\');">Отчёт о животных, содержащихся в приютах</div>';
                        TempContent += '<div onclick="showSheltersReportWindow(\'aviaries_report\');">Список животных по вольерам</div>';
                        TempContent += '<div onclick="showSheltersReportWindow(\'euthanasia_report\');">Журнал учета случаев эвтаназии</div>';

                        TempContent += '<div onclick="showSheltersReportWindow(\'catch_in_shelter_week_info\');">Информация по отловленным и поступившим в приюты (собак, кошек)</div>';
                        TempContent += '<div onclick="showSheltersReportWindow(\'new_owner_info\');">Информация о новых владельцах животных</div>';
                        TempContent += '<div onclick="showSheltersReportWindow(\'out_death_signature\');">Выбытие по причине смерти с описью</div>';
                        TempContent += '<div onclick="showSheltersReportWindow(\'dvigenie_beznadzor\');">Информация по движению безнадзорных животных (собак, кошек) в приютах</div>';
                        TempContent += '<div onclick="showSheltersReportWindow(\'castrated_info\');">Информация по стерилизации животных за отчетный период</div>';
                        TempContent += '<div onclick="showSheltersReportWindow(\'puppy_to_dog\');">Aкт перевода из щенков в собаки с описью</div>';
                        TempContent += '<div onclick="showSheltersReportWindow(\'kitty_to_cat\');">Aкт перевода из котят в кошки с описью</div>';

                        //ГОТОВЫЕ ОТЧЕТЫ - ПО НАДОБНОСТИ РАСКОММЕНТИРОВАТЬ
                        // TempContent += '<div onclick="showSheltersReportWindow(\'mosvet_week_report\');">Мосветобъединение недельный отчет</div>';
                        // TempContent += '<div onclick="showSheltersReportWindow(\'mosvet_death_info\');">Мосветобъединение информация от АО по павшим животным</div>';
                        // TempContent += '<div onclick="showSheltersReportWindow(\'fauna_monitoring_report\');">Справка по мониторингу городской фауны за отчетную неделю</div>';
                        // TempContent += '<div onclick="showSheltersReportWindow(\'monitoring_report\');">Отчёт о мониторинге</div>';
                        // TempContent += '<div onclick="showSheltersReportWindow(\'act_beshenstvo\');">Aкт Бешенство</div>';
                        // TempContent += '<div onclick="showSheltersReportWindow(\'act_degelmintizacii\');">Aкт Дегельминтизации</div>';
                        // TempContent += '<div onclick="showSheltersReportWindow(\'act_ektoparazit\');">Aкт обработок от эктопаразитов</div>';
                        // TempContent += '<div onclick="showSheltersReportWindow(\'reestr_count_animals\');">Реестр о количестве животных в приюте</div>';


                        TempContent += '</div>';
                    }

                    if (TempLevels == 0 && canUserEdit) {
                        TempContent += '<a href="./?action=add"><button style="margin-left: 10px;" class="add_">Добавить животное</button></a>';
                    }
                    document.getElementById("shelters_buttons").innerHTML = TempContent;
                    ///Кнопки
                }
            }
        });
    }
}

function showSheltersTempDocuments(Id) {
    jQuery.ajax({
        'async': true,
        'global': false,
        'cache': false,
        'type': 'GET',
        'dataType': 'xml',
        'data': '&id=' + Id + '&mode=xml&action=temp_documents&id=' + Id,
        'url': "index.php",
        'beforeSend': function () {
            //показать загрузку
        },
        'success': function (Data) {
            //files
            if ($(Data).find('files').text()) {
                TempContent = '';
                $(Data).find('files').find('file').each(function () {
                    TempContent += '<div class="document">';
                    TempContent += '<div class="title">' + $(this).find('name').text() + '</div>';
                    TempContent += '<div class="download" onclick="downloadSheltersDocument(' + $(this).find('id_file').text() + ');"></div>';
                    if ($(this).find('protected').text() == 1) {
                        TempContent += '<div class="null"></div>';
                    } else {
                        TempContent += '<div class="delete" onclick="deleteSheltersDocument(' + $(this).find('id_file').text() + ');"></div>';
                    }

                    TempContent += '</div>';
                });

                document.getElementById("temp_documents").innerHTML = TempContent;
            }
            //files
        }
    });
}

function showSheltersAddImageWindow(Mode) {
    let TempContent = '';

    TempContent += '<div id="shelters_document_window" class="window main_row col-xl-4 col-lg-6 col-12">';
    //
    TempContent += '<div class="close" onclick="closeSheltersAddImageWindow();"></div>';
    TempContent += '<div class="top">Добавить изображение</div>';

    TempContent += '<div class="error" id="document_window_message" style="display: none; margin: 15px;"></div>';

    TempContent += '<div class="col-12 main_row">';
    TempContent += '<form enctype="multipart/form-data" method="POST" class="form-window main_row" id="form_shelters_document">';

    TempContent += '<div class="col-12 required">Изображение:</div>';
    TempContent += '<div class="col-12">';
    TempContent += '<input type="file" name="document" id="document" value="">';
    TempContent += '</div>';

    TempContent += '<input type="hidden" name="action" id="action" value="add_image">';
    TempContent += '<input type="hidden" name="mode" id="mode" value="xml">';

    TempContent += '</form>';
    TempContent += '</div>';

    //Кнопки управления
    TempContent += '<div class="controls">';
    TempContent += '<button class="button" id="add_document" style="width: 250px;" onclick="addSheltersImage();">Добавить изображение</button>';
    TempContent += '</div>';
    //Кнопки управления

    TempContent += '</div>';

    document.getElementById("sub_container_m").innerHTML = TempContent;
    $("#background_m").fadeIn();
    $("#container_m").show();
}

function closeSheltersAddImageWindow() {
    $("#background_m").fadeOut();
    $("#container_m").hide();
}

function removewithfilter(arr) {
    let outputArray = arr.filter(function (v, i, self) {
        return i == self.indexOf(v);
    });

    return outputArray;
}

function executeSheltersVac(Id) {
    let vaccinations = vetas_get_cookie("vaccinations");
    let arrayVaccinations = [];
    if (vaccinations !== undefined) {
        arrayVaccinations = vaccinations.split(';');
    }

    arrayVaccinations = removewithfilter(arrayVaccinations);

    if (~arrayVaccinations.indexOf('' + Id + '')) {
        console.log(Id);
        $("#vac_" + Id).removeClass("delete_vac").addClass("add_vac");
        arrayVaccinations = arrayVaccinations.filter(e => e !== Id);
        arrayVaccinations.splice(arrayVaccinations.indexOf('' + Id + ''), 1);
    } else {
        $("#vac_" + Id).removeClass("add_vac").addClass("delete_vac");
        arrayVaccinations.push(Id);
    }

    let Temp = '';
    for (let i = 0; i < arrayVaccinations.length; i++) {
        if (arrayVaccinations[i] != '') {
            Temp += arrayVaccinations[i] + ';'
        }
    }

    if (arrayVaccinations.length > 0 && Temp != '') {
        $("#shelters_list_vac").html('Список вакцинирования (' + (arrayVaccinations.length - 1) + ')');
        $("#shelters_list_vac").show();
    } else {
        $("#shelters_list_vac").hide();
    }

    vetas_set_cookie("vaccinations", Temp, {'max-age': 7200});
}

function deleteSheltersVac(Id) {
    let vaccinations = vetas_get_cookie("vaccinations");
    let arrayVaccinations = [];
    if (vaccinations !== undefined) {
        arrayVaccinations = vaccinations.split(';');
    }


    let Temp = '';

    for (let i = 0; i < arrayVaccinations.length; i++) {
        if (arrayVaccinations[i] && arrayVaccinations[i] != Id) {
            Temp += arrayVaccinations[i] + ';'
        }
    }


    vetas_set_cookie("vaccinations", Temp, {'max-age': 7200});
}

function addSheltersImage() {
    if ($('#shelters_document_window #document').val() == '') {
        $("#document_window_message").html('Необходимо выбрать изображение для загрузки.');
        $("#document_window_message").show();

        return true;
    }

    var dataForm = new FormData();
    jQuery.each(jQuery('#document')[0].files, function (i, file) {
        dataForm.append('file', file);
    });
    dataForm.append('action', $('#shelters_document_window #action').val());
    dataForm.append('mode', $('#shelters_document_window #mode').val());
    dataForm.append('pet', $('#pet_id').val() !== undefined ? $('#pet_id').val() : '');
    dataForm.append('temp_pet', $('#temp_pet').val() !== undefined ? $('#temp_pet').val() : '');

    $.ajax({
        'url': "index.php",
        'data': dataForm,
        'processData': false,
        'contentType': false,
        'type': 'POST',
        'method': 'POST',
        'dataType': 'xml',
        'beforeSend': function () {
            showTopLoading();
        },
        'success': function (Data) {
            closeTopLoading();
            showSheltersInformation($('#pet_id').val(), 'tab1');
            showSheltersTab(1);
            closeSheltersAddImageWindow();
        }
    });
}

function showSheltersAddDocumentWindow(Mode) {
    //считываем текущий статус и формирует набор документов

    //возможно, удаляем ненужное из списка
    let TempContent = '';

    TempContent += '<div id="shelters_document_window" class="window main_row col-xl-4 col-lg-6 col-12">';
    //
    TempContent += '<div class="close" onclick="closeSheltersAddDocumentWindow();"></div>';
    TempContent += '<div class="top">Добавить документ</div>';

    TempContent += '<div class="error" id="document_window_message" style="display: none; margin: 15px;"></div>';

    TempContent += '<div class="col-12 main_row">';
    TempContent += '<form enctype="multipart/form-data" method="POST" class="form-window main_row" id="form_shelters_document">';

    TempContent += '<div class="col-12 required">Тип документа:</div>';
    TempContent += '<div class="col-12">';
    TempContent += '<select name="type" id="type">';
    if (Mode == 'add') {
        //находим основание прибытия
        for (let i = 0; i <= ArrayDocumentTypesRecs.length - 1; i++) {
            if (ArrayDocumentTypesRecs[i].group == $("#arrival_reason").val()) {
                TempContent += '<option value="' + ArrayDocumentTypesRecs[i].id + '" type="' + ArrayDocumentTypesRecs[i].type + '">' + ArrayDocumentTypesRecs[i].name + '</option>';
            }
        }
    }
    if (Mode == 'save') {
        //находим основание прибытия
        for (let i = 0; i <= ArrayDocumentTypesRecs.length - 1; i++) {
                TempContent += '<option value="' + ArrayDocumentTypesRecs[i].id + '" type="' + ArrayDocumentTypesRecs[i].type + '">' + ArrayDocumentTypesRecs[i].name + '</option>';
        }
    }
    if (Mode == 'departure') {
        //находим причину выбытия

        //для эвтаназии свои документы
        if ($("#departure_reason").val() == 'DEATH') {
            for (let i = 0; i <= ArrayDocumentTypesRecs.length - 1; i++) {
                if ($("#reason_death").val() == ArrayDocumentTypesRecs[i].group) {
                    TempContent += '<option value="' + ArrayDocumentTypesRecs[i].id + '" type="' + ArrayDocumentTypesRecs[i].type + '">' + ArrayDocumentTypesRecs[i].name + '</option>';
                }
            }
        } else {
            for (let i = 0; i <= ArrayDocumentTypesRecs.length - 1; i++) {
                if (ArrayDocumentTypesRecs[i].group == $("#departure_reason").val()) {
                    TempContent += '<option value="' + ArrayDocumentTypesRecs[i].id + '" type="' + ArrayDocumentTypesRecs[i].type + '">' + ArrayDocumentTypesRecs[i].name + '</option>';
                }
            }
        }
    }



    if (Mode == 'edit') {
        //добавляем некий текущий набор документов
    }
    TempContent += '</select>';

    TempContent += '</div>';

    TempContent += '<div class="col-12 required">№ документа:</div>';
    TempContent += '<div class="col-12">';
    TempContent += '<input type="text" name="number" id="number" value="" autocomplete="off">';
    TempContent += '</div>';

    TempContent += '<div class="col-12 required">Дата документа:</div>';
    TempContent += '<div class="col-12">';
    TempContent += '<input type="date" name="date" id="date" autocomplete="off" style="width: 150px;" value="" min="1950-01-01" max="' + get_current_date() + '">';
    TempContent += '</div>';

    TempContent += '<div class="col-12 required">Документ:</div>';
    TempContent += '<div class="col-12">';
    TempContent += '<input type="file" name="document" id="document" value="">';
    TempContent += '</div>';

    TempContent += '<input type="hidden" name="action" id="action" value="add_document">';
    TempContent += '<input type="hidden" name="mode" id="mode" value="xml">';

    TempContent += '</form>';
    TempContent += '</div>';

    //Кнопки управления
    TempContent += '<div class="controls">';
    if (Mode == 'save') {
        TempContent += '<button class="button" id="add_document" style="width: 250px;" onclick="addSheltersDocument(\'save\');">Добавить документ</button>';
    } else if (Mode == 'add') {
        TempContent += '<button class="button" id="add_document" style="width: 250px;" onclick="addSheltersDocument(\'add\');">Добавить документ</button>';
    } else {
        TempContent += '<button class="button" id="add_document" style="width: 250px;" onclick="addSheltersDocument();">Добавить документ</button>';
    }
    TempContent += '</div>';
    //Кнопки управления

    TempContent += '</div>';

    document.getElementById("sub_container_m").innerHTML = TempContent;
    $("#background_m").fadeIn();
    $("#container_m").show();
}

function closeSheltersAddDocumentWindow() {
    $("#background_m").fadeOut();
    $("#container_m").hide();
}

function addSheltersDocument(Mode = null) {
    if ($('#shelters_document_window #number').val() == '' || $('#shelters_document_window #date').val() == '' || $('#shelters_document_window #document').val() == '') {
        $("#document_window_message").html('Не заполнены необходимые поля.');
        $("#document_window_message").show();

        return true;
    }

    var dataForm = new FormData();
    jQuery.each(jQuery('#document')[0].files, function (i, file) {
        dataForm.append('file', file);
    });
    dataForm.append('action', $('#shelters_document_window #action').val());
    dataForm.append('mode', $('#shelters_document_window #mode').val());
    dataForm.append('number', $('#shelters_document_window #number').val());
    dataForm.append('date', $('#shelters_document_window #date').val());
    dataForm.append('type', $('#shelters_document_window #type').val());
    dataForm.append('pet', $('#pet_id').val() !== undefined ? $('#pet_id').val() : '');
    dataForm.append('temp_pet', $('#temp_pet').val() !== undefined ? $('#temp_pet').val() : '');

    $.ajax({
        'url': "index.php",
        'data': dataForm,
        'processData': false,
        'contentType': false,
        'type': 'POST',
        'method': 'POST',
        'dataType': 'xml',
        'beforeSend': function () {
            showTopLoading();
        },
        'success': function (Data) {
            closeTopLoading();
            if (Mode == 'add') {
                //обновляем документы
                showSheltersTempDocuments($('#temp_pet').val());

                //закрываем окно
                closeSheltersAddDocumentWindow();
            } else if (Mode == 'save') {
                showSheltersInformation($('#pet_id').val(), 'tab2');
                showSheltersTab(2);
                closeSheltersAddDocumentWindow();
            } else {
                if ($(Data).find('message').text() == 'document_added') {
                    showSheltersDepartureDocuments($('#pet_id').val());
                    closeSheltersAddDocumentWindow();
                }
            }
        }
    });
}

function showSheltersDepartureDocuments(Id) {
    jQuery.ajax({
        'async': true,
        'global': false,
        'cache': false,
        'type': 'GET',
        'dataType': 'xml',
        'data': '&id=' + Id + '&mode=xml&action=departure_documents&id=' + Id,
        'url': "index.php",
        'beforeSend': function () {
            //показать загрузку
        },
        'success': function (Data) {
            //files
            if ($(Data).find('files').text()) {
                TempContent = '';
                $(Data).find('files').find('file').each(function () {
                    TempContent += '<div class="document">';
                    TempContent += '<div class="title">' + $(this).find('name').text() + '</div>';
                    // TempContent+='<div class="download" onclick="downloadSheltersDocument(' + $(this).find('id_file').text() + ');"></div>';
                    // if($(this).find('protected').text() == 1){
                    // 	TempContent+='<div class="null"></div>';
                    // }else{
                    // 	TempContent+='<div class="delete" onclick="deleteSheltersDocument(' + $(this).find('id_file').text() + ');"></div>';
                    // }
                    TempContent += '</div>';
                });

                $("#form_departure #documents").html(TempContent);
            }
            //files
        }
    });
}

function deleteSheltersDocument(id_file) {
    jQuery.ajax({
        'async': true,
        'global': false,
        'cache': false,
        'type': 'GET',
        'dataType': 'xml',
        'data': 'action=delete_document&xml=1&document_id=' + id_file,
        'url': "index.php",
        'beforeSend': function () {
            showTopLoading();
        },
        'success': function (Data) {
            closeTopLoading();
            showSheltersInformation($('#pet_id').val(), 'tab2');
            showSheltersTab(2);
            closeSheltersAddDocumentWindow();
        }
    });
}

function deleteSheltersImage(id_file) {
    jQuery.ajax({
        'async': true,
        'global': false,
        'cache': false,
        'type': 'GET',
        'dataType': 'xml',
        'data': 'action=delete_image&xml=1&image_id=' + id_file,
        'url': "index.php",
        'beforeSend': function () {
            showTopLoading();
        },
        'success': function (Data) {
            closeTopLoading();
            showSheltersInformation($('#pet_id').val(), 'tab1');
            showSheltersTab(1);
            closeSheltersAddDocumentWindow();
        }
    });
}

function mainSheltersImage(id_file) {
    jQuery.ajax({
        'async': true,
        'global': false,
        'cache': false,
        'type': 'GET',
        'dataType': 'xml',
        'data': 'action=main_image&xml=1&image_id=' + id_file,
        'url': "index.php",
        'beforeSend': function () {
            showTopLoading();
        },
        'success': function (Data) {
            closeTopLoading();
            showSheltersInformation($('#pet_id').val(), 'tab1');
            showSheltersTab(1);
            closeSheltersAddDocumentWindow();
        }
    });
}

function downloadSheltersDocument(id_file) {
    location.href = 'index.php?action=download_document&mode=xml&document_id=' + id_file;
}

function downloadSheltersImage(id_file) {
    location.href = 'index.php?action=download_image&mode=xml&image_id=' + id_file;
}

function showSheltersAviaryWindow() {
    jQuery.ajax({
        'async': true,
        'global': false,
        'cache': false,
        'type': 'GET',
        'dataType': 'xml',
        'data': 'action=aviaries&mode=xml',
        'url': "index.php",
        'beforeSend': function () {
            //показать загрузку
            showLoading('ListRecs');
        },
        'success': function (Recs) {
            if ($(Recs).find('message').text() == 'token_invalid') {
                window.location.href = 'index.php';
            } else {
                let TempContent = '';

                TempContent += '<div id="shelters_aviaries_window" class="window main_row col-xl-4 col-lg-6 col-12">';
                //
                TempContent += '<div class="close" onclick="closeSheltersAviaryWindow();"></div>';
                TempContent += '<div class="top">Выбрать вольер</div>';


                TempContent += '<div class="col-12">';
                TempContent += '<form class="form-window" id="shelters_aviaries_list">';

                TempContent += '<div id="shelters_aviares" style="overflow-y: overlay; overflow-x: hidden;">';
                if ($(Recs).find('rec').text()) {
                    // if($('#aviary_').val() == '0'){
                    TempContent += '<div class="row" style="margin-bottom: 6px; padding-top: 6px; padding-bottom: 6px; border-bottom: 1px solid #dee2e6; background: var(--slider-background);">';
                    TempContent += '<div class="col-1 row-center-align row-justify-content">';
                    TempContent += '<input type="radio" name="aviary" id="aviary_0" value="0" checked>';
                    TempContent += '</div>';
                    TempContent += '<div class="col-11">';
                    TempContent += '<label for="aviary_0"><span style="font-size: 18px; font-weight: bold;">Без вольера</span></label>';
                    TempContent += '</div>';
                    TempContent += '</div>';
                    // }

                    let l = 0;
                    $(Recs).find('rec').each(function () {
                        TempContent += '<div class="row" style="';

                        if ($('#aviary_').val() == $(this).find('rec_id').text()) {
                            TempContent += 'background: var(--slider-background); padding-top: 6px; padding-bottom: 6px;';
                        }

                        if (l != 0) {
                            TempContent += 'border-top: 1px solid #dee2e6; padding-top: 6px; margin-top: 6px;';
                        }

                        TempContent += '">';


                        TempContent += '<div class="col-1 row-center-align row-justify-content">';
                        TempContent += '<input type="radio" name="aviary" id="aviary_' + $(this).find('rec_id').text() + '" value="' + $(this).find('rec_id').text() + '"';
                        if ($('#aviary_').val() == $(this).find('rec_id').text()) {
                            TempContent += ' checked';
                        }
                        TempContent += '>';
                        TempContent += '</div>';

                        TempContent += '<div class="col-11">';
                        TempContent += '<label for="aviary_' + $(this).find('rec_id').text() + '"><span style="font-size: 18px; font-weight: bold;">' + $(this).find('rec_title').text() + '</span>';

                        if ($(this).find('rec_number').text()) {
                            TempContent += ' (№' + $(this).find('rec_number').text() + ')';
                        }

                        if ($(this).find('rec_description').text()) {
                            TempContent += '<br />' + $(this).find('rec_description').text() + '';
                        }

                        TempContent += '</label>';
                        TempContent += '</div>';

                        TempContent += '</div>';

                        l++;
                    });
                }
                TempContent += '</div>';

                TempContent += '</form>';
                TempContent += '</div>';

                //Кнопки управления
                TempContent += '<div class="controls">';
                TempContent += '<button class="button" id="choose_shelters_aviary_button" style="width: 250px;" onclick="chooseSheltersAviary();">Выбрать вольер</button>';
                TempContent += '</div>';
                //Кнопки управления

                TempContent += '</div>';

                document.getElementById("sub_container").innerHTML = TempContent;
                $("#background").fadeIn();
                $("#container").show();

                if ($("#shelters_aviaries_window").height() - 300 >= 150) {
                    $("#shelters_aviares").height($("#shelters_aviaries_window").height() - 300);
                }
            }
        }
    });
}

function showDateAviaryWindow() {
    jQuery.ajax({
        'async': true,
        'global': false,
        'cache': false,
        'type': 'GET',
        'dataType': 'xml',
        'data': 'action=aviaries&mode=xml',
        'url': "index.php",
        'beforeSend': function () {
            //показать загрузку
            showLoading('ListRecs');
        },
        'success': function (Recs) {
            if ($(Recs).find('message').text() == 'token_invalid') {
                window.location.href = 'index.php';
            } else {
                let TempContent = '';

                TempContent += '<div id="shelters_aviaries_window" class="window main_row col-xl-4 col-lg-6 col-12">';
                //
                TempContent += '<div class="close" onclick="closeDateAviaryWindow();"></div>';
                TempContent += '<div class="top">Изменить дату прибытия</div>';


                TempContent += '<div class="col-12">';
                TempContent += '<form class="form-window" id="shelters_aviaries_list">';

                TempContent += '<input type="date" name="arrival_date" id="arrival_date" value="">';

                TempContent += '</form>';
                TempContent += '</div>';

                //Кнопки управления
                TempContent += '<div class="controls">';
                TempContent += '<button class="button" id="choose_date_aviary_button" style="width: 250px;" onclick="chooseDateAviary();">Сохранить</button>';
                TempContent += '</div>';
                //Кнопки управления

                TempContent += '</div>';

                document.getElementById("sub_container").innerHTML = TempContent;
                $("#background").fadeIn();
                $("#container").show();
            }
        }
    });
}

function closeDateAviaryWindow() {
    $("#background").fadeOut();
    $("#container").hide();
}

function chooseDateAviary() {
    let date = $('input[name="arrival_date"]').val();
    jQuery.ajax({
        'async': true,
        'global': false,
        'cache': false,
        'type': 'GET',
        'dataType': 'xml',
        'data': '&pet=' + $('#pet_id').val() + '&action=choose_date_aviary&date=' + date + '&mode=xml',
        'url': "index.php",
        'beforeSend': function () {
            //блокируем кнопку
            $('#choose_date_aviary_button').prop('disabled', true);

            //показать загрузку
            showTopLoading();
        },
        'success': function (Data) {
            if ($(Data).find('message').text() == 'date_choosen'
            ) {
                showSheltersInformation($('#pet_id').val(), 'tab2');
                closeDateAviaryWindow();
            } else {
                closeTopLoading();
                closeDateAviaryWindow();
            }
            closeTopLoading();
        }
    });
}

function closeSheltersAviaryWindow() {
    $("#background").fadeOut();
    $("#container").hide();
}

function chooseSheltersAviary() {
    let aviaries = $('input[name="aviary"]');
    let aviary = '';
    if (aviaries.length >= 1) {
        aviaries.each(function () {
            if ($(this).prop("checked") == true) {
                aviary = $(this).val();
            }
        });
    }

    jQuery.ajax({
        'async': true,
        'global': false,
        'cache': false,
        'type': 'GET',
        'dataType': 'xml',
        'data': 'aviary=' + aviary + '&pet=' + $('#pet_id').val() + '&action=choose_aviary&mode=xml',
        'url': "index.php",
        'beforeSend': function () {
            //блокируем кнопку
            $('#choose_shelters_aviary_button').prop('disabled', true);

            //показать загрузку
            showTopLoading();
        },
        'success': function (Data) {
            if ($(Data).find('message').text() == 'aviary_choosen') {
                showSheltersInformation($('#pet_id').val(), 'tab2');
                showSheltersTab(2);
                closeSheltersAviaryWindow();
            }
            if ($(Data).find('message').text() == 'aviary_not_choosen') {
                closeSheltersAviaryWindow();
            }
            closeTopLoading();
        }
    });
}

function chooseSheltersHealth(Status) {
    jQuery.ajax({
        'async': true,
        'global': false,
        'cache': false,
        'type': 'GET',
        'dataType': 'xml',
        'data': 'status=' + Status + '&pet=' + $('#pet_id').val() + '&action=choose_health&mode=xml',
        'url': "index.php",
        'beforeSend': function () {
            //показать загрузку
            showTopLoading();
        },
        'success': function (Data) {
            if ($(Data).find('message').text() == 'health_choosen') {
                showSheltersInformation($('#pet_id').val(), 'tab3');
                showSheltersTab(3);
            }
            closeTopLoading();
        }
    });
}

function showSheltersReportWindow(Id) {
    let TempContent = '';

    TempContent += '<div id="shelters_report_window" class="window main_row col-xl-4 col-lg-6 col-12">';
    //
    TempContent += '<div class="close" onclick="closeSheltersReportWindow();"></div>';
    if (Id == 'monitoring_report') {
        TempContent += '<div class="top">Отчёт о мониторинге</div>';
    } else if (Id == 'shelters_report') {
        TempContent += '<div class="top">Отчёт о животных, содержащихся в приютах</div>';
    } else if (Id == 'aviaries_report') {
        TempContent += '<div class="top">Список животных по вольерам</div>';
    } else if (Id == 'euthanasia_report') {
        TempContent += '<div class="top">Журнал учета случаев эвтаназии</div>';
    } else if (Id == 'mosvet_week_report') {
        TempContent += '<div class="top">Мосветобъединение недельный отчет</div>';
    } else if (Id == 'catch_in_shelter_week_info') {
        TempContent += '<div class="top"><div style="width: 95%">Информация по отловленным и поступившим в приюты (собак, кошек)</div></div>';
    } else if (Id == 'dvigenie_beznadzor') {
        TempContent += '<div class="top"><div style="width: 95%">Информация по движению безнадзорных животных (собак, кошек) в приютах</div></div>';
    } else if (Id == 'new_owner_info') {
        TempContent += '<div class="top">Информация о новых владельцах животных</div>';
    } else if (Id == 'mosvet_death_info') {
        TempContent += '<div class="top"><div style="width: 95%">Мосветобъединение информация от АО по павшим животным</div></div>';
    } else if (Id == 'out_death_signature') {
        TempContent += '<div class="top"><div style="width: 95%">Выбытие по причине смерти с описью</div></div>';
    } else if (Id == 'castrated_info') {
        TempContent += '<div class="top"><div style="width: 95%">Информация по стерилизации животных за отчетный период</div></div>';
    } else if (Id == 'reestr_count_animals') {
        TempContent += '<div class="top"><div style="width: 95%">Реестр о количестве животных в приюте</div></div>';
    } else if (Id == 'puppy_to_dog') {
        TempContent += '<div class="top"><div style="width: 95%">Aкт перевода из щенков в собаки с описью</div></div>';
    } else if (Id == 'kitty_to_cat') {
        TempContent += '<div class="top"><div style="width: 95%">Aкт перевода из котят в кошки с описью</div></div>';
    } else if (Id == 'fauna_monitoring_report') {
        TempContent += '<div class="top"><div style="width: 95%">Справка по мониторингу городской фауны за отчетную неделю</div></div>';
    } else if (Id == 'act_beshenstvo') {
        TempContent += '<div class="top"><div style="width: 95%">Акт Бешенство</div></div>';
    } else if (Id == 'act_degelmintizacii') {
        TempContent += '<div class="top"><div style="width: 95%">Акт Дегельминтизации</div></div>';
    } else if (Id == 'act_ektoparazit') {
        TempContent += '<div class="top"><div style="width: 95%">Акт обработок от эктопаразитов</div></div>';
    }

    TempContent += '<div class="error" id="shelters_report_window_message" style="margin: 15px; display: none;"></div>';

    TempContent += '<div class="col-12 main_row">';
    TempContent += '<form class="form-window main_row" id="form_shelters_report">';

    if (Id == 'monitoring_report') {
        if (currentLevel >= 2) {
            //для ДЖКХ
            TempContent += '<div class="col-12">Округ:</div>';
            TempContent += '<div class="col-12">';
            TempContent += '<select name="area">';
            TempContent += '<option value="">Все</option>';
            for (let k = 0; k <= ArrayAreasRecs.length - 1; k++) {
                TempContent += '<option value="' + ArrayAreasRecs[k].id + '">' + ArrayAreasRecs[k].title + '</option>';
            }
            TempContent += '</select>';
            TempContent += '</div>';
            //для УО
            TempContent += '<div class="col-12">Приют:</div>';
            TempContent += '<div class="col-12">';
            TempContent += '<select name="shelter">';
            TempContent += '<option value="">Все</option>';
            for (let k = 0; k <= ArraySheltersRecs.length - 1; k++) {
                TempContent += '<option value="' + ArraySheltersRecs[k].id + '">' + ArraySheltersRecs[k].title + '</option>';
            }
            TempContent += '</select>';
            TempContent += '</div>';
        }

        if (currentLevel > 0 && currentLevel <= 1) {
            TempContent += '<div class="col-12">Приют:</div>';
            TempContent += '<div class="col-12">';
            TempContent += '<select name="shelter">';
            TempContent += '<option value="">Все</option>';
            for (let k = 0; k <= ArraySheltersRecs.length - 1; k++) {
                TempContent += '<option value="' + ArraySheltersRecs[k].id + '">' + ArraySheltersRecs[k].title + '</option>';
            }
            TempContent += '</select>';
            TempContent += '</div>';
        }

        TempContent += '<div class="col-12 required">Период:</div>';
        TempContent += '<div class="col-12">';
        TempContent += '<input type="date" name="date_from" id="date_from" autocomplete="off" style="width: 150px;" value="" min="2018-01-01" max="2050-01-01">';
        TempContent += '&nbsp;—&nbsp;';
        TempContent += '<input type="date" name="date_to" id="date_to" autocomplete="off" style="width: 150px;" value="" min="2018-01-01" max="2050-01-01">';
        TempContent += '</div>';

        TempContent += '<div class="col-12" style="margin-top: 10px;">ФИО Начальника отдела:</div>';
        TempContent += '<div class="col-12" style="margin-top: 10px;">';
        if (ArraySpecs && ArraySpecs.length > 0) {
            TempContent += '<select name="fio_chif" id="fio_chif">';
            TempContent += '<option value="">Не выбран</option>';
            if (ArraySpecs.length > 0) {
                for (let i = 0; i <= ArraySpecs.length - 1; i++) {
                    TempContent += '<option value="' + ArraySpecs[i].id + '">' + ArraySpecs[i].name + '</option>';
                }
            }
            TempContent += '</select>';
            TempContent += '</div>';
        } else {
            TempContent += '<div class="input"><input type="text" name="fio_chif" class="fio_chif" value="" autocomplete="off"></div>';
            TempContent += '</div>';
        }
    }
    if (Id == 'shelters_report') {
        // TempContent+='<div class="col-12 required">По состоянию на:</div>';
        // TempContent+='<div class="col-12">';
        // TempContent+='<input type="date" name="date_from" id="date_from" autocomplete="off" style="width: 150px;" value="" min="2018-01-01" max="2050-01-01">';
        // TempContent+='&nbsp;—&nbsp;';
        // TempContent+='<input type="date" name="date_to" id="date_to" autocomplete="off" style="width: 150px;" value="" min="2018-01-01" max="2050-01-01">';
        // TempContent+='</div>';
        if (currentLevel >= 2) {
            //для ДЖКХ
            TempContent += '<div class="col-12">Округ:</div>';
            TempContent += '<div class="col-12">';
            TempContent += '<select name="area">';
            TempContent += '<option value="">Все</option>';
            for (let k = 0; k <= ArrayAreasRecs.length - 1; k++) {
                TempContent += '<option value="' + ArrayAreasRecs[k].id + '">' + ArrayAreasRecs[k].title + '</option>';
            }
            TempContent += '</select>';
            TempContent += '</div>';
            //для УО
            TempContent += '<div class="col-12">Приют:</div>';
            TempContent += '<div class="col-12">';
            TempContent += '<select name="shelter">';
            TempContent += '<option value="">Все</option>';
            for (let k = 0; k <= ArraySheltersRecs.length - 1; k++) {
                TempContent += '<option value="' + ArraySheltersRecs[k].id + '">' + ArraySheltersRecs[k].title + '</option>';
            }
            TempContent += '</select>';
            TempContent += '</div>';
        }

        if (currentLevel > 0 && currentLevel <= 1) {
            TempContent += '<div class="col-12">Приют:</div>';
            TempContent += '<div class="col-12">';
            TempContent += '<select name="shelter">';
            TempContent += '<option value="">Все</option>';
            for (let k = 0; k <= ArraySheltersRecs.length - 1; k++) {
                TempContent += '<option value="' + ArraySheltersRecs[k].id + '">' + ArraySheltersRecs[k].title + '</option>';
            }
            TempContent += '</select>';
            TempContent += '</div>';
        }

        TempContent += '<div class="col-12">Статус:</div>';
        TempContent += '<div class="col-12">';
        TempContent += '<select class="status" name="status">';
        TempContent += '<option value="">Все</option>';
        TempContent += '<option value="IN_SHELTER">В приюте</option>';
        TempContent += '<option value="QUARANTINE">Карантин</option>';
        TempContent += '<option value="QUARANTINE_OTHER">Карантин (продлён)</option>';
        TempContent += '<option value="DEPARTURED">Выбыло</option>';
        TempContent += '<option value="IN_ISOLATION">В изоляторе</option>';
        TempContent += '<option value="IN_HOSPITAL">В стационаре</option>';
        TempContent += '</select>';
        TempContent += '</div>';

        TempContent += '<div class="col-12">Вид:</div>';
        TempContent += '<div class="col-12">';
        TempContent += '<select class="status" name="species">';
        TempContent += '<option value="">Все</option>';
        TempContent += '<option value="25">Собаки</option>';
        TempContent += '<option value="9">Кошки</option>';
        TempContent += '</select>';
        TempContent += '</div>';

        TempContent += '<div class="col-12">Дата поступления:</div>';
        TempContent += '<div class="col-12">';
        TempContent += '<input type="date" name="arrival_date_from" id="date_from" autocomplete="off" style="width: 150px;" value="" min="2018-01-01" max="2050-01-01">';
        TempContent += '&nbsp;—&nbsp;';
        TempContent += '<input type="date" name="arrival_date_to" id="date_to" autocomplete="off" style="width: 150px;" value="" min="2018-01-01" max="2050-01-01">';
        TempContent += '</div>';

        TempContent += '<div class="col-12">Дата выбытия:</div>';
        TempContent += '<div class="col-12">';
        TempContent += '<input type="date" name="departure_date_from" id="date_from" autocomplete="off" style="width: 150px;" value="" min="2018-01-01" max="2050-01-01">';
        TempContent += '&nbsp;—&nbsp;';
        TempContent += '<input type="date" name="departure_date_to" id="date_to" autocomplete="off" style="width: 150px;" value="" min="2018-01-01" max="2050-01-01">';
        TempContent += '</div>';

        TempContent += '<div class="col-12">Причина выбытия:</div>';
        TempContent += '<div class="col-12">';
        TempContent += '<select class="status" name="departure_reason">';
        TempContent += '<option value="">Все</option>';
        TempContent += '<option value="RETURNED_TO_NEW_OWNER">Передача новому владельцу</option>';
        TempContent += '<option value="RETURNED_TO_OWNER">Возврат прежнему владельцу</option>';
        TempContent += '<option value="DEATH">Смерть</option>';
        TempContent += '<option value="ESCAPE">Побег</option>';
        TempContent += '</select>';
        TempContent += '</div>';

        TempContent += '<div class="col-12">Вакцинация:</div>';
        TempContent += '<div class="col-12">';
        TempContent += '<select class="status" name="vac">';
        TempContent += '<option value="">Все</option>';
        TempContent += '<option value="1">Есть</option>';
        TempContent += '<option value="2">Нет</option>';
        TempContent += '<option value="3">Истекает в течение 30 дней</option>';
        TempContent += '</select>';
        TempContent += '</div>';

        TempContent += '<div class="col-12">Социализация:</div>';
        TempContent += '<div class="col-12">';

        TempContent += '<select class="status" name="socialized">';
        TempContent += '<option value="">Все</option>';
        TempContent += '<option value="1">Да</option>';
        TempContent += '<option value="0">Нет</option>';
        TempContent += '</select>';

        TempContent += '</div>';
    }

    if (Id == 'aviaries_report') {
        if (currentLevel > 0) {
            TempContent += '<div class="col-12 required">Приют:</div>';
            TempContent += '<div class="col-12">';
            TempContent += '<select name="shelter">';
            for (let k = 0; k <= ArraySheltersRecs.length - 1; k++) {
                TempContent += '<option value="' + ArraySheltersRecs[k].id + '">' + ArraySheltersRecs[k].title + '</option>';
            }
            TempContent += '</select>';
            TempContent += '</div>';
        } else {
            TempContent += '<div class="col-12 required">Приют:</div>';
            TempContent += '<div class="col-12">';
            TempContent += '<strong>' + currentShelterTitle + '</strong>';
            TempContent += '</div>';
        }
    }

    if (Id == 'euthanasia_report') {
        if (currentLevel > 0) {
            TempContent += '<div class="col-12 required">Приют:</div>';
            TempContent += '<div class="col-12">';
            TempContent += '<select name="shelter">';
            for (let k = 0; k <= ArraySheltersRecs.length - 1; k++) {
                TempContent += '<option value="' + ArraySheltersRecs[k].id + '">' + ArraySheltersRecs[k].title + '</option>';
            }
            TempContent += '</select>';
            TempContent += '</div>';
        } else {
            TempContent += '<div class="col-12 required">Приют:</div>';
            TempContent += '<div class="col-12">';
            TempContent += '<strong>' + currentShelterTitle + '</strong>';
            TempContent += '</div>';
        }

        TempContent += '<div class="col-12 required">Период:</div>';
        TempContent += '<div class="col-12">';
        TempContent += '<input type="date" name="date_from" id="date_from" autocomplete="off" style="width: 150px;" value="" min="2018-01-01" max="2050-01-01">';
        TempContent += '&nbsp;—&nbsp;';
        TempContent += '<input type="date" name="date_to" id="date_to" autocomplete="off" style="width: 150px;" value="" min="2018-01-01" max="2050-01-01">';
        TempContent += '</div>';

    }
    if (Id == 'dvigenie_beznadzor') {
        TempContent += '<div class="col-12 required">По состоянию на:</div>';
        TempContent += '<div class="col-12">';
        TempContent += '<input type="month" name="date_to" id="date_to" autocomplete="off" style="width: 150px;" value="" min="2000-01" max="2050-12">';
        TempContent += '</div>';
    }
    if (Id == 'mosvet_death_info') {
    }
    if (Id == 'new_owner_info') {
        TempContent += '<div class="col-12">По состоянию на:</div>';
        TempContent += '<div class="col-12" style="margin-top: 10px;">';
        TempContent += '<input type="date" name="date_to" id="date_to" autocomplete="off" style="width: 150px;" value="" min="2000-01-01" max="2050-01-01">';
        TempContent += '</div>';
    }
    if (Id == 'out_death_signature') {

        const ArrayDeathReasons = deathReasons;
        ArrayDeathReasons.unshift({id: '', description: ''})

        if (currentLevel > 0) {
            TempContent += '<div class="col-12 required" >Приют:</div>';
            TempContent += '<div class="col-12" style="margin-top: 10px;">';
            TempContent += '<select name="shelter">';
            for (let k = 0; k <= ArraySheltersRecs.length - 1; k++) {
                TempContent += '<option value="' + ArraySheltersRecs[k].id + '">' + ArraySheltersRecs[k].title + '</option>';
            }
            TempContent += '</select>';
            TempContent += '</div>';
        } else {
            TempContent += '<div class="col-12 required">Приют:</div>';
            TempContent += '<div class="col-12" style="margin-top: 10px;">';
            TempContent += '<strong>' + currentShelterTitle + '</strong>';
            TempContent += '</div>';
        }
        TempContent += '<div class="col-12">Дата отчета, с:</div>';
        TempContent += '<div class="col-12" style="margin-top: 10px;">';
        TempContent += '<input type="date" name="date_from" id="date_from" autocomplete="off" style="width: 150px;" value="" min="2000-01-01" max="2050-01-01">';
        TempContent += '</div>';

        TempContent += '<div class="col-12" style="margin-top: 10px;">Причина смерти:</div>';
        TempContent += '<div class="col-12" style="margin-top: 10px;">';
        TempContent += '<select name="death_reason">';

        for (let k = 0; k <= ArrayDeathReasons.length - 1; k++) {

            TempContent += '<option value="' + ArrayDeathReasons[k].id + '">' + ArrayDeathReasons[k].description + '</option>';
        }
        TempContent += '</select>';
        TempContent += '</div>';
    }

    if (Id == 'mosvet_week_report') {
    }

    if (Id == 'catch_in_shelter_week_info') {
        TempContent += '<div class="col-12"> По состоянию на:</div>';
        TempContent += '<div class="col-12" style="margin-top: 10px;">';
        TempContent += '<input type="date" name="date_from" id="date_from" autocomplete="off" style="width: 150px;" value="" min="2018-01-01" max="2050-01-01">';
        TempContent += '&nbsp;—&nbsp;';
        TempContent += '<input type="date" name="date_to" id="date_to" autocomplete="off" style="width: 150px;" value="" min="2018-01-01" max="2050-01-01">';
        TempContent += '</div>';
    }
    if (Id == 'castrated_info') {
        TempContent += '<div class="col-12"> По состоянию на:</div>';
        TempContent += '<div class="col-12" style="margin-top: 10px;">';
        TempContent += '<input type="date" name="date_from" id="date_from" autocomplete="off" style="width: 150px;" value="" min="2018-01-01" max="2050-01-01">';
        TempContent += '&nbsp;—&nbsp;';
        TempContent += '<input type="date" name="date_to" id="date_to" autocomplete="off" style="width: 150px;" value="" min="2018-01-01" max="2050-01-01">';
        TempContent += '</div>';

        TempContent += '<div class="col-12" style="margin-top: 10px;">ФИО Начальника отдела:</div>';
        TempContent += '<div class="col-12" style="margin-top: 10px;">';
        if (ArraySpecs && ArraySpecs.length > 0) {
            TempContent += '<select name="fio_chif" id="fio_chif">';
            TempContent += '<option value="">Не выбран</option>';
            if (ArraySpecs.length > 0) {
                for (let i = 0; i <= ArraySpecs.length - 1; i++) {
                    TempContent += '<option value="' + ArraySpecs[i].id + '">' + ArraySpecs[i].name + '</option>';
                }
            }
            TempContent += '</select>';
            TempContent += '</div>';
        } else {
            TempContent += '<div class="input"><input type="text" name="fio_chif" class="fio_chif" value="" autocomplete="off"></div>';
            TempContent += '</div>';
        }
    }
    if (Id == 'reestr_count_animals') {
        TempContent += '<div class="col-12">По состоянию на:</div>';
        TempContent += '<div class="col-12" style="margin-top: 10px;">';
        TempContent += '<input type="date" name="date_from" id="date_from" autocomplete="off" style="width: 150px;" value="" min="2018-01-01" max="2050-01-01">';
        TempContent += '&nbsp;—&nbsp;';
        TempContent += '<input type="date" name="date_to" id="date_to" autocomplete="off" style="width: 150px;" value="" min="2018-01-01" max="2050-01-01">';
        TempContent += '</div>';
    }
    if ((Id == 'puppy_to_dog') || (Id == 'kitty_to_cat')) {
        if (currentLevel > 0) {
            TempContent += '<div class="col-12 required">Приют:</div>';
            TempContent += '<div class="col-12">';
            TempContent += '<select name="shelter">';
            for (let k = 0; k <= ArraySheltersRecs.length - 1; k++) {
                TempContent += '<option value="' + ArraySheltersRecs[k].id + '">' + ArraySheltersRecs[k].title + '</option>';
            }
            TempContent += '</select>';
            TempContent += '</div>';
        } else {
            TempContent += '<div class="col-12 required">Приют:</div>';
            TempContent += '<div class="col-12">';
            TempContent += '<strong>' + currentShelterTitle + '</strong>';
            TempContent += '</div>';
        }
        TempContent += '<div class="col-12" style="margin-top: 10px;">По состоянию на:</div>';
        TempContent += '<div class="col-12" style="margin-top: 10px;">';
        TempContent += '<input type="date" name="date_from" id="date_from" autocomplete="off" style="width: 150px;" value="" min="2018-01-01" max="2050-01-01">';
        TempContent += '&nbsp;—&nbsp;';
        TempContent += '<input type="date" name="date_to" id="date_to" autocomplete="off" style="width: 150px;" value="" min="2018-01-01" max="2050-01-01">';
        TempContent += '</div>';

        TempContent += '<div class="col-12" style="margin-top: 10px;">ФИО Начальника отдела:</div>';
        TempContent += '<div class="col-12" style="margin-top: 10px;">';
        if (ArraySpecs && ArraySpecs.length > 0) {
            TempContent += '<select name="fio_chif" id="fio_chif">';
            TempContent += '<option value="">Не выбран</option>';
            if (ArraySpecs.length > 0) {
                for (let i = 0; i <= ArraySpecs.length - 1; i++) {
                    TempContent += '<option value="' + ArraySpecs[i].id + '">' + ArraySpecs[i].name + '</option>';
                }
            }
            TempContent += '</select>';
            TempContent += '</div>';
        } else {
            TempContent += '<div class="input"><input type="text" name="fio_chif" class="fio_chif" value="" autocomplete="off"></div>';
            TempContent += '</div>';
        }

        TempContent += '<div class="col-12" style="margin-top: 10px;">ФИО Начальника отдела №2:</div>';
        TempContent += '<div class="col-12" style="margin-top: 10px;">';
        if (ArraySpecs && ArraySpecs.length > 0) {
            TempContent += '<select name="fio_chif2" id="fio_chif2">';
            TempContent += '<option value="">Не выбран</option>';
            if (ArraySpecs.length > 0) {
                for (let i = 0; i <= ArraySpecs.length - 1; i++) {
                    TempContent += '<option value="' + ArraySpecs[i].id + '">' + ArraySpecs[i].name + '</option>';
                }
            }
            TempContent += '</select>';
            TempContent += '</div>';
        } else {
            TempContent += '<div class="input"><input type="text" name="fio_chif2" class="fio_chif2" value="" autocomplete="off"></div>';
            TempContent += '</div>';
        }
        TempContent += '<div class="col-12" style="margin-top: 10px;">ФИО Ветеринарного врача:</div>';
        TempContent += '<div class="col-12" style="margin-top: 10px;">';
        if (ArraySpecs && ArraySpecs.length > 0) {
            TempContent += '<select name="fio_specialist" id="fio_specialist">';
            TempContent += '<option value="">Не выбран</option>';
            if (ArraySpecs.length > 0) {
                for (let i = 0; i <= ArraySpecs.length - 1; i++) {
                    TempContent += '<option value="' + ArraySpecs[i].id + '">' + ArraySpecs[i].name + '</option>';
                }
            }
            TempContent += '</select>';
            TempContent += '</div>';
        } else {
            TempContent += '<div class="input"><input type="text" name="fio_specialist" class="fio_specialist" value="" autocomplete="off"></div>';
            TempContent += '</div>';
        }
    }
    if (Id == 'fauna_monitoring_report') {
        //
        // TempContent += '<div class="col-12 required">Период:</div>';
        // TempContent += '<div class="col-12">';
        // TempContent += '<input type="date" name="date_from" id="date_from" autocomplete="off" style="width: 150px;" value="" min="2018-01-01" max="2050-01-01">';
        // TempContent += '&nbsp;—&nbsp;';
        // TempContent += '<input type="date" name="date_to" id="date_to" autocomplete="off" style="width: 150px;" value="" min="2018-01-01" max="2050-01-01">';
        // TempContent += '</div>';
    }
    if (Id == 'act_beshenstvo') {
        if (currentLevel > 0) {
            TempContent += '<div class="col-12 required">Приют:</div>';
            TempContent += '<div class="col-12">';
            TempContent += '<select name="org1" id="org1" name="shelter">';
            for (let k = 0; k <= ArraySheltersRecs.length - 1; k++) {
                TempContent += '<option name="org2" id="org2" value="' + ArraySheltersRecs[k].id + '">' + ArraySheltersRecs[k].title + '</option>';
            }
            TempContent += '</select>';
            TempContent += '</div>';
        } else {
            TempContent += '<div class="col-12 required">Приют:</div>';
            TempContent += '<div  name="org3" id="org3" class="col-12">';
            TempContent += '<strong name="org4" id="org4" >' + currentShelterTitle + '</strong>';
            TempContent += '</div>';
        }

        TempContent += '<div class="col-12" style="margin-top: 10px;">Дата вакцинации:</div>';
        TempContent += '<div class="col-12" style="margin-top: 10px;">';
        TempContent += '<input type="date" name="date_from" id="date_from" autocomplete="off" style="width: 150px;" value="" min="2018-01-01" max="2050-01-01">';
        TempContent += '&nbsp;—&nbsp;';
        TempContent += '<input type="date" name="date_to" id="date_to" autocomplete="off" style="width: 150px;" value="" min="2018-01-01" max="2050-01-01">';
        TempContent += '</div>';

        TempContent += '<div class="col-9 row-center-align" style="margin-top: 10px;">ФИО ветеринарного врача №1:</div>';
        TempContent += '<div class="col-9" style="margin-top: 10px;">';
        if (ArraySpecs && ArraySpecs.length > 0) {
            TempContent += '<select name="vac_specialist" id="vac_specialist">';
            TempContent += '<option value="">Не выбран</option>';
            if (ArraySpecs.length > 0) {
                for (let i = 0; i <= ArraySpecs.length - 1; i++) {
                    TempContent += '<option value="' + ArraySpecs[i].id + '">' + ArraySpecs[i].name + '</option>';
                }
            }
            TempContent += '</select>';
            TempContent += '</div>';
        } else {
            TempContent += '<div class="input"><input type="text" name="vac_specialist" class="vac_specialist" value="" autocomplete="off"></div>';
            TempContent += '</div>';
        }

        TempContent += '<div class="col-9 row-center-align" style="margin-top: 10px;">ФИО ветеринарного врача №2:</div>';
        TempContent += '<div class="col-9" style="margin-top: 10px;">';
        if (ArraySpecs && ArraySpecs.length > 0) {
            TempContent += '<select name="vac_specialist2" id="vac_specialist2">';
            TempContent += '<option value="">Не выбран</option>';
            if (ArraySpecs.length > 0) {
                for (let i = 0; i <= ArraySpecs.length - 1; i++) {
                    TempContent += '<option value="' + ArraySpecs[i].id + '">' + ArraySpecs[i].name + '</option>';
                }
            }
            TempContent += '</select>';
            TempContent += '</div>';
        } else {
            TempContent += '<div class="input"><input type="text" name="vac_specialist2" class="vac_specialist2" value="" autocomplete="off"></div>';
            TempContent += '</div>';
        }
    }
    if (Id == 'act_degelmintizacii') {
        if (currentLevel > 0) {
            TempContent += '<div class="col-12 required">Приют:</div>';
            TempContent += '<div class="col-12">';
            TempContent += '<select name="org1" id="org1" name="shelter">';
            for (let k = 0; k <= ArraySheltersRecs.length - 1; k++) {
                TempContent += '<option name="org2" id="org2" value="' + ArraySheltersRecs[k].id + '">' + ArraySheltersRecs[k].title + '</option>';
            }
            TempContent += '</select>';
            TempContent += '</div>';
        } else {
            TempContent += '<div class="col-12 required">Приют:</div>';
            TempContent += '<div  name="org3" id="org3" class="col-12">';
            TempContent += '<strong name="org4" id="org4" >' + currentShelterTitle + '</strong>';
            TempContent += '</div>';
        }
        TempContent += '<div class="col-12" style="margin-top: 10px;">Дата дегельминтизации:</div>';
        TempContent += '<div class="col-12" style="margin-top: 10px;">';
        TempContent += '<input type="date" name="date_from" id="date_from" autocomplete="off" style="width: 150px;" value="" min="2018-01-01" max="2050-01-01">';
        TempContent += '&nbsp;—&nbsp;';
        TempContent += '<input type="date" name="date_to" id="date_to" autocomplete="off" style="width: 150px;" value="" min="2018-01-01" max="2050-01-01">';
        TempContent += '</div>';

        TempContent += '<div class="col-9 row-center-align" style="margin-top: 10px;">ФИО ветеринарного врача №1:</div>';
        TempContent += '<div class="col-9" style="margin-top: 10px;">';
        if (ArraySpecs && ArraySpecs.length > 0) {
            TempContent += '<select name="vac_specialist" id="vac_specialist">';
            TempContent += '<option value="">Не выбран</option>';
            if (ArraySpecs.length > 0) {
                for (let i = 0; i <= ArraySpecs.length - 1; i++) {
                    TempContent += '<option value="' + ArraySpecs[i].id + '">' + ArraySpecs[i].name + '</option>';
                }
            }
            TempContent += '</select>';
            TempContent += '</div>';
        } else {
            TempContent += '<div class="input"><input type="text" name="vac_specialist" class="vac_specialist" value="" autocomplete="off"></div>';
            TempContent += '</div>';
        }

        TempContent += '<div class="col-9 row-center-align" style="margin-top: 10px;">ФИО ветеринарного врача №2:</div>';
        TempContent += '<div class="col-9" style="margin-top: 10px;">';
        if (ArraySpecs && ArraySpecs.length > 0) {
            TempContent += '<select name="vac_specialist2" id="vac_specialist2">';
            TempContent += '<option value="">Не выбран</option>';
            if (ArraySpecs.length > 0) {
                for (let i = 0; i <= ArraySpecs.length - 1; i++) {
                    TempContent += '<option value="' + ArraySpecs[i].id + '">' + ArraySpecs[i].name + '</option>';
                }
            }
            TempContent += '</select>';
            TempContent += '</div>';
        } else {
            TempContent += '<div class="input"><input type="text" name="vac_specialist2" class="vac_specialist2" value="" autocomplete="off"></div>';
            TempContent += '</div>';
        }
    }
    if (Id == 'act_ektoparazit') {
        if (currentLevel > 0) {
            TempContent += '<div class="col-12 required">Приют:</div>';
            TempContent += '<div class="col-12">';
            TempContent += '<select name="org1" id="org1" name="shelter">';
            for (let k = 0; k <= ArraySheltersRecs.length - 1; k++) {
                TempContent += '<option name="org2" id="org2" value="' + ArraySheltersRecs[k].id + '">' + ArraySheltersRecs[k].title + '</option>';
            }
            TempContent += '</select>';
            TempContent += '</div>';
        } else {
            TempContent += '<div class="col-12 required">Приют:</div>';
            TempContent += '<div  name="org3" id="org3" class="col-12">';
            TempContent += '<strong name="org4" id="org4" >' + currentShelterTitle + '</strong>';
            TempContent += '</div>';
        }

        TempContent += '<div class="col-12" style="margin-top: 10px;">Дата обработок от эктопаразитов:</div>';
        TempContent += '<div class="col-12" style="margin-top: 10px;">';
        TempContent += '<input type="date" name="date_from" id="date_from" autocomplete="off" style="width: 150px;" value="" min="2018-01-01" max="2050-01-01">';
        TempContent += '&nbsp;—&nbsp;';
        TempContent += '<input type="date" name="date_to" id="date_to" autocomplete="off" style="width: 150px;" value="" min="2018-01-01" max="2050-01-01">';
        TempContent += '</div>';

        TempContent += '<div class="col-9 row-center-align" style="margin-top: 10px;">ФИО ветеринарного врача №1:</div>';
        TempContent += '<div class="col-9" style="margin-top: 10px;">';
        if (ArraySpecs && ArraySpecs.length > 0) {
            TempContent += '<select name="vac_specialist" id="vac_specialist">';
            TempContent += '<option value="">Не выбран</option>';
            if (ArraySpecs.length > 0) {
                for (let i = 0; i <= ArraySpecs.length - 1; i++) {
                    TempContent += '<option value="' + ArraySpecs[i].id + '">' + ArraySpecs[i].name + '</option>';
                }
            }
            TempContent += '</select>';
            TempContent += '</div>';
        } else {
            TempContent += '<div class="input"><input type="text" name="vac_specialist" class="vac_specialist" value="" autocomplete="off"></div>';
            TempContent += '</div>';
        }

        TempContent += '<div class="col-9 row-center-align" style="margin-top: 10px;">ФИО ветеринарного врача №2:</div>';
        TempContent += '<div class="col-9" style="margin-top: 10px;">';
        if (ArraySpecs && ArraySpecs.length > 0) {
            TempContent += '<select name="vac_specialist2" id="vac_specialist2">';
            TempContent += '<option value="">Не выбран</option>';
            if (ArraySpecs.length > 0) {
                for (let i = 0; i <= ArraySpecs.length - 1; i++) {
                    TempContent += '<option value="' + ArraySpecs[i].id + '">' + ArraySpecs[i].name + '</option>';
                }
            }
            TempContent += '</select>';
            TempContent += '</div>';
        } else {
            TempContent += '<div class="input"><input type="text" name="vac_specialist2" class="vac_specialist2" value="" autocomplete="off"></div>';
            TempContent += '</div>';
        }
    }

    TempContent += '</form>';
    TempContent += '</div>';

    //Кнопки управления
    TempContent += '<div class="controls">';
    TempContent += '<button class="appointment" id="add_brigade" style="width: 250px;" onclick="createSheltersReport(\'' + Id + '\');">Сформировать отчёт</button>';
    TempContent += '</div>';
    //Кнопки управления

    TempContent += '</div>';

    document.getElementById("sub_container").innerHTML = TempContent;
    $("#background").fadeIn();
    $("#container").show();
}

function createSheltersReport(Id) {
    if (Id == 'monitoring_report' || Id == 'euthanasia_report'
        || Id == 'mosvet_week_report' || Id == 'catch_in_shelter_week_info'
        || Id == 'dvigenie_beznadzor' || Id == 'new_owner_info'
        || Id == 'mosvet_death_info' || Id == 'out_death_signature'
        || Id == 'castrated_info' || Id == 'reestr_count_animals'
        || Id == 'puppy_to_dog' || Id == 'kitty_to_cat'
        || Id == 'act_beshenstvo' || Id == 'act_degelmintizacii'
        || Id == 'act_ektoparazit' || Id == 'fauna_monitoring_report'
    ) {
        let date_from = $("#form_shelters_report #date_from").val();
        let date_to = $("#form_shelters_report #date_to").val();

        if (date_from == '' || date_to == '') {
            $("#shelters_report_window_message").html('Не заполнены необходимые поля.');
            $("#shelters_report_window_message").show();

            return true;
        }

        let date_from_ = new Date(date_from);
        let date_to_ = new Date(date_to);

        if (date_from_ > date_to_) {
            $("#shelters_report_window_message").html('Дата начала периода не может быть больше даты его окончания.');
            $("#shelters_report_window_message").show();

            return true;
        }
    }

    jQuery.ajax({
        'async': true,
        'global': false,
        'cache': false,
        'type': 'GET',
        'xhrFields': {
            responseType: 'blob'
        },
        'data': jQuery("#form_shelters_report").serialize() + '&report=' + Id + '&action=report&mode=xml',
        'url': "index.php",
        'beforeSend': function () {
            //показать загрузку
            //showLoading('ListRecs');
        },
        'success': function (Data) {
            //closeLoading('ListRecs');

            if ($(Data).find('message').text() == 'token_invalid') {
                window.location.href = 'index.php';
            } else {
                var a = document.createElement('a');
                var url = window.URL.createObjectURL(Data);
                a.href = url;
                //a.download = 'report.xlsx';
                if (Id == 'monitoring_report') {
                    // с ' + date_from + ' по ' + date_to + '
                    a.download = 'Отчёт о мониторинге.xlsx';
                } else if (Id == 'shelters_report') {
                    a.download = 'Отчёт о животных содержащихся в приютах.xlsx';
                } else if (Id == 'aviaries_report') {
                    a.download = 'Список животных по вольерам.xlsx';
                } else if (Id == 'euthanasia_report') {
                    a.download = 'Журнал учета случаев эвтаназии.xlsx';
                } else if (Id == 'mosvet_week_report') {
                    a.download = 'Мосветобъединение недельный отчет.xlsx';
                } else if (Id == 'catch_in_shelter_week_info') {
                    a.download = 'Информация по отловленным и поступившим в приюты (собак, кошек).xlsx';
                } else if (Id == 'dvigenie_beznadzor') {
                    a.download = 'Информация по движению безнадзорных животных в приютах (собак, кошек).xlsx';
                } else if (Id == 'new_owner_info') {
                    a.download = 'Информация о новых владельцах животных.xlsx';
                } else if (Id == 'mosvet_death_info') {
                    a.download = 'Мосветобъединение информация от АО по павшим животным.xlsx';
                } else if (Id == 'out_death_signature') {
                    a.download = 'Выбытие по причине смерти с описью.xlsx';
                } else if (Id == 'castrated_info') {
                    a.download = 'Информация по стерилизации животных за  отчетный период.xlsx';
                } else if (Id == 'reestr_count_animals') {
                    a.download = 'Реестр о количестве животных в приюте.xlsx';
                } else if (Id == 'puppy_to_dog') {
                    a.download = 'Aкт перевода из щенков в собаки с описью.xlsx';
                } else if (Id == 'kitty_to_cat') {
                    a.download = 'Aкт перевода из котят в кошки с описью.xlsx';
                } else if (Id == 'fauna_monitoring_report') {
                    a.download = 'Справка по мониторингу городской фауны за отчетную неделю.xlsx';
                }
                document.body.append(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(url);

                // TempContent='<div class="p-3">Загрузка файла началась...</div>';
                // document.getElementById('ListRecs').innerHTML=TempContent;
            }
        }
    });
}

function closeSheltersReportWindow() {
    $("#background").fadeOut();
    $("#container").hide();
}

function showDepartureWindow(Id) {
    let TempContent = '';

    TempContent += '<div id="departure_window" class="window main_row col-xl-8 col-lg-10 col-12">';
    TempContent += '<div class="close" onclick="closeDepartureWindow();"></div>';
    TempContent += '<div class="top">Оформление выбытия животного</div>';


    TempContent += '<div class="w-100" id="window_departure" style="overflow-y: auto;">';
    TempContent += '<form class="form-window" id="form_departure">';
    TempContent += '<div class="col-12 main_row row">';

    TempContent += '<div class="col-12 error" id="departure_window_message" style="margin-bottom: 15px; display: none;"></div>';

    //reason
    TempContent += '<div class="col-3 required row-center-align">Причина выбытия:</div>';
    TempContent += '<div class="col-9"><div class="col-12 tabs_text">';
    if (Id == 'RETURNED_TO_NEW_OWNER') {
        TempContent += 'Передача новому владельцу';
    } else if (Id == 'RETURNED_TO_OWNER') {
        TempContent += 'Возврат прежнему владельцу';
    } else if (Id == 'DEATH') {
        TempContent += 'Смерть';
    } else if (Id == 'ESCAPE') {
        TempContent += 'Побег';
    }
    TempContent += '</div></div>';

    TempContent += '<input type="hidden" name="departure_reason" id="departure_reason" value="' + Id + '">';
    //reason

    //
    TempContent += '<div class="col-3 required row-center-align">Дата выбытия:</div>';
    TempContent += '<div class="col-9">';
    TempContent += '<input type="date" name="departure_date" id="departure_date" autocomplete="off" style="width: 150px;" value="" min="1950-01-01" max="' + get_current_date() + '">';
    TempContent += '</div>';

    if (Id == 'RETURNED_TO_NEW_OWNER' || Id == 'RETURNED_TO_OWNER') {
        TempContent += '<div class="col-3 required row-center-align">Владелец:</div>';

        TempContent += '<div class="col-9" style="margin-top: 5px;"><div class="main_row row">';

        TempContent += '<div class="col-11 row-center-align tabs_text" id="owner_title">';
        TempContent += '</div>';
        TempContent += '<input type="hidden" id="owner_id" name="owner_id" value="">';

        TempContent += '<div class="col-1 row-center-align row-justify-content">';
        TempContent += '<div class="tabs_edit" onclick="showSheltersOwnerWindow();"></div>';
        TempContent += '</div>';

        TempContent += '</div>';

        TempContent += '</div>';
    }

    if (Id == 'DEATH') {
        TempContent += `
		<div class="col-3 required row-center-align">Причина смерти:</div>
		<div class="col-9" style="margin-top: 10px;">
			<select name="reason_death" id="reason_death" onchange="changeDeathReason();">'
				<option value="DEATH">Падёж</option>
				<option value="EUTHANASIA">Эвтаназия</option>
			</select>
		</div>
		<div id="death_type" class="w-100 main_row row">
			<div class="col-3 row-center-align"></div>
			<div class="col-9" style="margin-top: 10px;">
				<select name="death_type" id="death_type">
					<option value="">Не выбран</option>
					${deathReasons.map(reason => `<option value="${reason.id}">(${reason.name}) ${reason.description}</option>`)}
				</select>
			</div>
		</div>
		<div id="euthanasia" class="w-100 main_row row" style="display: none;">
			<div class="col-3 required row-center-align">Причина эвтаназии:</div>
			<div class="col-9" style="margin-top: 10px;">
				<input type="text" name="euthanasia_reason" id="euthanasia_reason" value="" autocomplete="off">
			</div>
			<div class="col-3 required row-center-align">ФИО ветеринарного врача:</div>
			<div class="col-9" style="margin-top: 10px;">
				<select name="euthanasia_specialist" id="euthanasia_specialist">
					<option value="">Не выбран</option>
					${ArraySpecs.map(spec => `<option value="${spec.id}">${spec.name}</option>`)}
				</select>
			</div>
		</div>`
    }


    TempContent += '<div class="col-12 required" style="margin-top: 10px;">Документы:</div>';
    TempContent += '<div class="col-12">';


    TempContent += '<div class="documents w-100 main_row row">';
    TempContent += '<div class="col-2"><div class="w-100 h-100 add" onclick="showSheltersAddDocumentWindow(\'departure\');"></div></div>';
    TempContent += '<div class="documents_inner col-10 w-100" id="documents"></div>';
    TempContent += '</div>';
    TempContent += '</div>';


    TempContent += '</div>';
    TempContent += '</form>';


    //Кнопки управления
    TempContent += '<div class="controls">';
    TempContent += '<button class="button" id="departure" style="width: 220px;" onclick="saveDeparture();">Подтвердить</button>';
    TempContent += '</div>';
    //Кнопки управления

    TempContent += '</div>';

    document.getElementById("sub_container").innerHTML = TempContent;
    $("#background").fadeIn();
    $("#container").show();
    ///////

    //$("#window_departure").height($("#departure_window").height()-210);
}

function closeDepartureWindow() {
    $("#background").fadeOut();
    $("#container").hide();
}

function changeDeathReason() {
    if ($('#reason_death').val() == 'EUTHANASIA') {
        $('#euthanasia').show();
        $('#death_type').hide();
    } else {
        $('#euthanasia').hide();
        $('#death_type').show();
    }
}

function changeSheltersSocialized() {
    if ($('#socialized').prop("checked") == true) {
        //показываем навыки
        skill_input.Enable();
    } else {
        //убираем навыки
        skill_input.Disable();
    }
}

function saveDeparture() {
    if ($('#departure_window #departure_date').val() == '') {
        $('#departure_window #departure_date').addClass("error_field");
        $("#departure_window_message").html('Не заполнены необходимые поля.');
        $("#departure_window_message").show();

        return true;
    }

    if ($('#departure_window #owner_id').val() == '') {
        $("#departure_window_message").html('Необходимо выбрать владельца животного.');
        $("#departure_window_message").show();

        return true;
    }

    if (
        $('#departure_window #departure_reason').val() == 'DEATH' &&
        $('#departure_window #reason_death').val() == 'EUTHANASIA' &&
        (
            $('#departure_window #euthanasia_reason').val() == '' ||
            $('#departure_window #euthanasia_specialist').val() == ''
        )
    ) {
        $("#departure_window_message").html('Не заполнены необходимые поля.');
        if ($('#departure_window #euthanasia_reason').val() == '') {
            $('#departure_window #euthanasia_reason').addClass("error_field");
        }
        if ($('#departure_window #euthanasia_specialist').val() == '') {
            $('#departure_window #euthanasia_specialist').addClass("error_field");
        }
        $("#departure_window_message").show();

        return true;
    }

    //При оформлении обязательные документы

    jQuery.ajax({
        'async': true,
        'global': false,
        'cache': false,
        'type': 'GET',
        'dataType': 'xml',
        'data': jQuery("#form_departure").serialize() + '&pet=' + $('#pet_id').val() + '&action=departure_pet&mode=xml',
        'url': "index.php",
        'beforeSend': function () {
            //показать загрузку
            showTopLoading();
        },
        'success': function (Data) {
            if ($(Data).find('message').text() == 'token_invalid') {
                window.location.href = 'index.php';
            } else {
                if ($(Data).find('message').text() == 'pet_departured') {
                    closeTopLoading();
                    closeDepartureWindow();

                    showSheltersInformation($('#pet_id').val(), 'tab2');//показываем нужную вкладку
                    showSheltersTab(2);//показываем нужную вкладку
                }
            }
        }
    });
}

function returnPet() {
    closeAcceptWindow();

    jQuery.ajax({
        'async': true,
        'global': false,
        'cache': false,
        'type': 'GET',
        'dataType': 'xml',
        'data': 'pet=' + $('#pet_id').val() + '&action=return_pet&mode=xml',
        'url': "index.php",
        'beforeSend': function () {
            //показать загрузку
            showTopLoading();
        },
        'success': function (Data) {
            if ($(Data).find('message').text() == 'token_invalid') {
                window.location.href = 'index.php';
            } else {
                if ($(Data).find('message').text() == 'pet_returned') {
                    closeTopLoading();

                    showSheltersInformation($('#pet_id').val(), 'tab2');//показываем нужную вкладку
                    showSheltersTab(2);//показываем нужную вкладку
                }
            }
        }
    });
}


function addSheltersOwner() {
    if ($('#form_owners input[name="name"]').val() || $('#form_owners  input[name="telephone"]').val()) {
        $('#owner_new_surname').val($('#form_owners input[name="name"]').val());
        $('#owner_new_telephone').val($('#form_owners input[name="telephone"]').val());
    }

    $('.email').mask(
        "A", {
            translation: {
                "A": {pattern: /[\w@\-.+]/, recursive: true}
            }
        }
    );

    $('#window_title').html('Добавление владельца');


    $('#add_owner').hide();
    $('#make_request').hide();
    $('#choose_owner').width('360px');
    $('#choose_owner').prop('disabled', false);
    $('#choose_owner').html('Добавить и выбрать владельца');
    $('#choose_owner').show();

    // $('.next').show();

    $('#add_new').val('1');
    $('#AddOwners').show();
    $('#ListOwners').hide();
    $('#FormSearch').hide();
}

function listSheltersShowOwners() {
    $(".telephone").unmask();
    jQuery.ajax({
        'async': true,
        'global': false,
        'cache': false,
        'type': 'GET',
        'dataType': 'xml',
        'data': jQuery("#form_owners").serialize() + '&action=owners',
        'url': "index.php",
        'beforeSend': function () {
            //показать загрузку
            showLoading('ListOwners');
            // changeAmbulanceOwner();
        },
        'success': function (Recs) {
            let TempRecsNum = 0;

            if ($(Recs).find('message').text() == 'token_invalid') {
                window.location.href = 'index.php';
            } else {
                $('.telephone').mask("+7(999) 999-9999");
                let TempContent = '';

                if ($(Recs).find('rec').text()) {
                    TempContent += '<div class="row owners" id="ListInOwners">';

                    $(Recs).find('rec').each(function () {
                        let FLAG = 0;
                        let Id = $(this).find('rec_id').text();

                        TempContentRec = '<div class="owner col-12 main_row row">';
                        TempContentRec += '<div class="col-xl-5 col-lg-5 col-md-12 col-sm-12 col-xs-12 col-12">';
                        //
                        TempContentRec += '<input type="radio" onchange="changeSheltersOwner();" name="owner" id="owner_' + $(this).find('rec_id').text() + '" value="' + $(this).find('rec_id').text() + '"><label for="owner_' + $(this).find('rec_id').text() + '"><span class="name">' + $(this).find('rec_name').text() + '</span></label>';
                        if ($(this).find('rec_birthday').text()) {
                            TempContentRec += '<br><span class="birthday" title="День рождения">' + $(this).find('rec_birthday').text() + '</span>';
                        }
                        if ($(this).find('rec_snils').text()) {
                            TempContentRec += '<br><span class="snils" title="СНИЛС">' + $(this).find('rec_snils').text() + '</span>';
                        }
                        TempContentRec += '</div>';

                        TempContentRec += '<div class="col-xl-3 col-lg-3 col-md-12 col-sm-12 col-xs-12 col-12 contacts">';
                        if ($(this).find('rec_contacts').text()) {
                            $(this).find('rec_contacts').find('contact').each(function () {
                                TempContentRec += '<div title="' + $(this).find('type_title').text() + '"';
                                if ($(this).find('type_id').text() == 1) {
                                    TempContentRec += ' class="mobiletelephone"';
                                } else if ($(this).find('type_id').text() == 6) {
                                    TempContentRec += ' class="mail"';
                                }
                                TempContentRec += '>';
                                TempContentRec += '' + $(this).find('name').text() + '</div>';
                            });
                        } else {
                            TempContentRec += '-';
                        }
                        TempContentRec += '<div class="edit" title="Добавить/изменить контакт" onclick="editAmbulanceOwnerContacts(' + $(this).find('rec_id').text() + ');	"></div>';
                        TempContentRec += '</div>';

                        TempContentRec += '<div class="col-xl-4 col-lg-4 col-md-12 col-sm-12 col-xs-12 col-12 addresses">';
                        if ($(this).find('rec_address').text()) {
                            TempContentRec += '<p class="address">' + $(this).find('rec_address').text() + '</p>';
                        } else {
                            TempContentRec += '-';
                        }
                        TempContentRec += '<input type="hidden" id="owner_address_' + $(this).find('rec_id').text() + '" value="' + $(this).find('rec_address').text() + '">';
                        TempContentRec += '</div>';

                        TempContentRec += '</div>';


                        if (FLAG == 0) {
                            TempRecsNum++;
                            TempContent += TempContentRec;
                        }
                    });

                    TempContent += '</div>';
                }

                if ($(Recs).find('message').text() == 'service_not_selected') {

                } else {
                    if (TempRecsNum == 0) {
                        TempContent = '<div class="message">По данному запросу не найдено записей.</div>';
                    }
                }
                $('.add').show();
                document.getElementById('ListOwners').innerHTML = TempContent;
                $('.species').multiselect({
                    buttonWidth: '100%',
                    maxHeight: 300,
                    enableFiltering: true,
                    enableCaseInsensitiveFiltering: true,
                    filterPlaceholder: 'Выбрать вид животного...'
                });
            }

            if (TempRecsNum < 2) {
                $("#ListInOwners").height($("#shelters_owners_window").height() - 200);
            } else {
                $("#ListInOwners").height($("#shelters_owners_window").height() - 300);
            }
        }
    });


}

function showSheltersOwnerWindow() {
    //запрашиваем список владельцев

    let TempContent = '';

    TempContent += '<div id="shelters_owners_window" class="window main_row col-xl-8 col-lg-10 col-12">';
    TempContent += '<div class="close" onclick="closeSheltersOwnerWindow();"></div>';
    TempContent += '<div class="top" id="window_title">Поиск владельца</div>';

    TempContent += '<div id="FormSearch" class="search">';
    TempContent += '<form id="form_owners" onsubmit="listSheltersShowOwners(); return false;">';
    TempContent += '<div class="d-inline-flex"><div class="label">ФИО или наименование владельца:&nbsp;</div><div class="input"><input type="text" name="name" class="name_" value="" autocomplete="off"></div></div>';
    TempContent += '<div class="d-inline-flex"><div class="label">Телефон:&nbsp;</div><div class="input"><input type="text" name="telephone" value="" autocomplete="off" class="telephone"></div></div>';
    TempContent += '<div class="d-inline-flex"><button type="submit">Поиск</button></div>';
    TempContent += '</form>';
    TempContent += '</div>';

    TempContent += '<div class="error" id="request_window_message" style="display: none;"></div>';
    ///
    TempContent += '<div id="AddRequest" class="add_form col-xl-12 col-lg-12" style="display: none;">';
    TempContent += '<form id="form_add_request">';

    TempContent += '<div class="w-100 d-table">';

    //Расписание бригад
    TempContent += '<div class="d-table-row title required">Расписание</div>';

    //////////////
    TempContent += '<div class="schedule">';

    TempContent += '<input type="hidden" id="id_brigade" value=""></input>';
    TempContent += '<input type="hidden" id="date_request" value=""></input>';

    TempContent += '<div class="title">';

    TempContent += '<div class="date-time title"></div>';

    let today = new Date();
    for (let j = 1; j <= 14; j++) {
        let current_date = today.getDate();
        if (current_date < 10) {
            current_date = '0' + current_date;
        }
        let current_month = today.getMonth();
        current_month++;
        if (current_month < 10) {
            current_month = '0' + current_month;
        }
        TempContent += '<div class="date-time">' + current_date + '.' + current_month + '<br />' + ArrayDays[today.getDay()] + '</div>';

        today.setDate(today.getDate() + 1);
    }
    TempContent += '</div>';


    /////////////////

    //Причина вызова + время вызова
    TempContent += '<div class="d-table-row title">Вызов</div>';

    TempContent += '<div class="d-table-row">';
    TempContent += '<div class="d-table-cell required">Причина вызова:</div>';

    TempContent += '<div class="d-table-cell">';
    TempContent += '<select class="reasons" id="call_reason">';

    let ArrayServiceTypesRecs_ = new Array();
    ArrayServiceTypesRecs_[0] = 'Терапия';
    ArrayServiceTypesRecs_[1] = 'Хирургия';
    ArrayServiceTypesRecs_[2] = 'Вакцинация';
    ArrayServiceTypesRecs_[3] = 'Чипирование';
    ArrayServiceTypesRecs_[4] = 'Обрезка когтей';
    ArrayServiceTypesRecs_[5] = 'Стрижка';
    ArrayServiceTypesRecs_[6] = 'Клинико-диагностические исследования';
    ArrayServiceTypesRecs_[7] = 'Лабораторные исследования';
    ArrayServiceTypesRecs_[8] = 'Стоматология';
    ArrayServiceTypesRecs_[9] = 'Оформление ветеринарных сопроводительных документов';
    ArrayServiceTypesRecs_[10] = 'Эвтаназия животных';

    for (let k = 0; k <= ArrayServiceTypesRecs_.length - 1; k++) {
        TempContent += '<option value="' + ArrayServiceTypesRecs_[k] + '">' + ArrayServiceTypesRecs_[k] + '</option>';
    }
    TempContent += '</select>';
    TempContent += '</div>';

    TempContent += '</div>';

    TempContent += '<div class="d-table-row">';
    TempContent += '<div class="d-table-cell required" style="padding-right: 5px;">Плановое начало приема:</div>';
    TempContent += '<div class="d-table-cell"><input type="time" id="time_request" class="" value="" autocomplete="off" step="600"></div>';
    TempContent += '</div>';

    //Адрес владельца или выбор вручную
    TempContent += '<div class="d-table-row title required">Адрес</div>';

    TempContent += '<div class="d-table-row">';
    TempContent += '<div class="d-table-cell"><input type="radio" id="address_1" name="address_" class="" value="1" checked> <label for="address_1">Адрес владельца:</label></div>';
    TempContent += '<div class="d-table-cell" id="owner_address_title"></div>';
    TempContent += '<input type="hidden" id="address1" value="">';
    TempContent += '</div>';

    TempContent += '<div class="d-table-row">';
    TempContent += '<div class="d-table-cell"><input type="radio" id="address_2" name="address_" class="" value="2"> <label for="address_2">Выбор адреса:</label></div>';
    TempContent += '<div class="d-table-cell">';

    TempContent += '<textarea id="owner_address_request" name="owner_address_request" class="JxTag"></textarea>';

    TempContent += '</div>';
    TempContent += '</div>';

    TempContent += '<div class="d-table-row">';
    TempContent += '<div class="d-table-cell">Примечание:</div>';
    TempContent += '<div class="d-table-cell"><textarea id="description" rows="3"></textarea></div>';
    TempContent += '</div>';

    TempContent += '<div class="d-table-row title">Льготы</div>';

    TempContent += '<div class="d-table">';
    TempContent += '<input type="checkbox" id="is_veteran" class="" value="1">';
    TempContent += ' <label for="is_veteran">Ветеран ВОВ</label>';
    TempContent += '</div>';

    TempContent += '<div class="d-table">';
    TempContent += '<input type="checkbox" id="is_disabled" class="" value="1">';
    TempContent += ' <label for="is_disabled">Инвалид 1 группы</label>';
    TempContent += '</div>';

    TempContent += '<div class="d-table">';
    TempContent += '<input type="checkbox" id="is_blind" class="" value="1">';
    TempContent += ' <label for="is_blind">Слабовидящий с животным-поводырём</label>';
    TempContent += '</div>';

    TempContent += '<div class="d-table">';
    TempContent += '<input type="checkbox" id="is_orphan" class="" value="1">';
    TempContent += ' <label for="is_orphan">Дети-сироты, дети, оставшиеся без попечения родителей в возрасте до 23 лет</label>';
    TempContent += '</div>';

    TempContent += '<div class="d-table">';
    TempContent += '<input type="checkbox" id="is_large_family" class="" value="1">';
    TempContent += ' <label for="is_large_family">Многодетные семьи</label>';
    TempContent += '</div>';

    TempContent += '<div class="d-table">';
    TempContent += '<input type="checkbox" id="is_veteran_of_labour" class="" value="1">';
    TempContent += ' <label for="is_veteran_of_labour">Ветераны труда</label>';
    TempContent += '</div>';

    TempContent += '</div>';

    TempContent += '</form>';
    TempContent += '</div>';
    TempContent += '</div>';
    ///

    //
    TempContent += '<div id="AddOwners" class="add_form col-xl-12 col-lg-12" style="display: none;">';
    TempContent += '<form id="form_add_owners">';
    TempContent += '<input type="hidden" id="add_new" value=""></input>';

    TempContent += '<div class="w-50 d-table">';

    TempContent += '<div class="d-table-row title">Владелец</div>';

    TempContent += '<div class="d-table-row">';
    TempContent += '<div class="d-table-cell w-50 required">Фамилия:</div>';
    TempContent += '<div class="d-table-cell w-50 "><input type="text" id="owner_new_surname" class="name_" value="" autocomplete="off"></div>';
    TempContent += '</div>';

    TempContent += '<div class="d-table-row">';
    TempContent += '<div class="d-table-cell required">Имя:</div>';
    TempContent += '<div class="d-table-cell"><input type="text" id="owner_new_name" class="name_" value="" autocomplete="off"></div>';
    TempContent += '</div>';

    TempContent += '<div class="d-table-row">';
    TempContent += '<div class="d-table-cell">Отчество:</div>';
    TempContent += '<div class="d-table-cell"><input type="text" id="owner_new_secondname" class="name_" value="" autocomplete="off"></div>';
    TempContent += '</div>';

    TempContent += '<div class="d-table-row">';
    TempContent += '<div class="d-table-cell required">Телефон:</div>';
    TempContent += '<div class="d-table-cell"><input type="text" id="owner_new_telephone" class="telephone" value="" autocomplete="off"></div>';
    TempContent += '</div>';

    TempContent += '<div class="d-table-row">';
    TempContent += '<div class="d-table-cell required">Адрес:</div>';
    TempContent += '<div class="d-table-cell"><input type="text" id="owner_new_address" class="address" value="" autocomplete="off"></div>';
    TempContent += '</div>';

    TempContent += '</form>';
    TempContent += '</div>';

    TempContent += '</div>';

    //Список в окне
    TempContent += '<div class="results" id="ListOwners">';
    TempContent += '<div class="message">Для поиска владельца введите параметры поиска.</div>';
    TempContent += '</div>';
    //Список в окне

    //Кнопки управления
    TempContent += '<div class="controls">';
    TempContent += '<button class="add" id="add_owner" style="width: 220px; display: none;" onclick="addSheltersOwner();">Добавить владельца</button>';
    TempContent += '<button class="next" id="choose_owner" style="width: 220px; display: none;" onclick="chooseSheltersOwner();">Выбрать владельца</button>';
    TempContent += '</div>';
    //Кнопки управления

    TempContent += '</div>';

    document.getElementById("sub_container_t").innerHTML = TempContent;
    $("#background_t").fadeIn();
    $("#container_t").show();
    // changeAmbulanceOwner();
    $('.name_').mask("R", {
        translation: {
            "R": {pattern: /[А-Яа-яё\s+]/, recursive: true}
        }
    });
    $('.telephone').mask("+7(999) 999-9999");
    $('.species').multiselect({
        buttonWidth: '100%',
        maxHeight: 300,
        enableFiltering: true,
        enableCaseInsensitiveFiltering: true,
        filterPlaceholder: 'Выбрать вид животного...'
    });
    $('.breeds').multiselect({
        buttonWidth: '100%',
        maxHeight: 300,
        enableFiltering: true,
        enableCaseInsensitiveFiltering: true,
        filterPlaceholder: 'Выбрать породу животного...'
    });

    changeSpecies();

    var options = {
        name: 'owner_address_request',
        mode: 'normal',
        new_tags: 0,
        max_tags: 1,
        request_min: 3,
        request_title: 'title',
        response_id: 'rec_id',
        response_title: 'rec_title',
        width: '100%',
        height: 250,
        max_tags_win: 10,
        url: "?action=addresses&tokken=" + fiasTokken + "&mode=xml"
    };
    addresses_1_input = new JxTag(options);

    var options = {
        name: 'owner_new_address',
        mode: 'normal',
        new_tags: 0,
        max_tags: 1,
        request_min: 3,
        request_title: 'title',
        response_id: 'rec_id',
        response_title: 'rec_title',
        width: '100%',
        height: 250,
        max_tags_win: 10,
        url: "?action=addresses&tokken=" + fiasTokken + "&mode=xml"
    };
    addresses_2_input = new JxTag(options);
}

function changeSheltersOwner() {
    let owners = $('input[name="owner"]');
    let owner = '';

    if (owners.length >= 1) {
        owners.each(function () {
            if ($(this).prop("checked") == true) {
                owner = $(this).val();
            }
        });
    }

    if (owner) {
        $('#choose_owner').prop('disabled', false);
        $('#choose_owner').show();
    }
}

function chooseSheltersOwner() {
    //ищем выбранного пользователя
    let owners = $('input[name="owner"]');

    let owner;
    if (owners.length >= 1) {
        owners.each(function () {
            if ($(this).prop("checked") == true) {
                owner = $(this).val();
            }
        });
    }

    if (owner) {
        //выбираем из списка
        $('#owner_id').val(owner);
        $('#owner_title').html($("label[for='owner_" + owner + "']").text());
        closeSheltersOwnerWindow();
    } else {
        //добавляем нового и выбираем его

        $('#owner_new_surname').removeClass("error_field");
        $('#owner_new_name').removeClass("error_field");
        $('#owner_new_telephone').removeClass("error_field");
        $('#owner_new_address_').removeClass("error_field");

        if ($('#owner_new_surname').val() == '' || $('#owner_new_name').val() == '' || $('#owner_new_telephone').val() == '' || $('#owner_new_address').val() == '') {
            $("#request_window_message").html('Не заполнены необходимые поля.');
            $("#request_window_message").show();

            if ($('#owner_new_surname').val() == '') {
                $('#owner_new_surname').addClass("error_field");
            }
            if ($('#owner_new_name').val() == '') {
                $('#owner_new_name').addClass("error_field");
            }
            if ($('#owner_new_telephone').val() == '') {
                $('#owner_new_telephone').addClass("error_field");
            }
            if ($('#owner_new_address').val() == '') {
                $('#owner_new_address_').addClass("error_field");
            }

            return true;
        }

        owner_surname = $('#owner_new_surname').val();
        owner_name = $('#owner_new_name').val();
        owner_secondname = $('#owner_new_secondname').val();
        $(".telephone").unmask();
        owner_telephone = '+7' + $('#owner_new_telephone').val();
        $('.telephone').mask("+7(999) 999-9999");

        let data = '';
        owner_address = $('#owner_new_address').val();

        let result = owner_address.replace("[ ", "");
        owner_address = result.replace(" ]", "");
        //новый владелец
        data += 'owner_name=' + owner_name + '';
        data += '&owner_secondname=' + owner_secondname + '';
        data += '&owner_surname=' + owner_surname + '';
        data += '&owner_telephone=' + owner_telephone + '';
        data += '&owner_address=' + owner_address + '';
        data += '&action=add_new_owner';
        data += '&mode=xml';

        jQuery.ajax({
            'async': true,
            'global': false,
            'cache': false,
            'type': 'GET',
            'dataType': 'xml',
            'data': data,
            'url': "index.php",
            'beforeSend': function () {
                //блокируем кнопку
                $('#choose_owner').prop('disabled', true);
                //показать загрузку
            },
            'success': function (Data) {
                if ($(Data).find('message').text() == 'owner_added') {
                    $('#owner_id').val($(Data).find('id').text());
                    $('#owner_title').html($(Data).find('name').text());
                    closeSheltersOwnerWindow();
                }
                if ($(Data).find('message').text() == 'empty_fields') {
                    $("#request_window_message").html('Не заполнены необходимые поля.');
                    $("#request_window_message").show();
                    $('#choose_owner').prop('disabled', false);
                }
            }
        });
    }
}

function closeSheltersOwnerWindow() {
    $("#background_t").fadeOut();
    $("#container_t").hide();
}

function showSheltersInformation(Id, Tab = null) {
    jQuery.ajax({
        'async': true,
        'global': false,
        'cache': false,
        'type': 'GET',
        'dataType': 'xml',
        'data': '&id=' + Id + '&mode=xml&action=information',
        'url': "index.php",
        'beforeSend': function () {
            //показать загрузку
            showLoading('main');

            if (Tab != 'tab1') {
                $("#tab1").hide();
            }

            $("#tab2").hide();
            $("#tab3").hide();
            $("#tab4").hide();
            $("#tab5").hide();
        },
        'success': function (Data) {
            closeLoading('main');

            if (Tab) {
                $("#" + Tab + "").show();
            } else {
                $("#tab1").show();
            }

            currentPet = $(Data).find('rec_id').text();
            currentOrganzationTitle = $(Data).find('rec_organization').text();

            ////////////
            let TempContent = '<div class="row">';

            //
            TempContent += '<h1 class="col-12">';
            if ($(Data).find('rec_name').text()) {
                TempContent += $(Data).find('rec_name').text();
            } else {
                TempContent += 'Нет клички';
            }

            TempContent += ', ';
            TempContent += $(Data).find('rec_species_title').text() + ', ';

            if ($(Data).find('rec_sex_title').text()) {
                TempContent += '' + $(Data).find('rec_sex_title').text() + ', ';
            }
            if ($(Data).find('rec_breeds_title').text()) {
                TempContent += '' + $(Data).find('rec_breeds_title').text() + ', ';
            }
            if ($(Data).find('rec_color_title').text()) {
                TempContent += '' + $(Data).find('rec_color_title').text() + ', ';
            }

            TempContent += '' + $(Data).find('rec_age').text() + '';
            TempContent += '</h1>';
            //

            //информация по вакцинации
            if ($(Data).find('rec_vac_date').text() == '') {
                TempContent += '<div class="col-12 warning"><span>Вакцинация не проводилась</span></div>';
            } else {
                let cur_date = new Date();
                let vac_date = new Date($(Data).find('rec_vac_date').text());
                let vac_30_date = new Date();
                let vac_date_ = (vac_date.getDate() + 1 < 10 ? '0' : '') + vac_date.getDate() + '.' + (vac_date.getMonth() + 1 < 10 ? '0' : '') + (vac_date.getMonth() + 1) + '.' + vac_date.getFullYear();

                vac_30_date.setDate(vac_date.getDate() - 30);

                if (cur_date > vac_date) {
                    TempContent += '<div class="col-12 warning"><span>Действие вакцинации закончилось (' + vac_date_ + ')</span></div>';
                } else if (cur_date < vac_30_date) {
                    //До вакцинации осталось менее 30 дней (Дата)
                    TempContent += '<div class="col-12 attention"><span>До вакцинации осталось менее 30 дней (' + vac_date_ + ')</span></div>';
                }
            }
            //информация по вакцинации

            TempContent += '<div class="col-6" style="border-right: 1px solid #fff;">';
            TempContent += 'Чип: ';
            if ($(Data).find('rec_chip').text()) {
                TempContent += '<strong>' + $(Data).find('rec_chip').text() + '</strong><br />';
            } else {
                TempContent += '<span class="attention">Нет данных</span><br />';
            }

            TempContent += 'Размер: ';
            if ($(Data).find('rec_size_title').text()) {
                TempContent += '<strong>' + $(Data).find('rec_size_title').text() + '</strong><br />';
            } else {
                TempContent += '<span class="attention">Нет данных</span><br />';
            }


            TempContent += 'Тип ушей: ';
            if ($(Data).find('rec_ear_title').text()) {
                TempContent += '<strong>' + $(Data).find('rec_ear_title').text() + '</strong><br />';
            } else {
                TempContent += '<span class="attention">Нет данных</span><br />';
            }

            TempContent += 'Тип хвоста: ';
            if ($(Data).find('rec_tail_title').text()) {
                TempContent += '<strong>' + $(Data).find('rec_tail_title').text() + '</strong><br />';
            } else {
                TempContent += '<span class="attention">Нет данных</span><br />';
            }

            TempContent += '</div>';

            TempContent += '<div class="col-6">';
            TempContent += 'Статус: <strong>';
            if ($(Data).find('rec_status').text() == 'IN_SHELTER') {
                TempContent += 'В приюте';
            } else if ($(Data).find('rec_status').text() == 'QUARANTINE') {
                TempContent += 'Карантин';
            } else if ($(Data).find('rec_status').text() == 'QUARANTINE_OTHER') {
                TempContent += 'Карантин (продлён)';
            } else if ($(Data).find('rec_status').text() == 'DEPARTURED') {
                TempContent += 'Выбыло';
            } else if ($(Data).find('rec_status').text() == 'IN_ISOLATION') {
                TempContent += 'В изоляторе';
            } else if ($(Data).find('rec_status').text() == 'IN_HOSPITAL') {
                TempContent += 'В стационаре';
            } else {
                TempContent += $(Data).find('rec_status').text();
            }

            TempContent += '</strong><br />';

            TempContent += 'Приют: <strong>' + $(Data).find('rec_shelter').text() + '</strong><br />';
            TempContent += 'Вольер: ';
            $("#aviary").html($(Data).find('rec_aviary_title').text());
            if ($(Data).find('rec_aviary_id').text()) {
                $("#aviary_").val($(Data).find('rec_aviary_id').text());
            }
            if ($(Data).find('rec_aviary_title').text()) {
                TempContent += '<strong>' + $(Data).find('rec_aviary_title').text() + '</strong><br />';
            } else {
                TempContent += '<span class="warning">Нет данных</span><br />';
            }

            TempContent += 'Социализация: <strong>';
            if ($(Data).find('rec_socialized').text() == 't') {
                TempContent += 'Да';
            } else {
                TempContent += 'Нет';
            }

            TempContent += '</strong>';
            TempContent += '</div>';

            TempContent += '</div>';
            document.getElementById("main").innerHTML = TempContent;

            ///
            //чистим предварительно элементы
            $('#health_values table').find("tr:not(:first)").remove();
            $('#rabies_vaccinations_values table').find('tr:not(:first)').remove();
            $('#other_vaccinations_values table').find('tr:not(:first)').remove();
            $('#ectoparasites_values table').find('tr:not(:first)').remove();
            $('#chip_data_values table').find('tr:not(:first)').remove();

            let not_editable = $(Data).find('not_editable').text();

            ArraySpecs = [];
            ArrayDrugs = [];
            ArrayVaccines = [];
            ArrayOrganizations = [];

            if ($(Data).find('specialists').text()) {
                $(Data).find('specialists').find('rec').each(function () {
                    ArraySpecs.push({name: $(this).find('name').text(), id: $(this).find('id').text()});
                });
            }

            if ($(Data).find('drugs').text()) {
                $(Data).find('drugs').find('rec').each(function () {
                    ArrayDrugs.push({
                        name: $(this).find('name').text(),
                        producer: $(this).find('producer').text(),
                        id: $(this).find('id').text()
                    });
                });
            }

            if ($(Data).find('vaccines').text()) {
                $(Data).find('vaccines').find('rec').each(function () {
                    ArrayVaccines.push({
                        name: $(this).find('name').text(),
                        producer: $(this).find('producer').text(),
                        id: $(this).find('id').text()
                    });
                });
            }

            if ($(Data).find('organizations').text()) {
                $(Data).find('organizations').find('rec').each(function () {
                    ArrayOrganizations.push({name: $(this).find('name').text(), id: $(this).find('id').text()});
                });
            }

            //
            if ($(Data).find('health').text()) {
                let ArrayHealth = [];

                let l = 0;
                $(Data).find('health').find('rec').each(function () {
                    addListItem('health', 'health_values', '{not_editable: ' + not_editable + ', protected:\'' + $(this).find('protected').text() + '\', id:\'' + $(this).find('id').text() + '\', specialist:\'' + $(this).find('specialist_name').text() + '\', spec_id:\'' + $(this).find('specialist_id').text() + '\', status:\'' + $(this).find('status').text() + '\', date:\'' + $(this).find('date').text() + '\',temperature:\'' + $(this).find('temperature').text() + '\',weight:\'' + $(this).find('weight').text() + '\',anamnesis:\'' + $(this).find('anamnesis').text() + '\'}');
                    $(this).find('anamnesis_rec').each(function () {
                        ArrayHealth.push({
                            num: l,
                            title: $(this).find('anamnesis_title').text(),
                            id: $(this).find('anamnesis_id').text()
                        });
                    });
                    l++;
                });

                for (i = 0; i <= document.getElementsByClassName('anamnesis').length - 1; i++) {
                    document.getElementsByClassName('anamnesis')[i].classList.add('anamnesis' + i);
                    var options_anamnesis = {
                        class: 'anamnesis' + i,
                        mode: 'normal',
                        new_tags: 0,
                        text_tags: 1,
                        request_min: 2,
                        request_title: 'title',
                        response_id: 'rec_id',
                        response_title: 'rec_title',
                        width: '100%',
                        height: 350,
                        max_tags_win: 10,
                        request_time: 100,
                        url: "?action=anamnesiss&mode=xml"
                    };
                    let t = new JxTag(options_anamnesis);

                    for (j = 0; j <= ArrayHealth.length - 1; j++) {
                        if (ArrayHealth[j].num == i) {
                            if (ArrayHealth[j].id) {
                                t.AddTag(ArrayHealth[j].title, {id: ArrayHealth[j].id});
                            } else {
                                t.AddTag(ArrayHealth[j].title, {text: 1});
                            }
                        }
                    }
                }
            }
            //

            if ($(Data).find('rabies_vaccinations').text()) {
                $(Data).find('rabies_vaccinations').find('rec').each(function () {
                    addListItem('rabies_vaccinations', 'rabies_vaccinations_values', '{not_editable: ' + not_editable + ', protected:\'' + $(this).find('protected').text() + '\', id:\'' + $(this).find('id').text() + '\', specialist:\'' + $(this).find('specialist_name').text() + '\', spec_id:\'' + $(this).find('specialist_id').text() + '\', drug_name:\'' + $(this).find('drug_name').text() + '\', drug_id:\'' + $(this).find('drug_id').text() + '\', status:\'' + $(this).find('status').text() + '\',date:\'' + $(this).find('date').text() + '\',producer_name:\'' + $(this).find('producer_name').text() + '\',batch:\'' + $(this).find('batch').text() + '\',expiry_date:\'' + $(this).find('expiry_date').text() + '\',valid_until:\'' + $(this).find('valid_until').text() + '\',is_out_org:\'' + $(this).find('is_out_org').text() + '\',organization:\'' + $(this).find('organization').text() + '\',organization_id:\'' + $(this).find('organization_id').text() + '\',organization_name:\'' + $(this).find('organization_name').text() + '\'}');
                });
            }

            if ($(Data).find('chip_data').text()) {
                $(Data).find('chip_data').find('rec').each(function () {
                    addListItem('chip_data', 'chip_data_values', '{ chip:\'' + $(this).find('chip').text() + '\', main_flag:\'' + $(this).find('main_flag').text() + '\'}');
                });
            }

            if ($(Data).find('other_vaccinations').text()) {
                $(Data).find('other_vaccinations').find('rec').each(function () {
                    addListItem('other_vaccinations', 'other_vaccinations_values', '{not_editable: ' + not_editable + ', protected:\'' + $(this).find('protected').text() + '\', id:\'' + $(this).find('id').text() + '\', specialist:\'' + $(this).find('specialist_name').text() + '\', spec_id:\'' + $(this).find('specialist_id').text() + '\', drug_name:\'' + $(this).find('drug_name').text() + '\', drug_id:\'' + $(this).find('drug_id').text() + '\', status:\'' + $(this).find('status').text() + '\',date:\'' + $(this).find('date').text() + '\',producer_name:\'' + $(this).find('producer_name').text() + '\',batch:\'' + $(this).find('batch').text() + '\',expiry_date:\'' + $(this).find('expiry_date').text() + '\',valid_until:\'' + $(this).find('valid_until').text() + '\',is_out_org:\'' + $(this).find('is_out_org').text() + '\',organization:\'' + $(this).find('organization').text() + '\',organization_id:\'' + $(this).find('organization_id').text() + '\',organization_name:\'' + $(this).find('organization_name').text() + '\'}');
                });
            }

            if ($(Data).find('ectoparasites').text()) {
                $(Data).find('ectoparasites').find('rec').each(function () {
                    addListItem('ectoparasites', 'ectoparasites_values', '{not_editable: ' + not_editable + ', protected:\'' + $(this).find('protected').text() + '\', id:\'' + $(this).find('id').text() + '\', specialist:\'' + $(this).find('specialist_name').text() + '\', spec_id:\'' + $(this).find('specialist_id').text() + '\', drug_name:\'' + $(this).find('drug_name').text() + '\', drug_id:\'' + $(this).find('drug_id').text() + '\', producer_name:\'' + $(this).find('producer_name').text() + '\',dose:\'' + $(this).find('dose').text() + '\',date:\'' + $(this).find('date').text() + '\',expiry_date:\'' + $(this).find('date_exp').text() + '\',valid_until:\'' + $(this).find('valid_until').text() + '\',organization:\'' + $(this).find('organization').text() + '\',organization_id:\'' + $(this).find('organization_id').text() + '\',organization_name:\'' + $(this).find('organization_name').text() + '\'}');
                });
            }
            ///

            if ($(Data).find('skills').text()) {
                $(Data).find('skills').find('rec').each(function () {
                    skill_input.AddTag($(this).find('title').text(), {id: $(this).find('id').text()});
                });
            }

            if ($(Data).find('rec_castrated_date').text()) {
                $('.castrated_date').val($(Data).find('rec_castrated_date').text())
            }

            if ($(Data).find('history').text()) {
                ///////
                TempContent = '<table class="table w-100">';
                TempContent += '<tr>';
                TempContent += '<th>Дата/время</th>';
                TempContent += '<th>Событие</th>';
                TempContent += '<th>Автор изменений</th>';
                TempContent += '<th>Организация</th>';
                TempContent += '</tr>';


                $(Data).find('history').find('event').each(function () {
                    TempContent += '<tr>';
                    TempContent += '<td>' + $(this).find('datetime').text() + '</td>';
                    TempContent += '<td>' + $(this).find('title').text() + '</td>';
                    TempContent += '<td>' + $(this).find('user').text() + '</td>';
                    TempContent += '<td>' + $(this).find('organization').text() + '</td>';
                    TempContent += '</tr>';
                });
                TempContent += '</table>';
            } else {
                TempContent = 'Нет данных по истории животного.';
            }
            document.getElementById("tab5").innerHTML = TempContent;

            //
            if ($(Data).find('rec_socialized').text() == 't') {
                $("#socialized").prop("checked", true);
                skill_input.Enable();
            } else {
                $("#socialized").prop("checked", false);
                skill_input.Disable();
            }
            //

            //
            if ($(Data).find('rec_castrated').text() == 't') {
                $("#castrated").prop("checked", true);
                //skill_input.Enable();
            } else {
                $("#castrated").prop("checked", false);
            }

            if ($(Data).find('rec_early_castrated').text() == 't') {
                $("#early_castrated").prop("checked", true);
            } else {
                $("#early_castrated").prop("checked", false);
            }
            //

            //files
            if ($(Data).find('files').text()) {
                TempContent = '';
                $(Data).find('files').find('file').each(function () {
                    TempContent += '<div class="document">';
                    TempContent += '<div class="title">' + $(this).find('name').text() + '</div>';
                    TempContent += '<div class="download" onclick="downloadSheltersDocument(' + $(this).find('id_file').text() + ');"></div>';
                    if ($(this).find('protected').text() == 1) {
                        TempContent += '<div class="null"></div>';
                    } else {
                        if (window.canUserEdit) TempContent += '<div class="delete" onclick="deleteSheltersDocument(' + $(this).find('id_file').text() + ');"></div>';
                    }

                    TempContent += '</div>';
                });

                document.getElementById("documents").innerHTML = TempContent;
            }
            //files

            //images
            if ($(Data).find('images').text()) {
                TempContent = '';
                $(Data).find('images').find('image').each(function () {
                    TempContent += '<div class="image">';
                    let main_style = '';
                    if ($(this).find('type').text() == 'shelter_main') {
                        main_style = 'main';
                    }

                    TempContent += '<div class="pic ' + main_style + '" style="background-image: url(\'' + $(this).find('path').text() + '\');" onclick="mainSheltersImage(' + $(this).find('id_file').text() + ');"></div>';
                    TempContent += '<div class="download" onclick="downloadSheltersImage(' + $(this).find('id_file').text() + ');"></div>';
                    if ($(this).find('protected').text() == 1) {
                        TempContent += '<div class="null"></div>';
                    } else {
                        if (window.canUserEdit) TempContent += '<div class="delete" onclick="deleteSheltersImage(' + $(this).find('id_file').text() + ');"></div>';
                    }
                    TempContent += '</div>';
                });

                document.getElementById("images").innerHTML = TempContent;
            }
            //images

            $("#name").val($(Data).find('rec_name').text());
            $("#characteristics").val($(Data).find('rec_characteristics').text());
            $("#character").val($(Data).find('rec_character').text());
            // $("#chip").val($(Data).find('rec_chip').text());
            $("#label").val($(Data).find('rec_label').text());
            $("#birthday").val($(Data).find('rec_birthday').text());
            $("#specie").val($(Data).find('rec_species_id').text()).multiselect('refresh');
            $("#breed").val($(Data).find('rec_breeds_id').text()).multiselect('refresh');
            $("#sex").val($(Data).find('rec_sex').text()).multiselect('refresh');

            $("#arrival_reason_id").val($(Data).find('rec_arrival_reason').text());


            $("#color").val($(Data).find('rec_color_id').text()).multiselect('refresh');
            $("#size").val($(Data).find('rec_size_id').text()).multiselect('refresh');
            $("#wool").val($(Data).find('rec_wool_id').text()).multiselect('refresh');
            $("#tail").val($(Data).find('rec_tail_id').text()).multiselect('refresh');
            $("#ear").val($(Data).find('rec_ear_id').text()).multiselect('refresh');

            $('.drug').multiselect({
                enableClickableOptGroups: true,
                maxHeight: 300,
                enableFiltering: true,
                enableCaseInsensitiveFiltering: true,
                nonSelectedText: 'Выберите...',
                allSelectedText: "Выбраны все",
                nSelectedText: "выбрано",
                buttonWidth: '100%',
                filterPlaceholder: 'Выбрать запись...'
            });

            //
            if ($(Data).find('rec_arrival_reason').text() == 'CATCH') {
                $("#tab2_form #arrival_reason").html('Отлов');
            } else if ($(Data).find('rec_arrival_reason').text() == 'COURT_DECISION') {
                $("#tab2_form #arrival_reason").html('Решение суда');
            } else if ($(Data).find('rec_arrival_reason').text() == 'FOUNDLING') {
                $("#tab2_form #arrival_reason").html('Подкидыш');
            } else if ($(Data).find('rec_arrival_reason').text() == 'OWNER_REFUSAL') {
                $("#tab2_form #arrival_reason").html('Отказ владельца');
            }
            $("#tab2_form #arrival_date").html($(Data).find('rec_arrival_date').text());

            if ($(Data).find('rec_departure_reason').text()) {
                $("#tab2_form #departure_reason_").show();

                if ($(Data).find('rec_departure_reason').text() == 'RETURNED_TO_NEW_OWNER') {
                    $("#tab2_form #departure_reason").html('Передача новому владельцу');
                } else if ($(Data).find('rec_departure_reason').text() == 'RETURNED_TO_OWNER') {
                    $("#tab2_form #departure_reason").html('Возврат прежнему владельцу');
                } else if ($(Data).find('rec_departure_reason').text() == 'DEATH') {
                    $("#tab2_form #departure_reason").html('Естественная смерть');
                } else if ($(Data).find('rec_departure_reason').text() == 'EUTHANASIA') {
                    $("#tab2_form #departure_reason").html('Эвтаназия');
                } else if ($(Data).find('rec_departure_reason').text() == 'ESCAPE') {
                    $("#tab2_form #departure_reason").html('Побег');
                }
                $("#tab2_form #departure_date").html($(Data).find('rec_departure_date').text());
            } else {
                $("#tab2_form #departure_reason_").hide();
            }

            if ($(Data).find('rec_arrival_act_number').text()) {
                $("#tab2_form #arrival_act").html($(Data).find('rec_arrival_act_number').text() + ' от ' + $(Data).find('rec_arrival_act_number_date').text());
            } else {
                $("#tab2_form #arrival_act_").hide();
            }

            if ($(Data).find('rec_catching_act_number').text()) {
                $("#tab2_form #catching_act").html($(Data).find('rec_catching_act_number').text() + ' от ' + $(Data).find('rec_catching_act_date').text());
            } else {
                $("#tab2_form #catching_act_").hide();
            }

            if ($(Data).find('rec_catching_video').text()) {
                $("#tab2_form #catching_video").html('<a href="' + $(Data).find('rec_catching_video').text() + '" target="blank">' + $(Data).find('rec_catching_video').text() + '</a>');
            } else {
                $("#tab2_form #catching_video_").hide();
            }

            if ($(Data).find('rec_catching_address').text()) {
                $("#tab2_form #catching_address").html($(Data).find('rec_catching_address').text() + '');
            } else {
                $("#tab2_form #catching_address_").hide();
            }

            if ($(Data).find('rec_arrival_work_order').text()) {
                $("#tab2_form #arrival_work_order").html($(Data).find('rec_arrival_work_order').text() + ' от ' + $(Data).find('rec_arrival_work_order_date').text());
            } else {
                $("#tab2_form #arrival_work_order_").hide();
            }
            //

            TempContent = '<button onclick="showButtonMenu(\'shelters_export\');">Печать</button>';
            TempContent += '<div class="button_menu" id="menu_shelters_export" style="width: 250px; display: none;">';
            TempContent += '<div><a href="index.php?action=doc&id=' + Id + '&doc=animal_card">Карточка учета животного</a></div>';
            TempContent += '<div><a href="index.php?action=doc&id=' + Id + '&doc=volier_animal_card">Карточка животного на вольер</a></div>';

            if ($(Data).find('rec_departure_reason').text() != 'DEATH' && $(Data).find('rec_departure_reason').text() != 'EUTHANASIA') {
                TempContent += '<div><a href="index.php?action=doc&id=' + Id + '&doc=anketa">Анкета желающего взять животное</a></div>';
                TempContent += '<div><a href="index.php?action=doc&id=' + Id + '&doc=transfer">Договор передачи</a></div>';
                TempContent += '<div><a href="index.php?action=doc&id=' + Id + '&doc=return">Акт возврата</a></div>';
            }

            TempContent += '<div><a href="index.php?action=doc&id=' + Id + '&doc=death">Акт смерти</a></div>';
            TempContent += '</div>';

            if ($(Data).find('rec_status').text() == 'DEPARTURED') {
                if ($(Data).find('rec_departure_reason').text() != 'DEATH' && $(Data).find('rec_departure_reason').text() != 'EUTHANASIA') {
                    ///
                    TempContent = '';
                    if ($(Data).find('rec_departure_reason').text() == 'RETURNED_TO_NEW_OWNER' || $(Data).find('rec_departure_reason').text() == 'RETURNED_TO_OWNER') {
                        TempContent += '<button onclick="showButtonMenu(\'shelters_export\');">Печать</button>';
                        TempContent += '<div class="button_menu" id="menu_shelters_export" style="width: 250px;display: none;">';
                        TempContent += '<div><a href="index.php?action=doc&id=' + Id + '&doc=animal_card">Карточка учета животного</a></div>';
                        TempContent += '<div><a href="index.php?action=doc&id=' + Id + '&doc=volier_animal_card">Карточка учета животного на вольер</a></div>';
                        TempContent += '</div>';
                    }
                    ///
                    TempContent += '<button style="margin-left: 10px;" onclick="returnPetAcceptWindow();">Вернуть в приют</button>';
                    document.getElementById("shelters_buttons").innerHTML = TempContent;
                }

                $("#tab2_form #aviary_edit").removeAttr("onclick");
                $("#tab2_form #aviary_edit").addClass("disabled");


            } else {
                if (not_editable != '1') {
                    if (window.canUserEdit) {
                        TempContent += `<button style="margin-left: 10px;" onclick="showButtonMenu(\'shelters_retirement\');">Оформить выбытие</button>
						<div class="button_menu" id="menu_shelters_retirement" style="right: 15px; display: none;">
							<div onclick="showDepartureWindow(\'RETURNED_TO_NEW_OWNER\');">Передача новому владельцу</div>
							<div onclick="showDepartureWindow(\'RETURNED_TO_OWNER\');">Возврат прежнему владельцу</div>
							<div onclick="showDepartureWindow(\'DEATH\');">Смерть</div>
							<div onclick="showDepartureWindow(\'ESCAPE\');">Побег</div>
						</div>`;
                    }

                    document.getElementById("shelters_buttons").innerHTML = TempContent;

                    //кнопки стационар и изолятор
                    if ($(Data).find('rec_status').text() == 'IN_ISOLATION' || $(Data).find('rec_status').text() == 'IN_HOSPITAL') {
                        TempContent = '<button type="button" class="button health" onclick="chooseSheltersHealth(0);">Животное здорово</button>';
                    } else {
                        TempContent = '<button type="button" class="button health" onclick="chooseSheltersHealth(1);">В стационаре</button>';
                        TempContent += '<button type="button" class="button health" style="margin-left: 10px;" onclick="chooseSheltersHealth(2);">В изоляторе</button>';
                    }
                    //кнопки стационар и изолятор

                    if (window.canUserEdit) document.getElementById("tab3_1_buttons").innerHTML = TempContent;
                }
            }

            if (not_editable == '1') {
                //выключаем всё редактирование

                //основная информация
                $("#tab1_buttons").hide();
                $("#tab1").find("input").prop('disabled', true);
                $("#tab1").find("textarea").prop('disabled', true);
                //основная информация

                $("#specie").multiselect("disable");
                $("#breed").multiselect("disable");
                $("#sex").multiselect("disable");
                $("#size").multiselect("disable");
                $("#wool").multiselect("disable");
                $("#ear").multiselect("disable");
                $("#tail").multiselect("disable");
                $("#color").multiselect("disable");

                //сведения о движении
                $("#tab2_buttons").hide();

                $("#aviary_edit").removeAttr("onclick");
                $("#aviary_edit").addClass("disabled");
                //сведения о движении

                //состояние здоровья
                $("#tab3_1_buttons").hide();
                $("#tab3_2_buttons").hide();
                $("#tab3_3_buttons").hide();
                $("#tab3").find("input").prop('disabled', true);
                $("#tab3").find("textarea").prop('disabled', true);
                $("#tab3").find("button").prop('disabled', true);
                //состояние здоровья

                //вакцинации и обработки
                $("#tab4").find("input").prop('disabled', true);
                $("#tab4").find("button").prop('disabled', true);
                $("#tab4_buttons").hide();
                //вакцинации и обработки
            }


        }
    });

    if ($(document).width() < 2250) {
        $("#shelters_buttons").addClass("smallbuttons");
    }
}

function returnPetAcceptWindow() {
    let Text = 'Подтверждаете возвращение животного в приют?';
    showAcceptWindow('Возврат в приют', Text, 'returnPet();', 'Подтвердить');
}

function showSheltersDoc(Id) {

}

function showButtonMenu(Id) {
    if ($('#menu_' + Id).css('display') == 'none') {
        $('#menu_' + Id).show();
    } else {
        $('#menu_' + Id).hide();
    }
}

function showSheltersTab(Id) {
    //
    $("#message").hide();
    //

    $(".tabs .item1").removeClass("current");
    $(".tabs .item2").removeClass("current");
    $(".tabs .item3").removeClass("current");
    $(".tabs .item4").removeClass("current");
    $(".tabs .item5").removeClass("current");

    $("#tab1").hide();
    $("#tab2").hide();
    $("#tab3").hide();
    $("#tab4").hide();
    $("#tab5").hide();

    $(".tabs .item" + Id).addClass("current");
    $("#tab" + Id).show();
}

function addSheltersPet() {
    let chip_ = $("#chip").val();
    if (chip_.length < 15 && chip_ != '') {
        $("#message").removeClass('success');
        $("#message").removeClass('process');
        $("#chip").addClass('error');

        //
        $("#message").html('Номер чипа должен содержать 15 цифр.');
        $("#message").addClass('error');
        $("#message").show();
        //

        return true;
    }

    if ($("#is_quarantine").prop("checked") == true) {
        let date_from = $("#date_from").val();
        let date_to = $("#date_to").val();

        let date_from_ = new Date(date_from);
        let date_to_ = new Date(date_to);

        if (date_from == '' || date_to == '') {
            $("#message").html('Не заполнен период карантина.');
            $("#message").addClass('error');
            $("#message").show();

            return true;
        }

        if (date_from_ > date_to_) {
            //
            $("#message").html('Дата начала периода карантина не может быть больше даты его окончания.');
            $("#message").addClass('error');
            $("#message").show();
            //

            return true;
        }
    }

    // if(chip_){
    // 	//запускаем проверку чипа
    // 	jQuery.ajax({
    // 		'async': true,
    // 		'global': false,
    // 		'cache': false,
    // 		'type': 'GET',
    // 		'dataType': 'xml',
    // 		'data': '&chip='+chip_+'&mode=xml&action=check_chip',
    // 		'url': "index.php",
    // 		'beforeSend': function() {
    // 			//показать загрузку

    // 		},
    // 		'success': function (Data) {
    // 			if($(Data).find('error').text()){
    // 				return true;

    // 			}

    // 		}
    // 	});
    // }

    //Дата рождения не может превышать текущую
    let result = jQuery("#add_form").serialize();
    let result_ = result.replace("%5B%20", "");
    result = result_.replace("%20%5D", "");

    jQuery.ajax({
        'async': true,
        'global': false,
        'cache': false,
        'type': 'GET',
        'dataType': 'xml',
        'data': result + '&action=add_pet&mode=xml',
        'url': "index.php",
        'beforeSend': function () {
            //показать загрузку
            $("#message").removeClass('error');
            $("#message").removeClass('success');
            $("#message").addClass('process');
            $("#message").html('Пожалуйста подожите...');
            $("#message").show();
        },
        'success': function (Data) {
            if ($(Data).find('message').text() == 'token_invalid') {
                window.location.href = 'index.php';
            } else {
                if ($(Data).find('message').text() == 'pet_added') {
                    window.location.href = './?action=edit&id=' + $(Data).find('id_pet').text() + '';
                }
                if ($(Data).find('message').text() == 'error_fields') {
                    $("#message").html('Неверный формат поля.');
                    $("#message").removeClass('process');
                    $("#message").addClass('error');
                    $("#message").show();
                    if ($(Data).find('birthday').text() == '1') {
                        $("#birthday").addClass('error');
                    }
                }
                if ($(Data).find('message').text() == 'empty_fields') {
                    //

                    $("#message").html('Не заполнены необходимые поля.');
                    $("#message").removeClass('process');
                    $("#message").addClass('error');
                    $("#message").show();
                    //
                    //считываем поля с ошибкой
                    if ($(Data).find('sex').text() == '1') {
                        $("#sex").next().addClass('error');
                    }
                    if ($(Data).find('birthday').text() == '1') {
                        $("#birthday").addClass('error');
                    }
                    if ($(Data).find('breed').text() == '1') {
                        $("#breed+.btn-group").addClass('error-border');
                    }
                    if ($(Data).find('specie').text() == '1') {
                        $("#specie").next().addClass('error');
                    }
                }
                if ($(Data).find('message').text() == 'docs_error') {
                    if ($(Data).find('text').text() == 'act_arrive') {
                        $("#message").html('Необходимо прикрепить документ акт приёма.');
                    }
                    $("#message").removeClass('process');
                    $("#message").addClass('error');
                    $("#message").show();
                }
                if ($(Data).find('message').text() == 'chip_error') {
                    if ($(Data).find('chip').text() == '1') {
                        $("#chip").addClass('error');
                    }

                    //Внимание!
                    if ($(Data).find('text').text() == 'in_this_shelter') {
                        $("#message").html('В системе с указанным идентификационным номером зарегистрировано животное. Животное ранее содержалось в вашем приюте. Необходимо оформить возврат животного в приют. <a href="/shelters/?action=edit&id=' + $(Data).find('id_pet').text() + '">Перейти в карточку животного?</a>');
                        //1. животное обнаружено в этом же приюте - переход к нему
                        //
                    }
                    if ($(Data).find('text').text() == 'in_another_shelter') {
                        $("#message").html('В системе с указанным идентификационным номером зарегистрировано животное. Животное ранее содержалось в приюте (' + $(Data).find('shelter_title').text() + '). Необходимо оформить возврат животного в этот приют.');
                        //1. животное обнаружено в этом же приюте - переход к нему
                        //
                    }

                    if ($(Data).find('text').text() == 'now_in_this_shelter') {
                        $("#message").html('В системе с указанным идентификационным номером зарегистрировано животное. Животное находится в вашем приюте. <a href="/shelters/?action=edit&id=' + $(Data).find('id_pet').text() + '">Перейти в карточку животного?</a>');
                        //1. животное обнаружено в этом же приюте - переход к нему
                        //
                    }
                    if ($(Data).find('text').text() == 'now_in_another_shelter') {
                        $("#message").html('Животное содержится в приюте (' + $(Data).find('shelter_title').text() + '). Для добавления животного необходимо оформить выбытие животного из приюта, в котором оно содержится.');
                        //3. животное обнаружено в другом приюте - сообщение?
                        //Животное содержится в приюте (Благотворительный фонд защиты животных "Ласковый зверь" - Дубнинская). Для добавления животного необходимо оформить выбытие животного из приюта, в котором оно содержится.
                        //Кличка	Вид	Порода	Пол	Дата рождения	ИН
                        //Матильда	собаки	метис	женский	15.05.2015	586735687667798
                    }

                    if ($(Data).find('text').text() == 'in_system') {
                        let message_ = 'В системе с указанным идентификационным номером зарегистрировано животное. ';
                        message_ += 'Вы можете подтвердить, что найденное в системе животное поступило в приют, либо исправить данные об идентификации поступившего животного. ';
                        message_ += 'При подтверждении в карточке животного появиться информация о поступлении животного в приют. <span class="link" onclick="copySheltersPet(\'' + $(Data).find('id_pet').text() + '\');">Подтвердить?</span>';

                        $("#message").html(message_);
                        //2. животное обнаружено вообще, но не в этом = автозаполнение
                        //
                        // Дата рождения 02.2019
                        // Кличка Бобстерьер
                        // Порода метис
                    }
                    if ($(Data).find('text').text() == 'death') {
                        $("#message").html('В системе с указанным идентификационным номером зарегистрировано животное, данное животное пало, смерть (падеж). Укажите корректный номер чипа.');
                    }
                    if ($(Data).find('text').text() == 'departured') {
                        $("#message").html('В системе с указанным идентификационным номером зарегистрировано животное снятое с учёта. Укажите корректный номер чипа.');
                    }

                    $("#message").removeClass('process');
                    $("#message").addClass('error');
                    $("#message").show();
                }
            }
        }
    });
}

function changeSheltersSpecie() {
    let SpeciesVal = $('#specie').val();

    if (SpeciesVal) {
        $('#breed option').prop('disabled', true).prop('selected', false);
        $('#breed option[species_id=' + SpeciesVal + ']').prop('disabled', false);
        $("#breed option[species_id='']").prop('disabled', false);
        $('#breed').val('').multiselect('refresh');
    } else {
        $("#breed option[species_id='']").prop('disabled', false);
        $('#breed option').prop('disabled', false);
        $('#breed').multiselect('refresh');
    }

}

function copySheltersPet(Id) {
    //перезапрашиваем данные по животному

    jQuery.ajax({
        'async': true,
        'global': false,
        'cache': false,
        'type': 'GET',
        'dataType': 'xml',
        'data': 'pet=' + Id + '&action=get_pet&mode=xml',
        'url': "index.php",
        'beforeSend': function () {
            // //показать загрузку
            // $("#message").removeClass('error');
            // $("#message").removeClass('success');
            // $("#message").addClass('process');
            // $("#message").html('Пожалуйста подожите...');
            // $("#message").show();
        },
        'success': function (Data) {
            $("#pet").val($(Data).find('id_pet').text());

            $("#chip").removeClass('error');
            $('#chip').prop('disabled', true);

            if ($(Data).find('name').text()) {
                $("#name").val($(Data).find('name').text());
                $('#name').prop('disabled', true);
            }

            $("#birthday").val($(Data).find('birthday').text());
            $('#birthday').prop('disabled', true);

            $("#specie").val($(Data).find('specie').text()).multiselect('refresh');
            $('#specie').prop('disabled', true);
            $("#specie").multiselect("disable");

            $("#breed").val($(Data).find('breed').text()).multiselect('refresh');
            $('#breed').prop('disabled', true);
            $("#breed").multiselect("disable");

            $("#sex").val($(Data).find('sex').text()).multiselect('refresh');
            $('#sex').prop('disabled', true);
            $("#sex").multiselect("disable");
        }
    });
}

function changeCastrated(Id) {
    if (Id == 1) {
        $("#early_castrated").prop("checked", false);
        //показываем врача
    }
    if (Id == 2) {
        $("#castrated").prop("checked", false);
    }

    // if($("#castrated").prop("checked") == true){
    // if($("#early_castrated").prop("checked") == true){
}

function changeArrivalReason() {
    if ($('#arrival_reason').val() == 'CATCH') {
        $('#catching_addresss_').show();
        $('#catching_video_').show();
    } else {
        $('#catching_addresss_').hide();
        $('#catching_video_').hide();
    }
}

function saveSheltersPet(Id, Pet) {
    if (Id == 'tab1') {

        let flag = false;

        // Проверка на фронте количества цифр в каждом чипе
        // Получаем все значения из полей с классом "chip"
        let chips = $(".chip").map(function () {
            return $(this);
        });

        chips.each(function () {
            let chipValue = $(this).val();
            if (chipValue.length < 15 || chipValue == '') {
                flag = true
                $(this).addClass('error');
                return true;
            } else {
                $(this).removeClass('error');
            }
        });

        if (flag) {
            $("#message").addClass('error');
            $("#message").addClass('error');
            $("#message").html('Номер чипа должен содержать 15 цифр.');
            $("#message").show();
            return true
        } else {
            $("#message").removeClass('error');
        }

        // Проверка на фронте наличия основного признака
        // Получаем все значения из полей с классом "main_flag" - checked
        var checkedMainFlags = $(".main_flag").filter(function () {
            return $(this).prop("checked") == true;
        });
        if (checkedMainFlags.length == 0 && chips.length != 0) {
            $("#message").addClass('error');
            $("#message").html('Должен быть установлен признак "Основной".');
            $("#message").show();
            $("#chip_data_values").addClass('error');
            return true;
        } else {
            $("#chip_data_values").removeClass('error');
            $("#message").hide();
        }

        // let  TempChipData = '';
        // $("#chip_data_values tr").each(function (index) {
        //     TempChipData += '{';
        //     TempChipData += '"chip":"' + $(this).find("#id").val() + '",';
        //     if ($(this).find("#main_flag").prop("checked") == true) {
        //         TempChipData += '"main_flag":"1",';
        //     } else {
        //         TempChipData += '"main_flag":"0",';
        //     }
        //     TempChipData += '}';
        // });
        // $("#chip_data_values").val(TempChipData);
    }

    if (Id == 'tab3_1') {//стерилизация/кастрация

        var time = new Date($('#castrated_date').val()).getTime() / 1000

        if (parseInt($('#castrated_date').attr('max_date')) < time) {
            $("#message").hide();
            $(".castrated_date_error").show();
            return true;
        } else {
            $(".castrated_date_error").hide();
        }

        $("#message").removeClass('success');
        $("#message").removeClass('process');
        $("#message").removeClass('error');

    }

    if (Id == 'tab3_2') {//таблица по здоровью
        $("#message").removeClass('success');
        $("#message").removeClass('process');
        $("#message").removeClass('error');

        let date_error = 0;
        $("#health_values tr").each(function (index) {
            if (index != 0) {
                if ($(this).find("#not_editable").val() == 0 && $(this).find("#date").val() == '') {
                    date_error = 1;
                }
            }
        });

        if (date_error == 1) {
            $("#message").removeClass('success');
            $("#message").removeClass('process');
            $("#message").html('Дата обязательна для заполнения.');
            $("#message").addClass('error');
            $("#message").show();

            return true;
        }

        let TempHealth = '';
        $("#health_values tr").each(function (index) {
            if (index != 0) {
                if ($(this).find("#not_editable").val() == 0) {
                    if (TempHealth) {
                        TempHealth += ',';
                    }
                    TempHealth += '{"id":"' + $(this).find("#id").val() + '","status":"' + $(this).find("#status").val() + '","date":"' + $(this).find("#date").val() + '","temperature":"' + $(this).find("#temperature").val() + '","weight":"' + $(this).find("#weight").val() + '","anamnesis":"' + $(this).find("#anamnesis").val() + '","specialist":"' + $(this).find("#specialist").val() + '"}';
                }
            }
        });
        $("#health").val(TempHealth);
    }

    if (Id == 'tab4') {//Вакцинация и обработка
        $("#message").removeClass('success');
        $("#message").removeClass('process');
        $("#message").removeClass('error');

        let date_error = 0;
        let date_error_ = 0;
        let date_error__ = 0;

        $("#rabies_vaccinations_values tr").each(function (index) {
            if (index != 0) {
                if ($(this).find("#not_editable").val() == 0 && $(this).find("input[type='date']").val() == '') {
                    date_error = 1;
                }
                if ($(this).find("#date").val() > $(this).find("#valid_until").val()) {
                    date_error_ = 1;
                }
                if ($(this).find("#date").val() > $(this).find("#expiry_date").val() && $(this).find("#expiry_date").val() != '') {
                    date_error_ = 1;
                }
            }
        });
        $("#ectoparasites_values tr").each(function (index) {
            if (index != 0) {
                if ($(this).find("#not_editable").val() == 0 && $(this).find("input[type='date']").val() == '') {
                    date_error = 1;
                }
                if ($(this).find("#date").val() > $(this).find("#valid_until").val()) {
                    date_error__ = 1;
                }
                if ($(this).find("#date").val() > $(this).find("#expiry_date").val() && $(this).find("#expiry_date").val() != '') {
                    date_error_ = 1;
                }
            }
        });
        $("#other_vaccinations_values tr").each(function (index) {
            if (index != 0) {
                if ($(this).find("#not_editable").val() == 0 && $(this).find("input[type='date']").val() == '') {
                    date_error = 1;
                }
                if ($(this).find("#date").val() > $(this).find("#valid_until").val()) {
                    date_error_ = 1;
                }
                if ($(this).find("#date").val() > $(this).find("#expiry_date").val() && $(this).find("#expiry_date").val() != '') {
                    date_error_ = 1;
                }
            }
        });

        if (date_error == 1) {
            $("#message").removeClass('success');
            $("#message").removeClass('process');
            $("#message").html('Дата обязательна для заполнения.');
            $("#message").addClass('error');
            $("#message").show();

            return true;
        }

        if (date_error_ == 1) {
            $("#message").removeClass('success');
            $("#message").removeClass('process');
            $("#message").html('"Дата вакцинации" не может превышать дату "Действительно до".');
            $("#message").addClass('error');
            $("#message").show();

            return true;
        }

        if (date_error__ == 1) {
            $("#message").removeClass('success');
            $("#message").removeClass('process');
            $("#message").html('"Дата обработки" не может превышать дату "Действительно до".');
            $("#message").addClass('error');
            $("#message").show();

            return true;
        }

        let TempRabiesVaccinations = '';
        $("#rabies_vaccinations_values tr").each(function (index) {
            if (index != 0) {
                if ($(this).find("#not_editable").val() == 0) {
                    if (TempRabiesVaccinations) {
                        TempRabiesVaccinations += ',';
                    }
                    TempRabiesVaccinations += '{';
                    TempRabiesVaccinations += '"id":"' + $(this).find("#id").val() + '",';
                    TempRabiesVaccinations += '"date":"' + $(this).find("#date").val() + '",';
                    TempRabiesVaccinations += '"drug":"' + $(this).find("#drug").val() + '",';
                    TempRabiesVaccinations += '"organization":"' + $(this).find("#organization").val() + '",';
                    if ($(this).find("#is_out_org").prop("checked") == true) {
                        TempRabiesVaccinations += '"is_out_org":"1",';
                    } else {
                        TempRabiesVaccinations += '"is_out_org":"0",';
                    }
                    TempRabiesVaccinations += '"batch":"' + $(this).find("#batch").val() + '",';
                    TempRabiesVaccinations += '"expiry_date":"' + $(this).find("#expiry_date").val() + '",';
                    TempRabiesVaccinations += '"valid_until":"' + $(this).find("#valid_until").val() + '",';
                    TempRabiesVaccinations += '"specialist":"' + $(this).find("#specialist").val() + '"';
                    TempRabiesVaccinations += '}';
                }
            }
        });
        $("#rabies_vaccinations").val(TempRabiesVaccinations);

        let TempOtherVaccinations = '';
        $("#other_vaccinations_values tr").each(function (index) {
            if (index != 0) {
                if ($(this).find("#not_editable").val() == 0) {
                    if (TempOtherVaccinations) {
                        TempOtherVaccinations += ',';
                    }
                    TempOtherVaccinations += '{';
                    TempOtherVaccinations += '"id":"' + $(this).find("#id").val() + '",';
                    TempOtherVaccinations += '"date":"' + $(this).find("#date").val() + '",';
                    TempOtherVaccinations += '"drug":"' + $(this).find("#drug").val() + '",';
                    TempOtherVaccinations += '"organization":"' + $(this).find("#organization").val() + '",';
                    if ($(this).find("#is_out_org").prop("checked") == true) {
                        TempOtherVaccinations += '"is_out_org":"1",';
                    } else {
                        TempOtherVaccinations += '"is_out_org":"0",';
                    }
                    TempOtherVaccinations += '"batch":"' + $(this).find("#batch").val() + '",';
                    TempOtherVaccinations += '"expiry_date":"' + $(this).find("#expiry_date").val() + '",';
                    TempOtherVaccinations += '"valid_until":"' + $(this).find("#valid_until").val() + '",';
                    TempOtherVaccinations += '"specialist":"' + $(this).find("#specialist").val() + '"';
                    TempOtherVaccinations += '}';
                }
            }
        });
        $("#other_vaccinations").val(TempOtherVaccinations);
        //console.log(TempOtherVaccinations);

        let TempEctoparasites = '';
        $("#ectoparasites_values tr").each(function (index) {
            if (index != 0) {
                if ($(this).find("#not_editable").val() == 0) {
                    if (TempEctoparasites) {
                        TempEctoparasites += ',';
                    }
                    TempEctoparasites += '{';
                    TempEctoparasites += '"id":"' + $(this).find("#id").val() + '",';
                    TempEctoparasites += '"date":"' + $(this).find("#date").val() + '",';
                    TempEctoparasites += '"drug":"' + $(this).find("#drug").val() + '",';
                    TempEctoparasites += '"organization":"",';
                    //' + $(this).find("#organization").val() + '
                    if ($(this).find("#is_out_org").prop("checked") == true) {
                        TempEctoparasites += '"is_out_org":"1",';
                    } else {
                        TempEctoparasites += '"is_out_org":"0",';
                    }
                    TempEctoparasites += '"dose":"' + $(this).find("#dose").val() + '",';
                    TempEctoparasites += '"expiry_date":"' + $(this).find("#expiry_date").val() + '",';
                    TempEctoparasites += '"valid_until":"' + $(this).find("#valid_until").val() + '",';
                    TempEctoparasites += '"specialist":"' + $(this).find("#specialist").val() + '"';
                    TempEctoparasites += '}';
                }
            }
        });
        $("#ectoparasites").val(TempEctoparasites);

        //return true;
    }

    function toggleElementAndGetValue(elementId) {
        const element = document.getElementById(elementId);
        const originalState = element.disabled; // Сохраняем текущее состояние disabled
        element.disabled = false; // Отключаем временно disabled, чтобы считать значение
        const value = element.value; // Читаем значение элемента
        // element.disabled = originalState; // Восстанавливаем исходное состояние disabled
        return value;
    }

    const specieValue = toggleElementAndGetValue('specie');
    const birthdayValue = toggleElementAndGetValue('birthday');
    const skillValue = toggleElementAndGetValue('skill');
    const labelValue = toggleElementAndGetValue('label');
    const sexValue = toggleElementAndGetValue('sex');
    const nameValue = toggleElementAndGetValue('name');
    const breedValue = toggleElementAndGetValue('breed');
    const colorValue = toggleElementAndGetValue('color');
    const characteristicsValue = toggleElementAndGetValue('characteristics');
    const characterValue = toggleElementAndGetValue('character');
    const sizeValue = toggleElementAndGetValue('size');
    const woolValue = toggleElementAndGetValue('wool');
    const earValue = toggleElementAndGetValue('ear');
    const tailValue = toggleElementAndGetValue('tail');
    const socializedValue = toggleElementAndGetValue('socialized');

    var data = $("#" + Id + "_form").serializeArray();
    data.push({name: "pet", value: Pet});
    data.push({name: "action", value: "save_pet"});
    data.push({name: "tab", value: Id});
    data.push({name: "mode", value: "xml"});

    let chipCounter = 1;

    // Изменяем ключи 'chip', чтобы на бэк ушли все чипы
    const result = data.map(item => {
        if (item.name === 'chip') {
            return {name: 'chip' + chipCounter++, value: item.value};
        }
        return item; // Возвращаем остальные элементы без изменений
    });

    data = result;

    jQuery.ajax({
        'async': true,
        'global': false,
        'cache': false,
        'type': 'POST',
        'dataType': 'xml',
        data: $.param(data),
        //'data': jQuery("#"+Id+"_form").serialize()+'&pet='+Pet+'&action=save_pet&tab='+Id+'&mode=xml',
        'url': "index.php",
        'beforeSend': function () {

            //показать загрузку
            $("#message").removeClass('error');
            $("#message").removeClass('success');
            $("#message").addClass('process');
            $("#message").html('Пожалуйста подожите...');
            $("#message").show();
        },
        'success': function (Data) {
            if ($(Data).find('message').text() == 'token_invalid') {
                window.location.href = 'index.php';
            } else {
                if ($(Data).find('message').text() == 'pet_saved') {
                    $("#message").removeClass('error');
                    $("#message").removeClass('process');
                    $("#message").addClass('success');
                    $("#message").html('Сохранено успешно');
                    $("#message").show();

                    if (Id == 'tab3_1' || Id == 'tab3_2') {
                        showSheltersInformation(Pet, 'tab3');
                    } else {
                        showSheltersInformation(Pet, Id);
                    }

                    // $('#tab1_form input').each(function(){
                    // 	$(this).removeClass('error');
                    // });
                }

                if ($(Data).find('message').text() == 'chip_error') {
                    const ident_code = $(Data).find('code').text()

                    let chips = $(".chip").map(function () {
                        return $(this);
                    });


                    chips.each(function () {
                        let chipValue = $(this).val();
                        if (chipValue == ident_code) {
                            $(this).addClass('error'); // Добавляем ошибку только к текущему чипу
                        } else {
                            $(this).removeClass('error');
                        }
                    });

                    const id_pet = $(Data).find('id_pet').text()

                    if ($(Data).find('text').text() == 'now_in_shelter') {
                        $("#message").html('Животное ( id ' + id_pet + ' ) с указанным чипом находится в приюте');
                    }
                    if ($(Data).find('text').text() == 'in_system') {
                        $("#message").html('Животное ( id ' + id_pet + ' ) с указанным чипом уже есть в системе');
                    }
                    if ($(Data).find('text').text() == 'death') {
                        $("#message").html('Животное ( id ' + id_pet + ' ) с указанным чипом снято с учёта');
                    }
                    if ($(Data).find('text').text() == 'departured') {
                        $("#message").html('Животное ( id ' + id_pet + ' ) с указанным чипом снято с учёта');
                    }

                    $("#message").removeClass('process');
                    $("#message").addClass('error');
                    $("#message").show();
                }

                if ($(Data).find('message').text() == 'error_fields') {
                    $("#message").html('Неверный формат поля.');
                    $("#message").removeClass('process');
                    $("#message").addClass('error');
                    $("#message").show();
					if ($(Data).find('birthday').text() == '1') {
						$("#birthday").addClass('error');
					}
                }
                if ($(Data).find('message').text() == 'empty_fields') {
					//
					$("#message").html('Не заполнены необходимые поля.');
					$("#message").removeClass('process');
					$("#message").addClass('error');
					$("#message").show();
					//
					//считываем поля с ошибкой
					if ($(Data).find('sex').text() == '1') {
						$("#sex").next().addClass('error');
					}
					if ($(Data).find('birthday').text() == '1') {
						$("#birthday").addClass('error');
					}
					if ($(Data).find('specie').text() == '1') {
						$("#specie").next().addClass('error');
					}
				}
            }
        }
    });
}

function addListItem(Mode, Id, Value = null) {
    let TempContent = '';

    var Values;
    if (Value) {
        eval('var Values = ' + Value);
    }

    let flag = 0;
    if ($(document).width() < 2250) {
        flag = 1;

        $("#rabies_vaccinations_values").addClass("small");
        $("#ectoparasites_values").addClass("small");
        $("#other_vaccinations_values").addClass("small");
    }

    if (Mode == 'health') {
        let not_editable_ = 0;
        if (Value != undefined) {
            if (Values['not_editable'] !== undefined) {
                not_editable_ = Values['not_editable']
            }
        }
        let protected_ = '';
        if (Value != undefined) {
            if (Values['protected'] !== undefined) {
                protected_ = Values['protected']
            }
        }
        protected_ = protected_ || !window.canUserEdit

        let id_ = '';
        if (Value != undefined) {
            if (Values['id'] !== undefined) {
                id_ = Values['id']
            }
        }
        let spec_id_ = '';
        if (Value != undefined) {
            if (Values['spec_id'] !== undefined) {
                spec_id_ = Values['spec_id']
            }
        }

        //Состояние здоровья
        TempContent = '<tr>';

        TempContent += '<td style="width: 200px;">';

        if (not_editable_ || protected_ == 1 || !window.canUserEdit) {
            TempContent += '<input type="hidden" id="not_editable" class="" value="1">';
        } else {
            TempContent += '<input type="hidden" id="not_editable" class="" value="0">';
        }

        TempContent += '<input type="hidden" id="id" class="" value="' + id_ + '">';

        TempContent += '<select id="status"';
        if (not_editable_ || protected_ == 1 || !window.canUserEdit) {
            TempContent += ' disabled';
        }
        TempContent += '>';
        TempContent += '<option value="QUARANTINE">Kaрантин</option>';
        TempContent += '<option value="HEALTHY">В приюте</option>';
        TempContent += '</select>';
        TempContent += '</td>';

        TempContent += '<td style="width: 150px;">';
        let date_ = '';
        if (Value != undefined) {
            if (Values['date'] !== undefined) {
                date_ = Values['date']
            }
        }
        TempContent += '<input type="date" id="date" class="" value="' + date_ + '" style="width: 150px;"';
        if (not_editable_ || protected_ == 1 || !window.canUserEdit) {
            TempContent += ' disabled';
        }
        TempContent += '>';
        TempContent += '</td>';

        TempContent += '<td style="width: 150px;">';
        let temperature_ = '';
        if (Value != undefined) {
            if (Values['temperature'] !== undefined) {
                temperature_ = Values['temperature']
            }
        }
        TempContent += '<input type="number" min="30" max="50" step="0.01" id="temperature" value="' + temperature_ + '" style="width: 150px;"';
        if (not_editable_ || protected_ == 1 || !window.canUserEdit) {
            TempContent += ' disabled';
        }
        TempContent += '>';
        TempContent += '</td>';

        TempContent += '<td style="width: 150px;">';
        let weight_ = '';
        if (Value != undefined) {
            if (Values['weight'] !== undefined) {
                weight_ = Values['weight']
            }
        }
        TempContent += '<input type="number" min="1" max="200" step="0.01" id="weight" value="' + weight_ + '" style="width: 150px;"';
        if (not_editable_ || protected_ == 1 || !window.canUserEdit) {
            TempContent += ' disabled';
        }
        TempContent += '>';
        TempContent += '</td>';

        TempContent += '<td>';
        let anamnesis_ = '';
        if (Value != undefined) {
            if (Values['anamnesis'] !== undefined) {
                anamnesis_ = Values['anamnesis']
            }
        }
        TempContent += '<input type="text" id="anamnesis" value="' + anamnesis_ + '" autocomplete="off"';
        if (not_editable_ || protected_ == 1 || !window.canUserEdit) {
            TempContent += ' disabled';
        }
        TempContent += '>';
        TempContent += '</td>';

        TempContent += '<td>';
        let specialist_ = '';
        if (Value != undefined) {
            if (Values['specialist'] !== undefined) {
                specialist_ = Values['specialist']
            }
        }
        if (protected_ == 1 || !window.canUserEdit) {
            TempContent += specialist_;
        } else {
            if (ArraySpecs != undefined) {
                TempContent += '<select id="specialist">';
                TempContent += '<option value="">Не выбран</option>';
                if (ArraySpecs.length > 0) {
                    for (let i = 0; i <= ArraySpecs.length - 1; i++) {
                        TempContent += '<option ';
                        if (spec_id_ == ArraySpecs[i].id) {
                            TempContent += 'selected ';
                        }
                        TempContent += 'value="' + ArraySpecs[i].id + '">' + ArraySpecs[i].name + '</option>';
                    }
                }
                TempContent += '</select>';
            }
        }
        TempContent += '</td>';

        TempContent += '<td style="width: 32px;">';
        if (!not_editable_ && protected_ == 0 && window.canUserEdit) {
            TempContent += '<div class="delete" onclick="deleteListItem(this);"></div>';
        }
        TempContent += '</td>';

        TempContent += '</tr>';
    } else if (Mode == 'chip_data') {
        let chip_ = '';
        if (Value != undefined) {
            if (Values['chip'] !== undefined) {
                chip_ = Values['chip']
            }
        }
        let main_flag_ = '';
        if (Value != undefined) {
            if (Values['main_flag'] !== undefined) {
                main_flag_ = Values['main_flag']
            }
        }

        TempContent = '<tr>';
        TempContent += '<td >';
        TempContent += ' <div className="select" tabIndex="0">';
        TempContent += '<div class="select__control">';
        TempContent += '<div class="select__value">чип</div>';
        TempContent += '<div class="select__arrow"></div>';
        TempContent += ' </div>';
        TempContent += '</div>';
        TempContent += '</td>';

        TempContent += '<td >';
        TempContent += window.canUserEdit ? '<input name="chip"  id="chip" class="chip" value="' + chip_ + '" oninput="validateChipInput(this)" required >' : chip_;
        TempContent += '<label for="chip"></label>';
        TempContent += '</td>';

        TempContent += '<td >';
        TempContent += `<input type="radio" class="main_flag" name="main_flag" id="main_flag" ${main_flag_ == 't' && 'checked'} ${!window.canUserEdit && 'disabled'}/>`
        TempContent += '<label for="main_flag"></label>';
        TempContent += '</td>';

        TempContent += '<td>';
        TempContent += window.canUserEdit ? '<button class="delete" onclick="removeChipRow(this)"></button>' : '';
        TempContent += '</td>';

        TempContent += '</tr>';
    } else if (Mode == 'rabies_vaccinations' || Mode == 'other_vaccinations') {
        let not_editable_ = 0;
        if (Value != undefined) {
            if (Values['not_editable'] !== undefined) {
                not_editable_ = Values['not_editable']
            }
        }
        let protected_ = '';
        if (Value != undefined) {
            if (Values['protected'] !== undefined) {
                protected_ = Values['protected']
            }
        }
        protected_ = protected_ || !window.canUserEdit
        let id_ = '';
        if (Value != undefined) {
            if (Values['id'] !== undefined) {
                id_ = Values['id']
            }
        }
        let spec_id_ = '';
        if (Value != undefined) {
            if (Values['spec_id'] !== undefined) {
                spec_id_ = Values['spec_id']
            }
        }

        //Вакцинация против бешенства

        TempContent = '<tr>';
        // Дата вакцинации
        TempContent += '<td class="date_">';

        if (not_editable_ || protected_ == 1) {
            TempContent += '<input type="hidden" id="not_editable" class="" value="1">';
        } else {
            TempContent += '<input type="hidden" id="not_editable" class="" value="0">';
        }
        TempContent += '<input type="hidden" id="id" class="" value="' + id_ + '">';

        let date_ = '';
        if (Value != undefined) {
            if (Values['date'] !== undefined) {
                date_ = Values['date']
            }
        }
        TempContent += '<input onchange="changeVacDate(this);" type="date" id="date" class="" value="' + date_ + '" ';
        if (not_editable_ || protected_ == 1 || !window.canUserEdit) {
            TempContent += ' disabled';
        }
        TempContent += ' min="1950-01-01" max="' + get_current_date() + '">';
        TempContent += '</td>';

        // Наименование вакцины
        let drug_name_ = '';
        if (Value != undefined) {
            if (Values['drug_name'] !== undefined) {
                drug_name_ = Values['drug_name']
            }
        }
        let drug_id_ = '';
        if (Value != undefined) {
            if (Values['drug_id'] !== undefined) {
                drug_id_ = Values['drug_id']
            }
        }

        TempContent += '<td class="drug_">';
        if (protected_ == 1 || !window.canUserEdit) {
            TempContent += drug_name_;
        } else {
            if (ArrayVaccines != undefined) {
                TempContent += `<select id="drug" class="drug" onchange="changeDrug(this);">`;
                TempContent += '<option value="">Не выбран</option>';
                if (ArrayVaccines.length > 0) {
                    for (let i = 0; i <= ArrayVaccines.length - 1; i++) {
                        TempContent += '<option ';
                        if (drug_id_ == ArrayVaccines[i].id) {
                            TempContent += 'selected ';
                        }
                        TempContent += 'value="' + ArrayVaccines[i].id + '" producer="' + ArrayVaccines[i].producer + '">' + ArrayVaccines[i].name + '</option>';
                    }
                }
                TempContent += '</select>';
            }
        }

        TempContent += '</td>';

        // Производитель
        TempContent += '<td class="producer_name">';
        let producer_ = '';
        if (Value != undefined) {
            if (Values['producer_name'] !== undefined) {
                producer_ = Values['producer_name']
            }
        }
        TempContent += '<div class="producer">' + producer_ + '</div>';
        TempContent += '</td>';

        let is_out_org_ = '';
        if (Value != undefined) {
            if (Values['is_out_org'] !== undefined) {
                is_out_org_ = Values['is_out_org']
            }
        }
        let organization_ = '';
        if (Value != undefined) {
            if (Values['organization'] !== undefined) {
                organization_ = Values['organization']
            }
        }
        let organization_id_ = '';
        if (Value != undefined) {
            if (Values['organization_id'] !== undefined) {
                organization_id_ = Values['organization_id']
            }
        }
        let organization_name_ = '';
        if (Value != undefined) {
            if (Values['organization_name'] !== undefined) {
                organization_name_ = Values['organization_name']
            }
        }

        // Организация
        TempContent += '<td>';
        TempContent += '<div class="organization1"';
        if (is_out_org_ == 1) {
            TempContent += ' style="display: block;"';
        } else {
            TempContent += ' style="display: none;"';
        }
        TempContent += '>';
        if (ArrayOrganizations != undefined) {
            TempContent += '<select id="organization" class="organization">';
            TempContent += '<option value="">Не выбран</option>';
            if (ArrayOrganizations.length > 0) {
                for (let i = 0; i <= ArrayOrganizations.length - 1; i++) {
                    TempContent += '<option ';
                    if (organization_id_ == ArrayOrganizations[i].id) {
                        TempContent += 'selected ';
                    }
                    TempContent += 'value="' + ArrayOrganizations[i].id + '">' + ArrayOrganizations[i].name + '</option>';
                }
            }
            TempContent += '</select>';
        }
        TempContent += '</div>';
        //
        TempContent += '<div class="organization2"';
        if (is_out_org_ == 0) {
            TempContent += ' style="display: block;"';
        } else {
            TempContent += ' style="display: none;"';
        }
        TempContent += '>';

        if (organization_ || organization_name_) {
            if (protected_ == 1 || !window.canUserEdit) {
                TempContent += '' + organization_ + '';
            } else {
                TempContent += '' + organization_name_ + '';
            }
        } else {
            TempContent += '' + currentOrganzationTitle + '';
        }

        TempContent += '</div>';

        TempContent += '</td>';
        // Организация

        // ФИО врача
        TempContent += '<td>';
        TempContent += '<div class="specialist1"';
        if (is_out_org_ == 0 && protected_ == 0) {
            TempContent += ' style="display: block;"';
        } else {
            TempContent += ' style="display: none;"';
        }
        TempContent += '>';
        let specialist_ = '';
        if (Value != undefined) {
            if (Values['specialist'] !== undefined) {
                specialist_ = Values['specialist']
            }
        }
        if (!window.canUserEdit) TempContent += `${specialist_}`
        else if (ArraySpecs != undefined) {
            TempContent += '<select id="specialist">';
            TempContent += '<option value="">Не выбран</option>';
            if (ArraySpecs.length > 0) {
                for (let i = 0; i <= ArraySpecs.length - 1; i++) {
                    TempContent += '<option ';
                    if (spec_id_ == ArraySpecs[i].id) {
                        TempContent += 'selected ';
                    }
                    TempContent += 'value="' + ArraySpecs[i].id + '">' + ArraySpecs[i].name + '</option>';
                }
            }
            TempContent += '</select>';
        }
        TempContent += '</div>';

        TempContent += '<div class="specialist2"';
        if (is_out_org_ == 1 || protected_ == 1) {
            TempContent += ' style="display: block;"';
        } else {
            TempContent += ' style="display: none;"';
        }
        TempContent += '>';

        TempContent += '' + specialist_ + '';

        TempContent += '</div>';

        TempContent += '</td>';
        // ФИО врача

        // Номер партии/серии
        TempContent += '<td>';
        let batch_ = '';
        if (Value != undefined) {
            if (Values['batch'] !== undefined) {
                batch_ = Values['batch']
            }
        }
        TempContent += '<input type="text" id="batch" class="" value="' + batch_ + '"';
        if (not_editable_ || protected_ == 1 || !window.canUserEdit) {
            TempContent += ' disabled';
        }
        TempContent += ' autocomplete="off">';
        TempContent += '</td>';

        // Срок годности
        TempContent += '<td style="width: 150px;">';
        let expiry_date_ = '';
        if (Value != undefined) {
            if (Values['expiry_date'] !== undefined) {
                expiry_date_ = Values['expiry_date']
            }
        }
        TempContent += '<input type="date" id="expiry_date" class="" value="' + expiry_date_ + '"';
        if (not_editable_ || protected_ == 1 || !window.canUserEdit) {
            TempContent += ' disabled';
        }
        TempContent += ' autocomplete="off">';
        TempContent += '</td>';

        // Действительно до
        TempContent += '<td style="width: 150px;">';
        let valid_until_ = '';
        if (Value != undefined) {
            if (Values['valid_until'] !== undefined) {
                valid_until_ = Values['valid_until']
            }
        }
        TempContent += '<input type="date" id="valid_until" class="" value="' + valid_until_ + '"';
        if (not_editable_ || protected_ == 1 || !window.canUserEdit) {
            TempContent += ' disabled';
        }
        TempContent += ' autocomplete="off">';
        TempContent += '</td>';

        // Сторонняя организация
        TempContent += '<td style="padding-top: 0px;">';
        TempContent += '<label class="checkbox_container">';
        TempContent += '<input type="checkbox" name="is_out_org" id="is_out_org" ';
        if (is_out_org_ == 1) {
            TempContent += 'checked ';
        }
        if (not_editable_ || protected_ == 1 || !window.canUserEdit) {
            TempContent += 'disabled ';
        }
        TempContent += 'value="1" onclick="changeIsOutOrg(this);">';
        TempContent += '<span class="checkbox_checkmark"></span>';
        TempContent += '</label>';
        TempContent += '</td>';

        TempContent += '<td style="width: 32px;">';
        if (!not_editable_ && protected_ == 0 && window.canUserEdit) {
            TempContent += '<div class="delete" onclick="deleteListItem(this);"></div>';
        }
        TempContent += '</td>';

        TempContent += '</tr>';
    } else if (Mode == 'ectoparasites') {
        //Обработка против эктопаразитов

        let not_editable_ = 0;
        if (Value != undefined) {
            if (Values['not_editable'] !== undefined) {
                not_editable_ = Values['not_editable']
            }
        }
        not_editable_ = not_editable_ || !window.canUserEdit
        let protected_ = '';
        if (Value != undefined) {
            if (Values['protected'] !== undefined) {
                protected_ = Values['protected']
            }
        }
        protected_ = protected_ || !window.canUserEdit
        let id_ = '';
        if (Value != undefined) {
            if (Values['id'] !== undefined) {
                id_ = Values['id']
            }
        }
        let spec_id_ = '';
        if (Value != undefined) {
            if (Values['spec_id'] !== undefined) {
                spec_id_ = Values['spec_id']
            }
        }

        // Дата обработки
        // Наименование препарата
        // Производитель
        // Доза
        // Организация
        // ФИО врача
        // Срок годности
        // Действительно до

        TempContent = '<tr>';
        // Дата обработки
        TempContent += '<td style="width: 150px;">';
        if (not_editable_ || protected_ == 1) {
            TempContent += '<input type="hidden" id="not_editable" class="" value="1">';
        } else {
            TempContent += '<input type="hidden" id="not_editable" class="" value="0">';
        }

        TempContent += '<input type="hidden" id="id" class="" value="' + id_ + '">';

        let date_ = '';
        if (Value != undefined) {
            if (Values['date'] !== undefined) {
                date_ = Values['date']
            }
        }
        TempContent += '<input onchange="changeVacDate(this);" type="date" id="date" class="" value="' + date_ + '" style="width: 150px;"';
        if (not_editable_ || protected_ == 1) {
            TempContent += ' disabled';
        }
        TempContent += ' min="1950-01-01" max="' + get_current_date() + '">';
        TempContent += '</td>';

        // Наименование препарата
        let drug_name_ = '';
        if (Value != undefined) {
            if (Values['drug_name'] !== undefined) {
                drug_name_ = Values['drug_name']
            }
        }
        let drug_id_ = '';
        if (Value != undefined) {
            if (Values['drug_id'] !== undefined) {
                drug_id_ = Values['drug_id']
            }
        }

        TempContent += '<td class="drug_">';
        if (protected_ == 1 || !window.canUserEdit) {
            TempContent += drug_name_;
        } else {
            if (ArrayDrugs != undefined) {
                TempContent += '<select id="drug" class="drug" onchange="changeDrug(this);">';
                TempContent += '<option value="">Не выбран</option>';
                if (ArrayDrugs.length > 0) {
                    for (let i = 0; i <= ArrayDrugs.length - 1; i++) {
                        TempContent += '<option ';
                        if (drug_id_ == ArrayDrugs[i].id) {
                            TempContent += 'selected ';
                        }
                        TempContent += 'value="' + ArrayDrugs[i].id + '" producer="' + ArrayDrugs[i].producer + '">' + ArrayDrugs[i].name + '</option>';
                    }
                }
                TempContent += '</select>';
            }
        }
        TempContent += '</td>';

        // Производитель
        TempContent += '<td style="width: 200px;">';
        let producer_ = '';
        if (Value != undefined) {
            if (Values['producer_name'] !== undefined) {
                producer_ = Values['producer_name']
            }
        }
        TempContent += '<div class="producer">' + producer_ + '</div>';
        TempContent += '</td>';

        // Организация
        TempContent += '<td>';
        let organization_ = '';
        if (Value != undefined) {
            if (Values['organization'] !== undefined) {
                organization_ = Values['organization']
            }
        }
        let organization_name_ = '';
        if (Value != undefined) {
            if (Values['organization_name'] !== undefined) {
                organization_name_ = Values['organization_name']
            }
        }
        if (organization_) {
            TempContent += '' + organization_ + '';
        } else {
            TempContent += '' + currentOrganzationTitle + '';
        }
        TempContent += '</td>';

        // ФИО врача
        TempContent += '<td>';
        let specialist_ = '';
        if (Value != undefined) {
            if (Values['specialist'] !== undefined) {
                specialist_ = Values['specialist']
            }
        }
        if (protected_ == 1 || !window.canUserEdit) {
            TempContent += specialist_;
        } else {
            if (ArraySpecs != undefined) {
                TempContent += '<select id="specialist">';
                TempContent += '<option value="">Не выбран</option>';
                if (ArraySpecs.length > 0) {
                    for (let i = 0; i <= ArraySpecs.length - 1; i++) {
                        TempContent += '<option ';
                        if (spec_id_ == ArraySpecs[i].id) {
                            TempContent += 'selected ';
                        }
                        TempContent += 'value="' + ArraySpecs[i].id + '">' + ArraySpecs[i].name + '</option>';
                    }
                }
                TempContent += '</select>';
            }
        }
        TempContent += '</td>';

        // Доза
        TempContent += '<td style="width: 150px;">';
        let dose_ = '';
        if (Value != undefined) {
            if (Values['dose'] !== undefined) {
                dose_ = Values['dose']
            }
        }
        TempContent += '<input type="number" min="0.01" max="200" step="0.01" id="dose" value="' + dose_ + '" style="width: 150px;"';
        if (not_editable_ || protected_ == 1) {
            TempContent += ' disabled';
        }
        TempContent += '>';
        TempContent += '</td>';

        // Срок годности
        TempContent += '<td style="width: 150px;">';
        let expiry_date_ = '';
        if (Value != undefined) {
            if (Values['expiry_date'] !== undefined) {
                expiry_date_ = Values['expiry_date']
            }
        }
        TempContent += '<input type="date" id="expiry_date" class="" value="' + expiry_date_ + '" style="width: 150px;"';
        if (not_editable_ || protected_ == 1) {
            TempContent += ' disabled';
        }
        TempContent += '>';
        TempContent += '</td>';

        // Действительно до
        TempContent += '<td style="width: 150px;">';
        let valid_until_ = '';
        if (Value != undefined) {
            if (Values['valid_until'] !== undefined) {
                valid_until_ = Values['valid_until']
            }
        }
        TempContent += '<input type="date" id="valid_until" class="" value="' + valid_until_ + '" style="width: 150px;"';
        if (not_editable_ || protected_ == 1) {
            TempContent += ' disabled';
        }
        TempContent += '>';
        TempContent += '</td>';

        TempContent += '<td style="width: 32px;">';
        if (!not_editable_ && protected_ == 0) {
            TempContent += '<div class="delete" onclick="deleteListItem(this);"></div>';
        }
        TempContent += '</td>';

        TempContent += '</tr>';

    } else if (Mode == 'dehelmintization') {
        //Дегельминтизация

        //Наименование препарата
        //Производитель
        //Сторонняя организация
        //Организация
        //Дата обработки
    }

    //$("#"+Id).closest('tr').after(TempContent);
    //$('#'+Id).append(TempContent);

    $("#" + Id + ' tr:last').after(TempContent);
    if (Mode == 'health') {
        let id_ = '';
        if (Value != undefined) {
            if (Values['id'] !== undefined) {
                id_ = Values['id']
            }
        }

        if (!id_) {
            document.getElementsByClassName('anamnesis')[document.getElementsByClassName('anamnesis').length - 1].classList.add('anamnesis' + document.getElementsByClassName('anamnesis').length - 1);
            var options_anamnesis = {
                class: 'anamnesis' + document.getElementsByClassName('anamnesis').length - 1,
                mode: 'normal',
                new_tags: 0,
                text_tags: 1,
                request_min: 2,
                request_title: 'title',
                response_id: 'rec_id',
                response_title: 'rec_title',
                width: '100%',
                height: 350,
                max_tags_win: 10,
                url: "?action=anamnesiss&mode=xml"
            };
            new JxTag(options_anamnesis);

            for (i = 0; i <= document.getElementsByClassName('anamnesis').length - 1; i++) {
                document.getElementsByClassName('anamnesis')[i].className = 'anamnesis';
                document.getElementsByClassName('anamnesis')[i].classList.add('anamnesis' + i);
            }
        }
    }


    $('.drug').multiselect({
        enableClickableOptGroups: true,
        enableFiltering: true,
        enableCaseInsensitiveFiltering: true,
        nonSelectedText: 'Выберите...',
        allSelectedText: "Выбраны все",
        nSelectedText: "выбрано",
        filterPlaceholder: 'Выбрать запись...'
    });
    $('.organization').multiselect({
        enableClickableOptGroups: true,
        enableFiltering: true,
        enableCaseInsensitiveFiltering: true,
        nonSelectedText: 'Выберите...',
        allSelectedText: "Выбраны все",
        nSelectedText: "выбрано",
        filterPlaceholder: 'Выбрать запись...'
    });

    //после прорисовки
    if (Mode == 'health') {
        if (Value != undefined) {
            if (Values['status'] !== undefined) {
                $("#" + Id + " tr:last #status").val(Values['status']);
            }
        }
    }
}

function changeDrug(element) {
    $(element).parent().parent().parent().find(".producer").html($('option:selected', element).attr("producer"));
}

function changeIsOutOrgList(element) {
    if ($(element).prop("checked") == true) {
        $("#organization_").show();
        $("#specialist_").hide();
    } else {
        $("#organization_").hide();
        $("#specialist_").show();
    }
}

function changeVacDate(element) {
    if ($(element).val()) {
        let vac_date = new Date($(element).val());

        if ($(element).parent().parent().find("#valid_until").val() == '') {
            $(element).parent().parent().find("#valid_until").val((vac_date.getFullYear() + 1) + '-' + (vac_date.getMonth() + 1) + '-' + (vac_date.getDate() < 10 ? '0' + vac_date.getDate() : vac_date.getDate()));
        }
    }
}

function changeExpireVacDate(element) {
    if ($(element).val()) {
        let expiry_date = new Date($(element).val());

        if ($(element).parent().parent().find("#expiry_date").val() == '') {
            $(element).parent().parent().find("#expiry_date").val((expiry_date.getFullYear() + 1) + '-' + (expiry_date.getMonth() + 1) + '-' + (expiry_date.getDate() < 10 ? '0' + expiry_date.getDate() : expiry_date.getDate()));
        }
    }
}

function changeIsOutOrg(element) {
    if ($(element).prop("checked") == true) {
        //показываем список организаций
        $(element).parent().parent().parent().find(".organization1").show();
        $(element).parent().parent().parent().find(".organization2").hide();

        $(element).parent().parent().parent().find(".specialist1").hide();
        //$(element).parent().parent().parent().find(".specialist2").show();
    }
    if ($(element).prop("checked") == false) {
        //показываем текущую организаций
        $(element).parent().parent().parent().find(".organization2").show();
        $(element).parent().parent().parent().find(".organization1").hide();

        $(element).parent().parent().parent().find(".specialist1").show();
        $(element).parent().parent().parent().find(".specialist2").hide();
    }
    //$(element).parent().parent().parent().find(".producer").html($('option:selected', element).attr("producer"));
}

function deleteListItem(element) {
    $(element).parent().parent().remove();

    for (i = 0; i <= document.getElementsByClassName('anamnesis').length - 1; i++) {
        document.getElementsByClassName('anamnesis')[i].className = 'anamnesis';
        document.getElementsByClassName('anamnesis')[i].classList.add('anamnesis' + i);
    }
}

function getFiasTokken(add = null) {
    jQuery.ajax({
        'async': true,
        'global': false,
        'cache': false,
        'type': 'GET',
        'dataType': 'xml',
        'data': 'action=fias_tokken&mode=xml',
        'url': "index.php",
        'beforeSend': function () {

        },
        'success': function (Data) {
            fiasTokken = $(Data).find('fias_tokken').text();
            if (add == 1) {
                var options = {
                    name: 'catching_address',
                    mode: 'normal',
                    new_tags: 0,
                    max_tags: 1,//убрать поле input в этом случае
                    request_min: 3,
                    request_title: 'title',
                    response_id: 'rec_id',
                    response_title: 'rec_title',
                    width: '100%',
                    height: 250,
                    max_tags_win: 10,
                    url: "?action=addresses&tokken=" + fiasTokken + "&mode=xml"
                };
                catching_address_input = new JxTag(options);
            }
        }
    });
}

function addChipRow() {
    $("#message").hide();
    event.preventDefault();

    let chips = $(".chip").map(function () {
        return $(this);
    });
    const addButton = document.getElementById('chip_add_button');
    if (chips.length >= 2) {
        addButton.disabled = true;
        return true
    } else {
        addButton.disabled = false;
    }

    const tableBody = document.getElementById('table-body');
    const rowCount = tableBody.rows.length + 1;

    const newRow = document.createElement('tr');
    newRow.className = 'v2-visits__table-row';
    newRow.innerHTML = `
        <td>
        <div class="select" tabIndex="0">
        <div class="select__control">
        <div class="select__value">чип</div>
        <div class="select__arrow"></div>
        </div>
        </div>
        </td>
        <td>
        <input id="chip" name="chip" class="chip" oninput="validateChipInput(this)" required value="">
        <label for="chip"></label>
        </td>

        <td>
        <input type="radio" class="main_flag" name="main_flag" id="main_flag" >
        <label for="main_flag"></label>
        </td>

        <td>
        <button class="delete" style="border: 0px" onclick="removeChipRow(this)"></button>
        </td>
        
        </tr>
`;
    tableBody.appendChild(newRow);
}

function removeChipRow(button) {
    $("#message").hide();
    const row = button.closest('tr');
    row.parentNode.removeChild(row);
    let chips = $(".chip").map(function () {
        return $(this);
    });
    const addButton = document.getElementById('chip_add_button');
    if (chips.length >= 2) {
        addButton.disabled = true;
        return true
    } else {
        addButton.disabled = false;
    }
}
function validateChipInput(input) {
    // Удаляем все символы, кроме цифр
    input.value = input.value.replace(/[^\d]/g, '');

    // Ограничиваем длину до 15 символов
    if (input.value.length > 15) {
        input.value = input.value.slice(0, 15);
    }
}

loadDocumentTypes();//Загрузка типов документов
loadSpecies();
loadBreeds();
getFiasTokken();


encodeDataToURL = (data) => {
    return Object
        .keys(data)
        .map(value => `${value}=${encodeURIComponent(data[value])}`)
        .join('&');
}
