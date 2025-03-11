var currentPage = '';
var currentSort = '';
var currentDirection = 0;
var ArrayRecs=new Array();
var ArrayFields=new Array();
var DuplicateOffset = 1;

function listShowDuplicates(Page = 1, Sort = null, Direction = 0){
	if(currentSort == Sort && currentPage == Page){if(currentDirection == 1){currentDirection = 0;}else{currentDirection = 1;}}
	if((currentSort == '' && Sort != null) || (currentSort != Sort)){currentSort = Sort;}
	if((currentPage == '' && Page != null) || (currentPage != Page)){currentPage = Page;}

	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#form_search_recs").serialize()+'&page='+Page+'&sort='+Sort+'&direction='+currentDirection+'&action=recs&mode=xml',
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
					
					if($('#tab').val() == 1){
						TempContent+='<th><div id="fullname" class="sort" onclick="listShowDuplicates('+Page+', \'fullname\');">ФИО владельца</div></th>';
						TempContent+='<th><div id="address" class="sort" onclick="listShowDuplicates('+Page+', \'address\');">Адрес фактический</div></th>';
						TempContent+='<th><div id="fact_address" class="sort" onclick="listShowDuplicates('+Page+', \'fact_address\');">Адрес регистрации</div></th>';
						TempContent+='<th><div id="birthday" class="sort" onclick="listShowDuplicates('+Page+', \'birthday\');">Дата рождения</div></th>';
						TempContent+='<th><div id="telephone" class="sort" onclick="listShowDuplicates('+Page+', \'telephone\');">Телефон</div></th>';
						TempContent+='<th><div id="email" class="sort" onclick="listShowDuplicates('+Page+', \'email\');">Электронная почта</div></th>';
						TempContent+='<th><div id="last_visit" class="sort" onclick="listShowDuplicates('+Page+', \'last_visit\');">Дата последнего приема</div></th>';
						TempContent+='<th><div id="num_duplicates" class="sort" onclick="listShowDuplicates('+Page+', \'num_duplicates\');">Кол-во дублей</div></th>';
					}else{
						TempContent+='<th><div id="specie" class="sort" onclick="listShowDuplicates('+Page+', \'specie\');">Вид животного</div></th>';
						TempContent+='<th><div id="breed" class="sort" onclick="listShowDuplicates('+Page+', \'breed\');">Порода</div></th>';
						TempContent+='<th><div id="name" class="sort" onclick="listShowDuplicates('+Page+', \'name\');">Кличка</div></th>';
						TempContent+='<th><div id="address" class="sort" onclick="listShowDuplicates('+Page+', \'address\');">Адрес содержания животного</div></th>';
						TempContent+='<th><div id="age" class="sort" onclick="listShowDuplicates('+Page+', \'age\');">Возраст</div></th>';
						TempContent+='<th><div id="last_visit" class="sort" onclick="listShowDuplicates('+Page+', \'last_visit\');">Дата последнего приема</div></th>';
						TempContent+='<th><div id="num_duplicates" class="sort" onclick="listShowDuplicates('+Page+', \'num_duplicates\');">Кол-во дублей</div></th>';
					}

					TempContent+='<th></th>';
					TempContent+='</tr>';

					$(Recs).find('rec').each(function(){
						let FLAG=0;

						let TempContentRec='<tr>';
						if($('#tab').val() == 1){
							TempContentRec+='<td>' + $(this).find('rec_fullname').text()+'</td>';
							TempContentRec+='<td>' + $(this).find('rec_address').text()+'</td>';
							TempContentRec+='<td>' + $(this).find('rec_fact_address').text()+'</td>';
							TempContentRec+='<td>' + $(this).find('rec_birthday').text()+'</td>';
							TempContentRec+='<td>' + $(this).find('rec_telephone').text()+'</td>';
							TempContentRec+='<td>' + $(this).find('rec_email').text()+'</td>';
							TempContentRec+='<td>' + $(this).find('rec_last_visit').text()+'</td>';
							TempContentRec+='<td>' + $(this).find('rec_num_duplicates').text()+'</td>';
						}else{
							TempContentRec+='<td>' + $(this).find('rec_specie').text()+'</td>';
							TempContentRec+='<td>' + $(this).find('rec_breed').text()+'</td>';
							TempContentRec+='<td>' + $(this).find('rec_name').text()+'</td>';
							TempContentRec+='<td>' + $(this).find('rec_address').text()+'</td>';
							// TempContentRec+='<td>' + $(this).find('rec_birthday').text()+'</td>';
							TempContentRec+='<td>' + $(this).find('rec_age').text()+'</td>';
							TempContentRec+='<td>' + $(this).find('rec_last_visit').text()+'</td>';
							TempContentRec+='<td>' + $(this).find('rec_num_duplicates').text()+'</td>';
						}
						TempContentRec+='<td style="width: 32px;">';
						TempContentRec+='<a href="./?action=edit&id='+$(this).find('rec_id').text()+'">';
						TempContentRec+='<div class="edit"></div>';
						TempContentRec+='</a>';
						TempContentRec+='</td>';
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
						TempContent+='<div class="first" onclick="listShowDuplicates(1,\''+$(Recs).find('sort').text()+'\')")"></div>';
						TempContent+='<div class="previous" onclick="listShowDuplicates('+(parseInt($(Recs).find('current').text())-1)+',\''+$(Recs).find('sort').text()+'\')")"></div>';
					}
					if($(Recs).find('current').text() > 3){
						for(let i=parseInt($(Recs).find('current').text())-2;i<=parseInt($(Recs).find('current').text())+2;i++){
							if(i <= (parseInt($(Recs).find('last').text())+1)){
								TempContent+='<div ';
								if($(Recs).find('current').text() == i){
									TempContent+='class="current" ';
								}
								TempContent+='onclick="listShowDuplicates('+i+',\''+$(Recs).find('sort').text()+'\')")">' + i + '</div>';
							}
						}
					}else{
						for(let i=1;i<=5;i++){
							if(i <= (parseInt($(Recs).find('last').text())+1)){
								TempContent+='<div ';
								if($(Recs).find('current').text() == i){
									TempContent+='class="current" ';
								}
								TempContent+='onclick="listShowDuplicates('+i+',\''+$(Recs).find('sort').text()+'\')">' + i + '</div>';
							}
						}
					}
						
					if(parseInt($(Recs).find('current').text()) < (parseInt($(Recs).find('last').text())+1)){
						TempContent+='<div class="next" onclick="listShowDuplicates('+(parseInt($(Recs).find('current').text())+1)+',\''+$(Recs).find('sort').text()+'\')")"></div>';
						TempContent+='<div class="last" onclick="listShowDuplicates('+(parseInt($(Recs).find('last').text())+1)+',\''+$(Recs).find('sort').text()+'\')")"></div>';
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
					$('#'+$(Recs).find('sort').text()).addClass('down');
				}else{
					currentDirection=0;
					$('#'+$(Recs).find('sort').text()).addClass('up');
				}
				if(Sort == null && $(Recs).find('sort').text() != ''){
					currentSort = $(Recs).find('sort').text();
				}
			}
		}
	});
}

// Реестр дубликатов автоматического объединения
function listShowAutoDuplicates(Page = 1, Sort = null, Direction = 0){
	if(currentSort == Sort && currentPage == Page){if(currentDirection == 1){currentDirection = 0;}else{currentDirection = 1;}}
	if((currentSort == '' && Sort != null) || (currentSort != Sort)){currentSort = Sort;}
	if((currentPage == '' && Page != null) || (currentPage != Page)){currentPage = Page;}

	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#form_search_auto_recs").serialize()+'&page='+Page+'&sort='+Sort+'&direction='+currentDirection+'&action=auto_recs&mode=xml',
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

					if($('#tab').val() == 1){
						// заготовка для сортировки - если надо - добавить в код
						//class="sort" onclick="listShowAutoDuplicates('+Page+', \'join_date\')
						TempContent+='<th><div id="join_date">Дата склейки</div></th>';
						TempContent+='<th><div id="id_rec">ID</div></th>';
						TempContent+='<th><div id="fullname">ФИО владельца</div></th>';
						TempContent+='<th><div id="telephone">Телефон</div></th>';
						TempContent+='<th><div id="fact_address">Адрес регистрации</div></th>';
						TempContent+='<th><div id="address">Адрес фактический</div></th>';
						TempContent+='<th><div id="duble_cards">Связанные архивы</div></th>';
						TempContent+='<th><div id="duble_animals">Животные</div></th>';

					}else{
						TempContent+='<th><div id="join_date">Дата склейки</div></th>';
						TempContent+='<th><div id="id_owner">ID владельца</div></th>';
						TempContent+='<th><div id="id_pet">ID животного</div></th>';
						TempContent+='<th><div id="specie">Вид</div></th>';
						TempContent+='<th><div id="breed">Порода</div></th>';
						TempContent+='<th><div id="name">Кличка</div></th>';
					}

					TempContent+='<th></th>';
					TempContent+='</tr>';

					$(Recs).find('rec').each(function(){
						let FLAG=0;

						let TempContentRec='<tr>';
						if($('#tab').val() == 1){
							TempContentRec+='<td>' + $(this).find('rec_join_date').text()+'</td>';
							TempContentRec+='<td>' + $(this).find('rec_id').text() + '</td>';
							TempContentRec+='<td>' + $(this).find('rec_fullname').text()+'</td>';
							TempContentRec+='<td>' + $(this).find('rec_telephone').text()+'</td>';
							TempContentRec+='<td>' + $(this).find('rec_fact_address').text()+'</td>';
							TempContentRec+='<td>' + $(this).find('rec_address').text()+'</td>';

							let cardDuplicatesID = $(this).find('rec_card_duplicates').text();
							let cardDuplicatesIDArray = cardDuplicatesID.replace(/{|}/g, '').split(',');
							let cardDuplicatesListItems = '<ul style="list-style-type: none; padding-left: 0px;;">';
							cardDuplicatesIDArray.forEach(function(duplicate) {
								cardDuplicatesListItems +=
									'<li>'+
									duplicate.trim()
									+'</li>';
							});
							cardDuplicatesListItems += '</ul>';
							TempContentRec += '<td>' + cardDuplicatesListItems + '</td>';

							let animalsDuplicatesID = $(this).find('rec_animals_duplicates').text();
							let animalsDuplicatesIDArray = animalsDuplicatesID.replace(/{|}/g, '').split(',');
							let animalsDuplicatesListItems = '<ul style="list-style-type: none; padding-left: 0px;;">';
							animalsDuplicatesIDArray.forEach(function(duplicate) {
								animalsDuplicatesListItems +=
									'<li>' +
									duplicate.trim()
									+ '</li>';
							});
							animalsDuplicatesListItems += '</ul>';
							TempContentRec += '<td>' + animalsDuplicatesListItems + '</td>';

						}else{
							TempContentRec+='<td>' + $(this).find('rec_join_date').text()+'</td>';
							TempContentRec+='<td>' + $(this).find('rec_id_owner').text()+'</td>';
							TempContentRec+='<td>' + $(this).find('rec_id_pet').text()+'</td>';
							TempContentRec+='<td>' + $(this).find('rec_specie').text()+'</td>';
							TempContentRec+='<td>' + $(this).find('rec_breed').text()+'</td>';
							TempContentRec+='<td>' + $(this).find('rec_name').text()+'</td>';
							}

						// Заготовка под редактирование данных
						// TempContentRec+='<td style="width: 32px;">';
						// TempContentRec+='<a href="./?action=edit&id='+$(this).find('rec_id').text()+'">';
						// TempContentRec+='<div class="edit"></div>';
						// TempContentRec+='</a>';
						// TempContentRec+='</td>';
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
						TempContent+='<div class="first" onclick="listShowAutoDuplicates(1,\''+$(Recs).find('sort').text()+'\')")"></div>';
						TempContent+='<div class="previous" onclick="listShowAutoDuplicates('+(parseInt($(Recs).find('current').text())-1)+',\''+$(Recs).find('sort').text()+'\')")"></div>';
					}
					if($(Recs).find('current').text() > 3){
						for(let i=parseInt($(Recs).find('current').text())-2;i<=parseInt($(Recs).find('current').text())+2;i++){
							if(i <= (parseInt($(Recs).find('last').text())+1)){
								TempContent+='<div ';
								if($(Recs).find('current').text() == i){
									TempContent+='class="current" ';
								}
								TempContent+='onclick="listShowAutoDuplicates('+i+',\''+$(Recs).find('sort').text()+'\')")">' + i + '</div>';
							}
						}
					}else{
						for(let i=1;i<=5;i++){
							if(i <= (parseInt($(Recs).find('last').text())+1)){
								TempContent+='<div ';
								if($(Recs).find('current').text() == i){
									TempContent+='class="current" ';
								}
								TempContent+='onclick="listShowAutoDuplicates('+i+',\''+$(Recs).find('sort').text()+'\')">' + i + '</div>';
							}
						}
					}

					if(parseInt($(Recs).find('current').text()) < (parseInt($(Recs).find('last').text())+1)){
						TempContent+='<div class="next" onclick="listShowAutoDuplicates('+(parseInt($(Recs).find('current').text())+1)+',\''+$(Recs).find('sort').text()+'\')")"></div>';
						TempContent+='<div class="last" onclick="listShowAutoDuplicates('+(parseInt($(Recs).find('last').text())+1)+',\''+$(Recs).find('sort').text()+'\')")"></div>';
					}

					TempContent+='</div>';
				}

				if(TempRecsNum == 0){
					TempContent='<div class="message">По данному запросу не найдено записей.</div>';
				}
				document.getElementById('ListRecs').innerHTML=TempContent;

				if($(Recs).find('direction').text() == '1'){
					currentDirection=1;
					$('#'+$(Recs).find('sort').text()).addClass('down');
				}else{
					currentDirection=0;
					$('#'+$(Recs).find('sort').text()).addClass('up');
				}
				if(Sort == null && $(Recs).find('sort').text() != ''){
					currentSort = $(Recs).find('sort').text();
				}
			}
		}
	});
}

// АРХИВ ДУБЛЕЙ
function listShowArchiveDuplicates(Page = 1, Sort = null, Direction = 0){
	if(currentSort == Sort && currentPage == Page){if(currentDirection == 1){currentDirection = 0;}else{currentDirection = 1;}}
	if((currentSort == '' && Sort != null) || (currentSort != Sort)){currentSort = Sort;}
	if((currentPage == '' && Page != null) || (currentPage != Page)){currentPage = Page;}

	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#form_search_archive_recs").serialize()+'&page='+Page+'&sort='+Sort+'&direction='+currentDirection+'&action=archive_recs&mode=xml',
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

					// if($('#tab').val() == 1){
						// заготовка для сортировки - если надо - добавить в код
						//class="sort" onclick="listShowAutoDuplicates('+Page+', \'join_date\')
						TempContent+='<th><div id="archive_date">Дата архивирования</div></th>';
						TempContent+='<th><div id="id_rec">ID</div></th>';
						TempContent+='<th><div id="fullname">ФИО владельца</div></th>';
						TempContent+='<th><div id="fact_address">Адрес регистрации</div></th>';
						TempContent+='<th><div id="address">Адрес фактический</div></th>';
						TempContent+='<th><div id="main_card">Основная карта</div></th>';
					// }


					TempContent+='<th></th>';
					TempContent+='</tr>';

					$(Recs).find('rec').each(function(){
						let FLAG=0;

						let TempContentRec='<tr>';
						// if($('#tab').val() == 1){
							TempContentRec+='<td>' + $(this).find('rec_archive_date').text()+'</td>';
							TempContentRec+='<td>' + $(this).find('rec_id').text() + '</td>';
							TempContentRec+='<td>' + $(this).find('rec_fullname').text()+'</td>';
							TempContentRec+='<td>' + $(this).find('rec_fact_address').text()+'</td>';
							TempContentRec+='<td>' + $(this).find('rec_address').text()+'</td>';
							TempContentRec+='<td>' + $(this).find('rec_main_card').text()+'</td>';
						// }

						// Заготовка под редактирование данных
						// TempContentRec+='<td style="width: 32px;">';
						// TempContentRec+='<a href="./?action=edit&id='+$(this).find('rec_id').text()+'">';
						// TempContentRec+='<div class="edit"></div>';
						// TempContentRec+='</a>';
						// TempContentRec+='</td>';

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
						TempContent+='<div class="first" onclick="listShowArchiveDuplicates(1,\''+$(Recs).find('sort').text()+'\')")"></div>';
						TempContent+='<div class="previous" onclick="listShowArchiveDuplicates('+(parseInt($(Recs).find('current').text())-1)+',\''+$(Recs).find('sort').text()+'\')")"></div>';
					}
					if($(Recs).find('current').text() > 3){
						for(let i=parseInt($(Recs).find('current').text())-2;i<=parseInt($(Recs).find('current').text())+2;i++){
							if(i <= (parseInt($(Recs).find('last').text())+1)){
								TempContent+='<div ';
								if($(Recs).find('current').text() == i){
									TempContent+='class="current" ';
								}
								TempContent+='onclick="listShowArchiveDuplicates('+i+',\''+$(Recs).find('sort').text()+'\')")">' + i + '</div>';
							}
						}
					}else{
						for(let i=1;i<=5;i++){
							if(i <= (parseInt($(Recs).find('last').text())+1)){
								TempContent+='<div ';
								if($(Recs).find('current').text() == i){
									TempContent+='class="current" ';
								}
								TempContent+='onclick="listShowArchiveDuplicates('+i+',\''+$(Recs).find('sort').text()+'\')">' + i + '</div>';
							}
						}
					}

					if(parseInt($(Recs).find('current').text()) < (parseInt($(Recs).find('last').text())+1)){
						TempContent+='<div class="next" onclick="listShowArchiveDuplicates('+(parseInt($(Recs).find('current').text())+1)+',\''+$(Recs).find('sort').text()+'\')")"></div>';
						TempContent+='<div class="last" onclick="listShowArchiveDuplicates('+(parseInt($(Recs).find('last').text())+1)+',\''+$(Recs).find('sort').text()+'\')")"></div>';
					}

					TempContent+='</div>';
				}

				if(TempRecsNum == 0){
					TempContent='<div class="message">По данному запросу не найдено записей.</div>';
				}
				document.getElementById('ListRecs').innerHTML=TempContent;

				if($(Recs).find('direction').text() == '1'){
					currentDirection=1;
					$('#'+$(Recs).find('sort').text()).addClass('down');
				}else{
					currentDirection=0;
					$('#'+$(Recs).find('sort').text()).addClass('up');
				}
				if(Sort == null && $(Recs).find('sort').text() != ''){
					currentSort = $(Recs).find('sort').text();
				}
			}
		}
	});
}

function showDuplicatesAddRecWindow(){
	let TempContent='';

	TempContent+='<div id="duplicates_add_rec_window" class="window main_row col-xl-4 col-lg-6 col-12">';
	//
	TempContent+='<div class="close" onclick="closeDuplicatesAddRecWindow();"></div>';
	TempContent+='<div class="top">Сравнить по ID</div>';

	TempContent+='<div class="error" id="duplicates_add_rec_window_message" style="display: none; margin: 15px;"></div>';

	TempContent+='<div class="col-12">';
	TempContent+='<form class="form-window" id="add_duplicates_recs">';

	TempContent+='<div class="row">';

	TempContent+='<div class="col-6 row">';
	TempContent+='<div class="col-2 row-justify-content">';
	TempContent+='<input type="radio" name="type" id="type_1" value="1" checked>';
	TempContent+='</div>';
	TempContent+='<div class="col-10">';
	TempContent+='<label for="type_1">Владельцы</label>';
	TempContent+='</div>';
	TempContent+='</div>';

	TempContent+='<div class="col-6 row">';
	TempContent+='<div class="col-2 row-justify-content">';
	TempContent+='<input type="radio" name="type" id="type_2" value="2">';
	TempContent+='</div>';
	TempContent+='<div class="col-10">';
	TempContent+='<label for="type_2">Животные</label>';
	TempContent+='</div>';
	TempContent+='</div>';

	TempContent+='</div>';


	TempContent+='<div class="row" style="margin-top: 15px;">';

	TempContent+='<div class="col-6 row">';
	TempContent+='<div class="col-6">';
	TempContent+='ID №1';
	TempContent+='</div>';
	TempContent+='<div class="col-6">';
	TempContent+='<input type="number" name="id1" id="id1" value="">';
	TempContent+='</div>';
	TempContent+='</div>';

	TempContent+='<div class="col-6 row">';
	TempContent+='<div class="col-6">';
	TempContent+='ID №2';
	TempContent+='</div>';
	TempContent+='<div class="col-6">';
	TempContent+='<input type="number" name="id2" id="id2" value="">';
	TempContent+='</div>';
	TempContent+='</div>';

	TempContent+='</div>';

	
	TempContent+='</form>';
	TempContent+='</div>';

	//Кнопки управления
	TempContent+='<div class="controls">';
	TempContent+='<button class="button" id="duplicates_type_button" style="width: 250px;" onclick="addDuplicatesRecs();">Сравнить</button>';
	TempContent+='</div>';
	//Кнопки управления
	
	TempContent+='</div>';

	document.getElementById("sub_container").innerHTML=TempContent;
	$("#background").fadeIn();
	$("#container").show();
}
function closeDuplicatesAddRecWindow(){
	$("#background").fadeOut();
	$("#container").hide();
}

function showDuplicatesAddDuplicateWindow(){
	let TempContent='';

	TempContent+='<div id="duplicates_add_duplicate_window" class="window main_row col-xl-2 col-lg-4 col-12">';
	//
	TempContent+='<div class="close" onclick="closeDuplicatesAddDuplicateWindow();"></div>';
	TempContent+='<div class="top">Добавить к сравнению</div>';

	TempContent+='<div class="error" id="duplicates_add_duplicate_window_message" style="display: none; margin: 15px;"></div>';

	TempContent+='<div class="col-12">';
	TempContent+='<form class="form-window" id="add_duplicates_duplicate">';

	TempContent+='<div class="row">';

	TempContent+='<div class="col-12">ID</div>';
	TempContent+='<div class="col-12"><input type="number" name="id" id="id" value=""></div>';
	TempContent+='</div>';

	TempContent+='</form>';
	TempContent+='</div>';

	//Кнопки управления
	TempContent+='<div class="controls">';
	TempContent+='<button class="button" id="duplicates_type_button" style="width: 250px;" onclick="addDuplicatesDuplicate();">Добавить</button>';
	TempContent+='</div>';
	//Кнопки управления
	
	TempContent+='</div>';

	document.getElementById("sub_container").innerHTML=TempContent;
	$("#background").fadeIn();
	$("#container").show();
}
function closeDuplicatesAddDuplicateWindow(){
	$("#background").fadeOut();
	$("#container").hide();
}

function addDuplicatesDuplicate(){
	if($('#duplicates_add_duplicate_window #id').val() == ''){
		$("#duplicates_add_duplicate_window_message").html('Не заполнены необходимые поля.');
		$("#duplicates_add_duplicate_window_message").show();

		return true;
	}

	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#add_duplicates_duplicate").serialize()+'&duplicate='+$("#duplicate").val()+'&action=add_duplicate&mode=xml',
		'url': "index.php",
		'beforeSend': function() {
			//показать загрузку
			showTopLoading();
		},
		'success': function (Data) {
			if($(Data).find('message').text() == 'token_invalid'){
				window.location.href = 'index.php';
			}else{
				if($(Data).find('message').text() == 'wrong_id'){
					$("#duplicates_add_duplicate_window_message").html('Введен неверный ID.');
					$("#duplicates_add_duplicate_window_message").show();
					closeTopLoading();

					return true;
				}
				if($(Data).find('message').text() == 'duplicate_added'){
					showDuplicate($("#duplicate").val());
					closeDuplicatesAddDuplicateWindow();
					closeTopLoading();
				}
			}
		}
	});
}

function addDuplicatesRecs(){
	if($('#add_duplicates_recs #id1').val() == '' || $('#add_duplicates_recs #id2').val() == ''){
		$("#duplicates_add_rec_window_message").html('Не заполнены необходимые поля.');
		$("#duplicates_add_rec_window_message").show();

		return true;
	}
	
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': jQuery("#add_duplicates_recs").serialize()+'&action=create_rec&mode=xml',
		'url': "index.php",
		'beforeSend': function() {
			//показать загрузку
			showTopLoading();
		},
		'success': function (Data) {
			if($(Data).find('message').text() == 'token_invalid'){
				window.location.href = 'index.php';
			}else{
				if($(Data).find('message').text() == 'rec_added'){
					closeTopLoading();
					closeDuplicatesAddRecWindow();
					window.location.href = 'index.php?action=edit&id='+$(Data).find('id').text()+'';
				}
			}
		}
	});
}

function showDuplicate_(Mode = null){
	let cols = 2;
	let cols_m = 2;
	if(ArrayRecs.length == 2){
		cols = 5;
	}else if(ArrayRecs.length == 3){
		cols_m = 3;
		cols = 3;
	}else if(ArrayRecs.length == 4){
		cols_m = 4;
		cols = 2;
	}

	let TempContent='';
	
	TempContent+='<div class="col-'+cols_m+' title"></div>';

	for(let i=0; i<=ArrayRecs.length-1; i++){
		if(ArrayRecs[i].main == 1){
			TempContent+='<div class="col-'+cols+'';
			TempContent+=' main title_';
			TempContent+='" title="'+ArrayRecs[i].id+'">Основной</div>';
		}
	}

	let counter = 0;
	for(let i=DuplicateOffset; i<=ArrayRecs.length-1; i++){
		if(counter < 4 && ArrayRecs[i].main != 1){
			TempContent+='<div id="'+ArrayRecs[i].id+'" class="not_main col-'+cols+'" onclick="chooseMainDuplicate(this);" title="'+ArrayRecs[i].id+'">';
			TempContent+='Сделать основным';
			TempContent+='<div class="delete" onclick="deleteDuplicate('+ArrayRecs[i].id+');"></div>';
			TempContent+='</div>';
			counter++;
		}
	}

	for(let i=0; i<=ArrayFields.length-1; i++){
		if((Mode == null || Mode == 1 || Mode == 2) || (Mode == 3 && ArrayFields[i].important == 1)){
			//поиск различий
			let flag_razl = 0;
			if(Mode == 2){
				let main_val = '';
				for(let j=0; j<=ArrayRecs.length-1; j++){
					if(ArrayRecs[j].main == 1 && ArrayFields[i].name != ''){
						let v='ArrayRecs[j].'+ArrayFields[i].name+'';
						main_val=eval(v);
					}
				}	
				
				for(let j=0; j<=ArrayRecs.length-1; j++){
					if(ArrayRecs[j].main == 0 && ArrayFields[i].name != ''){
						let v='ArrayRecs[j].'+ArrayFields[i].name+'';
						val_=eval(v);
						if(main_val != val_){
							flag_razl = 1;
						}
					}

				}
			}
			//поиск различий

			if((flag_razl == 1 && Mode == 2) || Mode != 2){
				TempContent+='<div class="col-'+cols_m+' title row-center-align';
				if(!ArrayFields[i].name){TempContent+=' title_main row-center-align';}
				TempContent+='">';
				TempContent+=''+ArrayFields[i].title+'';
				TempContent+='</div>';

				//найти основное поле чтобы сравнивать с другими

				let counter = 0;


				for(let j=0; j<=ArrayRecs.length-1; j++){
					if(ArrayRecs[j].main == 1){
						let val_='';
						if(ArrayFields[i].name != ''){
							let v='ArrayRecs[j].'+ArrayFields[i].name+'';
							val_=eval(v);
							
							if(val_){
								TempContent+='<div class="col-'+cols+'';
								if(ArrayRecs[j].main == 1){
									TempContent+=' main';
								}
								TempContent+='">';

								if(ArrayRecs[j].main == 0 && ArrayFields[i].info != 1){
									TempContent+='<label class="checkbox_container">';
									TempContent+='<input type="checkbox" name="'+ArrayFields[i].name+'_'+ArrayRecs[j].id+'" id="'+ArrayFields[i].name+'_'+ArrayRecs[j].id+'" ';

									let v_='ArrayRecs[j].'+ArrayFields[i].name+'_ch';
									val__=eval(v_);
									if(val__ == 1){
										TempContent+='checked ';
									}

									TempContent+='value="1" onclick="chooseMainDuplicateField(this);">';
								}

								let birthday_f = 0;
								let fullname_f = 0;
								let name_f = 0;
								let telephone_f = 0;
								let email_f = 0;
								let address_f = 0;
								let factadd_f = 0;
								let specie_f = 0;
								let breed_f = 0;

								let chip_f = 0;
								let label_f = 0;

								if((
									ArrayFields[i].name == 'telephone' || 
									ArrayFields[i].name == 'email' || 
									ArrayFields[i].name == 'fullname' || 
									ArrayFields[i].name == 'name' || 
									ArrayFields[i].name == 'birthday' || 
									ArrayFields[i].name == 'address' || 
									ArrayFields[i].name == 'factadd' || 
									ArrayFields[i].name == 'chip' || 
									ArrayFields[i].name == 'label' || 
									ArrayFields[i].name == 'specie' || 
									ArrayFields[i].name == 'breed'
								) && ArrayRecs[j].main == 0){
									//проверяем отличие от основной записи
									for(let k=0; k<=ArrayRecs.length-1; k++){
										if(ArrayRecs[k].main == 1 && ArrayRecs[k].telephone != ArrayRecs[j].telephone && ArrayFields[i].name == 'telephone'){
											telephone_f = 1;
										}
										if(ArrayRecs[k].main == 1 && ArrayRecs[k].email != ArrayRecs[j].email && ArrayFields[i].name == 'email'){
											email_f = 1;
										}
										if(ArrayRecs[k].main == 1 && ArrayRecs[k].birthday != ArrayRecs[j].birthday && ArrayFields[i].name == 'birthday'){
											birthday_f = 1;
										}
										if(ArrayRecs[k].main == 1 && ArrayRecs[k].fullname != ArrayRecs[j].fullname && ArrayFields[i].name == 'fullname'){
											fullname_f = 1;
										}
										if(ArrayRecs[k].main == 1 && ArrayRecs[k].name != ArrayRecs[j].name && ArrayFields[i].name == 'name'){
											name_f = 1;
										}
										if(ArrayRecs[k].main == 1 && ArrayRecs[k].address != ArrayRecs[j].address && ArrayFields[i].name == 'address'){
											address_f = 1;
										}
										if(ArrayRecs[k].main == 1 && ArrayRecs[k].factadd != ArrayRecs[j].factadd && ArrayFields[i].name == 'factadd'){
											factadd_f = 1;
										}
										if(ArrayRecs[k].main == 1 && ArrayRecs[k].specie != ArrayRecs[j].specie && ArrayFields[i].name == 'specie'){
											specie_f = 1;
										}
										if(ArrayRecs[k].main == 1 && ArrayRecs[k].breed != ArrayRecs[j].breed && ArrayFields[i].name == 'breed'){
											breed_f = 1;
										}

										if(ArrayRecs[k].main == 1 && ArrayRecs[k].chip != ArrayRecs[j].chip && ArrayFields[i].name == 'chip'){
											chip_f = 1;
										}
										if(ArrayRecs[k].main == 1 && ArrayRecs[k].label != ArrayRecs[j].label && ArrayFields[i].name == 'label'){
											label_f = 1;
										}
									}
								}
								if(telephone_f || email_f || fullname_f || name_f || birthday_f || address_f || factadd_f || specie_f || breed_f || chip_f || label_f){TempContent+='<span>';}
								TempContent+=''+val_+'';
								if(ArrayFields[i].name == 'percent'){
									TempContent+='<div class="percent"><div style="width: '+val_+'%;"></div></div>';
								}
								if(telephone_f || email_f || fullname_f || name_f || birthday_f || address_f || factadd_f || specie_f || breed_f || chip_f || label_f){TempContent+='</span>';}

								if(ArrayRecs[j].main == 0 && ArrayFields[i].info != 1){
									TempContent+='<span class="checkbox_checkmark checkbox_checkmark_round"></span>';
									TempContent+='</label>';
								}
								
								TempContent+='</div>';
								TempContent+='</div>';
							}else{
								TempContent+='<div class="col-'+cols+' empty';
								if(ArrayRecs[j].main == 1){
									TempContent+=' main';
								}
								TempContent+='">';
								TempContent+='Нет данных';
								TempContent+='</div>';
							}
							
						}else{
							TempContent+='<div class="col-'+cols+'';
							if(ArrayRecs[j].main == 1){
								TempContent+=' main';
							}
							TempContent+='"></div>';
						}
					}
				}


				for(let j=DuplicateOffset; j<=ArrayRecs.length-1; j++){
					if(counter < 4 && ArrayRecs[j].main != 1){
						let val_='';
						if(ArrayFields[i].name != ''){
							let v='ArrayRecs[j].'+ArrayFields[i].name+'';
							val_=eval(v);
							
							if(val_){
								TempContent+='<div class="col-'+cols+'';
								if(ArrayRecs[j].main == 1){
									TempContent+=' main';
								}
								TempContent+='">';

								if(ArrayRecs[j].main == 0 && ArrayFields[i].info != 1){
									TempContent+='<label class="checkbox_container">';
									TempContent+='<input type="checkbox" name="'+ArrayFields[i].name+'_'+ArrayRecs[j].id+'" id="'+ArrayFields[i].name+'_'+ArrayRecs[j].id+'" ';

									let v_='ArrayRecs[j].'+ArrayFields[i].name+'_ch';
									val__=eval(v_);
									if(val__ == 1){
										TempContent+='checked ';
									}

									TempContent+='value="1" onclick="chooseMainDuplicateField(this);">';
								}

								let birthday_f = 0;
								let fullname_f = 0;
								let name_f = 0;
								let telephone_f = 0;
								let email_f = 0;
								let address_f = 0;
								let factadd_f = 0;
								let specie_f = 0;
								let breed_f = 0;

								let chip_f = 0;
								let label_f = 0;

								if((
									ArrayFields[i].name == 'telephone' || 
									ArrayFields[i].name == 'email' || 
									ArrayFields[i].name == 'fullname' || 
									ArrayFields[i].name == 'name' || 
									ArrayFields[i].name == 'birthday' || 
									ArrayFields[i].name == 'address' || 
									ArrayFields[i].name == 'factadd' || 
									ArrayFields[i].name == 'chip' || 
									ArrayFields[i].name == 'label' || 
									ArrayFields[i].name == 'specie' || 
									ArrayFields[i].name == 'breed'
								) && ArrayRecs[j].main == 0){
									//проверяем отличие от основной записи
									for(let k=0; k<=ArrayRecs.length-1; k++){
										if(ArrayRecs[k].main == 1 && ArrayRecs[k].telephone != ArrayRecs[j].telephone && ArrayFields[i].name == 'telephone'){
											telephone_f = 1;
										}
										if(ArrayRecs[k].main == 1 && ArrayRecs[k].email != ArrayRecs[j].email && ArrayFields[i].name == 'email'){
											email_f = 1;
										}
										if(ArrayRecs[k].main == 1 && ArrayRecs[k].birthday != ArrayRecs[j].birthday && ArrayFields[i].name == 'birthday'){
											birthday_f = 1;
										}
										if(ArrayRecs[k].main == 1 && ArrayRecs[k].fullname != ArrayRecs[j].fullname && ArrayFields[i].name == 'fullname'){
											fullname_f = 1;
										}
										if(ArrayRecs[k].main == 1 && ArrayRecs[k].name != ArrayRecs[j].name && ArrayFields[i].name == 'name'){
											name_f = 1;
										}
										if(ArrayRecs[k].main == 1 && ArrayRecs[k].address != ArrayRecs[j].address && ArrayFields[i].name == 'address'){
											address_f = 1;
										}
										if(ArrayRecs[k].main == 1 && ArrayRecs[k].factadd != ArrayRecs[j].factadd && ArrayFields[i].name == 'factadd'){
											factadd_f = 1;
										}
										if(ArrayRecs[k].main == 1 && ArrayRecs[k].specie != ArrayRecs[j].specie && ArrayFields[i].name == 'specie'){
											specie_f = 1;
										}
										if(ArrayRecs[k].main == 1 && ArrayRecs[k].breed != ArrayRecs[j].breed && ArrayFields[i].name == 'breed'){
											breed_f = 1;
										}

										if(ArrayRecs[k].main == 1 && ArrayRecs[k].chip != ArrayRecs[j].chip && ArrayFields[i].name == 'chip'){
											chip_f = 1;
										}
										if(ArrayRecs[k].main == 1 && ArrayRecs[k].label != ArrayRecs[j].label && ArrayFields[i].name == 'label'){
											label_f = 1;
										}
									}
								}
								if(telephone_f || email_f || fullname_f || name_f || birthday_f || address_f || factadd_f || specie_f || breed_f || chip_f || label_f){TempContent+='<span>';}
								TempContent+=''+val_+'';
								if(ArrayFields[i].name == 'percent'){
									TempContent+='<div class="percent"><div style="width: '+val_+'%;"></div></div>';
								}
								if(telephone_f || email_f || fullname_f || name_f || birthday_f || address_f || factadd_f || specie_f || breed_f || chip_f || label_f){TempContent+='</span>';}

								if(ArrayRecs[j].main == 0 && ArrayFields[i].info != 1){
									TempContent+='<span class="checkbox_checkmark checkbox_checkmark_round"></span>';
									TempContent+='</label>';
								}
								
								TempContent+='</div>';
								TempContent+='</div>';
							}else{
								TempContent+='<div class="col-'+cols+' empty';
								if(ArrayRecs[j].main == 1){
									TempContent+=' main';
								}
								TempContent+='">';
								TempContent+='Нет данных';
								TempContent+='</div>';
							}
							
						}else{
							TempContent+='<div class="col-'+cols+'';
							if(ArrayRecs[j].main == 1){
								TempContent+=' main';
							}
							TempContent+='"></div>';
						}

						counter++;
					}
				}
			}
		}
	}

	if(ArrayRecs.length > 5){
		if(DuplicateOffset != 0){
			$("#arrow_left").show();
		}else{
			$("#arrow_left").hide();
		}

		if((6 + DuplicateOffset) <= ArrayRecs.length){
			$("#arrow_right").show();
		}else{
			$("#arrow_right").hide();
		}
	}

	document.getElementById('Duplicates').innerHTML=TempContent;
}

function showDuplicate9_(Mode = null){
	let cols = 2;
	let cols_m = 2;
	if(ArrayRecs.length == 2){
		cols = 5;
	}else if(ArrayRecs.length == 3){
		cols_m = 3;
		cols = 3;
	}else if(ArrayRecs.length == 4){
		cols_m = 4;
		cols = 2;
	}

	let TempContent='';
	
	TempContent+='<div class="col-'+cols_m+' title"></div>';

	// for(let i=0; i<=ArrayRecs.length-1; i++){
	// 	if(ArrayRecs[i].main == 1){
	// 		TempContent+='<div class="col-'+cols+'';
	// 		TempContent+=' main title_';
	// 		TempContent+='" title="'+ArrayRecs[i].id+'">Основной</div>';
	// 	}
	// }

	let counter = 0;
	for(let i=DuplicateOffset; i<=ArrayRecs.length-1; i++){
		if(counter < 5){
			if(ArrayRecs[i].main == 1){
				TempContent+='<div class="col-'+cols+'';
				TempContent+=' main title_';
				TempContent+='" title="'+ArrayRecs[i].id+'">Основной</div>';
			}else{
				TempContent+='<div id="'+ArrayRecs[i].id+'" class="not_main col-'+cols+'" onclick="chooseMainDuplicate(this);" title="'+ArrayRecs[i].id+'">';
				TempContent+='Сделать основным';
				TempContent+='<div class="delete" onclick="deleteDuplicate('+ArrayRecs[i].id+');"></div>';
				TempContent+='</div>';
			}
			counter++;
		}
	}

	for(let i=0; i<=ArrayFields.length-1; i++){
		if((Mode == null || Mode == 1 || Mode == 2) || (Mode == 3 && ArrayFields[i].important == 1)){
			//поиск различий
			let flag_razl = 0;
			if(Mode == 2){
				let main_val = '';
				for(let j=0; j<=ArrayRecs.length-1; j++){
					if(ArrayRecs[j].main == 1 && ArrayFields[i].name != ''){
						let v='ArrayRecs[j].'+ArrayFields[i].name+'';
						main_val=eval(v);
					}
				}	
				
				for(let j=0; j<=ArrayRecs.length-1; j++){
					if(ArrayRecs[j].main == 0 && ArrayFields[i].name != ''){
						let v='ArrayRecs[j].'+ArrayFields[i].name+'';
						val_=eval(v);
						if(main_val != val_){
							flag_razl = 1;
						}
					}

				}
			}
			//поиск различий

			if((flag_razl == 1 && Mode == 2) || Mode != 2){
				TempContent+='<div class="col-'+cols_m+' title row-center-align';
				if(!ArrayFields[i].name){TempContent+=' title_main row-center-align';}
				TempContent+='">';
				TempContent+=''+ArrayFields[i].title+'';
				TempContent+='</div>';

				//найти основное поле чтобы сравнивать с другими

				let counter = 0;
				for(let j=DuplicateOffset; j<=ArrayRecs.length-1; j++){
					if(counter < 5){
						let val_='';
						if(ArrayFields[i].name != ''){
							let v='ArrayRecs[j].'+ArrayFields[i].name+'';
							val_=eval(v);
							
							if(val_){
								TempContent+='<div class="col-'+cols+'';
								if(ArrayRecs[j].main == 1){
									TempContent+=' main';
								}
								TempContent+='">';

								if(ArrayRecs[j].main == 0 && ArrayFields[i].info != 1){
									TempContent+='<label class="checkbox_container">';
									TempContent+='<input type="checkbox" name="'+ArrayFields[i].name+'_'+ArrayRecs[j].id+'" id="'+ArrayFields[i].name+'_'+ArrayRecs[j].id+'" ';

									let v_='ArrayRecs[j].'+ArrayFields[i].name+'_ch';
									val__=eval(v_);
									if(val__ == 1){
										TempContent+='checked ';
									}

									TempContent+='value="1" onclick="chooseMainDuplicateField(this);">';
								}

								let birthday_f = 0;
								let fullname_f = 0;
								let name_f = 0;
								let telephone_f = 0;
								let email_f = 0;
								let address_f = 0;
								let factadd_f = 0;
								let specie_f = 0;
								let breed_f = 0;

								let chip_f = 0;
								let label_f = 0;

								if((
									ArrayFields[i].name == 'telephone' || 
									ArrayFields[i].name == 'email' || 
									ArrayFields[i].name == 'fullname' || 
									ArrayFields[i].name == 'name' || 
									ArrayFields[i].name == 'birthday' || 
									ArrayFields[i].name == 'address' || 
									ArrayFields[i].name == 'factadd' || 
									ArrayFields[i].name == 'chip' || 
									ArrayFields[i].name == 'label' || 
									ArrayFields[i].name == 'specie' || 
									ArrayFields[i].name == 'breed'
								) && ArrayRecs[j].main == 0){
									//проверяем отличие от основной записи
									for(let k=0; k<=ArrayRecs.length-1; k++){
										if(ArrayRecs[k].main == 1 && ArrayRecs[k].telephone != ArrayRecs[j].telephone && ArrayFields[i].name == 'telephone'){
											telephone_f = 1;
										}
										if(ArrayRecs[k].main == 1 && ArrayRecs[k].email != ArrayRecs[j].email && ArrayFields[i].name == 'email'){
											email_f = 1;
										}
										if(ArrayRecs[k].main == 1 && ArrayRecs[k].birthday != ArrayRecs[j].birthday && ArrayFields[i].name == 'birthday'){
											birthday_f = 1;
										}
										if(ArrayRecs[k].main == 1 && ArrayRecs[k].fullname != ArrayRecs[j].fullname && ArrayFields[i].name == 'fullname'){
											fullname_f = 1;
										}
										if(ArrayRecs[k].main == 1 && ArrayRecs[k].name != ArrayRecs[j].name && ArrayFields[i].name == 'name'){
											name_f = 1;
										}
										if(ArrayRecs[k].main == 1 && ArrayRecs[k].address != ArrayRecs[j].address && ArrayFields[i].name == 'address'){
											address_f = 1;
										}
										if(ArrayRecs[k].main == 1 && ArrayRecs[k].factadd != ArrayRecs[j].factadd && ArrayFields[i].name == 'factadd'){
											factadd_f = 1;
										}
										if(ArrayRecs[k].main == 1 && ArrayRecs[k].specie != ArrayRecs[j].specie && ArrayFields[i].name == 'specie'){
											specie_f = 1;
										}
										if(ArrayRecs[k].main == 1 && ArrayRecs[k].breed != ArrayRecs[j].breed && ArrayFields[i].name == 'breed'){
											breed_f = 1;
										}

										if(ArrayRecs[k].main == 1 && ArrayRecs[k].chip != ArrayRecs[j].chip && ArrayFields[i].name == 'chip'){
											chip_f = 1;
										}
										if(ArrayRecs[k].main == 1 && ArrayRecs[k].label != ArrayRecs[j].label && ArrayFields[i].name == 'label'){
											label_f = 1;
										}
									}
								}
								if(telephone_f || email_f || fullname_f || name_f || birthday_f || address_f || factadd_f || specie_f || breed_f || chip_f || label_f){TempContent+='<span>';}
								TempContent+=''+val_+'';
								if(ArrayFields[i].name == 'percent'){
									TempContent+='<div class="percent"><div style="width: '+val_+'%;"></div></div>';
								}
								if(telephone_f || email_f || fullname_f || name_f || birthday_f || address_f || factadd_f || specie_f || breed_f || chip_f || label_f){TempContent+='</span>';}

								if(ArrayRecs[j].main == 0 && ArrayFields[i].info != 1){
									TempContent+='<span class="checkbox_checkmark checkbox_checkmark_round"></span>';
									TempContent+='</label>';
								}
								
								TempContent+='</div>';
								TempContent+='</div>';
							}else{
								TempContent+='<div class="col-'+cols+' empty';
								if(ArrayRecs[j].main == 1){
									TempContent+=' main';
								}
								TempContent+='">';
								TempContent+='Нет данных';
								TempContent+='</div>';
							}
							
						}else{
							TempContent+='<div class="col-'+cols+'';
							if(ArrayRecs[j].main == 1){
								TempContent+=' main';
							}
							TempContent+='"></div>';
						}

						counter++;
					}
				}
			}
		}
	}

	if(ArrayRecs.length > 5){
		if(DuplicateOffset != 0){
			$("#arrow_left").show();
		}else{
			$("#arrow_left").hide();
		}

		if((6 + DuplicateOffset) <= ArrayRecs.length){
			$("#arrow_right").show();
		}else{
			$("#arrow_right").hide();
		}
	}

	document.getElementById('Duplicates').innerHTML=TempContent;
}

function mergeDuplicatesAccept(Id){
	let Text='Внимание! Отменить данное действие будет невозможно.';
	showAcceptWindow('Объединить выбранное?', Text, 'mergeDuplicates('+Id+');', 'Объединить', 'delete');
}

function showDuplicate(Id){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': '&id='+Id+'&mode=xml&action=rec',
		'url': "index.php",
		'beforeSend': function() {
				//показать загрузку
			showLoading('Duplicates');
		},
		'success': function (Data) {
			if($(Data).find('message').text() == 'token_invalid'){
				window.location.href = 'index.php';
			}else{

				if($(Data).find('rec_type').text() == 1){
					ArrayRecs=[];

					ArrayFields.push({title: 'Персональные данные', name: ''});
					ArrayFields.push({title: 'ФИО', name: 'fullname',important: 1});
					ArrayFields.push({title: 'Дата рождения', name: 'birthday',important: 1});
					ArrayFields.push({title: '% заполнения карточки', important: 1, name: 'percent', info: 1});
					ArrayFields.push({title: 'СНИЛС', name: 'snils'});
					ArrayFields.push({title: 'Идентификатор mos.ru', name: 'sso_id', info: 1});
					ArrayFields.push({title: 'Согласие на обработку персональных данных', name: 'accept', info: 1});
					ArrayFields.push({title: 'Примечание', name: 'description'});
					ArrayFields.push({title: 'Контакты', name: ''});
					ArrayFields.push({title: 'Номер телефона (основной)', name: 'telephone',important: 1});
					ArrayFields.push({title: 'Электронная почта', name: 'email'});
					ArrayFields.push({title: 'Адресные данные', name: ''});
					ArrayFields.push({title: 'Адрес регистрации', name: 'address'});
					ArrayFields.push({title: 'Фактический адрес', name: 'factadd',important: 1});
					ArrayFields.push({title: 'Животные', name: ''});
					ArrayFields.push({title: 'Количество животных', name: 'pets_count', info: 1, important: 1});
					ArrayFields.push({title: 'Приёмы', name: ''});
					ArrayFields.push({title: 'Количество приёмов', name: 'visits_count', info: 1, important: 1});
					ArrayFields.push({title: 'Дата последнего приема', name: 'last_visit', info: 1});

					$(Data).find('owner').each(function(){
						ArrayRecs.push({
							id: $(this).find('id').text(), 
							main: $(this).find('main').text(), 
							pets_count: $(this).find('pets_count').text(), 
							last_visit: $(this).find('last_visit').text(), 
							percent: $(this).find('percent').text(), 
							visits_count: $(this).find('visits_count').text(), 
							fullname: $(this).find('fullname').text(), 
							birthday: $(this).find('birthday').text(), 
							sso_id: $(this).find('sso_id').text(), 

							address: $(this).find('address').text(), 
							factadd: $(this).find('factadd').text(), 
							telephone: $(this).find('telephone').text(), 
							email: $(this).find('email').text(), 

							snils: $(this).find('snils').text(), 
							accept: $(this).find('accept').text(), 
							description: $(this).find('description').text()
						});
					});

					ArrayRecs = ArrayRecs.sort((a, b) => b.percent - a.percent);
					ArrayRecs = ArrayRecs.sort((a, b) => b.main - a.main);
					
					$("#title").html('Сравнение дублей владельцев');
					showDuplicate_();
				}else{
					ArrayRecs=[];

					ArrayFields.push({title: 'Основная информация', name: ''});
					ArrayFields.push({title: 'Кличка', name: 'name',important: 1});
					ArrayFields.push({title: '% заполнения карточки', name: 'percent', info: 1});
					ArrayFields.push({title: 'Вид животного', name: 'specie',important: 1});
					ArrayFields.push({title: 'Порода', name: 'breed',important: 1});
					ArrayFields.push({title: 'Пол', name: 'sex',important: 1});
					ArrayFields.push({title: 'Дата рождения', name: 'birthday',important: 1});
					ArrayFields.push({title: 'Возраст', name: 'age',important: 1, info: 1});
					ArrayFields.push({title: 'Идентификатор mos.ru', name: 'ext_id', info: 1});
					ArrayFields.push({title: 'Примечание', name: 'description'});
					ArrayFields.push({title: 'Владелец', name: ''});
					ArrayFields.push({title: 'ФИО', name: 'fullname', important: 1, info: 1});
					ArrayFields.push({title: 'Фактический адрес', name: 'factadd', info: 1});
					ArrayFields.push({title: 'Идентификация', name: ''});
					ArrayFields.push({title: 'Чип', name: 'chip'});
					ArrayFields.push({title: 'Клеймо', name: 'label'});

					ArrayFields.push({title: 'Данные о регистрации', name: ''});
					ArrayFields.push({title: 'Регистрационное удостоверение',important: 1, name: 'reg_certificate', info: 1});

					ArrayFields.push({title: 'Вакцинации и обработки', name: ''});
					ArrayFields.push({title: 'Против бешенства (кол-во)', name: 'vac_r_count', info: 1});
					ArrayFields.push({title: 'Дата вакцинации', name: 'vac_r_date', info: 1});

					ArrayFields.push({title: 'Обработки (кол-во)', name: 'ecto_count', info: 1});
					ArrayFields.push({title: 'Дата обработки', name: 'ecto_date', info: 1});

					ArrayFields.push({title: 'Другие вакцины (кол-во)', name: 'vac_o_count', info: 1});
					ArrayFields.push({title: 'Дата вакцинации', name: 'vac_o_date', info: 1});
					

					ArrayFields.push({title: 'Приёмы', name: ''});
					ArrayFields.push({title: 'Количество приёмов', name: 'visits_count', info: 1,important: 1});
					ArrayFields.push({title: 'Дата последнего приема', name: 'last_visit', info: 1});
					
					$(Data).find('pet').each(function(){
						ArrayRecs.push({
							id: $(this).find('id').text(), 
							main: $(this).find('main').text(),

							percent: $(this).find('percent').text(), 

							ext_id: $(this).find('ext_id').text(),

							chip: $(this).find('chip').text(),
							label: $(this).find('label').text(),
							reg_certificate: $(this).find('reg_certificate').text(),

							name: $(this).find('name').text(),
							fullname: $(this).find('fullname').text(),
							factadd: $(this).find('factadd').text(),
							specie: $(this).find('specie').text(),
							breed: $(this).find('breed').text(),

							sex: $(this).find('sex').text(),
							age: $(this).find('age').text(),
							birthday: $(this).find('birthday').text(),
							
							description: $(this).find('description').text(),

							visits_count: $(this).find('visits_count').text(), 
							last_visit: $(this).find('last_visit').text(),

							vac_r_count: $(this).find('vac_r_count').text(),
							vac_r_date: $(this).find('vac_r_date').text(),
							ecto_count: $(this).find('ecto_count').text(),
							ecto_date: $(this).find('ecto_date').text(),
							vac_o_count: $(this).find('vac_o_count').text(),
							vac_o_date: $(this).find('vac_o_date').text()
						});
					});

					ArrayRecs = ArrayRecs.sort((a, b) => b.percent - a.percent);
					ArrayRecs = ArrayRecs.sort((a, b) => b.main - a.main);

					$("#title").html('Сравнение дублей животных');
					showDuplicate_();
				}
			}
		}
	});
}

function chooseMainDuplicate(Duplicate){
	for(let i=0; i<=ArrayRecs.length-1; i++){
		if(ArrayRecs[i].id == Duplicate.id){
			ArrayRecs[i].main = 1;
		}else{
			ArrayRecs[i].main = 0;
		}
	}
	
	ArrayRecs = ArrayRecs.sort((a, b) => b.percent - a.percent);
	ArrayRecs = ArrayRecs.sort((a, b) => b.main - a.main);
	showDuplicate_();
}

function showDuplicateFields(Mode){
	if(Mode == 2){
		showDuplicate_(2)
		$("#fields_1").removeClass("active");
		$("#fields_2").addClass("active");
		$("#fields_3").removeClass("active");
	}else if(Mode == 3){
		showDuplicate_(3)
		$("#fields_1").removeClass("active");
		$("#fields_2").removeClass("active");
		$("#fields_3").addClass("active");
	}else{
		showDuplicate_(1);
		$("#fields_1").addClass("active");
		$("#fields_2").removeClass("active");
		$("#fields_3").removeClass("active");
	}
}

function showAutoDuplicatesTab(Tab){
	if(Tab == 1){
		$("#tab").val(1);
		listShowAutoDuplicates();

		$("#owners_field1").removeClass("hidden");
		$("#owners_field2").removeClass("hidden");
		$("#owners_field3").removeClass("hidden");
		$("#owners_field4").removeClass("hidden");
		$("#owners_field5").removeClass("hidden");
		$("#owners_field6").removeClass("hidden");

		$("#pets_field1").addClass("hidden");
		$("#pets_field2").addClass("hidden");
		$("#pets_field3").addClass("hidden");
		$("#pets_field4").addClass("hidden");
		$("#pets_field5").addClass("hidden");
		$("#pets_field6").addClass("hidden");


		$("#tab_1").addClass("active");
		$("#tab_2").removeClass("active");
	}else{
		$("#tab").val(2);
		listShowAutoDuplicates();

		$("#owners_field1").addClass("hidden");
		$("#owners_field2").addClass("hidden");
		$("#owners_field3").addClass("hidden");
		$("#owners_field4").addClass("hidden");
		$("#owners_field5").addClass("hidden");
		$("#owners_field6").addClass("hidden");

		$("#pets_field1").removeClass("hidden");
		$("#pets_field2").removeClass("hidden");
		$("#pets_field3").removeClass("hidden");
		$("#pets_field4").removeClass("hidden");
		$("#pets_field5").removeClass("hidden");
		$("#pets_field6").removeClass("hidden");

		$("#tab_2").addClass("active");
		$("#tab_1").removeClass("active");
	}
}

function showArchiveDuplicatesTab(Tab){
	if(Tab == 1){
		$("#tab").val(1);
		listShowArchiveDuplicates();

		$("#owners_field1").removeClass("hidden");
		$("#owners_field2").removeClass("hidden");
		$("#owners_field3").removeClass("hidden");
		$("#owners_field4").removeClass("hidden");
		$("#owners_field5").removeClass("hidden");
		$("#owners_field6").removeClass("hidden");

		$("#pets_field1").addClass("hidden");
		$("#pets_field2").addClass("hidden");
		$("#pets_field3").addClass("hidden");
		$("#pets_field4").addClass("hidden");
		$("#pets_field5").addClass("hidden");
		$("#pets_field6").addClass("hidden");


		$("#tab_1").addClass("active");
		$("#tab_2").removeClass("active");
	}else{
		$("#tab").val(2);
		listShowArchiveDuplicates();

		$("#owners_field1").addClass("hidden");
		$("#owners_field2").addClass("hidden");
		$("#owners_field3").addClass("hidden");
		$("#owners_field4").addClass("hidden");
		$("#owners_field5").addClass("hidden");
		$("#owners_field6").addClass("hidden");

		$("#pets_field1").removeClass("hidden");
		$("#pets_field2").removeClass("hidden");
		$("#pets_field3").removeClass("hidden");
		$("#pets_field4").removeClass("hidden");
		$("#pets_field5").removeClass("hidden");
		$("#pets_field6").removeClass("hidden");

		$("#tab_2").addClass("active");
		$("#tab_1").removeClass("active");
	}
}

function showDuplicatesTab(Tab){
	if(Tab == 1){
		$("#tab").val(1);
		listShowDuplicates();

		$("#owners_field1").removeClass("hidden");
		$("#owners_field2").removeClass("hidden");

		$("#pets_field1").addClass("hidden");
		$("#pets_field2").addClass("hidden");

		$("#tab_1").addClass("active");
		$("#tab_2").removeClass("active");
	}else{
		$("#tab").val(2);
		listShowDuplicates();

		$("#owners_field1").addClass("hidden");
		$("#owners_field2").addClass("hidden");

		$("#pets_field1").removeClass("hidden");
		$("#pets_field2").removeClass("hidden");

		$("#tab_2").addClass("active");
		$("#tab_1").removeClass("active");
	}
}

function chooseMainDuplicateField(Duplicate){
	let temp_ = Duplicate.id;
	let temp__ = temp_.split('_');
	
	let flag = 0;
	if($("#"+temp_+"").prop("checked") == false){
		flag = 1;
	}

	$("[id*='"+temp__[0]+"']").prop("checked", false);

	if(!flag){
		$("#"+temp_+"").prop("checked", true);
	}

	for(let i=0; i<=ArrayRecs.length-1; i++){
		if(ArrayRecs[i].id == temp__[1] && !flag){
			let v='ArrayRecs[i].'+temp__[0]+'_ch="1"';
			eval(v);
		}else{
			let v='ArrayRecs[i].'+temp__[0]+'_ch="0"';
			eval(v);
		}
	}
}

function deleteDuplicate(Id){
	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': 'id='+Id+'&duplicate='+$("#duplicate").val()+'&action=delete_duplicate&mode=xml',
		'url': "index.php",
		'beforeSend': function() {
			//показать загрузку
			showTopLoading();
		},
		'success': function (Data) {
			if($(Data).find('message').text() == 'token_invalid'){
				window.location.href = 'index.php';
			}else{
				if($(Data).find('message').text() == 'duplicate_deleted'){
					closeTopLoading();
				}
			}
		}
	});

	showDuplicate($("#duplicate").val());
}

function mergeDuplicates(Id){
	//id записи дубликата
	//формируем пакет на объединение данных
	let Duplicate = '';
	
	for(let i=0; i<=ArrayRecs.length-1; i++){
		if(ArrayRecs[i].fullname_ch == 1){
			if(Duplicate){Duplicate+=',';}
			Duplicate+='"fullname":"'+ArrayRecs[i].id+'"';
		}
		if(ArrayRecs[i].name_ch == 1){
			if(Duplicate){Duplicate+=',';}
			Duplicate+='"name":"'+ArrayRecs[i].id+'"';
		}
		if(ArrayRecs[i].birthday_ch == 1){
			if(Duplicate){Duplicate+=',';}
			Duplicate+='"birthday":"'+ArrayRecs[i].id+'"';
		}

		if(ArrayRecs[i].telephone_ch == 1){
			if(Duplicate){Duplicate+=',';}
			Duplicate+='"telephone":"'+ArrayRecs[i].id+'"';
		}
		if(ArrayRecs[i].snils_ch == 1){
			if(Duplicate){Duplicate+=',';}
			Duplicate+='"snils":"'+ArrayRecs[i].id+'"';
		}
		if(ArrayRecs[i].description_ch == 1){
			if(Duplicate){Duplicate+=',';}
			Duplicate+='"description":"'+ArrayRecs[i].id+'"';
		}

		if(ArrayRecs[i].specie_ch == 1){
			if(Duplicate){Duplicate+=',';}
			Duplicate+='"specie":"'+ArrayRecs[i].id+'"';
		}
		if(ArrayRecs[i].breed_ch == 1){
			if(Duplicate){Duplicate+=',';}
			Duplicate+='"breed":"'+ArrayRecs[i].id+'"';
		}
		if(ArrayRecs[i].chip_ch == 1){
			if(Duplicate){Duplicate+=',';}
			Duplicate+='"chip":"'+ArrayRecs[i].id+'"';
		}
		if(ArrayRecs[i].label_ch == 1){
			if(Duplicate){Duplicate+=',';}
			Duplicate+='"label":"'+ArrayRecs[i].id+'"';
		}
		// if(ArrayRecs[i].reg_certificate_ch == 1){
		// 	if(Duplicate){Duplicate+=',';}
		// 	Duplicate+='"reg_certificate":"'+ArrayRecs[i].id+'"';
		// }

		if(ArrayRecs[i].email_ch == 1){
			if(Duplicate){Duplicate+=',';}
			Duplicate+='"email":"'+ArrayRecs[i].id+'"';
		}

		if(ArrayRecs[i].address_ch == 1){
			if(Duplicate){Duplicate+=',';}
			Duplicate+='"address":"'+ArrayRecs[i].id+'"';
		}
		if(ArrayRecs[i].factadd_ch == 1){
			if(Duplicate){Duplicate+=',';}
			Duplicate+='"factadd":"'+ArrayRecs[i].id+'"';
		}
		
		if(ArrayRecs[i].main == 1){
			if(Duplicate){Duplicate+=',';}
			Duplicate+='"main":"'+ArrayRecs[i].id+'"';
		}
	}
	Duplicate='{'+Duplicate+'}';
	console.log(Duplicate);

	jQuery.ajax({
		'async': true,
		'global': false,
		'cache': false,
		'type': 'GET',
		'dataType': 'xml',
		'data': 'id='+Id+'&data='+Duplicate+'&action=merge_duplicates&mode=xml',
		'url': "index.php",
		'beforeSend': function() {
			//показать загрузку
			showTopLoading();
		},
		'success': function (Data) {
			if($(Data).find('message').text() == 'token_invalid'){
				window.location.href = 'index.php';
			}else{
				if($(Data).find('message').text() == 'duplicates_merged'){
					//переход на главную
					closeTopLoading();
					window.location.href = 'index.php';
				}
			}
		}
	});
}

function showDuplicatesArrow(Mode){
	if(Mode == 'left'){
		DuplicateOffset = DuplicateOffset - 1;
		showDuplicate_();
	}
	if(Mode == 'right'){
		DuplicateOffset = DuplicateOffset + 1;
		showDuplicate_();
	}
}