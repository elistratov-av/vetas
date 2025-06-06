makerescheduleVisit_last=makerescheduleVisit;

function makerescheduleVisit(){
    document.querySelector("#container_reason select[name='reason_select']").value='';
    document.querySelector("#container_reason b").innerText="Причина переноса приёма";
    $("#background_reason").fadeIn();
	$("#container_reason").show();
}

function makerescheduleVisit_next(){
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

//====================================================================================
function makerescheduleVisit_next(){
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

//====================================================================================
function rescheduleVisit(Id,vDate,services,specid,orgid){
	//открываем окно со слотами
	CurrentRescheduleVisitId=Id;

	//при этом остаётся набор услуг
	$("#sub_container_m").html('');
	$("#reschedule").show();
	$("#sub_container_m").append($("#reschedule"));
	$("#background_m").fadeIn();
	$("#container_m").show();

    if(vDate){
        document.querySelector("#reschedule input[name='date-from']").value=vDate;
        document.querySelector("#reschedule input[name='date-to']").value=vDate;
    }
    /*var element = document.querySelector("#reschedule select[name='service']");
    for (var i = 0; i < element.options.length; i++) {
        element.options[i].selected = services.indexOf(element.options[i].value) >= 0;
    }*/
    $('#reschedule select[name="doc"]').val(specid).multiselect('refresh');
    $('#reschedule select[name="organization"]').val(orgid).multiselect('refresh');
    $('#reschedule select[name="service"]').val(services).multiselect('refresh');
    
	ListShowSlotsReschedule();
	//остаётся владелец и животное
}

//====================================================================================
function ListShowVisits(id){
	$(".telephone").unmask();
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#form_owners").serialize()+'&action=visits&id='+id,
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
                        let services="[";
						$(this).find('rec_services').find('service').each(function(){
							services+="'"+$(this).find('id').text()+"',";
                            TempContentRec+='<div class="service_item">' + $(this).find('name').text() + '</div>';
						});
                        services+="]";
						TempContentRec+='</div>';

						
						TempContentRec+='<div class="col-xl-3 col-lg-3 col-md-12 col-sm-12 col-xs-12 col-12" style="text-align: right;">';
						if($(this).find('rec_status').text() == 'N'){
							TempContentRec+='<button class="reschedule_visit" onclick="rescheduleVisit(' + Id + ',\''+($(this).find('rec_start_date').text()!='' ? date.toISOString() : '')+'\','+services+','+$(this).find('rec_specialist_id').text()+','+$(this).find('rec_org_id').text()+');" id="reschedule_visit_'+Id+'" style="width: 200px;">Перенести приём</button>';
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

//====================================================================================
function showVisitsWindow(id){
	let TempContent='';

	TempContent+='<div class="window main_row col-xl-8 col-lg-10 col-12">';
	//
	TempContent+='<div class="close" onclick="closeVisitsWindow();"></div>';

	TempContent+='<div class="top">Просмотр приёма</div>';

	
	TempContent+='<div class="results" id="ListVisits"><div class="loading"></div></div>';
	
	TempContent+='</div>';

    ListShowVisits(id);

	document.getElementById("sub_container").innerHTML=TempContent;
	$("#background").fadeIn();
	$("#container").show();
}

//====================================================================================
var currentPage = '';
function ListLogRecord(Page = 1){
	if((currentPage == '' && Page != null) || (currentPage != Page)){currentPage = Page;}

	$('#ListRecs').removeClass('message');
	$('#ListRecs').removeClass('error');

	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#form").serialize()+'&page='+Page+'&action=list_log_record&mode=xml',
		'url': "index.php",
		'beforeSend': function() {
			//показать загрузку
			showLoading('ListRecs');
		},
		'success': function (Recs) {
            //console.log(Recs);
            closeLoading('ListRecs');
			if($(Recs).find('message').text() == 'token_invalid'){
				window.location.href = 'index.php';
			}else{
				let TempContent='';
				let TempRecsNum=0;

				if($(Recs).find('rec').text()){
					TempContent+='<table class="w-100">';
					TempContent+='<tr>';
					/*TempContent+='<th style="width:120px"><div>Дата создания записи сотрудником</div></th>';
					TempContent+='<th style="width:120px"><div>Время создания записи сотрудником</div></th>';
					TempContent+='<th style="width:150px"><div>Клиника</div></th>';
					TempContent+='<th style="width:120px"><div>Услуга</div></th>';
                    TempContent+='<th style="width:70px"><div>ID приема</div></th>';
                    TempContent+='<th style="width:100px"><div>Дата и время приема</div></th>';
                    TempContent+='<th style="width:130px"><div>Статус приема</div></th>';
                    TempContent+='<th style="width:100px"><div>Оператор</div></th>';
                    TempContent+='<th style="width:90px"><div>Действие</div></th>';
                    TempContent+='<th style="width:90px"><div>Комментарий</div></th>';*/
                    TempContent+='<th>Дата создания записи сотрудником</th>';
					TempContent+='<th>Время создания записи сотрудником</th>';
					TempContent+='<th>Клиника</th>';
					TempContent+='<th>Услуга</th>';
                    TempContent+='<th>ID приема</th>';
                    TempContent+='<th>Дата и время приема</th>';
                    TempContent+='<th>Статус приема</th>';
                    TempContent+='<th>Оператор</th>';
                    TempContent+='<th>Действие</th>';
                    TempContent+='<th>Комментарий</th>';
					TempContent+='</tr>';

					$(Recs).find('rec').each(function(){
						let FLAG=0;
						let TempContentRec='<tr>';
						TempContentRec+='<td>' + $(this).find('rec_date_at').text() + '</td>';
						TempContentRec+='<td>' + $(this).find('rec_time_at').text() + '</td>';
						TempContentRec+='<td>' + $(this).find('rec_short_name').text() + '</td>';
						TempContentRec+='<td>' + $(this).find('rec_name_service').text() + '</td>';
                        TempContentRec+='<td><a style="color:#0000bb" href=\'#\' onclick="showVisitsWindow(' + $(this).find('rec_id').text() + '); return false;">' + $(this).find('rec_id').text() + '</a></td>';    
                        TempContentRec+='<td>' + $(this).find('rec_time_range').text() + '</td>';
                        TempContentRec+='<td>' + $(this).find('rec_sname').text() + '</td>'; 
                        TempContentRec+='<td>' + $(this).find('rec_fullname').text() + '</td>'; 
                        TempContentRec+='<td>' + $(this).find('').text() + '</td>';   
                        TempContentRec+='<td>' + $(this).find('rec_comment').text() + '</td>'; 

						//TempContentRec+='<td style="width: 32px;" onclick="showBreedsWindow(' + $(this).find('rec_id').text() + ');"><div class="edit"></div></td>';
						//TempContentRec+='<td style="width: 32px;" onclick="deleteBreedsAcceptWindow(\''+$(this).find('rec_name').text()+'\', ' + $(this).find('rec_id').text() + ');"><div class="delete"></div></td>';
						TempContentRec+='</tr>';

						if(FLAG == 0){
							TempRecsNum++;
							TempContent+=TempContentRec;
						}
					});
					TempContent+='</table>';

					/////////////////
					TempContent+='<div class="pages">';
					if(parseInt($(Recs).find('counter').text()) > parseInt($(Recs).find('to').text())){
						TempContent+='<div class="info">'+$(Recs).find('from').text()+' - '+$(Recs).find('to').text()+' из '+$(Recs).find('counter').text()+' записей</div>';
					}else{
						TempContent+='<div class="info">'+$(Recs).find('from').text()+' - '+$(Recs).find('counter').text()+' из '+$(Recs).find('counter').text()+' записей</div>';
					}
					if(parseInt($(Recs).find('current').text()) > 1){
						TempContent+='<div class="first" onclick="ListLogRecord(1,\''+$(Recs).find('sort').text()+'\')")"></div>';
						TempContent+='<div class="previous" onclick="ListLogRecord('+(parseInt($(Recs).find('current').text())-1)+',\''+$(Recs).find('sort').text()+'\')")"></div>';
					}
					if($(Recs).find('current').text() > 3){
						for(let i=parseInt($(Recs).find('current').text())-2;i<=parseInt($(Recs).find('current').text())+2;i++){
							if(i <= (parseInt($(Recs).find('last').text())+1)){
								TempContent+='<div ';
								if($(Recs).find('current').text() == i){
									TempContent+='class="current" ';
								}
								TempContent+='onclick="ListLogRecord('+i+',\''+$(Recs).find('sort').text()+'\')")">' + i + '</div>';
							}
						}
					}else{
						for(let i=1;i<=5;i++){
							if(i <= (parseInt($(Recs).find('last').text())+1)){
								TempContent+='<div ';
								if($(Recs).find('current').text() == i){
									TempContent+='class="current" ';
								}
								TempContent+='onclick="ListLogRecord('+i+',\''+$(Recs).find('sort').text()+'\')">' + i + '</div>';
							}
						}
					}
						
					if(parseInt($(Recs).find('current').text()) < (parseInt($(Recs).find('last').text())+1)){
						TempContent+='<div class="next" onclick="ListLogRecord('+(parseInt($(Recs).find('current').text())+1)+',\''+$(Recs).find('sort').text()+'\')")"></div>';
						TempContent+='<div class="last" onclick="ListLogRecord('+(parseInt($(Recs).find('last').text())+1)+',\''+$(Recs).find('sort').text()+'\')")"></div>';
					}

					TempContent+='</div>';
					/////////////////
				}

				
				if(TempRecsNum == 0){
					TempContent='<div class="message">По данному запросу не найдено записей.</div>';
				}
				
				document.getElementById('ListRecs').innerHTML=TempContent;
			}
		}
	});
}

//====================================================================================
function resetLogForm(){
    /*$('#create-d-from').val('');
    $('#create-d-to').val('');
    $('#create-t-from').val(''); 
    $('#create-t-to').val('');*/ 
    $('#organization').val('').multiselect('refresh');
    $('#service').val('').multiselect('refresh');
    //$('#visit-id').val('');
    $('#users').val('').multiselect('refresh');
    //$('#visit-from').val('');
    //$('#visit-to').val('');
    $('#status').val('').multiselect('refresh');
}

//====================================================================================
$(document).ready(function () {
	$('#users').multiselect({enableClickableOptGroups: true, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '250', maxHeight: 200, enableFiltering: true, enableCaseInsensitiveFiltering : true, selectAllText: 'Выбрать все', includeSelectAllOption: true, filterPlaceholder: 'Выбрать оператора...'});
  	$('#status').multiselect({enableClickableOptGroups: true, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '215', maxHeight: 200, enableFiltering: true, enableCaseInsensitiveFiltering : true, selectAllText: 'Выбрать все', includeSelectAllOption: true, filterPlaceholder: 'Выбрать статус...'});
    
});