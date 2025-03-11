var CurrentSlotId='';

function showAppointmentWindow(){
	//делаем бронирование
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': '&date='+CurrentDate+'&time='+CurrentTime+'&org_id='+CurrentOrgId+'&spec_id='+CurrentSpecId+'&services='+CurrentServices+'&action=make_booking',
		'url': "index.php",
		'beforeSend': function() {
			
		},
		'success': function (Data) {
			if($(Data).find('message').text() == 'booking_created'){
				//всё ок
				CurrentVisitId=$(Data).find('id').text();
			}
		}
	});
	
	//рисуем окошко
	console.log();

	//запрашиваем список владельцев
	
	let TempContent='';

	TempContent+='<div id="add_appoitment_window" class="window main_row col-xl-8 col-lg-10 col-12">';
	TempContent+='<div class="close" onclick="closeAppointmentWindow();"></div>';
	TempContent+='<div class="top">Запись на приём</div>';
	TempContent+='<div class="row main_row control">';
	TempContent+='<div class="col-6" id="window_title">Поиск владельца</div>';
	TempContent+='</div>';

	TempContent+='<div class="row main_row specialist">';
	TempContent+='<div class="col-12">';
	TempContent+='<div class="title">' + $("#slot_container_" + CurrentSlotId).attr("specialist") + '</div>';

	let specializations=$("#slot_container_" + CurrentSlotId).attr("specializations");
	if(specializations != ''){
		let arraySpecializations = specializations.split(';');
		TempContent+='<div class="specializations" style="display: inline-block;">';
		for(i=0; i<=arraySpecializations.length-1; i++){
			TempContent+='<div class="item">'+arraySpecializations[i]+'</div>';
		}
		TempContent+='</div>';
	}
	
	TempContent+='</div>';
	TempContent+='</div>';

	TempContent+='<div id="FormSearch" class="search">';
	TempContent+='<form id="form_owners" onsubmit="ListShowOwnersPets(); return false;">';
	TempContent+='<div class="d-inline-flex"><div class="label">ФИО или наименование владельца:&nbsp;</div><div class="input"><input type="text" name="name" class="name_" value="" autocomplete="off"></div></div>';
	TempContent+='<div class="d-inline-flex"><div class="label">Телефон:&nbsp;</div><div class="input"><input type="text" name="telephone" value="" autocomplete="off" class="telephone"></div></div>';
	TempContent+='<div class="d-inline-flex"><button type="submit">Поиск</button></div>';
	TempContent+='</form>';
	TempContent+='</div>';

	TempContent+='<div class="error margin-15" id="appointment_window_message" style="display: none;"></div>';
	
	
	TempContent+='<div id="AddOwners" class="add_form" style="display: none;">';
	TempContent+='<form id="form_add_owners" class="col-xl-6 col-lg-6">';
	TempContent+='<input type="hidden" id="add_new" value=""></input>';

	//TempContent+='<div class="error" id="appointment_window_message" style="display: none;"></div>';

	TempContent+='<div class="w-100 d-table">';

	TempContent+='<div class="d-table-row title">Владелец</div>';

	TempContent+='<div class="d-table-row">';
	TempContent+='<div class="d-table-cell required">Фамилия:</div>';
	TempContent+='<div class="d-table-cell"><input type="text" id="owner_new_surname" class="name_" value="" autocomplete="off"></div>';
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
	TempContent+='<div class="d-table-cell">Почта:</div>';
	TempContent+='<div class="d-table-cell"><input type="text" id="owner_new_email" class="email" value="" autocomplete="off"></div>';
	TempContent+='</div>';

	TempContent+='<div class="d-table-row">';
	//required
	TempContent+='<div class="d-table-cell">Фактический адрес:</div>';
	TempContent+='<div class="d-table-cell"><textarea id="owner_address" name="owner_address" class="JxTag"></textarea></div>';
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

	TempContent+='<div class="d-table-row">';
	TempContent+='<div class="d-table-cell required">Порода:</div>';
	TempContent+='<div class="d-table-cell">';
	TempContent+='<select class="breeds" id="pet_new_breed">';
	TempContent+='<option value="">Не выбрана</option>';
	for(let k=0; k<=ArrayBreedsRecs.length-1; k++){
		TempContent+='<option value="'+ArrayBreedsRecs[k].id+'" species_id="'+ArrayBreedsRecs[k].species+'">'+ArrayBreedsRecs[k].name+'</option>';
	}
	TempContent+='</select>';
	TempContent+='</div>';
	TempContent+='</div>';

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

	TempContent+='<div class="results" id="ListOwners"><div class="message">Для поиска владельца введите параметры поиска.</div></div>';
	TempContent+='<div class="controls">';
	TempContent+='<button class="add" style="width: 220px; display: none;" id="add_owner" onclick="addOwner();">Добавить владельца</button>';
	TempContent+='<button class="next" id="make_appointment" style="width: 185px;" onclick="makeAppointment();">Оформить приём</button>';
	TempContent+='</div>';

	TempContent+='</div>';

	document.getElementById("sub_container").innerHTML=TempContent;
	$("#background").fadeIn();
	$("#container").show();
	changeOwner();
	$('.name_').mask("R", {
		translation: {
			"R": { pattern: /[А-Яа-яё\s+]/, recursive: true }
		}
	});
	$('.telephone').mask("+7(999) 999-9999");
	$('.species').multiselect({buttonWidth: '100%', maxHeight: 300, enableFiltering: true, enableCaseInsensitiveFiltering : true});
	$('.breeds').multiselect({buttonWidth: '100%', maxHeight: 300, enableFiltering: true, enableCaseInsensitiveFiltering : true});

	var options ={
        name: 'owner_address',
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
    addresses_1_input = new JxTag(options);
}

function closeAppointmentWindow(){
	//отменяем бронирование
	if(CurrentVisitId){
		jQuery.ajax({
			'async': true,
			'global': false,
			'cache': false,
			'type': 'GET',
			'dataType': 'xml',
			'data': '&visit='+CurrentVisitId+'&action=delete_booking',
			'url': "index.php",
			'beforeSend': function() {
				
			},
			'success': function (Data) {
				if($(Data).find('message').text() == 'booking_deleted'){
					//всё ок
					CurrentVisitId=$(Data).find('id').text();
				}
			}
		});
	}

	$("#background").fadeOut();
	$("#container").hide();
}

function putSlot(org, spec, date, time, slot, duration){
	let slots_num=duration/10;

	let arrayTime = time.split(':');
	let start_time_h=parseInt(arrayTime[0]);
	let start_time_m=parseInt(arrayTime[1]);

	//выбранный слот
	let time_=convert_num(start_time_h) + '_' + convert_num(start_time_m);
	let IdSlot=''+slot+'_' + time_ + '';
	let CheckedSlot=IdSlot;
	
	//если выбран тот же слот удаляем пометки
	$('.minislot').removeClass('checked');
	$('.appointment').hide();

	if(CurrentSlot != CheckedSlot){
		for(i=1;i<=slots_num; i++){
			let time_=convert_num(start_time_h) + '_' + convert_num(start_time_m);
			let IdSlot_=''+slot+'_' + time_ + '';
			$('#slot_'+IdSlot_).addClass('checked');
	
			start_time_m+=10;
			if(start_time_m == 60){
				start_time_h++;
				start_time_m=0;
			}
		}

		CurrentTime=time;
		CurrentDate=date;
		CurrentOrgId=org;
		CurrentSpecId=spec;
		CurrentSlotId=slot;
		
		CurrentSlot=CheckedSlot;
		$('#slot_' + slot).show();
	}else{
		CurrentSlot='';
	}
}

function showSlots(Recs, Type){
	let TempContent='';
	let TempRecsNum=0;
	let ArraySlotsRecs=new Array();//заводим массив для сортировки по первому свободному слоту

	$(Recs).find('rec').each(function(){
		let FLAG=0;
		let NumFreeSlots=0;

		TempContentRec='<div class="rec col-12 main_row row" id="slot_container_' + $(this).find('rec_id').text() + '" specialist="' + $(this).find('rec_doc_name').text() + '" specializations="' + $(this).find('rec_doc_specializations').text() + '">';
		TempContentRec+='<div class="col-xl-3 col-lg-3 col-md-12 col-sm-12 col-xs-12 col-12"><span class="title">' + $(this).find('rec_doc_name').text() + '</span><br>';

		if($(this).find('rec_doc_specializations').text()){
			let specializations=$(this).find('rec_doc_specializations').text();
			let arraySpecializations = specializations.split(';');
			TempContentRec+='<div class="specializations">';
			for(i=0; i<=arraySpecializations.length-1; i++){
				TempContentRec+='<div class="item">'+arraySpecializations[i]+'</div>';
			}
			TempContentRec+='</div>';
		}

		TempContentRec+='<span class="org_name">' + $(this).find('rec_org_name').text() + '</span><br>';

		TempContentRec+='<span class="org_area">(';
		if($(this).find('rec_org_area').text()){
			TempContentRec+='' + $(this).find('rec_org_area').text() + ', ';
		}
		if($(this).find('rec_org_district').text()){
			TempContentRec+='' + $(this).find('rec_org_district').text() + ', ';
		}
		if($(this).find('rec_org_address').text()){
			TempContentRec+='' + $(this).find('rec_org_address').text() + '';
		}
		TempContentRec+=')</span>';

		$(this).find('rec_org_stations').find('station').each(function(){
			TempContentRec+='<div class="station" title="' + $(this).find('line').text() + '">';
			TempContentRec+='<div class="metro_station" style="background: #' + $(this).find('color').text() + ';"></div>';
			TempContentRec+='<div class="metro_title">' + $(this).find('title').text() + ' (' + $(this).find('distance').text() + ' км)</div>';
			TempContentRec+='</div>';
		});

		let Id=$(this).find('rec_id').text();

		TempContentRec+='</div>';
		TempContentRec+='<div class="col-xl-9 col-lg-9 col-md-12 col-sm-12 col-xs-12 col-12">'
		let date = new Date($(this).find('rec_start_date').text());

		TempContentRec+='<span class="date">' + date.toLocaleString('ru', {year: 'numeric', month: 'long', day: 'numeric'}) + '</span>';

		let Time1=0;
		let Time2=0;
		let Time3=0;
		let Time4=0;

		TempOrgId=$(this).find('rec_org_id').text();
		TempIdSpecialist=$(this).find('rec_doc_id').text();

		TempContentRec+='<div class="minislots">';
		$(this).find('rec_slots').find('slot').each(function(){
			let IdSlot=''+Id+'_' + $(this).find('time').text() + '';
			IdSlot=IdSlot.replace(/:/gi, '_');

			if($(this).find('status').text() == 'VISIT' || $(this).find('status').text() == 'UNAVAILABLE' || $(this).find('status').text() == 'INSUFFICIENT_DURATION' || $(this).find('status').text() == 'MOS'){
				TempContentRec+='<div class="minislot ';
				if($(this).find('status').text() == 'VISIT'){
					TempContentRec+='visit';
				}else if($(this).find('status').text() == 'UNAVAILABLE'){
					TempContentRec+='unavailable';
				}else if($(this).find('status').text() == 'INSUFFICIENT_DURATION'){
					TempContentRec+='insufficient_duration';
				}else if($(this).find('status').text() == 'MOS'){
					TempContentRec+='mos';
				}
				TempContentRec+='"';
				
				TempContentRec+=' title="';
				if($(this).find('status').text() == 'VISIT'){
					TempContentRec+='Занято';
				}else if($(this).find('status').text() == 'UNAVAILABLE'){
					TempContentRec+='Недоступно';
				}else if($(this).find('status').text() == 'INSUFFICIENT_DURATION'){
					TempContentRec+='Недоступно по времени';
				}else if($(this).find('status').text() == 'MOS'){
					TempContentRec+='Слот доступен на mos.ru';
				}
				
				TempContentRec+='" id="slot_'+IdSlot+'">' + $(this).find('time').text() + '</div>';

			}else{
				NumFreeSlots++;
				TempContentRec+='<div class="minislot free" title="Свободно" id="slot_'+IdSlot+'" onclick="putSlot(\'' + TempOrgId + '\',\'' + TempIdSpecialist + '\',\'' + $(this).find('date').text() + '\',\'' + $(this).find('time').text() + '\',\'' + Id + '\',\'' + AllDuration + '\');">' + $(this).find('time').text() + '</div>';
			}

			if($(this).find('status').text() == 'FREE' && $(this).find('time_of_day').text() == 1){Time1++;}
			if($(this).find('status').text() == 'FREE' && $(this).find('time_of_day').text() == 2){Time2++;}
			if($(this).find('status').text() == 'FREE' && $(this).find('time_of_day').text() == 3){Time3++;}
			if($(this).find('status').text() == 'FREE' && $(this).find('time_of_day').text() == 4){Time4++;}
		});
		TempContentRec+='</div>';

		if(Type == 'reschedule'){
			TempContentRec+='<button class="appointment" id="slot_' + Id + '" style="width: 200px; display: none;" onclick="makerescheduleVisit();">Перенести приём</button>';
		}else{
			TempContentRec+='<button class="appointment" id="slot_' + Id + '" style="width: 200px; display: none;" onclick="showAppointmentWindow();">Записать на приём</button>';
		}
		
		TempContentRec+='</div>';

		TempContentRec+='</div>';

		if(NumFreeSlots == 0){//не хватает слотов
			FLAG=1;
		}else{
			if(parseInt($(Recs).find('time_1').text()) == 1 || parseInt($(Recs).find('time_2').text()) == 1 || parseInt($(Recs).find('time_3').text()) == 1 || parseInt($(Recs).find('time_4').text()) == 1){
				//выбрано время дня
				if(
					(Time1 > 0 && parseInt($(Recs).find('time_1').text()) == 1) || 
					(Time2 > 0 && parseInt($(Recs).find('time_2').text()) == 1) || 
					(Time3 > 0 && parseInt($(Recs).find('time_3').text()) == 1) || 
					(Time4 > 0 && parseInt($(Recs).find('time_4').text()) == 1)
				){

				}else{
					FLAG=1;
				}
			}
		}
		if(FLAG == 0){
			TempRecsNum++;
			ArraySlotsRecs.push({content: TempContentRec, date: new Date($(this).find('rec_first_free_slot').text())});
		}
	});

	sortedArraySlotsRecs = ArraySlotsRecs.sort((b, a) => b.date - a.date);

	for(i=0; i<=sortedArraySlotsRecs.length-1; i++){
		TempContent+=sortedArraySlotsRecs[i].content;
		slotsSchedule.push(sortedArraySlotsRecs[i].content);
	}

	return [TempContent, TempRecsNum];
}
