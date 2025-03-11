var ArraySlotsRecs = new Array();
var ArrayServiceTypesRecs = new Array();
var fiasTokken = '';
var copyTime = '';
var map;

function loadAmbulanceSlots(){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#form_search_recs").serialize()+'&action=slots&mode=xml',
		'url': "index.php",
		'beforeSend': function() {
			//показать загрузку
			//showLoading('ListRecs');
		},
		'success': function (Recs) {
			if($(Recs).find('message').text() == 'token_invalid'){
				window.location.href = 'index.php';
			}else{
				if($(Recs).find('rec').text()){
					$(Recs).find('rec').each(function(){
						ArraySlotsRecs.push({brigade_id: $(this).find('rec_brigade_id').text(), brigade_name: $(this).find('rec_brigade_name').text(), specialists: $(this).find('rec_specialists').text(), date: $(this).find('rec_date').text(), start_time: $(this).find('rec_start_time').text(), end_time: $(this).find('rec_end_time').text()});
					});
				}
			}
		}
	});
}

function loadAmbulanceServiceTypes(){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#form_search_recs").serialize()+'&action=service_types&mode=xml',
		'url': "index.php",
		'beforeSend': function() {
			//показать загрузку
			//showLoading('ListRecs');
		},
		'success': function (Recs) {
			if($(Recs).find('message').text() == 'token_invalid'){
				window.location.href = 'index.php';
			}else{
				if($(Recs).find('rec').text()){
					$(Recs).find('rec').each(function(){
						ArrayServiceTypesRecs.push({id: $(this).find('rec_id').text(), name: $(this).find('rec_name').text()});
					});
				}
			}
		}
	});
}

function listShowRequests(){
	let xls=0;
	if(event !== undefined){
		event.preventDefault();
		if(event.submitter !== undefined){
			if(event.submitter.name == 'xls'){
				xls=1;
			}
		}

		
	}

    if(xls){
        jQuery.ajax({
            'async': true,
            'global': false,
            'cache': false,
            'type': 'GET',
            'xhrFields': {
                responseType: 'blob'
            },
            'data': jQuery("#form_search_recs").serialize()+'&xls='+xls+'&action=requests&mode=xml',
            'url': "index.php",
            'beforeSend': function() {
                //показать загрузку
                showLoading('ListRecs');
            },
            'success': function (Data) {
                closeLoading('ListRecs');

                if($(Data).find('message').text() == 'token_invalid'){
                    window.location.href = 'index.php';
                }else{
                    var a = document.createElement('a');
                    var url = window.URL.createObjectURL(Data);
                    a.href = url;
                    a.download = 'report.xlsx';
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
			'type': 'GET',
			'dataType': 'xml',
			'data': jQuery("#form_search_recs").serialize()+'&action=requests&mode=xml',
			'url': "index.php",
			'beforeSend': function() {
				//показать загрузку
				showLoading('ListRecs');
			},
			'success': function (Recs) {
				if($(Recs).find('message').text() == 'token_invalid'){
					window.location.href = 'index.php';
				}else{
					let TempContent='';
					let TempRecsNum=0;

					if($(Recs).find('rec').text()){
						TempContent+='<table class="w-100">';

						TempContent+='<tr>';
						TempContent+='<th>ID</th>';
						TempContent+='<th>Состояние заявки</th>';
						TempContent+='<th>Дата/время передачи вызова бригаде</th>';

						TempContent+='<th>Получение вызова бригадой</th>';
						TempContent+='<th>Приезд на вызов</th>';
						
						TempContent+='<th>Дата/время планового начала приёма</th>';

						TempContent+='<th>Фактическое начало приема</th>';
						TempContent+='<th>Фактическое окончание приема</th>';

						TempContent+='<th>Бригада/специалисты</th>';
						TempContent+='<th>Адрес вызова</th>';
						TempContent+='<th></th>';
						TempContent+='</tr>';

						$(Recs).find('rec').each(function(){
							let FLAG=0;
							let Id=$(this).find('rec_request_id').text();

							TempContentRec='<tr>';
							TempContentRec+='<td>' + $(this).find('rec_id_request').text()+'</td>';
							TempContentRec+='<td><strong>';
							if($(this).find('rec_status').text() == 'N'){TempContentRec+='Новая';}
							if($(this).find('rec_status').text() == 'W'){TempContentRec+='В работе';}
							if($(this).find('rec_status').text() == 'A'){TempContentRec+='Отменена';}
							if($(this).find('rec_status').text() == 'F'){TempContentRec+='Завершена';}
							if($(this).find('rec_status').text() == 'P'){TempContentRec+='Принято';}
							TempContentRec+='</strong></td>';

							TempContentRec+='<td>' + $(this).find('rec_created_at').text()+'</td>';
							
							TempContentRec+='<td>' + $(this).find('rec_start_request_date').text()+'</td>';
							TempContentRec+='<td>' + $(this).find('rec_end_request_date').text()+'</td>';

							// TempContentRec+='<td>' + $(this).find('rec_start_date').text()+' '+$(this).find('rec_start_time').text()+'</td>';
							TempContentRec+='<td>' + $(this).find('rec_start_dttm').text()+'</td>';

							TempContentRec+='<td>' + $(this).find('rec_fact_start_dttm').text()+'</td>';
							TempContentRec+='<td>' + $(this).find('rec_fact_end_dttm').text()+'</td>';

							TempContentRec+='<td><strong>' + $(this).find('rec_brigade').text() + '</strong><br />(';

							if($(this).find('rec_specialists').text()){
								let i=0;
								$(this).find('specialist').each(function(){
									if(i>0){TempContentRec+=', ';}
									TempContentRec+='' + $(this).find('name').text() + '';
									i++;
								});
							}
							TempContentRec+=')</td>';

							TempContentRec+='<td>' + $(this).find('rec_address').text() + '</td>';

							TempContentRec+='<td>';
							TempContentRec+='<button class="button" onclick="showRequestWindow('+$(this).find('rec_id_request').text()+');" style="width: 200px;">Просмотр заявки</button>';
							TempContentRec+='</td>';

							TempContentRec+='</tr>';

							if(FLAG == 0){
								TempRecsNum++;
								TempContent+=TempContentRec;
							}
						});
					}

					if(TempRecsNum == 0){
						TempContent='<div class="message">По данному запросу не найдено записей.</div>';
					}
					document.getElementById('ListRecs').innerHTML=TempContent;
				}
			}
		});
	}
}

function listShowBrigades(){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': 'action=brigades&mode=xml',
		'url': "index.php",
		'beforeSend': function() {
			//показать загрузку
			showLoading('ListRecs');
		},
		'success': function (Recs) {
			if($(Recs).find('message').text() == 'token_invalid'){
				window.location.href = 'index.php';
			}else{
				let TempContent='';
				let TempRecsNum=0;

				if($(Recs).find('rec').text()){
					TempContent+='<table class="w-100">';

					TempContent+='<tr>';
					TempContent+='<th>ID</th>';
					TempContent+='<th>Название</th>';
					TempContent+='<th>Специалисты</th>';
					TempContent+='<th>Расписание</th>';
					TempContent+='<th>ФИО водителя</th>';
					TempContent+='<th>Марка автомобиля</th>';
					TempContent+='<th>Номер автомобиля</th>';
					TempContent+='<th>Пропуск автомобиля</th>';
					TempContent+='<th></th>';
					TempContent+='</tr>';

					$(Recs).find('rec').each(function(){
						let FLAG=0;
						let Id=$(this).find('rec_id').text();

						TempContentRec='<tr>';
						TempContentRec+='<td>'+$(this).find('rec_id').text()+'</td>';

						TempContentRec+='<td>';
						TempContentRec+='<span class="title">' + $(this).find('rec_name').text() + '</span>';
						TempContentRec+='</td>';

						TempContentRec+='<td>'+$(this).find('rec_specialists').text()+'</td>';
						
						TempContentRec+='<td>';
						if($(this).find('rec_schedule').text()){
							TempContentRec+='<div class="schedule">';
							
							TempContentRec+='<div class="slots">';
							$(this).find('rec_schedule').find('slot').each(function(){
								TempContentRec+='<div class="slot big">'+$(this).find('date').text()+' с '+$(this).find('time_from').text()+' по '+$(this).find('time_to').text()+'</div>';
							});
							TempContentRec+='</div>';

							TempContentRec+='</div>';
						}

						TempContentRec+='</td>';

						TempContentRec+='<td>'+$(this).find('rec_car_driver').text()+'</td>';
						TempContentRec+='<td>'+$(this).find('rec_car_model').text()+'</td>';
						
						TempContentRec+='<td>'+$(this).find('rec_car_number').text()+'</td>';
						TempContentRec+='<td>'+$(this).find('rec_pass_number').text()+'</td>';
						TempContentRec+='</td>';

						TempContentRec+='<td style="width: 32px;">';
						TempContentRec+='<div class="delete" onclick="deleteBrigadeAcceptWindow(\''+$(this).find('rec_name').text()+'\', '+$(this).find('rec_id').text()+');"></div>';
						TempContentRec+='</td>';


						TempContentRec+='</tr>';

						

						if(FLAG == 0){
							TempRecsNum++;
							TempContent+=TempContentRec;
						}
					});

					TempContent+='</table>';
				}

				if(TempRecsNum == 0){
					TempContent='<div class="message">По данному запросу не найдено записей.</div>';
				}
				document.getElementById('ListRecs').innerHTML=TempContent;
			}
		}
	});
}

function deleteBrigadeAcceptWindow(Brigade, Id){
	let Text='Вы уверены, что хотите удалить бригаду <strong>'+Brigade+'</strong>?';
	showAcceptWindow('Удаление бригады', Text, 'deleteBrigade(' + Id + ');', 'Удалить','delete');
}

function deleteBrigade(Id){
	closeAcceptWindow();
	if(Id){
		jQuery.ajax({
			'async': true,
			'global': false,
			'cache': false,
			'type': 'GET',
			'dataType': 'xml',
			'data': 'id='+Id+'&action=delete_brigade&xml=1',
			'url': "index.php",
			'beforeSend': function() {
				$("#message").removeClass('error');
				$("#message").hide();
			},
			'success': function (Data) {
				if($(Data).find('message').text() == 'brigade_deleted_error'){
					$("#message").html('Внимание! У бригады есть незавершенные заявки. Удаление невозможно.');
					$("#message").addClass('error');
					$("#message").show();
				}
				if($(Data).find('message').text() == 'brigade_deleted'){
					closeLoading('ListRecs');
					listShowBrigades();			
				}
			}
		});
	}
}

function showAddBrigageWindow(){
	let TempContent='';

	TempContent+='<div id="add_brigade_window" class="window main_row col-xl-8 col-lg-10 col-12">';
	//
	TempContent+='<div class="close" onclick="closeAddBrigadeWindow();"></div>';
	TempContent+='<div class="top">Добавить бригаду</div>';

	TempContent+='<div class="error" id="brigade_window_message" style="display: none;"></div>';

	TempContent+='<div class="col-xl-9 col-lg-9 main_row">';
	TempContent+='<form class="form-window main_row" id="">';
	//TempContent+='<div class="row main_row control">';
	
	TempContent+='<div class="col-12 required">Наименование бригады:</div>';
	TempContent+='<div class="col-12">';
	TempContent+='<input type="text" name="name" id="name" value="">';
	TempContent+='</div>';

	TempContent+='<div class="col-12 required">ФИО ветеринарных специалистов:</div>';
	TempContent+='<div class="col-12">';
	
	TempContent+='<textarea id="specialists" name="specialists" rows="5" class="JxTag"></textarea>';

	TempContent+='</div>';

	TempContent+='<div class="col-12">ФИО водителя:</div>';
	TempContent+='<div class="col-12">';
	TempContent+='<input type="text" name="car_driver" id="car_driver" value="" autocomplete="off">';
	TempContent+='</div>';

	TempContent+='<div class="col-12 title">Автомобиль</div>';

	TempContent+='<div class="col-12">Марка:</div>';
	TempContent+='<div class="col-12">';
	TempContent+='<input type="text" name="car_model" id="car_model" value="" autocomplete="off">';
	TempContent+='</div>';

	TempContent+='<div class="col-12">Государственный регистрационный номер:</div>';
	TempContent+='<div class="col-12">';
	TempContent+='<input type="text" name="car_number" id="car_number" value="" autocomplete="off">';
	TempContent+='</div>';

	TempContent+='<div class="col-12">Номер путевого листа (номер пропуска):</div>';
	TempContent+='<div class="col-12">';
	TempContent+='<input type="text" name="pass_number" id="pass_number" value="" autocomplete="off">';
	TempContent+='</div>';

	TempContent+='<div class="col-12 required">Расписание:</div>';
	TempContent+='<div class="col-12">';
	//////////////
	TempContent+='<div class="schedule">';

	TempContent+='<div class="title">';
	let today = new Date();
	for(let j=1; j<=14; j++){
		let current_date=today.getDate();if(current_date < 10){current_date='0'+current_date;}
		let current_month=today.getMonth();current_month++;if(current_month < 10){current_month='0'+current_month;}
		TempContent+='<div class="date-time">'+ current_date +'.' + current_month +'<br />'+ArrayDays[today.getDay()]+'</div>';

		today.setDate(today.getDate() + 1);
	}
	TempContent+='</div>';

	let today_ = new Date();
	TempContent+='<div class="slots">';
	for(j=1; j<=14; j++){
		let current_date=today_.getDate();if(current_date < 10){current_date='0'+current_date;}
		let current_month=today_.getMonth();current_month++;if(current_month < 10){current_month='0'+current_month;}
		let current_year=today_.getFullYear();
		
		TempContent+='<div class="slot" oncontextmenu="showBrigadeScheduleMenu(\''+current_year+'-'+current_month+'-'+current_date+'\'); return false;" id="slot'+current_year+'-'+current_month+'-'+current_date+'" onclick="addBrigadeSchedule(\''+current_year+'-'+current_month+'-'+current_date+'\');">';
		TempContent+='</div>';

		today_.setDate(today_.getDate() + 1);
	}
	TempContent+='</div>';

	TempContent+='</div>';
	/////////////////
	TempContent+='</div>';

	TempContent+='</form>';
	TempContent+='</div>';

	//Кнопки управления
	TempContent+='<div class="controls">';
	TempContent+='<button class="appointment" id="add_brigade" style="width: 200px;" onclick="addBrigade();">Добавить</button>';
	TempContent+='</div>';
	//Кнопки управления
	
	TempContent+='</div>';

	document.getElementById("sub_container").innerHTML=TempContent;
	$("#background").fadeIn();
	$("#container").show();

	var options ={
        name: 'specialists',
        mode: 'normal',
		new_tags: 0,
		max_tags: 999,
		request_min: 3,
		request_title: 'name',
		response_id: 'rec_id',
		response_title: 'rec_name',
        width: '100%',
        height: 250,
        max_tags_win: 10,
        url: "?action=specialists&org_type=46&mode=xml"
    };
    tags_input = new JxTag(options);
}

function showBrigadeScheduleMenu(Date){
	//проверяем пустое ли расписание в окне
	let varContextMenuStyle = document.getElementById("contextmenu").style;
	let TempWidth = $("#contextmenu").width();

	let x;
	let y;
	if(document.all){ 
		x = event.clientX + document.body.scrollLeft; 
		y = event.clientY + document.body.scrollTop; 
	}else{ 
		x = event.pageX; // Координата X курсора
		y = event.pageY; // Координата Y курсора
	}
			
	//Справа от курсора 
	if((x + TempWidth + 30) < document.body.clientWidth){ 
		varContextMenuStyle.left = x + 'px';
	//Слева от курсора
	} else { 
		varContextMenuStyle.left = x - TempWidth + 'px';
	}
	varContextMenuStyle.top = y + 20 + 'px';

	let time=$('#slot'+Date+'').html();
	
	if(time){
		let TempContent='<div class="items">';
		TempContent+='<div class="item" onclick="copyBrigadeScheduleTime(\''+time+'\');">Копировать</div>';
		TempContent+='<div class="item" onclick="deleteBrigadeScheduleTime(\''+Date+'\');">Удалить</div>';
		TempContent+='</div>';
		document.getElementById("contextmenu").innerHTML = TempContent;
		varContextMenuStyle.display = "block";

	}else if(copyTime){
		let TempContent='<div class="items">';
		TempContent+='<div class="item" onclick="insertBrigadeScheduleTime(\''+Date+'\',1);">Вставить (день)</div>';
		TempContent+='<div class="item" onclick="insertBrigadeScheduleTime(\''+Date+'\',7);">Вставить (неделя)</div>';
		TempContent+='</div>';
		document.getElementById("contextmenu").innerHTML = TempContent;
		varContextMenuStyle.display = "block";
	}
}

function copyBrigadeScheduleTime(Time){
	copyTime = Time;
	$('#contextmenu').hide();
}
function deleteBrigadeScheduleTime(Date){
	$("#slot"+Date).removeClass('checked');
	$("#slot"+Date).html('');

	$('#contextmenu').hide();
}
function insertBrigadeScheduleTime(Date_, Days){
	if(Days > 1){
		var partsDate =Date_.split('-');

		let curDate = new Date(partsDate[0], partsDate[1] - 1, partsDate[2]);

		for(j=1; j<=Days; j++){
			let current_date=curDate.getDate();if(current_date < 10){current_date='0'+current_date;}
			let current_month=curDate.getMonth();current_month++;if(current_month < 10){current_month='0'+current_month;}
			let current_year=curDate.getFullYear();

			let Date__=''+current_year+'-'+current_month+'-'+current_date+'';

			$("#slot"+Date__).addClass('checked');
			$("#slot"+Date__).html(copyTime);
			
			curDate.setDate(curDate.getDate() + 1);
		}

		
	}else{
		$("#slot"+Date_).addClass('checked');
		$("#slot"+Date_).html(copyTime);
	}
	$('#contextmenu').hide();
}

$(document).click(function(event) { 
	var $target = $(event.target);
	if(!$target.closest('#contextmenu').length && $('#contextmenu').is(":visible")){
		$('#contextmenu').hide();
	}
});



function addBrigade(){
	let today = new Date();
	let timesheets = '';
	for(let j=1; j<=14; j++){
		let current_date=today.getDate();if(current_date < 10){current_date='0'+current_date;}
		let current_month=today.getMonth();current_month++;if(current_month < 10){current_month='0'+current_month;}
		let current_year=today.getFullYear();

		let times=$('#slot'+current_year+'-'+current_month+'-'+current_date+'').html();
		if(times){
			let arrTimes=times.split('-');

			let from=current_year+'-'+current_month+'-'+current_date+' '+arrTimes[0]+':00';
			let to=current_year+'-'+current_month+'-'+current_date+' '+arrTimes[1]+':00';

			if(timesheets){timesheets+=';'}
			timesheets+='["'+from+'","'+to+'")';
		}
		
		today.setDate(today.getDate() + 1);
	}

	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'POST',
		'dataType': 'xml',
		'data': jQuery("#add_brigade_window :input").serialize()+'&timesheets='+timesheets+'&action=add_brigade&mode=xml',
		'url': "index.php",
		'beforeSend': function() {
			$('#add_brigade_window #add_brigade').prop('disabled', true);
		},
		'success': function (Data) {
			if($(Data).find('message').text() == 'brigade_added'){
				closeAddBrigadeWindow();
				showMessageWindow('Бригада успешно создана.');
				listShowBrigades();
			}
			if($(Data).find('message').text() == 'empty_fields'){
				$("#brigade_window_message").html('Не заполнены необходимые поля.');
				$("#brigade_window_message").show();
				$('#add_brigade_window #add_brigade').prop('disabled', false);
			}
		}
	});
}

function closeAddBrigadeWindow(){
	$("#background").fadeOut();
	$("#container").hide();
}

function addBrigadeSchedule(Date){
	let TempContent='';

	TempContent+='<div class="window col-xl-2 col-lg-5 col-12 main_row">';
	TempContent+='<div class="close" onclick="closeBrigadeScheduleWindow();"></div>';
	TempContent+='<div class="top">Добавить расписание</div>';

	TempContent+='<div class="row main_row control">';

	TempContent+='<div class="col-12">Начало:</div>';
	TempContent+='<div class="col-12">';
	TempContent+='<input type="time" name="schedule_time_from" id="schedule_time_from" value="" step="600">';
	TempContent+='</div>';

	TempContent+='<div class="col-12">Окончание:</div>';
	TempContent+='<div class="col-12">';
	TempContent+='<input type="time" name="schedule_time_to" id="schedule_time_to" value="" step="600">';
	TempContent+='</div>';

	TempContent+='</div>';

	TempContent+='<input type="hidden" value="'+Date+'" id="schedule_date">';

	TempContent+='<div class="controls"><button class="ok" style="width: 120px;" onclick="addBrigadeScheduleTime();">Добавить</button></div>';
	TempContent+='</div>';

	document.getElementById("sub_container_m").innerHTML=TempContent;
	$("#background_m").fadeIn();
	$("#container_m").show();
}

function addBrigadeScheduleTime(){
	$("#slot"+$("#schedule_date").val()).addClass('checked');
	$("#slot"+$("#schedule_date").val()).html($("#schedule_time_from").val()+'-'+$("#schedule_time_to").val());
	closeBrigadeScheduleWindow();
}

function closeBrigadeScheduleWindow(){
	$("#background_m").fadeOut();
	$("#container_m").hide();
}

function showAddRequestWindow(){
	//рисуем окошко
	let ArrayBrigadesRecs = new Array();
	ArrayBrigadesRecs = [...new Set(ArraySlotsRecs.map(item => item.brigade_id))];
	
	//запрашиваем список владельцев
	
	let TempContent='';

	TempContent+='<div id="add_request_window" class="window main_row col-xl-8 col-lg-10 col-12">';
	TempContent+='<div class="close" onclick="closeAddRequestWindow();"></div>';
	TempContent+='<div class="top">Формирование заявки</div>';
	TempContent+='<div class="row main_row control">';
	TempContent+='<div class="col-6" id="window_title">Поиск владельца</div>';
	TempContent+='</div>';

	TempContent+='<div id="FormSearch" class="search">';
	TempContent+='<form id="form_owners" onsubmit="listAmbulanceShowOwnersPets(); return false;">';
	TempContent+='<div class="d-inline-flex"><div class="label">ФИО или наименование владельца:&nbsp;</div><div class="input"><input type="text" name="name" class="name_" value="" autocomplete="off"></div></div>';
	TempContent+='<div class="d-inline-flex"><div class="label">Телефон:&nbsp;</div><div class="input"><input type="text" name="telephone" value="" autocomplete="off" class="telephone"></div></div>';
	TempContent+='<div class="d-inline-flex"><button type="submit">Поиск</button></div>';
	TempContent+='</form>';
	TempContent+='</div>';

	TempContent+='<div class="error margin-15" id="request_window_message" style="display: none;"></div>';
	
	TempContent+='<div id="AddRequest" class="add_form col-xl-12 col-lg-12" style="display: none;">';
	TempContent+='<form id="form_add_request">';


	/////////////////////////////////////
	//Адрес владельца или выбор вручную//
	/////////////////////////////////////
	TempContent+='<div class="d-table">';

	TempContent+='<div class="d-table-row title required">Адрес</div>';

	TempContent+='<div class="d-table-row">';
	TempContent+='<div class="d-table-cell"><input onclick="chooseAddress();" type="radio" id="address_1" name="address_" class="" value="1" checked> <label for="address_1">Адрес владельца:</label></div>';
	TempContent+='<div class="d-table-cell" id="owner_address_title"></div>';
	TempContent+='<input type="hidden" id="address1" value="">';
	TempContent+='</div>';

	TempContent+='<div class="d-table-row">';
	TempContent+='<div class="d-table-cell"><input onclick="chooseAddress();" type="radio" id="address_2" name="address_" class="" value="2"> <label for="address_2">Выбор адреса:</label></div>';
	TempContent+='<div class="d-table-cell">';
	TempContent+='<textarea id="owner_address_request" name="owner_address_request" class="JxTag"></textarea>';
	TempContent+='</div>';
	TempContent+='</div>';

	TempContent+='<div class="d-table-row">';
	TempContent+='<div class="d-table-cell" id="brigades_map" style="vertical-align: top;"></div>';
	TempContent+='<div class="d-table-cell"><div id="map" style="padding-bottom:0px; height:300px; border: 1px solid #ced4da;"></div></div>';
	TempContent+='</div>';

	TempContent+='<div class="d-table-row">';
	TempContent+='<div class="d-table-cell">Примечание:</div>';
	TempContent+='<div class="d-table-cell"><textarea id="description" rows="3"></textarea></div>';
	TempContent+='</div>';



	/////////////////////////////////
	//Причина вызова + время вызова//
	/////////////////////////////////
	TempContent+='<div class="d-table-row title">Вызов</div>';

	TempContent+='<div class="d-table-row">';
	TempContent+='<div class="d-table-cell required">Причина вызова:</div>';
	
	TempContent+='<div class="d-table-cell">';
	TempContent+='<select class="reasons" id="call_reason">';

	let ArrayServiceTypesRecs_=new Array();
	ArrayServiceTypesRecs_[0]='Терапия';
	ArrayServiceTypesRecs_[1]='Хирургия';
	ArrayServiceTypesRecs_[2]='Вакцинация';
	ArrayServiceTypesRecs_[3]='Чипирование';
	ArrayServiceTypesRecs_[4]='Обрезка когтей';
	ArrayServiceTypesRecs_[5]='Стрижка';
	ArrayServiceTypesRecs_[6]='Клинико-диагностические исследования';
	ArrayServiceTypesRecs_[7]='Лабораторные исследования';
	ArrayServiceTypesRecs_[8]='Стоматология';
	ArrayServiceTypesRecs_[9]='Оформление ветеринарных сопроводительных документов';
	ArrayServiceTypesRecs_[10]='Эвтаназия животных';

	for(let k=0; k<=ArrayServiceTypesRecs_.length-1; k++){
		TempContent+='<option value="'+ArrayServiceTypesRecs_[k]+'">'+ArrayServiceTypesRecs_[k]+'</option>';
	}
	TempContent+='</select>';
	TempContent+='</div>';
	
	TempContent+='</div>';

	TempContent+='<div class="d-table-row">';
	TempContent+='<div class="d-table-cell required" style="padding-right: 5px;">Плановое начало приема:</div>';
	TempContent+='<div class="d-table-cell"><input type="time" id="time_request" class="" value="" autocomplete="off" step="600"></div>';
	TempContent+='</div>';

	/////////////////////////////////
	//Причина вызова + время вызова//
	/////////////////////////////////



	TempContent+='</div>';
	/////////////////////////////////////
	//Адрес владельца или выбор вручную//
	/////////////////////////////////////

	/////////////////////////////////////
	//////////Расписание бригад//////////
	/////////////////////////////////////
	TempContent+='<div class="w-100 d-table">';
	TempContent+='<div class="d-table-row title required">Расписание</div>';

	//////////////
	TempContent+='<div class="schedule">';

	TempContent+='<input type="hidden" id="id_brigade" value=""></input>';
	TempContent+='<input type="hidden" id="date_request" value=""></input>';

	TempContent+='<div class="title">';

	TempContent+='<div class="date-time title"></div>';

	let today = new Date();
	for(let j=1; j<=14; j++){
		let current_date=today.getDate();if(current_date < 10){current_date='0'+current_date;}
		let current_month=today.getMonth();current_month++;if(current_month < 10){current_month='0'+current_month;}
		TempContent+='<div class="date-time">'+ current_date +'.' + current_month +'<br />'+ArrayDays[today.getDay()]+'</div>';

		today.setDate(today.getDate() + 1);
	}
	TempContent+='</div>';

	for(let i=0; i<=ArrayBrigadesRecs.length-1;i++){
		let today_ = new Date();

		TempContent+='<div class="slots">';
		let brigade_name='';
		for(let k=0; k<=ArraySlotsRecs.length-1;k++){
			if(ArrayBrigadesRecs[i] == ArraySlotsRecs[k].brigade_id){
				brigade_name=ArraySlotsRecs[k].brigade_name;
			}
		}
		//////
		TempContent+='<div class="slot title">'+brigade_name+'</div>';
		for(j=1; j<=14; j++){
			let current_date=today_.getDate();if(current_date < 10){current_date='0'+current_date;}
			let current_month=today_.getMonth();current_month++;if(current_month < 10){current_month='0'+current_month;}
			let current_year=today_.getFullYear();

			let fulldate=current_year+'-'+current_month+'-'+current_date;
			let start_time='';
			let end_time='';
			let flag=0;

			///
			for(let s=0; s<=ArraySlotsRecs.length-1;s++){
				if(ArraySlotsRecs[s].brigade_id == ArrayBrigadesRecs[i] && ArraySlotsRecs[s].date == fulldate){
					start_time=ArraySlotsRecs[s].start_time;
					end_time=ArraySlotsRecs[s].end_time;
					flag=1;
				}
			}
			///
			start_time = start_time.substring(0, start_time.length -3);
			end_time = end_time.substring(0, end_time.length -3);
			
			TempContent+='<div class="slot ';
			if(flag){
				TempContent+='choose';
			}else{
				TempContent+='notchoose';
			}
			TempContent+='"';
			if(flag){
				TempContent+=' brigade="'+ArrayBrigadesRecs[i]+'" date="'+current_year+'-'+current_month+'-'+current_date+'" id="slot-'+ArrayBrigadesRecs[i]+'-'+current_year+'-'+current_month+'-'+current_date+'" onclick="chooseAmbulanceSlot(this);"';
			}
			TempContent+='>';
			if(flag){TempContent+=''+start_time+'<br />'+end_time+'';}
			TempContent+='</div>';

			today_.setDate(today_.getDate() + 1);
		}
		//////	
		TempContent+='</div>';
	}
	TempContent+='</div>';
	//TempContent+='</div>';
	//TempContent+='</div>';

	/////////////////////////////////////
	//////////Расписание бригад//////////
	/////////////////////////////////////

	

	TempContent+='<div class="w-100 d-table">';

	

	//TempContent+='<div class="d-table-row title">Льготы</div>';
	// TempContent+='<div class="d-table">';
	// TempContent+='<input type="checkbox" id="is_veteran" class="" value="1">';
	// TempContent+=' <label for="is_veteran">Ветеран ВОВ</label>';
	// TempContent+='</div>';

	// TempContent+='<div class="d-table">';
	// TempContent+='<input type="checkbox" id="is_disabled" class="" value="1">';
	// TempContent+=' <label for="is_disabled">Инвалид 1 группы</label>';
	// TempContent+='</div>';

	// TempContent+='<div class="d-table">';
	// TempContent+='<input type="checkbox" id="is_blind" class="" value="1">';
	// TempContent+=' <label for="is_blind">Слабовидящий с животным-поводырём</label>';
	// TempContent+='</div>';

	// TempContent+='<div class="d-table">';
	// TempContent+='<input type="checkbox" id="is_orphan" class="" value="1">';
	// TempContent+=' <label for="is_orphan">Дети-сироты, дети, оставшиеся без попечения родителей в возрасте до 23 лет</label>';
	// TempContent+='</div>';

	// TempContent+='<div class="d-table">';
	// TempContent+='<input type="checkbox" id="is_large_family" class="" value="1">';
	// TempContent+=' <label for="is_large_family">Многодетные семьи</label>';
	// TempContent+='</div>';

	// TempContent+='<div class="d-table">';
	// TempContent+='<input type="checkbox" id="is_veteran_of_labour" class="" value="1">';
	// TempContent+=' <label for="is_veteran_of_labour">Ветераны труда</label>';
	// TempContent+='</div>';

	TempContent+='</div>';

	TempContent+='</form>';
	TempContent+='</div>';
	TempContent+='</div>';
	///
	
	//
	TempContent+='<div id="AddOwners" class="add_form col-xl-12 col-lg-12" style="display: none;">';
	TempContent+='<form id="form_add_owners">';
	TempContent+='<input type="hidden" id="add_new" value=""></input>';

	TempContent+='<div class="w-50 d-table">';

	TempContent+='<div class="d-table-row title">Владелец</div>';

	TempContent+='<div class="d-table-row">';
	TempContent+='<div class="d-table-cell w-50 required">Фамилия:</div>';
	TempContent+='<div class="d-table-cell w-50 "><input type="text" id="owner_new_surname" class="name_" value="" autocomplete="off"></div>';
	TempContent+='</div>';

	TempContent+='<div class="d-table-row">';
	TempContent+='<div class="d-table-cell required">Имя:</div>';
	TempContent+='<div class="d-table-cell"><input type="text" id="owner_new_name" class="name_" value="" autocomplete="off"></div>';
	TempContent+='</div>';

	TempContent+='<div class="d-table-row">';
	TempContent+='<div class="d-table-cell">Отчество:</div>';
	TempContent+='<div class="d-table-cell"><input type="text" id="owner_new_secondname" class="name_" value="" autocomplete="off"></div>';
	TempContent+='</div>';

	TempContent+='<div class="d-table-row">';
	TempContent+='<div class="d-table-cell required">Телефон:</div>';
	TempContent+='<div class="d-table-cell"><input type="text" id="owner_new_telephone" class="telephone" value="" autocomplete="off"></div>';
	TempContent+='</div>';

	TempContent+='<div class="d-table-row">';
	TempContent+='<div class="d-table-cell required">Адрес:</div>';
	TempContent+='<div class="d-table-cell"><input type="text" id="owner_new_address" class="address" value="" autocomplete="off"></div>';
	TempContent+='</div>';

	TempContent+='<div class="d-table-row title">Питомец</div>';

	TempContent+='<div class="d-table-row">';
	TempContent+='<div class="d-table-cell required">Вид:</div>';
	TempContent+='<div class="d-table-cell">';
	TempContent+='<select class="species" id="pet_new_species" onchange="changeSpecies();">';
	for(let k=0; k<=ArraySpeciesRecs.length-1; k++){
		if(ArraySpeciesRecs[k].name == 'кошки' || ArraySpeciesRecs[k].name == 'собаки'){
			TempContent+='<option value="'+ArraySpeciesRecs[k].id+'">'+ArraySpeciesRecs[k].name+'</option>';
		}
	}
	for(let k=0; k<=ArraySpeciesRecs.length-1; k++){
		if(ArraySpeciesRecs[k].name != 'кошки' && ArraySpeciesRecs[k].name != 'собаки'){
			TempContent+='<option value="'+ArraySpeciesRecs[k].id+'">'+ArraySpeciesRecs[k].name+'</option>';
		}
	}
	TempContent+='</select>';
	TempContent+='</div>';
	TempContent+='</div>';

	//
	TempContent+='<div class="d-table-row">';
	TempContent+='<div class="d-table-cell">Порода:</div>';
	TempContent+='<div class="d-table-cell">';
	TempContent+='<select class="breeds" id="pet_new_breeds">';
	TempContent+='<option value="">Не выбрана</option>';
	for(let k=0; k<=ArrayBreedsRecs.length-1; k++){
		TempContent+='<option value="'+ArrayBreedsRecs[k].id+'" species_id="'+ArrayBreedsRecs[k].species+'">'+ArrayBreedsRecs[k].name+'</option>';
	}
	TempContent+='</select>';
	TempContent+='</div>';
	TempContent+='</div>';
	//

	TempContent+='<div class="d-table-row">';
	TempContent+='<div class="d-table-cell required">Пол:</div>';
	TempContent+='<div class="d-table-cell">';
	TempContent+='<select class="sex" id="pet_new_sex">';
	TempContent+='<option value="m">Мужской</option>';
	TempContent+='<option value="f">Женский</option>';
	TempContent+='</select>';
	TempContent+='</div>';
	TempContent+='</div>';

	TempContent+='<div class="d-table-row">';
	TempContent+='<div class="d-table-cell required">Кличка:</div>';
	TempContent+='<div class="d-table-cell"><input type="text" id="pet_new_name" value="" autocomplete="off"></div>';
	TempContent+='</div>';
	TempContent+='</form>';
	TempContent+='</div>';

	TempContent+='</div>';

	//Список в окне
	TempContent+='<div class="results" id="ListOwners">';
	TempContent+='<div class="message">Для поиска владельца введите параметры поиска.</div>';
	TempContent+='</div>';
	//Список в окне

	//Кнопки управления
	TempContent+='<div class="controls">';
	TempContent+='<button class="add" id="add_owner" style="width: 220px; display: none;" onclick="addRequestOwner();">Добавить владельца</button>';
	TempContent+='<button class="next" id="choose_owner" style="width: 220px;" onclick="chooseRequestOwner();">Выбрать владельца</button>';
	TempContent+='<button class="next" id="make_request" style="width: 220px; display: none;" onclick="makeRequest();">Оформить заявку</button>';
	TempContent+='</div>';
	//Кнопки управления

	TempContent+='</div>';

	document.getElementById("sub_container").innerHTML=TempContent;
	$("#background").fadeIn();
	$("#container").show();
	changeAmbulanceOwner();
	$('.name_').mask("R", {
		translation: {
			"R": { pattern: /[А-Яа-яё\s+]/, recursive: true }
		}
	});
	$('.telephone').mask("+7(999) 999-9999");
	$('.species').multiselect({buttonWidth: '100%', maxHeight: 300, enableFiltering: true, enableCaseInsensitiveFiltering : true, filterPlaceholder: 'Выбрать вид животного...'});
	$('.breeds').multiselect({buttonWidth: '100%', maxHeight: 300, enableFiltering: true, enableCaseInsensitiveFiltering : true, filterPlaceholder: 'Выбрать породу животного...'});

	changeSpecies();
	
	var options ={
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
        url: "?action=addresses&tokken="+fiasTokken+"&mode=xml",
		tag_add_function: 'chooseAddress'
    };
    addresses_1_input = new JxTag(options);

	var options ={
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
        url: "?action=addresses&tokken="+fiasTokken+"&mode=xml"
    };
    addresses_2_input = new JxTag(options);

	ymaps.ready(init);

	function init(){
		map = new ymaps.Map("map", {
			center: [55.76, 37.64],
			controls: [],
        	zoom: 10
        });
	}
}

function chooseAddress(){
	if($('#address_1').is(':checked')){
		let geo_address_=$('#owner_address_title').text();

		if(geo_address_){
			//let t='<a></a>';
			geo_address_=geo_address_.replace(/×/gi, '');
			
			var geocoder = ymaps.geocode(geo_address_);
			//map.geoObjects.removeAll(mapRoutes);

			geocoder.then(
				function (res) {
					map.geoObjects.removeAll();

					let coordinates_address=res.geoObjects.get(0).geometry.getCoordinates();
	
					map.geoObjects.add(res.geoObjects,
					{
						iconLayout: 'default#image',
						iconImageHref: '/images/home.svg',
						iconImageSize: [32, 32],
						iconImageOffset: [0, 0]
					});
					map.container.fitToViewport();
	
					if(coordinates_address.length > 0){
						//делаем отправку координат и получаем близжайшие бригады
						jQuery.ajax({
							'async': true,
							'global': false,
							'cache': false,
							'type': 'GET',
							'dataType': 'xml',
							'data': 'action=brigades_map&coordinates='+coordinates_address+'&mode=xml',
							'url': "index.php",
							'beforeSend': function() {
								//
							},
							'success': function (Recs) {
								if($(Recs).find('message').text() == 'token_invalid'){
									window.location.href = 'index.php';
								}else{
									let TempContent_='<strong>Ближайшие бригады</strong>';
									TempContent_+='<div>';
									$(Recs).find('rec').each(function(){
										TempContent_+='<div class="car">';
										TempContent_+=''+$(this).find('rec_name').text()+' ('+ $(this).find('rec_distance').text() + ' км)';
										TempContent_+='</div>';
									});
									TempContent_+='</div>';
									$("#brigades_map").html(TempContent_);
								}
							}
						});
					}
				},
				function (err) {
					// handling errors
				}
			);
		}
	}else{
		let geo_address_=$('#owner_address_request_ .tag').text();
		if(geo_address_){
			//let t='<a></a>';
			geo_address_=geo_address_.replace(/×/gi, '');
			
			var geocoder = ymaps.geocode(geo_address_);
			//map.geoObjects.removeAll(mapRoutes);

			geocoder.then(
				function (res) {
					map.geoObjects.removeAll();

					let coordinates_address=res.geoObjects.get(0).geometry.getCoordinates();
	
					map.geoObjects.add(res.geoObjects,
					{
						iconLayout: 'default#image',
						iconImageHref: '/images/home.svg',
						iconImageSize: [32, 32],
						iconImageOffset: [0, 0]
					});
					map.container.fitToViewport();
	
					if(coordinates_address.length > 0){
						//делаем отправку координат и получаем близжайшие бригады
						jQuery.ajax({
							'async': true,
							'global': false,
							'cache': false,
							'type': 'GET',
							'dataType': 'xml',
							'data': 'action=brigades_map&coordinates='+coordinates_address+'&mode=xml',
							'url': "index.php",
							'beforeSend': function() {
								//
							},
							'success': function (Recs) {
								if($(Recs).find('message').text() == 'token_invalid'){
									window.location.href = 'index.php';
								}else{
									let TempContent_='<strong>Ближайшие бригады</strong>';
									TempContent_+='<div>';
									$(Recs).find('rec').each(function(){
										TempContent_+='<div class="car">';
										TempContent_+=''+$(this).find('rec_name').text()+' ('+ $(this).find('rec_distance').text() + ' км)';
										TempContent_+='</div>';
									});
									TempContent_+='</div>';
									$("#brigades_map").html(TempContent_);
								}
							}
						});
					}
				},
				function (err) {
					// handling errors
				}
			);
		}
	}
}

function chooseAmbulanceSlot(slot){
	$(".schedule").find('.slot').each(function(){
		$(this).removeClass('selected');
	});
	
	$("#id_brigade").val($("#" + slot.id).attr("brigade"));
	$("#date_request").val($("#" + slot.id).attr("date"));
	
	$("#" + slot.id).addClass('selected');

	///

	// var options ={
    //     name: 'specialists',
    //     mode: 'normal',
	// 	new_tags: 0,
	// 	request_title: 'name',
	// 	response_id: 'rec_id',
	// 	response_title: 'rec_name',
    //     width: '100%',
    //     height: 250,
    //     max_tags_win: 10,
    //     url: "?action=specialists&org_type=46&mode=xml"
    // };
    // tags_input = new JxTag(options);
}

function changeAmbulanceOwner(){
	let owners = $('input[name="owner"]');
	let owner = '';

	if (owners.length >= 1){
		owners.each(function () {
			if($(this).prop("checked") == true){
				owner=$(this).val();
				
			}
		});
	}
	
	if(owner){
		$('#choose_owner').prop('disabled', false);
		$('#choose_owner').show();
	}else{
		$('#choose_owner').prop('disabled', true);
		$('#choose_owner').hide();
	}
}

function closeAddRequestWindow(){
	$("#background").fadeOut();
	$("#container").hide();
}

function addRequestOwner(){
	if($('#form_owners input[name="name"]').val() || $('#form_owners  input[name="telephone"]').val()){
		$('#owner_new_surname').val($('#form_owners input[name="name"]').val());
		$('#owner_new_telephone').val($('#form_owners input[name="telephone"]').val());
	}

	$('.email').mask(
		"A", {
			translation: {
				"A": { pattern: /[\w@\-.+]/, recursive: true }
			}
		}
	);

	$('#window_title').html('Добавление владельца');
	// $('.add').hide();
	// $('.next').prop('disabled', false);
	// 
	// //$('.next').html('Добавить владельца и перейти к оформлению заявки');

	
	$('#add_owner').hide();
	$('#make_request').hide();
	$('#choose_owner').width('360px');
	$('#choose_owner').prop('disabled', false);
	$('#choose_owner').html('Добавить владельца и перейти к оформлению заявки');
	$('#choose_owner').show();

	// $('.next').show();
	
	$('#add_new').val('1');
	$('#AddOwners').show();
	$('#ListOwners').hide();
	$('#FormSearch').hide();
}

function editAmbulanceOwnerContacts(Id){
	let TempContent='';

	//запрашиваем список контатов
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': '&owner='+Id+'&action=contacts',
		'url': "index.php",
		'beforeSend': function() {
			//показать загрузку
			
		},
		'success': function (Recs) {
			TempContent+='<div class="window edit main_row col-xl-4 col-lg-5 col-12">';
			TempContent+='<div class="close" onclick="closeEditWindow();"></div>';

			TempContent+='<div class="top">Изменить/добавить контакт</div>';

			TempContent+='<div class="contacts col-12">';
			if($(Recs).find('rec').text()){
				TempContent+='<div><input checked type="radio" onchange="changeAmbulanceOwner();" name="win_action" id="win_action_1" value="1"> <label for="win_action_1">Изменить контакт</label></div>';
				$(Recs).find('rec').each(function(){
					TempContent+='<div title="' + $(this).find('type_title').text() + '"';
					if($(this).find('type_id').text() == 1){
						TempContent+=' class="mobiletelephone"';
					}else if($(this).find('type_id').text() == 6){
						TempContent+=' class="mail"';
					}
					TempContent+='>';
					if($(this).find('type_id').text() == 1){
						TempContent+='<input type="text" id="' + $(this).find('id').text() + '" class="mobiletelephone_" value="' + $(this).find('name').text() + '"></div>';
					}else{
						TempContent+='<input type="text" id="' + $(this).find('id').text() + '" class="mail_" value="' + $(this).find('name').text() + '"></div>';
					}
				});
			}
			TempContent+='<div><input type="radio" ';
			if(!$(Recs).find('rec').text()){
				TempContent+='checked ';
			}
			TempContent+='onchange="changeAmbulanceOwner();" name="win_action" id="win_action_2" value="2"> <label for="win_action_2">Добавить новый контакт</label></div>';

			TempContent+='<div class="d-table-cell">';
			TempContent+='<select id="contact_new_type" onchange="changeContact();">';
			TempContent+='<option value="1">Мобильный телефон</option>';
			TempContent+='<option value="6">Электронная почта</option>';
			TempContent+='</select>';
			TempContent+='</div>';
			TempContent+='<div class="d-table-cell">';
			TempContent+='<input type="text" id="contact_new_name" class="contact_new_name" value="' + $(this).find('name').text() + '">';
			TempContent+='</div>';

			TempContent+='<input type="hidden" value='+Id+' id="contact_new_owner">';

			TempContent+='</div>';

			TempContent+='<div class="controls"><button class="ok" style="width: 130px; margin-right: 10px;" onclick="saveContacts();">Сохранить</button><button class="ok" style="width: 120px;" onclick="closeEditWindow();">Закрыть</button></div>';

			TempContent+='</div>';

			document.getElementById("sub_container_m").innerHTML=TempContent;

			$('.mobiletelephone_').mask("+7(999) 999-9999");
			changeContact();

			$("#background_m").fadeIn();
			$("#container_m").show();
		}
	});
}

function listAmbulanceShowOwnersPets(){
	$(".telephone").unmask();
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#form_owners").serialize()+'&action=owners',
		'url': "index.php",
		'beforeSend': function() {
			//показать загрузку
			showLoading('ListOwners');
			changeAmbulanceOwner();
		},
		'success': function (Recs) {
			if($(Recs).find('message').text() == 'token_invalid'){
				window.location.href = 'index.php';
			}else{
				$('.telephone').mask("+7(999) 999-9999");
				let TempContent='';
				let TempRecsNum=0;

				if($(Recs).find('rec').text()){
					TempContent+='<div class="row owners" id="ListInOwners">';

					$(Recs).find('rec').each(function(){
						let FLAG=0;
						let Id=$(this).find('rec_id').text();

						TempContentRec='<div class="owner col-12 main_row row">';
						TempContentRec+='<div class="col-xl-3 col-lg-3 col-md-12 col-sm-12 col-xs-12 col-12">';
						TempContentRec+='<input type="radio" onchange="changeAmbulanceOwner();" name="owner" id="owner_'+$(this).find('rec_id').text()+'" value="'+$(this).find('rec_id').text()+'"><label for="owner_'+$(this).find('rec_id').text()+'"><span class="name">' + $(this).find('rec_name').text() + '</span></label>';
						if($(this).find('rec_birthday').text()){
							TempContentRec+='<br><span class="birthday" title="День рождения">' + $(this).find('rec_birthday').text() + '</span>';
						}
						if($(this).find('rec_snils').text()){
							TempContentRec+='<br><span class="snils" title="СНИЛС">' + $(this).find('rec_snils').text() + '</span>';
						}
						TempContentRec+='</div>';

						TempContentRec+='<div class="col-xl-2 col-lg-2 col-md-12 col-sm-12 col-xs-12 col-12 contacts">';
						if($(this).find('rec_contacts').text()){
							$(this).find('rec_contacts').find('contact').each(function(){
								TempContentRec+='<div title="' + $(this).find('type_title').text() + '"';
								if($(this).find('type_id').text() == 1){
									TempContentRec+=' class="mobiletelephone"';
								}else if($(this).find('type_id').text() == 6){
									TempContentRec+=' class="mail"';
								}
								TempContentRec+='>';
								TempContentRec+='' + $(this).find('name').text() + '</div>';
							});
						}else{
							TempContentRec+='-';
						}
						TempContentRec+='<div class="edit" title="Добавить/изменить контакт" onclick="editAmbulanceOwnerContacts('+$(this).find('rec_id').text()+');	"></div>';
						TempContentRec+='</div>';

						TempContentRec+='<div class="col-xl-2 col-lg-2 col-md-12 col-sm-12 col-xs-12 col-12 addresses">';
						if($(this).find('rec_address').text()){
							TempContentRec+='<p class="address">' + $(this).find('rec_address').text() + '</p>';
						}else{
							TempContentRec+='-';
						}
						TempContentRec+='<input type="hidden" id="owner_address_'+$(this).find('rec_id').text()+'" value="'+$(this).find('rec_address').text()+'">';
						TempContentRec+='</div>';
						
						TempContentRec+='<div class="col-xl-5 col-lg-5 col-md-12 col-sm-12 col-xs-12 col-12 pets">';
						let i=0;
						$(this).find('rec_pets').find('pet').each(function(){
							TempContentRec+='<div class="pet">';
							TempContentRec+='<input type="checkbox" ';
							if(i==0){
								TempContentRec+='checked ';
							}
							TempContentRec+='name="pet_' + Id + '" id="pet_'+$(this).find('id').text()+'" value="'+$(this).find('id').text()+'"><label for="pet_'+$(this).find('id').text()+'">';
							if($(this).find('name').text()){
								TempContentRec+='<strong>'+ $(this).find('name').text() + '</strong>, ';
							}
							if($(this).find('sex').text() == 'f'){
								TempContentRec+='женский';
							}else{
								TempContentRec+='мужской';
							}
							if($(this).find('breed').text()){
								TempContentRec+=', ' + $(this).find('breed').text() + '';
							}
							if($(this).find('species').text()){
								TempContentRec+=', ' + $(this).find('species').text() + '';
							}
							TempContentRec+='</label></div>';
							i++;
						});
						TempContentRec+='<div class="pet">';

						TempContentRec+='<div class="d-table-cell">';
						TempContentRec+='<input type="checkbox" name="pet_' + Id + '" id="pet_' + Id + '" value="000">';
						TempContentRec+='</div>';

						TempContentRec+='<div class="d-table-cell">';
						TempContentRec+='<label for="pet_' + Id + '">';
						TempContentRec+='<select class="species" id="pet_species_' + Id + '">';
						for(let k=0; k<=ArraySpeciesRecs.length-1; k++){
							if(ArraySpeciesRecs[k].name == 'кошки' || ArraySpeciesRecs[k].name == 'собаки'){
								TempContentRec+='<option value="'+ArraySpeciesRecs[k].id+'">'+ArraySpeciesRecs[k].name+'</option>';
							}
						}
						for(let k=0; k<=ArraySpeciesRecs.length-1; k++){
							if(ArraySpeciesRecs[k].name != 'кошки' && ArraySpeciesRecs[k].name != 'собаки'){
								TempContentRec+='<option value="'+ArraySpeciesRecs[k].id+'">'+ArraySpeciesRecs[k].name+'</option>';
							}
						}
						TempContentRec+='</select>';
						TempContentRec+='</label>';
						TempContentRec+='</div>';

						TempContentRec+='<div class="d-table-cell">';
						TempContentRec+='<label for="pet_' + Id + '">';
						TempContentRec+='<select class="sex" id="pet_sex_' + Id + '">';
						TempContentRec+='<option value="m">Мужской</option>';
						TempContentRec+='<option value="f">Женский</option>';
						TempContentRec+='</select>';
						TempContentRec+='</label>';
						TempContentRec+='</div>';

						TempContentRec+='<div class="d-table-cell" style="padding-left: 5px;">';
						TempContentRec+='<label for="pet_' + Id + '">';
						TempContentRec+='<input autocomplete="off" type="text" id="pet_name_' + Id + '" value="" placeholder="Введите кличку...">';
						TempContentRec+='</label>';
						TempContentRec+='</div>';

						TempContentRec+='</div>';

						TempContentRec+='</div>';

						TempContentRec+='</div>';

						

						if(FLAG == 0){
							TempRecsNum++;
							TempContent+=TempContentRec;
						}
					});

					TempContent+='</div>';
				}

				if($(Recs).find('message').text() == 'service_not_selected'){
					
				}else{
					if(TempRecsNum == 0){
						TempContent='<div class="message">По данному запросу не найдено записей.</div>';
					}
				}
				$('.add').show();
				document.getElementById('ListOwners').innerHTML=TempContent;
				$('.species').multiselect({buttonWidth: '100%', maxHeight: 300, enableFiltering: true, enableCaseInsensitiveFiltering : true, filterPlaceholder: 'Выбрать вид животного...'});
			}

			$("#ListInOwners").height($("#add_request_window").height()-300);
		}
	});

	
}

function chooseRequestOwner(){
	if($('#add_new').val() == 1){
		$('#owner_new_surname').removeClass("error_field");
		$('#owner_new_name').removeClass("error_field");
		$('#owner_new_telephone').removeClass("error_field");
		$('#owner_new_address_').removeClass("error_field");
		$('#pet_new_name').removeClass("error_field");
		$("#request_window_message").hide();

		if($('#owner_new_surname').val() == '' || $('#owner_new_name').val() == '' || $('#owner_new_telephone').val() == '' || $('#owner_new_address').val() == '' || $('#pet_new_name').val() == ''){
			$("#request_window_message").html('Не заполнены необходимые поля.');
			$("#request_window_message").show();

			if($('#owner_new_surname').val() == ''){
				$('#owner_new_surname').addClass("error_field");
			}
			if($('#owner_new_name').val() == ''){
				$('#owner_new_name').addClass("error_field");
			}
			if($('#owner_new_telephone').val() == ''){
				$('#owner_new_telephone').addClass("error_field");
			}
			if($('#owner_new_address').val() == ''){
				$('#owner_new_address_').addClass("error_field");
			}
			if($('#pet_new_name').val() == ''){
				$('#pet_new_name').addClass("error_field");
			}

			return true;
		}
	}

	$('#window_title').html('Оформление заявки');

	$('#AddOwners').hide();
	$('#ListOwners').hide();
	$('#FormSearch').hide();

	//меняем кнопочки
	$('#add_owner').hide();
	$('#choose_owner').hide();
	$('#make_request').show();
	//меняем кнопочки

	$('#AddRequest').show();

	//ищем выбранного пользователя
	let owners = $('input[name="owner"]');
	let address = $("#owner_new_address_").text();
	let address_map = '';
	
	if (owners.length >= 1){
		owners.each(function () {
			if($(this).prop("checked") == true){
				owner=$(this).val();
				$("#owner_address_title").html($("#owner_address_"+owner).val());
				$("#address1").val($("#owner_address_"+owner).val());

				address_map = $("#owner_address_"+owner).val();
			}
		});
	}

	if(address){
		address=address.slice(0, -1);
		$("#owner_address_title").html(address);
		$("#address1").val(address);

		address_map = address;
	}

	$("#AddRequest").height($("#add_request_window").height()-210);

	if(address_map){
		var geocoder = ymaps.geocode(address_map);

		geocoder.then(
			function (res) {
				let coordinates_address=res.geoObjects.get(0).geometry.getCoordinates();

				map.geoObjects.add(res.geoObjects,
				{
					iconLayout: 'default#image',
					iconImageHref: '/images/home.svg',
					iconImageSize: [32, 32],
					iconImageOffset: [0, 0]
				});
				map.container.fitToViewport();

				//console.log(coordinates_address.length);
				if(coordinates_address.length > 0){
					//делаем отправку координат и получаем близжайшие бригады
					jQuery.ajax({
						'async': true,
						'global': false,
						'cache': false,
						'type': 'GET',
						'dataType': 'xml',
						'data': 'action=brigades_map&coordinates='+coordinates_address+'&mode=xml',
						'url': "index.php",
						'beforeSend': function() {
							//
						},
						'success': function (Recs) {
							if($(Recs).find('message').text() == 'token_invalid'){
								window.location.href = 'index.php';
							}else{
								let TempContent_='<strong>Ближайшие бригады</strong>';
								TempContent_+='<div>';
								$(Recs).find('rec').each(function(){
									TempContent_+='<div class="car">';
									TempContent_+=''+$(this).find('rec_name').text()+' ('+ $(this).find('rec_distance').text() + ' км)';
									TempContent_+='</div>';
								});
								TempContent_+='</div>';
								$("#brigades_map").html(TempContent_);
							}
						}
					});
				}

				
				// let mapObj = new ymaps.GeoObjectCollection({}, {draggable: false});
				// mapObj.add(new ymaps.Placemark(res.geoObjects,
				// {
				// 	iconLayout: 'default#image',
				// 	iconImageHref: '/images/home.svg',
				// 	iconImageSize: [32, 32],
				// 	iconImageOffset: [0, 0]
				// }
				// ));
				// map.geoObjects.add(mapObj);
			},
			function (err) {
				// handling errors
			}
		);
	}
}

function makeRequest(){
	let owner = '';
	let address = '';
	let pet_name='';
	let pet_species='';
	let pet_breeds='';
	let pet_sex='';

	let owner_surname='';
	let owner_name='';
	let owner_secondname='';
	let owner_telephone='';
	let owner_address='';
	
	let is_veteran=0;
	let is_disabled=0;
	let is_blind=0;
	let is_orphan=0;
	let is_large_family=0;
	let is_veteran_of_labour=0;

	//льготы
	if($('#is_veteran').prop("checked") == true){is_veteran=1;}
	if($('#is_disabled').prop("checked") == true){is_disabled=1;}
	if($('#is_blind').prop("checked") == true){is_blind=1;}
	if($('#is_orphan').prop("checked") == true){is_orphan=1;}
	if($('#is_large_family').prop("checked") == true){is_large_family=1;}
	if($('#is_veteran_of_labour').prop("checked") == true){is_veteran_of_labour=1;}
		
	let pets_='';

	if($('#add_new').val() == 1){//заводим нового владельца и питомца
		owner_surname=$('#owner_new_surname').val();
		owner_name=$('#owner_new_name').val();
		owner_secondname=$('#owner_new_secondname').val();
		$(".telephone").unmask();
		owner_telephone='+7'+$('#owner_new_telephone').val();
		$('.telephone').mask("+7(999) 999-9999");

		$(".email").unmask();
		owner_email=$('#owner_new_email').val();
		$('.email').mask(
			"A", {
				translation: {
					"A": { pattern: /[\w@\-.+]/, recursive: true }
				}
			}
		);

		pet_name=$('#pet_new_name').val();
		pet_species=$('#pet_new_species').val();
		pet_breeds=$('#pet_new_breeds').val();
		pet_sex=$('#pet_new_sex').val();
	}else{
		//ищем выбранного пользователя
		let owners = $('input[name="owner"]');
		if (owners.length >= 1){
			owners.each(function () {
				if($(this).prop("checked") == true){
					owner=$(this).val();
					address=$("#owner_address_"+owner).val();
				}
			});
		}

		//ищем выбранных питомцев
		let pets = $('input[name="pet_' + owner + '"]');
		
		if (pets.length > 0){
			pets.each(function () {
				if($(this).prop("checked") == true){
					if(pets_){pets_+=',';}
					if($(this).val() == '000'){
						pet_name=$('#pet_name_'+owner).val();
						pet_species=$('#pet_species_'+owner).val();
						pet_sex=$('#pet_sex_'+owner).val();
						pets_+='{"id":"000","name":"'+pet_name+'","sex":"'+pet_sex+'","species":"'+pet_species+'"}';
					}else{
						pets_+='{"id":"'+$(this).val()+'"}';
					}
				}
			});
		}
	}

	let id_brigade=$('#id_brigade').val();//считываем из расписания
	let date_request=$('#date_request').val();//считываем из расписания
	let time_request=$('#time_request').val();;//считываем из поля
	let call_reason=$('#call_reason').val();;//считываем из поля
	let description=$('#description').val();;//считываем из поля
	
	let request_address='';
	let fias=0;
	if($('input[name="address_"]:checked').val() == '1'){
		request_address=$('#address1').val();;//считываем из поля		
	}else if($('input[name="address_"]:checked').val() == '2'){
		request_address=$('#owner_address_request').val();
		fias=1;
	}

	let data='';
	data+='add_new='+$('#add_new').val()+'';//новый владелец//плодим дубли
	data+='&owner='+owner+'';
	data+='&pets='+pets_+'';
	data+='&date='+date_request+'';
	data+='&time='+time_request+'';
	data+='&request_address='+request_address+'';
	data+='&fias='+fias+'';
	
	owner_address=$('#owner_new_address').val();
	//новый владелец
	data+='&owner_name='+owner_name+'';
	data+='&owner_secondname='+owner_secondname+'';
	data+='&owner_surname='+owner_surname+'';
	data+='&owner_telephone='+owner_telephone+'';
	data+='&owner_address='+owner_address+'';

	data+='&pet_name='+pet_name+'';
	data+='&pet_sex='+pet_sex+'';
	data+='&pet_species='+pet_species+'';
	data+='&pet_breeds='+pet_breeds+'';
	//новый владелец

	data+='&is_veteran='+is_veteran+'';
	data+='&is_disabled='+is_disabled+'';
	data+='&is_blind='+is_blind+'';
	data+='&is_orphan='+is_orphan+'';
	data+='&is_large_family='+is_large_family+'';
	data+='&is_veteran_of_labour='+is_veteran_of_labour+'';

	data+='&brigade='+id_brigade+'';
	data+='&call_reason='+call_reason+'';
	data+='&description='+description+'';
	data+='&action=make_request';

	// console.log(data);

	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': data,
		'url': "index.php",
		'beforeSend': function() {
			//блокируем кнопку
			$('#make_request').prop('disabled', true);
			//показать загрузку
		},
		'success': function (Data) {
			if($(Data).find('message').text() == 'request_created'){
				closeAddRequestWindow();
				// let message='';
				// for(let i=0; i<=ArrayTemplatesRecs.length-1; i++){
				// 	if(ArrayTemplatesRecs[i].code == $(Data).find('message').text()){
				// 		message=ArrayTemplatesRecs[i].message;
				// 	}
				// }
				// message=message.replace(/{date}/gi, $(Data).find('date').text());
				// message=message.replace(/{time}/gi, $(Data).find('time').text());
				// message=message.replace(/{duration}/gi, $(Data).find('duration').text());
				// message=message.replace(/{organization}/gi, $(Data).find('organization').text());
				// message=message.replace(/{address}/gi, $(Data).find('address').text());
				// message=message.replace(/{specialist}/gi, $(Data).find('specialist').text());
				// message=message.replace(/{services}/gi, $(Data).find('services').text());
				// message=message.replace(/{ticket_number}/gi, $(Data).find('ticket_number').text());
				// showMessageWindow(message);
				showMessageWindow('Заявка успешно создана.');
				
				listShowRequests();
			}
			if($(Data).find('message').text() == 'empty_fields'){
				$("#AddRequest").height($("#add_request_window").height()-320);

				$("#request_window_message").html('Не заполнены необходимые поля.');
				$("#request_window_message").show();
				$('#make_request').prop('disabled', false);
			}
		}
	});
}

function showRequestWindow(Id){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': '&id='+Id+'&mode=xml&action=request',
		'url': "index.php",
		'beforeSend': function() {
			//показать загрузку
			showTopLoading();
		},
		'success': function (Data) {
			closeTopLoading();
			////////////
			let TempContent='';

			TempContent+='<div id="add_request_window" class="window main_row col-xl-8 col-lg-10 col-12">';
			TempContent+='<div class="close" onclick="closeRequestWindow();"></div>';
			TempContent+='<div class="top">Просмотр заявки</div>';

			if($(Data).find('rec_status').text() == 'N'){
				TempContent+='<div class="row main_row control"><div class="col-12 row-right-content">';
				TempContent+='<button class="button cancel" id="cancel_request" style="width: 200px; margin-right: 5px;" onclick="cancelRequest();">Отменить заявку</button>';
				TempContent+='<button class="button accept" id="accept_request" style="width: 200px;" onclick="acceptRequest();">Принять вызов</button>';
				TempContent+='</div></div>';
			}

			if($(Data).find('rec_status').text() == 'P'){
				TempContent+='<div class="row main_row control"><div class="col-12 row-right-content">';
				TempContent+='<button class="button cancel" id="cancel_request" style="width: 200px; margin-right: 5px;" onclick="cancelRequest();">Отменить заявку</button>';
				TempContent+='<button class="button accept" id="inwork_request" style="width: 200px;" onclick="inworkRequest();">Взять в работу</button>';
				TempContent+='</div></div>';
			}
			
			TempContent+='<div class="w-100" id="window_save_request" style="overflow-y: auto;">';

			TempContent+='<div class="col-xl-6 col-lg-9 main_row">';
			TempContent+='<form class="form-window" id="form_save_request">';
				
			TempContent+='<div class="error" id="appointment_window_message" style="display: none;"></div>';

			TempContent+='<div class="w-100 d-table">';

			///
			TempContent+='<input type="hidden" name="id_request" id="id_request" value="'+$(Data).find('rec_id_request').text()+'">';
			TempContent+='<div class="d-table-row title">Основная информация</div>';
			
			TempContent+='<div class="d-table-row">';
			TempContent+='<div class="d-table-cell">Номер заявки:</div>';TempContent+='<div class="d-table-cell">'+$(Data).find('rec_id_request').text()+'</div>';
			TempContent+='</div>';
			TempContent+='<div class="d-table-row">';
			TempContent+='<div class="d-table-cell">Состояние заявки:</div>';
			TempContent+='<div class="d-table-cell"><strong>';

			if($(Data).find('rec_status').text() == 'N'){TempContent+='Новая';}
			if($(Data).find('rec_status').text() == 'W'){TempContent+='В работе';}
			if($(Data).find('rec_status').text() == 'A'){TempContent+='Отменена';}
			if($(Data).find('rec_status').text() == 'F'){TempContent+='Завершена';}
			if($(Data).find('rec_status').text() == 'P'){TempContent+='Принято';}

			TempContent+='</strong></div>';
			TempContent+='</div>';
			TempContent+='<div class="d-table-row">';
			TempContent+='<div class="d-table-cell">Причина вызова:</div>';TempContent+='<div class="d-table-cell">'+$(Data).find('rec_call_reason').text()+'</div>';
			TempContent+='</div>';
			///

			///
			TempContent+='<div class="d-table-row title">Владелец и животное</div>';
			
			TempContent+='<div class="d-table-row">';
			TempContent+='<div class="d-table-cell">ФИО владельца:</div>';TempContent+='<div class="d-table-cell">'+$(Data).find('rec_owner_name').text()+'</div>';
			TempContent+='</div>';
			TempContent+='<div class="d-table-row">';
			TempContent+='<div class="d-table-cell">Телефон:</div>';
			TempContent+='<div class="d-table-cell contacts">';

			if($(Data).find('rec_contacts').text()){
				$(Data).find('rec_contacts').find('contact').each(function(){
					if($(this).find('type_id').text() == 1){
						TempContent+='<div title="' + $(this).find('type_title').text() + '"';
						if($(this).find('type_id').text() == 1){
							TempContent+=' class="mobiletelephone"';
						}else if($(this).find('type_id').text() == 6){
							TempContent+=' class="mail"';
						}
						TempContent+='>';
						TempContent+='' + $(this).find('name').text() + '</div>';
					}
				});
			}
			TempContent+='</div>';


			TempContent+='</div>';
			TempContent+='<div class="d-table-row">';
			TempContent+='<div class="d-table-cell">Адрес:</div>';TempContent+='<div class="d-table-cell">'+$(Data).find('rec_address').text()+'</div>';
			TempContent+='</div>';
			TempContent+='<div class="d-table-row">';
			TempContent+='<div class="d-table-cell">Льготные категории:</div>';
			TempContent+='<div class="d-table-cell checklist">';

			if($(Data).find('rec_is_veteran').text() == 't'){TempContent+='<div class="checked">Ветеран ВОВ</div>';}else{TempContent+='<div>Ветеран ВОВ</div>';}
			if($(Data).find('rec_is_disabled').text() == 't'){TempContent+='<div class="checked">Инвалид 1 группы</div>';}else{TempContent+='<div>Инвалид 1 группы</div>';}
			if($(Data).find('rec_is_family_disabled_children').text() == 't'){TempContent+='<div class="checked">Семьи, воспитывающие детей-инвалидов в возрасте 23 лет</div>';}else{TempContent+='<div>Семьи, воспитывающие детей-инвалидов в возрасте 23 лет</div>';}
			if($(Data).find('rec_is_blind').text() == 't'){TempContent+='<div class="checked">Ивалиды по зрению, имеющие собак проводников</div>';}else{TempContent+='<div>Ивалиды по зрению, имеющие собак проводников</div>';}
			
			// if($(Data).find('rec_is_orphan').text() == 't'){TempContent+='<div class="checked">Дети-сироты, дети, оставшиеся без попечения родителей в возрасте до 23 лет</div>';}else{TempContent+='<div>Дети-сироты, дети, оставшиеся без попечения родителей в возрасте до 23 лет</div>';}
			// if($(Data).find('rec_is_large_family').text() == 't'){TempContent+='<div class="checked">Многодетные семьи</div>';}else{TempContent+='<div>Многодетные семьи</div>';}
			// if($(Data).find('rec_is_veteran_of_labour').text() == 't'){TempContent+='<div class="checked">Ветераны труда</div>';}else{TempContent+='<div>Ветераны труда</div>';}

			TempContent+='</div>';
			TempContent+='</div>';

			$(Data).find('rec_pets').find('pet').each(function(){
				TempContent+='<div class="d-table-row title2">Животное</div>';

				TempContent+='<div class="d-table-row">';
				TempContent+='<div class="d-table-cell">Кличка:</div>';TempContent+='<div class="d-table-cell">'+$(this).find('name').text()+'</div>';
				TempContent+='</div>';
				TempContent+='<div class="d-table-row">';
				TempContent+='<div class="d-table-cell">Вид животного:</div>';TempContent+='<div class="d-table-cell">'+$(this).find('specie').text()+'</div>';
				TempContent+='</div>';
				TempContent+='<div class="d-table-row">';
				TempContent+='<div class="d-table-cell">Порода:</div>';TempContent+='<div class="d-table-cell">'+$(this).find('breed').text()+'</div>';
				TempContent+='</div>';
				TempContent+='<div class="d-table-row">';
				TempContent+='<div class="d-table-cell">Пол:</div>';TempContent+='<div class="d-table-cell">';
				if($(this).find('sex').text() == 'm'){
					TempContent+='мужской';
				}else{
					TempContent+='женский';
				}
				TempContent+='</div>';
				TempContent+='</div>';
				TempContent+='<div class="d-table-row">';
				TempContent+='<div class="d-table-cell">Возраст:</div>';TempContent+='<div class="d-table-cell">'+$(this).find('age').text()+'</div>';
				TempContent+='</div>';
			});

			///
			TempContent+='<div class="d-table-row title">Заявка</div>';
			
			TempContent+='<div class="d-table-row">';
			TempContent+='<div class="d-table-cell">Наименование организации:</div>';TempContent+='<div class="d-table-cell">'+$(Data).find('rec_org_name').text()+'</div>';
			TempContent+='</div>';
			TempContent+='<div class="d-table-row">';
			TempContent+='<div class="d-table-cell">Наименование бригады:</div>';TempContent+='<div class="d-table-cell">'+$(Data).find('rec_brigade').text()+'</div>';
			TempContent+='</div>';
			TempContent+='<div class="d-table-row">';
			TempContent+='<div class="d-table-cell">ФИО специалистов состоящих в бригаде:</div>';TempContent+='<div class="d-table-cell">'+$(Data).find('rec_specialists').text()+'</div>';
			TempContent+='</div>';
			TempContent+='<div class="d-table-row">';
			
			TempContent+='<div class="d-table-cell">Плановое начало приема:</div>';
			TempContent+='<div class="d-table-cell">'+$(Data).find('rec_start_dttm').text()+'</div>';
			// TempContent+='<div class="d-table-cell">'+$(Data).find('rec_start_date').text()+' '+$(Data).find('rec_start_time').text()+'</div>';

			TempContent+='</div>';
			TempContent+='<div class="d-table-row">';
			TempContent+='<div class="d-table-cell">Дата/время передачи вызова бригаде:</div>';TempContent+='<div class="d-table-cell">'+$(Data).find('rec_created_at').text()+'</div>';
			TempContent+='</div>';

			if($(Data).find('rec_start_request_date').text()){
				TempContent+='<div class="d-table-row">';
				TempContent+='<div class="d-table-cell">Получение вызова бригадой:</div>';TempContent+='<div class="d-table-cell">'+$(Data).find('rec_start_request_date').text()+'</div>';
				TempContent+='</div>';
			}
			if($(Data).find('rec_end_request_date').text()){
				TempContent+='<div class="d-table-row">';
				TempContent+='<div class="d-table-cell">Приезд на вызов:</div>';TempContent+='<div class="d-table-cell">'+$(Data).find('rec_end_request_date').text()+'</div>';
				TempContent+='</div>';
			}
			if($(Data).find('rec_fact_start_dttm').text()){
				TempContent+='<div class="d-table-row">';
				TempContent+='<div class="d-table-cell">Фактическое начало приема:</div>';TempContent+='<div class="d-table-cell">'+$(Data).find('rec_fact_start_dttm').text()+'</div>';
				TempContent+='</div>';
			}
			if($(Data).find('rec_fact_end_dttm').text()){
				TempContent+='<div class="d-table-row">';
				TempContent+='<div class="d-table-cell">Фактическое окончание приема:</div>';TempContent+='<div class="d-table-cell">'+$(Data).find('rec_fact_end_dttm').text()+'</div>';
				TempContent+='</div>';
			}

			TempContent+='<div class="d-table-row">';
			TempContent+='<div class="d-table-cell">Примечание:</div>';
			TempContent+='<div class="d-table-cell"><textarea id="description" name="description" rows="3">'+$(Data).find('rec_description').text()+'</textarea></div>';
			TempContent+='</div>';
			
			///

			TempContent+='</div>';//table
			
			TempContent+='</div>';
			TempContent+='</form>';

			TempContent+='</div>';
			
			//Кнопки управления
			TempContent+='<div class="controls">';
			TempContent+='<button class="button" id="save_request" style="width: 220px;" onclick="saveRequest();">Сохранить</button>';
			TempContent+='</div>';
			//Кнопки управления

			TempContent+='</div>';

			document.getElementById("sub_container").innerHTML=TempContent;
			$("#background").fadeIn();
			$("#container").show();
			///////

			$("#window_save_request").height($("#add_request_window").height()-210);
		}
	});
}

function closeRequestWindow(){
	$("#background").fadeOut();
	$("#container").hide();
}

function saveRequest(){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#form_save_request").serialize()+'&action=save_request&mode=xml',
		'url': "index.php",
		'beforeSend': function() {
			//блокируем кнопку
			$('#save_request').prop('disabled', true);
	
			//показать загрузку
			showTopLoading();
		},
		'success': function (Data) {
			if($(Data).find('message').text() == 'request_saved'){
				showRequestWindow($(Data).find('id_request').text());
			}
			closeTopLoading();
		}
	});
}

function cancelRequest(){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#form_save_request").serialize()+'&action=cancel_request&mode=xml',
		'url': "index.php",
		'beforeSend': function() {
			//блокируем кнопку
			$('#cancel_request').prop('disabled', true);
	
			//показать загрузку
			showTopLoading();
		},
		'success': function (Data) {
			if($(Data).find('message').text() == 'request_canceled'){
				showRequestWindow($(Data).find('id_request').text());
				listShowRequests();
			}
			closeTopLoading();
		}
	});
}

function acceptRequest(){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#form_save_request").serialize()+'&action=accept_request&mode=xml',
		'url': "index.php",
		'beforeSend': function() {
			//блокируем кнопку
			$('#accept_request').prop('disabled', true);
	
			//показать загрузку
			showTopLoading();
		},
		'success': function (Data) {
			if($(Data).find('message').text() == 'request_accepted'){
				showRequestWindow($(Data).find('id_request').text());
				listShowRequests();
				closeTopLoading();
			}
			if($(Data).find('message').text() == 'request_error'){
				$('#accept_request').prop('disabled', false);
				closeTopLoading();
				showMessageWindow('Для принятия вызова по новой заявке необходимо взять в работу предыдущую.');
			}
		}
	});
}

function inworkRequest(){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#form_save_request").serialize()+'&action=inwork_request&mode=xml',
		'url': "index.php",
		'beforeSend': function() {
			//блокируем кнопку
			$('#inwork_request').prop('disabled', true);
	
			//показать загрузку
			showTopLoading();
		},
		'success': function (Data) {
			if($(Data).find('message').text() == 'request_inwork'){
				showRequestWindow($(Data).find('id_request').text());
				listShowRequests();
			}
			closeTopLoading();
		}
	});
}

loadSpecies();
loadBreeds();
getFiasTokken();

//loadAmbulanceServiceTypes();
loadAmbulanceSlots();
