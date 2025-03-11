function saveServicesSpecialist(){
	if(ArrayServicesSpecialistsRecs.length > 0){
		let services_specialists='';

		for(let i=0;i<ArrayServicesSpecialistsRecs.length;i++){
			services_specialists+='&service='+ArrayServicesSpecialistsRecs[i].service+'&specialist='+ArrayServicesSpecialistsRecs[i].specialist+'&action_='+ArrayServicesSpecialistsRecs[i].action+'';
		}

		jQuery.ajax({
			'async': true,
			'global': false,
			'cache': false,
			'type': 'GET',
			'dataType': 'xml',
			'data': ''+services_specialists+'&action=save_services_specialists',
			'url': "index.php",
			'beforeSend': function() {
				$('.save_services_specialists').prop('disabled', true);
			},
			'success': function (Data) {
				if($(Data).find('message').text() == 'services_specialists_saved'){
					showMessageWindow('Связи специалиста и услуг успешно сохранены.');
					ListShowServicesSpecialists();
					$("#controllers").hide();
					$('.save_services_specialists').prop('disabled', false);
					ArrayServicesSpecialistsRecs=[];
					ArrayServicesSpecialistsRecs.splice(0,ArrayServicesSpecialistsRecs.length);
				}
			}
		});
	}
}

function ListShowServicesSpecialists(){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#form").serialize()+'&action=specialists',
		'url': "index.php",
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
				let TempRecsNum=$(Recs).find('counter').text();

				if($(Recs).find('rec').text()){
					let newURL='?action=services_specialists';
					if($('#service').val()){
						newURL+='&service='+$('#service').val()+'';
					}
					if($('#organization').val()){
						newURL+='&organization='+$('#organization').val()+'';
					}
					update_url(newURL);

					TempContent+='<div class="row recs">';

					$(Recs).find('recs').find('rec').each(function(){
						let Id=$(this).find('rec_id').text();//ID специалиста
						TempContentRec='<div class="rec col-12 main_row row">';			
						TempContentRec+='<div class="col-xl-3 col-lg-4 col-md-12 col-sm-12 col-xs-12 col-12">';
						TempContentRec+=''+$(this).find('rec_name_organization').text()+'<br>';
						TempContentRec+='<span class="title">'+$(this).find('rec_fullname').text()+'</span><br>';
						TempContentRec+='</div>';
						TempContentRec+='<div class="col-xl-9 col-lg-4 col-md-12 col-sm-12 col-xs-12 col-12">';
						let ArrayServicesRecs=[];
						ArrayServicesRecs.splice(0,ArrayServicesRecs.length);

						$(this).find('rec_services').find('service').each(function(){
							ArrayServicesRecs.push($(this).find('service_id').text());//все услуги оказываемые врачом
						});

						let services_num=0;
						$(Recs).find('services').find('service').each(function(){
							for(let i=0; i<=ArrayServicesRecs.length-1;i++){
								if(ArrayServicesRecs[i] == $(this).find('id').text()){
									services_num++;
								}
							}
						});

						TempContentRec+='Услуги (оказывает активных услуг '+services_num+'): ';
						
						$(Recs).find('types').find('type').each(function(){
							let type=$(this).find('id').text();
							let num=0;
							let type_services_num=0;
							$(Recs).find('services').find('service').each(function(){
								if(type == $(this).find('type').text()){
									for(let i=0; i<=ArrayServicesRecs.length-1;i++){
										if(ArrayServicesRecs[i] == $(this).find('id').text()){
											type_services_num++;
										}
									}
									
									num++;
								}
							});

							if(num > 0){
								TempContentRec+='<div class="col-12 main_row" style="height: 30px; cursor: pointer;" onclick="showhideDiv(\'type_'+Id+'_'+type+'\');"><strong>'+$(this).find('name').text()+' ('+type_services_num+' из '+num+')</strong></div>';
								TempContentRec+='<div class="row main_row" style="font-size: 11px; display: none;" id="type_'+Id+'_'+type+'">';

								$(Recs).find('services').find('service').each(function(){
									if(type == $(this).find('type').text()){
										TempContentRec+='<div class="col-6" style="margin-top: 10px; margin-bottom: 10px; display: flex;">';
										let ch=false;
										for(let i=0; i<=ArrayServicesRecs.length-1;i++){
											if(ArrayServicesRecs[i] == $(this).find('id').text()){
												ch=true;
											}
										}
										TempContentRec+='<input specialist="'+Id+'" service="'+$(this).find('id').text()+'" onclick="checkServicesSpecialists('+Id+','+$(this).find('id').text()+');" for="service_'+Id+'_'+$(this).find('id').text()+'" ';
										if(ch){TempContentRec+='checked ch="1" ';}else{TempContentRec+='ch="0" ';}
										TempContentRec+='type="checkbox" name="service_'+Id+'_'+$(this).find('id').text()+'" id="service_'+Id+'_'+$(this).find('id').text()+'" value="1">';
										TempContentRec+='<label for="service_'+Id+'_'+$(this).find('id').text()+'">['+$(this).find('cod').text()+'] '+$(this).find('name').text()+'</label></div>';
									}
								});

								TempContentRec+='<div class="col-12" style="padding-bottom: 15px;"><button class="button mini" onclick="checkAll(\'type_'+Id+'_'+type+'\');">Отметить все</button> <button class="button mini" onclick="uncheckAll(\'type_'+Id+'_'+type+'\');">Убрать все</button></div>';
								TempContentRec+='</div>';
							}
						});

						TempContentRec+='</div>';
						TempContentRec+='</div>';
						TempContent+=TempContentRec;
					});

					TempContent+='</div>';

					document.getElementById('ListInfo').innerHTML='Найдено врачей: ' + TempRecsNum + '.';
					$('#ListInfo').show();
				}

				if(TempRecsNum == 0){
					TempContent='<div class="message">По данному запросу не найдено записей.</div>';
				}
				document.getElementById('ListRecs').innerHTML=TempContent;
			}
		}
	});
}

function ListShowServices(Page = 1){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#form").serialize()+'&action=services&page='+Page+'&mode=xml',
		'url': "index.php",
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
				let TempRecsNum=0;

				if($(Recs).find('rec').text()){
					///ПАГИНАЦИЯ
					TempContent+='<div class="pages" style="margin-bottom: 15px;">';
					// if(parseInt($(Recs).find('counter').text()) > parseInt($(Recs).find('to').text())){
					// 	TempContent+='<div class="info">'+$(Recs).find('from').text()+' - '+$(Recs).find('to').text()+' из '+$(Recs).find('counter').text()+' записей</div>';
					// }else{
					// 	TempContent+='<div class="info">'+$(Recs).find('from').text()+' - '+$(Recs).find('counter').text()+' из '+$(Recs).find('counter').text()+' записей</div>';
					// }
					if(parseInt($(Recs).find('current').text()) > 1){
						TempContent+='<div class="first" onclick="ListShowServices(1,\''+$(Recs).find('sort').text()+'\')")"></div>';
						TempContent+='<div class="previous" onclick="ListShowServices('+(parseInt($(Recs).find('current').text())-1)+',\''+$(Recs).find('sort').text()+'\')")"></div>';
					}
					
					//
					if($(Recs).find('current').text() > 3){
						for(let i=parseInt($(Recs).find('current').text())-2;i<=parseInt($(Recs).find('current').text())+2;i++){
							if(i <= (parseInt($(Recs).find('last').text())+1)){
								TempContent+='<div ';
								if($(Recs).find('current').text() == i){
									TempContent+='class="current" ';
								}
								TempContent+='onclick="ListShowServices('+i+',\''+$(Recs).find('sort').text()+'\')")">' + i + '</div>';
							}
						}
					}else{
						for(let i=1;i<=5;i++){
							if(i <= (parseInt($(Recs).find('last').text())+1)){
								TempContent+='<div ';
								if($(Recs).find('current').text() == i){
									TempContent+='class="current" ';
								}
								TempContent+='onclick="ListShowServices('+i+',\''+$(Recs).find('sort').text()+'\')">' + i + '</div>';
							}
						}
					}
						
					if(parseInt($(Recs).find('current').text()) < (parseInt($(Recs).find('last').text())+1)){
						TempContent+='<div class="next" onclick="ListShowServices('+(parseInt($(Recs).find('current').text())+1)+',\''+$(Recs).find('sort').text()+'\')")"></div>';
						TempContent+='<div class="last" onclick="ListShowServices('+(parseInt($(Recs).find('last').text())+1)+',\''+$(Recs).find('sort').text()+'\')")"></div>';
					}

					TempContent+='</div>';
					///ПАГИНАЦИЯ

					TempContent+='<div class="row recs">';

					$(Recs).find('rec').each(function(){
						let FLAG=0;
						let Id=$(this).find('rec_id').text();

						TempContentRec='<div class="rec col-12 main_row row">';
						if($(this).find('rec_type').text() == 'mosru'){
							TempContentRec+='<div class="mosru">mos.ru</div>';
						}
						TempContentRec+='<div class="col-xl-4 col-lg-4 col-md-12 col-sm-12 col-xs-12 col-12">';
						
						TempContentRec+='Прайслист: '+$(this).find('rec_id_pricelist').text()+', ID услуги: '+$(this).find('rec_id').text()+'<br>';

						if($(this).find('rec_deleted').text() == 1){TempContentRec+='<del>';}
						TempContentRec+='<span class="title">';
						if($(this).find('rec_cod').text()){
							TempContentRec+='['+$(this).find('rec_cod').text()+'] ';
						}
						TempContentRec+=''+$(this).find('rec_name').text()+'</span><br>';
						if($(this).find('rec_deleted').text() == 1){TempContentRec+='</del>';}

						if($(this).find('rec_alternative_name').text() || $(this).find('rec_briefname').text()){						
							TempContentRec+='<font size="1">';
							if($(this).find('rec_alternative_name').text()){
								TempContentRec+='<strong>Альтернативное: ' + $(this).find('rec_alternative_name').text() + '</strong>';
							}
							if($(this).find('rec_briefname').text()){
								TempContentRec+=' (' + $(this).find('rec_briefname').text() + ')';
							}
							TempContentRec+='</font><br>';
						}
						
						if($(this).find('rec_price').text() != '0.00'){
							TempContentRec+='Цена: '+$(this).find('rec_price').text()+'₽<br>';
						}
						TempContentRec+='Длительность: '+$(this).find('rec_duration').text()+' минут, отдых: '+$(this).find('rec_cooldown').text()+' минут.<br>';

						TempContentRec+='Тип услуги: '+$(this).find('rec_service_types_name').text()+' ['+$(this).find('rec_service_types_id').text()+']<br>';
						if($(this).find('rec_specializations_name').text()){
							TempContentRec+='Специализация услуги: '+$(this).find('rec_specializations_name').text()+'<br>';
						}
						if($(this).find('rec_measures_name').text()){
							TempContentRec+='Единица измерения: '+$(this).find('rec_measures_name').text()+' ('+$(this).find('rec_measures_description').text()+')<br>';
						}

						if($(this).find('rec_deleted').text() == 1 && $(this).find('rec_type').text() != 'mosru'){
							TempContentRec+='<font color="gray">Врачи оказывающие услугу: '+$(this).find('rec_counter_docs').text()+'</font>';
						}else{
							TempContentRec+='<a href="index.php?action=services_specialists&service='+$(this).find('rec_id').text()+'">Врачи оказывающие услугу: '+$(this).find('rec_counter_docs').text()+'</a>';
						}
						TempContentRec+='<br><a href="index.php?action=edit_service&id='+$(this).find('rec_id').text()+'">Редактировать услугу</a>';
						TempContentRec+='</div>';

						TempContentRec+='<div class="col-xl-2 col-lg-4 col-md-12 col-sm-12 col-xs-12 col-12">';
						TempContentRec+='Параметры: <ul>';
						TempContentRec+='<li>Дома: ';if($(this).find('rec_at_home').text() == 1){TempContentRec+='Да';}else{TempContentRec+='Нет';}TempContentRec+='</li>';
						TempContentRec+='<li>В клинике: ';if($(this).find('rec_at_clinic').text() == 1){TempContentRec+='Да';}else{TempContentRec+='Нет';}TempContentRec+='</li>';
						TempContentRec+='<li>Оказывается раз в день: ';if($(this).find('rec_once_per_day').text() == 1){TempContentRec+='Да';}else{TempContentRec+='Нет';}TempContentRec+='</li>';
						TempContentRec+='<li>Классификация услуги для журналов: ';
						if($(this).find('rec_com_class_journal').text() == 'medicalAssistance'){
							TempContentRec+='Лечебная помощь';
						}else if($(this).find('rec_com_class_journal').text() == 'additional'){
							TempContentRec+='Дополнительные исследования';
						}else{
							TempContentRec+='Отсутствует';
						}
						TempContentRec+='</li>';

						TempContentRec+='<li>Для выводков: ';
						if($(this).find('rec_for_broods').text() == 'HEAD'){
							TempContentRec+='Голова';
						}else if($(this).find('rec_for_broods').text() == 'ALL'){
							TempContentRec+='Все';
						}else{
							TempContentRec+='Нет';
						}
						TempContentRec+='</li>';

						TempContentRec+='<li>Для множественных приемов: ';
						if($(this).find('rec_for_multiple').text() == 'HEAD'){
							TempContentRec+='Голова';
						}else if($(this).find('rec_for_multiple').text() == 'ALL'){
							TempContentRec+='Все';
						}else{
							TempContentRec+='Нет';
						}
						TempContentRec+='</li>';

						TempContentRec+='</ul>';
						TempContentRec+='</div>';

						TempContentRec+='<div class="col-xl-3 col-lg-5 col-md-12 col-sm-12 col-xs-12 col-12">';
						if($(this).find('rec_parameters').text()){
							TempContentRec+='Параметры (входящие): <ul>';
							$(this).find('rec_parameters').find('parameter').each(function(){
								TempContentRec+='<li title="'+$(this).find('parameter_tech_name').text()+'">'+$(this).find('parameter_name').text()+'';
								if($(this).find('parameter_required').text() == 1){
									TempContentRec+=' <font color="red">*</font>';
								}
								TempContentRec+='</li>';
							});
							TempContentRec+='</ul>';
						}
						TempContentRec+='</div>';


						TempContentRec+='<div class="col-xl-3 col-lg-5 col-md-12 col-sm-12 col-xs-12 col-12">';
						if($(this).find('rec_reports').text()){
							TempContentRec+='Отчёты: <ul>';
							$(this).find('report').each(function(){
								TempContentRec+='<li>'+$(this).find('report_name').text()+' ['+$(this).find('report_id').text()+']';

								if($(this).find('report_parameters').text()){
									TempContentRec+=' с параметрами: <ul>';
									$(this).find('report_parameters').find('parameter').each(function(){
										TempContentRec+='<li>'+$(this).find('parameter_name').text()+' <font size="1">['+$(this).find('parameter_tech_name').text()+']</font>';
										TempContentRec+='</li>';
									});
									TempContentRec+='</ul>';
								}


								TempContentRec+='</li>';
							});
							TempContentRec+='</ul>';
						}
						TempContentRec+='</div>';

						TempContentRec+='</div>';

						if(FLAG == 0){
							TempRecsNum++;
							TempContent+=TempContentRec;
						}
					});

					TempContent+='</div>';

					///ПАГИНАЦИЯ
					TempContent+='<div class="pages">';
					if(parseInt($(Recs).find('counter').text()) > parseInt($(Recs).find('to').text())){
						TempContent+='<div class="info">'+$(Recs).find('from').text()+' - '+$(Recs).find('to').text()+' из '+$(Recs).find('counter').text()+' записей</div>';
					}else{
						TempContent+='<div class="info">'+$(Recs).find('from').text()+' - '+$(Recs).find('counter').text()+' из '+$(Recs).find('counter').text()+' записей</div>';
					}
					if(parseInt($(Recs).find('current').text()) > 1){
						TempContent+='<div class="first" onclick="ListShowServices(1,\''+$(Recs).find('sort').text()+'\')")"></div>';
						TempContent+='<div class="previous" onclick="ListShowServices('+(parseInt($(Recs).find('current').text())-1)+',\''+$(Recs).find('sort').text()+'\')")"></div>';
					}
					
					//
					if($(Recs).find('current').text() > 3){
						for(let i=parseInt($(Recs).find('current').text())-2;i<=parseInt($(Recs).find('current').text())+2;i++){
							if(i <= (parseInt($(Recs).find('last').text())+1)){
								TempContent+='<div ';
								if($(Recs).find('current').text() == i){
									TempContent+='class="current" ';
								}
								TempContent+='onclick="ListShowServices('+i+',\''+$(Recs).find('sort').text()+'\')")">' + i + '</div>';
							}
						}
					}else{
						for(let i=1;i<=5;i++){
							if(i <= (parseInt($(Recs).find('last').text())+1)){
								TempContent+='<div ';
								if($(Recs).find('current').text() == i){
									TempContent+='class="current" ';
								}
								TempContent+='onclick="ListShowServices('+i+',\''+$(Recs).find('sort').text()+'\')">' + i + '</div>';
							}
						}
					}
						
					if(parseInt($(Recs).find('current').text()) < (parseInt($(Recs).find('last').text())+1)){
						TempContent+='<div class="next" onclick="ListShowServices('+(parseInt($(Recs).find('current').text())+1)+',\''+$(Recs).find('sort').text()+'\')")"></div>';
						TempContent+='<div class="last" onclick="ListShowServices('+(parseInt($(Recs).find('last').text())+1)+',\''+$(Recs).find('sort').text()+'\')")"></div>';
					}

					TempContent+='</div>';
					///ПАГИНАЦИЯ
				}

				if($(Recs).find('message').text() == 'service_not_selected'){
					
				}else{
					if(TempRecsNum == 0){
						TempContent='<div class="message">По данному запросу не найдено записей.</div>';
					}
				}
				document.getElementById('ListRecs').innerHTML=TempContent;
			}
		}
	});
}