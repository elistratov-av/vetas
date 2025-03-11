var currentPage = '';
var recs_on_page=3;
var recs_services_num=0;
var recs_counter_specs=0;

function parseSerializedData(serialized) {
    const obj = {};
    const pairs = serialized.split('&');
    pairs.forEach(function (pair) {
        const split = pair.split('=');
        const key = decodeURIComponent(split[0]);
        const value = decodeURIComponent(split[1] || '');
        if (!obj[key]) obj[key] = []
        obj[key].push(value);
    });
    return obj;
}

function ShowFullServicesAllReport(){
	$("#services_all").html("0");

	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'POST',
		'dataType': 'xml',
		'processData': false,
        'contentType': false,
        'data': JSON.stringify(parseSerializedData(jQuery("#form").serialize())),
		'url': "index.php?action=full_services_all_report&mode=xml",
		'beforeSend': function() {
			//показать загрузку
			$('#services_all').addClass('text_loading');
		},
		'success': function (Data) {
			$('#services_all').removeClass('text_loading');
			$("#services_all").html($(Data).find('total').text());
		}
	});
}

function ResetReport(report){
    event.preventDefault()
    $("#services_period").html("0");
    $("#services_all").html("0");
    $("#ListRecs").html("Введите параметры отчёта и нажмите кнопку Применить.");
    if (report !== 'ResetServicesPriceReport'
        && report !== 'ResetVaccinationReport'
        && report !== 'ResetVaccinationShelterReport'
        && report !== 'ResetAnimalDiseaseReport'
        && report !== 'ResetVeterinarySpecialistsReport'
        && report !== 'ResetNotificationReport'
        && report !== 'ResetDashboardReport'){
        // Находим все элементы с классом 'multiselect-all'
        var selectAllButtons = document.querySelectorAll('.multiselect-all');

        // Проходим по каждому элементу и вызываем клик
        selectAllButtons.forEach(function(button){
            // Проверяем, не выбран ли уже пункт "Выбрать все"
            var checkbox = button.querySelector('.form-check-input');
            if (!checkbox.checked) {
                button.click(); // Активируем выбор "Выбрать все"
            }
        });

    }
    else {
        $('.organization').val('').multiselect('refresh');
        $('.doc').val('').multiselect('refresh');
        $('.service').val('').multiselect('refresh');
        $('.type').val('').multiselect('refresh');
        $('.status').val('').multiselect('refresh');
        $('.species').val('').multiselect('refresh');
        $('.area').val('').multiselect('refresh');
        $('.shift').val('').multiselect('refresh');
        $('.channel').val('').multiselect('refresh');
        $('.district').val('').multiselect('refresh');
        $('.organization').val('').multiselect('refresh');
        $('.shelter').val('').multiselect('refresh');
        $('.disease').val('').multiselect('refresh');

        var checkbox = document.getElementById('contagious');
        if(checkbox){ checkbox.checked = false;} // Снимаем отметку с чекбокса

        var textarea = document.getElementById('owners');
        if(textarea){textarea.value = '';}// Очищаем текстовое поле
    }

    // Устанавливаем значения по умолчанию
    $('#date_from').val(new Date().getFullYear() + '-01-01'); // 1 января текущего года
    $('#date_to').val(new Date().toISOString().split('T')[0]); // сегодняшняя дата в формате YYYY-MM-DD

    $('#from').val(new Date().getFullYear() + '-01-01'); // 1 января текущего года
    $('#to').val(new Date().toISOString().split('T')[0]); // сегодняшняя дата в формате YYYY-MM-DD
}

function ResetNotificationReport(report){
    // Устанавливаем значения по умолчанию
    $('#date_from').val(new Date().getFullYear() + '-01-01'); // 1 января текущего года
    $('#date_to').val(new Date().toISOString().split('T')[0]); // сегодняшняя дата в формате YYYY-MM-DD

    $('.species').val('').multiselect('refresh');
    $('.sender').val('').multiselect('refresh');
    $('#owners').removeAttr('value');
}

function ShowServicesPriceReport(page = 1){
	//xls
	let xls=0;
	
	if (typeof event !== 'undefined') {
		event.preventDefault();
		if(event.submitter?.name){
			if(event.submitter.name == 'xls'){
				xls=1;
			}
		}
	}
	//xls

	let date_from = $("#form #date_from").val();
	let date_to = $("#form #date_to").val();

	if(date_from == '' || date_to == ''){
		$("#ListRecs").html('Поля «Дата начала» и «Дата окончания» являются обязательными для заполнения.');
		$("#ListRecs").show();

		return true;
	}

	let date_from_ = new Date(date_from);
	let date_to_ = new Date(date_to);

	if(date_from_ > date_to_){
		$("#ListRecs").html('«Дата начала» периода не может быть больше даты его окончания.');
		$("#ListRecs").show();

		return true;
	}

	if(xls){
        $.ajax({
            'async': true,
            'global': false,
            'cache': false,
            'type': 'POST',
            'xhrFields': {
                responseType: 'blob'
            },
			'processData': false,
			'contentType': false,
			'data': JSON.stringify(parseSerializedData(jQuery("#form").serialize())),
			'url': "index.php?action=services_price_report&xls="+xls+"&mode=xml",

            'beforeSend': function() {
                //показать загрузку
                showLoading('ListRecs');
            },
            'success': function (Data) {
                closeLoading('ListRecs');

                if($(Data).find('message').text() == 'token_invalid'){
                    window.location.href = 'index.php';
                }else{
					let date_from=$("#date_from").val();
					let date_to=$("#date_to").val();

                    var a = document.createElement('a');
                    var url = window.URL.createObjectURL(Data);
                    a.href = url;
                    a.download = 'Отчет по услугам (с '+date_from+' по '+date_to+').xls';
                    document.body.append(a);
                    a.click();
                    a.remove();
                    window.URL.revokeObjectURL(url);

                    TempContent='<div class="p-3">Загрузка файла началась...</div>';
                    document.getElementById('ListRecs').innerHTML=TempContent;
                }
            }
        }); 
    }else{
		jQuery.ajax({
			'async': true,
			'global': false,
			'cache': false,
			'type': 'POST',
			'dataType': 'xml',
			'processData': false,
			'contentType': false,
			'data': JSON.stringify(parseSerializedData(jQuery("#form").serialize())),
			'url': "index.php?action=services_price_report&page="+page+"&mode=xml",
			'beforeSend': function() {
				//показать загрузку
				showLoading('ListRecs');
			},
			'success': function (Recs) {
				closeLoading('ListRecs');

				if($(Recs).find('message').text() == 'token_invalid'){
					window.location.href = 'index.php';
				}else{
					let TempContent='';
					let TempContentHeader='';
					let TempServiceCountAll=0;
					let TempServiceSumAll=0;
					let TempRecsNum=0;

					TempContentHeader+='<tr class="first">';
					TempContentHeader+='<th>№п/п</th>';
					TempContentHeader+='<th>Код</th>';
					TempContentHeader+='<th>Услуги</th>';
					TempContentHeader+='<th>Количество услуг</th>';
					TempContentHeader+='<th>Стоимость оказанных услуг, руб.</th>';
					TempContentHeader+='</tr>';
					
					if($(Recs).find('rec').text()){
						$(Recs).find('rec').each(function(){
							TempContent+='<tr>';
							
							TempContent+='<td>'+(TempRecsNum+1)+'</td>';
							TempContent+='<td>'+$(this).find('rec_cod').text()+'</td>';
							TempContent+='<td>'+$(this).find('rec_service_name').text()+'</td>';
							TempContent+='<td>'+$(this).find('rec_service_count').text()+'</td>';
							TempContent+='<td>'+$(this).find('rec_service_sum').text()+'</td>';
							TempContent+='</tr>';

							TempServiceCountAll=$(this).find('rec_service_count_all').text();
							TempServiceSumAll=$(this).find('rec_service_sum_all').text();

							TempRecsNum++;
						});
					}

					let TempContentTotal='';
					TempContentTotal+='<tr class="total" style="border-top-width: 5px; border-bottom-width: 5px;">';
					TempContentTotal+='<th colspan="2">Итого</th>';
					TempContentTotal+='<td>'+TempRecsNum+'</td>';
					TempContentTotal+='<td>'+TempServiceCountAll+'</td>';
					TempContentTotal+='<td>'+TempServiceSumAll+'</td>';
					TempContentTotal+='</tr>';

					if(TempRecsNum == 0){
						TempContent='<table class="w-100"><tr><td>По периоду нет данных для формирования отчёта.</td></tr></table>';
					}else{
						TempContent='<table class="w-100">'+TempContentHeader+''+TempContentTotal+''+TempContent+''+TempContentTotal+'</table>';

						TempContent+='<div class="pages">';
						
						if(parseInt($(Recs).find('counter').text()) > 0){
							console.log($(Recs).find('pages').text());
							$(Recs).find('pages').find('page').each(function(){
								TempContent+='<div ';
								if(page == $(this).text()){
									TempContent+='class="current" ';
								}
								TempContent+='onclick="ShowServicesPriceReport('+$(this).text()+');">' + $(this).text() + '</div>';
							});
						}
						TempContent+='</div>';
					}
					document.getElementById('ListRecs').innerHTML=TempContent;

					let pos=(parseInt($('.row .search_sub').height())+60);
					$(".report .first th").css({ top : pos });
					$(".report .first2 th").css({ top : (pos+30) });
				}
			}
		});
	}
}

function ShowVaccinationShelterReport(){
    event.preventDefault();
    let xls=0;
    if(event.submitter.name == 'xls'){
        xls=1;
    }

    if(xls){
        $.ajax({
            'async': true,
            'global': false,
            'cache': false,
            'type': 'POST',
            'xhrFields': {
                responseType: 'blob'
            },
			'processData': false,
			'contentType': false,
			'data': JSON.stringify(parseSerializedData(jQuery("#form").serialize())),
			'url': "index.php?action=vaccination_shelter_report&mode=xml&xls=1",
            'beforeSend': function() {
                //показать загрузку
                showLoading('ListRecs');
            },
            'success': function (Data) {
                closeLoading('ListRecs');

                if($(Data).find('message').text() == 'token_invalid'){
                    window.location.href = 'index.php';
                }else{
					let date_from=$("#date_from").val();
					let date_to=$("#date_to").val();

                    var a = document.createElement('a');
                    var url = window.URL.createObjectURL(Data);
                    a.href = url;
                    a.download = 'Охват вакцинации в приютах (с '+date_from+' по '+date_to+').xls';
                    document.body.append(a);
                    a.click();
                    a.remove();
                    window.URL.revokeObjectURL(url);

                    TempContent='<div class="p-3">Загрузка файла началась...</div>';
                    document.getElementById('ListRecs').innerHTML=TempContent;
                }
            }
        }); 
    }else{
        $.ajax({
            'async': true,
            'global': false,
            'cache': false,
            'type': 'POST',
            'dataType': 'xml',
			'processData': false,
			'contentType': false,
			'data': JSON.stringify(parseSerializedData(jQuery("#form").serialize())),
			'url': "index.php?action=vaccination_shelter_report&mode=xml",			
            'beforeSend': function() {
                //показать загрузку
                showLoading('ListRecs');
            },
            'success': function (Recs) {
                closeLoading('ListRecs');
    
                if($(Recs).find('message').text() == 'token_invalid'){
                    window.location.href = 'index.php';
                }else if($(Recs).find('rec').text()){
                        let TempContent='';
                        let TempContentHeader='';
                        let TempRecsNum=0;
    
                        TempContentHeader+='<tr>';
                        TempContentHeader+='<th rowspan="3">Наименование приюта, адрес</th>';
                        TempContentHeader+='<th colspan="2">Поголовье животных содержащихся в приютах</th>';
                        TempContentHeader+='<th colspan="2">Поголовье животных на первый день заданого периода, в т.ч. по видам</th>';
                        TempContentHeader+='<th colspan="6" class="center">Движение животных за выбранный период</th>';
                        TempContentHeader+='<th colspan="2">Поголовье животных на последний день заданного периода, в т.ч. по видам</th>';
                        
						TempContentHeader+='<th colspan="4">Вакцинировано животных против бешенства (Рабикан)</th>';
                        TempContentHeader+='<th colspan="4">Вакцинировано животных против бешенства (другие вакцины)</th>';

                        TempContentHeader+='<th colspan="2">Вакцинировано собак против лептоспироза</th>';
						TempContentHeader+='<th colspan="2">Вакцинировано собак против чумы</th>';
                        TempContentHeader+='</tr>';
    
                        TempContentHeader+='<tr>';
                        TempContentHeader+='<th colspan="2">Всего</th>';
                        TempContentHeader+='<th colspan="2">Всего</th>';
                        TempContentHeader+='<th colspan="2">Прибыло</th>';
                        TempContentHeader+='<th colspan="2">Убыло</th>';
                        TempContentHeader+='<th colspan="2">Пало</th>';
                        TempContentHeader+='<th colspan="2">Всего</th>';
                        
						TempContentHeader+='<th colspan="2">с начала года текущего</th>';
                        TempContentHeader+='<th colspan="2">за выбранный период</th>';
                        TempContentHeader+='<th colspan="2">с начала года текущего</th>';
                        TempContentHeader+='<th colspan="2">за выбранный период</th>';

						TempContentHeader+='<th rowspan="2" class="vertical-rl">с начала года текущего</th>';
                        TempContentHeader+='<th rowspan="2" class="vertical-rl">за выбранный период</th>';
                        TempContentHeader+='<th rowspan="2" class="vertical-rl">с начала года текущего</th>';
                        TempContentHeader+='<th rowspan="2" class="vertical-rl">за выбранный период</th>';
                        
                        TempContentHeader+='</tr>';
    
                        TempContentHeader+='<tr>';
                        TempContentHeader+='<th class="vertical-rl">Собаки</th>';TempContentHeader+='<th class="vertical-rl">Кошки</th>';
                        TempContentHeader+='<th class="vertical-rl">Собаки</th>';TempContentHeader+='<th class="vertical-rl">Кошки</th>';
                        TempContentHeader+='<th class="vertical-rl">Собаки</th>';TempContentHeader+='<th class="vertical-rl">Кошки</th>';
                        TempContentHeader+='<th class="vertical-rl">Собаки</th>';TempContentHeader+='<th class="vertical-rl">Кошки</th>';
                        TempContentHeader+='<th class="vertical-rl">Собаки</th>';TempContentHeader+='<th class="vertical-rl">Кошки</th>';
                        TempContentHeader+='<th class="vertical-rl">Собаки</th>';TempContentHeader+='<th class="vertical-rl">Кошки</th>';

						TempContentHeader+='<th class="vertical-rl">Собаки</th>';TempContentHeader+='<th class="vertical-rl">Кошки</th>';
						TempContentHeader+='<th class="vertical-rl">Собаки</th>';TempContentHeader+='<th class="vertical-rl">Кошки</th>';
						TempContentHeader+='<th class="vertical-rl">Собаки</th>';TempContentHeader+='<th class="vertical-rl">Кошки</th>';
						TempContentHeader+='<th class="vertical-rl">Собаки</th>';TempContentHeader+='<th class="vertical-rl">Кошки</th>';

                        TempContentHeader+='</tr>';
                        
                        let t_all_dog_count=0;
                        let t_all_cat_count=0;
                        let t_firstday_dog_count=0;
                        let t_firstday_cat_count=0;
                        let t_lastday_dog_count=0;
                        let t_lastday_cat_count=0;
                        let t_arrival_dog_count=0;
                        let t_arrival_сat_count=0;
                        let t_death_dog_count=0;
                        let t_death_cat_count=0;
                        let t_left_dog_count=0;
                        let t_left_cat_count=0;
                        let t_rabican_dog_vaccin_year=0;
                        let t_rabican_cat_vaccin_year=0;
                        let t_rabican_dog_vaccin_period=0;
                        let t_rabican_cat_vaccin_period=0;
                        let t_rabies_dog_vaccin_year=0;
                        let t_rabies_cat_vaccin_year=0;
                        let t_rabies_dog_vaccin_period=0;
                        let t_rabies_cat_vaccin_period=0;
                        let t_plague_dog_vaccin_year=0;
                        let t_plague_dog_vaccin_period=0;
                        let t_lepra_dog_vaccin_year=0;
                        let t_lepra_dog_vaccin_period=0;
    
                        let o_all_dog_count=0;
                        let o_all_cat_count=0;
                        let o_firstday_dog_count=0;
                        let o_firstday_cat_count=0;
                        let o_lastday_dog_count=0;
                        let o_lastday_cat_count=0;
                        let o_arrival_dog_count=0;
                        let o_arrival_сat_count=0;
                        let o_death_dog_count=0;
                        let o_death_cat_count=0;
                        let o_left_dog_count=0;
                        let o_left_cat_count=0;
                        let o_rabican_dog_vaccin_year=0;
                        let o_rabican_cat_vaccin_year=0;
                        let o_rabican_dog_vaccin_period=0;
                        let o_rabican_cat_vaccin_period=0;
                        let o_rabies_dog_vaccin_year=0;
                        let o_rabies_cat_vaccin_year=0;
                        let o_rabies_dog_vaccin_period=0;
                        let o_rabies_cat_vaccin_period=0;
                        let o_plague_dog_vaccin_year=0;
                        let o_plague_dog_vaccin_period=0;
                        let o_lepra_dog_vaccin_year=0;
                        let o_lepra_dog_vaccin_period=0;
    
                        let area='';
                        if($(Recs).find('rec').text()){
                            $(Recs).find('rec').each(function(){
                                if(area != $(this).find('rec_area_name').text()){
                                    TempContent+='<tr class="area"><th colspan="15">'+$(this).find('rec_area_name').text()+'</th></tr>';//округ
                                    area = $(this).find('rec_area_name').text();
                                }
    
                                TempContent+='<tr>';
                                TempContent+='<td>'+$(this).find('rec_shelter_name').text()+'';
                                if($(this).find('rec_shelter_address').text()){
                                    TempContent+=', '+$(this).find('rec_shelter_address').text()+'';
                                }
    
                                TempContent+='</td>';
                                TempContent+='<td>'+$(this).find('rec_all_dog_count').text()+'</td>';
                                TempContent+='<td>'+$(this).find('rec_all_cat_count').text()+'</td>';
                                TempContent+='<td>'+$(this).find('rec_firstday_dog_count').text()+'</td>';
                                TempContent+='<td>'+$(this).find('rec_firstday_cat_count').text()+'</td>';
    
                                TempContent+='<td>'+$(this).find('rec_arrival_dog_count').text()+'</td>';
                                TempContent+='<td>'+$(this).find('rec_arrival_сat_count').text()+'</td>';
                                TempContent+='<td>'+$(this).find('rec_left_dog_count').text()+'</td>';
                                TempContent+='<td>'+$(this).find('rec_left_cat_count').text()+'</td>';
                                TempContent+='<td>'+$(this).find('rec_death_dog_count').text()+'</td>';
                                TempContent+='<td>'+$(this).find('rec_death_cat_count').text()+'</td>';
    
                                TempContent+='<td>'+$(this).find('rec_lastday_dog_count').text()+'</td>';
                                TempContent+='<td>'+$(this).find('rec_lastday_cat_count').text()+'</td>';
    
                                TempContent+='<td>'+$(this).find('rec_rabican_dog_vaccin_year').text()+'</td>';
								TempContent+='<td>'+$(this).find('rec_rabican_cat_vaccin_year').text()+'</td>';
								TempContent+='<td>'+$(this).find('rec_rabican_dog_vaccin_period').text()+'</td>';
                                TempContent+='<td>'+$(this).find('rec_rabican_cat_vaccin_period').text()+'</td>';
    
                                TempContent+='<td>'+$(this).find('rec_rabies_dog_vaccin_year').text()+'</td>';
								TempContent+='<td>'+$(this).find('rec_rabies_cat_vaccin_year').text()+'</td>';
                                TempContent+='<td>'+$(this).find('rec_rabies_dog_vaccin_period').text()+'</td>';
                                TempContent+='<td>'+$(this).find('rec_rabies_cat_vaccin_period').text()+'</td>';
    
                                TempContent+='<td>'+$(this).find('rec_lepra_dog_vaccin_year').text()+'</td>';
                                TempContent+='<td>'+$(this).find('rec_lepra_dog_vaccin_period').text()+'</td>';
                                TempContent+='<td>'+$(this).find('rec_plague_dog_vaccin_year').text()+'</td>';
                                TempContent+='<td>'+$(this).find('rec_plague_dog_vaccin_period').text()+'</td>';
                                
                                TempContent+='</tr>';
    
                                t_all_dog_count=t_all_dog_count+parseInt($(this).find('rec_all_dog_count').text());
                                t_all_cat_count=t_all_cat_count+parseInt($(this).find('rec_all_cat_count').text());
                                t_firstday_dog_count=t_firstday_dog_count+parseInt($(this).find('rec_firstday_dog_count').text());
                                t_firstday_cat_count=t_firstday_cat_count+parseInt($(this).find('rec_firstday_cat_count').text());
                                t_lastday_dog_count=t_lastday_dog_count+parseInt($(this).find('rec_lastday_dog_count').text());
                                t_lastday_cat_count=t_lastday_cat_count+parseInt($(this).find('rec_lastday_cat_count').text());
                                t_arrival_dog_count=t_arrival_dog_count+parseInt($(this).find('rec_arrival_dog_count').text());
                                t_arrival_сat_count=t_arrival_сat_count+parseInt($(this).find('rec_arrival_сat_count').text());
                                t_death_dog_count=t_death_dog_count+parseInt($(this).find('rec_death_dog_count').text());
                                t_death_cat_count=t_death_cat_count+parseInt($(this).find('rec_death_cat_count').text());
                                t_left_dog_count=t_left_dog_count+parseInt($(this).find('rec_left_dog_count').text());
                                t_left_cat_count=t_left_cat_count+parseInt($(this).find('rec_left_cat_count').text());
                                
								t_rabican_dog_vaccin_year=t_rabican_dog_vaccin_year+parseInt($(this).find('rec_rabican_dog_vaccin_year').text());
                                t_rabican_cat_vaccin_year=t_rabican_cat_vaccin_year+parseInt($(this).find('rec_rabican_cat_vaccin_year').text());
                                t_rabican_dog_vaccin_period=t_rabican_dog_vaccin_period+parseInt($(this).find('rec_rabican_dog_vaccin_period').text());
                                t_rabican_cat_vaccin_period=t_rabican_cat_vaccin_period+parseInt($(this).find('rec_rabican_cat_vaccin_period').text());

                                t_rabies_dog_vaccin_year=t_rabies_dog_vaccin_year+parseInt($(this).find('rec_rabies_dog_vaccin_year').text());
                                t_rabies_cat_vaccin_year=t_rabies_cat_vaccin_year+parseInt($(this).find('rec_rabies_cat_vaccin_year').text());
                                t_rabies_dog_vaccin_period=t_rabies_dog_vaccin_period+parseInt($(this).find('rec_rabies_dog_vaccin_period').text());
                                t_rabies_cat_vaccin_period=t_rabies_cat_vaccin_period+parseInt($(this).find('rec_rabies_cat_vaccin_period').text());
                                t_plague_dog_vaccin_year=t_plague_dog_vaccin_year+parseInt($(this).find('rec_plague_dog_vaccin_year').text());
                                t_plague_dog_vaccin_period=t_plague_dog_vaccin_period+parseInt($(this).find('rec_plague_dog_vaccin_period').text());
                                t_lepra_dog_vaccin_year=t_lepra_dog_vaccin_year+parseInt($(this).find('rec_lepra_dog_vaccin_year').text());
                                t_lepra_dog_vaccin_period=t_lepra_dog_vaccin_period+parseInt($(this).find('rec_lepra_dog_vaccin_period').text());
    
                                o_all_dog_count=o_all_dog_count+parseInt($(this).find('rec_all_dog_count').text());
                                o_all_cat_count=o_all_cat_count+parseInt($(this).find('rec_all_cat_count').text());
                                o_firstday_dog_count=o_firstday_dog_count+parseInt($(this).find('rec_firstday_dog_count').text());
                                o_firstday_cat_count=o_firstday_cat_count+parseInt($(this).find('rec_firstday_cat_count').text());
                                o_lastday_dog_count=o_lastday_dog_count+parseInt($(this).find('rec_lastday_dog_count').text());
                                o_lastday_cat_count=o_lastday_cat_count+parseInt($(this).find('rec_lastday_cat_count').text());
                                o_arrival_dog_count=o_arrival_dog_count+parseInt($(this).find('rec_arrival_dog_count').text());
                                o_arrival_сat_count=o_arrival_сat_count+parseInt($(this).find('rec_arrival_сat_count').text());
                                o_death_dog_count=o_death_dog_count+parseInt($(this).find('rec_death_dog_count').text());
                                o_death_cat_count=o_death_cat_count+parseInt($(this).find('rec_death_cat_count').text());
                                o_left_dog_count=o_left_dog_count+parseInt($(this).find('rec_left_dog_count').text());
                                o_left_cat_count=o_left_cat_count+parseInt($(this).find('rec_left_cat_count').text());
                                
								o_rabican_dog_vaccin_year=o_rabican_dog_vaccin_year+parseInt($(this).find('rec_rabican_dog_vaccin_year').text());
                                o_rabican_cat_vaccin_year=o_rabican_cat_vaccin_year+parseInt($(this).find('rec_rabican_cat_vaccin_year').text());
                                o_rabican_dog_vaccin_period=o_rabican_dog_vaccin_period+parseInt($(this).find('rec_rabican_dog_vaccin_period').text());
                                o_rabican_cat_vaccin_period=o_rabican_cat_vaccin_period+parseInt($(this).find('rec_rabican_cat_vaccin_period').text());

                                o_rabies_dog_vaccin_year=o_rabies_dog_vaccin_year+parseInt($(this).find('rec_rabies_dog_vaccin_year').text());
                                o_rabies_cat_vaccin_year=o_rabies_cat_vaccin_year+parseInt($(this).find('rec_rabies_cat_vaccin_year').text());
                                o_rabies_dog_vaccin_period=o_rabies_dog_vaccin_period+parseInt($(this).find('rec_rabies_dog_vaccin_period').text());
                                o_rabies_cat_vaccin_period=o_rabies_cat_vaccin_period+parseInt($(this).find('rec_rabies_cat_vaccin_period').text());
                                o_plague_dog_vaccin_year=o_plague_dog_vaccin_year+parseInt($(this).find('rec_plague_dog_vaccin_year').text());
                                o_plague_dog_vaccin_period=o_plague_dog_vaccin_period+parseInt($(this).find('rec_plague_dog_vaccin_period').text());
                                o_lepra_dog_vaccin_year=o_lepra_dog_vaccin_year+parseInt($(this).find('rec_lepra_dog_vaccin_year').text());
                                o_lepra_dog_vaccin_period=o_lepra_dog_vaccin_period+parseInt($(this).find('rec_lepra_dog_vaccin_period').text());
    
                                if(area != $(this).next().find('rec_area_name').text()){
                                    TempContent+='<tr class="area" style="border-bottom-width: 4px;">';
                                    TempContent+='<th>Итого по округу</th>';
                                    TempContent+='<td>'+o_all_dog_count+'</td>';
                                    TempContent+='<td>'+o_all_cat_count+'</td>';
                                    TempContent+='<td>'+o_firstday_dog_count+'</td>';
                                    TempContent+='<td>'+o_firstday_cat_count+'</td>';
    
                                    TempContent+='<td>'+o_arrival_dog_count+'</td>';
                                    TempContent+='<td>'+o_arrival_сat_count+'</td>';
									TempContent+='<td>'+o_left_dog_count+'</td>';
                                    TempContent+='<td>'+o_left_cat_count+'</td>';
                                    TempContent+='<td>'+o_death_dog_count+'</td>';
                                    TempContent+='<td>'+o_death_cat_count+'</td>';
                                    
									TempContent+='<td>'+o_lastday_dog_count+'</td>';
                                    TempContent+='<td>'+o_lastday_cat_count+'</td>';
    
                                    TempContent+='<td>'+o_rabican_dog_vaccin_year+'</td>';
									TempContent+='<td>'+o_rabican_cat_vaccin_year+'</td>';
									TempContent+='<td>'+o_rabican_dog_vaccin_period+'</td>';
                                    TempContent+='<td>'+o_rabican_cat_vaccin_period+'</td>';
    
                                    TempContent+='<td>'+o_rabies_dog_vaccin_year+'</td>';
                                    TempContent+='<td>'+o_rabies_cat_vaccin_year+'</td>';
									TempContent+='<td>'+o_rabies_dog_vaccin_period+'</td>';
                                    TempContent+='<td>'+o_rabies_cat_vaccin_period+'</td>';

									TempContent+='<td>'+o_lepra_dog_vaccin_year+'</td>';
                                    TempContent+='<td>'+o_lepra_dog_vaccin_period+'</td>';
    
                                    TempContent+='<td>'+o_plague_dog_vaccin_year+'</td>';
                                    TempContent+='<td>'+o_plague_dog_vaccin_period+'</td>';
                                    TempContent+='</tr>';
    
                                    o_all_dog_count=0;
                                    o_all_cat_count=0;
                                    o_firstday_dog_count=0;
                                    o_firstday_cat_count=0;
                                    o_lastday_dog_count=0;
                                    o_lastday_cat_count=0;
                                    o_arrival_dog_count=0;
                                    o_arrival_сat_count=0;
                                    o_death_dog_count=0;
                                    o_death_cat_count=0;
                                    o_left_dog_count=0;
                                    o_left_cat_count=0;
                                    o_rabican_dog_vaccin_year=0;
                                    o_rabican_cat_vaccin_year=0;
                                    o_rabican_dog_vaccin_period=0;
                                    o_rabican_cat_vaccin_period=0;
                                    o_rabies_dog_vaccin_year=0;
                                    o_rabies_cat_vaccin_year=0;
                                    o_rabies_dog_vaccin_period=0;
                                    o_rabies_cat_vaccin_period=0;
                                    o_plague_dog_vaccin_year=0;
                                    o_plague_dog_vaccin_period=0;
                                    o_lepra_dog_vaccin_year=0;
                                    o_lepra_dog_vaccin_period=0;
                                }
    
                                TempRecsNum++;
                            });
                        }
    
                        let TempContentTotal='';
                        TempContentTotal+='<tr class="total" style="border-top-width: 5px; border-bottom-width: 5px;">';
                        TempContentTotal+='<th>Итого</th>';
                        TempContentTotal+='<td>'+t_all_dog_count+'</td>';
                        TempContentTotal+='<td>'+t_all_cat_count+'</td>';
                        TempContentTotal+='<td>'+t_firstday_dog_count+'</td>';
                        TempContentTotal+='<td>'+t_firstday_cat_count+'</td>';
    
						TempContentTotal+='<td>'+t_arrival_dog_count+'</td>';
                        TempContentTotal+='<td>'+t_arrival_сat_count+'</td>';
						TempContentTotal+='<td>'+t_left_dog_count+'</td>';
                        TempContentTotal+='<td>'+t_left_cat_count+'</td>';
                        TempContentTotal+='<td>'+t_death_dog_count+'</td>';
                        TempContentTotal+='<td>'+t_death_cat_count+'</td>';

						TempContentTotal+='<td>'+t_lastday_dog_count+'</td>';
                        TempContentTotal+='<td>'+t_lastday_cat_count+'</td>'; 
    
                        TempContentTotal+='<td>'+t_rabican_dog_vaccin_year+'</td>';
						TempContentTotal+='<td>'+t_rabican_cat_vaccin_year+'</td>';
						TempContentTotal+='<td>'+t_rabican_dog_vaccin_period+'</td>';
                        TempContentTotal+='<td>'+t_rabican_cat_vaccin_period+'</td>';
    
                        TempContentTotal+='<td>'+t_rabies_dog_vaccin_year+'</td>';
						TempContentTotal+='<td>'+t_rabies_cat_vaccin_year+'</td>';
                        TempContentTotal+='<td>'+t_rabies_dog_vaccin_period+'</td>';
                        TempContentTotal+='<td>'+t_rabies_cat_vaccin_period+'</td>';
    
						TempContentTotal+='<td>'+t_lepra_dog_vaccin_year+'</td>';
                        TempContentTotal+='<td>'+t_lepra_dog_vaccin_period+'</td>';

                        TempContentTotal+='<td>'+t_plague_dog_vaccin_year+'</td>';
                        TempContentTotal+='<td>'+t_plague_dog_vaccin_period+'</td>';
                        TempContentTotal+='</tr>';
    
                        TempContent='<table class="w-100">'+TempContentHeader+''+TempContentTotal+''+TempContent+''+TempContentTotal+'</table>';
    
                        if(TempRecsNum == 0){
                            $('#ListRecs').addClass('message');
                            TempContent='<div class="p-3">По данному запросу невозможно сформировать отчёт.</div>';
                        }
                        document.getElementById('ListRecs').innerHTML=TempContent;
                        
                }
            }
        });
    }
}

function ShowVaccinationReport(){
    event.preventDefault();
    let xls=0;
    if(event.submitter.name == 'xls'){
        xls=1;
    }

    if(xls){
        $.ajax({
            'async': true,
            'global': false,
            'cache': false,
            'type': 'POST',
            'xhrFields': {
                responseType: 'blob'
            },
			'processData': false,
			'contentType': false,
			'data': JSON.stringify(parseSerializedData(jQuery("#form").serialize())),
            'url': "index.php?action=vaccination_report&xls="+xls+"&mode=xml",
            'beforeSend': function() {
                //показать загрузку
                showLoading('ListRecs');
            },
            'success': function (Data) {
                closeLoading('ListRecs');

                if($(Data).find('message').text() == 'token_invalid'){
                    window.location.href = 'index.php';
                }else{
					let date_from=$("#date_from").val();
					let date_to=$("#date_to").val();

                    var a = document.createElement('a');
                    var url = window.URL.createObjectURL(Data);
                    a.href = url;
                    a.download = 'Охват вакцинации (с '+date_from+' по '+date_to+').xls';
                    document.body.append(a);
                    a.click();
                    a.remove();
                    window.URL.revokeObjectURL(url);

                    TempContent='<div class="p-3">Загрузка файла началась...</div>';
                    document.getElementById('ListRecs').innerHTML=TempContent;
                }
            }
        }); 
    }else{
        $.ajax({
            'async': true,
            'global': false,
            'cache': false,
            'type': 'POST',
            'dataType': 'xml',
			'processData': false,
			'contentType': false,
			'data': JSON.stringify(parseSerializedData(jQuery("#form").serialize())),
            'url': "index.php?action=vaccination_report&xls="+xls+"&mode=xml",
            'beforeSend': function() {
                //показать загрузку
                showLoading('ListRecs');
            },
            'success': function (Recs) {
                closeLoading('ListRecs');
    
                if($(Recs).find('message').text() == 'token_invalid'){
                    window.location.href = 'index.php';
                }else if($(Recs).find('rec').text()){
                        let TempContent='';
                        let TempContentHeader='';
                        let TempRecsNum=0;

						TempContentHeader+='<tr>';
						TempContentHeader+='<th rowspan=2>Организация</th>';
						TempContentHeader+='<th rowspan=2 class="vertical-rl" style="background: #FAF9F9;">Кошек на учете<br />(всего)</th>';
						TempContentHeader+='<th rowspan=2 class="vertical-rl" style="background: #FAF9F9;">Кошек на учете<br />(за выбр. период)</th>';
						TempContentHeader+='<th colspan=2 style="background: #FAF9F9;">Вакцинировано (бешенство)</th>';
						TempContentHeader+='<th rowspan=2 class="vertical-rl" style="background: #FAF9F9;">Отказ от вакцинации</th>';
						TempContentHeader+='<th rowspan=2 class="vertical-rl">Собак на учете<br />(всего)</th>';
						TempContentHeader+='<th rowspan=2 class="vertical-rl">Собак на учете<br />(за выбр. период)</th>';
						TempContentHeader+='<th colspan=2>Вакцинировано (бешенство)</th>';
						TempContentHeader+='<th rowspan=2 class="vertical-rl">Отказ от вакцинации</th>';
						TempContentHeader+='<th rowspan=2 class="vertical-rl" style="background: #FAF9F9;">Прочих животных на учете<br />(всего)</th>';
						TempContentHeader+='<th rowspan=2 class="vertical-rl" style="background: #FAF9F9;">Прочих животных на учете<br />(за выбр. период)</th>';
						TempContentHeader+='<th colspan=2 style="background: #FAF9F9;">Вакцинировано (бешенство)</th>';
						TempContentHeader+='<th rowspan=2 style="background: #FAF9F9;" class="vertical-rl">Отказ от вакцинации</th>';
						TempContentHeader+='</tr>';
						TempContentHeader+='<tr>';
						TempContentHeader+='<th class="vertical-rl" style="background: #FAF9F9;">Вакциной Рабикан</th>';
						TempContentHeader+='<th class="vertical-rl" style="background: #FAF9F9;">Комплексной вакциной</th>';
						TempContentHeader+='<th class="vertical-rl">Вакциной Рабикан</th>';
						TempContentHeader+='<th class="vertical-rl">Комплексной вакциной</th>';
						TempContentHeader+='<th class="vertical-rl" style="background: #FAF9F9;">Вакциной Рабикан</th>';
						TempContentHeader+='<th class="vertical-rl" style="background: #FAF9F9;">Комплексной вакциной</th>';
						TempContentHeader+='</tr>';
                        
                        let t_all_dog_count=0;
                        let t_all_cat_count=0;
						let t_all_other_count=0;

                        let t_all_dog_count_period=0;
                        let t_all_cat_count_period=0;
                        let t_all_other_count_period=0;

						let t_rabican_dog_vaccin_period=0;
                        let t_rabican_cat_vaccin_period=0;
                        let t_rabican_other_vaccin_period=0;

                        let t_rabies_dog_vaccin_period=0;
                        let t_rabies_cat_vaccin_period=0;
						let t_rabies_other_vaccin_period=0;

                        let t_dog_cancellation_count=0;
                        let t_cat_cancellation_count=0;
                        let t_other_cancellation_count=0;
						//
						let o_all_dog_count=0;
                        let o_all_cat_count=0;
						let o_all_other_count=0;
						
                        let o_all_dog_count_period=0;
                        let o_all_cat_count_period=0;
                        let o_all_other_count_period=0;

						let o_rabican_dog_vaccin_period=0;
                        let o_rabican_cat_vaccin_period=0;
                        let o_rabican_other_vaccin_period=0;

                        let o_rabies_dog_vaccin_period=0;
                        let o_rabies_cat_vaccin_period=0;
						let o_rabies_other_vaccin_period=0;

                        let o_dog_cancellation_count=0;
                        let o_cat_cancellation_count=0;
                        let o_other_cancellation_count=0;
    
                        let area='';
                        if($(Recs).find('rec').text()){
                            $(Recs).find('rec').each(function(){
                                if(area != $(this).find('rec_area_name').text()){
                                    TempContent+='<tr class="area"><th colspan="16">'+$(this).find('rec_area_name').text()+'</th></tr>';//округ
                                    area = $(this).find('rec_area_name').text();
                                }
    
                                TempContent+='<tr>';
                                TempContent+='<td>'+$(this).find('rec_organization_name').text()+'';
                                if($(this).find('rec_organization_address').text()){
                                    TempContent+=', '+$(this).find('rec_organization_address').text()+'';
                                }
                                TempContent+='</td>';

                                TempContent+='<td>'+$(this).find('rec_all_cat_count').text()+'</td>';
                                TempContent+='<td>'+$(this).find('rec_all_cat_count_period').text()+'</td>';
                                TempContent+='<td>'+$(this).find('rec_rabican_cat_vaccin_period').text()+'</td>';
                                TempContent+='<td>'+$(this).find('rec_rabies_cat_vaccin_period').text()+'</td>';
                                TempContent+='<td>'+$(this).find('rec_cat_cancellation_count').text()+'</td>';

                                TempContent+='<td>'+$(this).find('rec_all_dog_count').text()+'</td>';
                                TempContent+='<td>'+$(this).find('rec_all_dog_count_period').text()+'</td>';
                                TempContent+='<td>'+$(this).find('rec_rabican_dog_vaccin_period').text()+'</td>';
                                TempContent+='<td>'+$(this).find('rec_rabies_dog_vaccin_period').text()+'</td>';
                                TempContent+='<td>'+$(this).find('rec_dog_cancellation_count').text()+'</td>';

                                TempContent+='<td>'+$(this).find('rec_all_other_count').text()+'</td>';
								TempContent+='<td>'+$(this).find('rec_all_other_count_period').text()+'</td>';
                                TempContent+='<td>'+$(this).find('rec_rabican_other_vaccin_period').text()+'</td>';
                                TempContent+='<td>'+$(this).find('rec_rabies_other_vaccin_period').text()+'</td>';
                                TempContent+='<td>'+$(this).find('rec_other_cancellation_count').text()+'</td>';
                                TempContent+='</tr>';
    
                                t_all_dog_count=t_all_dog_count+parseInt($(this).find('rec_all_dog_count').text());
                                t_all_cat_count=t_all_cat_count+parseInt($(this).find('rec_all_cat_count').text());
								t_all_other_count=t_all_other_count+parseInt($(this).find('rec_all_other_count').text());

								t_all_dog_count_period=t_all_dog_count_period+parseInt($(this).find('rec_all_dog_count_period').text());
                                t_all_cat_count_period=t_all_cat_count_period+parseInt($(this).find('rec_all_cat_count_period').text());
								t_all_other_count_period=t_all_other_count_period+parseInt($(this).find('rec_all_other_count_period').text());

								t_rabican_dog_vaccin_period=t_rabican_dog_vaccin_period+parseInt($(this).find('rec_rabican_dog_vaccin_period').text());
                                t_rabican_cat_vaccin_period=t_rabican_cat_vaccin_period+parseInt($(this).find('rec_rabican_cat_vaccin_period').text());
								t_rabican_other_vaccin_period=t_rabican_other_vaccin_period+parseInt($(this).find('rec_rabican_other_vaccin_period').text());

								t_rabies_dog_vaccin_period=t_rabies_dog_vaccin_period+parseInt($(this).find('rec_rabies_dog_vaccin_period').text());
                                t_rabies_cat_vaccin_period=t_rabies_cat_vaccin_period+parseInt($(this).find('rec_rabies_cat_vaccin_period').text());
								t_rabies_other_vaccin_period=t_rabies_other_vaccin_period+parseInt($(this).find('rec_rabies_other_vaccin_period').text());

								t_dog_cancellation_count=t_dog_cancellation_count+parseInt($(this).find('rec_dog_cancellation_count').text());
                                t_cat_cancellation_count=t_cat_cancellation_count+parseInt($(this).find('rec_cat_cancellation_count').text());
								t_other_cancellation_count=t_other_cancellation_count+parseInt($(this).find('rec_other_cancellation_count').text());
        
                                //

								o_all_dog_count=o_all_dog_count+parseInt($(this).find('rec_all_dog_count').text());
                                o_all_cat_count=o_all_cat_count+parseInt($(this).find('rec_all_cat_count').text());
								o_all_other_count=o_all_other_count+parseInt($(this).find('rec_all_other_count').text());

								o_all_dog_count_period=o_all_dog_count_period+parseInt($(this).find('rec_all_dog_count_period').text());
                                o_all_cat_count_period=o_all_cat_count_period+parseInt($(this).find('rec_all_cat_count_period').text());
								o_all_other_count_period=o_all_other_count_period+parseInt($(this).find('rec_all_other_count_period').text());

								o_rabican_dog_vaccin_period=o_rabican_dog_vaccin_period+parseInt($(this).find('rec_rabican_dog_vaccin_period').text());
                                o_rabican_cat_vaccin_period=o_rabican_cat_vaccin_period+parseInt($(this).find('rec_rabican_cat_vaccin_period').text());
								o_rabican_other_vaccin_period=o_rabican_other_vaccin_period+parseInt($(this).find('rec_rabican_other_vaccin_period').text());

								o_rabies_dog_vaccin_period=o_rabies_dog_vaccin_period+parseInt($(this).find('rec_rabies_dog_vaccin_period').text());
                                o_rabies_cat_vaccin_period=o_rabies_cat_vaccin_period+parseInt($(this).find('rec_rabies_cat_vaccin_period').text());
								o_rabies_other_vaccin_period=o_rabies_other_vaccin_period+parseInt($(this).find('rec_rabies_other_vaccin_period').text());

								o_dog_cancellation_count=o_dog_cancellation_count+parseInt($(this).find('rec_dog_cancellation_count').text());
                                o_cat_cancellation_count=o_cat_cancellation_count+parseInt($(this).find('rec_cat_cancellation_count').text());
								o_other_cancellation_count=o_other_cancellation_count+parseInt($(this).find('rec_other_cancellation_count').text());

    
                                if(area != $(this).next().find('rec_area_name').text()){
                                    TempContent+='<tr class="area" style="border-bottom-width: 4px;">';
                                    TempContent+='<th>Итого по округу</th>';
                                    
									TempContent+='<td>'+o_all_cat_count+'</td>';
                                    TempContent+='<td>'+o_all_cat_count_period+'</td>';
									TempContent+='<td>'+o_rabican_cat_vaccin_period+'</td>';
									TempContent+='<td>'+o_rabies_cat_vaccin_period+'</td>';
									TempContent+='<td>'+o_cat_cancellation_count+'</td>';
									
									TempContent+='<td>'+o_all_dog_count+'</td>';
                                    TempContent+='<td>'+o_all_dog_count_period+'</td>';
									TempContent+='<td>'+o_rabican_dog_vaccin_period+'</td>';
									TempContent+='<td>'+o_rabies_dog_vaccin_period+'</td>';
									TempContent+='<td>'+o_dog_cancellation_count+'</td>';

									TempContent+='<td>'+o_all_other_count+'</td>';
									TempContent+='<td>'+o_all_other_count_period+'</td>';
									TempContent+='<td>'+o_rabican_other_vaccin_period+'</td>';
									TempContent+='<td>'+o_rabies_other_vaccin_period+'</td>';
									TempContent+='<td>'+o_other_cancellation_count+'</td>';
                                    TempContent+='</tr>';
    
                                    o_all_dog_count=0;
									o_all_cat_count=0;
									o_all_other_count=0;
									
									o_all_dog_count_period=0;
									o_all_cat_count_period=0;
									o_all_other_count_period=0;

									o_rabican_dog_vaccin_period=0;
									o_rabican_cat_vaccin_period=0;
									o_rabican_other_vaccin_period=0;

									o_rabies_dog_vaccin_period=0;
									o_rabies_cat_vaccin_period=0;
									o_rabies_other_vaccin_period=0;

									o_dog_cancellation_count=0;
									o_cat_cancellation_count=0;
									o_other_cancellation_count=0;
                                }
    
                                TempRecsNum++;
                            });
                        }
    
						let TempContentTotal='';
						TempContentTotal+='<tr class="total" style="border-top-width: 5px; border-bottom-width: 5px;">';
						TempContentTotal+='<th>Итого</th>';

						TempContentTotal+='<td>'+t_all_cat_count+'</td>';
                        TempContentTotal+='<td>'+t_all_cat_count_period+'</td>';
						TempContentTotal+='<td>'+t_rabican_cat_vaccin_period+'</td>';
						TempContentTotal+='<td>'+t_rabies_cat_vaccin_period+'</td>';
						TempContentTotal+='<td>'+t_cat_cancellation_count+'</td>';
									
						TempContentTotal+='<td>'+t_all_dog_count+'</td>';
                        TempContentTotal+='<td>'+t_all_dog_count_period+'</td>';
						TempContentTotal+='<td>'+t_rabican_dog_vaccin_period+'</td>';
						TempContentTotal+='<td>'+t_rabies_dog_vaccin_period+'</td>';
						TempContentTotal+='<td>'+t_dog_cancellation_count+'</td>';

						TempContentTotal+='<td>'+t_all_other_count+'</td>';
						TempContentTotal+='<td>'+t_all_other_count_period+'</td>';
						TempContentTotal+='<td>'+t_rabican_other_vaccin_period+'</td>';
						TempContentTotal+='<td>'+t_rabies_other_vaccin_period+'</td>';
						TempContentTotal+='<td>'+t_other_cancellation_count+'</td>';

                        TempContentTotal+='</tr>';
    
                        TempContent='<table class="w-100">'+TempContentHeader+''+TempContentTotal+''+TempContent+''+TempContentTotal+'</table>';
    
                        if(TempRecsNum == 0){
                            $('#ListRecs').addClass('message');
                            TempContent='<div class="p-3">По данному запросу невозможно сформировать отчёт.</div>';
                        }
                        document.getElementById('ListRecs').innerHTML=TempContent;
                        
                }
            }
        });
    }
}

function ShowNotificationsReport(){
	event.preventDefault();
    let xls=0;
    if(event.submitter.name == 'xls'){
        xls=1;
    }

    if(xls){
		$.ajax({
            'async': true,
            'global': false,
            'cache': false,
            'type': 'POST',
            'xhrFields': {
                responseType: 'blob'
            },
			'processData': false,
			'contentType': false,
			'data': JSON.stringify(parseSerializedData(jQuery("#form").serialize())),
			'url': "index.php?action=notifications_report&xls="+xls+"&mode=xml",
            'beforeSend': function() {
                //показать загрузку
                showLoading('ListRecs');
            },
            'success': function (Data) {
                closeLoading('ListRecs');

                if($(Data).find('message').text() == 'token_invalid'){
                    window.location.href = 'index.php';
                }else{
					let date_from=$("#date_from").val();
					let date_to=$("#date_to").val();

                    var a = document.createElement('a');
                    var url = window.URL.createObjectURL(Data);
                    a.href = url;
                    a.download = 'Отчёт по уведомлениям (с '+date_from+' по '+date_to+').xls';
                    document.body.append(a);
                    a.click();
                    a.remove();
                    window.URL.revokeObjectURL(url);

                    TempContent='<div class="p-3">Загрузка файла началась...</div>';
                    document.getElementById('ListRecs').innerHTML=TempContent;
                }
            }
        });
	}else{
        $.ajax({
            'async': true,
            'global': false,
            'cache': false,
            'type': 'POST',
            'dataType': 'xml',
			'processData': false,
			'contentType': false,
			'data': JSON.stringify(parseSerializedData(jQuery("#form").serialize())),
			'url': "index.php?action=notifications_report&xls="+xls+"&mode=xml",
            'beforeSend': function() {
                //показать загрузку
                showLoading('ListRecs');
            },
            'success': function (Recs) {
                closeLoading('ListRecs');

				let TempRecsNum=0;
				let TempContent='';
    
                if($(Recs).find('message').text() == 'token_invalid'){
                    window.location.href = 'index.php';
                }else if($(Recs).find('rec').text()){
                        TempRecsNum=1;

						let initial_info_vaccination=0;
						let initial_info_identification=0;
						let initial_info_identification_and_vaccin=0;
						let remind_vaccination=0;
						let remind_vaccination_lepto=0;
						let remind_identification=0;
						let quarantine=0;
						let animal_found=0;
						let research=0;
						let transfer_visit_vetas_perenos_priema=0;
						let appointment_results=0;

						$(Recs).find('rec').each(function(){
							if($(this).find('rec_code').text() == 'initial_info_vaccination'){initial_info_vaccination=initial_info_vaccination+parseInt($(this).find('rec_count').text());}
							if($(this).find('rec_code').text() == 'initial_info_identification'){initial_info_identification=initial_info_identification+parseInt($(this).find('rec_count').text());}
							if($(this).find('rec_code').text() == 'initial_info_identification_and_vaccin'){initial_info_identification_and_vaccin=initial_info_identification_and_vaccin+parseInt($(this).find('rec_count').text());}
							if($(this).find('rec_code').text() == 'remind_vaccination'){remind_vaccination=remind_vaccination+parseInt($(this).find('rec_count').text());}
							if($(this).find('rec_code').text() == 'remind_vaccination_lepto'){remind_vaccination_lepto=remind_vaccination_lepto+parseInt($(this).find('rec_count').text());}
							if($(this).find('rec_code').text() == 'remind_identification'){remind_identification=remind_identification+parseInt($(this).find('rec_count').text());}
							if($(this).find('rec_code').text() == 'quarantine'){quarantine=quarantine+parseInt($(this).find('rec_count').text());}
							if($(this).find('rec_code').text() == 'animal_found'){animal_found=animal_found+parseInt($(this).find('rec_count').text());}
							if($(this).find('rec_code').text() == 'research'){research=research+parseInt($(this).find('rec_count').text());}
							if($(this).find('rec_code').text() == 'transfer_visit'){transfer_visit_vetas_perenos_priema=transfer_visit_vetas_perenos_priema+parseInt($(this).find('rec_count').text());}
							if($(this).find('rec_code').text() == 'vetas_perenos_priema'){transfer_visit_vetas_perenos_priema=transfer_visit_vetas_perenos_priema+parseInt($(this).find('rec_count').text());}
							if($(this).find('rec_code').text() == 'appointment_results'){appointment_results=appointment_results+parseInt($(this).find('rec_count').text());}
						});
    
						TempContent+='<colgroup><col span="3" /><col /><col /><col span="7" /></colgroup>';
						TempContent+='<tbody>';
						TempContent+='<tr><td colspan="5" rowspan="3" class="w-25"><strong>Первое уведомление</strong></td><td colspan="5" class="w-25"><strong>О вакцинации</strong></td><td colspan="2">'+initial_info_vaccination+'</td></tr>';
						TempContent+='<tr><td colspan="5"><strong>Об идентификации</strong></td><td colspan="2">'+initial_info_identification+'</td></tr>';//initial_info_identification
						TempContent+='<tr><td colspan="5"><strong>О вакцинации и идентификации</strong></td><td colspan="2">'+initial_info_identification_and_vaccin+'</td></tr>';//initial_info_identification_and_vaccin
						TempContent+='<tr><td colspan="5" height="52" rowspan="2"><strong>Напоминание о вакцинации</strong></td><td colspan="5"><strong>Бешенство</strong></td><td colspan="2">'+remind_vaccination+'</td></tr>';//remind_vaccination
						TempContent+='<tr><td colspan="5"><strong>Лептоспироз</strong></td><td colspan="2">'+remind_vaccination_lepto+'</td></tr>';//remind_vaccination_lepto
						TempContent+='<tr><td colspan="10"><strong>Напоминание об идентификации</strong></td><td colspan="2">'+remind_identification+'</td></tr>';//remind_identification
						TempContent+='<tr><td colspan="10"><strong>Уведомление о проведении противоэпизотических мероприятиях на местности</strong></td><td colspan="2">'+quarantine+'</td></tr>';//quarantine
						TempContent+='<tr><td colspan="10"><strong>Уведомление о найденом/отловленном владельческом животном</strong></td><td colspan="2">'+animal_found+'</td></tr>';//animal_found
						TempContent+='<tr><td colspan="10"><strong>Уведомление о готовности результатов исследований</strong></td><td colspan="2">'+research+'</td></tr>';//research
						TempContent+='<tr><td colspan="10"><strong>Уведомление о переносе приема владельцу животного</strong></td><td colspan="2">'+transfer_visit_vetas_perenos_priema+'</td></tr>';//transfer_visit+vetas_perenos_priema
						TempContent+='<tr><td colspan="10"><strong>Отправленные результаты по завершенному приему (телеветеринария)</strong></td><td colspan="2">'+appointment_results+'</td></tr>';//appointment_results
						TempContent+='</tbody>';
    
                        TempContent='<table class="w-100">'+TempContent+'</table>';
                }

				if(TempRecsNum == 0){
					$('#ListRecs').addClass('message');
					TempContent='<div class="p-3">По данному запросу невозможно сформировать отчёт.</div>';
				}
				document.getElementById('ListRecs').innerHTML=TempContent;
            }
        });
    }
}

function ShowVeterinarySpecialistsReport(load, page = 1){
	//xls
	let xls=0;
	
	if (typeof event !== 'undefined') {
		event.preventDefault();
		if(event.submitter?.name){
			if(event.submitter.name == 'xls'){
				xls=1;
			}
		}
	}
	//xls

	let date_from = $("#form #date_from").val();
	let date_to = $("#form #date_to").val();

	if(date_from == '' || date_to == ''){
		$("#ListRecs").html('Поля «Дата начала» и «Дата окончания» являются обязательными для заполнения.');
		$("#ListRecs").show();

		return true;
	}

	

	let date_from_ = new Date(date_from);
	let date_to_ = new Date(date_to);

	if(date_from_ > date_to_){
		$("#ListRecs").html('«Дата начала» периода не может быть больше даты его окончания.');
		$("#ListRecs").show();

		return true;
	}

	if((currentPage == '' && page != null) || (currentPage != page)){currentPage = page;}
	

    if(xls){
		$.ajax({
            'async': true,
            'global': false,
            'cache': false,
            'type': 'POST',
            'xhrFields': {
                responseType: 'blob'
            },
			'processData': false,
			'contentType': false,
			'data': JSON.stringify(parseSerializedData(jQuery("#form").serialize())),
			'url': "index.php?action=veterinary_specialists_report&xls="+xls+"&mode=xml",
            'beforeSend': function() {
                //показать загрузку
                showLoading('ListRecs');
            },
            'success': function (Data) {
                closeLoading('ListRecs');

                if($(Data).find('message').text() == 'token_invalid'){
                    window.location.href = 'index.php';
                }else{
					let date_from=$("#date_from").val();
					let date_to=$("#date_to").val();

                    var a = document.createElement('a');
                    var url = window.URL.createObjectURL(Data);
                    a.href = url;
                    a.download = 'Отчёт по работе ветеринарных специалистов (с '+date_from+' по '+date_to+').xls';
                    document.body.append(a);
                    a.click();
                    a.remove();
                    window.URL.revokeObjectURL(url);

                    TempContent='<div class="p-3">Загрузка файла началась...</div>';
                    document.getElementById('ListRecs').innerHTML=TempContent;
                }
            }
        });
	}else{
        $.ajax({
            'async': true,
            'global': false,
            'cache': false,
            'type': 'POST',
            'dataType': 'xml',
			'processData': false,
			'contentType': false,
			'data': JSON.stringify(parseSerializedData(jQuery("#form").serialize())),
            'url': "index.php?action=veterinary_specialists_report&xls="+xls+"&mode=xml",
            'beforeSend': function() {
                //показать загрузку
                showLoading('ListRecs');
            },
            'success': function (Recs) {
                closeLoading('ListRecs');

				let TempRecsNum=0;
				let TempContent='';
    
                if($(Recs).find('message').text() == 'token_invalid'){
                    window.location.href = 'index.php';
                }else if($(Recs).find('rec').text()){
					TempContent+='<tr class="first"><th style="width: 50px;">№п/п</th><th>Тип услуг</th>';

					let ArrayRecs=new Array();
					$(Recs).find('rec').each(function(){
						ArrayRecs.push({service: $(this).find('rec_service').text(), specialist: $(this).find('rec_specialist').text(), specialist_service_count: $(this).find('rec_specialist_service_count').text(), specialist_service_sum: $(this).find('rec_specialist_service_sum').text(), service_count: $(this).find('rec_service_count').text()});
					});

					let spec_num_=0;
					$(Recs).find('specialist').each(function(){
						let spec = $(this).find('specialist_name').text();
						spec=spec.replace(/\s/g, "<br>");
						TempContent+='<th class="vertical-rl" style="display: none;" id="specialist_'+spec_num_+'">'+spec+'</th>';
						spec_num_++;
					});
					TempContent+='<th>Итого</th></tr>';

					let services_num=0;
					$(Recs).find('service').each(function(){
						TempContent+='<tr><td>'+(TempRecsNum+1)+'</td><td style="text-align: left;">'+$(this).find('service_name').text()+'</td>';
						let service_name = $(this).find('service_name').text();
						
						let spec_num=0;
						$(Recs).find('specialist').each(function(){
							let specialist_name = $(this).find('specialist_name').text();
							TempContent+='<td style="display: none;" id="specialist_'+services_num+'_col_'+spec_num+'">';
							let flag = false;

							for(let i=0;i<ArrayRecs.length;i++){
								if(service_name == ArrayRecs[i].service && specialist_name == ArrayRecs[i].specialist){
									TempContent+='' + ArrayRecs[i].specialist_service_count + '';
									flag = true;
								}
							}
							if(!flag){
								TempContent+='0';
							}
							spec_num++;
							TempContent+='</td>';
						});

						TempContent+='<td>';
						for(let i=0;i<ArrayRecs.length;i++){
							if(service_name == ArrayRecs[i].service){
								TempContent+='' + ArrayRecs[i].service_count + '';
								break;
							}
						}
						TempContent+='</td></tr>';
						
						TempRecsNum++;

						services_num++;
					});
					recs_services_num=services_num;

					TempContent+='<tr class="total"><td style="width: 50px;"></td><th>Общая стоимость приёмов</th>';
					
					let spec_total_num=0;
					$(Recs).find('specialist').each(function(){
						let specialist_name = $(this).find('specialist_name').text();
						TempContent+='<td style="display: none;" id="specialist_total_'+spec_total_num+'">';
						for(let i=0;i<ArrayRecs.length;i++){
							if(specialist_name == ArrayRecs[i].specialist){
								TempContent+='' + ArrayRecs[i].specialist_service_sum + '';
								break;
							}
						}
						spec_total_num++;
						TempContent+='</td>';
					});
					TempContent+='<td>'+$(Recs).find('information').find('all_service_sum').text()+'</td></tr>';

					TempContent='<table class="w-100">'+TempContent+'</table>';

					////////////////////////
					let recs_counter = parseInt($(Recs).find('specialists_num').text());
					recs_counter_specs = recs_counter;

					TempContent+='<div class="pages">';
					
					if(recs_counter > 0){
						let pages_number = parseInt(recs_counter / recs_on_page);
						if(pages_number > 1){
							for(let i = 1; i < pages_number+1; i++){
								TempContent+='<div id="page_'+i+'" ';
								if(page == i){
									TempContent+='class="current" ';
								}
								TempContent+='onclick="ShowVeterinarySpecialistsReportPage('+i+');">' + i + '</div>';
							}
						}
					}

					TempContent+='</div>';
					////////////////////////
                }

				if(TempRecsNum == 0){
					$('#ListRecs').addClass('message');
					TempContent='<div class="p-3">По данному запросу невозможно сформировать отчёт.</div>';
				}
				document.getElementById('ListRecs').innerHTML=TempContent;

				if(TempRecsNum != 0){
					for(let i = 0; i <= recs_on_page*1-1; i++){
						$("#specialist_"+i).show();
						for(let j = 0; j <= recs_services_num-1; j++){
							$("#specialist_"+j+"_col_"+i).show();
						}
						$("#specialist_total_"+i).show();
					}
				}
            }
        });
    }
}

function ShowVeterinarySpecialistsReportPage(Page){
	console.log(recs_on_page*(Page-1));
	console.log(recs_on_page*Page-1);

	for(let i = 0; i <= recs_counter_specs; i++){
		$("#specialist_"+i).hide();
		for(let j = 0; j <= recs_services_num-1; j++){
			$("#specialist_"+j+"_col_"+i).hide();
		}
		$("#specialist_total_"+i).hide();
	}

	for(let i = recs_on_page*(Page-1); i <= recs_on_page*Page-1; i++){
		$("#specialist_"+i).show();
		for(let j = 0; j <= recs_services_num-1; j++){
			$("#specialist_"+j+"_col_"+i).show();
		}
		$("#specialist_total_"+i).show();
	}

	for(let i = 0; i <= recs_counter_specs; i++){
		$("#page_"+i).removeClass('current');
	}

	$("#page_"+Page).addClass('current');
}

function ShowAnimalDiseaseReport(page = 1){
	//xls
	let xls=0;
	
	if (typeof event !== 'undefined') {
		event.preventDefault();
		if(event.submitter?.name){
			if(event.submitter.name == 'xls'){
				xls=1;
			}
		}
	}
	//xls

	let date_from = $("#form #date_from").val();
	let date_to = $("#form #date_to").val();

	if(date_from == '' || date_to == ''){
		$("#ListRecs").html('Поля «Дата начала» и «Дата окончания» являются обязательными для заполнения.');
		$("#ListRecs").show();

		return true;
	}

	let date_from_ = new Date(date_from);
	let date_to_ = new Date(date_to);

	if(date_from_ > date_to_){
		$("#ListRecs").html('«Дата начала» периода не может быть больше даты его окончания.');
		$("#ListRecs").show();

		return true;
	}

    if(xls){
		$.ajax({
            'async': true,
            'global': false,
            'cache': false,
            'type': 'POST',
            'xhrFields': {
                responseType: 'blob'
            },
			'processData': false,
			'contentType': false,
			'data': JSON.stringify(parseSerializedData(jQuery("#form").serialize())),
			'url': "index.php?action=animal_disease_report&xls="+xls+"&mode=xml",
            'beforeSend': function() {
                //показать загрузку
                showLoading('ListRecs');
            },
            'success': function (Data) {
                closeLoading('ListRecs');

                if($(Data).find('message').text() == 'token_invalid'){
                    window.location.href = 'index.php';
                }else{
					let date_from=$("#date_from").val();
					let date_to=$("#date_to").val();

                    var a = document.createElement('a');
                    var url = window.URL.createObjectURL(Data);
                    a.href = url;
                    a.download = 'Отчёт по болезням животных (с '+date_from+' по '+date_to+').xls';
                    document.body.append(a);
                    a.click();
                    a.remove();
                    window.URL.revokeObjectURL(url);

                    TempContent='<div class="p-3">Загрузка файла началась...</div>';
                    document.getElementById('ListRecs').innerHTML=TempContent;
                }
            }
        });
	}else{
        $.ajax({
            'async': true,
            'global': false,
            'cache': false,
            'type': 'POST',
            'dataType': 'xml',
			'processData': false,
			'contentType': false,
			'data': JSON.stringify(parseSerializedData(jQuery("#form").serialize())),
			'url': "index.php?action=animal_disease_report&xls="+xls+"&mode=xml",
            'beforeSend': function() {
                //показать загрузку
                showLoading('ListRecs');
            },
            'success': function (Recs) {
                closeLoading('ListRecs');

				let TempRecsNum=0;
				let TempContent='';
    
                if($(Recs).find('message').text() == 'token_invalid'){
                    window.location.href = 'index.php';
                }else if($(Recs).find('rec').text()){
					TempContent+='<tr class="first">';
					TempContent+='<th style="width: 50px;">№п/п</th><th style="text-align: left;">Наименование заболевания</th><th style="width: 100px;">Количество</th>';
					TempContent+='</tr>';

					let rec_total = 0;
					$(Recs).find('rec').each(function(){
						TempContent+='<tr>';
						TempContent+='<td>'+(TempRecsNum+1)+'</td>';
						TempContent+='<td style="text-align: left;">'+$(this).find('rec_name').text()+'</td>';
						TempContent+='<td>' + $(this).find('rec_count').text() + '</td>';
						TempContent+='</tr>';

						rec_total = $(this).find('rec_total').text();

						TempRecsNum++;
					});
					

					TempContent+='<tr>';
					TempContent+='<td></td>';
					TempContent+='<th>Итого</th>';
					TempContent+='<td>'+rec_total+'</td></tr>';

					TempContent='<table class="w-100">'+TempContent+'</table>';

					TempContent+='<div class="pages">';
						
					if(parseInt($(Recs).find('counter').text()) > 0){
						console.log($(Recs).find('pages').text());
						$(Recs).find('pages').find('page').each(function(){
							TempContent+='<div ';
							if(page == $(this).text()){
								TempContent+='class="current" ';
							}
							TempContent+='onclick="ShowAnimalDiseaseReport('+$(this).text()+');">' + $(this).text() + '</div>';
						});
					}
					TempContent+='</div>';
                }

				if(TempRecsNum == 0){
					$('#ListRecs').addClass('message');
					TempContent='<div class="p-3">По данному запросу невозможно сформировать отчёт.</div>';
				}
				document.getElementById('ListRecs').innerHTML=TempContent;
            }
        });
    }
}