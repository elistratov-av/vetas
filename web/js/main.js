var ArrayServicesRecs=new Array();
var ArrayServicesSpecialistsRecs=new Array();
var ArraySpeciesRecs=new Array();
var ArrayBreedsRecs=new Array();
var ArrayAreasRecs=new Array();
var ArrayTemplatesRecs=new Array();
var AllDuration=0;
var AllPrice=0;
var CurrentSlot='';
var CurrentDate='';
var CurrentTime='';
var CurrentServices='';
var CurrentOrgId='';
var CurrentSpecId='';
var CurrentVisitId='';
var CurrentRescheduleVisitId='';
var ArrayDays=['Вс','Пн','Вт','Ср','Чт','Пт','Сб'];
var fiasTokken = '';
let slotsSchedule = new Array();
let slotsScheduleNum = 0;
let scheduleDate = new Date();
let scheduleDateStr = scheduleDate.toLocaleString('ru', {year: 'numeric', month: 'long', day: 'numeric'});
let lastMove = 1;

function loadServices(){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#form").serialize()+'&action=services',
		'url': "index.php",
		'beforeSend': function() {
			
		},
		'success': function (Recs) {
			if($(Recs).find('rec').text()){
				$(Recs).find('rec').each(function(){
					ArrayServicesRecs.push({id: $(this).find('rec_id').text(), name: $(this).find('rec_name').text(), price: $(this).find('rec_price').text(), duration: $(this).find('rec_duration').text(), cooldown: $(this).find('rec_cooldown').text()});
				});
			}
		}
	});
}

function loadTemplates(){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#form").serialize()+'&action=templates',
		'url': "index.php",
		'beforeSend': function() {
			
		},
		'success': function (Recs) {
			if($(Recs).find('rec').text()){
				$(Recs).find('rec').each(function(){
					ArrayTemplatesRecs.push({code: $(this).find('rec_code').text(), message: $(this).find('rec_message').text()});
				});
			}
		}
	});
}

function loadDocumentTypes(){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': 'action=document_types&mode=xml',
		'url': "index.php",
		'beforeSend': function() {
			
		},
		'success': function (Recs) {
			if($(Recs).find('rec').text()){
				$(Recs).find('rec').each(function(){
					ArrayDocumentTypesRecs.push({id: $(this).find('rec_id').text(), name: $(this).find('rec_name').text(), type: $(this).find('rec_type').text(), group: $(this).find('rec_group').text()});
				});
			}
		}
	});
}

function loadSpecies(){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#form").serialize()+'&action=species',
		'url': "index.php",
		'beforeSend': function() {
			
		},
		'success': function (Recs) {
			ArraySpeciesRecs.splice(0,ArraySpeciesRecs.length);

			if($(Recs).find('rec').text()){
				$(Recs).find('rec').each(function(){
					ArraySpeciesRecs.push({id: $(this).find('rec_id').text(), name: $(this).find('rec_name').text()});
				});	
			}
		}
	});
}

function loadDocuments(){
	// jQuery.ajax({
	// 	'async': true,
	// 	'global': false,
	// 	'cache': false,
	// 	'type': 'GET',
	// 	'dataType': 'xml',
	// 	'data': 'mode=xml&action=documents_types',
	// 	'url': "index.php",
	// 	'beforeSend': function() {
			
	// 	},
	// 	'success': function (Recs) {
	// 		ArrayDocumentRecs.splice(0,ArrayDocumentRecs.length);

	// 		if($(Recs).find('rec').text()){
	// 			$(Recs).find('rec').each(function(){
	// 				ArrayDocumentRecs.push({id: $(this).find('rec_id').text(), name: $(this).find('rec_name').text(), type: $(this).find('rec_type').text(), group: $(this).find('rec_group').text()});
	// 			});	
	// 		}
	// 	}
	// });
}

function loadBreeds(){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#form").serialize()+'&action=breeds',
		'url': "index.php",
		'beforeSend': function() {
			
		},
		'success': function (Recs) {
			ArrayBreedsRecs.splice(0,ArrayBreedsRecs.length);

			if($(Recs).find('rec').text()){
				$(Recs).find('rec').each(function(){
					ArrayBreedsRecs.push({id: $(this).find('rec_id').text(), name: $(this).find('rec_name').text(), species: $(this).find('rec_species').text()});
				});	
			}
		}
	});
}

function convert_num(temp){
    if(parseInt(temp) < 10){
        return '0'+temp;
    }else{
        return temp;
    }
}

function update_url(Temp) {
    if (history.pushState) {
        var baseUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
        var newUrl = baseUrl + Temp;
        history.pushState(null, null, newUrl);
    }
    else {
        console.warn('History API не поддерживает ваш браузер');
    }
}

function changeAreas(){
	//получаем выделенные округа
	
	let AreasArray=$('.area').val();

	if(AreasArray.length > 0){
		$('.organization option').prop('disabled', true).prop('selected', false);
		for(i=0; i<=AreasArray.length-1; i++){
			$('.organization option[area=' + AreasArray[i] + ']').prop('disabled', false);
		}
		$('.organization').val('').multiselect('refresh');

		$('.metro option').prop('disabled', true).prop('selected', false);
		for(i=0; i<=AreasArray.length-1; i++){
			$('.metro option[area=' + AreasArray[i] + ']').prop('disabled', false);
		}
		$('.metro').val('').multiselect('refresh');
	}else{
		$('.organization option').prop('disabled', false);
		$('.organization').multiselect('refresh');

		$('.metro option').prop('disabled', false);
		$('.metro').multiselect('refresh');
	}
}

function changeOrganizations(){
	//получаем выделенные округа
	
	let OrgArray=$('.organization').val();

	if(OrgArray.length > 0){
		$('.doc option').prop('disabled', true).prop('selected', false);
		for(i=0; i<=OrgArray.length-1; i++){
			$('.doc option[org=' + OrgArray[i] + ']').prop('disabled', false);
		}
		$('.doc').val('').multiselect('refresh');
	}else{
		$('.doc option').prop('disabled', false);
		$('.doc').multiselect('refresh');
	}
}

function changeServiceTypes(){
	//получаем выделенные округа
	
	let TypeArray=$('.type').val();

	if(TypeArray.length > 0){
		$('.service option').prop('disabled', true).prop('selected', false);
		for(i=0; i<=TypeArray.length-1; i++){
			$('.service option[type=' + TypeArray[i] + ']').prop('disabled', false);
		}
		$('.service').val('').multiselect('refresh');
	}else{
		$('.service option').prop('disabled', false);
		$('.service').multiselect('refresh');
	}
}

function changeSpecies(){
	let SpeciesVal=$('.species').val();

	if(SpeciesVal){
		$('.breeds option').prop('disabled', true).prop('selected', false);
		$('.breeds option[species_id=' + SpeciesVal + ']').prop('disabled', false);
		$('.breeds option[species_id=""]').prop('disabled', false);
		$('.breeds').val('').multiselect('refresh');
	}else{
		$('.breeds option').prop('disabled', false);
		$('.breeds option[species_id=""]').prop('disabled', false);
		$('.breeds').multiselect('refresh');
	}
}

function changeOwner(){
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
		$('.next').prop('disabled', false);
		$('.next').show();
	}else{
		$('.next').prop('disabled', true);
		$('.next').hide();
	}
}

function changeContact(){
	if($('#contact_new_type').val() == 1){
		$('.contact_new_name').unmask();
		$('.contact_new_name').mask("+7(999) 999-9999");
	}else{
		$('.contact_new_name').unmask();
		$('.contact_new_name').mask("A", {
			translation: {
				"A": { pattern: /[\w@\-.+]/, recursive: true }
			}
		});
	}
}

function makerescheduleVisit(){
	//предыдущий приём
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': '&visit='+CurrentRescheduleVisitId+'&date='+CurrentDate+'&time='+CurrentTime+'&services='+CurrentServices+'&org_id='+CurrentOrgId+'&spec_id='+CurrentSpecId+'&action=reschedule_appointment',
		'url': "index.php",
		'beforeSend': function() {
			//блокируем кнопку и скрываем кнопку

			$('.next').prop('disabled', true);
	
			//показать загрузку
		},
		'success': function (Data) {
			if($(Data).find('message').text() == 'visit_created'){
				closeAppointmentWindow();
				$("#reschedule").hide();
				$("body").append($("#reschedule"));
				
				let message='';
				for(let i=0; i<=ArrayTemplatesRecs.length-1; i++){
					if(ArrayTemplatesRecs[i].code == 'visit_rescheduled'){
						message=ArrayTemplatesRecs[i].message;
					}
				}
				message=message.replace(/{date}/gi, $(Data).find('date').text());
				message=message.replace(/{time}/gi, $(Data).find('time').text());
				message=message.replace(/{duration}/gi, $(Data).find('duration').text());
				message=message.replace(/{organization}/gi, $(Data).find('organization').text());
				message=message.replace(/{address}/gi, $(Data).find('address').text());
				message=message.replace(/{specialist}/gi, $(Data).find('specialist').text());
				message=message.replace(/{services}/gi, $(Data).find('services').text());
				message=message.replace(/{ticket_number}/gi, $(Data).find('ticket_number').text());
				showMessageWindow(message);
				
				ListShowSlots();
			}
			if($(Data).find('message').text() == 'empty_fields'){
				$("#appointment_window_message").html('Не заполнены необходимые поля.');
				$("#appointment_window_message").show();
				$('.next').prop('disabled', false);
			}
		}
	});
}

function makeAppointment(){
	let owner = '';
	let pet = '';
	let pet_name='';
	let pet_species='';
	let pet_breed='';
	let pet_sex='';

	let owner_surname='';
	let owner_name='';
	let owner_secondname='';
	let owner_telephone='';
	let owner_email='';
	let owner_address='';
	

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
		owner_address=$('#owner_address').val();

		$('#owner_new_surname').removeClass("error_field");
		$('#owner_new_name').removeClass("error_field");
		$('#owner_new_telephone').removeClass("error_field");
		$('#owner_new_address_').removeClass("error_field");
		$('#pet_new_name').removeClass("error_field");
		$('#pet_new_breed').removeClass("error-border");
		//|| $('#owner_address').val() == ''
		if($('#pet_new_name').val() == '' || $('#owner_new_surname').val() == '' || $('#owner_new_name').val() == '' || $('#owner_new_telephone').val() == '' || $('#pet_new_breed').val() == ''){
			if($('#pet_new_name').val() == ''){$('#pet_new_name').addClass('error_field');}
			if($('#owner_new_surname').val() == ''){$('#owner_new_surname').addClass('error_field');}
			if($('#owner_new_name').val() == ''){$('#owner_new_name').addClass('error_field');}
			if($('#owner_new_telephone').val() == ''){$('#owner_new_telephone').addClass('error_field');}
			if($('#pet_new_breed').val() == null || $('#pet_new_breed').val() == ''){$('#pet_new_breed+.btn-group').addClass('error-border');}
			//if($('#owner_address').val() == ''){$('#owner_address_').addClass('error_field');}

			//кличка обязательное поле
			$("#appointment_window_message").html('Не заполнены необходимые поля.');
			$("#appointment_window_message").show();
						
			return true;
		}

		pet_name=$('#pet_new_name').val();
		pet_species=$('#pet_new_species').val();
		pet_breed=$('#pet_new_breed').val();
		pet_sex=$('#pet_new_sex').val();
	}else{
		//ищем выбранного пользователя
		let owners = $('input[name="owner"]');
		
		if (owners.length >= 1){
			owners.each(function () {
				if($(this).prop("checked") == true){
					owner=$(this).val();
				}
			});
		}
		
		//ищем выбранного питомца
		let pets = $('input[name="pet_' + owner + '"]');
		let error_ = 0;
		
		if (pets.length > 1){
			pets.each(function () {
				if($(this).prop("checked") == true){
					if($(this).val() == '000'){
						if($('#pet_name_'+owner).val() == ''){
							//кличка обязательное поле
							$('#pet_name_'+owner).addClass('error_field');
							$("#appointment_window_message").html('Не заполнены необходимые поля.');
							$("#appointment_window_message").show();

							error_ = 1;
						}

						pet_name=$('#pet_name_'+owner).val();
						pet_species=$('#pet_species_'+owner).val();
						pet_breed=$('#pet_breed_'+owner).val();
						pet_sex=$('#pet_sex_'+owner).val();
					}else{
						pet=$(this).val();
					}
				}
			});
		}

		if(error_ == 1){
			return true;
		}
	}

	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': '&add_new='+$('#add_new').val()+'&visit_id='+CurrentVisitId+'&owner='+owner+'&pet='+pet+'&date='+CurrentDate+'&time='+CurrentTime+'&owner_surname='+owner_surname+'&owner_name='+owner_name+'&owner_address='+owner_address+'&owner_secondname='+owner_secondname+'&owner_telephone='+owner_telephone+'&owner_email='+owner_email+'&pet_sex='+pet_sex+'&pet_species='+pet_species+'&pet_breed='+pet_breed+'&pet_name='+pet_name+'&services='+CurrentServices+'&org_id='+CurrentOrgId+'&spec_id='+CurrentSpecId+'&action=make_appointment',
		'url': "index.php",
		'beforeSend': function() {
			//блокируем кнопку и скрываем кнопку
			$('.next').prop('disabled', true);
			//показать загрузку
		},
		'success': function (Data) {
			if($(Data).find('message').text() == 'visit_created'){
				closeAppointmentWindow();
				let message='';
				for(let i=0; i<=ArrayTemplatesRecs.length-1; i++){
					if(ArrayTemplatesRecs[i].code == $(Data).find('message').text()){
						message=ArrayTemplatesRecs[i].message;
					}
				}
				message=message.replace(/{date}/gi, $(Data).find('date').text());
				message=message.replace(/{time}/gi, $(Data).find('time').text());
				message=message.replace(/{duration}/gi, $(Data).find('duration').text());
				message=message.replace(/{organization}/gi, $(Data).find('organization').text());
				message=message.replace(/{address}/gi, $(Data).find('address').text());
				message=message.replace(/{specialist}/gi, $(Data).find('specialist').text());
				message=message.replace(/{services}/gi, $(Data).find('services').text());
				message=message.replace(/{ticket_number}/gi, $(Data).find('ticket_number').text());
				showMessageWindow(message);
				
				ListShowSlots();
			}
			if($(Data).find('message').text() == 'empty_fields'){
				$("#appointment_window_message").html('Не заполнены необходимые поля.');
				$("#appointment_window_message").show();
				$('.next').prop('disabled', false);
			}
			if($(Data).find('message').text() == 'booking_empty'){
				closeAppointmentWindow();
				showMessageWindow('Время бронирования истекло.');
				ListShowSlots();
			}
		}
	});
}

function showLoading(Temp){
	$('#'+Temp+'').html('<div class="loading"></div');
}

function closeLoading(Temp){
	$('#'+Temp+'').removeClass('loading');
}

function showTopLoading(){
	$('#sub_container_t').html('<div class="loading"></div');
	$("#background_t").fadeIn();
	$("#container_t").show();
}

function closeTopLoading(){
	$('#sub_container_t').removeClass('loading');
	$("#background_t").fadeOut();
	$("#container_t").hide();
}

function addOwner(){
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

	$('#add_appoitment_window #window_title').html('Добавление владельца');
	$('.add').hide();
	$('.next').prop('disabled', false);
	$('.next').width('360px');
	$('.next').html('Добавить владельца и оформить приём');
	$('.next').show();
	$('#add_new').val('1');
	$('#AddOwners').show();
	$('#ListOwners').hide();
	$('#add_appoitment_window #FormSearch').hide();

	// console.log($(window).height());
	// console.log();
	let window_height = $("#add_appoitment_window").height()+160;
	if($(window).height() < window_height){
		$("#AddOwners").height($("#add_appoitment_window").height()-300);
	}
}

function showScheduleWindow(){
	$("#background-sch").fadeIn();
	$("#container-schedule").show();
	changeOwner();
	$('.telephone').mask("+7(999) 999-9999");

	$('.date').datepicker({multidate: true, language: "ru", format: 'yyyy-mm-dd'});
}

// function showFaqAddGroup(){
// 	$('#group_exists').hide();
// 	$('#form-group').trigger('reset');
// 	$("#background-faq-group").fadeIn();
// 	$("#container-faq-group").show();
// }
function showFaqAddGroup(){
	$('#group_exists').hide();
	$('#form-group').trigger('reset');
	$("#background-faq-group").fadeIn();
	$("#container-faq-group").show();
	$('#questions').multiselect({
		enableClickableOptGroups: true,
		nonSelectedText: 'Выберите...',
		allSelectedText: "Выбраны все",
		nSelectedText  : "выбрано",
		buttonWidth: '250',
		maxHeight: 200,
		enableFiltering: true,
		enableCaseInsensitiveFiltering : true,
		selectAllText: 'Выбрать все',
		includeSelectAllOption: true,
		filterPlaceholder: 'Выбрать вопросы...'
	});
	$('#questions').val('').multiselect('refresh');
}


function closeFaqGroupWindow(){
	$("#background-faq-group").fadeOut();
	$("#container-faq-group").hide();
}

function onShowCreateQuestion(id){
	let TempContent='';

	TempContent+='<div class="window main_row col-xl-3 col-lg-4 col-5">';
	TempContent+='<div class="close" onclick="closeFaqWindow();"></div>';

	TempContent+='<div class="top">Добавить вопрос</div>';

	TempContent+='<form id="form-question" class="px-2 py-3" onsubmit="addQuestion(); return false;">';
	TempContent+='<input name="groupid" type="hidden" value="' + id + '"/>';
	TempContent+='<div class="d-flex justify-content-between"><div class="label required">Вопрос/проблема:</div><div class="input w-50"><input type="text" name="question" value="" autoComplete="off" required></div></div>';
	TempContent+='<div class="d-flex justify-content-between mt-3"><div class="label required">Описание:</div><div class="input w-50"><textarea rows="5" name="answer" autoComplete="off" required></textarea></div></div>';
	TempContent+='<div id="question_exists" class="modal-body__fetch-error" style="display:none">Такой вопрос уже есть</div>';
	TempContent+='<div class="d-flex justify-content-around mt-4"><button class="btn btn-outline-primary w-50" type="submit">Добавить</button>';
	TempContent+='<input type="button" class="btn btn-outline-secondary" onclick="closeFaqWindow(); event.stopPropagation();" value ="Отменить"/></div></form>';

	TempContent+='</div>';

	document.getElementById("sub_container_faq").innerHTML=TempContent;

	$("#background-faq").fadeIn();
	$("#container-faq").show();
}

function addQuestion() {
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#form-question").serialize().replaceAll('%0D%0A', '*EOL*')+'&action=add_question',
		'url': "index.php",
		'beforeSend': function() {
			$("#overlay").fadeIn(300);
		},
		'success': function (Recs) {

			if($(Recs).find('message').text() == 'question_already_exists'){
				$('#question_exists').show();
			}
			else if($(Recs).find('message').text() == 'token_invalid'){
				window.location.href = 'index.php';
			}else{
				location.reload();
			}
			setTimeout(function(){
				$("#overlay").fadeOut(300);
			},500);
		}
	});
}

function addGroup() {
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#form-group").serialize()+'&action=create_group',
		'url': "index.php",
		'beforeSend': function() {
			$("#overlay").fadeIn(300);
		},
		'success': function (Recs) {

			if($(Recs).find('message').text() == 'group_already_exists'){
				$('#group_exists').show();
			}
			else if($(Recs).find('message').text() == 'token_invalid'){
				window.location.href = 'index.php';
			}else{
				location.reload();
			}
			setTimeout(function(){
				$("#overlay").fadeOut(300);
			},500);
		}
	});
}

function showVisitsWindow(){
	let TempContent='';

	TempContent+='<div class="window main_row col-xl-8 col-lg-10 col-12">';
	//
	TempContent+='<div class="close" onclick="closeVisitsWindow();"></div>';

	TempContent+='<div class="top">Список приёмов</div>';

	TempContent+='<div class="row main_row control">';
	TempContent+='<div class="col-6" id="window_title">Поиск приёма</div>';
	TempContent+='</div>';

	TempContent+='<div id="FormSearch" class="search">';
	TempContent+='<form id="form_owners" onsubmit="ListShowVisits(); return false;">';
	TempContent+='<div class="d-inline-flex"><div class="label">Дата:&nbsp;</div><div class="input"><input type="text" class="date" name="date" value="" autocomplete="off"></div></div>';
	TempContent+='<div class="d-inline-flex"><div class="label">ФИО или наименование владельца:&nbsp;</div><div class="input"><input type="text" name="name" value="" autocomplete="off"></div></div>';
	TempContent+='<div class="d-inline-flex"><div class="label">Телефон:&nbsp;</div><div class="input"><input type="text" name="telephone" value="" autocomplete="off" class="telephone"></div></div>';
	TempContent+='<div class="d-inline-flex"><button type="submit">Поиск</button></div>';
	TempContent+='</form>';
	TempContent+='</div>';
	
	
	TempContent+='<div class="results" id="ListVisits"><div class="message">Для поиска приёма введите параметры поиска.</div></div>';
	
	TempContent+='</div>';

	document.getElementById("sub_container").innerHTML=TempContent;
	$("#background").fadeIn();
	$("#container").show();
	changeOwner();
	$('.telephone').mask("+7(999) 999-9999");
	
	$('.date').datepicker({multidate: true, language: "ru", format: 'yyyy-mm-dd'});
}

function closeFaqWindow(){
	$("#background-faq").fadeOut();
	$("#container-faq").hide();
}

function closeScheduleWindow(){
	$("#background-sch").fadeOut();
	$("#container-schedule").hide();
}

function closeVisitsWindow(){
	$("#background").fadeOut();
	$("#container").hide();
}

function showMessageWindow(Message){
	let TempContent='';

	TempContent+='<div class="window message col-xl-4 col-lg-5 col-12">';
	TempContent+='<div class="close" onclick="closeMessageWindow();"></div>';
	TempContent+='<div class="top">Сообщение</div>';
	TempContent+='<div>'+Message+'</div>';
	TempContent+='<div class="controls"><button class="ok" style="width: 120px;" onclick="closeMessageWindow();">Закрыть</button></div>';
	TempContent+='</div>';

	document.getElementById("sub_container_m").innerHTML=TempContent;
	$("#background_m").fadeIn();
	$("#container_m").show();
}

function closeMessageWindow(){
	$("#background_m").fadeOut();
	$("#container_m").hide();
}

function showAcceptWindow(Title, Text, Function = null, ButtonTitle = null, ButtonClass = null){
	let TempContent='';

	TempContent+='<div class="window accept col-xl-4 col-lg-5 col-12">';
	TempContent+='<div class="close" onclick="closeAcceptWindow();"></div>';
	TempContent+='<div class="top">'+Title+'</div>';
	TempContent+='<div class="text">'+Text+'</div>';
	TempContent+='<div class="controls">';
	TempContent+='<button class="button" style="width: 120px;" onclick="closeAcceptWindow();">Отмена</button>';
	TempContent+='<button class="button '+ButtonClass+'" style="margin-left: 15px; width: auto;" onclick="'+Function+'">'+ButtonTitle+'</button>';
	TempContent+='</div>';
	TempContent+='</div>';

	document.getElementById("sub_container_m").innerHTML=TempContent;
	$("#background_m").fadeIn();
	$("#container_m").show();
}

function closeAcceptWindow(){
	$("#background_m").fadeOut();
	$("#container_m").hide();
}

function showNotificationsWindow(){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': 'action=notifications&mode=xml',
		'url': "index.php",
		'beforeSend': function() {
			//показать загрузку
			showLoading('ListOwners');
			changeOwner();
		},
		'success': function (Recs) {
			if($(Recs).find('message').text() == 'token_invalid'){
				window.location.href = 'index.php';
			}else{
				let TempContent='';

				TempContent+='<div class="window accept col-xl-8 col-lg-6 col-12">';
				TempContent+='<div class="close" onclick="closeNotificationsWindow();"></div>';
				TempContent+='<div class="top">Уведомления</div>';

				TempContent+='<div class="table p-3" id="ListRecs">';
				TempContent+='<table class="w-100 notifications">';
				
				TempContent+='<tr><th>Тема</th><th>Дата и время</th><th>Сообщение</th></tr>';

				$(Recs).find('rec').each(function(){
					TempContent+='<tr id="'+$(this).find('rec_id').text()+'"';
					if($(this).find('rec_read').text() == 'f'){
						TempContent+=' class="not_read"';
					}
					TempContent+=' onclick="readNotification(this);">';
					TempContent+='<td>'+$(this).find('rec_title').text()+'</td>';
					TempContent+='<td>'+$(this).find('rec_datetime').text()+'</td>';
					TempContent+='<td>'+$(this).find('rec_text').text()+'</td>';
					
					TempContent+='</tr>';
				});

				TempContent+='</table>';
				TempContent+='</div>';
				
				TempContent+='</div>';

				document.getElementById("sub_container_m").innerHTML=TempContent;
				$("#background_m").fadeIn();
				$("#container_m").show();
			}
		}
	});
}
function readNotification(Notification){
	$(Notification).removeClass('not_read');
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': 'action=notification_read&id='+Notification.id+'&mode=xml',
		'url': "index.php",
		'beforeSend': function() {
			
		},
		'success': function (Data) {

		}
	});
}
function closeNotificationsWindow(){
	$("#background_m").fadeOut();
	$("#container_m").hide();
}

function editOwnerContacts(Id){
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
				TempContent+='<div><input checked type="radio" onchange="changeOwner();" name="win_action" id="win_action_1" value="1"> <label for="win_action_1">Изменить контакт</label></div>';
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
			TempContent+='onchange="changeOwner();" name="win_action" id="win_action_2" value="2"> <label for="win_action_2">Добавить новый контакт</label></div>';

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

function saveContacts(){
	let savestring='';
	if($('input[name="win_action"]:checked').val() == 1){
		$(".mobiletelephone_").unmask();
		savestring='&new=0&id_owner='+$("#contact_new_owner").val()+'';
		$(".contacts .mobiletelephone_").each(function() {
			savestring+='&id='+$(this).attr('id')+'&value='+$(this).val()+'&type=mobiletelephone';
		});
		$(".contacts .mail_").each(function() {
			savestring+='&id='+$(this).attr('id')+'&value='+$(this).val()+'&type=mail';
		});
	}else{
		savestring='&new=1&id_owner='+$("#contact_new_owner").val()+'';
		savestring+='&type='+$("#contact_new_type").val()+'&value='+$("#contact_new_name").val();
	}
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': ''+savestring+'&action=save_contacts',
		'url': "index.php",
		'beforeSend': function() {
			//блокируем кнопку
			$('.ok').prop('disabled', true);
	
			//показать загрузку
		},
		'success': function (Data) {
			if($(Data).find('message').text() == 'contacts_saved'){
				//closeAppointmentWindow();
				///let message='Назначен приём на '+$(Data).find('date').text()+' в '+$(Data).find('time').text()+' продожительностью '+$(Data).find('duration').text()+' минут в '+$(Data).find('organization').text()+' по адресу '+$(Data).find('address').text()+'. ';
				//message+='Специалистом '+$(Data).find('specialist').text()+' будут оказаны следующие услуги: '+$(Data).find('services').text()+'. Талон № <strong>'+$(Data).find('ticket_number').text()+'</strong>.';
				//showMessageWindow(message);
				closeEditWindow();
				ListShowOwnersPets();
			}
		}
	});
}

function closeEditWindow(){
	$("#background_m").fadeOut();
	$("#container_m").hide();
}

function ListShowOwnersPets(){
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
			changeOwner();
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
						TempContentRec+='<input type="radio" onchange="changeOwner();" name="owner" id="owner_'+$(this).find('rec_id').text()+'" value="'+$(this).find('rec_id').text()+'"><label for="owner_'+$(this).find('rec_id').text()+'"><span class="name">' + $(this).find('rec_name').text() + '</span></label>';
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
						TempContentRec+='<div class="edit" title="Добавить/изменить контакт" onclick="editOwnerContacts('+$(this).find('rec_id').text()+');	"></div>';
						TempContentRec+='</div>';

						TempContentRec+='<div class="col-xl-2 col-lg-2 col-md-12 col-sm-12 col-xs-12 col-12 addresses">';
						if($(this).find('rec_address').text()){
							TempContentRec+='<p class="address">' + $(this).find('rec_address').text() + '</p>';
						}else{
							TempContentRec+='-';
						}
						TempContentRec+='</div>';
						
						TempContentRec+='<div class="col-xl-5 col-lg-5 col-md-12 col-sm-12 col-xs-12 col-12 pets">';
						let i=0;
						$(this).find('rec_pets').find('pet').each(function(){
							TempContentRec+='<div class="pet">';
							TempContentRec+='<input type="radio" ';
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
						TempContentRec+='<input type="radio" name="pet_' + Id + '" id="pet_' + Id + '_000" value="000">';
						TempContentRec+='</div>';

						TempContentRec+='<div class="d-table-cell">';
						TempContentRec+='<label for="pet_' + Id + '_000">';
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
						TempContentRec+='<label for="pet_' + Id + '_000">';
						TempContentRec+='<select class="sex" id="pet_sex_' + Id + '">';
						TempContentRec+='<option value="m">Мужской</option>';
						TempContentRec+='<option value="f">Женский</option>';
						TempContentRec+='</select>';
						TempContentRec+='</label>';
						TempContentRec+='</div>';

						TempContentRec+='<div class="d-table-cell" style="padding-left: 5px;">';
						TempContentRec+='<label for="pet_' + Id + '_000">';
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
				$('.species').multiselect({buttonWidth: '100%', maxHeight: 300, enableFiltering: true, enableCaseInsensitiveFiltering : true});
			}

			$("#ListInOwners").height($("#add_appoitment_window").height()-350);
		}
	});
}

function ListShowVisits(){
	$(".telephone").unmask();
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#form_owners").serialize()+'&action=visits',
		'url': "index.php",
		'beforeSend': function() {
			//показать загрузку
			showLoading('ListOwners');
			changeOwner();
		},
		'success': function (Recs) {
			if($(Recs).find('message').text() == 'token_invalid'){
				window.location.href = 'index.php';
			}else{
				$('.telephone').mask("+7(999) 999-9999");
				let TempContent='';
				let TempRecsNum=0;

				if($(Recs).find('rec').text()){
					TempContent+='<div class="row visits">';

					$(Recs).find('rec').each(function(){
						let FLAG=0;
						let Id=$(this).find('rec_id').text();

						TempContentRec='<div class="visit col-12 main_row row">';
						TempContentRec+='<div class="col-xl-3 col-lg-3 col-md-12 col-sm-12 col-xs-12 col-12">';

						let date = new Date($(this).find('rec_start_date').text());
						TempContentRec+='<span class="date">' + date.toLocaleString('ru', {year: 'numeric', month: 'long', day: 'numeric'}) + '';
						TempContentRec+=' с '+$(this).find('rec_start_time').text()+' по '+$(this).find('rec_end_time').text();
						TempContentRec+='</span><br>';
						TempContentRec+='<span class="title">'+$(this).find('rec_specialist_name').text()+'</span><br>';
						TempContentRec+='<span class="org_name">' + $(this).find('rec_org_name').text() + '</span><br>';
						if($(this).find('rec_org_address').text()){
							TempContentRec+='<span class="org_area">(' + $(this).find('rec_org_address').text() + ')</span>';
						}
						TempContentRec+='</div>';

						TempContentRec+='<div class="col-xl-3 col-lg-3 col-md-12 col-sm-12 col-xs-12 col-12 contacts">';
						TempContentRec+='<span class="title">'+$(this).find('rec_owner_name').text()+'</span>';

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
						}

						TempContentRec+='</div>';

						TempContentRec+='<div class="col-xl-3 col-lg-3 col-md-12 col-sm-12 col-xs-12 col-12">';
						TempContentRec+='Услуги приёма: ';
						$(this).find('rec_services').find('service').each(function(){
							TempContentRec+='<div class="service_item">' + $(this).find('name').text() + '</div>';
						});
						TempContentRec+='</div>';

						
						TempContentRec+='<div class="col-xl-3 col-lg-3 col-md-12 col-sm-12 col-xs-12 col-12" style="text-align: right;">';
						if($(this).find('rec_status').text() == 'N'){
							TempContentRec+='<button class="reschedule_visit" onclick="rescheduleVisit(' + Id + ');" id="reschedule_visit_'+Id+'" style="width: 200px;">Перенести приём</button>';
							TempContentRec+='<button class="cancel_visit" onclick="confirmcancelVisit(' + Id + ');" id="cancel_visit_'+Id+'">Отменить приём</button>';
						}else if($(this).find('rec_status').text() == 'W'){
							TempContentRec+='Приём уже взят в работу. Отмена невозможна.';
						}
						
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
				document.getElementById('ListVisits').innerHTML=TempContent;
				$('.species').multiselect({buttonWidth: '100%', maxHeight: 300, enableFiltering: true, enableCaseInsensitiveFiltering : true});
			}
		}
	});
}

function ListShowSchedule(){
	slotsSchedule = new Array();

	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#form_schedule").serialize()+'&action=schedule',
		'url': "index.php",
		'beforeSend': function() {
			//показать загрузку
			$("#overlay").fadeIn(300);
		},
		'success': function (Recs) {

			if($(Recs).find('message').text() == 'date_period_error'){
				TempContent='<span style="color:red;">Ошибка заполнения даты: дата "по" раньше чем дата "с"</span>';
				document.getElementById('ListSchedule').innerHTML=TempContent;
			}
			else if($(Recs).find('message').text() == 'token_invalid'){
				window.location.href = 'index.php';
			}else{
				let TempContent='';
				let TempRecsNum=0;

				if($(Recs).find('rec').text()){
					TempContent+='<div class="row recs" id="scheduleRow">';

					AllDuration=parseInt($(Recs).find('info').find('duration').text())+parseInt($(Recs).find('info').find('cooldown').text());
					AllPrice=$(Recs).find('info').find('price').text();
					CurrentServices=$(Recs).find('info').find('services').text();

					let services=$(Recs).find('info').find('name').text()
					let arrayServices = services.split(';');
					let servicesText='';
					for(i=0; i<=arrayServices.length-1; i++){
						servicesText+='<div class="service_item">'+arrayServices[i]+'</div>';
					}

					let [Content, Num]=createSlotsSchedule(Recs, 'slots');
					TempContent+=showSlotsSchedule(0);
					slotsScheduleNum = 0;
					TempRecsNum=Num;

					TempContent+='</div>';
				}


				if(TempRecsNum == 0){
					TempContent='По данному запросу не найдено записей.';
				}
				document.getElementById('ListSchedule').innerHTML=TempContent;
			}

			setTimeout(function(){
				$("#overlay").fadeOut(300);
			},500);
		}
	});
}

function showSlotsSchedule(id) {
	return slotsSchedule[id];
}

function decSchedule() {

	if (slotsScheduleNum == 0) return;

	scheduleDate.setDate(scheduleDate.getDate() - 1);
	console.log(scheduleDate);
	let str = scheduleDate.toLocaleString('ru', {year: 'numeric', month: 'long', day: 'numeric'});

	$('#schDate').text(str);

	let slot = slotsSchedule.filter(function (slot) {
		return slot.includes(str);
	});
	console.log(slot);
	if (slot.length) {
		slotsScheduleNum--;
		document.getElementById('scheduleRow').innerHTML= slot[0];
	} else {
		$('div.minislots').text("Нет данных");
	}
}
function incSchedule() {

	if (slotsScheduleNum + 2 > slotsSchedule.length) return;

	scheduleDate.setDate(scheduleDate.getDate() + 1);
	console.log(scheduleDate);
	let str = scheduleDate.toLocaleString('ru', {year: 'numeric', month: 'long', day: 'numeric'});


	$('#schDate').text(str);

	let slot = slotsSchedule.filter(function (slot) {
		return slot.includes(str);
	});

	console.log(slot);

	if (slot.length) {
		slotsScheduleNum++;
		document.getElementById('scheduleRow').innerHTML= slot[0];
	} else {
		$('div.minislots').text("Нет данных");
	}
}

function showhideDiv(Temp){
	$('#'+Temp).toggle(100);
}

function checkAll(Temp){
	$('#'+Temp+' input:checkbox').prop('checked',true);

	$('#'+Temp+' input:checkbox').each(function(){
		let has=false;
		for(let i=0;i<ArrayServicesSpecialistsRecs.length;i++){
			if(ArrayServicesSpecialistsRecs[i].service == $(this).attr('service') && ArrayServicesSpecialistsRecs[i].specialist == $(this).attr('specialist')){
				has=true;//запись присутствует меняем состояние
			}
		}

		if(has || ($(this).attr('ch') == 0 && !has)){
			checkServicesSpecialists($(this).attr('specialist'), $(this).attr('service'));
		}
	});
}

function uncheckAll(Temp){
	$('#'+Temp+' input:checkbox').prop('checked',false);

	$('#'+Temp+' input:checkbox').each(function(){
		let has=false;
		for(let i=0;i<ArrayServicesSpecialistsRecs.length;i++){
			if(ArrayServicesSpecialistsRecs[i].service == $(this).attr('service') && ArrayServicesSpecialistsRecs[i].specialist == $(this).attr('specialist')){
				has=true;//запись присутствует меняем состояние
			}
		}
		if(has || ($(this).attr('ch') == 1 && !has)){
			checkServicesSpecialists($(this).attr('specialist'), $(this).attr('service'));
		}
	});
}

function checkServicesSpecialists(IdSpecialist, IdService){
	if($('#service_'+IdSpecialist+'_'+IdService+'').prop('checked') == false){
		let flag=true;
		for(let i=0;i<ArrayServicesSpecialistsRecs.length;i++){
			if(ArrayServicesSpecialistsRecs[i].service == IdService && ArrayServicesSpecialistsRecs[i].specialist == IdSpecialist && ArrayServicesSpecialistsRecs[i].action == 'delete'){
				flag=false;
			}
		}
		if(flag == true){
			let index=-1;
			for(let i=0;i<ArrayServicesSpecialistsRecs.length;i++){
				if(ArrayServicesSpecialistsRecs[i].service == IdService && ArrayServicesSpecialistsRecs[i].specialist == IdSpecialist && ArrayServicesSpecialistsRecs[i].action == 'add'){
					index=i;
				}
			}
			if(index > -1){
				ArrayServicesSpecialistsRecs.splice(index, 1);
			}else{
				ArrayServicesSpecialistsRecs.push({service: IdService, specialist: IdSpecialist, action: 'delete'});
			}
		}
	}else{
		let flag=true;
		for(let i=0;i<ArrayServicesSpecialistsRecs.length;i++){
			if(ArrayServicesSpecialistsRecs[i].service == IdService && ArrayServicesSpecialistsRecs[i].specialist == IdSpecialist && ArrayServicesSpecialistsRecs[i].action == 'add'){
				flag=false;
			}
		}
		if(flag == true){
			let index=-1;
			for(let i=0;i<ArrayServicesSpecialistsRecs.length;i++){
				if(ArrayServicesSpecialistsRecs[i].service == IdService && ArrayServicesSpecialistsRecs[i].specialist == IdSpecialist && ArrayServicesSpecialistsRecs[i].action == 'delete'){
					index=i;
				}
			}
			if(index > -1){
				ArrayServicesSpecialistsRecs.splice(index, 1);
			}else{
				ArrayServicesSpecialistsRecs.push({service: IdService, specialist: IdSpecialist, action: 'add'});
			}
		}
	}

	if(ArrayServicesSpecialistsRecs.length > 0){
		let add_=0;
		let delete_=0;
		let services_specialists='';
		for(let i=0;i<ArrayServicesSpecialistsRecs.length;i++){
			if(ArrayServicesSpecialistsRecs[i].action == 'add'){
				add_++;
			}
			if(ArrayServicesSpecialistsRecs[i].action == 'delete'){
				delete_++;
			}
			if(i>0){services_specialists+=',';}
			services_specialists+='{\'service\': \''+ArrayServicesSpecialistsRecs[i].service+'\', \'specialist\': \''+ArrayServicesSpecialistsRecs[i].specialist+'\', \'action\': \''+ArrayServicesSpecialistsRecs[i].action+'\'}';
		}

		let text='Будет ';
		if(add_ > 0){
			text+='добавлено — '+add_+'';
		}
		if(delete_ > 0){
			if(add_ > 0){
				text+=', ';
			}
			text+='удалено — '+delete_+'';
		}
		text+=' связей специалист и услуга.';
		$("#info").text(text);
		$("#services_specialists").text(services_specialists);

		$("#controllers").show();
	}else{
		$("#controllers").hide();
	}
}



function rescheduleVisit(Id){
	//открываем окно со слотами
	CurrentRescheduleVisitId=Id;

	//при этом остаётся набор услуг
	$("#sub_container_m").html('');
	$("#reschedule").show();
	$("#sub_container_m").append($("#reschedule"));
	$("#background_m").fadeIn();
	$("#container_m").show();
	ListShowSlotsReschedule();
	//остаётся владелец и животное
}

function confirmcancelVisit(Id){
	let message='';
	for(let i=0; i<=ArrayTemplatesRecs.length-1; i++){
		if(ArrayTemplatesRecs[i].code == 'visit_cancel_confirm'){
			message=ArrayTemplatesRecs[i].message;
		}
	}

	let TempContent='';

	TempContent+='<div class="window message col-xl-4 col-lg-5 col-12">';
	TempContent+='<div class="close" onclick="closeConfirmWindow();"></div>';
	TempContent+='<div class="top">Подтверждение</div>';
	TempContent+='<div>'+message+'</div>';
	TempContent+='<div class="controls">'
	TempContent+='<button class="ok" style="width: 150px;" onclick="cancelVisit('+Id+');">Подтвердить</button>&nbsp;';
	TempContent+='<button class="ok" style="width: 120px;" onclick="closeConfirmWindow();">Отменить</button></div>';
	TempContent+='</div>';

	document.getElementById("sub_container_m").innerHTML=TempContent;
	$("#background_m").fadeIn();
	$("#container_m").show();
}

function closeConfirmWindow(){
	$("#background_m").fadeOut();
	$("#container_m").hide();
}

function cancelVisit(Id){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': '&id='+Id+'&action=cancel_visit',
		'url': "index.php",
		'beforeSend': function() {
			//блокируем кнопку
			$('#cancel_visit_'+Id).prop('disabled', true);
			//показать загрузку
		},
		'success': function (Data) {
			if($(Data).find('message').text() == 'visit_canceled'){
				let message='';
				for(let i=0; i<=ArrayTemplatesRecs.length-1; i++){
					if(ArrayTemplatesRecs[i].code == $(Data).find('message').text()){
						message=ArrayTemplatesRecs[i].message;
					}
				}
				showMessageWindow(message);
				ListShowSlots();
				ListShowVisits();
			}
		}
	});
}

function createSlotsSchedule(Recs, Type){
	let TempContent='';
	let TempRecsNum=0;
	let ArraySlotsRecs=new Array();//заводим массив для сортировки по первому свободному слоту

	let firstDate = true;
	$(Recs).find('rec').each(function(){
		let FLAG=0;
		let NumFreeSlots=0;

		TempContentRec='<div class="rec col-12 main_row row">';
		TempContentRec+='<div class="col-xl-3 col-lg-3 col-md-12 col-sm-12 col-xs-12 col-12"><span class="title">' + $(this).find('rec_doc_name').text() + '</span><br>';

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

		TempContentRec+='<span class="date-ch" onclick="decSchedule();"><</span> <span class="date" id="schDate">' + date.toLocaleString('ru', {year: 'numeric', month: 'long', day: 'numeric'}) + '</span> <span class="date-ch" onclick="incSchedule()">></span>';

		if (firstDate) {
			firstDate = false;
			scheduleDate = date;
			scheduleDateStr = date.toLocaleString('ru', {year: 'numeric', month: 'long', day: 'numeric'});
		}

		TempOrgId=$(this).find('rec_org_id').text();
		TempIdSpecialist=$(this).find('rec_doc_id').text();

		TempContentRec+='<div class="minislots">';
		let minislots = "";
		let findHoliday = false;
		let findSick = false;
		let findVacation = false;
		$(this).find('rec_slots').find('slot').each(function(){
			let IdSlot=''+Id+'_' + $(this).find('time').text() + '';
			IdSlot=IdSlot.replace(/:/gi, '_');

			if($(this).find('status').text() == 'VISIT' || $(this).find('status').text() == 'UNAVAILABLE' || $(this).find('status').text() == 'INSUFFICIENT_DURATION' || $(this).find('status').text() == 'MOS'){
				minislots+='<div class="minislot minislot_schedule ';
				if($(this).find('status').text() == 'VISIT'){
					minislots+='visit';
				}else if($(this).find('status').text() == 'UNAVAILABLE'){
					minislots+='unavailable';
				}else if($(this).find('status').text() == 'INSUFFICIENT_DURATION'){
					minislots+='insufficient_duration';
				}else if($(this).find('status').text() == 'MOS'){
					minislots+='mos';
				}
				minislots+='"';

				minislots+=' title="';
				if($(this).find('status').text() == 'VISIT'){
					minislots+='Занято';
				}else if($(this).find('status').text() == 'UNAVAILABLE'){
					minislots+='Недоступно';
				}else if($(this).find('status').text() == 'INSUFFICIENT_DURATION'){
					minislots+='Недоступно по времени';
				}else if($(this).find('status').text() == 'MOS'){
					minislots+='Слот доступен на mos.ru';
				}

				minislots+='" id="slot_'+IdSlot+'">' + $(this).find('time').text() + '</div>';

			} else if ($(this).find('status').text() == 'HOLIDAY') {
				minislots = "Выходной";
			}  else if ($(this).find('status').text() == 'SICK_LEAVE') {
				minislots = "Больничный";
			}  else if ($(this).find('status').text() == 'VACATION') {
				minislots = "Отпуск";
			}
			else {
				NumFreeSlots++;
				minislots+='<div class="minislot minislot_schedule" title="Свободно">' + $(this).find('time').text() + '</div>';
			}
		});
		TempContentRec += minislots;
		TempContentRec+='</div>';

		TempContentRec+='</div>';

		TempContentRec+='</div>';

		if(true){
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

function ListShowSlotsReschedule(){
	$('#ListRecsReschedule').removeClass('message');

	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#form_reschedule").serialize()+'&action=slots',
		'url': "index.php",
		'beforeSend': function() {
			//показать загрузку
			showLoading('ListRecsReschedule');
			loadSpecies();
		},
		'success': function (Recs) {
			closeLoading('ListRecsReschedule');

			if($(Recs).find('message').text() == 'token_invalid'){
				window.location.href = 'index.php';
			}else{
				let TempContent='';
				let TempRecsNum=0;

				if($(Recs).find('rec').text()){
					TempContent+='<div class="row recs">';

					AllDuration=parseInt($(Recs).find('info').find('duration').text())+parseInt($(Recs).find('info').find('cooldown').text());
					AllPrice=$(Recs).find('info').find('price').text();
					CurrentServices=$(Recs).find('info').find('services').text();
					
					let services=$(Recs).find('info').find('name').text();
					let arrayServices = services.split(';');
					let servicesText='';
					for(i=0; i<=arrayServices.length-1; i++){
						servicesText+='<div class="service_item">'+arrayServices[i]+'</div>';
					}
					document.getElementById('ListInfoReschedule').innerHTML='Выбранные услуги: ' + servicesText + '. Полная цена: '+AllPrice+'₽, общее время: '+AllDuration+' мин.';
					$('#ListInfoReschedule').show();

					let [Content, Num]=showSlots(Recs, 'reschedule');
					TempContent+=Content;
					TempRecsNum=Num;

					TempContent+='</div>';
				}

				if($(Recs).find('message').text() == 'service_not_selected'){
					$('#ListRecsReschedule').addClass('message');
					$('#ListInfoReschedule').hide();
					TempContent='Внимание! Не выбрана услуга. Расчёт времени оказания услуг невозможен.';
				}else{
					if(TempRecsNum == 0){
						$('#ListRecsReschedule').addClass('message');
						TempContent='По данному запросу не найдено записей.';
					}
				}
				document.getElementById('ListRecsReschedule').innerHTML=TempContent;
			}
		}
	});
}

function ListShowSlots(){
	$('#ListRecs').removeClass('message');
	$('#ListRecs').removeClass('error');

	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#form").serialize()+'&action=slots',
		'url': "index.php",
		'beforeSend': function() {
			//показать загрузку
			showLoading('ListRecs');
			loadSpecies();
			loadBreeds();

		},
		'success': function (Recs) {
			closeLoading('ListRecs');

			if($(Recs).find('message').text() == 'token_invalid'){
				window.location.href = 'index.php';
			} else if ($(Recs).find('message').text() == 'date_period_error') {
				$('#ListInfo').hide();
				$('#ListRecs').addClass('error');
				TempContent='Ошибка заполнения даты: дата "по" раньше чем дата "с".';
				document.getElementById('ListRecs').innerHTML=TempContent;
			}
			else {
				let TempContent='';
				let TempRecsNum=0;

				if($(Recs).find('rec').text()){
					TempContent+='<div class="row recs">';

					AllDuration=parseInt($(Recs).find('info').find('duration').text())+parseInt($(Recs).find('info').find('cooldown').text());
					AllPrice=$(Recs).find('info').find('price').text();
					CurrentServices=$(Recs).find('info').find('services').text();
					
					let services=$(Recs).find('info').find('name').text()
					let arrayServices = services.split(';');
					let servicesText='';
					for(i=0; i<=arrayServices.length-1; i++){
						servicesText+='<div class="service_item">'+arrayServices[i]+'</div>';
					}
					document.getElementById('ListInfo').innerHTML='Выбранные услуги: ' + servicesText + '. Полная цена: '+AllPrice+'₽, общее время: '+AllDuration+' мин.';
					$('#ListInfo').show();

					let [Content, Num]=showSlots(Recs, 'slots');
					TempContent+=Content;
					TempRecsNum=Num;

					TempContent+='</div>';
				}

				if($(Recs).find('message').text() == 'service_not_selected'){
					$('#ListRecs').addClass('message');
					$('#ListInfo').hide();
					TempContent='Внимание! Не выбрана услуга. Расчёт времени оказания услуг невозможен.';
				}else{
					if(TempRecsNum == 0){
						$('#ListRecs').addClass('message');
						TempContent='По данному запросу не найдено записей.';
					}
				}
				document.getElementById('ListRecs').innerHTML=TempContent;
			}
		}
	});
}

function showMenu(){
	if($('#modal_menu').css('display') == 'none'){
		$('#modal_menu').show();
	}else{
		$('#modal_menu').hide();
	}
}

function showUserMenu(){
	if($('#modal_user_menu').css('display') == 'none'){
		$('#modal_user_menu').show();
	}else{
		$('#modal_user_menu').hide();
	}
}

function exitUser(){
	let TempDB='localforage';
	indexedDB.deleteDatabase(TempDB);
	
	window.location.href = 'index.php?action=exit';
}

function recoveryPasswordUser(){
	if(!$("#login").val()){
		$("#message").html('<strong>Ошибка!</strong> Поле логин обязательно для заполнения.');
		
		return true;
	}

	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'POST',
		'dataType': 'xml',
		'data': jQuery("#form").serialize()+'&action=recovery',
		'url': "index.php",
		'beforeSend': function() {
			$("#submit_recovery_password").prop("disabled", true);
			$("#login").prop("disabled", true);
		},
		'success': function (Data) {
			if($(Data).find('message').text() == 'recovery_password_error'){
				document.getElementById('message').innerHTML='<strong>Ошибка на сервере.</strong> Повторите попытку позднее.';
				document.getElementById('message').classList.add('error');

				$("#submit_recovery_password").prop("disabled", false);
				$("#login").prop("disabled", false);
			}else if($(Data).find('message').text() == 'ok'){
				document.getElementById('message').innerHTML='<strong>Спасибо!</strong> Запрос на восстановление пароля отправлен.';
				document.getElementById('message').classList.remove('error');
			}
		}
	});
}

function authUser(){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'POST',
		'dataType': 'xml',
		'data': jQuery("#form").serialize()+'&action=auth',
		'url': "index.php",
		'beforeSend': function() {
			
		},
		'success': function (Data) {
			if($(Data).find('message').text() == 'auth_error'){
				document.getElementById('message').innerHTML='<strong>Ошибка!</strong> Произошла ошибка авторизации.';
			}else if($(Data).find('message').text() == 'incorrect_password'){
				document.getElementById('message').innerHTML='<strong>Ошибка!</strong> Неверный логин/пароль.';
			}else if($(Data).find('message').text() == 'user_not_found'){
				document.getElementById('message').innerHTML='<strong>Ошибка!</strong> Пользователь не найден.';
			}else if($(Data).find('message').text() == 'user_access_error'){
				document.getElementById('message').innerHTML='<strong>Ошибка!</strong> Отсутствуют права доступа.';
			}else if($(Data).find('message').text() == 'empty_fields'){
				document.getElementById('message').innerHTML='<strong>Ошибка!</strong> Не заполнены логин/пароль.';
			}else if($(Data).find('message').text() == 'login_ok'){
				if($(Data).find('organization').text()){
					$('#auth_title_login').hide();
					$('#auth_input_login').hide();
					$('#auth_title_password').hide();
					$('#auth_input_password').hide();

					$('#auth_title_organization').show();
					$('#auth_input_organization').show();

					$('#login_').val($(Data).find('login').text());
					$('#token').val($(Data).find('token').text());
					$('#entry_point').val($(Data).find('entry_point').text());

					$('#login').val('');
					$('#password').val('');
										
					$(Data).find('organization').each(function(){
						$('#organization').append('<option value="'+$(this).find('id_organization').text()+'">'+$(this).find('short_name').text()+'</option>');
					});
				}
				document.getElementById('message').innerHTML='';
			}else if($(Data).find('message').text() == 'auth_ok'){
				if($(Data).find('token').text()){
					let TempDB='localforage';
					let TempObject='keyvaluepairs';
					let openRequest=indexedDB.open(TempDB,2);
					let db;
					
					openRequest.onupgradeneeded = function(){
						db=openRequest.result;
						if (!db.objectStoreNames.contains(TempObject)) {
							let objectStore = db.createObjectStore(TempObject);
						}
					};

					openRequest.onsuccess = function(){
					db=openRequest.result;
						let transaction = db.transaction(TempObject, "readwrite");
						let keyvaluepairs = transaction.objectStore(TempObject);
						keyvaluepairs.add($(Data).find('token').text(), 'token');
						keyvaluepairs.add($(Data).find('userid').text(), 'userId');
						keyvaluepairs.add('false', 'menuIsClosed');
					};
				}

				if($(Data).find('entry_point').text() == 'service'){
					window.location.href = '/'+$(Data).find('entry_point').text()+'/index.php';
				}else if($(Data).find('entry_point').text() == 'callcenter'){
					window.location.href = '/'+$(Data).find('entry_point').text()+'/index.php';
				}else if($(Data).find('entry_point').text() == 'analytics'){
					window.location.href = '/'+$(Data).find('entry_point').text()+'/index.php';
				}else if($(Data).find('entry_point').text() == 'support'){
					window.location.href = '/'+$(Data).find('entry_point').text()+'/index.php';
				}else{
					window.location.href = 'index.php';
				}
			}
		}
	});
}

function validateToken(){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'POST',
		'dataType': 'xml',
		'data': 'action=validate_token',
		'url': "index.php",
		'beforeSend': function() {
			
		},
		'success': function (Data) {
			if($(Data).find('message').text() == 'token_invalid'){
				window.location.href = 'index.php';
			}
		}
	});
}

function showModalMenuTab(Temp){
	if(Temp == 1){
		$('#tab1_modalmenu').show();
		$('#tab2_modalmenu').hide();

		$('#tab1_modalmenu_').addClass("current");
		$('#tab2_modalmenu_').removeClass("current");
	}
	if(Temp == 2){
		$('#tab1_modalmenu').hide();
		$('#tab2_modalmenu').show();

		$('#tab1_modalmenu_').removeClass("current");
		$('#tab2_modalmenu_').addClass("current");
	}
}

function resetForm(Temp){
	if(Temp == 'date'){
		$('.date').val('').datepicker('clearDates');
		$('.date').removeAttr('value');
		let date=new Date();
		let month = date.getMonth()+1;
		curdate = date.getFullYear() + '-' + (month < 10 ? '0' : '') + month + '-' + date.getDate();
		$('.date-from').val(curdate).datepicker("update");
		$('.date-from').attr("value", curdate);
		$('.date-to').attr("value", '');
	}else if(Temp == 'time'){
		$('.time').val('').multiselect('refresh');
	}else if(Temp == 'area'){
		$('.area').val('').multiselect('refresh');
		changeAreas();
	}else if(Temp == 'specialization'){
		$('.specialization').val('').multiselect('refresh');
	}else if(Temp == 'organization'){
		$('.organization').val('').multiselect('refresh');
	}else if(Temp == 'doc'){
		$('.doc').val('').multiselect('refresh');
	}else if(Temp == 'metro'){
		$('.metro').val('').multiselect('refresh');
	}else if(Temp == 'service'){
		$('.service option:selected').removeAttr('selected');
		$('.service').val('').multiselect('refresh');
	}else{
		$('.date').val('').datepicker('clearDates');
		$('.date').removeAttr('value');
		let date=new Date();
		let month = date.getMonth()+1;   
		curdate = date.getFullYear() + '-' + (month < 10 ? '0' : '') + month + '-' + date.getDate();
		$('.date-from').val(curdate).datepicker("update");
		$('.date-from').attr("value", curdate);
		$('.date-to').attr("value", '');

		$('.time').val('').multiselect('refresh');
		$('.area').val('').multiselect('refresh');
		$('.specialization').val('').multiselect('refresh');
		$('.organization').val('').multiselect('refresh');
		$('.doc').val('').multiselect('refresh');
		$('.service option:selected').removeAttr('selected');
		$('.service').val('').multiselect('refresh');

		changeAreas();
	}
}

function getFiasTokken(){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': 'action=fias_tokken&mode=xml',
		'url': "index.php",
		'beforeSend': function() {

		},
		'success': function (Data) {
			fiasTokken=$(Data).find('fias_tokken').text();
		}
	});
}

function outNum(num, elem) {
	const time = 1000;
	var step = 1;
	if(num >= 100 && num < 1000){
		step = 10;
	}else if(num >= 1000 && num < 10000){
		step = 100;
	}else if(num >= 10000 && num < 100000){
		step = 1000;
	}else if(num >= 100000){
		step = 10000;		
	}
	num=parseInt(num);
		
	let e = document.getElementById(elem);

	if(num != 0){
		n = 0;
		let t = Math.round(time / (num / step));
		let interval = setInterval(() => {

		n = n + step;
		if(n + step >= num){
			n=num;
		}

		if (n == num) {
			clearInterval(interval);
		}
		e.innerHTML = n;
		}, t);
	}else{
		e.innerHTML = 0;
	}
}

function jcms_format_for_json(text){
	if(text != '' && text !== undefined){
			
		text=text.replace(/"/gi,"&quot;");
		text=text.replace(/'/gi,"&apos;");
	}
	
	return text;
}

function get_current_date(){
	let cur_date = new Date();
	let cur_date_=cur_date.getFullYear();
	if((cur_date.getMonth()+1) < 10){cur_date_+='-0'+(cur_date.getMonth()+1);}else{cur_date_+='-'+(cur_date.getMonth()+1);}
	if(cur_date.getDate() < 10){cur_date_+='-0'+cur_date.getDate();}else{cur_date_+='-'+cur_date.getDate();}
	
	return cur_date_;
}

function toggleQuestion(id) {
	$('#q_' + id).toggleClass('q-hide');

	if ($('#q_' + id).hasClass('q-hide')) {
		$('#i_' + id).removeClass('question__title-block_icon__open-icon');
		$('#i_' + id).addClass('question__title-block_icon__close-icon');
	} else {
		$('#i_' + id).removeClass('question__title-block_icon__close-icon');
		$('#i_' + id).addClass('question__title-block_icon__open-icon');
	}
}

$.fn.setCursorPosition = function(pos) {
	if ($(this).get(0).setSelectionRange) {
		$(this).get(0).setSelectionRange(pos, pos);
	} else if ($(this).get(0).createTextRange) {
		var range = $(this).get(0).createTextRange();
		range.collapse(true);
		range.moveEnd('character', pos);
		range.moveStart('character', pos);
		range.select();
	}
};

$.fn.textWidth = (function (text, font) {
	if(!$.fn.textWidth.fakeEl) $.fn.textWidth.fakeEl = $('<span>').hide().appendTo(document.body);
	$.fn.textWidth.fakeEl.text(text || this.val() || this.text()).css('font', font || this.css('font'));
	return $.fn.textWidth.fakeEl.width();
});

/* cookies */
function vetas_get_cookie(name) {
	let matches = document.cookie.match(new RegExp(
	  "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
	));
	return matches ? decodeURIComponent(matches[1]) : undefined;
}
  
function vetas_set_cookie(name, value, options = {}) {
	options = {
	  path: '/',
		  ...options
	};
  
	if (options.expires instanceof Date) {
	  options.expires = options.expires.toUTCString();
	}
  
	let updatedCookie = encodeURIComponent(name) + "=" + encodeURIComponent(value);
  
	for (let optionKey in options) {
	  updatedCookie += "; " + optionKey;
	  let optionValue = options[optionKey];
	  if (optionValue !== true) {
		updatedCookie += "=" + optionValue;
	  }
	}
	document.cookie = updatedCookie;
}
  
function vetas_delete_cookie(name) {
	setCookie(name, "", {
	  'max-age': -1
	})
}
/* cookies */

$(document).ready(function () {

	$('.date-from').datepicker({multidate: false, language: "ru", format: 'yyyy-mm-dd'});
	$('.date-to').datepicker({multidate: false, language: "ru", format: 'yyyy-mm-dd'});
	$('.time').multiselect({enableClickableOptGroups: true, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '250', maxHeight: 200, enableFiltering: false, enableCaseInsensitiveFiltering : false, selectAllText: 'Выбрать все', includeSelectAllOption: false, filterPlaceholder: 'Выбрать время...'});
	$('.area').multiselect({enableClickableOptGroups: true, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '250', maxHeight: 200, enableFiltering: true, enableCaseInsensitiveFiltering : true, selectAllText: 'Выбрать все', includeSelectAllOption: true, filterPlaceholder: 'Выбрать округ...'});

	$('.type').multiselect({enableClickableOptGroups: true, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '250', maxHeight: 200, enableFiltering: true, enableCaseInsensitiveFiltering : true, selectAllText: 'Выбрать все', includeSelectAllOption: true, filterPlaceholder: 'Выбрать типы услуг...'});

	$('.specialization').multiselect({enableClickableOptGroups: true, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '250', maxHeight: 200, enableFiltering: true, enableCaseInsensitiveFiltering : true, selectAllText: 'Выбрать все', includeSelectAllOption: true, filterPlaceholder: 'Выбрать специализацию...'});
	$('.district').multiselect({enableClickableOptGroups: true, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '250', maxHeight: 200, enableFiltering: true, enableCaseInsensitiveFiltering : true, selectAllText: 'Выбрать все', includeSelectAllOption: true, filterPlaceholder: 'Выбрать район...'});
	
	$('.organization').multiselect({enableClickableOptGroups: true, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '250', maxHeight: 200, enableFiltering: true, enableCaseInsensitiveFiltering : true, selectAllText: 'Выбрать все', includeSelectAllOption: true, filterPlaceholder: 'Выбрать клинику...'});
	$('.brigade').multiselect({enableClickableOptGroups: true, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '250', maxHeight: 200, enableFiltering: true, enableCaseInsensitiveFiltering : true, selectAllText: 'Выбрать все', includeSelectAllOption: true, filterPlaceholder: 'Выбрать бригаду...'});
	$('.shift').multiselect({enableClickableOptGroups: true, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '250', maxHeight: 200, enableFiltering: true, enableCaseInsensitiveFiltering : true, selectAllText: 'Выбрать все', includeSelectAllOption: true, filterPlaceholder: 'Выбрать тип приёма...'});
	
	$('.shelter').multiselect({enableClickableOptGroups: true, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '250', maxHeight: 200, enableFiltering: true, enableCaseInsensitiveFiltering : true, selectAllText: 'Выбрать все', includeSelectAllOption: true, filterPlaceholder: 'Выбрать приют...'});

	$('.operating').multiselect({enableClickableOptGroups: true, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '250', maxHeight: 200, enableFiltering: true, enableCaseInsensitiveFiltering : true, selectAllText: 'Выбрать все', includeSelectAllOption: true, filterPlaceholder: 'Выбрать управляющую организацию...'});

    $('.doc').multiselect({enableClickableOptGroups: true, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '250', maxHeight: 200, enableFiltering: true, enableCaseInsensitiveFiltering : true, selectAllText: 'Выбрать все', includeSelectAllOption: true, filterPlaceholder: 'Выбрать врача...'});
	$('.drug').multiselect({enableClickableOptGroups: true, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '250', maxHeight: 200, enableFiltering: true, enableCaseInsensitiveFiltering : true, selectAllText: 'Выбрать все', includeSelectAllOption: true, filterPlaceholder: 'Выбрать вакцину...'});
	$('.vaccine').multiselect({enableClickableOptGroups: true, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '250', maxHeight: 200, enableFiltering: true, enableCaseInsensitiveFiltering : true, selectAllText: 'Выбрать все', includeSelectAllOption: true, filterPlaceholder: 'Выбрать вакцину...'});

    $('.doc-s').multiselect({enableClickableOptGroups: true, nonSelectedText: 'Выберите...', nSelectedText  : "выбрано", buttonWidth: '350', maxHeight: 200, enableFiltering: true, enableCaseInsensitiveFiltering : true, filterPlaceholder: 'Выбрать врача...'});
    $('.service').multiselect({enableClickableOptGroups: true, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '250', maxHeight: 300, enableCollapsibleOptGroups: true, enableFiltering: true, enableCaseInsensitiveFiltering : true, selectAllText: 'Выбрать все', includeSelectAllOption: true, filterPlaceholder: 'Выбрать услугу...'});
	$('.metro').multiselect({enableClickableOptGroups: true, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '250', maxHeight: 200, enableFiltering: true, enableCaseInsensitiveFiltering : true, selectAllText: 'Выбрать все', includeSelectAllOption: true, filterPlaceholder: 'Выбрать метро...'});

	$('.channel').multiselect({enableClickableOptGroups: true, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '250', maxHeight: 200, enableFiltering: true, enableCaseInsensitiveFiltering : true, selectAllText: 'Выбрать все', includeSelectAllOption: true, filterPlaceholder: 'Выбрать канал записи...'});
	$('.status').multiselect({enableClickableOptGroups: true, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '250', maxHeight: 200, enableFiltering: true, enableCaseInsensitiveFiltering : true, selectAllText: 'Выбрать все', includeSelectAllOption: true, filterPlaceholder: 'Выбрать статус...'});
	$('.species').multiselect({enableClickableOptGroups: true, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '250', maxHeight: 200, enableFiltering: true, enableCaseInsensitiveFiltering : true, selectAllText: 'Выбрать все', includeSelectAllOption: true, filterPlaceholder: 'Выбрать вид животного...'});
	$('.breeds').multiselect({enableClickableOptGroups: true, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '250', maxHeight: 200, enableFiltering: true, enableCaseInsensitiveFiltering : true, selectAllText: 'Выбрать все', includeSelectAllOption: true, filterPlaceholder: 'Выбрать породу животного...'});
	
	$('.disease').multiselect({enableClickableOptGroups: true, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '250', maxHeight: 200, enableFiltering: true, enableCaseInsensitiveFiltering : true, selectAllText: 'Выбрать все', includeSelectAllOption: true, filterPlaceholder: 'Выбрать заболевание...'});


	$('.socialized').multiselect({enableClickableOptGroups: true, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '250', maxHeight: 200, enableFiltering: true, enableCaseInsensitiveFiltering : true, selectAllText: 'Выбрать все', includeSelectAllOption: true, filterPlaceholder: 'Выбрать социализацию...'});
	$('.vaccination').multiselect({enableClickableOptGroups: true, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '250', maxHeight: 200, enableFiltering: true, enableCaseInsensitiveFiltering : true, selectAllText: 'Выбрать все', includeSelectAllOption: true, filterPlaceholder: 'Выбрать...'});
	$('.castrated').multiselect({enableClickableOptGroups: true, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '250', maxHeight: 200, enableFiltering: true, enableCaseInsensitiveFiltering : true, selectAllText: 'Выбрать все', includeSelectAllOption: true, filterPlaceholder: 'Выбрать...'});

	$('.departure_reason').multiselect({enableClickableOptGroups: true, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '250', maxHeight: 200, enableFiltering: true, enableCaseInsensitiveFiltering : true, selectAllText: 'Выбрать все', includeSelectAllOption: true, filterPlaceholder: 'Выбрать причину выбытия...'});

	$('.sender').multiselect({enableClickableOptGroups: true, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '250', maxHeight: 200, enableFiltering: true, enableCaseInsensitiveFiltering : true, selectAllText: 'Выбрать все', includeSelectAllOption: true, filterPlaceholder: 'Выбрать отправителя...'});
	$('.owner').multiselect({
		onChange: function (option) {
			console.log('111');
		},
		buttonText: function(options) {
			if (options.length === 0) {
				return 'None selected';
			}
		},
		enableClickableOptGroups: true, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '250', maxHeight: 200, enableFiltering: true, enableCaseInsensitiveFiltering : true, selectAllText: 'Выбрать все', includeSelectAllOption: true, filterPlaceholder: 'Выбрать владельца...'
	});

	$(".multiselect-filter").addClass("fixed-custom-filter-select");

	$('#questions').multiselect({enableClickableOptGroups: true, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '250', maxHeight: 200, enableFiltering: true, enableCaseInsensitiveFiltering : true, selectAllText: 'Выбрать все', includeSelectAllOption: true, filterPlaceholder: 'Выбрать вопросы...'});

	// $('.owner').on("keyup", function(e) {
	// 	var keyword = e.target.value;
	// 	if (keyword.length > 2) {
	// 		console.log('111');
	// 	//   $.ajax({
	// 	// 	url: "https://jsonplaceholder.typicode.com/users",
	// 	// 	type: "get",
	// 	// 	dataType: "json",
	// 	// 	success: function(response) {
	// 	// 	  $('#example-getting-started').empty() //clear previous options
	// 	// 	  $.each(response, function(i, option) {
	// 	// 		$('#example-getting-started').append("<option value=" + option.name + ">" + option.name + "</option>") //append to select itself
	// 	// 	  })
	// 	// 	  $('#example-getting-started').multiselect('rebuild') //rebuild your select
	// 	// 	  $('.multiselect-search').val(keyword) //again set search-box..value..it was lost when rebuild
	// 	// 	  $('.multiselect-search').focus()
	// 	// 	}
	// 	//   });
	// 	}
	//   });

	// $('#owner').multiselect({
    //     onсhange: function (option, checked) {
    //         console.log('111');
    //     }
    // });

	getFiasTokken();
	loadTemplates();
});
