var ArrayOrgsRecs=new Array();
var ArrayServicesRecs=new Array();
var ArraySpeciesRecs=new Array();
var ArrayOrgTypesRecs=new Array();
var ArrayAreasRecs=new Array();
var ArrayRolesRecs=new Array();
var ArrayElementsRecs=new Array();

var currentPage = '';
var currentSort = '';
var currentDirection = 0;

function loadOrgs(){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': 'mode=xml&action=orgs',
		'url': "index.php",
		'beforeSend': function() {
			
		},
		'success': function (Recs) {
			if($(Recs).find('rec').text()){
				$(Recs).find('rec').each(function(){
					ArrayOrgsRecs.push({id: $(this).find('rec_id').text(), name: $(this).find('rec_name').text(), short_name: $(this).find('rec_short_name').text()});
				});
			}
		}
	});
}
function loadServices(){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': 'mode=xml&action=services',
		'url': "index.php",
		'beforeSend': function() {
			
		},
		'success': function (Recs) {
			if($(Recs).find('rec').text()){
				$(Recs).find('rec').each(function(){
					ArrayServicesRecs.push({id: $(this).find('rec_id').text(), name: $(this).find('rec_name').text()});
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
		'data': 'mode=xml&action=species',
		'url': "index.php",
		'beforeSend': function() {
			
		},
		'success': function (Recs) {
			if($(Recs).find('rec').text()){
				$(Recs).find('rec').each(function(){
					ArraySpeciesRecs.push({id: $(this).find('rec_id').text(), name: $(this).find('rec_name').text()});
				});
			}
		}
	});
}

function loadOrgTypes(){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': 'mode=xml&action=org_types',
		'url': "index.php",
		'beforeSend': function() {
			
		},
		'success': function (Recs) {
			if($(Recs).find('rec').text()){
				$(Recs).find('rec').each(function(){
					ArrayOrgTypesRecs.push({id: $(this).find('rec_id').text(), name: $(this).find('rec_name').text()});
				});
			}
		}
	});
}

function loadAreas(){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': 'mode=xml&action=areas',
		'url': "index.php",
		'beforeSend': function() {
			
		},
		'success': function (Recs) {
			if($(Recs).find('rec').text()){
				$(Recs).find('rec').each(function(){
					ArrayAreasRecs.push({id: $(this).find('rec_id').text(), name: $(this).find('rec_name').text()});
				});
			}
		}
	});
}

function loadRoles(){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': 'mode=xml&type=1&action=show_elements',
		'url': "index.php",
		'beforeSend': function() {
			
		},
		'success': function (Recs) {
			if($(Recs).find('rec').text()){
				$(Recs).find('rec').each(function(){
					ArrayRolesRecs.push({description: $(this).find('rec_description').text(), name: $(this).find('rec_name').text()});
				});
			}
		}
	});
}

function loadElements(){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': 'mode=xml&type=2&action=show_elements',
		'url': "index.php",
		'beforeSend': function() {
			
		},
		'success': function (Recs) {
			if($(Recs).find('rec').text()){
				$(Recs).find('rec').each(function(){
					ArrayElementsRecs.push({description: $(this).find('rec_description').text(), name: $(this).find('rec_name').text()});
				});
			}
		}
	});
}

function getUserPassword(hash){
		jQuery.ajax({
			'async': true,
			'global': false,
			'cache': false,
			'type': 'GET',
			'dataType': 'xml',
			'data': jQuery("#form_user_password").serialize()+'&action=get_user_password',
			'url': "index.php",
			'beforeSend': function() {
				showLoading('user_password');
			},
			'success': function (Data) {
				if($(Data).find('user_password').text()){
					document.getElementById('user_password').innerHTML=$(Data).find('user_password').text();
				}
			}
		});
}

function deletePetOwnerLink(Id){
	if(Id){
		jQuery.ajax({
			'async': true,
			'global': false,
			'cache': false,
			'type': 'GET',
			'dataType': 'xml',
			'data': '&id='+Id+'&action=delete_pet_owner_link',
			'url': "index.php",
			'beforeSend': function() {
				showLoading('ListRecs');
			},
			'success': function (Data) {
				if($(Data).find('message').text() == 'pet_owner_link_deleted'){
					ListShowPetsRecs();			
				}
			}
		});
	}
}

function departurePet(Id){
	if(Id){
		jQuery.ajax({
			'async': true,
			'global': false,
			'cache': false,
			'type': 'GET',
			'dataType': 'xml',
			'data': '&id='+Id+'&action=departure_pet',
			'url': "index.php",
			'beforeSend': function() {
				showLoading('ListRecs');
			},
			'success': function (Data) {
				if($(Data).find('message').text() == 'pet_departured'){
					ListShowPetsRecs();			
				}
			}
		});
	}
}

function returnPet(Id){
	if(Id){
		jQuery.ajax({
			'async': true,
			'global': false,
			'cache': false,
			'type': 'GET',
			'dataType': 'xml',
			'data': '&id='+Id+'&action=return_pet',
			'url': "index.php",
			'beforeSend': function() {
				showLoading('ListRecs');
			},
			'success': function (Data) {
				if($(Data).find('message').text() == 'pet_returned'){
					ListShowPetsRecs();			
				}
			}
		});
	}
}

function deleteOwner(Id){
	if(Id){
		jQuery.ajax({
			'async': true,
			'global': false,
			'cache': false,
			'type': 'GET',
			'dataType': 'xml',
			'data': '&id='+Id+'&action=delete_owner',
			'url': "index.php",
			'beforeSend': function() {
				showLoading('ListRecs');
			},
			'success': function (Data) {
				if($(Data).find('message').text() == 'owner_deleted'){
					ListShowOwnersRecs();			
				}
			}
		});
	}
}

function returnOwner(Id){
	if(Id){
		jQuery.ajax({
			'async': true,
			'global': false,
			'cache': false,
			'type': 'GET',
			'dataType': 'xml',
			'data': '&id='+Id+'&action=return_owner',
			'url': "index.php",
			'beforeSend': function() {
				showLoading('ListRecs');
			},
			'success': function (Data) {
				if($(Data).find('message').text() == 'owner_returned'){
					ListShowOwnersRecs();			
				}
			}
		});
	}
}

function dismissSpecialist(Id){
	if(Id){
		jQuery.ajax({
			'async': true,
			'global': false,
			'cache': false,
			'type': 'GET',
			'dataType': 'xml',
			'data': '&id='+Id+'&action=dismiss_specialist',
			'url': "index.php",
			'beforeSend': function() {
				showLoading('ListRecs');
			},
			'success': function (Data) {
				if($(Data).find('message').text() == 'specialist_dismissed'){
					ListShowUsersRecs();
				}
			}
		});
	}
}

function cancelVisits() {
	$('.for_cancel_visits:checked').each(function() {
		jQuery.ajax({
			'async': true,
			'global': false,
			'cache': false,
			'type': 'GET',
			'dataType': 'xml',
			'data': '&id='+$(this).attr('visit_id')+'&action=cancel_visit',
			'url': "index.php",
			'beforeSend': function() {
				showLoading('ListRecs');
			},
			'success': function (Data) {
				if($(Data).find('message').text() == 'visit_closed'){
					ListShowVisitsRecs();
				}
			}
		});
	});
}


function deleteSpecialist(Id){
	if(Id){
		jQuery.ajax({
			'async': true,
			'global': false,
			'cache': false,
			'type': 'GET',
			'dataType': 'xml',
			'data': '&id='+Id+'&action=delete_specialist',
			'url': "index.php",
			'beforeSend': function() {
				showLoading('ListRecs');
			},
			'success': function (Data) {
				if($(Data).find('message').text() == 'specialist_deleted'){
					ListShowUsersRecs();
				}
			}
		});
	}
}

function returnSpecialist(Id){
	if(Id){
		jQuery.ajax({
			'async': true,
			'global': false,
			'cache': false,
			'type': 'GET',
			'dataType': 'xml',
			'data': '&id='+Id+'&action=return_specialist',
			'url': "index.php",
			'beforeSend': function() {
				showLoading('ListRecs');
			},
			'success': function (Data) {
				if($(Data).find('message').text() == 'specialist_returned'){
					ListShowUsersRecs();
				}
			}
		});
	}
}

function closeVisit(Id){
	if(Id){
		jQuery.ajax({
			'async': true,
			'global': false,
			'cache': false,
			'type': 'GET',
			'dataType': 'xml',
			'data': '&id='+Id+'&action=close_visit',
			'url': "index.php",
			'beforeSend': function() {
				showLoading('ListRecs');
			},
			'success': function (Data) {
				if($(Data).find('message').text() == 'visit_closed'){
					ListShowVisitsRecs();			
				}
			}
		});
	}
}

function workVisit(Id){
	if(Id){
		jQuery.ajax({
			'async': true,
			'global': false,
			'cache': false,
			'type': 'GET',
			'dataType': 'xml',
			'data': '&id='+Id+'&action=work_visit',
			'url': "index.php",
			'beforeSend': function() {
				showLoading('ListRecs');
			},
			'success': function (Data) {
				if($(Data).find('message').text() == 'visit_worked'){
					ListShowVisitsRecs();
				}
			}
		});
	}
}

function cancelSVisit(Id){
	if(Id){
		jQuery.ajax({
			'async': true,
			'global': false,
			'cache': false,
			'type': 'GET',
			'dataType': 'xml',
			'data': '&id='+Id+'&action=cancel_visit',
			'url': "index.php",
			'beforeSend': function() {
				showLoading('ListRecs');
			},
			'success': function (Data) {
				if($(Data).find('message').text() == 'visit_canceled'){
					ListShowVisitsRecs();
				}
			}
		});
	}
}

function cancelVisitPaid(Id){
	if(Id){
		jQuery.ajax({
			'async': true,
			'global': false,
			'cache': false,
			'type': 'GET',
			'dataType': 'xml',
			'data': '&id='+Id+'&action=cancel_visit_paid',
			'url': "index.php",
			'beforeSend': function() {
				showLoading('ListRecs');
			},
			'success': function (Data) {
				if($(Data).find('message').text() == 'visit_paid_canceled'){
					ListShowVisitsRecs();
				}
			}
		});
	}
}

function ListShowPetsRecs(){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#form_search_recs").serialize()+'&action=pets',
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
					$(Recs).find('rec').each(function(){
						let FLAG=0;
						let Id=$(this).find('rec_id').text();

						TempContentRec='<div class="rec col-12 main_row row">';
						TempContentRec+='<div class="col-xl-3 col-lg-3 col-md-12 col-sm-12 col-xs-12 col-12">';
						TempContentRec+='<span class="title">';
						
						if($(this).find('rec_sex').text() == 'm'){
							TempContentRec+='♂ ';
						}else if($(this).find('rec_sex').text() == 'f'){
							TempContentRec+='♀ ';
						}
						
						TempContentRec+='' + $(this).find('rec_name').text() + '</span>';
						
						if($(this).find('rec_expire_reason').text()){
							TempContentRec+='<br><strong><font color="red">Снят с учёта ' + $(this).find('rec_expire_reason').text() + '</font></strong>';
							TempContentRec+='<button class="button mini" title="Вернуть животное на учёт" onclick="returnPet(' + $(this).find('rec_id').text() + ');">Вернуть животное на учёт</button><br />';
						}else{
							TempContentRec+='<br><button class="button mini" title="Снять животное с учёта" onclick="departurePet(' + $(this).find('rec_id').text() + ');">Снять животное с учёта</button><br />';
						}

						if($(this).find('rec_birthday').text()){
							TempContentRec+='<br><span class="birthday" title="День рождения">' + $(this).find('rec_birthday').text() + '</span>';
						}
						if($(this).find('rec_breed_name').text()){
							TempContentRec+=', ' + $(this).find('rec_breed_name').text() + '';
						}
						if($(this).find('rec_species_name').text()){
							TempContentRec+=', ' + $(this).find('rec_species_name').text() + '';
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
						TempContentRec+='</div>';

						TempContentRec+='<div class="col-xl-2 col-lg-2 col-md-12 col-sm-12 col-xs-12 col-12 addresses">';
						if($(this).find('rec_ext_id').text()){
							TempContentRec+='' + $(this).find('rec_ext_id').text() + '';
						}else{
							TempContentRec+='-';
						}
						TempContentRec+='</div>';
						
						TempContentRec+='<div class="col-xl-5 col-lg-5 col-md-12 col-sm-12 col-xs-12 col-12">';
						let i=0;
						$(this).find('rec_owners').find('owner').each(function(){
							TempContentRec+='<div class="d-table w-100 p-1"><div class="d-table-cell w-75">';
							if($(this).find('fullname').text()){
								TempContentRec+='<a href="https://vetas.mos.ru/animals/owners/' + $(this).find('id').text() + '/edit/main-info/"><strong>' + $(this).find('fullname').text() + '</strong></a>';
							}
							TempContentRec+=' <font size="1">';
							if($(this).find('id_owner_type').text() == '2'){
								TempContentRec+='Представитель';
							}else{
								TempContentRec+='Владелец';
							}
							TempContentRec+='</font>';
							
							TempContentRec+='</div>';
							TempContentRec+='<div class="d-table-cell text-right w-25"><button class="button" title="Удалить связь" onclick="deletePetOwnerLink(' + $(this).find('pets_to_owner_id').text() + ');">x</button></div>';
							TempContentRec+='</div>';
							i++;
						});
						TempContentRec+='<div class="pet">';
						TempContentRec+='111';
						TempContentRec+='</div>';

						TempContentRec+='</div>';

						TempContentRec+='</div>';

						

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

function ListShowIdentificationRecs(){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#form_search_identification_recs").serialize()+'&action=identifications',
		'url': "index.php",
		'beforeSend': function() {
			//показать загрузку
			showLoading('ListRecsIds');
		},
		'success': function (Recs) {
			if($(Recs).find('message').text() == 'token_invalid'){
				window.location.href = 'index.php';
			}else{
				let TempContent='';
				let TempRecsNum=0;

				if($(Recs).find('rec').text()){
					$(Recs).find('rec').each(function(){
						let FLAG=0;
						let Id=$(this).find('rec_id').text();

						TempContentRec='<div class="rec col-12 main_row row">';

						TempContentRec+='<div class="col-xl-4 col-lg-4 col-md-12 col-sm-12 col-xs-12 col-12">';
						TempContentRec+='<span class="title">' + $(this).find('rec_type').text() + '';
						if($(this).find('rec_main_flag').text() == 't'){
							TempContentRec+=' (основной)';
						}
						TempContentRec+='</span></div>';

						TempContentRec+='<div class="col-xl-4 col-lg-4 col-md-12 col-sm-12 col-xs-12 col-12">';
						TempContentRec+='<span class="title">' + $(this).find('rec_identification_code').text() + ' <small>(ID ' + $(this).find('rec_id').text() + ')</small></span>';
						TempContentRec+='</div>';

						
						TempContentRec+='<div class="col-xl-4 col-lg-4 col-md-12 col-sm-12 col-xs-12 col-12">';
						TempContentRec+='ID PET: ';
						TempContentRec+='<a href="https://vetas.mos.ru/animals/' + $(this).find('rec_id_pet').text() + '/edit/main-info/"><strong>' + $(this).find('rec_id_pet').text() + '</strong></a>';
						TempContentRec+='</div>';

						TempContentRec+='</div>';

						

						if(FLAG == 0){
							TempRecsNum++;
							TempContent+=TempContentRec;
						}
					});
				}

				if(TempRecsNum == 0){
					TempContent='<div class="message">По данному запросу не найдено записей.</div>';
				}
				document.getElementById('ListRecsIds').innerHTML=TempContent;
			}
		}
	});
}

function ListShowPetsTempRecs(){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#form_search_temp_recs").serialize()+'&action=temp_pets',
		'url': "index.php",
		'beforeSend': function() {
			//показать загрузку
			showLoading('ListTempRecs');
		},
		'success': function (Recs) {
			if($(Recs).find('message').text() == 'token_invalid'){
				window.location.href = 'index.php';
			}else{
				let TempContent='';
				let TempRecsNum=0;

				if($(Recs).find('rec').text()){
					$(Recs).find('rec').each(function(){
						let FLAG=0;
						let Id=$(this).find('rec_id').text();

						TempContentRec='<div class="rec col-12 main_row row">';
						TempContentRec+='<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12 col-12">';
						TempContentRec+='<span class="title">';
						
						if($(this).find('rec_sex').text() == 'm'){
							TempContentRec+='♂ ';
						}else if($(this).find('rec_sex').text() == 'f'){
							TempContentRec+='♀ ';
						}
						
						TempContentRec+='' + $(this).find('rec_name').text() + '</span>';
						
						if($(this).find('rec_expire_reason').text()){
							TempContentRec+='<br><strong><font color="red">Снят с учёта ' + $(this).find('rec_expire_reason').text() + '</font></strong>';
							TempContentRec+='<button class="button mini" title="Вернуть животное на учёт" onclick="returnPet(' + $(this).find('rec_id').text() + ');">Вернуть животное на учёт</button><br />';
						}

						if($(this).find('rec_birthday').text()){
							TempContentRec+='<br><span class="birthday" title="День рождения">' + $(this).find('rec_birthday').text() + '</span>';
						}
						if($(this).find('rec_breed_name').text()){
							TempContentRec+=', ' + $(this).find('rec_breed_name').text() + '';
						}
						if($(this).find('rec_species_name').text()){
							TempContentRec+=', ' + $(this).find('rec_species_name').text() + '';
						}
						TempContentRec+='</div>';

						TempContentRec+='</div>';

						TempContentRec+='</div>';

						

						if(FLAG == 0){
							TempRecsNum++;
							TempContent+=TempContentRec;
						}
					});
				}

				if(TempRecsNum == 0){
					TempContent='<div class="message">По данному запросу не найдено записей.</div>';
				}
				document.getElementById('ListTempRecs').innerHTML=TempContent;
			}
		}
	});
}

function ListShowOwnersRecs(){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#form_search_recs").serialize()+'&action=owners',
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
					$(Recs).find('rec').each(function(){
						let FLAG=0;
						let Id=$(this).find('rec_id').text();

						TempContentRec='<div class="rec col-12 main_row row">';
						TempContentRec+='<div class="col-xl-3 col-lg-3 col-md-12 col-sm-12 col-xs-12 col-12">';

						if($(this).find('rec_is_deleted').text() == 't'){TempContentRec+='<del>';}
						TempContentRec+='<span class="title">' + $(this).find('rec_fullname').text() + ' (' + $(this).find('rec_id').text() + ')</span>';
						if($(this).find('rec_is_deleted').text() == 't'){TempContentRec+='</del>';}

						if($(this).find('rec_birthday').text()){
							TempContentRec+='<br><span class="birthday" title="День рождения">' + $(this).find('rec_birthday').text() + '</span>';
						}
						if($(this).find('rec_snils').text()){
							TempContentRec+='<br><span class="snils" title="СНИЛС">' + $(this).find('rec_snils').text() + '</span>';
						}
						if($(this).find('rec_inn').text()){
							TempContentRec+='<br><span class="snils" title="ИНН">' + $(this).find('rec_inn').text() + '</span>';
						}
						if($(this).find('rec_ogrn').text()){
							TempContentRec+='<br><span class="snils" title="ОГРН">' + $(this).find('rec_ogrn').text() + '</span>';
						}
						if($(this).find('rec_sso_id').text()){
							TempContentRec+='<br><font size="1">SSO ID: ' + $(this).find('rec_sso_id').text() + '</font>';
						}

						TempContentRec+='<br><font size="1">';
						if($(this).find('rec_is_main').text() == 't'){
							TempContentRec+='<strong>Главная</strong>';
						}else if($(this).find('rec_is_main').text() == 'f'){
							TempContentRec+='Дубль';
						}else{
							TempContentRec+='Неопределено';
						}
						
						if($(this).find('rec_id_main_owner').text()){
							TempContentRec+=', ID главной записи: ' + $(this).find('rec_id_main_owner').text() + '';
						}

						TempContentRec+=', дата валидации: ';
						if($(this).find('rec_duble_validation').text()){
							TempContentRec+='' + $(this).find('rec_duble_validation').text() + '';
						}else{
							TempContentRec+='отсутствует';
						}
						TempContentRec+='.</font>';
						
						TempContentRec+='<br><font size="1">Создано: '+$(this).find('rec_created_at').text()+', обновлено: '+$(this).find('rec_updated_at').text()+'.</font>';

						if($(this).find('rec_is_deleted').text() == 't'){
							TempContentRec+='<br><button class="button mini" title="Вернуть владельца" onclick="returnOwner(' + $(this).find('rec_id').text() + ');">Вернуть владельца</button><br />';
						}else{
							TempContentRec+='<br><button class="button mini" title="Удалить владельца" onclick="deleteOwner(' + $(this).find('rec_id').text() + ');">Удалить владельца</button><br />';
						}

						TempContentRec+='</div>';

						TempContentRec+='<div class="col-xl-2 col-lg-2 col-md-12 col-sm-12 col-xs-12 col-12 contacts">';
						TempContentRec+='<h3>Контакты</h3>';
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
						TempContentRec+='</div>';

						TempContentRec+='<div class="col-xl-2 col-lg-2 col-md-12 col-sm-12 col-xs-12 col-12 addresses">';
						if($(this).find('rec_ext_id').text()){
							TempContentRec+='' + $(this).find('rec_ext_id').text() + '';
						}else{
							TempContentRec+='-';
						}
						TempContentRec+='</div>';
						
						TempContentRec+='<div class="col-xl-5 col-lg-5 col-md-12 col-sm-12 col-xs-12 col-12">';
						TempContentRec+='<h3>Животные</h3>';
						let i=0;
						$(this).find('rec_pets').find('pet').each(function(){
							TempContentRec+='<div class="d-table w-100 p-1"><div class="d-table-cell w-75">';
							TempContentRec+='<a href="https://vetas.mos.ru/animals/' + $(this).find('id').text() + '/edit/main-info/"><strong>' + $(this).find('name').text() + '</strong></a>';
							TempContentRec+='</div>';
							// TempContentRec+='<div class="d-table-cell text-right w-25"><button class="button" title="Удалить связь" onclick="deleteOwnerPetLink(' + $(this).find('pets_to_owner_id').text() + ');">x</button></div>';
							TempContentRec+='</div>';
							i++;
						});
						TempContentRec+='<div class="pet">';

												
						

						TempContentRec+='</div>';

						TempContentRec+='</div>';

						TempContentRec+='</div>';

						

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

function ListShowOwnersTempRecs(){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#form_search_temp_recs").serialize()+'&action=temp_owners',
		'url': "index.php",
		'beforeSend': function() {
			//показать загрузку
			showLoading('ListTempRecs');
		},
		'success': function (Recs) {
			if($(Recs).find('message').text() == 'token_invalid'){
				window.location.href = 'index.php';
			}else{
				let TempContent='';
				let TempRecsNum=0;

				if($(Recs).find('rec').text()){
					$(Recs).find('rec').each(function(){
						let FLAG=0;
						let Id=$(this).find('rec_id').text();
						TempContentRec='<div class="rec col-12 main_row row">';
						TempContentRec+='<div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12 col-12">';
						TempContentRec+='<span class="title">' + $(this).find('rec_f_fio').text() + ' ' + $(this).find('rec_i_fio').text() + ' ' + $(this).find('rec_o_fio').text() + ' (' + $(this).find('rec_id').text() + ')</span>';
						if($(this).find('rec_birthday').text()){
							TempContentRec+='<br><span class="birthday" title="День рождения">' + $(this).find('rec_birthday').text() + '</span>';
						}
						if($(this).find('rec_snils').text()){
							TempContentRec+='<br><span class="snils" title="СНИЛС">' + $(this).find('rec_snils').text() + '</span>';
						}
						TempContentRec+='<br><font size="1">Создано: '+$(this).find('rec_created_at').text()+', обновлено: '+$(this).find('rec_updated_at').text()+'.</font>';
						TempContentRec+='</div>';
						TempContentRec+='</div>';
						if(FLAG == 0){
							TempRecsNum++;
							TempContent+=TempContentRec;
						}
					});
				}

				if(TempRecsNum == 0){
					TempContent='<div class="message">По данному запросу не найдено записей.</div>';
				}
				document.getElementById('ListTempRecs').innerHTML=TempContent;
			}
		}
	});
}

function ListShowFoundPetAds(){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#form_search_found_pets_ads").serialize()+'&action=found_pets_ads',
		'url': "index.php",
		'beforeSend': function() {
			//показать загрузку
			showLoading('ListFoundPetAds');
		},
		'success': function (Recs) {
			if($(Recs).find('message').text() == 'token_invalid'){
				window.location.href = 'index.php';
			}else{
				let TempContent='';
				let TempRecsNum=0;

				if($(Recs).find('rec').text()){
					$(Recs).find('rec').each(function(){
						let FLAG=0;
						let Id=$(this).find('rec_id').text();

						TempContentRec='<div class="rec col-12 main_row row">';
						//
						TempContentRec+='<div class="col-xl-4 col-lg-4 col-md-12 col-sm-12 col-xs-12 col-12">';
						
						if($(this).find('rec_type').text() == 'L'){
							TempContentRec+='Потеряно животное ';
						}else{
							TempContentRec+='Найдено животное ';
						}

						if($(this).find('rec_sex').text() == '1'){
							TempContentRec+='♂ ';
						}else if($(this).find('rec_sex').text() == '2'){
							TempContentRec+='♀ ';
						}

						if($(this).find('rec_animal_name').text()){
							TempContentRec+='<strong>'+$(this).find('rec_animal_name').text()+'</strong>';
						}
						if($(this).find('rec_chip').text()){
							TempContentRec+='<br><font size="1">Чип: <strong>'+$(this).find('rec_chip').text()+'</strong></font>';
						}
						if($(this).find('rec_stamp').text()){
							TempContentRec+='<br><font size="1">Клеймо или татуировка: <strong>'+$(this).find('rec_stamp').text()+'</strong></font>';
						}
						if($(this).find('rec_age').text()){
							TempContentRec+='<br><font size="1">Возраст: <strong>'+$(this).find('rec_age').text()+'</strong> лет</font>';
						}

						TempContentRec+='<br>'+$(this).find('rec_date_event').text()+'';
						if($(this).find('rec_time_event').text()){
							TempContentRec+=' в '+$(this).find('rec_time_event').text()+'';
						}

						TempContentRec+='<br>S/N: '+$(this).find('rec_service_number').text()+'';

						TempContentRec+='<br><font size="1">Создано: '+$(this).find('rec_created_at').text()+', обновлено: '+$(this).find('rec_updated_at').text()+'.</font>';
						

						TempContentRec+='</div>';

						
						TempContentRec+='<div class="col-xl-4 col-lg-4 col-md-12 col-sm-12 col-xs-12 col-12">';
						TempContentRec+=''+$(this).find('rec_notice').text()+'';
						TempContentRec+='</div>';

						TempContentRec+='<div class="col-xl-4 col-lg-4 col-md-12 col-sm-12 col-xs-12 col-12">';
						if($(this).find('rec_closed_by').text() == 'AUTHOR'){
							TempContentRec+='Закрыто пользователем';
						}else if($(this).find('rec_closed_by').text() == 'MODERATOR'){
							TempContentRec+='Закрыто модератором';
						}else if($(this).find('rec_closed_by').text() == 'AUTO'){
							TempContentRec+='Истёк срок публикации';
						}else if($(this).find('rec_closed_by').text() == 'AUTO_CENSOR'){
							TempContentRec+='Объявление удалено по причине несоответствия правилам предоставления электронного сервиса';
						}
						TempContentRec+='</div>';

						//
						TempContentRec+='</div>';
						

						if(FLAG == 0){
							TempRecsNum++;
							TempContent+=TempContentRec;
						}
					});
				}

				if(TempRecsNum == 0){
					TempContent='<div class="message">По данному запросу не найдено записей.</div>';
				}
				document.getElementById('ListFoundPetAds').innerHTML=TempContent;
			}
		}
	});
}

function ListShowRecoveryPasswordRecs(Page = 1, Sort = null, Direction = 0){

	if(Direction != null){
		if(currentSort == Sort && currentPage == Page){
			if(currentDirection == 1){currentDirection = 0;}else{currentDirection = 1;}
		}
	}
	if((currentSort == '' && Sort != null) || (currentSort != Sort)){currentSort = Sort;}
	if((currentPage == '' && Page != null) || (currentPage != Page)){currentPage = Page;}

	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#form_search_recs").serialize()+'&page='+Page+'&sort='+Sort+'&direction='+currentDirection+'&action=recovery_password_recs&mode=xml',
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
					TempContent+='<th><div id="list_status" class="sort" onclick="ListShowRecoveryPasswordRecs('+Page+', \'status\');">Статус</div></th>';
					TempContent+='<th><div id="list_name" class="sort" onclick="ListShowRecoveryPasswordRecs('+Page+', \'name\');">ФИО</div></th>';
					TempContent+='<th><div id="list_login" class="sort" onclick="ListShowRecoveryPasswordRecs('+Page+', \'login\');">Логин</div></th>';
					TempContent+='<th><div id="list_date" class="sort" onclick="ListShowRecoveryPasswordRecs('+Page+', \'date\');">Дата</div></th>';
					TempContent+='<th></th>';
					TempContent+='<th></th>';
					TempContent+='<th></th>';
					TempContent+='</tr>';

					$(Recs).find('rec').each(function(){
						let FLAG=0;
						let TempContentRec='<tr>';
						
						if($(this).find('rec_status').text() == 0){
							TempContentRec+='<td class="attention">';
							TempContentRec+='<span>Новый</span>';
						}else if($(this).find('rec_status').text() == 1){
							TempContentRec+='<td class="normal">';
							TempContentRec+='<span>Обработано</span>';
						}else if($(this).find('rec_status').text() == 2){
							TempContentRec+='<td class="warning">';
							TempContentRec+='<span>Отклонено</span>';
						}
						TempContentRec+='</td>';
						TempContentRec+='<td>' + $(this).find('rec_fullname').text() + '</td>';
						TempContentRec+='<td>' + $(this).find('rec_login').text() + '</td>';
						TempContentRec+='<td>' + $(this).find('rec_date').text() + '</td>';
						TempContentRec+='<td style="width: 32px;" onclick="showRecoveryPasswordRecWindow(' + $(this).find('rec_id').text() + ');"><div class="edit"></div></td>';
						TempContentRec+='<td style="width: 32px;" title="Обработано" onclick="saveRecoveryPasswordRec(' + $(this).find('rec_id').text() + ', 1);"><div class="icon normal"></div></td>';
						TempContentRec+='<td style="width: 32px;" title="Отклонено" onclick="saveRecoveryPasswordRec(' + $(this).find('rec_id').text() + ', 2);"><div class="icon warning"></div></td>';
						TempContentRec+='</tr>';

						if(FLAG == 0){
							TempRecsNum++;
							TempContent+=TempContentRec;
						}
					});
					TempContent+='</table>';


					TempContent+='<div class="pages">';
					if(parseInt($(Recs).find('counter').text()) > parseInt($(Recs).find('to').text())){
						TempContent+='<div class="info">'+$(Recs).find('from').text()+' - '+$(Recs).find('to').text()+' из '+$(Recs).find('counter').text()+' записей</div>';
					}else{
						TempContent+='<div class="info">'+$(Recs).find('from').text()+' - '+$(Recs).find('counter').text()+' из '+$(Recs).find('counter').text()+' записей</div>';
					}
					if(parseInt($(Recs).find('current').text()) > 1){
						TempContent+='<div class="first" onclick="ListShowRecoveryPasswordRecs(1,\''+$(Recs).find('sort').text()+'\')")"></div>';
						TempContent+='<div class="previous" onclick="ListShowRecoveryPasswordRecs('+(parseInt($(Recs).find('current').text())-1)+',\''+$(Recs).find('sort').text()+'\')")"></div>';
					}

					//информер
					$("#all").html($(Recs).find('counter').text());
					//информер

					//
					if($(Recs).find('current').text() > 3){
						for(let i=parseInt($(Recs).find('current').text())-2;i<=parseInt($(Recs).find('current').text())+2;i++){
							if(i <= (parseInt($(Recs).find('last').text())+1)){
								TempContent+='<div ';
								if($(Recs).find('current').text() == i){
									TempContent+='class="current" ';
								}
								TempContent+='onclick="ListShowRecoveryPasswordRecs('+i+',\''+$(Recs).find('sort').text()+'\')")">' + i + '</div>';
							}
						}
					}else{
						for(let i=1;i<=5;i++){
							if(i <= (parseInt($(Recs).find('last').text())+1)){
								TempContent+='<div ';
								if($(Recs).find('current').text() == i){
									TempContent+='class="current" ';
								}
								TempContent+='onclick="ListShowRecoveryPasswordRecs('+i+',\''+$(Recs).find('sort').text()+'\')">' + i + '</div>';
							}
						}
					}

					if(parseInt($(Recs).find('current').text()) < (parseInt($(Recs).find('last').text())+1)){
						TempContent+='<div class="next" onclick="ListShowRecoveryPasswordRecs('+(parseInt($(Recs).find('current').text())+1)+',\''+$(Recs).find('sort').text()+'\')")"></div>';
						TempContent+='<div class="last" onclick="ListShowRecoveryPasswordRecs('+(parseInt($(Recs).find('last').text())+1)+',\''+$(Recs).find('sort').text()+'\')")"></div>';
					}

					TempContent+='</div>';

				}

				if(TempRecsNum == 0){
					TempContent='<div class="message">По данному запросу не найдено записей.</div>';
				}

				document.getElementById('ListRecs').innerHTML=TempContent;


				//
				if($(Recs).find('direction').text() == '1'){
					currentDirection=1;
					$('#list_'+$(Recs).find('sort').text()).addClass('down');
				}else{
					currentDirection=0;
					$('#list_'+$(Recs).find('sort').text()).addClass('up');
				}
				if(Sort == null && $(Recs).find('sort').text() != ''){
					currentSort = $(Recs).find('sort').text();
				}
				//
			}
		}
	});
}
function showRecoveryPasswordRecWindow(Id = null){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': '&id='+Id+'&mode=xml&action=recovery_password_rec',
		'url': "index.php",
		'beforeSend': function() {
			//показать загрузку
			showTopLoading();
		},
		'success': function (Data) {
			closeTopLoading();
			let TempContent='';

			TempContent+='<div id="recovery_password_rec_window" class="window main_row col-xl-3 col-lg-4 col-12">';
			TempContent+='<div class="close" onclick="closeRecoveryPasswordRecWindow();"></div>';
			if(Id){
				TempContent+='<div class="top">Редактировать запрос</div>';
			}else{
				TempContent+='<div class="top">Добавить запрос</div>';
			}
					
			TempContent+='<div class="w-100" id="window_recovery_password_rec" style="overflow-y: auto;">';
			TempContent+='<form class="form-window" id="form_recovery_password_rec">';
			TempContent+='<div class="col-12 main_row row">';

			TempContent+='<div class="col-12 error" id="recovery_password_rec_window_message" style="margin-bottom: 15px; display: none;"></div>';

			TempContent+='<div class="col-3 row-center-align">Логин:</div>';
			TempContent+='<div class="col-9" style="margin-top: 10px;">';
			TempContent+='<strong>'+$(Data).find('rec_login').text()+'</strong>';
			TempContent+='</div>';
			
			TempContent+='<div class="col-3 row-center-align">Статус:</div>';
			TempContent+='<div class="col-9" style="margin-top: 10px;">';

			let status_array = new Array();
			TempContent+='<select class="status" name="status">';
			status_array.push({id: 0, title: 'Новый'});
			status_array.push({id: 1, title: 'Обработано'});
			status_array.push({id: 2, title: 'Отклонено'});
			for(let k=0; k<=status_array.length-1; k++){
				if($(Data).find('rec_status').text() == status_array[k].id){
					TempContent+='<option selected value="'+status_array[k].id+'">'+status_array[k].title+'</option>';
				}else{
					TempContent+='<option value="'+status_array[k].id+'">'+status_array[k].title+'</option>';
				}
			}
			TempContent+='</select>';
			TempContent+='</div>';

			TempContent+='<input type="hidden" name="id" id="id" value="'+Id+'">';
			//
			
			
			TempContent+='</div>';
			TempContent+='</form>';
			
					
			//Кнопки управления
			TempContent+='<div class="controls">';
			if(Id){
				TempContent+='<button class="button" id="recovery_password_rec" style="width: 220px;" onclick="saveRecoveryPasswordRec();">Сохранить</button>';
			}else{
				TempContent+='<button class="button" id="recovery_password_rec" style="width: 220px;" onclick="saveRecoveryPasswordRec();">Создать</button>';
			}
			TempContent+='</div>';
			//Кнопки управления

			TempContent+='</div>';

			document.getElementById("sub_container").innerHTML=TempContent;
			$('.services').multiselect({enableClickableOptGroups: true, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '100%', maxHeight: 300, enableFiltering: true, enableCaseInsensitiveFiltering : true, selectAllText: 'Выбрать все', includeSelectAllOption: true, filterPlaceholder: 'Выбрать услугу...'});
			$("#background").fadeIn();
			$("#container").show();
		}
	});
}
function saveRecoveryPasswordRec(Id = null, Status = null){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'POST',
		'dataType': 'xml',
		'data': jQuery("#form_recovery_password_rec").serialize()+'&id_='+Id+'&status_='+Status+'&action=save_recovery_password_rec&mode=xml',
		'url': "index.php",
		'beforeSend': function() {
			//показать загрузку
			showTopLoading();
		},
		'success': function (Data) {
			if($(Data).find('message').text() == 'token_invalid'){
				window.location.href = 'index.php';
			}else{
				if($(Data).find('message').text() == 'recovery_password_rec_saved'){
					closeTopLoading();
					closeRecoveryPasswordRecWindow();
					console.log(currentPage+', '+currentSort+', '+currentDirection);
					ListShowRecoveryPasswordRecs(currentPage, currentSort, null);
				}
			}
		}
	});
}
function closeRecoveryPasswordRecWindow(){
	$("#background").fadeOut();
	$("#container").hide();
}


function ListShowFoundPetMessages(){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#form_search_found_pets_messages").serialize()+'&action=found_pets_messages',
		'url': "index.php",
		'beforeSend': function() {
			//показать загрузку
			showLoading('ListFoundPetMessages');
		},
		'success': function (Recs) {
			if($(Recs).find('message').text() == 'token_invalid'){
				window.location.href = 'index.php';
			}else{
				let TempContent='';
				let TempRecsNum=0;

				if($(Recs).find('rec').text()){
					$(Recs).find('rec').each(function(){
						let FLAG=0;
						let Id=$(this).find('rec_id').text();

						TempContentRec='<div class="rec col-12 main_row row">';
						//
						TempContentRec+='<div class="col-xl-4 col-lg-4 col-md-12 col-sm-12 col-xs-12 col-12">';
						TempContentRec+='<strong>'+$(this).find('rec_type').text()+'</strong>';
						

						TempContentRec+='<br>S/N: '+$(this).find('rec_service_number').text()+'';

						if($(this).find('rec_is_success').text()){
							TempContentRec+='<br><font size="1">Успешно: '+$(this).find('rec_is_success').text()+'</font>';
						}
						if($(this).find('rec_ad_errors').text()){
							TempContentRec+='<br><font size="1">Ad errors:'+$(this).find('rec_ad_errors').text()+'</font>';
						}

						TempContentRec+='<br><font size="1">Создано: '+$(this).find('rec_created_at').text()+', обновлено: '+$(this).find('rec_updated_at').text()+'</font>';
						TempContentRec+='</div>';
						
						TempContentRec+='<div class="col-xl-4 col-lg-4 col-md-12 col-sm-12 col-xs-12 col-12">';
						TempContentRec+='<font size="1">'+$(this).find('rec_body').text()+'</font>';
						TempContentRec+='</div>';

						TempContentRec+='<div class="col-xl-4 col-lg-4 col-md-12 col-sm-12 col-xs-12 col-12">';
						TempContentRec+='<font size="1">'+$(this).find('rec_headers').text()+'</font>';
						TempContentRec+='</div>';

						//
						TempContentRec+='</div>';
						

						if(FLAG == 0){
							TempRecsNum++;
							TempContent+=TempContentRec;
						}
					});
				}

				if(TempRecsNum == 0){
					TempContent='<div class="message">По данному запросу не найдено записей.</div>';
				}
				document.getElementById('ListFoundPetMessages').innerHTML=TempContent;
			}
		}
	});
}

function ListShowFoundPetMessagesSent(){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#form_search_found_pets_messages_sent").serialize()+'&action=found_pets_messages_sent',
		'url': "index.php",
		'beforeSend': function() {
			//показать загрузку
			showLoading('ListFoundPetMessagesSent');
		},
		'success': function (Recs) {
			if($(Recs).find('message').text() == 'token_invalid'){
				window.location.href = 'index.php';
			}else{
				let TempContent='';
				let TempRecsNum=0;

				if($(Recs).find('rec').text()){
					$(Recs).find('rec').each(function(){
						let FLAG=0;
						let Id=$(this).find('rec_id').text();

						TempContentRec='<div class="rec col-12 main_row row">';
						//
						TempContentRec+='<div class="col-xl-4 col-lg-4 col-md-12 col-sm-12 col-xs-12 col-12">';
						TempContentRec+='<strong>'+$(this).find('rec_type').text()+'</strong>';
						

						TempContentRec+='<br>S/N: '+$(this).find('rec_service_number').text()+'';

						if($(this).find('rec_curl_error').text()){
							TempContentRec+='<br><font size="1">CURL error: '+$(this).find('rec_curl_error').text()+'</font>';
						}
						if($(this).find('rec_user_error').text()){
							TempContentRec+='<br><font size="1">User error:'+$(this).find('rec_user_error').text()+'</font>';
						}

						TempContentRec+='<br><font size="1">Создано: '+$(this).find('rec_created_at').text()+'.</font>';
						

						TempContentRec+='</div>';

						
						TempContentRec+='<div class="col-xl-4 col-lg-4 col-md-12 col-sm-12 col-xs-12 col-12">';
						TempContentRec+='<font size="1">'+$(this).find('rec_request').text()+'</font>';
						TempContentRec+='</div>';

						TempContentRec+='<div class="col-xl-4 col-lg-4 col-md-12 col-sm-12 col-xs-12 col-12">';
						TempContentRec+='<strong>'+$(this).find('rec_response_code').text()+'</strong><br>';
						TempContentRec+='<font size="1">'+$(this).find('rec_response').text()+'</font>';

						TempContentRec+='<br><br><font size="1">'+$(this).find('rec_response_headers').text()+'</font>';
						TempContentRec+='</div>';

						//
						TempContentRec+='</div>';
						

						if(FLAG == 0){
							TempRecsNum++;
							TempContent+=TempContentRec;
						}
					});
				}

				if(TempRecsNum == 0){
					TempContent='<div class="message">По данному запросу не найдено записей.</div>';
				}
				document.getElementById('ListFoundPetMessagesSent').innerHTML=TempContent;
			}
		}
	});
}

function ListShowMosruMessages(){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#form_search_messages").serialize()+'&action=mosru_messages',
		'url': "index.php",
		'beforeSend': function() {
			//показать загрузку
			showLoading('ListMessages');
		},
		'success': function (Recs) {
			if($(Recs).find('message').text() == 'token_invalid'){
				window.location.href = 'index.php';
			}else{
				let TempContent='';
				let TempRecsNum=0;

				// TempContent='<div class="row">';
				// TempContent+='<div class="col-12"><button style="margin-bottom: 20px;" onclick="cancelVisits();"type="submit">Отменить выбранные</button></div>';
				// TempContent+='</div>';

				if($(Recs).find('rec').text()){
					$(Recs).find('rec').each(function(){
						let FLAG=0;
						let Id=$(this).find('rec_id').text();

						TempContentRec='<div class="rec col-12 main_row row">';
						//
						TempContentRec+='<div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-xs-12 col-12">';
						//TempContentRec+='<input type="checkbox" class="for_cancel_visits" visit_id="' + $(this).find('rec_visit_id').text() + '" id="' + $(this).find('rec_visit_id').text() + '" name="for_cancel_visits" /> <label for="' + $(this).find('rec_visit_id').text() + '">Выбрать</label><br/>';
						TempContentRec+='<strong>Приём № '+$(this).find('rec_visit_id').text()+'</strong>';
						TempContentRec+='<br>'+$(this).find('rec_first_name').text()+' '+$(this).find('rec_middle_name').text()+' '+$(this).find('rec_last_name').text()+'';
						TempContentRec+='<br>'+$(this).find('rec_phone').text()+', '+$(this).find('rec_email').text()+'';

						TempContentRec+='<br>S/N: '+$(this).find('rec_service_number').text()+'<br>SSO ID: '+$(this).find('rec_sso_id').text()+'';
						TempContentRec+='<br>'+$(this).find('rec_system_id').text()+'<br>'+$(this).find('rec_message_id').text()+'';

						TempContentRec+='<br><font size="1">Создано: '+$(this).find('rec_created_at').text()+', обновлено: '+$(this).find('rec_updated_at').text()+'.</font>';
						

						TempContentRec+='</div>';

						
						TempContentRec+='<div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-xs-12 col-12">';
						TempContentRec+=''+$(this).find('rec_message').text()+'';
						TempContentRec+='</div>';
						//
						TempContentRec+='</div>';
						

						if(FLAG == 0){
							TempRecsNum++;
							TempContent+=TempContentRec;
						}
					});
				}

				if(TempRecsNum == 0){
					TempContent='<div class="message">По данному запросу не найдено записей.</div>';
				}
				document.getElementById('ListMessages').innerHTML=TempContent;
			}
		}
	});
}

function ListShowMosruPets(){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#form_search_pets").serialize()+'&action=mosru_pets',
		'url': "index.php",
		'beforeSend': function() {
			//показать загрузку
			showLoading('ListPets');
		},
		'success': function (Recs) {
			if($(Recs).find('message').text() == 'token_invalid'){
				window.location.href = 'index.php';
			}else{
				let TempContent='';
				let TempRecsNum=0;

				if($(Recs).find('rec').text()){
					$(Recs).find('rec').each(function(){
						let FLAG=0;
						let Id=$(this).find('rec_id').text();

						TempContentRec='<div class="rec col-12 main_row row">';
						//
						TempContentRec+='<div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-xs-12 col-12">';
						
						TempContentRec+='<strong>PET ID '+$(this).find('rec_ext_id').text()+', ID животного '+$(this).find('rec_id_pet').text()+'</strong>';
						

						TempContentRec+='<br><font size="1">Создано: '+$(this).find('rec_created_at').text()+', обновлено: '+$(this).find('rec_updated_at').text()+'.</font>';
						

						TempContentRec+='</div>';

						
						TempContentRec+='<div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-xs-12 col-12">';
						TempContentRec+='<strong>'+$(this).find('rec_name').text()+'</strong><br>';
						TempContentRec+='Чип: '+$(this).find('rec_chip').text()+'<br>';
						TempContentRec+='ID владельца: '+$(this).find('rec_id_pet_owner').text()+'<br>';
						TempContentRec+='ID владельца (ЕЛК): '+$(this).find('rec_id_elk_owner').text()+'';
						TempContentRec+='</div>';
						//
						TempContentRec+='</div>';
						

						if(FLAG == 0){
							TempRecsNum++;
							TempContent+=TempContentRec;
						}
					});
				}

				if(TempRecsNum == 0){
					TempContent='<div class="message">По данному запросу не найдено записей.</div>';
				}
				document.getElementById('ListPets').innerHTML=TempContent;
			}
		}
	});
}

function ListShowMosruOwners(){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#form_search_owners").serialize()+'&action=mosru_owners',
		'url': "index.php",
		'beforeSend': function() {
			//показать загрузку
			showLoading('ListOwners');
		},
		'success': function (Recs) {
			if($(Recs).find('message').text() == 'token_invalid'){
				window.location.href = 'index.php';
			}else{
				let TempContent='';
				let TempRecsNum=0;

				if($(Recs).find('rec').text()){
					$(Recs).find('rec').each(function(){
						let FLAG=0;
						let Id=$(this).find('rec_id').text();

						TempContentRec='<div class="rec col-12 main_row row">';
						//
						TempContentRec+='<div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-xs-12 col-12">';
						
						TempContentRec+='<strong>SSO ID '+$(this).find('rec_sso_id').text()+', ID владельца '+$(this).find('rec_id_owner').text()+'</strong>';
						

						TempContentRec+='<br><font size="1">Создано: '+$(this).find('rec_created_at').text()+', обновлено: '+$(this).find('rec_updated_at').text()+'.</font>';
						

						TempContentRec+='</div>';

						
						TempContentRec+='<div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-xs-12 col-12">';
						TempContentRec+='<strong>'+$(this).find('rec_last_name').text()+' '+$(this).find('rec_first_name').text()+' '+$(this).find('rec_middle_name').text()+'</strong><br>';
						TempContentRec+=''+$(this).find('rec_phone').text()+'<br>'+$(this).find('rec_email').text()+'<br>СНИЛС: '+$(this).find('rec_snils').text()+'';
						TempContentRec+='</div>';
						//
						TempContentRec+='</div>';
						

						if(FLAG == 0){
							TempRecsNum++;
							TempContent+=TempContentRec;
						}
					});
				}

				if(TempRecsNum == 0){
					TempContent='<div class="message">По данному запросу не найдено записей.</div>';
				}
				document.getElementById('ListOwners').innerHTML=TempContent;
			}
		}
	});
}

function ListShowMosruLogs(){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#form_search_logs").serialize()+'&action=mosru_logs',
		'url': "index.php",
		'beforeSend': function() {
			//показать загрузку
			showLoading('ListLogs');
		},
		'success': function (Recs) {
			if($(Recs).find('message').text() == 'token_invalid'){
				window.location.href = 'index.php';
			}else{
				let TempContent='';
				let TempRecsNum=0;

				if($(Recs).find('rec').text()){
					$(Recs).find('rec').each(function(){
						let FLAG=0;
						let Id=$(this).find('rec_id').text();

						let TempContentRec='';
						TempContentRec='<div class="rec col-12 main_row row">';
						//
						TempContentRec+='<div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-xs-12 col-12">';
						
						TempContentRec+='<strong>Приём № '+$(this).find('rec_visit_id').text()+'</strong>';
						TempContentRec+='<br>S/N: '+$(this).find('rec_service_number').text()+'';
						TempContentRec+='<br>'+$(this).find('rec_system_id').text()+'<br>'+$(this).find('rec_message_id').text()+'';
						TempContentRec+='<br><font size="1">Время: '+$(this).find('rec_log_time').text()+'.</font>';
						TempContentRec+='</div>';
						
						TempContentRec+='<div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-xs-12 col-12">';
						TempContentRec+='<strong>'+$(this).find('rec_etp_status').text()+'</strong> (';

						if($(this).find('rec_etp_status').text() == '1050'){
							TempContentRec+='Запись произведена';
						}
						if($(this).find('rec_etp_status').text() == '1050.2'){
							TempContentRec+='Произведена запись на онлайн-консультацию';
						}
						
						if($(this).find('rec_etp_status').text() == '8031.1'){
							TempContentRec+='Оплата произведена';
						}
						if($(this).find('rec_etp_status').text() == '8031.2'){
							TempContentRec+='Истек срок оплаты';
						}
						if($(this).find('rec_etp_status').text() == '8031.3'){
							TempContentRec+='Ввод реквизитов недоступен';
						}
						if($(this).find('rec_etp_status').text() == '8031.4'){
							TempContentRec+='Реквизиты получены';
						}

						if($(this).find('rec_etp_status').text() == '10090'){
							TempContentRec+='Отзыв заявления возможен';
						}
						if($(this).find('rec_etp_status').text() == '10190'){
							TempContentRec+='Отзыв заявления невозможен';
						}
						if($(this).find('rec_etp_status').text() == '10191'){
							TempContentRec+='Изменение заявления невозможно';
						}
						if($(this).find('rec_etp_status').text() == '10091'){
							TempContentRec+='Изменение заявления возможно';
						}
						if($(this).find('rec_etp_status').text() == '8021'){
							TempContentRec+='Запись перенесена по инициативе клиники';
						}
						if($(this).find('rec_etp_status').text() == '1053'){
							TempContentRec+='Запись перенесена по инициативе заявителя';
						}
						if($(this).find('rec_etp_status').text() == '1075'){
							TempContentRec+='Заявитель явился на прием';
						}
						if($(this).find('rec_etp_status').text() == '10801'){
							TempContentRec+='Запись отменена по инициативе ветеринарной клиники';
						}
						if($(this).find('rec_etp_status').text() == '10802'){
							TempContentRec+='Технический статус';
						}
						if($(this).find('rec_etp_status').text() == '1090'){
							TempContentRec+='Заявление отозвано';
						}
						TempContentRec+=')<br>';

						TempContentRec+=''+$(this).find('rec_message').text()+'';
						TempContentRec+='</div>';
						//
						TempContentRec+='</div>';

						if(FLAG == 0){
							TempRecsNum++;
							TempContent+=TempContentRec;
						}
					});
				}

				if(TempRecsNum == 0){
					TempContent='<div class="message">По данному запросу не найдено записей.</div>';
				}
				document.getElementById('ListLogs').innerHTML=TempContent;
			}
		}
	});
}

function ListShowMosruISPKLogs(){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#form_search_ispk_logs").serialize()+'&action=mosru_ispk_logs',
		'url': "index.php",
		'beforeSend': function() {
			//показать загрузку
			showLoading('ListMosruISPKLogs');
		},
		'success': function (Recs) {
			if($(Recs).find('message').text() == 'token_invalid'){
				window.location.href = 'index.php';
			}else{
				let TempContent='';
				let TempRecsNum=0;

				if($(Recs).find('rec').text()){
					$(Recs).find('rec').each(function(){
						let FLAG=0;
						let Id=$(this).find('rec_id').text();

						let TempContentRec='';
						TempContentRec='<div class="rec col-12 main_row row">';
						//
						TempContentRec+='<div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-xs-12 col-12">';
						
						TempContentRec+='<strong>ID '+$(this).find('rec_event_id').text()+'</strong>';
						TempContentRec+='<br>Код: '+$(this).find('rec_event_code').text()+'';
						TempContentRec+='<br>'+$(this).find('rec_system_id').text()+'<br>'+$(this).find('rec_message_id').text()+'';
						TempContentRec+='<br><font size="1">Время: '+$(this).find('rec_log_time').text()+'.</font>';
						TempContentRec+='</div>';
						
						TempContentRec+='<div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-xs-12 col-12">';
						TempContentRec+='Статус: ';
						if($(this).find('rec_is_success').text() == 'f'){
							TempContentRec+='неуспешно (' + $(this).find('rec_error').text() + ')';
						}else{
							TempContentRec+='успешно';
						}
						TempContentRec+='</div>';
						//
						TempContentRec+='</div>';

						if(FLAG == 0){
							TempRecsNum++;
							TempContent+=TempContentRec;
						}
					});
				}

				if(TempRecsNum == 0){
					TempContent='<div class="message">По данному запросу не найдено записей.</div>';
				}
				document.getElementById('ListMosruISPKLogs').innerHTML=TempContent;
			}
		}
	});
}

function ListShowVisitsRecs(){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#form_search_recs").serialize()+'&action=visits',
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
					$(Recs).find('rec').each(function(){
						let FLAG=0;
						let Id=$(this).find('rec_id').text();


						TempContentRec='<div class="rec col-12 main_row row">';
						TempContentRec+='<div class="col-xl-3 col-lg-3 col-md-12 col-sm-12 col-xs-12 col-12">';
						TempContentRec+='<strong>Приём № '+$(this).find('rec_id').text()+'</strong><br>';

						TempContentRec+='Канал: <strong>';
						if($(this).find('rec_channel').text() == '1'){
							TempContentRec+='По направлению';
						}else if($(this).find('rec_channel').text() == '2'){
							TempContentRec+='mos.ru';
						}else if($(this).find('rec_channel').text() == '3'){
							TempContentRec+='Запись по телефону';
						}else if($(this).find('rec_channel').text() == '4'){
							TempContentRec+='Живая очередь';
						}else if($(this).find('rec_channel').text() == '10'){
							TempContentRec+='Неотложная помощь';
						}else{
							TempContentRec+='Не известен';
						}
						TempContentRec+='</strong><br />';

						TempContentRec+='Статус: <strong>';
						if($(this).find('rec_status').text() == 'N'){
							TempContentRec+='Новый';
						}else if($(this).find('rec_status').text() == 'F'){
							TempContentRec+='Завершен';
						}else if($(this).find('rec_status').text() == 'O'){
							TempContentRec+='Завершен (неоплачен)';
						}else if($(this).find('rec_status').text() == 'A'){
							TempContentRec+='Отменен';
						}else if($(this).find('rec_status').text() == 'W'){
							TempContentRec+='В работе';
						}else if($(this).find('rec_status').text() == 'T'){
							TempContentRec+='Перенесен';
						}else if($(this).find('rec_status').text() == 'C'){
							TempContentRec+='Изменен';
						}else if($(this).find('rec_status').text() == 'D'){
							TempContentRec+='Пациент не явился';
						}else if($(this).find('rec_status').text() == 'B'){
							TempContentRec+='Бронирован';
						}
						TempContentRec+='</strong><br />';

						if($(this).find('rec_start_date').text()){
							let date = new Date($(this).find('rec_start_date').text());
							TempContentRec+='<span class="date">' + date.toLocaleString('ru', {year: 'numeric', month: 'long', day: 'numeric'}) + '';
							TempContentRec+=' с '+$(this).find('rec_start_time').text()+' по '+$(this).find('rec_end_time').text();
							TempContentRec+='</span><br>';
						}

						TempContentRec+='<span class="title">'+$(this).find('rec_specialist_name').text()+'</span><br>';
						TempContentRec+='<span class="org_name">' + $(this).find('rec_org_name').text() + '</span><br>';
						if($(this).find('rec_org_address').text()){
							TempContentRec+='<span class="org_area">(' + $(this).find('rec_org_address').text() + ')</span>';
						}

						TempContentRec+='<br><a href="index.php?action=visits_logs&id='+Id+'">Логи приёма</a>';
						TempContentRec+='</div>';

						TempContentRec+='<div class="col-xl-3 col-lg-3 col-md-12 col-sm-12 col-xs-12 col-12 contacts">';
						TempContentRec+='<span class="title">'+$(this).find('rec_owner_name').text()+'</span>';

						if($(this).find('rec_contacts').text()){
							TempContentRec+='<h3>Контакты</h3>';
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

						let i=0;
						$(this).find('rec_pets').find('pet').each(function(){
							TempContentRec+='<h3>Животные</h3>';
							TempContentRec+='<div class="d-table w-100 p-1">';
							TempContentRec+='<a href="https://vetas.mos.ru/animals/' + $(this).find('id').text() + '/edit/main-info/"><strong>' + $(this).find('name').text() + '</strong></a> ('+$(this).find('id').text()+')';
							TempContentRec+='</div>';
							i++;
						});

						TempContentRec+='</div>';

						TempContentRec+='<div class="col-xl-3 col-lg-3 col-md-12 col-sm-12 col-xs-12 col-12">';
						TempContentRec+='Услуги приёма: ';
						$(this).find('rec_services').find('service').each(function(){
							TempContentRec+='<div class="service_item">' + $(this).find('name').text() + '</div>';
						});
						TempContentRec+='</div>';

						
						TempContentRec+='<div class="col-xl-3 col-lg-3 col-md-12 col-sm-12 col-xs-12 col-12" style="text-align: right;">';
						if($(this).find('rec_status').text() == 'N' || $(this).find('rec_status').text() == 'W'){
							TempContentRec+='<button class="button mini" onclick="closeVisit(' + Id + ');" id="close_visit_'+Id+'">Закрыть приём</button>';
							TempContentRec+='<button class="button mini" onclick="cancelSVisit(' + Id + ');" id="cancel_visit_'+Id+'" style="margin-left: 10px;">Отменить приём</button>';
						}else if($(this).find('rec_status').text() == 'F' || $(this).find('rec_status').text() == 'A' || $(this).find('rec_status').text() == 'O'){
							TempContentRec+='<button class="button mini" onclick="workVisit(' + Id + ');" id="work_visit_'+Id+'">Вернуть приём в работу</button>';
						}

						if($(this).find('rec_paid').text() == 't'){
							TempContentRec+='<button class="button mini" onclick="cancelVisitPaid(' + Id + ');" id="cancel_visit_paid_'+Id+'" style="margin-left: 10px;">Отменить оплату</button>';
						}
						
						TempContentRec+='</div>';

						TempContentRec+='</div>';						

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

function ListShowVisitsLogsRecs(){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#form_search_recs").serialize()+'&action=visits_logs',
		'url': "index.php",
		'beforeSend': function() {
			//показать загрузку
			showLoading('ListPets');
		},
		'success': function (Recs) {
			if($(Recs).find('message').text() == 'token_invalid'){
				window.location.href = 'index.php';
			}else{
				let TempContent='';
				let TempRecsNum=0;

				if($(Recs).find('rec').text()){
					$(Recs).find('rec').each(function(){
						let FLAG=0;
						let Id=$(this).find('rec_id').text();

						TempContentRec='<div class="rec col-12 main_row row">';
						TempContentRec+='<div class="col-xl-3 col-lg-3 col-md-12 col-sm-12 col-xs-12 col-12">';
						TempContentRec+='<strong>Приём № '+$(this).find('rec_id_visit').text()+'</strong><br>';

						if($(this).find('rec_date').text()){
							let date = new Date($(this).find('rec_date').text());
							TempContentRec+='<span class="date">' + date.toLocaleString('ru', {year: 'numeric', month: 'long', day: 'numeric', hour: 'numeric', minute: 'numeric', second: 'numeric'}) + '';
							TempContentRec+='</span><br>';
						}

						TempContentRec+='<span class="title">';
						if($(this).find('rec_status_visit').text() == 'N'){
							TempContentRec+='Новый';
						}else if($(this).find('rec_status_visit').text() == 'F'){
							TempContentRec+='Завершен';
						}else if($(this).find('rec_status_visit').text() == 'O'){
							TempContentRec+='Завершен (неоплачен)';
						}else if($(this).find('rec_status_visit').text() == 'A'){
							TempContentRec+='Отменен';
						}else if($(this).find('rec_status_visit').text() == 'W'){
							TempContentRec+='В работе';
						}else if($(this).find('rec_status_visit').text() == 'T'){
							TempContentRec+='Перенесен';
						}else if($(this).find('rec_status_visit').text() == 'C'){
							TempContentRec+='Изменен';
						}else if($(this).find('rec_status_visit').text() == 'D'){
							TempContentRec+='Пациент не явился';
						}else if($(this).find('rec_status_visit').text() == 'B'){
							TempContentRec+='Бронирован';
						}
						TempContentRec+='</span>';
						TempContentRec+='</div>';

						TempContentRec+='<div class="col-xl-3 col-lg-3 col-md-12 col-sm-12 col-xs-12 col-12 contacts">';
						TempContentRec+='<span class="title">'+$(this).find('rec_fio_user').text()+' (ID: '+$(this).find('rec_id_user').text()+')</span>';
						TempContentRec+='<br>'+$(this).find('rec_name_organization').text()+' (ID: '+$(this).find('rec_id_organization').text()+')';
						TempContentRec+='</div>';
						
						TempContentRec+='<div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-xs-12 col-12" style="text-align: right;">';
						TempContentRec+='' + $(this).find('rec_snapshot').text() + '';
						TempContentRec+='</div>';

						TempContentRec+='</div>';						

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

function ListShowUsersRecs(){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#form_search_recs").serialize()+'&action=users',
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
					
					$(Recs).find('rec').each(function(){
						let FLAG=0;
						var Id=$(this).find('rec_id').text();

						TempContentRec='<div class="rec col-12 main_row row">';
						TempContentRec+='<div class="col-xl-3 col-lg-3 col-md-12 col-sm-12 col-xs-12 col-12">';

						TempContentRec+='<span class="title">';
						TempContentRec+=''+$(this).find('rec_fullname').text()+'<br />';
						TempContentRec+=''+$(this).find('rec_login').text()+' ['+$(this).find('rec_id').text()+']<br />';
						TempContentRec+='</span>';

						let date = new Date($(this).find('rec_birthday').text());
						TempContentRec+='<span class="birthday">' + date.toLocaleString('ru', {year: 'numeric', month: 'long', day: 'numeric'}) + '</span><br>';
						TempContentRec+='<span class="email">' + $(this).find('rec_email').text() + '</span><br>';

						TempContentRec+='<font size="1">Последний вход: ' + $(this).find('rec_last_login').text() + '</font><br>';
						// TempContentRec+='<font size="1">Последний вход: ' + $(this).find('rec_password_valid_till').text() + '</font><br>';
						// TempContentRec+='<font size="1">Последний вход: ' + $(this).find('rec_password_valid_till_min').text() + '</font><br>';
						// TempContentRec+='<font size="1">Заблокирован до: ' + $(this).find('rec_block_until').text() + '</font><br>';
						if($(this).find('rec_is_blocked').text()){
							TempContentRec+='<font size="1">Блокировка: Да</font><br>';
						}else{
							TempContentRec+='<font size="1">Блокировка: Нет</font><br>';
						}
						
						// TempContentRec+='<font size="1">Последний вход: ' + $(this).find('rec_sex').text() + '</font><br>';
						TempContentRec+='<font size="1">Электронная почта: ' + $(this).find('rec_email').text() + '</font><br>';
						// TempContentRec+='<font size="1">Последний вход: ' + $(this).find('rec_is_deleted').text() + '</font><br>';

						//СПЕЦИАЛИЗАЦИИ
						TempContentRec+='<p style="margin-top: 10px; margin-bottom: 10px;">Специализации: ';

						if($(this).find('rec_specializations').text()){
							TempContentRec+='<div class="specializations">';
							$(this).find('rec_specializations').find('specialization').each(function(){
								TempContentRec+='<div class="item">'+$(this).find('name').text()+'</div>';
							});
							TempContentRec+='</div>';
						}else{
							TempContentRec+='Нет';
						}

						TempContentRec+='<br /><button class="button mini" title="Редактировать специализации" onclick="showUserSpecializationWindow(' + Id + ');">+/-</button>';
						TempContentRec+='</p>';
						//СПЕЦИАЛИЗАЦИИ
						

						TempContentRec+='<br /><button class="button mini" title="Добавить организацию" onclick="showAddOrgWindow(' + Id + ');">Добавить организацию</button>';
						TempContentRec+='</div>';

						TempContentRec+='<div class="col-xl-9 col-lg-9 col-md-12 col-sm-12 col-xs-12 col-12 recs">';
						TempContentRec+='Организации: ';
						$(this).find('rec_specialists').find('specialist').each(function(){
							TempContentRec+='<div class="rec">';
							TempContentRec+='<strong>['+ $(this).find('id').text()+'] '+ $(this).find('name_organization').text()+' ['+ $(this).find('id_organization').text()+']</strong><br />';
							TempContentRec+='<strong>Роли:</strong> ';
							let IdSpec=$(this).find('id').text();
							
							let a=0;
							$(this).find('roles').find('role').each(function(){
								if(a>0){TempContentRec+=', ';}
								TempContentRec+=''+$(this).find('item_name').text()+' <button style="margin-bottom: 5px;" class="button mini" title="Удалить роль" onclick="deleteSpecialistRole('+Id+','+IdSpec+',\''+$(this).find('item_name').text()+'\');">x</button>';
								a++;
							});
							TempContentRec+='<br /><br /><button class="button mini" title="Добавить роль" onclick="showAddRoleWindow('+Id+','+ $(this).find('id').text() + ');">Добавить роль</button>';

							TempContentRec+='<br /><br />';

							TempContentRec+='Принят: '+$(this).find('reg_date').text()+'';
							if($(this).find('expel_date').text()){
								TempContentRec+=', уволен: '+ $(this).find('expel_date').text();
							}
							TempContentRec+='<br />';
							TempContentRec+='Создан: '+ $(this).find('created_at').text()+' ['+ $(this).find('created_by').text()+']<br />';
							TempContentRec+='Обновлен: '+ $(this).find('updated_at').text()+' ['+ $(this).find('updated_by').text()+']';

							if($(this).find('expel_date').text()){
								TempContentRec+='<br /><button class="button mini" title="Вернуть в организацию" onclick="returnSpecialist(' + $(this).find('id').text() + ');">Вернуть в организацию</button><br />';
								TempContentRec+='<br /><button class="button mini" title="Вернуть в организацию" onclick="deleteSpecialist(' + $(this).find('id').text() + ');">Удалить</button><br />';
							}else{
								TempContentRec+='<br /><button class="button mini" title="Уволить" onclick="dismissSpecialist(' + $(this).find('id').text() + ');">Уволить из организации</button><br />';
							}


							TempContentRec+='</div>';
						});
						TempContentRec+='</div>';

						TempContentRec+='</div>';

						

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

function showAddUserWindow(){
	let TempContent='';

	TempContent+='<div id="add_user_window" class="window main_row col-xl-8 col-lg-10 col-12">';
	//
	TempContent+='<div class="close" onclick="closeAddUserWindow();"></div>';

	TempContent+='<div class="top">Добавить пользователя</div>';

	TempContent+='<div class="row main_row control">';
	
	TempContent+='<div class="col-12 required">Логин:</div>';
	TempContent+='<div class="col-12">';
	TempContent+='<input type="text" name="login" id="login" value="">';
	TempContent+='</div>';

	TempContent+='<div class="col-12 required">Пароль:</div>';
	TempContent+='<div class="col-12">';
	TempContent+='<input type="password" name="password" id="password" value="">';
	TempContent+='</div>';

	TempContent+='<div class="col-12 required">Email:</div>';
	TempContent+='<div class="col-12">';
	TempContent+='<input type="text" name="email" id="email" value="">';
	TempContent+='</div>';

	TempContent+='<div class="col-12 required">Фамилия:</div>';
	TempContent+='<div class="col-12">';
	TempContent+='<input type="text" name="surname" id="surname" value="">';
	TempContent+='</div>';

	TempContent+='<div class="col-12 required">Имя:</div>';
	TempContent+='<div class="col-12">';
	TempContent+='<input type="text" name="name" id="name" value="">';
	TempContent+='</div>';

	TempContent+='<div class="col-12 required">Отчество:</div>';
	TempContent+='<div class="col-12">';
	TempContent+='<input type="text" name="secondname" id="secondname" value="">';
	TempContent+='</div>';

	TempContent+='<div class="col-12 required">Пол:</div>';
	TempContent+='<div class="col-12">';
	TempContent+='<select name="gender" id="gender">';
	TempContent+='<option value="m">Мужской</option>';
	TempContent+='<option value="f">Женский</option>';
	TempContent+='</select>';
	TempContent+='</div>';

	TempContent+='<div class="col-12 required">Дата рождения:</div>';
	TempContent+='<div class="col-12">';
	TempContent+='<input type="date" name="birthday" id="birthday" value="" max="2050-01-01" min="1950-01-01">';
	TempContent+='</div>';

	TempContent+='</div>';

	TempContent+='<div class="col-12" style="margin-bottom: 15px;">';
	TempContent+='<button class="appointment" id="add_user" style="margin-top: 15px; width: 200px;" onclick="addUser();">Добавить</button>';
	TempContent+='</div>';

	TempContent+='</div>';
	
	TempContent+='</div>';

	document.getElementById("sub_container").innerHTML=TempContent;
	$("#background").fadeIn();
	$("#container").show();
}

function closeAddUserWindow(){
	$("#background").fadeOut();
	$("#container").hide();
}

function addUser(){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'POST',
		'dataType': 'xml',
		'data': jQuery("#add_user_window :input").serialize()+'&action=add_user&mode=xml',
		'url': "index.php",
		'beforeSend': function() {
			$('#add_user_window #add_user').prop('disabled', true);
		},
		'success': function (Data) {
			if($(Data).find('message').text() == 'user_added'){
				closeAddUserWindow();
				showMessageWindow('Пользователь успешно добавлен.');
				//ListShowUsersRecs();
			}
		}
	});
}

function deleteSpecialistRole(user, specialist, role){
	if(user && specialist && role){
		jQuery.ajax({
			'async': true,
			'global': false,
			'cache': false,
			'type': 'GET',
			'dataType': 'xml',
			'data': '&role='+role+'&specialist='+specialist+'&user='+user+'&action=delete_specialist_role&xml=1',
			'url': "index.php",
			'beforeSend': function() {
				showLoading('ListRecs');
			},
			'success': function (Data) {
				if($(Data).find('message').text() == 'specialist_role_deleted'){
					ListShowUsersRecs();			
				}
			}
		});
	}
}

function showAddRoleWindow(User, Specialist){
	let TempContent='';

	TempContent+='<div id="add_support_role" class="window mini main_row col-xl-8 col-lg-10 col-12">';
	//
	TempContent+='<div class="close" onclick="closeAddRoleWindow();"></div>';

	TempContent+='<div class="top">Добавить роль</div>';

	TempContent+='<div class="row main_row control">';
	
	TempContent+='<div class="col-12">Роль:</div>';
	TempContent+='<div class="col-12">';
	TempContent+='<select name="role" id="role" class="role">';
	for(let i=0; i<=ArrayRolesRecs.length-1;i++){
		TempContent+='<option value="'+ArrayRolesRecs[i].name+'" title="'+ArrayRolesRecs[i].description+'">'+ArrayRolesRecs[i].name+'</option>';
	}
	TempContent+='</select>';
	TempContent+='</div>';

	TempContent+='<div class="col-12">';
	TempContent+='<button class="appointment" id="add_role" style="margin-top: 15px; width: 200px;" onclick="addSpecialistRole();">Добавить роль</button>';
	TempContent+='</div>';

	TempContent+='</div>';

	TempContent+='<input type="hidden" id="user" value="'+User+'">';
	TempContent+='<input type="hidden" id="specialist" value="'+Specialist+'">';
	
	TempContent+='</div>';

	document.getElementById("sub_container").innerHTML=TempContent;
	$("#background").fadeIn();
	$("#container").show();

	$('.role').multiselect({enableClickableOptGroups: true, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '250', maxHeight: 200, enableFiltering: true, enableCaseInsensitiveFiltering : true, selectAllText: 'Выбрать все', includeSelectAllOption: true, filterPlaceholder: 'Выбрать роль...'});
}

function closeAddRoleWindow(){
	$("#background").fadeOut();
	$("#container").hide();
}

function addSpecialistRole(){
	let role=$('#add_support_role #role').val();
	let specialist=$('#add_support_role #specialist').val();
	let user=$('#add_support_role #user').val();

	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': '&role='+role+'&specialist='+specialist+'&user='+user+'&action=add_specialist_role&xml=1',
		'url': "index.php",
		'beforeSend': function() {
			$('.add_role').prop('disabled', true);
		},
		'success': function (Data) {
			if($(Data).find('message').text() == 'specialist_role_added'){
				closeAddRoleWindow();
				showMessageWindow('Роль добавлена.');
				ListShowUsersRecs();
			}
		}
	});
}

function showUserSpecializationWindow(Id){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': '&id='+Id+'&mode=xml&action=user_specializations',
		'url': "index.php",
		'beforeSend': function() {
			//показать загрузку
			showTopLoading();
		},
		'success': function (Data) {
			closeTopLoading();

			///////////////////////
			//считываем специализации

			let TempContent='';
			
			TempContent+='<div id="add_support_org" class="window mini main_row col-xl-8 col-lg-10 col-12">';
			//
			
			TempContent+='<div class="close" onclick="closeUserSpecializationWindow();"></div>';

			TempContent+='<div class="top">Специализации</div>';

			TempContent+='<div class="row main_row control">';

			TempContent+='<form id="form_save_specializations" class="w-100" onsubmit="return false;">';
			TempContent+='<div class="col-12">';
			TempContent+='<textarea name="specializations" id="specializations" class="JxTag"></textarea>';
			TempContent+='</div>';

			TempContent+='<div class="col-12">';
			TempContent+='<button class="appointment" style="margin-top: 15px; width: 200px;" onclick="saveUserSpecializations();">Сохранить</button>';
			TempContent+='</div>';
			TempContent+='<input type="hidden" name="user" id="user" value="'+Id+'">';
			TempContent+='<input type="hidden" name="action" id="action" value="save_specializations">';
			TempContent+='<input type="hidden" name="mode" value="xml">';
			TempContent+='</form>';
			TempContent+='</div>';
			TempContent+='</div>';
			
			document.getElementById("sub_container_m").innerHTML=TempContent;
			$("#background_m").fadeIn();
			$("#container_m").show();

			var options ={
				name: 'specializations',
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
				url: "?action=specializations&mode=xml"
			};
			tags_input = new JxTag(options);

			$(Data).find('specialization').each(function(){
				tags_input.AddTag(''+$(this).find('name').text()+'', {id: $(this).find('id').text()});
			});
			///////////////////////
		}
	});	
}
function closeUserSpecializationWindow(){
	$("#background_m").fadeOut();
	$("#container_m").hide();
}
function saveUserSpecializations(){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'POST',
		'dataType': 'xml',
		'data': jQuery("#form_save_specializations").serialize(),
		'url': "index.php",
		'beforeSend': function() {
			//
		},
		'success': function (Data) {
			if($(Data).find('message').text() == 'specializations_saved'){
				closeUserSpecializationWindow();
				showMessageWindow('Специализации успешно сохранены.');
				ListShowUsersRecs();
			}
		}
	});
}

function showAddOrgWindow(User){
	let TempContent='';

	TempContent+='<div id="add_support_org" class="window mini main_row col-xl-8 col-lg-10 col-12">';
	//
	TempContent+='<div class="close" onclick="closeAddOrgWindow();"></div>';

	TempContent+='<div class="top">Добавить организацию</div>';

	TempContent+='<div class="row main_row control">';
	
	TempContent+='<div class="col-12">Организация:</div>';
	TempContent+='<div class="col-12">';
	TempContent+='<select class="organization" name="organization" id="organization">';
	for(let i=0; i<=ArrayOrgsRecs.length-1;i++){
		TempContent+='<option value="'+ArrayOrgsRecs[i].id+'">'+ArrayOrgsRecs[i].short_name+'</option>';
	}
	TempContent+='</select>';
	TempContent+='</div>';

	TempContent+='<div class="col-12">';
	TempContent+='<button class="appointment" style="margin-top: 15px; width: 200px;" onclick="addUserOrganization();">Добавить организацию</button>';
	TempContent+='</div>';
	TempContent+='<input type="hidden" id="user" value="'+User+'">';

	TempContent+='</div>';
	
	TempContent+='</div>';

	document.getElementById("sub_container").innerHTML=TempContent;
	$("#background").fadeIn();
	$("#container").show();

	$('.organization').multiselect({enableClickableOptGroups: true, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '250', maxHeight: 200, enableFiltering: true, enableCaseInsensitiveFiltering : true, selectAllText: 'Выбрать все', includeSelectAllOption: true, filterPlaceholder: 'Выбрать организацию...'});
}

function addUserOrganization(){
	let organization=$('#add_support_org #organization').val();
	let user=$('#add_support_org #user').val();

	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': '&organization='+organization+'&user='+user+'&action=add_user_org&xml=1',
		'url': "index.php",
		'beforeSend': function() {
			$('.add_role').prop('disabled', true);
		},
		'success': function (Data) {
			if($(Data).find('message').text() == 'user_org_added'){
				closeAddRoleWindow();
				showMessageWindow('Организация добавлена.');
				ListShowUsersRecs();
			}
		}
	});
}

function closeAddOrgWindow(){
	$("#background").fadeOut();
	$("#container").hide();
}

function ListShowRolesRecs(){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#form_search_recs").serialize()+'&action=show_roles',
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
				// let TempContentRec='';

				if($(Recs).find('rec').text()){
					
					$(Recs).find('rec').each(function(){
						let FLAG=0;
						let Id=$(this).find('rec_id').text();
						let elements=0;

						$(this).find('rec_elements').find('element').each(function(){
							elements++;
						});
						let role=$(this).find('rec_name').text();

						TempContentRec='<div class="rec col-12 main_row row">';
						TempContentRec+='<div class="col-xl-3 col-lg-3 col-md-12 col-sm-12 col-xs-12 col-12">';
						TempContentRec+='<span class="title">'+$(this).find('rec_name').text()+'<br>'+$(this).find('rec_description').text()+'</span>';
						TempContentRec+='</div>';
						TempContentRec+='<div class="col-xl-9 col-lg-9 col-md-12 col-sm-12 col-xs-12 col-12 recs">';
						TempContentRec+='<strong>Элементы (всего ' + elements + ')</strong>';

						TempContentRec+='<br /><button class="appointment" id="add_element" style="margin-top: 15px; width: 200px;" onclick="showAddElementRoleWindow(\''+$(this).find('rec_name').text()+'\');">Добавить элемент</button><br /><br />';

						$(this).find('rec_elements').find('element').each(function(){
							TempContentRec+='<div class="rec">';
							TempContentRec+='<strong>['+ $(this).find('child').text()+']</strong>';
							TempContentRec+='<br />'+ $(this).find('desc').text()+'';
							TempContentRec+=`<br /><button class="button mini m-3" onclick="deleteElementRole('` + role + `','` + $(this).find('child').text() + `');">Удалить</button>`;
							TempContentRec+='</div>';
						});
						
						TempContentRec+='</div>';

						TempContentRec+='</div>';

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

function deleteElementRole(role, element){
	if(role && element){
		jQuery.ajax({
			'async': true,
			'global': false,
			'cache': false,
			'type': 'GET',
			'dataType': 'xml',
			'data': '&role='+role+'&element='+element+'&action=delete_element_role&xml=1',
			'url': "index.php",
			'beforeSend': function() {
				showLoading('ListRecs');
			},
			'success': function (Data) {
				if($(Data).find('message').text() == 'element_role_deleted'){
					ListShowRolesRecs();
				}
			}
		});
	}
}

function ListShowElementsRecs(){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#form_search_recs").serialize()+'&action=show_elements&type=2',
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
					
					$(Recs).find('rec').each(function(){
						let FLAG=0;
						let Id=$(this).find('rec_id').text();

						TempContentRec='<div class="rec col-12 main_row row">';
						TempContentRec+='<div class="col-xl-3 col-lg-3 col-md-12 col-sm-12 col-xs-12 col-12">';
						TempContentRec+='<span class="title">'+$(this).find('rec_name').text()+'<br></span>';
						TempContentRec+='</div>';
						TempContentRec+='<div class="col-xl-9 col-lg-9 col-md-12 col-sm-12 col-xs-12 col-12 recs">'+$(this).find('rec_description').text()+'</div>';

						TempContentRec+='</div>';
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

function showAddElementRoleWindow(Role){
	let TempContent='';

	TempContent+='<div id="add_support_element_role" class="window mini main_row col-xl-8 col-lg-10 col-12">';
	//
	TempContent+='<div class="close" onclick="closeAddElementRoleWindow();"></div>';

	TempContent+='<div class="top">Добавить элемент</div>';

	TempContent+='<div class="row main_row control">';
	
	TempContent+='<div class="col-12">Элемент:</div>';
	TempContent+='<div class="col-12">';
	TempContent+='<select name="element" id="element" class="element">';
	for(let i=0; i<=ArrayElementsRecs.length-1;i++){
		TempContent+='<option value=\''+ArrayElementsRecs[i].name+'\' title=\''+ArrayElementsRecs[i].description+'\'>'+ArrayElementsRecs[i].name+'</option>';
	}
	TempContent+='</select>';
	TempContent+='</div>';

	TempContent+='<div class="col-12">';
	TempContent+='<button class="appointment" id="add_role" style="margin-top: 15px; width: 200px;" onclick="addElementRole();">Добавить элемент</button>';
	TempContent+='</div>';

	TempContent+='</div>';

	TempContent+='<input type="hidden" id="role" value="'+Role+'">';
	TempContent+='</div>';

	document.getElementById("sub_container").innerHTML=TempContent;
	$("#background").fadeIn();
	$("#container").show();

	$('.element').multiselect({enableClickableOptGroups: true, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '250', maxHeight: 200, enableFiltering: true, enableCaseInsensitiveFiltering : true, selectAllText: 'Выбрать все', includeSelectAllOption: true, filterPlaceholder: 'Выбрать роль...'});
}

function addElementRole(){
	let role=$('#add_support_element_role #role').val();
	let element=$('#add_support_element_role #element').val();

	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': '&role='+role+'&element='+element+'&action=add_element_role&xml=1',
		'url': "index.php",
		'beforeSend': function() {
			$('.add_role').prop('disabled', true);
		},
		'success': function (Data) {
			if($(Data).find('message').text() == 'element_role_added'){
				closeAddRoleWindow();
				showMessageWindow('Элемент к роли добавлен.');
				ListShowRolesRecs();
			}
		}
	});
}

/* Breeds */
function ListShowBreedsRecs(Page = 1){
	if((currentPage == '' && Page != null) || (currentPage != Page)){currentPage = Page;}

	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#form_search_recs").serialize()+'&page='+Page+'&action=breeds&mode=xml',
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
					/////////////////
					TempContent+='<div class="pages">';
					if(parseInt($(Recs).find('counter').text()) > parseInt($(Recs).find('to').text())){
						TempContent+='<div class="info">'+$(Recs).find('from').text()+' - '+$(Recs).find('to').text()+' из '+$(Recs).find('counter').text()+' записей</div>';
					}else{
						TempContent+='<div class="info">'+$(Recs).find('from').text()+' - '+$(Recs).find('counter').text()+' из '+$(Recs).find('counter').text()+' записей</div>';
					}
					if(parseInt($(Recs).find('current').text()) > 1){
						TempContent+='<div class="first" onclick="ListShowBreedsRecs(1,\''+$(Recs).find('sort').text()+'\')")"></div>';
						TempContent+='<div class="previous" onclick="ListShowBreedsRecs('+(parseInt($(Recs).find('current').text())-1)+',\''+$(Recs).find('sort').text()+'\')")"></div>';
					}
					if($(Recs).find('current').text() > 3){
						for(let i=parseInt($(Recs).find('current').text())-2;i<=parseInt($(Recs).find('current').text())+2;i++){
							if(i <= (parseInt($(Recs).find('last').text())+1)){
								TempContent+='<div ';
								if($(Recs).find('current').text() == i){
									TempContent+='class="current" ';
								}
								TempContent+='onclick="ListShowBreedsRecs('+i+',\''+$(Recs).find('sort').text()+'\')")">' + i + '</div>';
							}
						}
					}else{
						for(let i=1;i<=5;i++){
							if(i <= (parseInt($(Recs).find('last').text())+1)){
								TempContent+='<div ';
								if($(Recs).find('current').text() == i){
									TempContent+='class="current" ';
								}
								TempContent+='onclick="ListShowBreedsRecs('+i+',\''+$(Recs).find('sort').text()+'\')">' + i + '</div>';
							}
						}
					}
						
					if(parseInt($(Recs).find('current').text()) < (parseInt($(Recs).find('last').text())+1)){
						TempContent+='<div class="next" onclick="ListShowBreedsRecs('+(parseInt($(Recs).find('current').text())+1)+',\''+$(Recs).find('sort').text()+'\')")"></div>';
						TempContent+='<div class="last" onclick="ListShowBreedsRecs('+(parseInt($(Recs).find('last').text())+1)+',\''+$(Recs).find('sort').text()+'\')")"></div>';
					}

					TempContent+='</div>';
					/////////////////

					TempContent+='<table class="w-100">';
					TempContent+='<tr>';
					TempContent+='<th><div id="name">Порода</div></th>';
					TempContent+='<th><div id="description">Описание</div></th>';
					TempContent+='<th><div id="species">Вид</div></th>';
					
					
					TempContent+='<th></th>';
					TempContent+='<th></th>';
					TempContent+='</tr>';

					$(Recs).find('rec').each(function(){
						let FLAG=0;
						let TempContentRec='<tr>';
						TempContentRec+='<td>' + $(this).find('rec_name').text() + '</td>';
						TempContentRec+='<td>' + $(this).find('rec_description').text() + '</td>';
						TempContentRec+='<td>' + $(this).find('rec_species').text() + '</td>';
						
						TempContentRec+='<td style="width: 32px;" onclick="showBreedsWindow(' + $(this).find('rec_id').text() + ');"><div class="edit"></div></td>';
						TempContentRec+='<td style="width: 32px;" onclick="deleteBreedsAcceptWindow(\''+$(this).find('rec_name').text()+'\', ' + $(this).find('rec_id').text() + ');"><div class="delete"></div></td>';
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
						TempContent+='<div class="first" onclick="ListShowBreedsRecs(1,\''+$(Recs).find('sort').text()+'\')")"></div>';
						TempContent+='<div class="previous" onclick="ListShowBreedsRecs('+(parseInt($(Recs).find('current').text())-1)+',\''+$(Recs).find('sort').text()+'\')")"></div>';
					}
					if($(Recs).find('current').text() > 3){
						for(let i=parseInt($(Recs).find('current').text())-2;i<=parseInt($(Recs).find('current').text())+2;i++){
							if(i <= (parseInt($(Recs).find('last').text())+1)){
								TempContent+='<div ';
								if($(Recs).find('current').text() == i){
									TempContent+='class="current" ';
								}
								TempContent+='onclick="ListShowBreedsRecs('+i+',\''+$(Recs).find('sort').text()+'\')")">' + i + '</div>';
							}
						}
					}else{
						for(let i=1;i<=5;i++){
							if(i <= (parseInt($(Recs).find('last').text())+1)){
								TempContent+='<div ';
								if($(Recs).find('current').text() == i){
									TempContent+='class="current" ';
								}
								TempContent+='onclick="ListShowBreedsRecs('+i+',\''+$(Recs).find('sort').text()+'\')">' + i + '</div>';
							}
						}
					}
						
					if(parseInt($(Recs).find('current').text()) < (parseInt($(Recs).find('last').text())+1)){
						TempContent+='<div class="next" onclick="ListShowBreedsRecs('+(parseInt($(Recs).find('current').text())+1)+',\''+$(Recs).find('sort').text()+'\')")"></div>';
						TempContent+='<div class="last" onclick="ListShowBreedsRecs('+(parseInt($(Recs).find('last').text())+1)+',\''+$(Recs).find('sort').text()+'\')")"></div>';
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
function showBreedsWindow(Id = null){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': '&id='+Id+'&mode=xml&action=breed',
		'url': "index.php",
		'beforeSend': function() {
			//показать загрузку
			showTopLoading();
		},
		'success': function (Data) {
			closeTopLoading();
			let TempContent='';

			TempContent+='<div id="breed_window" class="window main_row col-xl-6 col-lg-8 col-12">';
			TempContent+='<div class="close" onclick="closeBreedsWindow();"></div>';
			if(Id){
				TempContent+='<div class="top">Редактировать породу</div>';
			}else{
				TempContent+='<div class="top">Добавить породу</div>';
			}
					
			TempContent+='<div class="w-100" id="window_breed" style="overflow-y: auto;">';
			TempContent+='<form class="form-window" id="form_breed">';
			TempContent+='<div class="col-12 main_row row">';

			TempContent+='<div class="col-12 error" id="breed_window_message" style="margin-bottom: 15px; display: none;"></div>';

			TempContent+='<div class="col-3 row-center-align">Вид:</div>';
			TempContent+='<div class="col-9" style="margin-top: 10px;">';
			TempContent+='<select class="species" id="species" name="species" style="width:100%;">';
			for(let i=0; i<=ArraySpeciesRecs.length-1;i++){
				TempContent+='<option value="'+ArraySpeciesRecs[i].id+'"';
				if($(Data).find('rec_species').text() == ArraySpeciesRecs[i].id){
					TempContent+=' selected';
				}
				TempContent+='>'+ArraySpeciesRecs[i].name+'</option>';
			}
			TempContent+='</select>';
			TempContent+='</div>';
			
			TempContent+='<div class="col-3 required row-center-align">Наименование:</div>';
			TempContent+='<div class="col-9" style="margin-top: 10px;">';
			TempContent+='<input type="text" name="name" id="name" value="'+$(Data).find('rec_name').text()+'" autocomplete="off">';
			TempContent+='</div>';

			TempContent+='<div class="col-3 row-center-align">Описание:</div>';
			TempContent+='<div class="col-9" style="margin-top: 10px;">';
			TempContent+='<textarea name="description" id="description" rows="5">'+$(Data).find('rec_description').text()+'</textarea>';
			TempContent+='</div>';
			

			TempContent+='<input type="hidden" name="id" id="id" value="'+Id+'">';
			//
			
			
			TempContent+='</div>';
			TempContent+='</form>';
			
					
			//Кнопки управления
			TempContent+='<div class="controls">';
			if(Id){
				TempContent+='<button class="button" id="breed" style="width: 220px;" onclick="saveBreeds();">Сохранить</button>';
			}else{
				TempContent+='<button class="button" id="breed" style="width: 220px;" onclick="saveBreeds();">Создать</button>';
			}
			TempContent+='</div>';
			//Кнопки управления

			TempContent+='</div>';

			document.getElementById("sub_container").innerHTML=TempContent;
			$('.services').multiselect({enableClickableOptGroups: true, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '100%', maxHeight: 300, enableFiltering: true, enableCaseInsensitiveFiltering : true, selectAllText: 'Выбрать все', includeSelectAllOption: true, filterPlaceholder: 'Выбрать услугу...'});
			$("#background").fadeIn();
			$("#container").show();
		}
	});
}
function saveBreeds(){
	if($('#window_breed #name').val() == ''){
		$("#breed_window_message").html('Не заполнены необходимые поля.');
		$("#breed_window_message").show();

		return true;
	}

	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'POST',
		'dataType': 'xml',
		'data': jQuery("#form_breed").serialize()+'&action=save_breed&mode=xml',
		'url': "index.php",
		'beforeSend': function() {
			//показать загрузку
			showTopLoading();
		},
		'success': function (Data) {
			if($(Data).find('message').text() == 'token_invalid'){
				window.location.href = 'index.php';
			}else{
				if($(Data).find('message').text() == 'breed_saved'){
					closeTopLoading();
					closeBreedsWindow();
					ListShowBreedsRecs();
				}
			}
		}
	});
}
function closeBreedsWindow(){
	$("#background").fadeOut();
	$("#container").hide();
}
function deleteBreedsAcceptWindow(Breeds, Id){
	let Text=`Вы уверены, что хотите удалить породу <strong>${Breeds}</strong>?`;
	showAcceptWindow('Удаление породы', Text, 'deleteBreeds(' + Id + ');', 'Удалить','delete');
}
function deleteBreeds(id){
	closeAcceptWindow();
	if(id){
		jQuery.ajax({
			'async': true,
			'global': false,
			'cache': false,
			'type': 'GET',
			'dataType': 'xml',
			'data': '&id='+id+'&action=delete_breed&xml=1',
			'url': "index.php",
			'beforeSend': function() {
				showLoading('ListRecs');
			},
			'success': function (Data) {
				if($(Data).find('message').text() == 'breed_deleted'){
					ListShowBreedsRecs();
				}
			}
		});
	}
}
/* Breeds */

/* Species */
function ListShowSpeciesRecs(Page = 1){
	if((currentPage == '' && Page != null) || (currentPage != Page)){currentPage = Page;}

	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#form_search_recs").serialize()+'&page='+Page+'&action=species_list&mode=xml',
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
					/////////////////
					TempContent+='<div class="pages">';
					if(parseInt($(Recs).find('counter').text()) > parseInt($(Recs).find('to').text())){
						TempContent+='<div class="info">'+$(Recs).find('from').text()+' - '+$(Recs).find('to').text()+' из '+$(Recs).find('counter').text()+' записей</div>';
					}else{
						TempContent+='<div class="info">'+$(Recs).find('from').text()+' - '+$(Recs).find('counter').text()+' из '+$(Recs).find('counter').text()+' записей</div>';
					}
					if(parseInt($(Recs).find('current').text()) > 1){
						TempContent+='<div class="first" onclick="ListShowSpeciesRecs(1,\''+$(Recs).find('sort').text()+'\')")"></div>';
						TempContent+='<div class="previous" onclick="ListShowSpeciesRecs('+(parseInt($(Recs).find('current').text())-1)+',\''+$(Recs).find('sort').text()+'\')")"></div>';
					}
					if($(Recs).find('current').text() > 3){
						for(let i=parseInt($(Recs).find('current').text())-2;i<=parseInt($(Recs).find('current').text())+2;i++){
							if(i <= (parseInt($(Recs).find('last').text())+1)){
								TempContent+='<div ';
								if($(Recs).find('current').text() == i){
									TempContent+='class="current" ';
								}
								TempContent+='onclick="ListShowSpeciesRecs('+i+',\''+$(Recs).find('sort').text()+'\')")">' + i + '</div>';
							}
						}
					}else{
						for(let i=1;i<=5;i++){
							if(i <= (parseInt($(Recs).find('last').text())+1)){
								TempContent+='<div ';
								if($(Recs).find('current').text() == i){
									TempContent+='class="current" ';
								}
								TempContent+='onclick="ListShowSpeciesRecs('+i+',\''+$(Recs).find('sort').text()+'\')">' + i + '</div>';
							}
						}
					}
						
					if(parseInt($(Recs).find('current').text()) < (parseInt($(Recs).find('last').text())+1)){
						TempContent+='<div class="next" onclick="ListShowSpeciesRecs('+(parseInt($(Recs).find('current').text())+1)+',\''+$(Recs).find('sort').text()+'\')")"></div>';
						TempContent+='<div class="last" onclick="ListShowSpeciesRecs('+(parseInt($(Recs).find('last').text())+1)+',\''+$(Recs).find('sort').text()+'\')")"></div>';
					}

					TempContent+='</div>';
					/////////////////

					TempContent+='<table class="w-100">';
					TempContent+='<tr>';
					TempContent+='<th><div id="name">Вид</div></th>';
					TempContent+='<th><div id="description">Описание</div></th>';
					TempContent+='<th></th>';
					TempContent+='<th></th>';
					TempContent+='</tr>';

					$(Recs).find('rec').each(function(){
						let FLAG=0;
						let TempContentRec='<tr>';
						TempContentRec+='<td>' + $(this).find('rec_name').text() + '</td>';
						TempContentRec+='<td>' + $(this).find('rec_description').text() + '</td>';
						
						TempContentRec+='<td style="width: 32px;" onclick="showSpeciesWindow(' + $(this).find('rec_id').text() + ');"><div class="edit"></div></td>';
						TempContentRec+='<td style="width: 32px;" onclick="deleteSpeciesAcceptWindow(\''+$(this).find('rec_name').text()+'\', ' + $(this).find('rec_id').text() + ');"><div class="delete"></div></td>';
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
						TempContent+='<div class="first" onclick="ListShowSpeciesRecs(1,\''+$(Recs).find('sort').text()+'\')")"></div>';
						TempContent+='<div class="previous" onclick="ListShowSpeciesRecs('+(parseInt($(Recs).find('current').text())-1)+',\''+$(Recs).find('sort').text()+'\')")"></div>';
					}
					if($(Recs).find('current').text() > 3){
						for(let i=parseInt($(Recs).find('current').text())-2;i<=parseInt($(Recs).find('current').text())+2;i++){
							if(i <= (parseInt($(Recs).find('last').text())+1)){
								TempContent+='<div ';
								if($(Recs).find('current').text() == i){
									TempContent+='class="current" ';
								}
								TempContent+='onclick="ListShowSpeciesRecs('+i+',\''+$(Recs).find('sort').text()+'\')")">' + i + '</div>';
							}
						}
					}else{
						for(let i=1;i<=5;i++){
							if(i <= (parseInt($(Recs).find('last').text())+1)){
								TempContent+='<div ';
								if($(Recs).find('current').text() == i){
									TempContent+='class="current" ';
								}
								TempContent+='onclick="ListShowSpeciesRecs('+i+',\''+$(Recs).find('sort').text()+'\')">' + i + '</div>';
							}
						}
					}
						
					if(parseInt($(Recs).find('current').text()) < (parseInt($(Recs).find('last').text())+1)){
						TempContent+='<div class="next" onclick="ListShowSpeciesRecs('+(parseInt($(Recs).find('current').text())+1)+',\''+$(Recs).find('sort').text()+'\')")"></div>';
						TempContent+='<div class="last" onclick="ListShowSpeciesRecs('+(parseInt($(Recs).find('last').text())+1)+',\''+$(Recs).find('sort').text()+'\')")"></div>';
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
function showSpeciesWindow(Id = null){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': '&id='+Id+'&mode=xml&action=specie',
		'url': "index.php",
		'beforeSend': function() {
			//показать загрузку
			showTopLoading();
		},
		'success': function (Data) {
			closeTopLoading();
			let TempContent='';

			TempContent+='<div id="specie_window" class="window main_row col-xl-6 col-lg-8 col-12">';
			TempContent+='<div class="close" onclick="closeSpeciesWindow();"></div>';
			if(Id){
				TempContent+='<div class="top">Редактировать вид</div>';
			}else{
				TempContent+='<div class="top">Добавить вид</div>';
			}
					
			TempContent+='<div class="w-100" id="window_specie" style="overflow-y: auto;">';
			TempContent+='<form class="form-window" id="form_specie">';
			TempContent+='<div class="col-12 main_row row">';

			TempContent+='<div class="col-12 error" id="specie_window_message" style="margin-bottom: 15px; display: none;"></div>';
			
			TempContent+='<div class="col-3 required row-center-align">Наименование:</div>';
			TempContent+='<div class="col-9" style="margin-top: 10px;">';
			TempContent+='<input type="text" name="name" id="name" value="'+$(Data).find('rec_name').text()+'" autocomplete="off">';
			TempContent+='</div>';

			TempContent+='<div class="col-3 row-center-align">Описание:</div>';
			TempContent+='<div class="col-9" style="margin-top: 10px;">';
			TempContent+='<textarea name="description" id="description" rows="5">'+$(Data).find('rec_description').text()+'</textarea>';
			TempContent+='</div>';

			TempContent+='<input type="hidden" name="id" id="id" value="'+Id+'">';
			//
			
			
			TempContent+='</div>';
			TempContent+='</form>';
			
					
			//Кнопки управления
			TempContent+='<div class="controls">';
			if(Id){
				TempContent+='<button class="button" id="specie" style="width: 220px;" onclick="saveSpecies();">Сохранить</button>';
			}else{
				TempContent+='<button class="button" id="specie" style="width: 220px;" onclick="saveSpecies();">Создать</button>';
			}
			TempContent+='</div>';
			//Кнопки управления

			TempContent+='</div>';

			document.getElementById("sub_container").innerHTML=TempContent;
			$('.services').multiselect({enableClickableOptGroups: true, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '100%', maxHeight: 300, enableFiltering: true, enableCaseInsensitiveFiltering : true, selectAllText: 'Выбрать все', includeSelectAllOption: true, filterPlaceholder: 'Выбрать услугу...'});
			$("#background").fadeIn();
			$("#container").show();
		}
	});
}
function saveSpecies(){
	if($('#window_specie #name').val() == ''){
		$("#specie_window_message").html('Не заполнены необходимые поля.');
		$("#specie_window_message").show();

		return true;
	}

	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'POST',
		'dataType': 'xml',
		'data': jQuery("#form_specie").serialize()+'&action=save_specie&mode=xml',
		'url': "index.php",
		'beforeSend': function() {
			//показать загрузку
			showTopLoading();
		},
		'success': function (Data) {
			if($(Data).find('message').text() == 'token_invalid'){
				window.location.href = 'index.php';
			}else{
				if($(Data).find('message').text() == 'specie_saved'){
					closeTopLoading();
					closeSpeciesWindow();
					ListShowSpeciesRecs();
				}
			}
		}
	});
}
function closeSpeciesWindow(){
	$("#background").fadeOut();
	$("#container").hide();
}
function deleteSpeciesAcceptWindow(Species, Id){
	let Text=`Вы уверены, что хотите удалить вид <strong>${Species}</strong>?`;
	showAcceptWindow('Удаление вида', Text, 'deleteSpecies(' + Id + ');', 'Удалить','delete');
}
function deleteSpecies(id){
	closeAcceptWindow();
	if(id){
		jQuery.ajax({
			'async': true,
			'global': false,
			'cache': false,
			'type': 'GET',
			'dataType': 'xml',
			'data': '&id='+id+'&action=delete_specie&xml=1',
			'url': "index.php",
			'beforeSend': function() {
				showLoading('ListRecs');
			},
			'success': function (Data) {
				if($(Data).find('message').text() == 'specie_deleted'){
					ListShowSpeciesRecs();			
				}
			}
		});
	}
}
/* Species */

/* Diseases */
function ListShowDiseasesRecs(Page = 1){
	if((currentPage == '' && Page != null) || (currentPage != Page)){currentPage = Page;}

	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#form_search_recs").serialize()+'&page='+Page+'&action=diseases&mode=xml',
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
					/////////////////
					TempContent+='<div class="pages">';
					if(parseInt($(Recs).find('counter').text()) > parseInt($(Recs).find('to').text())){
						TempContent+='<div class="info">'+$(Recs).find('from').text()+' - '+$(Recs).find('to').text()+' из '+$(Recs).find('counter').text()+' записей</div>';
					}else{
						TempContent+='<div class="info">'+$(Recs).find('from').text()+' - '+$(Recs).find('counter').text()+' из '+$(Recs).find('counter').text()+' записей</div>';
					}
					if(parseInt($(Recs).find('current').text()) > 1){
						TempContent+='<div class="first" onclick="ListShowDiseasesRecs(1,\''+$(Recs).find('sort').text()+'\')")"></div>';
						TempContent+='<div class="previous" onclick="ListShowDiseasesRecs('+(parseInt($(Recs).find('current').text())-1)+',\''+$(Recs).find('sort').text()+'\')")"></div>';
					}
					if($(Recs).find('current').text() > 3){
						for(let i=parseInt($(Recs).find('current').text())-2;i<=parseInt($(Recs).find('current').text())+2;i++){
							if(i <= (parseInt($(Recs).find('last').text())+1)){
								TempContent+='<div ';
								if($(Recs).find('current').text() == i){
									TempContent+='class="current" ';
								}
								TempContent+='onclick="ListShowDiseasesRecs('+i+',\''+$(Recs).find('sort').text()+'\')")">' + i + '</div>';
							}
						}
					}else{
						for(let i=1;i<=5;i++){
							if(i <= (parseInt($(Recs).find('last').text())+1)){
								TempContent+='<div ';
								if($(Recs).find('current').text() == i){
									TempContent+='class="current" ';
								}
								TempContent+='onclick="ListShowDiseasesRecs('+i+',\''+$(Recs).find('sort').text()+'\')">' + i + '</div>';
							}
						}
					}
						
					if(parseInt($(Recs).find('current').text()) < (parseInt($(Recs).find('last').text())+1)){
						TempContent+='<div class="next" onclick="ListShowDiseasesRecs('+(parseInt($(Recs).find('current').text())+1)+',\''+$(Recs).find('sort').text()+'\')")"></div>';
						TempContent+='<div class="last" onclick="ListShowDiseasesRecs('+(parseInt($(Recs).find('last').text())+1)+',\''+$(Recs).find('sort').text()+'\')")"></div>';
					}

					TempContent+='</div>';
					/////////////////

					TempContent+='<table class="w-100">';
					TempContent+='<tr>';
					TempContent+='<th><div id="cod">Код</div></th>';
					TempContent+='<th><div id="name">Наименование заболевания</div></th>';
					
					TempContent+='<th></th>';
					TempContent+='<th></th>';
					TempContent+='</tr>';

					$(Recs).find('rec').each(function(){
						let FLAG=0;
						let TempContentRec='<tr>';
						TempContentRec+='<td>' + $(this).find('rec_cod').text() + '</td>';
						TempContentRec+='<td>' + $(this).find('rec_name').text() + '</td>';
						
						TempContentRec+='<td style="width: 32px;" onclick="showDiseaseWindow(' + $(this).find('rec_id').text() + ');"><div class="edit"></div></td>';
						TempContentRec+='<td style="width: 32px;" onclick="deleteDiseaseAcceptWindow(\''+$(this).find('rec_name').text()+'\', ' + $(this).find('rec_id').text() + ');"><div class="delete"></div></td>';
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
						TempContent+='<div class="first" onclick="ListShowDiseasesRecs(1,\''+$(Recs).find('sort').text()+'\')")"></div>';
						TempContent+='<div class="previous" onclick="ListShowDiseasesRecs('+(parseInt($(Recs).find('current').text())-1)+',\''+$(Recs).find('sort').text()+'\')")"></div>';
					}
					if($(Recs).find('current').text() > 3){
						for(let i=parseInt($(Recs).find('current').text())-2;i<=parseInt($(Recs).find('current').text())+2;i++){
							if(i <= (parseInt($(Recs).find('last').text())+1)){
								TempContent+='<div ';
								if($(Recs).find('current').text() == i){
									TempContent+='class="current" ';
								}
								TempContent+='onclick="ListShowDiseasesRecs('+i+',\''+$(Recs).find('sort').text()+'\')")">' + i + '</div>';
							}
						}
					}else{
						for(let i=1;i<=5;i++){
							if(i <= (parseInt($(Recs).find('last').text())+1)){
								TempContent+='<div ';
								if($(Recs).find('current').text() == i){
									TempContent+='class="current" ';
								}
								TempContent+='onclick="ListShowDiseasesRecs('+i+',\''+$(Recs).find('sort').text()+'\')">' + i + '</div>';
							}
						}
					}
						
					if(parseInt($(Recs).find('current').text()) < (parseInt($(Recs).find('last').text())+1)){
						TempContent+='<div class="next" onclick="ListShowDiseasesRecs('+(parseInt($(Recs).find('current').text())+1)+',\''+$(Recs).find('sort').text()+'\')")"></div>';
						TempContent+='<div class="last" onclick="ListShowDiseasesRecs('+(parseInt($(Recs).find('last').text())+1)+',\''+$(Recs).find('sort').text()+'\')")"></div>';
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
function showDiseaseWindow(Id = null){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': '&id='+Id+'&mode=xml&action=disease',
		'url': "index.php",
		'beforeSend': function() {
			//показать загрузку
			showTopLoading();
		},
		'success': function (Data) {
			closeTopLoading();
			let TempContent='';

			TempContent+='<div id="disease_window" class="window main_row col-xl-6 col-lg-8 col-12">';
			TempContent+='<div class="close" onclick="closeDiseaseWindow();"></div>';
			if(Id){
				TempContent+='<div class="top">Редактировать заболевание</div>';
			}else{
				TempContent+='<div class="top">Добавить заболевание</div>';
			}
					
			TempContent+='<div class="w-100" id="window_disease" style="overflow-y: auto;">';
			TempContent+='<form class="form-window" id="form_disease">';
			TempContent+='<div class="col-12 main_row row">';

			TempContent+='<div class="col-12 error" id="disease_window_message" style="margin-bottom: 15px; display: none;"></div>';
			
			TempContent+='<div class="col-3 row-center-align">Код ГОСТ заболевания:</div>';
			TempContent+='<div class="col-9" style="margin-top: 10px;">';
			TempContent+='<input type="text" name="cod" id="cod" value="'+$(Data).find('rec_cod').text()+'" autocomplete="off">';
			TempContent+='</div>';
			
			TempContent+='<div class="col-3 required row-center-align">Наименование заболевания:</div>';
			TempContent+='<div class="col-9" style="margin-top: 10px;">';
			TempContent+='<input type="text" name="name" id="name" value="'+$(Data).find('rec_name').text()+'" autocomplete="off">';
			TempContent+='</div>';

			// TempContent+='<div class="col-3 row-center-align">Услуги:</div>';
			// TempContent+='<div class="col-9" style="margin-top: 10px;">';
			// TempContent+='<select class="services" name="services[]" id="services" multiple="multiple" style="width:100%;">';
			// for(let i=0; i<=ArrayServicesRecs.length-1;i++){
			// 	let ch = 0;
			// 	$(Data).find('rec_services').find('service').each(function(){
			// 		if($(this).text() == ArrayServicesRecs[i].id){
			// 			ch = 1;
			// 		}
			// 	});
			// 	TempContent+='<option value="'+ArrayServicesRecs[i].id+'"';
			// 	if(ch){
			// 		TempContent+=' selected';
			// 	}
			// 	TempContent+='>'+ArrayServicesRecs[i].name+'</option>';
			// }
			// TempContent+='</select>';
			// TempContent+='</div>';

			// TempContent+='<div class="col-3 row-center-align">Описание:</div>';
			// TempContent+='<div class="col-9" style="margin-top: 10px;">';
			// TempContent+='<textarea name="description" id="description" rows="5">'+$(Data).find('rec_description').text()+'</textarea>';
			// TempContent+='</div>';

			TempContent+='<input type="hidden" name="id" id="id" value="'+Id+'">';
			//
			
			
			TempContent+='</div>';
			TempContent+='</form>';
			
					
			//Кнопки управления
			TempContent+='<div class="controls">';
			if(Id){
				TempContent+='<button class="button" id="disease" style="width: 220px;" onclick="saveDisease();">Сохранить</button>';
			}else{
				TempContent+='<button class="button" id="disease" style="width: 220px;" onclick="saveDisease();">Создать</button>';
			}
			TempContent+='</div>';
			//Кнопки управления

			TempContent+='</div>';

			document.getElementById("sub_container").innerHTML=TempContent;
			$('.services').multiselect({enableClickableOptGroups: true, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '100%', maxHeight: 300, enableFiltering: true, enableCaseInsensitiveFiltering : true, selectAllText: 'Выбрать все', includeSelectAllOption: true, filterPlaceholder: 'Выбрать услугу...'});
			$("#background").fadeIn();
			$("#container").show();
		}
	});
}
function saveDisease(){
	if($('#window_disease #name').val() == ''){
		$("#disease_window_message").html('Не заполнены необходимые поля.');
		$("#disease_window_message").show();

		return true;
	}

	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'POST',
		'dataType': 'xml',
		'data': jQuery("#form_disease").serialize()+'&action=save_disease&mode=xml',
		'url': "index.php",
		'beforeSend': function() {
			//показать загрузку
			showTopLoading();
		},
		'success': function (Data) {
			if($(Data).find('message').text() == 'token_invalid'){
				window.location.href = 'index.php';
			}else{
				if($(Data).find('message').text() == 'disease_saved'){
					closeTopLoading();
					closeDiseaseWindow();
					ListShowDiseasesRecs();
				}
			}
		}
	});
}
function closeDiseaseWindow(){
	$("#background").fadeOut();
	$("#container").hide();
}
function deleteDiseaseAcceptWindow(Disease, Id){
	let Text=`Вы уверены, что хотите удалить заболевание <strong>${Disease}</strong>?`;
	showAcceptWindow('Удаление заболевания', Text, 'deleteDisease(' + Id + ');', 'Удалить','delete');
}
function deleteDisease(id){
	closeAcceptWindow();
	if(id){
		jQuery.ajax({
			'async': true,
			'global': false,
			'cache': false,
			'type': 'GET',
			'dataType': 'xml',
			'data': '&id='+id+'&action=delete_disease&xml=1',
			'url': "index.php",
			'beforeSend': function() {
				showLoading('ListRecs');
			},
			'success': function (Data) {
				if($(Data).find('message').text() == 'disease_deleted'){
					ListShowDiseasesRecs();			
				}
			}
		});
	}
}
/* Diseases */

/* Specialization */
function ListShowSpecializationsRecs(){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#form_search_recs").serialize()+'&action=specializations',
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
					TempContent+='<th><div id="name">Специализация</div></th>';
					TempContent+='<th><div>Услуги</div></th>';
					TempContent+='<th></th>';
					TempContent+='<th></th>';
					TempContent+='</tr>';

					$(Recs).find('rec').each(function(){
						let FLAG=0;
						let TempContentRec='<tr>';
						TempContentRec+='<td>' + $(this).find('rec_name').text() + '</td>';
						TempContentRec+='<td>' + $(this).find('rec_services').text() + '</td>';
						TempContentRec+='<td style="width: 32px;" onclick="showSpecializationWindow(' + $(this).find('rec_id').text() + ');"><div class="edit"></div></td>';
						TempContentRec+='<td style="width: 32px;" onclick="deleteSpecializationAcceptWindow(\''+$(this).find('rec_name').text()+'\', ' + $(this).find('rec_id').text() + ');"><div class="delete"></div></td>';
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
function showSpecializationWindow(Id = null){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': '&id='+Id+'&mode=xml&action=specialization',
		'url': "index.php",
		'beforeSend': function() {
			//показать загрузку
			showTopLoading();
		},
		'success': function (Data) {
			closeTopLoading();
			let TempContent='';

			TempContent+='<div id="specialization_window" class="window main_row col-xl-6 col-lg-8 col-12">';
			TempContent+='<div class="close" onclick="closeSpecializationWindow();"></div>';
			if(Id){
				TempContent+='<div class="top">Редактировать специализацию</div>';
			}else{
				TempContent+='<div class="top">Создать специализацию</div>';
			}
					
			TempContent+='<div class="w-100" id="window_specialization" style="overflow-y: auto;">';
			TempContent+='<form class="form-window" id="form_specialization">';
			TempContent+='<div class="col-12 main_row row">';

			TempContent+='<div class="col-12 error" id="specialization_window_message" style="margin-bottom: 15px; display: none;"></div>';
			
			
			TempContent+='<div class="col-3 required row-center-align">Наименование специализации:</div>';
			TempContent+='<div class="col-9" style="margin-top: 10px;">';
			TempContent+='<input type="text" name="name" id="name" value="'+$(Data).find('rec_name').text()+'">';
			TempContent+='</div>';

			TempContent+='<div class="col-3 row-center-align">Услуги:</div>';
			TempContent+='<div class="col-9" style="margin-top: 10px;">';
			TempContent+='<select class="services" name="services[]" id="services" multiple="multiple" style="width:100%;">';
			for(let i=0; i<=ArrayServicesRecs.length-1;i++){
				let ch = 0;
				$(Data).find('rec_services').find('service').each(function(){
					if($(this).text() == ArrayServicesRecs[i].id){
						ch = 1;
					}
				});
				TempContent+='<option value="'+ArrayServicesRecs[i].id+'"';
				if(ch){
					TempContent+=' selected';
				}
				TempContent+='>'+ArrayServicesRecs[i].name+'</option>';
			}
			TempContent+='</select>';
			TempContent+='</div>';

			TempContent+='<div class="col-3 row-center-align">Описание:</div>';
			TempContent+='<div class="col-9" style="margin-top: 10px;">';
			TempContent+='<textarea name="description" id="description" rows="5">'+$(Data).find('rec_description').text()+'</textarea>';
			TempContent+='</div>';

			TempContent+='<input type="hidden" name="id" id="id" value="'+Id+'">';
			//
			
			
			TempContent+='</div>';
			TempContent+='</form>';
			
					
			//Кнопки управления
			TempContent+='<div class="controls">';
			if(Id){
				TempContent+='<button class="button" id="specialization" style="width: 220px;" onclick="saveSpecialization();">Сохранить</button>';
			}else{
				TempContent+='<button class="button" id="specialization" style="width: 220px;" onclick="saveSpecialization();">Создать</button>';
			}
			TempContent+='</div>';
			//Кнопки управления

			TempContent+='</div>';

			document.getElementById("sub_container").innerHTML=TempContent;
			$('.services').multiselect({enableClickableOptGroups: true, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '100%', maxHeight: 300, enableFiltering: true, enableCaseInsensitiveFiltering : true, selectAllText: 'Выбрать все', includeSelectAllOption: true, filterPlaceholder: 'Выбрать услугу...'});
			$("#background").fadeIn();
			$("#container").show();
		}
	});
}
function saveSpecialization(){
	if($('#window_specialization #name').val() == ''){
		$("#specialization_window_message").html('Не заполнены необходимые поля.');
		$("#specialization_window_message").show();

		return true;
	}

	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'POST',
		'dataType': 'xml',
		'data': jQuery("#form_specialization").serialize()+'&action=save_specialization&mode=xml',
		'url': "index.php",
		'beforeSend': function() {
			//показать загрузку
			showTopLoading();
		},
		'success': function (Data) {
			if($(Data).find('message').text() == 'token_invalid'){
				window.location.href = 'index.php';
			}else{
				if($(Data).find('message').text() == 'specialization_saved'){
					closeTopLoading();
					closeSpecializationWindow();
					ListShowSpecializationsRecs();
				}
			}
		}
	});
}
function closeSpecializationWindow(){
	$("#background").fadeOut();
	$("#container").hide();
}
function deleteSpecializationAcceptWindow(Specialization, Id){
	let Text=`Вы уверены, что хотите удалить специализацию <strong>${Specialization}</strong>?`;
	showAcceptWindow('Удаление специализации', Text, 'deleteSpecialization(' + Id + ');', 'Удалить','delete');
}
function deleteSpecialization(id){
	closeAcceptWindow();
	if(id){
		jQuery.ajax({
			'async': true,
			'global': false,
			'cache': false,
			'type': 'GET',
			'dataType': 'xml',
			'data': '&id='+id+'&action=delete_specialization&xml=1',
			'url': "index.php",
			'beforeSend': function() {
				showLoading('ListRecs');
			},
			'success': function (Data) {
				if($(Data).find('message').text() == 'specialization_deleted'){
					ListShowSpecializationsRecs();			
				}
			}
		});
	}
}
/* Specialization */

function closeAddElementRoleWindow(){
	$("#background").fadeOut();
	$("#container").hide();
}

function showAddElementWindow(){
	let TempContent='';

	TempContent+='<div id="add_element_window" class="window mini main_row col-xl-8 col-lg-10 col-12">';
	//
	TempContent+='<div class="close" onclick="closeAddElementWindow();"></div>';

	TempContent+='<div class="top">Создать элемент</div>';

	TempContent+='<div class="row main_row control">';
	
	TempContent+='<div class="col-12 required">Название:</div>';
	TempContent+='<div class="col-12">';
	TempContent+='<input type="text" name="name" id="name" value="">';
	TempContent+='</div>';
	TempContent+='<div class="col-12 required">Описание:</div>';
	TempContent+='<div class="col-12">';
	TempContent+='<textarea name="description" id="description" rows="5"></textarea>';
	TempContent+='</div>';

	TempContent+='<div class="col-12">';
	TempContent+='<button class="appointment" id="add_role" style="margin-top: 15px; width: 200px;" onclick="addElement();">Создать элемент</button>';
	TempContent+='</div>';

	TempContent+='</div>';
	
	TempContent+='</div>';

	document.getElementById("sub_container").innerHTML=TempContent;
	$("#background").fadeIn();
	$("#container").show();
}

function closeAddElementWindow(){
	$("#background").fadeOut();
	$("#container").hide();
}

function addElement(){
	let name=$('#add_element_window #name').val();
	let description=$('#add_element_window #description').val();

	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': '&name='+name+'&description='+description+'&action=add_element&xml=1',
		'url': "index.php",
		'beforeSend': function() {
			$('.add_role').prop('disabled', true);
		},
		'success': function (Data) {
			if($(Data).find('message').text() == 'element_added'){
				closeAddRoleWindow();
				showMessageWindow('Элемент добавлен.');
				ListShowElementsRecs();
			}
		}
	});
}

function ListShowOrganizationsRecs(){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#form_search_recs").serialize()+'&action=organizations',
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
					$(Recs).find('rec').each(function(){
						let FLAG=0;
						let Id=$(this).find('rec_id').text();

						TempContentRec='<div class="rec col-12 main_row row">';
						TempContentRec+='<div class="col-xl-4 col-lg-4 col-md-12 col-sm-12 col-xs-12 col-12">';
						TempContentRec+='<span class="title">' + $(this).find('rec_short_name').text() + '';
						TempContentRec+='</span>';

						if($(this).find('rec_type_name').text()){
							TempContentRec+='<br />'+$(this).find('rec_type_name').text()+'';
						}
						
						if($(this).find('rec_organization_type_const').text()){
							TempContentRec+='<br /><font size="1"><strong>(' + $(this).find('rec_organization_type_const').text() + ', ';
							
							if($(this).find('rec_is_managing_organization').text() == 't'){TempContentRec+='да';}else{TempContentRec+='нет';}

							TempContentRec+=')</strong></font>';
						}
						
						if($(this).find('rec_short_name').text()){
							TempContentRec+='<br /><font size="1">' + $(this).find('rec_name').text() + '</font>';
						}
						
						if($(this).find('rec_area_name').text()){
							TempContentRec+='<br /><font size="1"><strong>' + $(this).find('rec_area_name').text() + '</strong></font>';
						}

						if($(this).find('rec_district_name').text()){
							TempContentRec+='<br /><font size="1">' + $(this).find('rec_district_name').text() + '</font>';
						}

						if($(this).find('rec_address').text()){
							TempContentRec+='<br /><font size="1">Адрес: <strong>' + $(this).find('rec_address').text() +'</strong></font>';
						}

						if($(this).find('rec_address').text() && $(this).find('rec_longitude').text()){
							TempContentRec+='<br /><font size="1">Координаты: <strong>' + $(this).find('rec_latitude').text() + '/' + $(this).find('rec_longitude').text() + '</strong></font>';
						}

						TempContentRec+='</div>';

						TempContentRec+='<div class="col-xl-3 col-lg-3 col-md-12 col-sm-12 col-xs-12 col-12"><font size="2">';
						TempContentRec+='<strong>Родительская организация:</strong> ' + ($(this).find('rec_parent_name').text() ? $(this).find('rec_parent_name').text() : "-") + '';
						TempContentRec+='<br /><strong>Управляющая организация:</strong> ' + ($(this).find('rec_managing_name').text() ? $(this).find('rec_managing_name').text() : "-") + '';
						TempContentRec+='</font></div>';

						TempContentRec+='<div class="col-xl-2 col-lg-2 col-md-12 col-sm-12 col-xs-12 col-12">';
						TempContentRec+='<font size="2">';
						TempContentRec+='<strong>ИНН:</strong> ' + ($(this).find('rec_inn').text() ? $(this).find('rec_inn').text() : "-");
						TempContentRec+='<br /><strong>КПП:</strong> ' + ($(this).find('rec_kpp').text() ? $(this).find('rec_kpp').text() : "-");
						TempContentRec+='<br /><strong>ОГРН:</strong> ' + ($(this).find('rec_ogrn').text() ? $(this).find('rec_ogrn').text() : "-");
						TempContentRec+='<br /><strong>Рег.ном.:</strong> ' + ($(this).find('rec_reg_number').text() ? $(this).find('rec_reg_number').text() : "-");
						TempContentRec += `<br><strong>${$(this).find('rec_is_hidden').text() == 'f' ? 'Не скрыт' : 'Скрыт'}<strong></br>`
						TempContentRec+='</font>';
						TempContentRec+='</div>';
						
						TempContentRec+='<div class="col-xl-2 col-lg-2 col-md-12 col-sm-12 col-xs-12 col-12 align-text-middle">';
						TempContentRec+='<button class="button mini m-3" onclick="showAddEditOrganizationWindow(' + $(this).find('rec_id').text() + ');">Редактировать</button>';
						TempContentRec+='<button class="button mini m-3" onclick="deleteOrganization(' + $(this).find('rec_id').text() + ');">Удалить</button>';
						TempContentRec+='</div>';

						TempContentRec+='</div>';

						TempContentRec+='</div>';

						

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

function showAddEditOrganizationWindow(TempId = null){
	let Data;
	// let TempName='';
	// let TempShortName='';
	// let TempTypeId='';

	if(TempId){
		jQuery.ajax({
			'async': false,
			'global': false,
			'cache': false,
			'type': 'GET',
			'dataType': 'xml',
			'data': jQuery("#form_search_recs").serialize()+'&action=organizations',
			'url': "index.php",
			'beforeSend': function() {
				//показать загрузку
			},
			'success': function (Recs) {
				$(Recs).find('rec').each(function(){
					if($(this).find('rec_id').text() == TempId){
						Data=$(this);
					}
				});
			}
		});
	}
	

	let TempContent='';
	TempContent+='<div id="add_org_window" class="window main_row col-xl-8 col-lg-10 col-12">';
	//
	TempContent+='<div class="close" onclick="closeAddElementWindow();"></div>';

	if(TempId){
		TempContent+='<div class="top">Редактировать организацию</div>';
	}else{
		TempContent+='<div class="top">Создать организацию</div>';
	}

	TempContent+='<div class="col-12 error" id="org_window_message" style="margin-bottom: 15px; display: none;"></div>';

	TempContent+='<form id="form_save_organization" class="p-3" onsubmit="return false;">';
	TempContent+='<div class="row main_row control">';
	
	TempContent+='<div class="col-12 required">Название:</div>';
	TempContent+='<div class="col-12">';
	TempContent+='<input type="text" name="name" id="name" value="' + jcms_format_for_json(Data !== undefined ? Data.find('rec_name').text() : '') + '">';
	TempContent+='</div>';

	TempContent+='<div class="col-12">Сокращённое название:</div>';
	TempContent+='<div class="col-12">';
	TempContent+='<input type="text" name="short_name" id="short_name" value="' + jcms_format_for_json(Data !== undefined ? Data.find('rec_short_name').text() : '') + '">';
	TempContent+='</div>';

	TempContent+='<div class="col-12">Тип организации:</div>';

	TempContent+='<div class="col-12">';
	TempContent+='<select class="type" name="type" id="type">';

	TempContent+='<option value="">Не указан</option>';

	for(let i=0; i<=ArrayOrgTypesRecs.length-1;i++){
		TempContent+='<option ';
		if((Data !== undefined ? Data.find('rec_type_id').text() : '') == ArrayOrgTypesRecs[i].id){
			TempContent+='selected ';	
		}
		TempContent+='value="'+ArrayOrgTypesRecs[i].id+'">'+ArrayOrgTypesRecs[i].name+'</option>';
	}
	TempContent+='</select>';
	TempContent+='</div>';

	TempContent+='<div class="col-12">Округ:</div>';
	TempContent+='<div class="col-12">';
	TempContent+='<select class="area" name="area" id="area">';
	TempContent+='<option value="">Нет</option>';
	for(let i=0; i<=ArrayAreasRecs.length-1;i++){
		TempContent+='<option ';
		if((Data !== undefined ? Data.find('rec_area_id').text() : '') == ArrayAreasRecs[i].id){
			TempContent+='selected ';	
		}
		TempContent+='value="'+ArrayAreasRecs[i].id+'">'+ArrayAreasRecs[i].name+'</option>';
	}
	TempContent+='</select>';
	TempContent+='</div>';

	TempContent+='<div class="col-12 required">Родительская организация:</div>';
	TempContent+='<div class="col-12">';
	TempContent+='<select class="organization" name="parent" id="parent">';
	TempContent+='<option value="0">Нет</option>';
	for(let i=0; i<=ArrayOrgsRecs.length-1;i++){
		TempContent+='<option ';
		if((Data !== undefined ? Data.find('rec_parent_id').text() : '') == ArrayOrgsRecs[i].id){
			TempContent+='selected ';	
		}
		TempContent+='value="'+ArrayOrgsRecs[i].id+'">'+ArrayOrgsRecs[i].short_name+'</option>';
	}
	TempContent+='</select>';
	TempContent+='</div>';

	TempContent+='<div class="col-12 required">Управляющая организация:</div>';
	TempContent+='<div class="col-12">';
	TempContent+='<select class="organization" name="managing" id="managing">';
	TempContent+='<option value="">Нет</option>';
	for(let i=0; i<=ArrayOrgsRecs.length-1;i++){
		TempContent+='<option ';
		if((Data !== undefined ? Data.find('rec_managing_id').text() : '') == ArrayOrgsRecs[i].id){
			TempContent+='selected ';	
		}
		TempContent+='value="'+ArrayOrgsRecs[i].id+'">'+ArrayOrgsRecs[i].short_name+'</option>';
	}
	TempContent+='</select>';
	TempContent+='</div>';

	TempContent+='<div class="col-12">ИНН.:</div>';
	TempContent+='<div class="col-12">';
	TempContent+='<input type="text" name="inn" id="inn" value="' + jcms_format_for_json(Data !== undefined ? Data.find('rec_inn').text() : '') + '">';
	TempContent+='</div>';

	TempContent+='<div class="col-12">КПП:</div>';
	TempContent+='<div class="col-12">';
	TempContent+='<input type="text" name="kpp" id="kpp" value="' + jcms_format_for_json(Data !== undefined ? Data.find('rec_kpp').text() : '') + '">';
	TempContent+='</div>';

	TempContent+='<div class="col-12">ОГРН:</div>';
	TempContent+='<div class="col-12">';
	TempContent+='<input type="text" name="ogrn" id="ogrn" value="' + jcms_format_for_json(Data !== undefined ? Data.find('rec_ogrn').text() : '') + '">';
	TempContent+='</div>';

	TempContent+='<div class="col-12">Рег.ном.:</div>';
	TempContent+='<div class="col-12">';
	TempContent+='<input type="text" name="reg_number" id="reg_number" value="' + jcms_format_for_json(Data !== undefined ? Data.find('rec_reg_number').text() : '') + '">';
	TempContent+='</div>';

	TempContent+='<div class="col-12">Координаты:</div>';
	TempContent+='<div class="col-6">';
	TempContent+='<input type="number" max="180" step="any" name="latitude" id="latitude" value="' + jcms_format_for_json(Data !== undefined ? Data.find('rec_latitude').text() : '') + '">';
	TempContent+='</div>';
	TempContent+='<div class="col-6">';
	TempContent+='<input type="number" max="180" step="any" name="longitude" id="longitude" value="' + jcms_format_for_json(Data !== undefined ? Data.find('rec_longitude').text() : '') + '">';
	TempContent+='</div>';

	TempContent+='<div class="col-12">Адрес:</div>';
	TempContent+='<div class="col-12">';
	TempContent+='<textarea id="address" name="address" class="JxTag"></textarea>';
	TempContent+='</div>';

	TempContent+='<div class="col-12">';
	TempContent += `<div class="col-12"> Скрыть организацию? <input type="checkbox" name="is_hidden" id="is_hidden" ${Data && Data.find('rec_is_hidden').text() != 'f' && 'checked'}></div>`
	if(TempId){
		TempContent+='<button id="add_role" class="appointment" style="margin-top: 15px; width: 200px;" onclick="saveOrganization();">Сохранить организацию</button>';
	}else{
		TempContent+='<button id="add_role" class="appointment" style="margin-top: 15px; width: 200px;" onclick="saveOrganization();">Добавить организацию</button>';
	}

	TempContent+='</div>';
	TempContent+='</div>';
	TempContent+='<input type="hidden" name="action" value="save_organization">';
	TempContent+='<input type="hidden" name="mode" value="xml">';
	if(TempId){
		TempContent+='<input type="hidden" name="id" value="'+TempId+'">';
	}
	TempContent+='</form>';	
	TempContent+='</div>';
	
	document.getElementById("sub_container").innerHTML=TempContent;
	$("#background").fadeIn();
	$("#container").show();

	var options ={
        name: 'address',
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
    };
    var addr = new JxTag(options);
	if(Data && Data.find('rec_address').text()){
		addr.AddTag(Data.find('rec_address').text(), {id: Data.find('rec_fias').text()});
	}
}

function closeAddEditOrganizationWindow(){
	$("#background").fadeOut();
	$("#container").hide();
}

function saveOrganization(){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'POST',
		'dataType': 'xml',
		'data': jQuery("#form_save_organization").serialize(),
		'url': "index.php",
		'beforeSend': function() {
			//
		},
		'success': function (Data) {
			if($(Data).find('message').text() == 'oranization_saved'){
				closeAddEditOrganizationWindow();
				showMessageWindow('Организация успешно сохранена.');
				ListShowOrganizationsRecs();
			}
			if($(Data).find('message').text() == 'oranization_added'){
				closeAddEditOrganizationWindow();
				showMessageWindow('Организация успешно добавлена.');
				ListShowOrganizationsRecs();
			}
			if($(Data).find('message').text() == 'fields_not_filled'){
				$('#add_org_window #name').addClass("error_field");
				$("#add_org_window #org_window_message").html('Не заполнены необходимые поля.');
				$("#add_org_window #org_window_message").show();
			}
		}
	});
}

function deleteOrganization(id){
	if(id){
		jQuery.ajax({
			'async': true,
			'global': false,
			'cache': false,
			'type': 'GET',
			'dataType': 'xml',
			'data': '&id='+id+'&action=delete_organization&xml=1',
			'url': "index.php",
			'beforeSend': function() {
				showLoading('ListRecs');
			},
			'success': function (Data) {
				if($(Data).find('message').text() == 'organization_deleted'){
					ListShowOrganizationsRecs();			
				}
			}
		});
	}
}

function showInformingWindow(Id = null){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': '&id='+Id+'&mode=xml&action=informing',
		'url': "index.php",
		'beforeSend': function() {
			//показать загрузку
			showTopLoading();
		},
		'success': function (Data) {
			closeTopLoading();

			let TempContent='';

			TempContent+='<div id="add_informing_window" class="window main_row col-xl-6 col-lg-10 col-12">';
			//
			TempContent+='<div class="close" onclick="closeInformingWindow();"></div>';

			if(Id){
				TempContent+='<div class="top">Редактирование информирования</div>';
				TempContent+='<input type="hidden" name="id" value="'+$(Data).find('rec_id').text()+'">';
			}else{
				TempContent+='<div class="top">Новое информирование</div>';
			}

			TempContent+='<div class="col-xl-12 col-lg-12" style="margin-top: 15px;">';
			TempContent+='<div class="error" id="informing_window_message" style="display: none;"></div>';
			TempContent+='</div>';

			TempContent+='<div class="col-xl-12 col-lg-12 main_row">';
			TempContent+='<form class="form-window main_row" id="">';
			
			TempContent+='<div class="col-12 required">Текст информирования (осталось <span id="counter">250</span> символов):</div>';
			TempContent+='<div class="col-12">';
			
			TempContent+='<textarea id="text" name="text" rows="5" maxlength="250">'+$(Data).find('rec_text').text()+'</textarea>';

			TempContent+='</div>';

			TempContent+='<div class="col-12 required" style="margin-top: 30px;">Период действия:</div>';
			TempContent+='<div class="col-12">';

			let date_from;if($(Data).find('rec_date_from').text()){date_from=$(Data).find('rec_date_from').text();}else{date_from=get_current_date();}
			let date_to;if($(Data).find('rec_date_to').text()){date_to=$(Data).find('rec_date_to').text();}else{date_to=get_current_date();}

			let time_from;if($(Data).find('rec_time_from').text()){time_from=$(Data).find('rec_time_from').text();}else{time_from='00:00:00';}
			let time_to;if($(Data).find('rec_time_to').text()){time_to=$(Data).find('rec_time_to').text();}else{time_to='00:00:00';}
			
			TempContent+='<input type="date" name="date_from" id="date_from" autocomplete="off" style="width: 150px; margin-right: 10px;" min="1950-01-01" max="'+get_current_date()+'" value="'+date_from+'">';
			TempContent+='<input type="time" name="time_from" id="time_from" style="width: 150px; margin-right: 10px;" value="'+time_from+'" name="timein[]">';

			TempContent+='<input type="date" name="date_to" id="date_to" autocomplete="off" style="width: 150px; margin-right: 10px;" min="'+get_current_date()+'" max="2050-01-01" value="'+date_to+'">';
			TempContent+='<input type="time" name="time_to" id="time_to" autocomplete="off" style="width: 150px;" value="'+time_to+'" name="timeout[]">';

			TempContent+='</div>';


			TempContent+='<div class="col-12 required" style="margin-top: 30px;">Организации:</div>';
			TempContent+='<div class="col-12">';

			TempContent+='<select class="organizations" name="organizations[]" id="organizations" multiple="multiple" style="width:100%;">';
			for(let i=0; i<=ArrayOrgsRecs.length-1;i++){
				let ch = 0;
				$(Data).find('rec_organizations').find('organization').each(function(){
					if($(this).text() == ArrayOrgsRecs[i].id){
						ch = 1;
					}
				});
				TempContent+='<option value="'+ArrayOrgsRecs[i].id+'"';
				if(ch){
					TempContent+=' selected';
				}
				TempContent+='>'+ArrayOrgsRecs[i].short_name+'</option>';
			}
			TempContent+='</select>';

			TempContent+='</div>';


			TempContent+='</form>';
			TempContent+='</div>';

			//Кнопки управления
			TempContent+='<div class="controls">';
			TempContent+='<button class="add cancel" id="" style="width: 200px; margin-right: 10px;" onclick="closeInformingWindow();">Отмена</button>';
			
			if(Id){
				TempContent+='<button class="appointment" id="save_informing" style="width: 200px;" onclick="saveInforming();">Сохранить</button>';
			}else{
				TempContent+='<button class="appointment" id="add_informing" style="width: 200px;" onclick="addInforming();">Опубликовать</button>';
			}

			TempContent+='</div>';
			//Кнопки управления
			
			TempContent+='</div>';

			document.getElementById("sub_container").innerHTML=TempContent;
			
			$('.organizations').multiselect({enableClickableOptGroups: true, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '100%', maxHeight: 300, enableFiltering: true, enableCaseInsensitiveFiltering : true, selectAllText: 'Выбрать все', includeSelectAllOption: true, filterPlaceholder: 'Выбрать оранизацию...'});

			textarea = document.getElementById("text");
			counter = document.getElementById("counter");
			const maxlength = 250;
			textarea.addEventListener('input', onInput);

			function onInput(event) {
				event.target.value = event.target.value.substr(0, maxlength);
				const length = event.target.value.length;
				counter.textContent = maxlength - length;
			}

			$("#background").fadeIn();
			$("#container").show();
		}
		
	});
}

function addInforming(){
	if($("#add_informing_window #text").val() == ''){
		$("#informing_window_message").html('Не заполнены необходимые поля.');
		$("#informing_window_message").show();
		//$("#add_informing_window #text").addClass('error');
		
		return true;
	}

	//проверка периода действия
	let date_from = $("#add_informing_window #date_from").val();
	let date_to = $("#add_informing_window #date_to").val();
	let time_from = $("#add_informing_window #time_from").val();
	let time_to = $("#add_informing_window #time_to").val();
	
	let date_from_ = new Date(date_from+' '+time_from);
	let date_to_ = new Date(date_to)+' '+time_to;

	if(date_from == '' || date_to == ''){
		$("#informing_window_message").html('Не заполнен период информирования.');
		$("#informing_window_message").show();
		return true;
	}

	if($("#add_informing_window #organizations").val() == ''){
		$("#informing_window_message").html('Необходимо выбрать хотя бы одну организацию для информирования.');
		$("#informing_window_message").show();
		return true;
	}

	if(date_from_ > date_to_){
		//
		$("#informing_window_message").html('Дата и время начала периода не может быть больше даты и времени его окончания.');
		$("#informing_window_message").show();
		//

		return true;
	}
	
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'POST',
		'dataType': 'xml',
		'data': jQuery("#add_informing_window :input").serialize()+'&action=add_informing&mode=xml',
		'url': "index.php",
		'beforeSend': function() {
			$('#add_informing_window #add_informing').prop('disabled', true);
		},
		'success': function (Data) {
			if($(Data).find('message').text() == 'informing_added'){
				closeInformingWindow();
				showMessageWindow('Информирование успешно создано.');
				listShowInformings();
			}
			if($(Data).find('message').text() == 'empty_fields'){
				$("#informing_window_message").html('Не заполнены необходимые поля.');
				$("#informing_window_message").show();
				$('#add_informing_window #add_informing').prop('disabled', false);
			}
		}
	});
}

function saveInforming(){
	if($("#add_informing_window #text").val() == ''){
		$("#informing_window_message").html('Не заполнены необходимые поля.');
		$("#informing_window_message").show();
		//$("#add_informing_window #text").addClass('error');
		
		return true;
	}

	//проверка периода действия
	let date_from = $("#add_informing_window #date_from").val();
	let date_to = $("#add_informing_window #date_to").val();
	let time_from = $("#add_informing_window #time_from").val();
	let time_to = $("#add_informing_window #time_to").val();
	
	let date_from_ = new Date(date_from+' '+time_from);
	let date_to_ = new Date(date_to)+' '+time_to;

	if(date_from == '' || date_to == ''){
		$("#informing_window_message").html('Не заполнен период информирования.');
		$("#informing_window_message").show();
		return true;
	}

	if($("#add_informing_window #organizations").val() == ''){
		$("#informing_window_message").html('Необходимо выбрать хотя бы одну организацию для информирования.');
		$("#informing_window_message").show();
		return true;
	}

	if(date_from_ > date_to_){
		$("#informing_window_message").html('Дата и время начала периода не может быть больше даты и времени его окончания.');
		$("#informing_window_message").show();
		
		return true;
	}
	
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'POST',
		'dataType': 'xml',
		'data': jQuery("#add_informing_window :input").serialize()+'&action=save_informing&mode=xml',
		'url': "index.php",
		'beforeSend': function() {
			$('#add_informing_window #add_informing').prop('disabled', true);
		},
		'success': function (Data) {
			if($(Data).find('message').text() == 'informing_saved'){
				closeInformingWindow();
				showMessageWindow('Информирование успешно сохранено.');
				listShowInformings();
			}
			if($(Data).find('message').text() == 'empty_fields'){
				$("#informing_window_message").html('Не заполнены необходимые поля.');
				$("#informing_window_message").show();
				$('#add_informing_window #add_informing').prop('disabled', false);
			}
		}
	});
}


function closeInformingWindow(){
	$("#background").fadeOut();
	$("#container").hide();
}

function deleteInformings(Id){
	if(Id){
		jQuery.ajax({
			'async': true,
			'global': false,
			'cache': false,
			'type': 'GET',
			'dataType': 'xml',
			'data': '&id='+Id+'&action=delete_informing&mode=xml',
			'url': "index.php",
			'beforeSend': function() {
				showLoading('ListRecs');
			},
			'success': function (Data) {
				if($(Data).find('message').text() == 'informing_deleted'){
					listShowInformings();			
				}
			}
		});
	}
}

function listShowInformings(){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': 'action=informings&mode=xml',
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
					TempContent+='<th>Дата публикации</th>';
					TempContent+='<th>Период действия</th>';
					TempContent+='<th>Текст уведомления</th>';
					TempContent+='<th></th>';
					TempContent+='<th></th>';
					TempContent+='</tr>';

					$(Recs).find('rec').each(function(){
						let FLAG=0;
						let Id=$(this).find('rec_id').text();

						TempContentRec='<tr>';
						TempContentRec+='<td>'+$(this).find('rec_date').text()+'</td>';
						TempContentRec+='<td>'+$(this).find('rec_period').text()+'</td>';
						TempContentRec+='<td>'+$(this).find('rec_text').text()+'</td>';

						TempContentRec+='<td style="width: 32px;"><div class="edit" onclick="showInformingWindow('+$(this).find('rec_id').text()+');"></div></td>';
						TempContentRec+='<td style="width: 32px;"><div class="delete" onclick="deleteInformings('+$(this).find('rec_id').text()+');"></div></td>';
						
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

function saveSupportOptions(){
	
	let encodeData = btoa($("#support_options #sql").val());
	
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'POST',
		'dataType': 'xml',
		'data': 'data='+encodeData+'&action=save_options&mode=xml',
		'url': "index.php",
		'beforeSend': function(data) {
			// //показать загрузку
			// $("#message").removeClass('error');
			// $("#message").removeClass('success');
			// $("#message").addClass('process');
			// $("#message").html('Пожалуйста подожите...');
			// $("#message").show();
		},
		'success': function (Data) {
			$("#message").html($(Data).find('message').text());
		}
	});
}

$(document).ready(function () {
	loadOrgs();
	loadServices();
	loadOrgTypes();
	loadAreas();
	loadRoles();
	loadElements();
	loadSpecies();
});