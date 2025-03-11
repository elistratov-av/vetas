<?php
error_reporting(0);
use Mpdf\Tag\Tr;

	$configuration = require $_SERVER['DOCUMENT_ROOT'].'/callcenter/config.php';
	
	include_once $_SERVER['DOCUMENT_ROOT'].'/callcenter/functions.php';
	include_once $_SERVER['DOCUMENT_ROOT'].'/callcenter/header.php';
	include_once $_SERVER['DOCUMENT_ROOT'].'/callcenter/footer.php';
	
	if (!function_exists('pg_connect')) {echo xml('<message>Функция pg_connect отсутсвует!</message>');exit;}else{$dbconn = pg_connect("host=".$configuration['host']." port=".$configuration['port']." dbname=".$configuration['dbname']." user=".$configuration['user']." password=".$configuration['password']."") or die('Не удалось соединиться: ' . pg_last_error());$result = pg_query('SET TIME ZONE \'Europe/Moscow\';') or die('Ошибка запроса: ' . pg_last_error());pg_free_result($result);}

	if(isset($_POST["action"])){$action=$_POST["action"];}else{$action=$_GET["action"];}
	if(isset($_POST["mode"])){$mode=$_POST["mode"];}else{$mode=$_GET["mode"];}

	function support_show_species_xml($config){
		if(isset($_POST["name"])){$name=$_POST["name"];}else{$name=$_GET["name"];}
		if(isset($_POST["description"])){$description=$_POST["description"];}else{$description=$_GET["description"];}
		if(isset($_POST["page"])){$page=$_POST["page"];}else{$page=$_GET["page"];}//страница
		if($page == ''){$page=1;}
	
		$recs_on_page=50;//кол-во на странице
	
		header("Content-type: text/xml; charset=utf-8");
	
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<xml>';
	
		$query='SELECT species.id, species.name, species.description ';
		$query.='FROM species ';
		$query.='WHERE species.name IS NOT NULL ';
		if($name){
			$query.='AND species.name ILIKE \'%'.$name.'%\' ';
		}
		if($description){
			$query.='AND species.description ILIKE \'%'.$description.'%\' ';
		}

		 //Пагинация
		 $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		 $recs_counter = pg_num_rows($result);//количество записей
		 pg_free_result($result);
	 
		 echo '<from>'.intval(($page-1)*$recs_on_page+1).'</from>';
		 echo '<to>'.intval(($page)*$recs_on_page).'</to>';
		 echo '<current>'.$page.'</current>';
		 echo '<counter>'.$recs_counter.'</counter>';
		 echo '<pages>';
		if($recs_counter > 0){
			$pages_number = intval($recs_counter / $recs_on_page);
			if($pages_number > 1){
				for($i = 1; $i < $pages_number+1; $i++){
					echo '<page>'.$i.'</page>';
				}
			}
			echo '<last>'.$pages_number.'</last>';
		}
		echo '</pages>';
		//Пагинация
	
		$query.='ORDER BY species.name ';
		$query.='LIMIT '.$recs_on_page.' OFFSET '.intval(($page-1)*$recs_on_page).'';
		
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		while ($row = pg_fetch_assoc($result)) {
	
			echo '<rec>';
			echo '<rec_id>'.$row['id'].'</rec_id>';
			echo '<rec_name>'.$row['name'].'</rec_name>';
			echo '<rec_description>'.$row['description'].'</rec_description>';
			echo '</rec>';
		}
		pg_free_result($result);
		echo '</xml>';
	}
	function support_show_specie_xml($config){
		$id=$_GET["id"];

		header("Content-type: text/xml; charset=utf-8");
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<xml>';
		echo '<rec>';


		$query='SELECT species.* ';
		$query.='FROM species ';
		
		$query.='WHERE species.id='.string_formating_for_sql($id).' ';
				
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    	while ($row = pg_fetch_assoc($result)) {
			echo '<rec_id>'.$row['id'].'</rec_id>';
			echo '<rec_name>'.$row['name'].'</rec_name>';
			echo '<rec_description>'.$row['description'].'</rec_description>';
		}
		pg_free_result($result);

		echo '</rec>';
		echo '</xml>';
	}

	function support_show_breeds_xml($config){
		if(isset($_POST["name"])){$name=$_POST["name"];}else{$name=$_GET["name"];}
		if(isset($_POST["description"])){$description=$_POST["description"];}else{$description=$_GET["description"];}
		if(isset($_POST["species"])){$species=$_POST["species"];}else{$species=$_GET["species"];}
		if(isset($_POST["page"])){$page=$_POST["page"];}else{$page=$_GET["page"];}//страница
		if($page == ''){$page=1;}
	
		$recs_on_page=50;//кол-во на странице
	
		header("Content-type: text/xml; charset=utf-8");
	
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<xml>';
	
		$query='SELECT breeds.id, breeds.name, species.name AS species, breeds.description, breeds.species_id ';
		$query.='FROM breeds ';
		$query.='LEFT JOIN species ON breeds.species_id=species.id ';
		$query.='WHERE breeds.name IS NOT NULL ';
		if($name){
			$query.='AND breeds.name ILIKE \'%'.$name.'%\' ';
		}
		if($description){
			$query.='AND breeds.description ILIKE \'%'.$description.'%\' ';
		}
		if($species){
			$query.='AND breeds.species_id='.$species.' ';
		}
		 //Пагинация
		 $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		 $recs_counter = pg_num_rows($result);//количество записей
		 pg_free_result($result);
	 
		 echo '<from>'.intval(($page-1)*$recs_on_page+1).'</from>';
		 echo '<to>'.intval(($page)*$recs_on_page).'</to>';
		 echo '<current>'.$page.'</current>';
		 echo '<counter>'.$recs_counter.'</counter>';
		 echo '<pages>';
		if($recs_counter > 0){
			$pages_number = intval($recs_counter / $recs_on_page);
			if($pages_number > 1){
				for($i = 1; $i < $pages_number+1; $i++){
					echo '<page>'.$i.'</page>';
				}
			}
			echo '<last>'.$pages_number.'</last>';
		}
		echo '</pages>';
		//Пагинация
	
		$query.='ORDER BY breeds.name ';
		$query.='LIMIT '.$recs_on_page.' OFFSET '.intval(($page-1)*$recs_on_page).'';
		
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		while ($row = pg_fetch_assoc($result)) {
	
			echo '<rec>';
			echo '<rec_id>'.$row['id'].'</rec_id>';
			echo '<rec_name>'.$row['name'].'</rec_name>';
			echo '<rec_description>'.$row['description'].'</rec_description>';
			echo '<rec_species>'.$row['species'].'</rec_species>';
			echo '</rec>';
		}
		pg_free_result($result);
		echo '</xml>';
	}
	function support_show_breed_xml($config){
		$id=$_GET["id"];

		header("Content-type: text/xml; charset=utf-8");
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<xml>';
		echo '<rec>';


		$query='SELECT breeds.* ';
		$query.='FROM breeds ';
		
		$query.='WHERE breeds.id='.string_formating_for_sql($id).' ';
				
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    	while ($row = pg_fetch_assoc($result)) {
			echo '<rec_id>'.$row['id'].'</rec_id>';
			echo '<rec_name>'.$row['name'].'</rec_name>';
			echo '<rec_species>'.$row['species_id'].'</rec_species>';
			echo '<rec_description>'.$row['description'].'</rec_description>';
		}
		pg_free_result($result);

		echo '</rec>';
		echo '</xml>';
	}

	function support_show_informing_xml($config){
		$id=$_GET["id"];

		header("Content-type: text/xml; charset=utf-8");
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<xml>';
		echo '<rec>';


		$query='SELECT informings.*, ';

		
		$query.='(LOWER(informings.date)::date) AS date_from, ';
		$query.='(UPPER(informings.date)::date) AS date_to,';

		$query.='(LOWER(informings.date)::time) AS time_from,';
		$query.='(UPPER(informings.date)::time) AS time_to,';
		

		$query.='string_agg(organizations.id::character varying, \',\') AS organizations ';
		$query.='FROM informings ';
		$query.='LEFT JOIN informings_organizations ON informings.id=informings_organizations.id_informing ';
		$query.='LEFT JOIN organizations ON organizations.id=informings_organizations.id_organization ';
		$query.='WHERE informings.id='.string_formating_for_sql($id).' ';
		$query.='GROUP BY informings.id ';
		$query.='ORDER BY informings.id DESC';
		
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    	while ($row = pg_fetch_assoc($result)) {
			echo '<rec_id>'.$row['id'].'</rec_id>';
			echo '<rec_text>'.$row['text'].'</rec_text>';
			echo '<rec_period>'.$row['date'].'</rec_period>';

			echo '<rec_date_from>'.$row['date_from'].'</rec_date_from>';
			echo '<rec_date_to>'.$row['date_to'].'</rec_date_to>';
			echo '<rec_time_from>'.$row['time_from'].'</rec_time_from>';
			echo '<rec_time_to>'.$row['time_to'].'</rec_time_to>';

			$organizations = ($row['organizations'] != '')?explode(",",$row['organizations']):NULL;
			if(count($organizations) > 0){
				echo '<rec_organizations>';

				for ($i=0; $i<count($organizations); $i++) {
					echo '<organization>'.$organizations[$i].'</organization>';
				}
				
				echo '</rec_organizations>';
			}
		}
		pg_free_result($result);

		echo '</rec>';
		echo '</xml>';
	}

	function support_show_specialization_xml($config){
		$id=$_GET["id"];

		header("Content-type: text/xml; charset=utf-8");
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<xml>';
		echo '<rec>';


		$query='SELECT specializations.*, string_agg(gov_services.id::character varying, \';\') AS services ';
		$query.='FROM specializations ';
		$query.='LEFT JOIN gov_services ON gov_services.id_specialization=specializations.id ';
		
		$query.='WHERE specializations.id='.string_formating_for_sql($id).' ';

		$query.='GROUP BY specializations.id';
				
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    	while ($row = pg_fetch_assoc($result)) {
			echo '<rec_id>'.$row['id'].'</rec_id>';
			echo '<rec_name>'.$row['name'].'</rec_name>';
			
			$servicesArray  = explode(';', $row['services']);
			echo '<rec_services>';
			for($i=0; $i<=count($servicesArray);$i++){
				echo '<service>'.$servicesArray[$i].'</service>';
			}
			echo '</rec_services>';
			echo '<rec_description>'.$row['description'].'</rec_description>';
		}
		pg_free_result($result);

		echo '</rec>';
		echo '</xml>';
	}

	function support_show_disease_xml($config){
		$id=$_GET["id"];

		header("Content-type: text/xml; charset=utf-8");
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<xml>';
		echo '<rec>';


		$query='SELECT diseases.* ';
		//, string_agg(gov_services.id::character varying, \';\') AS services ';
		$query.='FROM diseases ';
		// $query.='LEFT JOIN gov_services ON gov_services.id_specialization=specializations.id ';
		$query.='WHERE diseases.id='.string_formating_for_sql($id).' ';
		//$query.='GROUP BY diseases.id';
				
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    	while ($row = pg_fetch_assoc($result)) {
			echo '<rec_id>'.$row['id'].'</rec_id>';
			echo '<rec_cod>'.$row['cod'].'</rec_cod>';
			echo '<rec_name>'.$row['name'].'</rec_name>';
			echo '<rec_flag_danger>'.$row['flag_danger'].'</rec_flag_danger>';
			
			// $servicesArray  = explode(';', $row['services']);
			// echo '<rec_services>';
			// for($i=0; $i<=count($servicesArray);$i++){
			// 	echo '<service>'.$servicesArray[$i].'</service>';
			// }
			// echo '</rec_services>';
			echo '<rec_description>'.$row['description'].'</rec_description>';
		}
		pg_free_result($result);

		echo '</rec>';
		echo '</xml>';
	}

	function support_add_informing($configuration){
		$text=$_POST["text"];

		$date_from=$_POST["date_from"];
		$time_from=$_POST["time_from"];
		$date_to=$_POST["date_to"];
		$time_to=$_POST["time_to"];

		$organizations=$_POST["organizations"];

		$id_user = user_id($configuration);

		$query='INSERT INTO informings ';
		$query.='(text, date, created_at, created_by, updated_at, updated_by) ';
		$query.='VALUES (';
		$query.='\''.$text.'\',';
		
		$query.='tsrange(\''.$date_from.' '.$time_from.'\', \''.$date_to.' '.$time_to.'\', \'[)\'),';

		$query.="NOW()::timestamp(0),";#created_at
		$query.="".$id_user.",";#created_by
    	$query.="NOW()::timestamp(0),";#updated_at
		$query.="".$id_user."";#created_by

		$query.=') RETURNING id;';
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		$row = pg_fetch_row($result);
		$id_informing = $row[0];
		pg_free_result($result);
		
        for ($i=0; $i<count($organizations); $i++) {
			$query='INSERT INTO informings_organizations ';
			$query.='(id_informing, id_organization, created_at, created_by) ';
			$query.='VALUES (';
			$query.=''.$id_informing.',';
			$query.=''.$organizations[$i].',';
			$query.="NOW()::timestamp(0),";#created_at
			$query.="".$id_user."";#created_by
			$query.=')';
			
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			$row = pg_fetch_row($result);
			$id=$row[0];
			pg_free_result($result);
		}

		echo xml('<message>informing_added</message>');
	}

	function support_save_informing($configuration){
		$text=$_POST["text"];

		$id=$_POST["id"];
		$date_from=$_POST["date_from"];
		$time_from=$_POST["time_from"];
		$date_to=$_POST["date_to"];
		$time_to=$_POST["time_to"];

		$organizations=$_POST["organizations"];

		$id_user = user_id($configuration);

		$query='UPDATE informings SET ';
		$query.='text = \''.$text.'\',';
		$query.='date = tsrange(\''.$date_from.' '.$time_from.'\', \''.$date_to.' '.$time_to.'\', \'[)\'),';
		$query.='updated_at = NOW()::timestamp(0),';
		$query.='updated_by = '.$id_user.'';
		$query.='WHERE id='.$id.'';
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		$row = pg_fetch_row($result);
		pg_free_result($result);

		$query_delete='DELETE FROM public.informings_organizations WHERE id_informing='.$id.'';
		$result = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
		pg_free_result($result);

        for ($i=0; $i<count($organizations); $i++) {
			$query='INSERT INTO informings_organizations ';
			$query.='(id_informing, id_organization, created_at, created_by) ';
			$query.='VALUES (';
			$query.=''.$id.',';
			$query.=''.$organizations[$i].',';
			$query.="NOW()::timestamp(0),";#created_at
			$query.="".$id_user."";#created_by
			$query.=')';
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			$row = pg_fetch_row($result);
			pg_free_result($result);
		}

		echo xml('<message>informing_saved</message>');
	}

	function support_save_disease($configuration){
		$id=$_POST["id"];
		$name=$_POST["name"];
		$cod=$_POST["cod"];
		// $description=$_POST["description"];
		// $services=$_POST["services"];

		$id_user = user_id($configuration);

		if($id && $id != 'null'){
			$query='UPDATE diseases SET ';
			$query.='name = \''.string_formating_for_sql($name).'\',';
			$query.='cod = \''.string_formating_for_sql($cod).'\',';
			$query.='updated_at = NOW()::timestamp(0),';
			$query.='updated_by = '.$id_user.'';
			$query.='WHERE id='.$id.'';
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			$row = pg_fetch_row($result);
			pg_free_result($result);
		}else{
			$query='INSERT INTO diseases ';
			$query.='(name, cod, created_at, created_by, updated_at, updated_by) ';
			$query.='VALUES (';
			$query.='\''.string_formating_for_sql($name).'\',';
			$query.='\''.string_formating_for_sql($cod).'\',';
			$query.="NOW()::timestamp(0),";#created_at
			$query.="".$id_user.",";#created_by
			$query.="NOW()::timestamp(0),";#updated_at
			$query.="".$id_user."";#created_by
			$query.=') RETURNING id;';

			//echo $query;
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			$row = pg_fetch_row($result);
			$id = $row[0];
			pg_free_result($result);
		}

		// ################################
		// ###обнуляем для текущих услуг###
		// ################################
		// $query='UPDATE gov_services SET ';
		// $query.='id_specialization = NULL ';
		// $query.='WHERE id_specialization='.$id.' AND deleted=false';
		// $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		// $row = pg_fetch_row($result);
		// pg_free_result($result);

		// for ($i=0; $i<count($services); $i++) {
		// 	$query='UPDATE gov_services SET ';
		// 	$query.='id_specialization = '.$id.',';
		// 	$query.='updated_at = NOW()::timestamp(0),';
		// 	$query.='updated_by = '.$id_user.'';
		// 	$query.='WHERE id='.$services[$i].'';
		// 	$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		// 	$row = pg_fetch_row($result);
		// 	pg_free_result($result);
		// }

		echo xml('<message>disease_saved</message>');
	}

	function support_save_breed($configuration){
		$id=$_POST["id"];
		$name=$_POST["name"];
		$specie=$_POST["species"];
		$description=$_POST["description"];
		
		$id_user = user_id($configuration);

		if($id && $id != 'null'){
			$query='UPDATE breeds SET ';
			$query.='name = \''.string_formating_for_sql($name).'\',';
			$query.='species_id = '.string_formating_for_sql($specie).',';
			$query.='description = \''.string_formating_for_sql($description).'\',';
			$query.='updated_at = NOW()::timestamp(0),';
			$query.='updated_by = '.$id_user.'';
			$query.='WHERE id='.$id.'';
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			$row = pg_fetch_row($result);
			pg_free_result($result);
		}else{
			$query='INSERT INTO breeds ';
			$query.='(name, species_id, description, created_at, created_by, updated_at, updated_by) ';
			$query.='VALUES (';
			$query.='\''.string_formating_for_sql($name).'\',';
			$query.=''.string_formating_for_sql($specie).',';
			$query.='\''.string_formating_for_sql($description).'\',';
			$query.="NOW()::timestamp(0),";#created_at
			$query.="".$id_user.",";#created_by
			$query.="NOW()::timestamp(0),";#updated_at
			$query.="".$id_user."";#created_by
			$query.=') RETURNING id;';
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			$row = pg_fetch_row($result);
			$id = $row[0];
			pg_free_result($result);
		}

		echo xml('<message>breed_saved</message>');
	}
	function support_save_specie($configuration){
		$id=$_POST["id"];
		$name=$_POST["name"];
		$description=$_POST["description"];
		
		$id_user = user_id($configuration);

		if($id && $id != 'null'){
			$query='UPDATE species SET ';
			$query.='name = \''.string_formating_for_sql($name).'\',';
			$query.='description = \''.string_formating_for_sql($description).'\',';
			$query.='updated_at = NOW()::timestamp(0),';
			$query.='updated_by = '.$id_user.'';
			$query.='WHERE id='.$id.'';
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			$row = pg_fetch_row($result);
			pg_free_result($result);
		}else{
			$query='INSERT INTO species ';
			$query.='(name, description, created_at, created_by, updated_at, updated_by) ';
			$query.='VALUES (';
			$query.='\''.string_formating_for_sql($name).'\',';
			$query.='\''.string_formating_for_sql($description).'\',';
			$query.="NOW()::timestamp(0),";#created_at
			$query.="".$id_user.",";#created_by
			$query.="NOW()::timestamp(0),";#updated_at
			$query.="".$id_user."";#created_by
			$query.=') RETURNING id;';
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			$row = pg_fetch_row($result);
			$id = $row[0];
			pg_free_result($result);
		}

		echo xml('<message>specie_saved</message>');
	}

	function support_save_recovery_password_rec($configuration){
		$id=$_POST["id"];
		$status=$_POST["status"];

		if($_POST["id_"] && $_POST["status_"]){
			$id=$_POST["id_"];
			$status=$_POST["status_"];
		}
				
		if($id && $id != 'null'){
			$query='UPDATE recovery_password SET ';
			$query.='status = '.string_formating_for_sql($status).'';
			$query.='WHERE id='.$id.'';
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			$row = pg_fetch_row($result);
			pg_free_result($result);
		}else{
			// $query='INSERT INTO recovery_password ';
			// $query.='(name, cod, created_at, created_by, updated_at, updated_by) ';
			// $query.='VALUES (';
			// $query.='\''.string_formating_for_sql($name).'\',';
			// $query.='\''.string_formating_for_sql($cod).'\',';
			// $query.="NOW()::timestamp(0),";#created_at
			// $query.="".$id_user.",";#created_by
			// $query.="NOW()::timestamp(0),";#updated_at
			// $query.="".$id_user."";#created_by
			// $query.=') RETURNING id;';

			// //echo $query;
			// $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			// $row = pg_fetch_row($result);
			// $id = $row[0];
			// pg_free_result($result);
		}

		echo xml('<message>recovery_password_rec_saved</message>');
	}

	function support_save_specialization($configuration){
		$id=$_POST["id"];
		$name=$_POST["name"];
		$description=$_POST["description"];
		$services=$_POST["services"];

		$id_user = user_id($configuration);

		if($id && $id != 'null'){
			$query='UPDATE specializations SET ';
			$query.='name = \''.string_formating_for_sql($name).'\',';
			$query.='description = \''.string_formating_for_sql($description).'\',';
			$query.='updated_at = NOW()::timestamp(0),';
			$query.='updated_by = '.$id_user.'';
			$query.='WHERE id='.$id.'';
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			$row = pg_fetch_row($result);
			pg_free_result($result);
		}else{
			$query='INSERT INTO specializations ';
			$query.='(name, description, created_at, created_by, updated_at, updated_by) ';
			$query.='VALUES (';
			$query.='\''.string_formating_for_sql($name).'\',';
			$query.='\''.string_formating_for_sql($description).'\',';
			$query.="NOW()::timestamp(0),";#created_at
			$query.="".$id_user.",";#created_by
			$query.="NOW()::timestamp(0),";#updated_at
			$query.="".$id_user."";#created_by
			$query.=') RETURNING id;';

			//echo $query;
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			$row = pg_fetch_row($result);
			$id = $row[0];
			pg_free_result($result);
		}

		################################
		###обнуляем для текущих услуг###
		################################
		$query='UPDATE gov_services SET ';
		$query.='id_specialization = NULL ';
		$query.='WHERE id_specialization='.$id.' AND deleted=false';
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		$row = pg_fetch_row($result);
		pg_free_result($result);

		for ($i=0; $i<count($services); $i++) {
			$query='UPDATE gov_services SET ';
			$query.='id_specialization = '.$id.',';
			$query.='updated_at = NOW()::timestamp(0),';
			$query.='updated_by = '.$id_user.'';
			$query.='WHERE id='.$services[$i].'';
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			$row = pg_fetch_row($result);
			pg_free_result($result);
		}

		echo xml('<message>specialization_saved</message>');
	}

	function support_show_informings_xml($config){
		header("Content-type: text/xml; charset=utf-8");
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<xml>';

		echo '<recs>';
		
		$query='SELECT informings.*, ';
		$query.='(LOWER(informings.date)::timestamp) AS date_from, ';
		$query.='(UPPER(informings.date)::timestamp) AS date_to,';
		$query.='(LOWER(informings.date)::time) AS time_from,';
		$query.='(UPPER(informings.date)::time) AS time_to,';
		$query.='string_agg(organizations.short_name::character varying, \', \') AS organizations ';
		$query.='FROM informings ';
		$query.='LEFT JOIN informings_organizations ON informings.id=informings_organizations.id_informing ';
		$query.='LEFT JOIN organizations ON organizations.id=informings_organizations.id_organization ';
			
		$query.='GROUP BY informings.id ';
		$query.='ORDER BY informings.id DESC';
		//echo $query;
		
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		while ($row = pg_fetch_assoc($result)) {
			echo '<rec>';
			echo '<rec_id>'.$row['id'].'</rec_id>';
			echo '<rec_text>'.$row['text'].'</rec_text>';


			echo '<rec_date>'.date_format(new \DateTime($row['created_at']), "d.m.Y").'</rec_date>';
			echo '<rec_period>'.date_format(new \DateTime($row['date_from']), "d.m.Y H:i").' — '.date_format(new \DateTime($row['date_to']), "d.m.Y H:i").'</rec_period>';


			echo '</rec>';
		
		}
		pg_free_result($result);
		
		echo '</recs>';
		echo '</xml>';
	}

	function support_close_visit($configuration){
		$id=$_GET["id"];

		$query='SELECT id FROM users WHERE login=\''.string_formating_for_sql($_COOKIE['login']).'\' LIMIT 1';
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		$id_user=pg_fetch_result($result, 0);
		pg_free_result($result);
					
		if($id != ''){
			$query='UPDATE visits SET ';
			$query.='status=\'F\', ';
			$query.="updated_by=".$id_user.",";#updated_by
			$query.="updated_at=NOW()::timestamp(0) WHERE id=".$id."";#updated_at
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			$row = pg_fetch_row($result);
			pg_free_result($result);
						
			echo xml('<message>visit_closed</message>');
		}else{
			echo xml('<error>visit_not_found</error>');
		}
	}

	function support_work_visit($configuration){
		$id=$_GET["id"];

		$query='SELECT id FROM users WHERE login=\''.string_formating_for_sql($_COOKIE['login']).'\' LIMIT 1';
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		$id_user=pg_fetch_result($result, 0);
		pg_free_result($result);
	
		if($id != ''){
			$query='UPDATE visits SET ';
			$query.='status=\'W\', ';
			$query.="updated_by=".$id_user.",";#updated_by
			$query.="updated_at=NOW()::timestamp(0) WHERE id=".$id."";#updated_at
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			$row = pg_fetch_row($result);
			pg_free_result($result);
						
			echo xml('<message>visit_worked</message>');
		}else{
			echo xml('<error>visit_not_found</error>');
		}
	}

	function support_cancel_visit($configuration){
		$id=$_GET["id"];

		$query='SELECT id FROM users WHERE login=\''.string_formating_for_sql($_COOKIE['login']).'\' LIMIT 1';
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		$id_user=pg_fetch_result($result, 0);
		pg_free_result($result);
	
		if($id != ''){
			$query='UPDATE visits SET ';
			$query.='status=\'A\', ';
			$query.="updated_by=".$id_user.",";#updated_by
			$query.="updated_at=NOW()::timestamp(0) WHERE id=".$id."";#updated_at
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			$row = pg_fetch_row($result);
			pg_free_result($result);
						
			echo xml('<message>visit_canceled</message>');
		}else{
			echo xml('<error>visit_not_found</error>');
		}
	}

	function support_cancel_visit_paid($configuration){
		$id=$_GET["id"];

		$query='SELECT id FROM users WHERE login=\''.string_formating_for_sql($_COOKIE['login']).'\' LIMIT 1';
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		$id_user=pg_fetch_result($result, 0);
		pg_free_result($result);
	
		if($id != ''){
			$query='UPDATE visits SET ';
			$query.='is_paid=false, ';
			$query.="updated_by=".$id_user.",";#updated_by
			$query.="updated_at=NOW()::timestamp(0) WHERE id=".$id."";#updated_at
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			$row = pg_fetch_row($result);
			pg_free_result($result);

			$query="DELETE FROM public.visit_price WHERE id_visit=".$id."";
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			$row = pg_fetch_row($result);
			pg_free_result($result);
						
			echo xml('<message>visit_paid_canceled</message>');
		}else{
			echo xml('<error>visit_not_found</error>');
		}
	}

	function support_delete_pet_owner_link($configuration){
		$id=$_GET["id"];
	
		$query_delete='DELETE FROM public.pets_to_owner WHERE id='.$id.'';
		$result = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
		pg_free_result($result);

		echo xml('<message>pet_owner_link_deleted</message>');
	}

	function support_delete_organization_xml($configuration){
		$id=$_GET["id"];
	
		$query_delete='DELETE FROM public.organizations WHERE id='.$id.'';
		$result = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
		pg_free_result($result);

		echo xml('<message>organization_deleted</message>');
	}

	function support_delete_disease_xml($configuration){
		$id=$_GET["id"];

		//связь с специализацией услуг
		// $query='UPDATE gov_services SET ';
		// $query.='id_specialization = NULL ';
		// $query.='WHERE id_specialization='.$id.'';
		// $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		// $row = pg_fetch_row($result);
		// pg_free_result($result);

		//связь с специализацией врача
		// $query_delete='DELETE FROM public.diseases WHERE id_specialization='.$id.'';
		// $result = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
		// pg_free_result($result);
	
		$query_delete='DELETE FROM public.diseases WHERE id='.$id.'';
		$result = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
		pg_free_result($result);

		echo xml('<message>disease_deleted</message>');
	}

	function support_delete_breed_xml($configuration){
		$id=$_GET["id"];

		$query_delete='DELETE FROM public.breeds WHERE id='.$id.'';
		$result = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
		pg_free_result($result);

		echo xml('<message>breed_deleted</message>');
	}

	function support_delete_specie_xml($configuration){
		$id=$_GET["id"];

		$query_delete='DELETE FROM public.species WHERE id='.$id.'';
		$result = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
		pg_free_result($result);

		echo xml('<message>specie_deleted</message>');
	}

	function support_delete_specialization_xml($configuration){
		$id=$_GET["id"];

		//связь с специализацией услуг
		$query='UPDATE gov_services SET ';
		$query.='id_specialization = NULL ';
		$query.='WHERE id_specialization='.$id.'';
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		$row = pg_fetch_row($result);
		pg_free_result($result);

		//связь с специализацией врача
		$query_delete='DELETE FROM public.users_specializations WHERE id_specialization='.$id.'';
		$result = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
		pg_free_result($result);
	
		$query_delete='DELETE FROM public.specializations WHERE id='.$id.'';
		$result = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
		pg_free_result($result);

		echo xml('<message>specialization_deleted</message>');
	}

	function support_delete_informing_xml($configuration){
		$id=$_GET["id"];
	
		$query_delete='DELETE FROM public.informings_organizations WHERE id_informing='.$id.'';
		$result = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
		pg_free_result($result);

		$query_delete='DELETE FROM public.informings WHERE id='.$id.'';
		$result = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
		pg_free_result($result);

		echo xml('<message>informing_deleted</message>');
	}
	
	function support_return_pet($configuration){
		$id=$_GET["id"];

		$query='SELECT id FROM users WHERE login=\''.string_formating_for_sql($_COOKIE['login']).'\' LIMIT 1';
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		$id_user=pg_fetch_result($result, 0);
		pg_free_result($result);
	
		$query='UPDATE pets SET ';
        $query.='reg_expire_date=NULL, ';
        $query.='id_reg_expire_reason=NULL, ';
        $query.="updated_by=".$id_user.",";#updated_by
        $query.="updated_at=NOW()::timestamp(0)";#updated_at
        $query.=' WHERE id='.$id.';';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_row($result);
        pg_free_result($result);

		echo xml('<message>pet_returned</message>');
	}
	function support_departure_pet($configuration){
		$id=$_GET["id"];

		$query='SELECT id FROM users WHERE login=\''.string_formating_for_sql($_COOKIE['login']).'\' LIMIT 1';
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		$id_user=pg_fetch_result($result, 0);
		pg_free_result($result);
	
		$query='UPDATE pets SET ';
        $query.='reg_expire_date=NOW()::timestamp(0), ';
        $query.='id_reg_expire_reason=15, ';
        $query.="updated_by=".$id_user.",";#updated_by
        $query.="updated_at=NOW()::timestamp(0)";#updated_at
        $query.=' WHERE id='.$id.';';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_row($result);
        pg_free_result($result);

		echo xml('<message>pet_departured</message>');
	}

	function support_return_owner($configuration){
		$id=$_GET["id"];

		$query='SELECT id FROM users WHERE login=\''.string_formating_for_sql($_COOKIE['login']).'\' LIMIT 1';
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		$id_user=pg_fetch_result($result, 0);
		pg_free_result($result);
	
		$query='UPDATE pet_owners SET ';
        $query.='is_deleted=false, ';
        $query.="updated_by=".$id_user.",";#updated_by
        $query.="updated_at=NOW()::timestamp(0)";#updated_at
        $query.=' WHERE id='.$id.';';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_row($result);
        pg_free_result($result);

		echo xml('<message>owner_returned</message>');
	}
	function support_delete_owner($configuration){
		$id=$_GET["id"];

		$query='SELECT id FROM users WHERE login=\''.string_formating_for_sql($_COOKIE['login']).'\' LIMIT 1';
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		$id_user=pg_fetch_result($result, 0);
		pg_free_result($result);
	
		$query='UPDATE pet_owners SET ';
        $query.='is_deleted=true, ';
        $query.="updated_by=".$id_user.",";#updated_by
        $query.="updated_at=NOW()::timestamp(0)";#updated_at
        $query.=' WHERE id='.$id.';';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_row($result);
        pg_free_result($result);

		echo xml('<message>owner_deleted</message>');
	}

	function support_return_specialist($configuration){
		$id=$_GET["id"];

		$query='SELECT id FROM users WHERE login=\''.string_formating_for_sql($_COOKIE['login']).'\' LIMIT 1';
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		$id_user=pg_fetch_result($result, 0);
		pg_free_result($result);
	
		$query='UPDATE specialists SET ';
        $query.='expel_date=NULL, ';
        $query.="updated_by=".$id_user.",";#updated_by
        $query.="updated_at=NOW()::timestamp(0)";#updated_at
        $query.=' WHERE id='.$id.';';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_row($result);
        pg_free_result($result);

		echo xml('<message>specialist_returned</message>');
	}

	function support_dismiss_specialist($configuration){
		$id=$_GET["id"];

		$query='SELECT id FROM users WHERE login=\''.string_formating_for_sql($_COOKIE['login']).'\' LIMIT 1';
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		$id_user=pg_fetch_result($result, 0);
		pg_free_result($result);
	
		$query='UPDATE specialists SET ';
        $query.='expel_date=NOW(), ';
        $query.="updated_by=".$id_user.",";#updated_by
        $query.="updated_at=NOW()::timestamp(0)";#updated_at
        $query.=' WHERE id='.$id.';';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_row($result);
        pg_free_result($result);

		echo xml('<message>specialist_dismissed</message>');
	}

	function support_delete_specialist($configuration){
		$id=$_GET["id"];

		$query_delete='DELETE FROM specialists WHERE id='.$id.';';
		$result = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
		pg_free_result($result);

		echo xml('<message>specialist_deleted</message>');
	}

	function support_add_specialist_role($configuration){
		$role=$_GET["role"];
		$id_user=$_GET["user"];
		$id_specialist=$_GET["specialist"];

		$query='INSERT INTO auth_assignment ';
		$query.='(item_name, id_user, id_specialist) ';
		$query.='VALUES (';
		$query.='\''.$role.'\',';
		$query.=''.$id_user.',';
		$query.=''.$id_specialist.'';
		$query.=') RETURNING item_name;';
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		$row = pg_fetch_row($result);
		$item_name=$row[0];
		pg_free_result($result);

		echo xml('<message>specialist_role_added</message>');
	}

	function support_add_element_role($configuration){
		$role=$_GET["role"];
		$element=$_GET["element"];
		
		$query='INSERT INTO auth_item_child ';
		$query.='(parent, child) ';
		$query.='VALUES (';
		$query.='\''.$role.'\',';
		$query.='\''.$element.'\'';
		$query.=') RETURNING parent;';
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		$row = pg_fetch_row($result);
		$parent=$row[0];
		pg_free_result($result);

		echo xml('<message>element_role_added</message>');
	}

	function support_delete_element_role_xml($configuration){
		$role=$_GET["role"];
		$element=$_GET["element"];
	
		$query_delete='DELETE FROM public.auth_item_child WHERE parent=\''.$role.'\' AND  child=\''.$element.'\'';
		$result = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
		pg_free_result($result);

		echo xml('<message>element_role_deleted</message>');
	}

	function support_add_element($configuration){
		$name=$_GET["name"];
		$description=$_GET["description"];
		
		$query='INSERT INTO auth_item ';
		$query.='(name, description, type) ';
		$query.='VALUES (';
		$query.='\''.$name.'\',';
		$query.='\''.$description.'\',';
		$query.='\'2\'';
		$query.=') RETURNING name;';
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		$row = pg_fetch_row($result);
		$name=$row[0];
		pg_free_result($result);

		echo xml('<message>element_added</message>');
	}

	function support_add_user_org($configuration){
		$user=$_GET["user"];
		$organization=$_GET["organization"];

		$id_user = user_id($configuration);

		$query='INSERT INTO specialists ';
		$query.='(reg_date, id_organization, id_user, created_at, created_by, updated_at, updated_by) ';
		$query.='VALUES (';
		$query.="NOW()::timestamp(0),";#reg_date
		$query.=''.$organization.',';
		$query.=''.$user.',';
		$query.="NOW()::timestamp(0),";#created_at
		$query.="".$id_user.",";#created_by
    	$query.="NOW()::timestamp(0),";#updated_at
		$query.="".$id_user."";#created_by
		$query.=') RETURNING id;';
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		$row = pg_fetch_row($result);
		$id=$row[0];
		pg_free_result($result);

		echo xml('<message>user_org_added</message>');
	}

	function support_add_user($configuration){
		$login=$_POST["login"];
		$password=$_POST["password"];
		$email=$_POST["email"];
		$surname=$_POST["surname"];
		$name=$_POST["name"];
		$secondname=$_POST["secondname"];
		$birthday=$_POST["birthday"];
		$gender=$_POST["gender"];

		$query='SELECT id FROM users WHERE login=\''.string_formating_for_sql($_COOKIE['login']).'\' LIMIT 1';
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		$id_user=pg_fetch_result($result, 0);
		pg_free_result($result);

		$keyString = 'KEUDfBcb+SiOarQeWwT5G9Zf4R5JZ8zu7x1ClgR+th4=';
		$key = base64_decode($keyString);
		$auth_key = \Yii::$app->security->generateRandomString();
		$password_ = base64_encode(\Yii::$app->security->encryptByKey($password, $key));
 
		$query='INSERT INTO users ';
		$query.='(login, password, auth_key, email, is_blocked, is_deleted, is_system_user, is_temp_password, f_fio, i_fio, o_fio, fullname, birthday, sex, password_valid_till, password_valid_till_min, created_at, created_by, updated_at, updated_by) ';
		$query.='VALUES (';

		$query.='\''.$login.'\',';
		$query.='\''.$password_.'\',';
		$query.='\''.$auth_key.'\',';
		$query.='\''.$email.'\',';
		
		$query.='\'false\',';
		$query.='\'false\',';
		$query.='\'false\',';
		$query.='\'false\',';

		$query.='\''.$surname.'\',';
		$query.='\''.$name.'\',';
		$query.='\''.$secondname.'\',';

		$query.='\''.$surname.' '.$name.' '.$secondname.'\',';

		$query.='\''.$birthday.'\',';
		$query.='\''.$gender.'\',';
		
		
		$query.="NOW() + INTERVAL '365 DAY',";
		$query.="NOW() + INTERVAL '365 DAY',";

		$query.="NOW()::timestamp(0),";#created_at
		$query.="".$id_user.",";#created_by
    	$query.="NOW()::timestamp(0),";#updated_at
		$query.="".$id_user."";#created_by
		$query.=') RETURNING id;';

		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		$row = pg_fetch_row($result);
		$id=$row[0];
		pg_free_result($result);

		echo xml('<message>user_added</message>');
	}
	
	function support_delete_specialist_role($configuration){
		$role=$_GET["role"];
		$id_user=$_GET["user"];
		$id_specialist=$_GET["specialist"];

		$query='DELETE FROM auth_assignment ';
        $query.=' WHERE item_name=\''.$role.'\' AND id_user='.$id_user.' AND id_specialist='.$id_specialist.';';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $row = pg_fetch_row($result);
        pg_free_result($result);
		
		echo xml('<message>specialist_role_deleted</message>');
	}

	function support_get_user_password($configuration){
		$password_hash=$_GET["password_hash"];

		$keyString = 'KEUDfBcb+SiOarQeWwT5G9Zf4R5JZ8zu7x1ClgR+th4=';
		if (empty($keyString)) {//не задан ключ
			#'You should specify passwordEncryptionKey'
		}
		$key = base64_decode($keyString);
		$user_password = \Yii::$app->security->decryptByKey(base64_decode($password_hash), $key);
		
		echo xml('<user_password>'.$user_password.'</user_password>');
	}

	function support_show_organizations_xml($config){
		###
		$id=$_GET["id"];
		$name = $_GET["name"];
		$organization_type = $_GET["organization_type"];
		###
	
		header("Content-type: text/xml; charset=utf-8");
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<xml>';
	
		$query='SELECT public.organizations.*, public.org_types.name AS type_name, public.areas.name AS area_name, public.areas.id AS area_id, ';
		$query.='fias_addresses.id AS fias, fias_addresses.full_address AS address, ';
		$query.='public.districts.name AS district_name, p_organizations.name AS parent_name, m_organizations.name AS managing_name ';
			
		$query.='FROM public.organizations ';
		$query.='LEFT JOIN fias_addresses ON organizations.id_fias_address=fias_addresses.id ';#адрес
		$query.='LEFT JOIN public.areas ON organizations.id_area=areas.id ';
		$query.='LEFT JOIN public.districts ON organizations.id_district=districts.id ';
		$query.='LEFT JOIN public.org_types ON organizations.id_org_type=org_types.id ';
		$query.='LEFT JOIN public.organizations AS p_organizations ON organizations.parent_id=p_organizations.id ';
		$query.='LEFT JOIN public.organizations AS m_organizations ON organizations.managing_organization_id=m_organizations.id ';
					
		#ПОИСК
		if(string_formating_for_sql($id) || string_formating_for_sql($name) || string_formating_for_sql($organization_type)){
			$query.='WHERE ';
		}
		$query_='';
		if(string_formating_for_sql($id)){
			if($query_){$query_.=' AND ';}
			$query_.='public.organizations.id='.string_formating_for_sql($id).'';
		}
		if(string_formating_for_sql($name)){
			if($query_){$query_.=' AND ';}
			$query_.='public.organizations.name LIKE \'%'.string_formating_for_sql($name).'%\'';
		}
		if(string_formating_for_sql($organization_type)){
			if($query_){$query_.=' AND ';}
			$query_.='public.organizations.id_org_type='.string_formating_for_sql($organization_type).'';
		}
		if($query_){$query.=$query_;}
		#ПОИСК
		//$query.='GROUP BY public.pets.id, public.breeds.name, public.species.name, public.reg_expire_reasons.name ';

		//echo $query;

		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
	
		while ($row = pg_fetch_assoc($result)) {
			echo '<rec>';
			echo '<rec_id>'.$row['id'].'</rec_id>';
			echo '<rec_short_name>'.$row['short_name'].'</rec_short_name>';
			echo '<rec_name>'.$row['name'].'</rec_name>';
			
			echo '<rec_type_id>'.$row['id_org_type'].'</rec_type_id>';
			echo '<rec_type_name>'.$row['type_name'].'</rec_type_name>';
			echo '<rec_is_hidden>' . $row['hidden'] . '</rec_is_hidden>';
			echo '<rec_parent_id>'.$row['parent_id'].'</rec_parent_id>';
			echo '<rec_parent_name>'.$row['parent_name'].'</rec_parent_name>';

			//служебные константы/флаги
			echo '<rec_organization_type_const>'.$row['organization_type_const'].'</rec_organization_type_const>';
			echo '<rec_is_managing_organization>'.$row['is_managing_organization'].'</rec_is_managing_organization>';
			//служебные константы/флаги

			echo '<rec_managing_id>'.$row['managing_organization_id'].'</rec_managing_id>';
			echo '<rec_managing_name>'.$row['managing_name'].'</rec_managing_name>';

			echo '<rec_area_id>'.$row['area_id'].'</rec_area_id>';
			echo '<rec_area_name>'.$row['area_name'].'</rec_area_name>';
			echo '<rec_district_name>'.$row['district_name'].'</rec_district_name>';

			echo '<rec_inn>'.$row['inn'].'</rec_inn>';
			echo '<rec_kpp>'.$row['kpp'].'</rec_kpp>';
			echo '<rec_ogrn>'.$row['ogrn'].'</rec_ogrn>';
			echo '<rec_reg_number>'.$row['reg_number'].'</rec_reg_number>';

			echo '<rec_fias>'.$row['fias'].'</rec_fias>';
			echo '<rec_address>'.$row['address'].'</rec_address>';
			echo '<rec_latitude>'.$row['latitude'].'</rec_latitude>';
			echo '<rec_longitude>'.$row['longitude'].'</rec_longitude>';

			echo '<rec_reseption_corpses>'.$row['reseption_corpses'].'</rec_reseption_corpses>';
			echo '<rec_free_vaccination>'.$row['free_vaccination'].'</rec_free_vaccination>';
			echo '<rec_pet_registration>'.$row['pet_registration'].'</rec_pet_registration>';
			
			echo '<rec_chief_name>'.$row['chief_name'].'</rec_chief_name>';
			echo '<rec_chief_position>'.$row['chief_position'].'</rec_chief_position>';
			
			echo '</rec>';
		}
		pg_free_result($result);

		echo '</xml>';
	}

	function support_save_organization_xml($configuration){
		$id=$_POST["id"];
		$name=$_POST["name"];
		$short_name=$_POST["short_name"];
		$parent=$_POST["parent"];
		$managing=$_POST["managing"];
		$type=$_POST["type"];
		$area=$_POST["area"];
		$inn=$_POST["inn"];
		$kpp=$_POST["kpp"];
		$ogrn=$_POST["ogrn"];
		$reg_number=$_POST["reg_number"];

		$id_fias_from_table = '';
		$address=$_POST["address"];
		$latitude=$_POST["latitude"];
		$longitude=$_POST["longitude"];
		$hidden = $_POST['is_hidden'] && $_POST['is_hidden'] == 'on' ? 'true' : 'false';
		$query='SELECT id FROM public.users WHERE login=\''.string_formating_for_sql($_COOKIE['login']).'\' LIMIT 1';
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		$id_user=pg_fetch_result($result, 0);
		pg_free_result($result);

		$const='';
		if(string_formating_for_sql($type)){
			$query='SELECT const FROM public.org_types WHERE id=\''.string_formating_for_sql($type).'\' LIMIT 1';
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			$const=pg_fetch_result($result, 0);
			pg_free_result($result);
		}

		if($name){
			//ФИАС
			if($address != ''){
				if(strripos($address, '-') > 1){
					//поиск fias по текущим данным
					$id_fias = str_replace("[ ", "", $address);
					$id_fias = str_replace(" ]", "", $id_fias);
					$fias_tokken=fias_request($configuration);
					
					$fias_address_=fias_address($fias_tokken->access_token, $id_fias, $configuration);
					$fias_address=$fias_address_->suggestions[0];
					file_put_contents('debug.log',print_r($fias_address_->suggestions[0], true), FILE_APPEND);
					
					$query='SELECT id FROM fias_addresses WHERE ';
					if($fias_address->data->house_fias_id || $fias_address->data->house_fias_id != 'null'){
						$query.='houseguid=\''.string_formating_for_sql($fias_address->data->house_fias_id).'\' AND ';
					}else{
						$query.='houseguid IS NULL AND ';
					}
					$query.='cityguid=\''.string_formating_for_sql($fias_address->data->region_fias_id).'\' AND ';
					if($fias_address->data->street_fias_id || $fias_address->data->street_fias_id != 'null'){
						$query.='streetguid=\''.string_formating_for_sql($fias_address->data->street_fias_id).'\' AND ';
					}else{
						$query.='streetguid IS NULL AND ';
					}
					$query.='roomguid IS NULL ';
					$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
					$id_fias_from_table=pg_fetch_result($result, 0);
					pg_free_result($result);
					
					if(!$id_fias_from_table){//fias адрес не найден в таблице
						// "ш"
						// "ул"
						$street_prefix="";
						if($fias_address->data->street_type == 'ш'){$street_prefix='шоссе';}else{$street_prefix='улица';}
		
						if($fias_address->highlight_value){
							$full_address=$fias_address->highlight_value;
						}else{
							$full_address='город '.$fias_address->data->region.', '.$street_prefix.' '.$fias_address->data->street.', дом '.$fias_address->data->house.'';
						}
		
						$lon=$fias_address->data->polygon->coordinates[0][0][0][0];
						$lat=$fias_address->data->polygon->coordinates[0][0][0][1];
		
						//$fias_address->data->adm_area
						//сравнение адреса
						//Северный административный округ
						//в БД
						//Северный Административный Округ
		
						$query='INSERT INTO fias_addresses ';
						$query.='(full_address, lon, lat, region, city, street, house, cityguid, streetguid, houseguid, roomguid, created_by, updated_by, created_at, updated_at) ';
						$query.='VALUES (';
						$query.="'".$full_address."',";
		
						if($lon){$query.="'".$lon."',";}else{$query.="NULL,";}
						if($lat){$query.="'".$lat."',";}else{$query.="NULL,";}
		
						$query.="'город ".$fias_address->data->region."',";
						$query.="'город ".$fias_address->data->region."',";
		
						if($fias_address->data->street){$query.="'".$street_prefix." ".$fias_address->data->street."',";}else{$query.="NULL,";}
						if($fias_address->data->house){$query.="'дом ".$fias_address->data->house."',";}else{$query.="NULL,";}
						$query.="'".$fias_address->data->region_fias_id."',";
						if($fias_address->data->street_fias_id){$query.="'".$fias_address->data->street_fias_id."',";}else{$query.="NULL,";}
						if($fias_address->data->house_fias_id){$query.="'".$fias_address->data->house_fias_id."',";}else{$query.="NULL,";}
						$query.="NULL,";
		
						$query.="".$id_user.",";#created_by
						$query.="".$id_user.",";#updated_by
						$query.="NOW()::timestamp(0),";#created_at
						$query.="NOW()::timestamp(0)";#updated_at
						$query.=') RETURNING id;';
						$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
						$row = pg_fetch_row($result);
						$id_fias_from_table=$row[0];
						pg_free_result($result);
					}
				}else{
					$id_fias = str_replace("[ ", "", $address);
					$id_fias = str_replace(" ]", "", $id_fias);
					
					$id_fias_from_table = $id_fias;
				}
			}
			//ФИАС

			if($id){
				$query='UPDATE public.organizations SET ';
				$query.='name=\''.$name.'\', ';
				$query.='short_name=\''.$short_name.'\',';
				
				if(string_formating_for_sql($type)){
					$query.="id_org_type=".$type.",";
				}else{
					$query.="id_org_type=NULL,";
				}

				if($area){
					$query.="id_area=".$area.",";
				}else{
					$query.="id_area=NULL,";
				}

				if($const){
					$query.="organization_type_const='".$const."',";
				}else{
					$query.="organization_type_const=NULL,";
				}

				if($const == 'operating'){$query.="is_managing_organization='true',";}else{$query.="is_managing_organization='false',";}

				if($parent){$query.="parent_id=".$parent.",";}else{$query.="parent_id=0,";}
				if($managing){$query.="managing_organization_id=".$managing.",";}else{$query.="managing_organization_id=NULL,";}

				if($inn){$query.="inn=".$inn.",";}else{$query.="inn=NULL,";}
				if($kpp){$query.="kpp=".$kpp.",";}else{$query.="kpp=NULL,";}
				if($ogrn){$query.="ogrn=".$ogrn.",";}else{$query.="ogrn=NULL,";}
				if($reg_number){$query.="reg_number=".$reg_number.",";}else{$query.="reg_number=NULL,";}

				if($latitude){$query.="latitude=".$latitude.",";}else{$query.="latitude=NULL,";}
				if($longitude){$query.="longitude=".$longitude.",";}else{$query.="longitude=NULL,";}

				if($id_fias_from_table){
					$query.="id_fias_address=".$id_fias_from_table.",";#адрес
				}else{
					$query.="id_fias_address=NULL,";
				}
				$query .= " hidden = $hidden, ";
				$query.="updated_by=".$id_user.",";#updated_by
				$query.="updated_at=NOW()::timestamp(0)";#updated_at
				$query.=' WHERE id='.$id.';';
				$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
				$row = pg_fetch_row($result);
				pg_free_result($result);

				echo xml('<message>oranization_saved</message>');
			}else{
				$query='INSERT INTO public.organizations ';
				$query.='(';
				$query.='name, ';
				$query.='short_name, ';

				$query.='id_org_type, ';
				$query.='id_area, ';
				$query.='organization_type_const, ';
				$query.='is_managing_organization, ';

				$query.='parent_id, ';
				$query.='managing_organization_id, ';

				$query.='inn, ';
				$query.='kpp, ';
				$query.='ogrn, ';
				$query.='reg_number, ';


				$query.='id_fias_address, ';
				$query.='latitude, ';
				$query.='longitude, ';
				
				$query.='created_at, created_by) ';
				$query.='VALUES (';
				$query.='\''.$name.'\', ';
				$query.='\''.$short_name.'\',';

				if(string_formating_for_sql($type)){$query.="".$type.",";}else{$query.="NULL,";}
				if($area){$query.="".$area.",";}else{$query.="NULL,";}
				if($const){$query.="'".$const."',";}else{$query.="NULL,";}
				if($const == 'operating'){$query.="'true',";}else{$query.="'false',";}
				
				if($parent){$query.="".$parent.",";}else{$query.="0,";}
				if($managing){$query.="".$managing.",";}else{$query.="NULL,";}

				if($inn){$query.="".$inn.",";}else{$query.="NULL,";}
				if($kpp){$query.="".$kpp.",";}else{$query.="NULL,";}
				if($ogrn){$query.="".$ogrn.",";}else{$query.="NULL,";}
				if($reg_number){$query.="".$reg_number.",";}else{$query.="NULL,";}

				if($id_fias_from_table){$query.="".$id_fias_from_table.",";}else{$query.="NULL,";}
				if($latitude){$query.="".$latitude.",";}else{$query.="NULL,";}
				if($longitude){$query.="".$longitude.",";}else{$query.="NULL,";}

				$query.="NOW()::timestamp(0),";#created_at
				$query.="".$id_user."";#created_by
				$query.=');';
				$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
				$row = pg_fetch_row($result);
				pg_free_result($result);

				echo xml('<message>oranization_added</message>');
			}
		}else{
			echo xml('<message>fields_not_filled</message>');
		}
	}

	function support_save_specializations_xml($configuration){
		$user=$_POST["user"];
		$specializations=$_POST["specializations"];
		$specializationsArray = explode(",", $specializations);

		$id_user=user_id($configuration);

		$query_delete="DELETE FROM users_specializations WHERE id_user=".$user." ";
		$result_delete = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
		pg_free_result($result_delete);

		for($i=0; $i<=count($specializationsArray);$i++){
			if($specializationsArray[$i] != ''){
				$id_specialization = str_replace("[ ", "", $specializationsArray[$i]);
				$id_specialization = str_replace(" ]", "", $id_specialization);

				$query='INSERT INTO users_specializations ';
				$query.='(id_specialization, id_user, created_at, created_by) ';
				$query.='VALUES (';
				$query.="".$id_specialization.",";
				$query.="".$user.",";
				$query.="NOW()::timestamp(0),";#created_at
				$query.="".$id_user."";#created_by
				$query.=');';
				$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
				$row = pg_fetch_row($result);
				pg_free_result($result);
			}
		}
		
		echo xml('<message>specializations_saved</message>');
	}

	function support_show_pets_xml($config){
		###
		$id=$_GET["id"];
		$name = $_GET["name"];
		###
	
		header("Content-type: text/xml; charset=utf-8");
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<xml>';
	
		if($id || $name){
			$query='SELECT public.pets.*, public.breeds.name AS breed_name, public.species.name AS species_name, public.reg_expire_reasons.name AS expire_reason, ';#pets_to_owner.id_owner_type, 
			$query.='string_agg(pet_owners.id::character varying, \',\') AS pet_owners ';
			//$query.='string_agg(elk.pets.ext_id::character varying, \', \') AS ext_ids ';
			$query.='FROM public.pets ';
			// $query.='LEFT JOIN fias_addresses ON pet_owners.id_fias_address=fias_addresses.id ';#адрес
			$query.='LEFT JOIN public.pets_to_owner ON pets.id=pets_to_owner.id_pet ';
			$query.='LEFT JOIN public.pet_owners ON pets_to_owner.id_owner=pet_owners.id ';
			$query.='LEFT JOIN public.breeds ON pets.id_breed = breeds.id ';
            $query.='LEFT JOIN public.species ON pets.id_species = species.id ';
			$query.='LEFT JOIN public.reg_expire_reasons ON pets.id_reg_expire_reason = reg_expire_reasons.id ';
			#$query.='LEFT JOIN elk.pets ON elk.pets.id_pet=public.pets.id ';
			
			#ПОИСК
			$query.='WHERE id_pet_tmp IS NULL';
			if(string_formating_for_sql($id)){
				$query.=' AND public.pets.id='.string_formating_for_sql($id).'';
			}
			if(string_formating_for_sql($name)){
				$query.=' AND public.pets.name LIKE \'%'.string_formating_for_sql($name).'%\'';
			}
			#ПОИСК

			// $query.=' AND pet_owners.is_deleted=false AND pet_owners.is_main=true ';
			// if($name != ''){
			// 	$query.=' AND (contacts.name LIKE \'+7'.string_formating_for_sql($telephone).'%\' OR contacts.name = \'+7'.string_formating_for_sql($telephone).'\')';
			// }
			$query.='GROUP BY public.pets.id, public.breeds.name, public.species.name, public.reg_expire_reasons.name ';
			// $query.='ORDER BY pet_owners.fullname,pet_owners.id ASC ';
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
	
			while ($row = pg_fetch_assoc($result)) {
				echo '<rec>';
				echo '<rec_id>'.$row['id'].'</rec_id>';
				echo '<rec_name>'.$row['name'].'</rec_name>';
				echo '<rec_birthday>'.$row['birthday'].'</rec_birthday>';
				echo '<rec_sex>'.$row['sex'].'</rec_sex>';
				
				echo '<rec_expire_reason>'.$row['expire_reason'].'</rec_expire_reason>';
				echo '<rec_breed_name>'.$row['breed_name'].'</rec_breed_name>';
				echo '<rec_species_name>'.$row['species_name'].'</rec_species_name>';

				echo '<rec_ext_ids>'.$row['ext_ids'].'</rec_ext_ids>';
				
				$owners = explode(",", $row['pet_owners']);
				if(count($owners) > 0 && $row['pet_owners'] != ''){
					echo '<rec_owners>';

					$query="SELECT pet_owners.*, pets_to_owner.id_owner_type, pets_to_owner.id_owner_type, pets_to_owner.id AS pets_to_owner_id FROM pet_owners ";
					$query.="LEFT JOIN pets_to_owner ON pet_owners.id=pets_to_owner.id_owner ";
                    $query.="WHERE (";
                    $k=0;
                    for ($j=0; $j<count($owners); $j++) {
                        if($owners[$j] != ''){
                            if($k>0){$query.=' OR ';}
                            $query.='pet_owners.id = '.string_formating_for_sql($owners[$j]).' ';
                            $k++;
                        }
                    }
					$query.=") AND pets_to_owner.id_pet=".$row['id']." ";

					$query.="ORDER BY pet_owners.fullname";
					
					$result_o = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
                    while ($row_o = pg_fetch_assoc($result_o)) {
						echo '<owner>';
						echo '<id>'.$row_o['id'].'</id>';
						echo '<pets_to_owner_id>'.$row_o['pets_to_owner_id'].'</pets_to_owner_id>';
						echo '<fullname>'.$row_o['fullname'].'</fullname>';
						echo '<is_legal>'.$row_o['is_legal'].'</is_legal>';
						echo '<id_owner_type>'.$row_o['id_owner_type'].'</id_owner_type>';
						echo '</owner>';
					}
					pg_free_result($result_o);
					echo '</rec_owners>';
				}

				echo '</rec>';
			}
			pg_free_result($result);
			
		}else{
			echo '<message>fields_not_filled</message>';
		}
		echo '</xml>';
	}

	function support_show_identifications_xml($config){
		###
		$identification_code=$_GET["identification_code"];
		$type = $_GET["type"];
		###
	
		header("Content-type: text/xml; charset=utf-8");
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<xml>';
	
		if($identification_code || $type){
			$query='SELECT public.pet_identification.*, public.identification_types.name AS type ';
			$query.='FROM public.pet_identification ';
			$query.='LEFT JOIN public.identification_types ON pet_identification.id_ident_type=identification_types.id ';
			
			#ПОИСК
			$query.='WHERE pet_identification.id IS NOT NULL';
			if(string_formating_for_sql($identification_code)){
				$query.=' AND public.pet_identification.identification_code=\''.string_formating_for_sql($identification_code).'\'';
			}
			if(string_formating_for_sql($type)){
				$query.=' AND public.pet_identification.id_ident_type='.string_formating_for_sql($type).'';
			}
			#ПОИСК
			
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());	
			while ($row = pg_fetch_assoc($result)) {
				echo '<rec>';
				echo '<rec_id>'.$row['id'].'</rec_id>';
				echo '<rec_id_pet>'.$row['id_pet'].'</rec_id_pet>';
				echo '<rec_type>'.$row['type'].'</rec_type>';
				echo '<rec_identification_code>'.$row['identification_code'].'</rec_identification_code>';
				echo '<rec_main_flag>'.$row['main_flag'].'</rec_main_flag>';
				echo '</rec>';
			}
			pg_free_result($result);
			
		}else{
			echo '<message>fields_not_filled</message>';
		}
		echo '</xml>';
	}

	function support_show_temp_pets_xml($config){
		###
		$name = $_GET["name"];
		###
	
		header("Content-type: text/xml; charset=utf-8");
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<xml>';
		echo 'dss';
	
		if($name){
			$query='SELECT public.pets_tmp.*, public.breeds.name AS breed_name, public.species.name AS species_name FROM public.pets_tmp ';
			$query.='LEFT JOIN public.breeds ON pets_tmp.id_breed = breeds.id ';
            $query.='LEFT JOIN public.species ON pets_tmp.id_species = species.id ';
			#ПОИСК
			$query.='WHERE ';
			if(string_formating_for_sql($name)){
				$query.='pets_tmp.name LIKE \'%'.string_formating_for_sql($name).'%\'';
			}
			#ПОИСК
			#echo $query;

			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
	
			while ($row = pg_fetch_assoc($result)) {
				echo '<rec>';
				echo '<rec_id>'.$row['id'].'</rec_id>';
				echo '<rec_name>'.$row['name'].'</rec_name>';
				echo '<rec_birthday>'.$row['birthday'].'</rec_birthday>';
				echo '<rec_sex>'.$row['sex'].'</rec_sex>';
				echo '<rec_breed_name>'.$row['breed_name'].'</rec_breed_name>';
				echo '<rec_species_name>'.$row['species_name'].'</rec_species_name>';
				echo '<rec_created_at>'.$row['created_at'].'</rec_created_at>';
				echo '<rec_updated_at>'.$row['updated_at'].'</rec_updated_at>';
				echo '</rec>';
			}
			pg_free_result($result);
			
		}else{
			echo '<message>fields_not_filled</message>';
		}
		echo '</xml>';
	}

	function support_show_owners_xml($config){
		###
		$id=$_GET["id"];
		$id_main_owner=$_GET["id_main_owner"];
		$fullname = $_GET["fullname"];
		###
	
		header("Content-type: text/xml; charset=utf-8");
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<xml>';
	
		if($id || $id_main_owner || $fullname){
			$query='SELECT public.pet_owners.*, ';
			$query.='string_agg(contacts.id::character varying, \',\') AS contacts, ';
			$query.='string_agg(pets.id::character varying, \',\') AS pets ';
			$query.='FROM public.pet_owners ';
			$query.='LEFT JOIN contacts ON pet_owners.id=contacts.entity_id ';#контакты
			$query.='LEFT JOIN pets_to_owner ON pet_owners.id=pets_to_owner.id_owner ';#животные
        	$query.='LEFT JOIN pets ON pets_to_owner.id_pet=pets.id ';#животные
			
			#ПОИСК
			$query.='WHERE id_pet_owner_tmp IS NULL';
			if(string_formating_for_sql($id)){
				$query.=' AND pet_owners.id='.string_formating_for_sql($id).'';
			}
			if(string_formating_for_sql($id_main_owner)){
				$query.=' AND pet_owners.id_main_owner='.string_formating_for_sql($id_main_owner).'';
			}
			if(string_formating_for_sql($fullname)){
				$query.=' AND pet_owners.fullname LIKE \'%'.string_formating_for_sql($fullname).'%\'';
			}
			#ПОИСК
			// echo $query;
			$query.='GROUP BY pet_owners.id ';
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
	
			while ($row = pg_fetch_assoc($result)) {
				echo '<rec>';
				echo '<rec_id>'.$row['id'].'</rec_id>';
				echo '<rec_f_fio>'.$row['f_fio'].'</rec_f_fio>';
				echo '<rec_i_fio>'.$row['i_fio'].'</rec_i_fio>';
				echo '<rec_o_fio>'.$row['o_fio'].'</rec_o_fio>';
				echo '<rec_jur_name>'.$row['jur_name'].'</rec_jur_name>';
				echo '<rec_inn>'.$row['inn'].'</rec_inn>';
				echo '<rec_ogrn>'.$row['ogrn'].'</rec_ogrn>';
				echo '<rec_birthday>'.$row['birthday'].'</rec_birthday>';
				echo '<rec_snils>'.$row['snils'].'</rec_snils>';
				echo '<rec_id_address>'.$row['id_address'].'</rec_id_address>';
				echo '<rec_created_by>'.$row['created_by'].'</rec_created_by>';
				echo '<rec_updated_by>'.$row['updated_by'].'</rec_updated_by>';
				echo '<rec_created_at>'.$row['created_at'].'</rec_created_at>';
				echo '<rec_updated_at>'.$row['updated_at'].'</rec_updated_at>';
				echo '<rec_id_fact_address>'.$row['id_fact_address'].'</rec_id_fact_address>';
				echo '<rec_is_legal>'.$row['is_legal'].'</rec_is_legal>';
				echo '<rec_fullname>'.$row['fullname'].'</rec_fullname>';
				echo '<rec_id_area>'.$row['id_area'].'</rec_id_area>';
				echo '<rec_id_district>'.$row['id_district'].'</rec_id_district>';
				echo '<rec_id_fias_address>'.$row['id_fias_address'].'</rec_id_fias_address>';
				echo '<rec_id_fact_fias_address>'.$row['id_fact_fias_address'].'</rec_id_fact_fias_address>';
				echo '<rec_is_deleted>'.$row['is_deleted'].'</rec_is_deleted>';
				echo '<rec_entrepreneur>'.$row['entrepreneur'].'</rec_entrepreneur>';
				echo '<rec_sso_id>'.$row['sso_id'].'</rec_sso_id>';
				echo '<rec_is_main>'.$row['is_main'].'</rec_is_main>';
				echo '<rec_id_main_owner>'.$row['id_main_owner'].'</rec_id_main_owner>';
				echo '<rec_duble_validation>'.$row['duble_validation'].'</rec_duble_validation>';
				echo '<rec_description>'.$row['description'].'</rec_description>';
				echo '<rec_addresses_is_equal>'.$row['addresses_is_equal'].'</rec_addresses_is_equal>';

				$contacts = ($row['contacts'] != '')?explode(",",$row['contacts']):NULL;
				if(count($contacts) > 0){
					#контакты
					echo '<rec_contacts>';
					$q='SELECT contacts.name,contacts.main_flag,contacts.confirmed,contact_types.name AS contact_type_title, contacts.id_contact_type AS contact_type_id FROM contacts ';
					$q.='LEFT JOIN contact_types ON contacts.id_contact_type = contact_types.id ';
					$q.='WHERE ';

					for($i=0; $i<count($contacts);$i++){
						if($i>0){$q.=' OR ';}
						$q.='contacts.id='.$contacts[$i].'';
					}

					$result_c = pg_query($q) or die('Ошибка запроса: ' . pg_last_error());
					while ($row_c = pg_fetch_assoc($result_c)) {
						echo '<contact>';
						echo '<name>'.$row_c['name'].'</name>';
						echo '<main>'.$row_c['main_flag'].'</main>';
						echo '<confirmed>'.$row_c['confirmed'].'</confirmed>';
						echo '<type_title>'.$row_c['contact_type_title'].'</type_title>';
						echo '<type_id>'.$row_c['contact_type_id'].'</type_id>';
						echo '</contact>';
					}
					pg_free_result($result_c);
					echo '</rec_contacts>';
					#контакты
				}

				$pets = ($row['pets'] != '')?explode(",",$row['pets']):NULL;
				if(count($pets) > 0){
					#питомцы
					echo '<rec_pets>';
					$q='SELECT pets.id,pets.name,pets.sex,breeds.name AS breed_name,species.name AS species_name FROM pets ';
					$q.='LEFT JOIN breeds ON pets.id_breed = breeds.id ';
					$q.='LEFT JOIN species ON pets.id_species = species.id ';
					$q.='WHERE (pets.is_main=true OR pets.is_main IS NULL) AND ';
					$q.='(';
					for($i=0; $i<count($pets);$i++){
						if($i>0){$q.=' OR ';}
						$q.='pets.id='.$pets[$i].'';
					}
					$q.=')';
					#echo $q;

					$result_c = pg_query($q) or die('Ошибка запроса: ' . pg_last_error());
					while ($row_c = pg_fetch_assoc($result_c)) {
						echo '<pet>';
						echo '<id>'.$row_c['id'].'</id>';
						echo '<name>'.$row_c['name'].'</name>';
						echo '<sex>'.$row_c['sex'].'</sex>';
						echo '<breed>'.$row_c['breed_name'].'</breed>';
						echo '<species>'.$row_c['species_name'].'</species>';
						echo '</pet>';
					}
					pg_free_result($result_c);
					echo '</rec_pets>';
					#питомцы
				}

				echo '</rec>';
			}
			pg_free_result($result);
			
		}else{
			echo '<message>fields_not_filled</message>';
		}
		echo '</xml>';
	}

	function support_show_temp_owners_xml($config){
		###
		$id=$_GET["id"];
		$f_fio=$_GET["f_fio"];
		$i_fio=$_GET["i_fio"];
		$o_fio=$_GET["o_fio"];
		###
	
		header("Content-type: text/xml; charset=utf-8");
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<xml>';
	
		if($f_fio || $i_fio || $o_fio){
			$query='SELECT * FROM pet_owners_tmp ';
			#ПОИСК
			$query.='WHERE ';
			if(string_formating_for_sql($f_fio)){
				$query.='f_fio LIKE \'%'.string_formating_for_sql($f_fio).'%\'';
			}
			if(string_formating_for_sql($i_fio)){
				if(string_formating_for_sql($id)){
					$query.=' AND ';	
				}
				$query.='i_fio LIKE \'%'.string_formating_for_sql($i_fio).'%\'';
			}
			if(string_formating_for_sql($o_fio)){
				if(string_formating_for_sql($f_fio) || string_formating_for_sql($i_fio)){
					$query.=' AND ';	
				}
				$query.='o_fio LIKE \'%'.string_formating_for_sql($o_fio).'%\'';
			}
			#ПОИСК
			// echo $query;
			$query.='GROUP BY pet_owners_tmp.id ';
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
	
			while ($row = pg_fetch_assoc($result)) {
				echo '<rec>';
				echo '<rec_id>'.$row['id'].'</rec_id>';
				echo '<rec_f_fio>'.$row['f_fio'].'</rec_f_fio>';
				echo '<rec_i_fio>'.$row['i_fio'].'</rec_i_fio>';
				echo '<rec_o_fio>'.$row['o_fio'].'</rec_o_fio>';
				echo '<rec_birthday>'.$row['birthday'].'</rec_birthday>';
				echo '<rec_snils>'.$row['snils'].'</rec_snils>';

				echo '<rec_created_at>'.$row['created_at'].'</rec_created_at>';
				echo '<rec_updated_at>'.$row['updated_at'].'</rec_updated_at>';
				echo '</rec>';
			}
			pg_free_result($result);
			
		}else{
			echo '<message>fields_not_filled</message>';
		}
		echo '</xml>';
	}

	function support_show_found_pets_ads_xml($config){
		###
		$id=$_GET["id"];
		$service_number=$_GET["service_number"];
		###
	
		header("Content-type: text/xml; charset=utf-8");
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<xml>';
	
		if($id || $service_number){
			$query='SELECT * FROM found_pet.ads ';
			#ПОИСК
			$query.='WHERE ';

			if(string_formating_for_sql($id)){
				$query.='found_pet.ads.id='.string_formating_for_sql($id).'';
			}

			if(string_formating_for_sql($service_number)){
				if(string_formating_for_sql($id)){
					$query.=' AND ';	
				}
				$query.='found_pet.ads.service_number=\''.string_formating_for_sql($service_number).'\'';
			}

			#ПОИСК
			#echo $query;
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
	
			while ($row = pg_fetch_assoc($result)) {
				echo '<rec>';
				echo '<rec_id>'.$row['id'].'</rec_id>';
				echo '<rec_service_number>'.$row['service_number'].'</rec_service_number>';
				echo '<rec_type>'.$row['type'].'</rec_type>';
				echo '<rec_date_event>'.$row['date_event'].'</rec_date_event>';
				echo '<rec_time_event>'.$row['time_event'].'</rec_time_event>';
				echo '<rec_id_address>'.$row['id_address'].'</rec_id_address>';
				echo '<rec_animal_name>'.$row['animal_name'].'</rec_animal_name>';
				echo '<rec_chip>'.$row['chip'].'</rec_chip>';
				echo '<rec_stamp>'.$row['stamp'].'</rec_stamp>';
				echo '<rec_id_species>'.$row['id_species'].'</rec_id_species>';
				echo '<rec_id_breed>'.$row['id_breed'].'</rec_id_breed>';
				echo '<rec_id_color>'.$row['id_color'].'</rec_id_color>';
				echo '<rec_age>'.$row['age'].'</rec_age>';
				echo '<rec_sex>'.$row['sex'].'</rec_sex>';
				echo '<rec_notice>'.$row['notice'].'</rec_notice>';
				echo '<rec_is_active>'.$row['is_active'].'</rec_is_active>';
				echo '<rec_verify_status>'.$row['verify_status'].'</rec_verify_status>';
				echo '<rec_verify_at>'.$row['verify_at'].'</rec_verify_at>';
				echo '<rec_closed_at>'.$row['closed_at'].'</rec_closed_at>';
				echo '<rec_closed_reason>'.$row['closed_reason'].'</rec_closed_reason>';
				echo '<rec_id_author>'.$row['id_author'].'</rec_id_author>';
				echo '<rec_photo>'.$row['photo'].'</rec_photo>';
				echo '<rec_stamp_photo>'.$row['stamp_photo'].'</rec_stamp_photo>';
				echo '<rec_subscriptions>'.$row['subscriptions'].'</rec_subscriptions>';
				echo '<rec_created_at>'.$row['created_at'].'</rec_created_at>';
				echo '<rec_updated_at>'.$row['updated_at'].'</rec_updated_at>';
				echo '<rec_active_till>'.$row['active_till'].'</rec_active_till>';
				echo '<rec_closed_by>'.$row['closed_by'].'</rec_closed_by>';
				echo '<rec_id_specialist>'.$row['id_specialist'].'</rec_id_specialist>';
				echo '<rec_is_priority_for_moderation>'.$row['is_priority_for_moderation'].'</rec_is_priority_for_moderation>';
				echo '<rec_processed>'.$row['processed'].'</rec_processed>';
				echo '</rec>';
			}
			pg_free_result($result);
			
		}else{
			echo '<message>fields_not_filled</message>';
		}
		echo '</xml>';
	}

	function support_show_found_pets_messages_xml($config){
		###
		$service_number=$_GET["service_number"];
		###
	
		header("Content-type: text/xml; charset=utf-8");
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<xml>';
	
		if($service_number){
			$query='SELECT * FROM found_pet.messages ';
			#ПОИСК
			$query.='WHERE ';

			if(string_formating_for_sql($service_number)){
				$query.='found_pet.messages.service_number=\''.string_formating_for_sql($service_number).'\'';
			}

			#ПОИСК
			#echo $query;
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
	
			while ($row = pg_fetch_assoc($result)) {
				echo '<rec>';
				echo '<rec_id>'.$row['id'].'</rec_id>';
				echo '<rec_service_number>'.$row['service_number'].'</rec_service_number>';
				echo '<rec_type>'.$row['type'].'</rec_type>';
				echo '<rec_body>'.$row['body'].'</rec_body>';
				echo '<rec_headers>'.$row['headers'].'</rec_headers>';
				echo '<rec_created_at>'.$row['created_at'].'</rec_created_at>';
				echo '<rec_updated_at>'.$row['updated_at'].'</rec_updated_at>';
				echo '<rec_is_success>'.$row['is_success'].'</rec_is_success>';
				echo '<rec_ad_errors>'.$row['ad_errors'].'</rec_ad_errors>';
				echo '</rec>';
			}
			pg_free_result($result);
			
		}else{
			echo '<message>fields_not_filled</message>';
		}
		echo '</xml>';
	}

	function support_show_found_pets_messages_sent_xml($config){
		###
		$service_number=$_GET["service_number"];
		###
	
		header("Content-type: text/xml; charset=utf-8");
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<xml>';
	
		if($service_number){
			$query='SELECT * FROM found_pet.messages_sent ';
			#ПОИСК
			$query.='WHERE ';

			if(string_formating_for_sql($service_number)){
				$query.='found_pet.messages_sent.service_number=\''.string_formating_for_sql($service_number).'\'';
			}

			#ПОИСК
			#echo $query;
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
	
			while ($row = pg_fetch_assoc($result)) {
				echo '<rec>';
				echo '<rec_id>'.$row['id'].'</rec_id>';
				echo '<rec_service_number>'.$row['service_number'].'</rec_service_number>';
				echo '<rec_type>'.$row['type'].'</rec_type>';
				echo '<rec_request>'.string_formating_for_xml($row['request']).'</rec_request>';
				echo '<rec_response>'.$row['response'].'</rec_response>';
				echo '<rec_response_headers>'.$row['response_headers'].'</rec_response_headers>';
				echo '<rec_response_code>'.$row['response_code'].'</rec_response_code>';
				echo '<rec_curl_error>'.$row['curl_error'].'</rec_curl_error>';
				echo '<rec_user_error>'.$row['user_error'].'</rec_user_error>';
				echo '<rec_created_at>'.$row['created_at'].'</rec_created_at>';
				echo '</rec>';
			}
			pg_free_result($result);
			
		}else{
			echo '<message>fields_not_filled</message>';
		}
		echo '</xml>';
	}

	function support_show_mosru_messages_xml($config){
		###
		$visit_id=$_GET["visit_id"];
		$service_number=$_GET["service_number"];
		$sso_id=$_GET["sso_id"];
		$snils=$_GET["snils"];
		$chip=$_GET["chip"];
		###
	
		header("Content-type: text/xml; charset=utf-8");
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<xml>';
	
		if($visit_id || $service_number || $sso_id || $snils || $chip){
			$query='SELECT etp.message_v2.* FROM etp.message_v2 ';

			if (string_formating_for_sql($snils) || string_formating_for_sql($chip)) {
				$query .= 'LEFT JOIN public.visits ON public.visits.id = etp.message_v2.visit_id ';
				if (string_formating_for_sql($snils)) {
					$query .= 'LEFT JOIN elk.owners ON elk.owners.id = public.visits.id_owner ';
				}

				if (string_formating_for_sql($chip)) {
					$query .= 'LEFT JOIN public.visits_gov_services ON public.visits_gov_services.id_visit = public.visits.id ';
					$query .= 'LEFT JOIN public.pet_identification ON public.pet_identification.id_pet = public.visits_gov_services.id_pet ';
				}
			}

			#ПОИСК
			$query.='WHERE ';

			$needAnd = 0;
			if(string_formating_for_sql($visit_id)){
				$query.='etp.message_v2.visit_id='.string_formating_for_sql($visit_id).'';
				$needAnd = 1;
			}

			if(string_formating_for_sql($service_number)){
				if(string_formating_for_sql($visit_id)){
					$query.=' AND ';	
					$query.=' AND ';
				}
				$query.='etp.message_v2.service_number=\''.string_formating_for_sql($service_number).'\'';
				$needAnd = 1;
			}

			if(string_formating_for_sql($sso_id)){
				if(string_formating_for_sql($service_number) || string_formating_for_sql($visit_id)){
					$query.=' AND ';	
					$query.=' AND ';
				}
				$query.='etp.message_v2.sso_id=\''.string_formating_for_sql($sso_id).'\'';
				$needAnd = 1;
			}

			if (string_formating_for_sql($snils)) {
				if ($needAnd == 1) {
					$query.=' AND ';
				}
				$query .= 'elk.owners.snils =\''.string_formating_for_sql($snils).'\'';
			}

			if (string_formating_for_sql($chip)) {
				if ($needAnd == 1) {
					$query.=' AND ';
				}
				$query .= 'public.pet_identification.identification_code =\''.string_formating_for_sql($chip).'\'';
			}

			#ПОИСК
			#echo $query;
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
	
			while ($row = pg_fetch_assoc($result)) {
				echo '<rec>';
				echo '<rec_id>'.$row['id'].'</rec_id>';
				echo '<rec_visit_id>'.$row['visit_id'].'</rec_visit_id>';
				echo '<rec_service_number>'.$row['service_number'].'</rec_service_number>';
				echo '<rec_message>'.$row['message'].'</rec_message>';
				echo '<rec_last_name>'.$row['last_name'].'</rec_last_name>';
				echo '<rec_first_name>'.$row['first_name'].'</rec_first_name>';
				echo '<rec_middle_name>'.$row['middle_name'].'</rec_middle_name>';
				echo '<rec_sso_id>'.$row['sso_id'].'</rec_sso_id>';
				echo '<rec_phone>'.$row['phone'].'</rec_phone>';
				echo '<rec_email>'.$row['email'].'</rec_email>';
				echo '<rec_created_at>'.$row['created_at'].'</rec_created_at>';
				echo '<rec_updated_at>'.$row['updated_at'].'</rec_updated_at>';
				echo '<rec_system_id>'.$row['system_id'].'</rec_system_id>';
				echo '<rec_message_id>'.$row['message_id'].'</rec_message_id>';
				echo '</rec>';
			}
			pg_free_result($result);
			
		}else{
			echo '<message>fields_not_filled</message>';
		}
		echo '</xml>';
	}

	function support_show_mosru_logs_xml($config){
		###
		$visit_id=$_GET["visit_id"];
		$service_number=$_GET["service_number"];
		###
	
		header("Content-type: text/xml; charset=utf-8");
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<xml>';
	
		if($visit_id || $service_number){
			$query='SELECT * FROM etp.status_log ';
			#ПОИСК
			$query.='WHERE ';

			if(string_formating_for_sql($visit_id)){
				$query.='etp.status_log.visit_id='.string_formating_for_sql($visit_id).'';
			}

			if(string_formating_for_sql($service_number)){
				if(string_formating_for_sql($visit_id)){
					$query.=' AND ';	
				}
				$query.='etp.status_log.service_number=\''.string_formating_for_sql($service_number).'\'';
			}

			#ПОИСК
			$query.=" ORDER BY log_time DESC";
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
	
			while ($row = pg_fetch_assoc($result)) {
				echo '<rec>';
				echo '<rec_id>'.$row['id'].'</rec_id>';
				echo '<rec_visit_id>'.$row['visit_id'].'</rec_visit_id>';
				echo '<rec_service_number>'.$row['service_number'].'</rec_service_number>';
				echo '<rec_message>'.$row['message'].'</rec_message>';
				echo '<rec_log_time>'.$row['log_time'].'</rec_log_time>';
				echo '<rec_etp_status>'.$row['etp_status'].'</rec_etp_status>';
				echo '</rec>';
			}
			pg_free_result($result);
			
		}else{
			echo '<message>fields_not_filled</message>';
		}
		echo '</xml>';
	}
	
	function support_show_mosru_ispk_logs_xml($config){
		###
		$event_code=$_GET["event_code"];
		$date=$_GET["date"];
		###
	
		header("Content-type: text/xml; charset=utf-8");
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<xml>';
	
		if($event_code || $date){
			$query='SELECT * FROM subscription.log ';
			#ПОИСК
			$query.='WHERE ';

			if(string_formating_for_sql($event_code)){
				$query.='subscription.log.event_code=\''.string_formating_for_sql($event_code).'\'';
			}

			if(string_formating_for_sql($date)){
				if(string_formating_for_sql($event_code)){
					$query.=' AND ';
				}
				$query.='subscription.log.log_time BETWEEN \''.$date.' 00:00:00\' AND \''.$date.' 00:00:00\'::DATE + INTERVAL \'1 DAY\'';
			}

			#ПОИСК
			$query.=" ORDER BY log_time DESC";
			//echo $query;
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
	
			while ($row = pg_fetch_assoc($result)) {
				echo '<rec>';
				echo '<rec_id>'.$row['id'].'</rec_id>';
				echo '<rec_log_time>'.$row['log_time'].'</rec_log_time>';
				echo '<rec_event_id>'.$row['event_id'].'</rec_event_id>';
				echo '<rec_event_code>'.$row['event_code'].'</rec_event_code>';
				echo '<rec_is_success>'.$row['is_success'].'</rec_is_success>';
				echo '<rec_error>'.$row['error'].'</rec_error>';
				echo '<rec_id_owner>'.$row['id_owner'].'</rec_id_owner>';
				echo '<rec_id_pet>'.$row['id_pet'].'</rec_id_pet>';
				echo '</rec>';
			}
			pg_free_result($result);
			
		}else{
			echo '<message>fields_not_filled</message>';
		}
		echo '</xml>';
	}
	function support_show_mosru_pets_xml($config){
		###
		$id_pet=$_GET["id_pet"];
		$ext_id=$_GET["ext_id"];
		###
	
		header("Content-type: text/xml; charset=utf-8");
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<xml>';
	
		if($id_pet || $ext_id){
			$query='SELECT * FROM elk.pets ';
			#ПОИСК
			$query.='WHERE ';

			if(string_formating_for_sql($id_pet)){
				$query.='elk.pets.id_pet='.string_formating_for_sql($id_pet).'';
			}

			if(string_formating_for_sql($ext_id)){
				if(string_formating_for_sql($id_pet)){
					$query.=' AND ';	
				}
				$query.='elk.pets.ext_id=\''.string_formating_for_sql($ext_id).'\'';
			}
			#ПОИСК
			#echo $query;
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
	
			while ($row = pg_fetch_assoc($result)) {
				echo '<rec>';
				echo '<rec_id>'.$row['id'].'</rec_id>';
				echo '<rec_id_pet>'.$row['id_pet'].'</rec_id_pet>';
				echo '<rec_ext_id>'.$row['ext_id'].'</rec_ext_id>';
				echo '<rec_id_elk_owner>'.$row['id_elk_owner'].'</rec_id_elk_owner>';
				echo '<rec_id_pet_owner>'.$row['id_pet_owner'].'</rec_id_pet_owner>';
				echo '<rec_id_species>'.$row['id_species'].'</rec_id_species>';
				echo '<rec_id_breed>'.$row['id_breed'].'</rec_id_breed>';
				echo '<rec_name>'.$row['name'].'</rec_name>';
				echo '<rec_chip>'.$row['chip'].'</rec_chip>';
				echo '<rec_birthday>'.$row['birthday'].'</rec_birthday>';
				echo '<rec_sex>'.$row['sex'].'</rec_sex>';
				echo '<rec_created_at>'.$row['created_at'].'</rec_created_at>';
				echo '<rec_updated_at>'.$row['updated_at'].'</rec_updated_at>';
				echo '</rec>';
			}
			pg_free_result($result);
			
		}else{
			echo '<message>fields_not_filled</message>';
		}
		echo '</xml>';
	}
	function support_show_mosru_owners_xml($config){
		###
		$id_owner=$_GET["id_owner"];
		$sso_id=$_GET["sso_id"];
		###
	
		header("Content-type: text/xml; charset=utf-8");
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<xml>';
	
		if($id_owner || $sso_id){
			$query='SELECT * FROM elk.owners ';
			#ПОИСК
			$query.='WHERE ';

			if(string_formating_for_sql($id_owner)){
				$query.='elk.owners.id_owner='.string_formating_for_sql($id_owner).'';
			}

			if(string_formating_for_sql($sso_id)){
				if(string_formating_for_sql($id_owner)){
					$query.=' AND ';	
				}
				$query.='elk.owners.sso_id=\''.string_formating_for_sql($sso_id).'\'';
			}
			#ПОИСК
			echo $query;
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
	
			while ($row = pg_fetch_assoc($result)) {
				echo '<rec>';
				echo '<rec_id>'.$row['id'].'</rec_id>';
				echo '<rec_id_owner>'.$row['id_owner'].'</rec_id_owner>';
				echo '<rec_sso_id>'.$row['sso_id'].'</rec_sso_id>';
				
				echo '<rec_first_name>'.$row['first_name'].'</rec_first_name>';
				echo '<rec_last_name>'.$row['last_name'].'</rec_last_name>';
				echo '<rec_middle_name>'.$row['middle_name'].'</rec_middle_name>';
				echo '<rec_phone>'.$row['phone'].'</rec_phone>';
				echo '<rec_email>'.$row['email'].'</rec_email>';
				echo '<rec_snils>'.$row['snils'].'</rec_snils>';

				echo '<rec_created_at>'.$row['created_at'].'</rec_created_at>';
				echo '<rec_updated_at>'.$row['updated_at'].'</rec_updated_at>';
				echo '</rec>';
			}
			pg_free_result($result);
			
		}else{
			echo '<message>fields_not_filled</message>';
		}
		echo '</xml>';
	}

	function support_show_visits_xml($config){
		$id=$_GET["id"];
		$date=$_GET["date"];
		$status=$_GET["status"];
		$channel=$_GET["channel"];
		
		header("Content-type: text/xml; charset=utf-8");
	
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<xml>';
	
		if($id || $date || $status || $channel){
			$query='SELECT visits_specialists.id_specialist AS specialist_id, visits.id, visits.is_paid AS paid, visits.status, visits.channel, visits.is_paid, visits.time_range,';
			$query.='visits.id_owner, visits.id_pet, ';
			$query.='organizations.short_name AS org_name, addresses.name AS org_address, visits.ticket_number, pet_owners.fullname AS owner_name, ';
			$query.='(LOWER(VISITS.TIME_RANGE)::time) AS start_time,';
			$query.='(UPPER(VISITS.TIME_RANGE)::time) AS end_time,';
			$query.='(LOWER(VISITS.TIME_RANGE)::date) AS start_date, ';
			$query.='string_agg(contacts.id::character varying, \',\') AS contacts, ';
			$query.='string_agg(pets.id::character varying, \',\') AS pets ';
			$query.='FROM visits ';
			$query.='LEFT JOIN pet_owners ON visits.id_owner=pet_owners.id ';
			$query.='LEFT JOIN contacts ON pet_owners.id=contacts.entity_id ';#контакты
			$query.='LEFT JOIN organizations ON visits.id_organization=organizations.id ';
			$query.='LEFT JOIN visits_specialists ON visits.id=visits_specialists.id_visit ';
			$query.='LEFT JOIN addresses ON organizations.id_address=addresses.id ';
			$query.='LEFT JOIN visit_pets ON visits.id=visit_pets.id_visit ';
			$query.='LEFT JOIN pets ON pets.id=visit_pets.id_pet ';
			$query.='WHERE ';
			
			if($id){
				$query.='visits.id='.$id.' ';
			}

			if($date){
				if($id){
					$query.='AND ';
				}
				$query.='(tsrange(\''.$date.'\'::DATE, \''.$date.'\'::DATE + INTERVAL \'1 DAY\', \'[)\') @> visits.time_range) ';
			}

			if($status){
				if($id || $date){
					$query.='AND ';
				}
				$query.='visits.status=\''.$status.'\' ';
			}

			if($channel){
				if($id || $date || $status){
					$query.='AND ';
				}
				$query.='visits.channel=\''.$channel.'\' ';
			}
					
			$query.='GROUP BY pet_owners.id, visits_specialists.id_specialist, visits.id, organizations.short_name, addresses.name ';
			$query.='ORDER BY visits.time_range ';
			#echo $query;
						
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			while ($row = pg_fetch_assoc($result)) {
				$specialist_name='';
				if($row['specialist_id']){
					$query_='SELECT users.fullname FROM specialists ';
					$query_.='LEFT JOIN users ON specialists.id_user=users.id ';
					$query_.='WHERE specialists.id='.$row['specialist_id'].'';
					$result_ = pg_query($query_) or die('Ошибка запроса: ' . pg_last_error());
					$row_ = pg_fetch_row($result_);
					pg_free_result($result_);
					$specialist_name=$row_[0];
				}
		
				echo '<rec>';
				echo '<rec_id>'.$row['id'].'</rec_id>';
				echo '<rec_status>'.$row['status'].'</rec_status>';
				echo '<rec_channel>'.$row['channel'].'</rec_channel>';
				echo '<rec_paid>'.$row['paid'].'</rec_paid>';
				echo '<rec_org_name>'.$row['org_name'].'</rec_org_name>';
				echo '<rec_org_address>'.$row['org_address'].'</rec_org_address>';

				echo '<rec_specialist_name>'.$specialist_name.'</rec_specialist_name>';
				echo '<rec_owner_name>'.$row['owner_name'].'</rec_owner_name>';
				echo '<rec_start_date>'.$row['start_date'].'</rec_start_date>';
				echo '<rec_start_time>'.$row['start_time'].'</rec_start_time>';
				echo '<rec_end_time>'.$row['end_time'].'</rec_end_time>';
		
				//оказываемые услуги на приёме
				
				$query_='SELECT * FROM visits_gov_services ';
				$query_.='LEFT JOIN gov_services ON visits_gov_services.id_service=gov_services.id ';
				$query_.='WHERE visits_gov_services.id_visit='.$row['id'].'';
				$result_ = pg_query($query_) or die('Ошибка запроса: ' . pg_last_error());
				echo '<rec_services>';
				while ($row_ = pg_fetch_assoc($result_)) {
					echo '<service>';
					echo '<name>'.$row_['name'].'</name>';
					echo '</service>';
				}
				pg_free_result($result_);
				echo '</rec_services>';
				//оказываемые услуги на приёме
		
				$contacts = ($row['contacts'] != '')?explode(",",$row['contacts']):NULL;
				if(count($contacts) > 0){
					#контакты
					echo '<rec_contacts>';
					$q='SELECT contacts.name,contacts.main_flag,contacts.confirmed,contact_types.name AS contact_type_title, contacts.id_contact_type AS contact_type_id FROM contacts ';
					$q.='LEFT JOIN contact_types ON contacts.id_contact_type = contact_types.id ';
					$q.='WHERE ';
		
					for($i=0; $i<count($contacts);$i++){
						if($i>0){$q.=' OR ';}
						$q.='contacts.id='.$contacts[$i].'';
					}
		
					$result_c = pg_query($q) or die('Ошибка запроса: ' . pg_last_error());
					while ($row_c = pg_fetch_assoc($result_c)) {
						echo '<contact>';
						echo '<name>'.$row_c['name'].'</name>';
						echo '<main>'.$row_c['main_flag'].'</main>';
						echo '<confirmed>'.$row_c['confirmed'].'</confirmed>';
						echo '<type_title>'.$row_c['contact_type_title'].'</type_title>';
						echo '<type_id>'.$row_c['contact_type_id'].'</type_id>';
						echo '</contact>';
					}
					pg_free_result($result_c);
					echo '</rec_contacts>';
					#контакты
				}

				$pets = ($row['pets'] != '')?explode(",",$row['pets']):NULL;
				if(count($pets) > 0){
					#питомцы
					echo '<rec_pets>';
					$q='SELECT pets.id,pets.name,pets.sex,breeds.name AS breed_name,species.name AS species_name FROM pets ';
					$q.='LEFT JOIN breeds ON pets.id_breed = breeds.id ';
					$q.='LEFT JOIN species ON pets.id_species = species.id ';
					$q.='WHERE (pets.is_main=true OR pets.is_main IS NULL) AND ';
					$q.='(';
					for($i=0; $i<count($pets);$i++){
						if($i>0){$q.=' OR ';}
						$q.='pets.id='.$pets[$i].'';
					}
					$q.=')';
					#echo $q;

					$result_c = pg_query($q) or die('Ошибка запроса: ' . pg_last_error());
					while ($row_c = pg_fetch_assoc($result_c)) {
						echo '<pet>';
						echo '<id>'.$row_c['id'].'</id>';
						echo '<name>'.$row_c['name'].'</name>';
						echo '<sex>'.$row_c['sex'].'</sex>';
						echo '<breed>'.$row_c['breed_name'].'</breed>';
						echo '<species>'.$row_c['species_name'].'</species>';
						echo '</pet>';
					}
					pg_free_result($result_c);
					echo '</rec_pets>';
					#питомцы
				}
		
				echo '</rec>';
			}
			pg_free_result($result);
		}
		echo '</xml>';
	}
	
	function support_show_visits_logs_xml($config){
		$id=$_GET["id"];
				
		header("Content-type: text/xml; charset=utf-8");
	
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<xml>';
	
		if($id){
			$query='SELECT audit.visits_logs.*, public.organizations.name AS name_organization FROM audit.visits_logs ';
			$query.='LEFT JOIN public.organizations ON audit.visits_logs.id_organization=public.organizations.id ';
			$query.='WHERE ';
			
			if($id){
				$query.='id_visit='.$id.' ';
			}
			$query.='ORDER BY date DESC ';
			#echo $query;
									
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			while ($row = pg_fetch_assoc($result)) {
				echo '<rec>';
				echo '<rec_id>'.$row['id'].'</rec_id>';
				echo '<rec_id_visit>'.$row['id_visit'].'</rec_id_visit>';
				echo '<rec_status_visit>'.$row['status_visit'].'</rec_status_visit>';
				echo '<rec_snapshot>'.$row['snapshot'].'</rec_snapshot>';
				echo '<rec_initiator>'.$row['initiator'].'</rec_initiator>';
				echo '<rec_id_user>'.$row['id_user'].'</rec_id_user>';
				echo '<rec_fio_user>'.$row['fio_user'].'</rec_fio_user>';
				echo '<rec_date>'.$row['date'].'</rec_date>';
				echo '<rec_id_organization>'.$row['id_organization'].'</rec_id_organization>';
				echo '<rec_name_organization>'.$row['name_organization'].'</rec_name_organization>';
				echo '</rec>';
			}
			pg_free_result($result);
		}
		echo '</xml>';
	}

	function support_show_user_specializations_xml($config){
		$id=$_GET["id"];

		header("Content-type: text/xml; charset=utf-8");
	
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<xml>';

		$query_='SELECT specializations.name AS specialization, specializations.id AS id FROM users_specializations ';
		$query_.='LEFT JOIN public.specializations ON users_specializations.id_specialization=specializations.id ';
		$query_.='WHERE id_user='.$id.'';
		$result_ = pg_query($query_) or die('Ошибка запроса: ' . pg_last_error());
		echo '<specializations>';
		while ($row_ = pg_fetch_assoc($result_)) {
			echo '<specialization>';
			echo '<id>'.$row_['id'].'</id>';
			echo '<name>'.$row_['specialization'].'</name>';
			echo '</specialization>';
		}
		pg_free_result($result_);
		echo '</specializations>';

		echo '</xml>';
	}

	function support_show_users_xml($config){
		$id=$_GET["id"];
		$login=$_GET["login"];
		$email=$_GET["email"];
		$fullname=$_GET["fullname"];
		
		header("Content-type: text/xml; charset=utf-8");
	
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<xml>';
	
		if($id || $login || $email || $fullname){
			$query='SELECT * FROM users ';
			$query.='WHERE ';
			
			$query_='';
			if($id){
				$query_.='id='.$id.' ';
			}

			if($login){
				if($query_){$query_.='AND ';}
				$query_.='login ILIKE \'%'.string_formating_for_sql($login).'%\' ';
			}

			if($email){
				if($query_){$query_.='AND ';}
				$query_.='email ILIKE \'%'.string_formating_for_sql($email).'%\' ';
			}

			if($fullname){
				if($query_){$query_.='AND ';}
				$query_.='fullname ILIKE \'%'.string_formating_for_sql($fullname).'%\' ';
			}
			$query.=$query_.'ORDER BY login ';
						
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			while ($row = pg_fetch_assoc($result)) {
				echo '<rec>';
				echo '<rec_id>'.$row['id'].'</rec_id>';
				echo '<rec_login>'.$row['login'].'</rec_login>';
				echo '<rec_is_blocked>'.$row['is_blocked'].'</rec_is_blocked>';
				echo '<rec_last_login>'.$row['last_login'].'</rec_last_login>';
				echo '<rec_block_until>'.$row['block_until'].'</rec_block_until>';
				echo '<rec_f_fio>'.$row['f_fio'].'</rec_f_fio>';
				echo '<rec_i_fio>'.$row['i_fio'].'</rec_i_fio>';
				echo '<rec_o_fio>'.$row['o_fio'].'</rec_o_fio>';
				echo '<rec_fullname>'.$row['fullname'].'</rec_fullname>';
				echo '<rec_birthday>'.$row['birthday'].'</rec_birthday>';

				$query_='SELECT users_specializations.*,specializations.name AS specialization FROM users_specializations ';
				$query_.='LEFT JOIN public.specializations ON users_specializations.id_specialization=specializations.id ';
				$query_.='WHERE id_user='.$row['id'].'';
				echo $query_;
				$result_ = pg_query($query_) or die('Ошибка запроса: ' . pg_last_error());
				echo '<rec_specializations>';
				while ($row_ = pg_fetch_assoc($result_)) {
					echo '<specialization>';
					echo '<id>'.$row_['id'].'</id>';
					echo '<name>'.$row_['specialization'].'</name>';
					echo '</specialization>';
				}
				pg_free_result($result_);
				echo '</rec_specializations>';

				echo '<rec_sex>'.$row['sex'].'</rec_sex>';
				echo '<rec_email>'.$row['email'].'</rec_email>';
				echo '<rec_password_valid_till>'.$row['password_valid_till'].'</rec_password_valid_till>';
				echo '<rec_password_valid_till_min>'.$row['password_valid_till_min'].'</rec_password_valid_till_min>';
				echo '<rec_is_deleted>'.$row['is_deleted'].'</rec_is_deleted>';
						
				$query_='SELECT specialists.*,organizations.name AS name_organization FROM specialists ';
				$query_.='LEFT JOIN public.organizations ON specialists.id_organization=public.organizations.id ';
				$query_.='WHERE id_user='.$row['id'].'';
				$result_ = pg_query($query_) or die('Ошибка запроса: ' . pg_last_error());
				echo '<rec_specialists>';
				while ($row_ = pg_fetch_assoc($result_)) {
					echo '<specialist>';
					echo '<id>'.$row_['id'].'</id>';
					echo '<reg_date>'.$row_['reg_date'].'</reg_date>';
					echo '<expel_date>'.$row_['expel_date'].'</expel_date>';
					echo '<name_organization>'.$row_['name_organization'].'</name_organization>';
					echo '<id_organization>'.$row_['id_organization'].'</id_organization>';
					echo '<created_by>'.$row_['created_by'].'</created_by>';
					echo '<updated_by>'.$row_['updated_by'].'</updated_by>';
					echo '<created_at>'.$row_['created_at'].'</created_at>';
					echo '<updated_at>'.$row_['updated_at'].'</updated_at>';

					$query__='SELECT item_name FROM auth_assignment ';
					#$query_.='LEFT JOIN public.organizations ON specialists.id_organization=public.organizations.id ';
					$query__.='WHERE id_user='.$row['id'].' AND id_specialist='.$row_['id'].'';
					$result__ = pg_query($query__) or die('Ошибка запроса: ' . pg_last_error());
					echo '<roles>';
					while ($row__ = pg_fetch_assoc($result__)) {
						echo '<role>';
						echo '<item_name>'.$row__['item_name'].'</item_name>';
						echo '</role>';
					}
					pg_free_result($result__);
					echo '</roles>';

					echo '</specialist>';
				}
				pg_free_result($result_);
				echo '</rec_specialists>';

				###
			
				echo '</rec>';
			}
			pg_free_result($result);
		}
		echo '</xml>';
	}

	function support_show_roles_xml($config){
		$name=$_GET["name"];
		$description=$_GET["description"];
		
		header("Content-type: text/xml; charset=utf-8");
	
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<xml>';
	
		if($name || $description){
			$query='SELECT * FROM auth_item ';
			$query.='WHERE ';
			
			if($name){$query.='name=\''.$name.'\' ';}

			if($description){
				if($name){
					$query.='AND ';
				}
				$query.='(description LIKE \'%'.$description.'%\' OR description LIKE \''.$description.'%\' OR description LIKE \'%'.$description.'\') ';
			}

			$query.=' AND type=\'1\' ';
			$query.='ORDER BY name ';
						
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			while ($row = pg_fetch_assoc($result)) {
				echo '<rec>';
				echo '<rec_name>'.$row['name'].'</rec_name>';
				echo '<rec_description>'.$row['description'].'</rec_description>';
										
				$query_='SELECT * FROM auth_item_child ';
				$query_.='LEFT JOIN public.auth_item ON auth_item.name=public.auth_item_child.child ';
				$query_.='WHERE parent=\''.$row['name'].'\'';
				$result_ = pg_query($query_) or die('Ошибка запроса: ' . pg_last_error());
				echo '<rec_elements>';
				while ($row_ = pg_fetch_assoc($result_)) {
					echo '<element>';
					echo '<child>'.$row_['child'].'</child>';
					echo '<desc>'.$row_['description'].'</desc>';
					echo '</element>';
				}
				pg_free_result($result_);
				echo '</rec_elements>';

				###
			
				echo '</rec>';
			}
			pg_free_result($result);
		}
		echo '</xml>';
	}

	function support_show_elements_xml($config){
		$type=$_GET["type"];
		$name=$_GET["name"];
		$description=$_GET["description"];

		header("Content-type: text/xml; charset=utf-8");
	
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<xml>';
	
		$query='SELECT * FROM auth_item WHERE type=\''.$type.'\' ';
		if($name){
			$query.=' AND (name LIKE \'%'.$name.'%\' OR name LIKE \''.$name.'%\' OR name LIKE \'%'.$name.'\')';
		}
		if($description){
			$query.=' AND (description LIKE \'%'.$description.'%\' OR description LIKE \''.$description.'%\' OR description LIKE \'%'.$description.'\')';
		}
		$query.='ORDER BY name ';
						
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		while ($row = pg_fetch_assoc($result)) {
			echo '<rec>';
			echo '<rec_name>'.$row['name'].'</rec_name>';
			echo '<rec_description>'.$row['description'].'</rec_description>';
			echo '</rec>';
		}
		pg_free_result($result);
		echo '</xml>';
	}

	function support_orgs_xml($config){
		header("Content-type: text/xml; charset=utf-8");
	
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<xml>';
	
		$query='SELECT * FROM organizations ';
		//$query.='WHERE type=\'1\' ';
		$query.='ORDER BY name ';
						
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		while ($row = pg_fetch_assoc($result)) {
			echo '<rec>';
			echo '<rec_id>'.$row['id'].'</rec_id>';
			echo '<rec_name>'.$row['name'].'</rec_name>';
			echo '<rec_short_name>'.$row['short_name'].'</rec_short_name>';
			echo '</rec>';
		}
		pg_free_result($result);
		echo '</xml>';
	}
	function support_services_xml($config){
		header("Content-type: text/xml; charset=utf-8");
	
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<xml>';
	
		$query='SELECT * FROM gov_services WHERE deleted=false ';
		$query.='ORDER BY name ';
		
		echo '<recs>';
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		while ($row = pg_fetch_assoc($result)) {
			echo '<rec>';
			echo '<rec_id>'.$row['id'].'</rec_id>';
			echo '<rec_name>['.$row['cod'].'] '.$row['name'].'</rec_name>';
			echo '</rec>';
		}
		echo '</recs>';

		pg_free_result($result);
		echo '</xml>';
	}

	function support_recovery_password_xml($config){
		$status=$_GET["status"];
		$date=$_GET["date"];
		$login=$_GET["login"];
		$name=$_GET["name"];

		$page=intval($_GET["page"]);//страница
		if($page == ''){$page=1;}
		$sort=$_GET["sort"];//сортировка
		if($sort == '' || $sort == 'null'){$sort='date';}
		$direction=$_GET["direction"];//направление

		$recs_on_page=50;//кол-во на странице

		header("Content-type: text/xml; charset=utf-8");
	
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<xml>';
	
		$query='SELECT recovery_password.id, recovery_password.status, recovery_password.date, recovery_password.login, users.fullname AS fullname FROM recovery_password ';
		$query.='LEFT JOIN users AS users ON users.login=recovery_password.login ';
		$query.='WHERE recovery_password.id IS NOT NULL ';
		
		if($login){$query.=" AND recovery_password.login ILIKE '%".$login."%' ";}
		if($name){$query.=" AND users.fullname ILIKE '%".$name."%' ";}
		if($status != ''){$query.=" AND recovery_password.status=".$status." ";}
		if($date != ''){$query.=' AND date BETWEEN \''.$date.' 00:00:00\' AND \''.$date.' 00:00:00\'::DATE + INTERVAL \'1 DAY\'';}

		//сортировка по умолчанию
        $query.='ORDER BY ';
        if($sort == 'login'){
            $query.='recovery_password.login ';
        }else if($sort == 'status'){
            $query.='recovery_password.status ';
        }else if($sort == 'name'){
            $query.='fullname ';
        }else{
            $query.='recovery_password.date ';
        }

        if($direction){
            $query.='ASC NULLS LAST ';
        }else{
            $query.='DESC NULLS LAST ';
        }
        //сортировка по умолчанию
		echo $query;
						
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		$recs_counter = pg_num_rows($result);//количество записей
		echo '<recs>';
		while ($row = pg_fetch_assoc($result)) {
			echo '<rec>';
			echo '<rec_id>'.$row['id'].'</rec_id>';
			echo '<rec_fullname>'.$row['fullname'].'</rec_fullname>';
			echo '<rec_status>'.$row['status'].'</rec_status>';
			echo '<rec_date>'.$row['date'].'</rec_date>';
			echo '<rec_login>'.$row['login'].'</rec_login>';
			echo '</rec>';
		}
		echo '</recs>';

		echo '<from>'.intval(($page-1)*$recs_on_page+1).'</from>';
        echo '<to>'.intval(($page)*$recs_on_page).'</to>';

        echo '<current>'.$page.'</current>';
        echo '<sort>'.$sort.'</sort>';
        echo '<direction>'.$direction.'</direction>';

        echo '<counter>'.$recs_counter.'</counter>';

		echo '<pages>';
        if($recs_counter > 0){
            $pages_number = intval($recs_counter / $recs_on_page);
            if($pages_number > 1){
                for($i = 1; $i < $pages_number+1; $i++){
                    echo '<page>'.$i.'</page>';
                }
            }
            echo '<last>'.$pages_number.'</last>';
        }
        echo '</pages>';

		pg_free_result($result);
		echo '</xml>';
	}
	function support_recovery_password_rec_xml($config){
		$id=$_GET["id"];

		header("Content-type: text/xml; charset=utf-8");
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<xml>';
		echo '<rec>';


		$query='SELECT recovery_password.* ';
		$query.='FROM recovery_password ';
		$query.='WHERE recovery_password.id='.string_formating_for_sql($id).' ';
				
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    	while ($row = pg_fetch_assoc($result)) {
			echo '<rec_id>'.$row['id'].'</rec_id>';
			echo '<rec_fullname>'.$row['fullname'].'</rec_fullname>';
			echo '<rec_status>'.$row['status'].'</rec_status>';
			echo '<rec_date>'.$row['date'].'</rec_date>';
			echo '<rec_login>'.$row['login'].'</rec_login>';
		}
		pg_free_result($result);

		echo '</rec>';
		echo '</xml>';
	}

	function support_org_types_xml($config){
		header("Content-type: text/xml; charset=utf-8");
	
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<xml>';
	
		$query='SELECT * FROM org_types ';
		$query.='ORDER BY name ';
						
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		while ($row = pg_fetch_assoc($result)) {
			echo '<rec>';
			echo '<rec_id>'.$row['id'].'</rec_id>';
			echo '<rec_name>'.$row['name'].'</rec_name>';
			echo '</rec>';
		}
		pg_free_result($result);
		echo '</xml>';
	}

	function support_areas_xml($config){
		header("Content-type: text/xml; charset=utf-8");
	
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<xml>';
	
		$query='SELECT * FROM areas ';
		$query.='ORDER BY name ';
						
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		while ($row = pg_fetch_assoc($result)) {
			echo '<rec>';
			echo '<rec_id>'.$row['id'].'</rec_id>';
			echo '<rec_name>'.$row['name'].'</rec_name>';
			echo '</rec>';
		}
		pg_free_result($result);
		echo '</xml>';
	}
	
	function support_show_reports_xml($config){
		header("Content-type: text/xml; charset=utf-8");

		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<xml>';

		$date=string_formating_for_sql($_GET["date"]);
		
		$query='SELECT TO_CHAR(created_at, \'HH24\') AS hour, COUNT(id) FROM etp.message_v2 ';
		$query.='WHERE (created_at BETWEEN \''.$date.'\'::TIMESTAMP AND \''.$date.'\'::TIMESTAMP + INTERVAL \'1\' DAY) ';
		//AND phone!=\'+71111111111\'
		$query.='GROUP BY TO_CHAR(created_at, \'HH24\')';
		#echo $query;
		
		$results=[];
		
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		while ($row = pg_fetch_assoc($result)) {
			array_push($results, array(
				'hour' => ''.$row['hour'].'',
				'count' => ''.$row['count'].'',
			));
		}
		pg_free_result($result);

		echo '<recs>';
		for($i=0; $i<=23; $i++){
			$count=0;
			$hour='';
			if($i<10){$hour='0'.$i;}else{$hour=$i;}

			for($j=0; $j<=count($results); $j++){
				if($results[$j]['hour'] == $hour){
					$count=$results[$j]['count'];
				}
			}

			echo '<rec>';
			echo '<rec_hour>'.$hour.'</rec_hour>';
			echo '<rec_count>'.$count.'</rec_count>';
			echo '</rec>';
		}
		echo '</recs>';

		echo '</xml>';
	}

	function support_save_options_xml($config){
		$data=$_POST["data"];

		if($data){
			$result = pg_query(base64_decode($data)) or die('Ошибка запроса: ' . pg_last_error());
			$row = pg_fetch_assoc($result);
			echo xml('<message>'.print_r($row, true).'</message>');
			pg_free_result($result);
		}else{
			echo xml('<message></message>');
		}
	}

	function sub_header__site(){
		echo '<div class="row main_row">';
		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		echo '<div class="col-xl-8 col-lg-10" style="padding-top: 20px; padding-bottom: 20px;">';
	}

	function sub_footer__site(){
		echo '</div>';
		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		echo '</div>';
	}

	function viewMosru(){
		sub_header__site();
		echo '<h1>MOS.RU</h1>';

		$q='SELECT log_time FROM etp.status_log ORDER BY id DESC';
		$result = pg_query($q) or die('Ошибка запроса: ' . pg_last_error());
		$row = pg_fetch_row($result);
		$log_time=$row[0];
		pg_free_result($result);
		echo '<div class="info">Последнее отправленное сообщение: <strong>'.$log_time.'</strong></div>';

		$q='SELECT created_at FROM etp.message_v2 ORDER BY id DESC';
		$result = pg_query($q) or die('Ошибка запроса: ' . pg_last_error());
		$row = pg_fetch_row($result);
		$created_at=$row[0];
		pg_free_result($result);
		echo '<div class="info">Последнее принятое сообщение: <strong>'.$created_at.'</strong></div>';
		
		$q='SELECT COUNT(*) FROM public.queue';
		$result = pg_query($q) or die('Ошибка запроса: ' . pg_last_error());
		$row = pg_fetch_row($result);
		$count_messages=$row[0];
		pg_free_result($result);

		echo '<div class="info">Количество записей в очереди ЕТП: <strong>'.$count_messages.'</strong></div>';

		echo '<div class="info"></div>';
		echo '<form id="form_search_messages" onsubmit="ListShowMosruMessages(); return false;">';
		echo '<h2>Поиск записей</h2>';
		echo '<div class="row">';
		echo '<div class="col-2"><input placeholder="ID приёма. Например: 77777." value="" type="text" id="visit_id" name="visit_id" class="w-100"></div>';
		echo '<div class="col-2"><input placeholder="Service number. Например: 0001-9000003-061101-0039798/22." value="" type="text" id="service_number" name="service_number" class="w-100"></div>';
		echo '<div class="col-2"><input placeholder="SSO ID. Например: 12e238fd-1bc5-4034-802f-af038502d4fd." value="" type="text" id="sso_id" name="sso_id" class="w-100"></div>';
		echo '<div class="col-2"><input placeholder="СНИЛС" value="" type="text" id="snils" name="snils" class="w-100"></div>';
		echo '<div class="col-2"><input placeholder="ЧИП" value="" type="text" id="chip" name="chip" class="w-100"></div>';
		echo '<div class="col-2"><button type="submit">Искать</button></div>';
		echo '</div>';
		echo '<input type="hidden" name="mode" value="xml">';
		echo '</form>';
		echo '<div style="margin-top: 30px;" id="ListMessages" class="recs row main_row"></div>';


		echo '<form id="form_search_pets" onsubmit="ListShowMosruPets(); return false;">';
		echo '<h2>Поиск животных</h2><font size="1">Таблица elk.pets.</font>';
		echo '<div class="row">';
		echo '<div class="col-4"><input type="text" id="id_pet" name="id_pet" class="w-100" placeholder="ID животного. Например: 153289." value=""></div>';
		echo '<div class="col-6"><input placeholder="PET ID. Например: FCAFF5D9C0B5473F990E12B867E1B22D." value="" type="text" id="ext_id" name="ext_id" class="w-100"></div>';
		echo '<div class="col-2"><button type="submit">Искать</button></div>';
		echo '</div>';
		echo '<input type="hidden" name="mode" value="xml">';
		echo '</form>';
		echo '<div style="margin-top: 30px;" id="ListPets" class="recs row main_row"></div>';

		echo '<form id="form_search_owners" onsubmit="ListShowMosruOwners(); return false;">';
		echo '<h2>Поиск владельцев</h2><font size="1">Таблица elk.owners.</font>';
		echo '<div class="row">';
		echo '<div class="col-4"><input type="text" id="id_owner" name="id_owner" class="w-100" placeholder="ID владельца. Например: 153289." value=""></div>';
		echo '<div class="col-6"><input placeholder="SSO ID. Например: d0abb18d-90c9-4b2c-bc15-6d01aa903a12." value="" type="text" id="sso_id" name="sso_id" class="w-100"></div>';
		echo '<div class="col-2"><button type="submit">Искать</button></div>';
		echo '</div>';
		echo '<input type="hidden" name="mode" value="xml">';
		echo '</form>';
		echo '<div style="margin-top: 30px;" id="ListOwners" class="recs row main_row"></div>';


		echo '<form id="form_search_logs" onsubmit="ListShowMosruLogs(); return false;">';
		echo '<h2>Поиск логов</h2>';
		echo '<div class="row">';
		echo '<div class="col-4"><input placeholder="ID приёма. Например: 14745." value="" type="text" id="visit_id" name="visit_id" class="w-100"></div>';
		echo '<div class="col-6"><input placeholder="Service number. Например: 0001-9000003-061101-0039798/22." value="" type="text" id="service_number" name="service_number" class="w-100"></div>';
		echo '<div class="col-2"><button type="submit">Искать</button></div>';
		echo '</div>';
		echo '<input type="hidden" name="mode" value="xml">';
		echo '</form>';

		echo '<div style="margin-top: 30px;" id="ListLogs" class="recs row main_row"></div>';

		echo '<form id="form_search_ispk_logs" onsubmit="ListShowMosruISPKLogs(); return false;">';
		echo '<h2>Поиск логов ИС ПК</h2>';
		echo '<div class="row">';
		echo '<div class="col-5"><input placeholder="Event code. Например: remind_vaccination." value="" type="text" id="service_number" name="event_code" class="w-100"></div>';
		echo '<div class="col-5"><input type="date" id="date" name="date" class="w-100"></div>';
		echo '<div class="col-2"><button type="submit">Искать</button></div>';
		echo '<div class="col-12"><font size="1">Виды кодов событий: animal_found, cancel_violation, cancel_visit, confirm_email, info_declined, initial_info_identification_and_vaccin, initial_info_identification, initial_info_vaccination, sendPassword, primary_AVR, primary_violation_order, quarantine, research, remind_identification, remind_vaccination, remind_vaccination_lepto, secondary_AVR, secondary_violation_order, transfer_visit, violation_vaccination, vetas_link, violation, vetas_k_perenosu, vetas_perenos_priema.</font></div>';
		echo '</div>';
		echo '<input type="hidden" name="mode" value="xml">';
		echo '</form>';

		echo '<div style="margin-top: 30px;" id="ListMosruISPKLogs" class="recs row main_row"></div>';

		sub_footer__site();
	}

	function viewFoundPet(){
		sub_header__site();
		echo '<h1>Сервис "Поиск животных"</h1>';

		echo '<div class="info"></div>';
		echo '<form id="form_search_found_pets_ads" onsubmit="ListShowFoundPetAds(); return false;">';
		echo '<h2>Поиск объявлений</h2>';
		echo '<div class="row">';
		echo '<div class="col-4"><input placeholder="ID объявления. Например: 777." value="" type="text" id="id" name="id" class="w-100"></div>';
		echo '<div class="col-5"><input placeholder="Service number. Например: 0001-9000003-061101-0039798/22." value="" type="text" id="service_number" name="service_number" class="w-100"></div>';
		echo '<div class="col-3"><button type="submit">Искать</button></div>';
		echo '</div>';
		echo '<input type="hidden" name="mode" value="xml">';
		echo '</form>';
		echo '<div style="margin-top: 30px; margin-bottom: 30px;" id="ListFoundPetAds" class="recs row main_row"></div>';

		echo '<form id="form_search_found_pets_messages" onsubmit="ListShowFoundPetMessages(); return false;">';
		echo '<h2>Поиск сообщений</h2>';
		echo '<div class="row">';
		echo '<div class="col-9"><input placeholder="Service number. Например: 0001-9000003-061101-0039798/22." type="text" id="service_number" name="service_number" class="w-100"></div><div class="col-3"><button type="submit">Искать</button></div>';
		echo '</div>';
		echo '<input type="hidden" name="mode" value="xml">';
		echo '</form>';
		echo '<div style="margin-top: 30px; margin-bottom: 30px;" id="ListFoundPetMessages" class="recs row main_row"></div>';

		echo '<form id="form_search_found_pets_messages_sent" onsubmit="ListShowFoundPetMessagesSent(); return false;">';
		echo '<h2>Поиск отправленных сообщений</h2>';
		echo '<div class="row">';
		echo '<div class="col-9"><input placeholder="Service number. Например: 0001-9000003-061101-0039798/22." type="text" id="service_number" name="service_number" class="w-100"></div><div class="col-3"><button type="submit">Искать</button></div>';
		echo '</div>';
		echo '<input type="hidden" name="mode" value="xml">';
		echo '</form>';

		echo '<div style="margin-top: 30px;" id="ListFoundPetMessagesSent" class="recs row main_row"></div>';

		sub_footer__site();
	}

	function viewPets(){
		sub_header__site();
		echo '<h1>Животные</h1>';

		echo '<div class="info"></div>';
		echo '<form id="form_search_recs" onsubmit="ListShowPetsRecs(); return false;">';
		echo '<h2>Поиск животных</h2>';
		echo '<div class="row">';
		echo '<div class="col-4"><input type="text" id="id" name="id" class="w-100" placeholder="ID. Например: 153289." value=""></div>';
		echo '<div class="col-4"><input type="text" id="name" name="name" class="w-100" placeholder="Кличка. Например: Шарик."></div>';
		echo '<div class="col-4"><button type="submit">Искать</button></div>';
		echo '</div>';
		echo '<input type="hidden" name="mode" value="xml">';
		echo '</form>';

		echo '<div style="margin-top: 30px;" id="ListRecs" class="recs row main_row"></div>';

		echo '<div class="info"></div>';
		echo '<form id="form_search_identification_recs" onsubmit="ListShowIdentificationRecs(); return false;">';
		echo '<h2>Поиск идентификации животных</h2>';
		echo '<div class="row">';
		echo '<div class="col-4"><input type="text" id="identification_code" name="identification_code" class="w-100" placeholder="Идентификационный номер." value=""></div>';
		echo '<div class="col-4">';

		echo '<select id="type" name="type">';
		echo '<option value="">Все</option>';
		
		$query='SELECT id, name FROM identification_types ';
		$query.='ORDER BY name ';
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		while ($row = pg_fetch_assoc($result)) {
			echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
		}
		pg_free_result($result);
		

		echo '</select>';

		echo '</div>';
		echo '<div class="col-4"><button type="submit">Искать</button></div>';
		echo '</div>';
		echo '<input type="hidden" name="mode" value="xml">';
		echo '</form>';

		$q='SELECT COUNT(*) FROM pets_tmp';
		$result = pg_query($q) or die('Ошибка запроса: ' . pg_last_error());
		$row = pg_fetch_row($result);
		$count_=$row[0];
		pg_free_result($result);

		echo '<div style="margin-top: 30px;" id="ListRecsIds" class="recs row main_row"></div>';

		echo '<div class="info"></div>';
		echo '<form id="form_search_temp_recs" onsubmit="ListShowPetsTempRecs(); return false;">';
		echo '<h2>Поиск животных (временных - '.$count_.')</h2>';
		echo '<div class="row">';
		echo '<div class="col-8"><input type="text" id="name" name="name" class="w-100" placeholder="Кличка. Например: Шарик."></div>';
		echo '<div class="col-4"><button type="submit">Искать</button></div>';
		echo '</div>';
		echo '<input type="hidden" name="mode" value="xml">';
		echo '</form>';

		echo '<div style="margin-top: 30px;" id="ListTempRecs" class="recs row main_row"></div>';
		sub_footer__site();
	}

	function viewOwners(){
		sub_header__site();

		echo '<h1>Владельцы</h1>';

		echo '<div class="info"></div>';
		echo '<form id="form_search_recs" onsubmit="ListShowOwnersRecs(); return false;">';
		echo '<h2>Поиск владельцев</h2>';
		echo '<div class="row">';
		echo '<div class="col-2"><input type="text" id="id" name="id" class="w-100" placeholder="ID. Например: 153289." title="ID. Например: 153289." value=""></div>';
		echo '<div class="col-2"><input type="text" id="id_main_owner" name="id_main_owner" class="w-100" placeholder="ID главной записи для дублей. Например: 153289." title="ID главной записи для дублей. Например: 153289." value=""></div>';
		echo '<div class="col-4"><input type="text" id="fullname" name="fullname" class="w-100" placeholder="ФИО. Например: Шариков Полиграф Полиграфович." title="ФИО. Например: Шариков Полиграф Полиграфович."></div>';
		echo '<div class="col-4"><button type="submit">Искать</button></div>';
		echo '</div>';
		echo '<input type="hidden" name="mode" value="xml">';
		echo '</form>';

		echo '<div style="margin-top: 30px;" id="ListRecs" class="recs row main_row"></div>';

		$q='SELECT COUNT(*) FROM pet_owners_tmp';
		$result = pg_query($q) or die('Ошибка запроса: ' . pg_last_error());
		$row = pg_fetch_row($result);
		$count_=$row[0];
		pg_free_result($result);

		echo '<div class="info"></div>';
		echo '<form id="form_search_temp_recs" onsubmit="ListShowOwnersTempRecs(); return false;">';
		echo '<h2>Поиск владельцев (временных - '.$count_.')</h2>';
		echo '<div class="row">';
		echo '<div class="col-3"><input type="text" id="f_fio" name="f_fio" class="w-100" placeholder="Фамилия. Например: Шариков." title="ФИО. Например: Шариков."></div>';
		echo '<div class="col-3"><input type="text" id="i_fio" name="i_fio" class="w-100" placeholder="Имя. Например: Полиграф." title="ФИО. Например: Полиграф."></div>';
		echo '<div class="col-3"><input type="text" id="o_fio" name="o_fio" class="w-100" placeholder="Отчество. Например: Полиграфович." title="ФИО. Например: Полиграфович."></div>';
		echo '<div class="col-3"><button type="submit">Искать</button></div>';
		echo '</div>';
		echo '<input type="hidden" name="mode" value="xml">';
		echo '</form>';

		echo '<div style="margin-top: 30px;" id="ListTempRecs" class="recs row main_row"></div>';

		sub_footer__site();
	}

	function viewUsers(){
		sub_header__site();
		echo '<h1>Пользователи';
		echo '<button type="submit" style="float: right; width: 250px;" onclick="showAddUserWindow();">Добавить пользователя</button>';
		echo '</h1>';

		echo '<div class="info"></div>';

		echo '<form id="form_user_password" onsubmit="getUserPassword(); return false;">';
		echo '<h2>Пароль</h2>';
		echo '<div class="row">';
		echo '<div class="col-6"><input type="text" id="password_hash" name="password_hash" class="w-100" placeholder="HASH пароля. Например: +ff........ff==." value="" autocomplete="off"></div>';
		echo '<div class="col-2" id="user_password" style="padding-top: 7px;">...</div>';
		echo '<div class="col-4"><button type="submit">Получить</button></div>';
		echo '</div>';
		echo '<input type="hidden" name="mode" value="xml">';
		echo '</form>';

		
		echo '<div class="info"></div>';
		echo '<form id="form_search_recs" onsubmit="ListShowUsersRecs(); return false;">';
		echo '<h2>Поиск пользователей</h2>';
		echo '<div class="row">';
		echo '<div class="col-2"><input type="text" id="id" name="id" class="w-100" placeholder="ID пользователя. Например: 153289." value=""></div>';
		echo '<div class="col-2"><input type="text" id="fullname" name="fullname" class="w-100" placeholder="ФИО пользователя. Например: Иванов Иван Иванович." value=""></div>';
		echo '<div class="col-2"><input type="text" id="login" name="login" class="w-100" placeholder="Логин пользователя. Например: UserUU." value=""></div>';
		echo '<div class="col-2"><input type="text" id="email" name="email" class="w-100" placeholder="Почта пользователя. Например: user@users.ru." value=""></div>';
		echo '<div class="col-4"><button type="submit">Искать</button></div>';
		echo '</div>';
		echo '<input type="hidden" name="mode" value="xml">';
		echo '</form>';

		echo '<div style="margin-top: 30px;" id="ListRecs" class="recs row main_row"></div>';
		sub_footer__site();
	}

	function viewRoles(){
		sub_header__site();
		echo '<h1>Роли</h1>';

		echo '<h2>Доступные роли</h2>';
		$query='SELECT * FROM auth_item WHERE type=\'1\' ';
		$query.='ORDER BY name ';
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		$a=0;
		while ($row = pg_fetch_assoc($result)) {
			if($a>0){
				echo ', ';
			}
			echo ''.$row['name'].'';
			$a++;
		}
		pg_free_result($result);

		echo '<form id="form_search_recs" onsubmit="ListShowRolesRecs(); return false;">';
		echo '<h2>Поиск ролей</h2>';
		echo '<div class="row">';
		echo '<div class="col-5"><input type="text" id="name" name="name" class="w-100" placeholder="Имя роли. Например: shelterSysAdmin." value=""></div>';
		echo '<div class="col-5"><input type="text" id="description" name="description" class="w-100" placeholder="Описание. Например: Системный администратор приюта." value=""></div>';
		echo '<div class="col-2"><button type="submit">Искать</button></div>';
		echo '</div>';
		echo '<input type="hidden" name="mode" value="xml">';
		echo '</form>';

		echo '<div style="margin-top: 30px;" id="ListRecs" class="recs row main_row"></div>';
		sub_footer__site();
	}

	function viewSpecializations(){
		sub_header__site();
		echo '<h1>Специализации</h1>';

		echo '<form id="form_search_recs" onsubmit="ListShowSpecializationsRecs(); return false;">';
		echo '<h2>Поиск специализаций</h2>';
		echo '<div class="row">';
		echo '<div class="col-10"><input type="text" id="name" name="name" class="w-100" placeholder="Название специализации. Например: Эндоскопия." value=""></div>';
		echo '<div class="col-2"><button type="submit">Искать</button></div>';
		echo '</div>';
		echo '<input type="hidden" name="mode" value="xml">';
		echo '</form>';

		echo '<div style="margin-top: 30px;" id="ListRecs" class="table"></div>';

$html = <<<HTML

		<script type="text/javascript">

		$(document).ready(function () {
			ListShowSpecializationsRecs();
		});
HTML;

		$html.='</script>';
		echo $html;

		sub_footer__site();
	}

	function viewBreeds($configuration){
		sub_header__site();
		echo '<h1>Породы</h1>';

		echo '<form id="form_search_recs" onsubmit="ListShowBreedsRecs(); return false;">';
		echo '<h2>Поиск пород</h2>';
		echo '<div class="row">';
		echo '<div class="col-3"><input type="text" id="name" name="name" class="w-100" placeholder="Наименование породы. Например: австралийский терьер." value=""></div>';
		echo '<div class="col-3"><input type="text" id="description" name="description" class="w-100" placeholder="Описание породы." value=""></div>';
		echo '<div class="col-3">';
		show_species($configuration,2);
		echo '</div>';
		echo '<div class="col-3"><button type="submit">Искать</button></div>';
		echo '</div>';
		echo '<input type="hidden" name="mode" value="xml">';
		echo '</form>';

		echo '<div style="margin-top: 30px;" id="ListRecs" class="table"></div>';

$html = <<<HTML

		<script type="text/javascript">

		$(document).ready(function () {
			ListShowBreedsRecs();
		});
HTML;

		$html.='</script>';
		echo $html;

		sub_footer__site();
	}

	function viewSpecies($configuration){
		sub_header__site();
		echo '<h1>Виды</h1>';

		echo '<form id="form_search_recs" onsubmit="ListShowSpeciesRecs(); return false;">';
		echo '<h2>Поиск виды</h2>';
		echo '<div class="row">';
		echo '<div class="col-4"><input type="text" id="name" name="name" class="w-100" placeholder="Наименование вида. Например: кролики." value=""></div>';
		echo '<div class="col-4"><input type="text" id="description" name="description" class="w-100" placeholder="Описание вида." value=""></div>';
		
		echo '<div class="col-4"><button type="submit">Искать</button></div>';
		echo '</div>';
		echo '<input type="hidden" name="mode" value="xml">';
		echo '</form>';

		echo '<div style="margin-top: 30px;" id="ListRecs" class="table"></div>';

$html = <<<HTML

		<script type="text/javascript">

		$(document).ready(function () {
			ListShowSpeciesRecs();
		});
HTML;

		$html.='</script>';
		echo $html;

		sub_footer__site();
	}

	function viewDiseases(){
		sub_header__site();
		echo '<h1>Заболевания</h1>';

		echo '<form id="form_search_recs" onsubmit="ListShowDiseasesRecs(); return false;">';
		echo '<h2>Поиск заболеваний</h2>';
		echo '<div class="row">';
		echo '<div class="col-5"><input type="text" id="cod" name="cod" class="w-100" placeholder="Код заболевания ГОСТ." value=""></div>';
		echo '<div class="col-5"><input type="text" id="name" name="name" class="w-100" placeholder="Наименование заболевания. Например: Парагрипп." value=""></div>';
		echo '<div class="col-2"><button type="submit">Искать</button></div>';
		echo '</div>';
		echo '<input type="hidden" name="mode" value="xml">';
		echo '</form>';

		echo '<div style="margin-top: 30px;" id="ListRecs" class="table"></div>';

$html = <<<HTML

		<script type="text/javascript">

		$(document).ready(function () {
			ListShowDiseasesRecs();
		});
HTML;

		$html.='</script>';
		echo $html;

		sub_footer__site();
	}


	function viewRecoveryPasswordList(){
		// sub_header__site();
		

		echo '<div class="row main_row search">';

		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		echo '<div class="col-xl-8 col-lg-10 col-md-12 col-sm-12 col-12">';

		// echo '<h1>Запросы на восстановление пароля</h1>';
		
		echo '<form id="form_search_recs" onsubmit="ListShowRecoveryPasswordRecs(); return false;">';
		echo '<div class="row search_sub">';
		
		echo '<div class="d-inline-flex row">';
		echo '<div class="label col-12">Логин</div>';
		echo '<div class="input col-12">';
		echo '<input type="text" id="login" name="login" style="width: 250px;">';
		echo '</div>';
		echo '</div>';

		echo '<div class="d-inline-flex row">';
		echo '<div class="label col-12">ФИО</div>';
		echo '<div class="input col-12">';
		echo '<input type="text" id="name" name="name" style="width: 250px;">';
		echo '</div>';
		echo '</div>';

		echo '<div class="d-inline-flex row">';
		echo '<div class="label col-12">Статус</div>';
		echo '<div class="input col-12">';
		
		echo '<select class="status" name="status">';
		$status_array = array();
		array_push($status_array, array('id' => '','title' => 'Все'));
		array_push($status_array, array('id' => '0','title' => 'Новый'));
		array_push($status_array, array('id' => '1','title' => 'Обработано'));
		array_push($status_array, array('id' => '2','title' => 'Отклонено'));

		for ($i = 0; $i < count($status_array); $i++) {
			echo '<option value="'.$status_array[$i]['id'].'">'.$status_array[$i]['title'].'</option>';
		}
		echo '</select>';

		echo '</div>';
		echo '</div>';

		echo '<div class="d-inline-flex row">';
		echo '<div class="label col-12">Дата</div>';
		echo '<div class="input col-12">';
		echo '<input type="date" name="date" id="date" autocomplete="off" style="width: 150px;" value="'.date("Y-m-d").'" min="2010-01-01" max="2050-01-01">';
		echo '</div>';
		echo '</div>';

		echo '<div class="d-inline-flex buttons">';
    	echo '<button tabindex="1" type="submit" style="margin-left: 15px; width: 120px;" value="1" name="submit" id="submit">Поиск</button>';    
    	echo '</div>';

		echo '</div>';
		echo '<input type="hidden" name="mode" value="xml">';
		echo '</form>';

		echo '<div style="margin-top: 30px;" id="ListRecs" class="table"></div>';

$html = <<<HTML

		<script type="text/javascript">

		$(document).ready(function () {
			ListShowRecoveryPasswordRecs();
		});
HTML;

		$html.='</script>';
		echo $html;

		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		echo '</div>';

		// sub_footer__site();
	}

	function viewElements(){
		sub_header__site();
		echo '<h1>Элементы</h1>';

		echo '<button type="submit" style="float: right; width: 250px;" onclick="showAddElementWindow();">Создать новый элемент</button>';

		echo '<h2>Доступные элементы</h2><font size="1">';
		$query='SELECT * FROM auth_item WHERE type=\'2\' ';
		$query.='ORDER BY name ';
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		$a=0;
		while ($row = pg_fetch_assoc($result)) {
			if($a>0){
				echo ', ';
			}
			echo ''.$row['name'].'';
			$a++;
		}
		pg_free_result($result);
		echo '</font>';
		
		echo '<form id="form_search_recs" onsubmit="ListShowElementsRecs(); return false;">';
		echo '<h2>Поиск элементов</h2>';
		echo '<div class="row">';
		echo '<div class="col-5"><input type="text" id="name" name="name" class="w-100" placeholder="Имя элемента. Например: data.classificators.drugs." value=""></div>';
		echo '<div class="col-5"><input type="text" id="description" name="description" class="w-100" placeholder="Описание. Например: Прейскурант: доступность пункта меню." value=""></div>';
		echo '<div class="col-2"><button type="submit">Искать</button></div>';
		echo '</div>';
		echo '<input type="hidden" name="mode" value="xml">';
		echo '</form>';

		echo '<div style="margin-top: 30px;" id="ListRecs" class="recs row main_row"></div>';
		sub_footer__site();
	}

	function viewVisits(){
		sub_header__site();
		echo '<h1>Приёмы</h1>';

		echo '<div class="info"></div>';
		echo '<form id="form_search_recs" onsubmit="ListShowVisitsRecs(); return false;">';
		echo '<h2>Поиск приёмов</h2>';
		echo '<div class="row">';
		echo '<div class="col-3"><input type="text" id="id" name="id" class="w-100" placeholder="ID приёма. Например: 153289." value=""></div>';
		echo '<div class="col-2">';
		echo '<select id="status" name="status">';
		echo '<option value="">Все</option>';
		echo '<option value="W">В работе</option>';
		echo '<option value="N">Новый</option>';
		echo '<option value="F">Завершенный</option>';
		echo '<option value="O">Завершенный (неоплачен)</option>';
		echo '<option value="A">Отмененный</option>';
		echo '<option value="T">Перенесён</option>';
		echo '<option value="B">Бронирован</option>';
		echo '</select>';
		echo '</div>';
		echo '<div class="col-2">';
		echo '<select id="channel" name="channel">';
		echo '<option value="">Все</option>';
		echo '<option value="4">Живая очередь</option>';
		echo '<option value="3">Запись по телефону</option>';
		echo '<option value="2">mos.ru</option>';
		echo '<option value="1">По направлению</option>';
		echo '<option value="10">Неотложная помощь</option>';
		echo '</select>';
		echo '</div>';
		echo '<div class="col-2"><input type="date" id="date" name="date" class="w-100"></div>';
		echo '<div class="col-3"><button type="submit">Искать</button></div>';
		echo '</div>';
		echo '<input type="hidden" name="mode" value="xml">';
		echo '</form>';

		echo '<div style="margin-top: 30px;" id="ListRecs" class="recs row main_row"></div>';
		sub_footer__site();
	}

	function viewVisitsLogs(){
		sub_header__site();
		$id=$_GET["id"];

		echo '<h1>Логи приёмов</h1>';

		echo '<div class="info"></div>';
		echo '<form id="form_search_recs" onsubmit="ListShowVisitsLogsRecs(); return false;">';
		echo '<h2>Поиск логов приёмов</h2>';
		echo '<div class="row">';
		echo '<div class="col-9"><input type="text" id="id" name="id" class="w-100" placeholder="ID приёма. Например: 153289." value="'.$id.'"></div>';
		echo '<div class="col-3"><button type="submit">Искать</button></div>';
		echo '</div>';
		echo '<input type="hidden" name="mode" value="xml">';
		echo '</form>';

		echo '<div style="margin-top: 30px;" id="ListRecs" class="recs row main_row"></div>';
		sub_footer__site();
	}

	function viewOrganizations($configuration){
		sub_header__site();
		echo '<h1>Организации</h1>';

		echo '<div class="info"></div>';
		echo '<form id="form_search_recs" onsubmit="ListShowOrganizationsRecs(); return false;">';
		echo '<h2>Поиск организаций</h2>';
		echo '<div class="row">';
		echo '<div class="col-3"><input type="text" id="id" name="id" class="w-100" placeholder="ID организации. Например: 670." value=""></div>';
		echo '<div class="col-3"><input type="text" id="name" name="name" class="w-100" placeholder="Название организации. Например: Комитет ветеринарии города Москвы." value=""></div>';
		echo '<div class="col-3">';
		show_organizations_types($configuration, 2);
		echo '</div>';
		
		echo '<div class="col-3"><button type="submit">Искать</button></div>';
		echo '</div>';
		echo '<input type="hidden" name="action" value="organizations">';
		echo '<input type="hidden" name="mode" value="xml">';
		echo '</form>';

		echo '<div style="margin-top: 30px;" id="ListRecs" class="recs row main_row"></div>';

		echo '<div class="row">';
		echo '<div class="col-6">';
		echo '<h2>Структура организаций (по подчинённости)</h2>';

		function getCategory() {
			$query='SELECT * FROM organizations ';
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			$results = array();
			while ($row = pg_fetch_assoc($result)) {
				$results[$row["parent_id"]][] = $row;
			}
			pg_free_result($result);

			return $results;
		}

		global $category_arr;
		$category_arr = getCategory();

		function outTree($parent_id, $level) {
			global $category_arr;
			if (isset($category_arr[$parent_id])) {
				foreach ($category_arr[$parent_id] as $value) {
					echo "<div style='margin-left:" . ($level * 25) . "px;";
					if($level == 1){
						echo "font-weight: bold;";
					}

					if($level == 0){
						echo "font-size: 10px;";
					}else{
						echo "font-size: 9px;";
					}
					
					echo "'>";
					
					for($i=0;$i<=$level;$i++){
						echo ' · ';
					}
					
					echo "".$value["name"]." (".$value["id"].")";
					
					if($value["id_area"] == ''){
						echo " <font color='red' size='2'>!</font>";
					}

					if($value["latitude"] == '' && $value["longitude"] == ''){
						echo " <font color='red' title='Нет координат'>Н/К</font>";
					}
					if($value["id_fias_address"] == ''){
						echo " <font color='red' title='Нет адреса'>Н/А</font>";
					}
					
					echo "</div>";
					$level = $level + 1;
					outTree($value["id"], $level);
					$level = $level - 1;
				}
			}
		}
		echo outTree(0, 0);
		echo '</div>';
		echo '<div class="col-6">';
		echo '<h2>Структура организаций (по управляемости)</h2>';

		function getCategory2() {
			$query='SELECT * FROM organizations ';
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			$results = array();
			while ($row = pg_fetch_assoc($result)) {
				if($row["managing_organization_id"] == ''){
					$row["managing_organization_id"]=0;
				}
				$results[$row["managing_organization_id"]][] = $row;
			}
			pg_free_result($result);

			return $results;
		}
		global $category_arr2;
		$category_arr2 = getCategory2();

		function outTree2($managing_organization_id, $level) {
			global $category_arr2;
			//echo print_r($category_arr2[$managing_organization_id], true);
			if (isset($category_arr2[$managing_organization_id])) {
				foreach ($category_arr2[$managing_organization_id] as $value) {
					echo "<div style='margin-left:" . ($level * 25) . "px;";
					if($level == 1){
						echo "font-weight: bold;";
					}

					if($level == 0){
						echo "font-size: 10px;";
					}else{
						echo "font-size: 9px;";
					}
					echo "'>";

					for($i=0;$i<=$level;$i++){
						echo ' · ';
					}

					echo "". $value["name"] .' ('.$value["id"].'';
					if($value["organization_type_const"]){
						echo ' ,'.$value["organization_type_const"].'';
					}
					echo ')'. "</div>";
					$level = $level + 1; //Увеличиваем уровень вложености
					//Рекурсивно вызываем эту же функцию, но с новым $parent_id и $level
					outTree2($value["id"], $level);
					$level = $level - 1; //Уменьшаем уровень вложености
				}
			}
		}
		echo outTree2(0, 0);

		echo '</div>';
		echo '</div>';
		sub_footer__site();
	}

	function viewSupport($config){
		sub_header__site();
		$base_url="http://localhost:8888/callcenter/support.php";
		$base_url="https://vetas.mos.ru/callcenter/support.php";
	
		echo '<p>Список доступных функций:</p>';
		echo '<ul>';
		// echo '<li style="padding-bottom: 15px;"><strong>Закрыть приём.</strong> Введите ID приёма <a href="'.$base_url.'?action=close_visit&visit_id=">'.$base_url.'?action=close_visit&visit_id={ID}</a>.</li>';
		// echo '<li style="padding-bottom: 15px;"><strong>Отменить приём.</strong> Введите ID приёма <a href="'.$base_url.'?action=undo_visit&visit_id=">'.$base_url.'?action=undo_visit&visit_id={ID}</a>.</li>';
		// echo '<li style="padding-bottom: 15px;"><strong>Отмена оплаты приёма.</strong> Введите ID приёма <a href="'.$base_url.'?action=undo_paid_visit&visit_id=">'.$base_url.'?action=undo_paid_visit&visit_id={ID}</a>.</li>';
		echo '<li style="padding-bottom: 15px;"><strong>Удаление владельца животного и его животных.</strong> Введите ID владельца животного <a href="'.$base_url.'?action=delete_pet_owner&pet_owner_id=">'.$base_url.'?action=delete_pet_owner&pet_owner_id={ID}</a>.</li>';
		echo '<li style="padding-bottom: 15px;"><strong>Удаление специалиста.</strong> Введите ID специалиста <a href="'.$base_url.'?action=delete_specialist&specialist_id=">'.$base_url.'?action=delete_specialist&specialist_id={ID}</a>.</li>';
		echo '</ul>';

		sub_footer__site();
	}

	function viewStatusesMosru($config){
		sub_header__site();

		echo '<h1>Отправка статусов MOS.RU</h1>';
		
		echo '<div class="row">';
		echo '<div class="col-4">';
		echo 'Код статуса';
		echo '</div>';
		echo '<div class="col-6">';
		echo 'ЕНО';
		echo '</div>';
		echo '<div class="col-2">';
		echo '</div>';

		echo '<div class="col-4">';
		echo '<textarea class="JxTag" id="statuses"></textarea>';
		echo '</div>';
		echo '<div class="col-6">';
		echo '<textarea class="JxTag" id="eno"></textarea>';
		echo '</div>';

		echo '<div class="col-2">';
		echo '<button type="submit">Отправить</button>';
		echo '</div>';

		echo '</div>';

		$html = <<<HTML
		<script type="text/javascript">
		document.addEventListener("DOMContentLoaded", function(event) {
			let ArrayData=new Array();
			ArrayData.push({title: "1075", id: 1075});
			ArrayData.push({title: "1081", id: 1081});
			ArrayData.push({title: "10190", id: 10190});
			ArrayData.push({title: "10191", id: 10191});
			ArrayData.push({title: "10197", id: 10197});
			
			var options_statuses ={name: 'statuses', request_time: 1, mode: 'normal', text_tags: false, new_tags: false, width: '100%', data: ArrayData};
			new JxTag(options_statuses);

			var options ={name: 'eno', mode: 'normal', placeholder: '0001-9000003-061101-0039798/22', text_tags: false, new_tags: true, width: '100%'};
			new JxTag(options);
		});
		</script>
		HTML;

		echo $html;

		sub_footer__site();
	}

	function viewPhonesBug(){
		

		$query="SELECT id, name FROM public.contacts WHERE name LIKE '+7 %' AND id_contact_type=1";
		//+7 79153218352
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		while ($row = pg_fetch_assoc($result)) {
			$row['name'] = substr_replace($row['name'], null, 0, 3);
			$query_='UPDATE contacts SET name=\'+'.$row['name'].'\' WHERE id='.$row['id'].'';
			echo $query_."<br>";
			$result_ = pg_query($query_) or die('Ошибка запроса: ' . pg_last_error());
			pg_free_result($result_);
		}
		pg_free_result($result);

		$query="SELECT id, name FROM public.contacts WHERE name LIKE '+ 7%' AND id_contact_type=1";
		//+ 79124372066
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		while ($row = pg_fetch_assoc($result)) {
			$row['name'] = substr_replace($row['name'], null, 0, 3);
			$query_='UPDATE contacts SET name=\'+7'.$row['name'].'\' WHERE id='.$row['id'].'';
			echo $query_."<br>";
			$result_ = pg_query($query_) or die('Ошибка запроса: ' . pg_last_error());
			pg_free_result($result_);
		}
		pg_free_result($result);
	}

	function viewSaveOptions(){
		sub_header__site();

		echo '<h1>Режим "суперов"</h1>';//2731

		echo '<div class="info" id="message">';
		echo '</div>';
		
		echo '<div class="info"></div>';
		echo '<form method="POST" id="support_options" onsubmit="saveSupportOptions(); return true;">';
		echo '<h2>SQL-код</h2>';
		echo '<div class="row">';
		echo '<div class="col-10"><textarea name="sql" id="sql" rows="12"></textarea></div>';
		echo '<div class="col-2"><button type="button" class="button" onclick="saveSupportOptions();">Выполнить</button></div>';
		echo '</div>';
		echo '</form>';

		echo '<div class="recs" style="margin-top: 30px;">';
		echo '<div class="rec">';
		echo 'SELECT * FROM public.pet_owners WHERE id=0';
		echo '</div>';
		echo '<div class="rec">';
		echo 'INSERT INTO species_services (id_species, id_service) VALUES (\'777\',NULL);';
		echo '</div>';
		echo '<div class="rec">';
		echo 'UPDATE visits SET status=\'F\', updated_by=2731, updated_at=NOW()::timestamp(0) WHERE id=0';
		echo '</div>';
		echo '<div class="rec">';
		echo 'DELETE FROM public.pets_to_owner WHERE id=0';
		echo '</div>';
		echo '</div>';

		sub_header__site();
	}

	function viewAnalytics($config){
		if($_SERVER['SERVER_NAME'] == 'localhost'){
			$base_url="http://localhost:8888/support/index.php";
		}else if($_SERVER['SERVER_NAME'] == 'vetas-prod.starlink-soft.ru'){
			$base_url="https://vetas-prod.starlink-soft.ru/support/index.php";
		}else if($_SERVER['SERVER_NAME'] == 'vetas-predprod.mos.ru'){
			$base_url="https://vetas-predprod.mos.ru/support/index.php";
		}else{
			$base_url="https://vetas.mos.ru/support/index.php";
		}
		//
		echo '<div class="row main_row search">';

		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		echo '<div class="col-xl-8 col-lg-10">';
		echo '<form id="form_support" onsubmit="ShowCharts(); return false;">';
		echo '<div class="row search_sub">';

		echo '<div class="d-inline-flex row">';
		echo '<div class="label col-12">Дата</div>';
		echo '<div class="input col-12">';
		echo '<input type="date" name="date" id="date" autocomplete="off" style="width: 150px;" value="'.date("Y-m-d").'" min="2010-01-01" max="2050-01-01">';
		echo '</div>';
		echo '</div>';
		
		//
		echo '<div class="d-inline-flex row">';
		echo '<div class="label col-12">Канал записи</div>';
		echo '<div class="input col-12">';
		echo '<select name="chanel" id="chanel" style="width: 150px;" >';//multiple
		// echo '<option value="1" selected>Хренов Володимир</option>';

		// $query='SELECT id, name, species_id FROM breeds ';
    	// $query.=' ORDER BY name ';
		// $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
    	// while ($row = pg_fetch_assoc($result)) {
		// 	echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
		// }
		// pg_free_result($result);
		
		// echo '<option value="1" selected>mos.ru</option>';
		// echo '<option value="2">вtr trпд</option>';
		// echo '<option value="3" selected>по телефону</option>';
		// echo '<option value="4">впд1</option>';
		// echo '<option value="5" selected>впд2</option>';
		// echo '<option value="6">впд3</option>';
		// echo '<option value="7">впд4</option>';
		echo '</select>';
		echo '</div>';
		echo '</div>';



		// echo '<div class="d-inline-flex row">';
		// echo '<div class="label col-12">Канал записи</div>';
		// echo '<div class="input col-12">';
		// echo '<select name="chanel2" id="chanel2">';
		// echo '<option value="1" style="background: #2B84D0;" icon="http://localhost:8888/images/ic-plus.svg">мос.ру</option>';
		// echo '<option value="2" style="background: #d3d3d3;" icon="http://localhost:8888/images/ic-arrow-right.svg" disabled>впд</option>';
		// echo '<option value="3" style="background: #E39632;" icon="http://localhost:8888/images/ic-arrow-down.svg">по телефону</option>';
		// echo '</select>';
		// echo '</div>';
		// echo '</div>';


		
		echo '<div class="d-inline-flex buttons"><button type="submit" style="width: 120px;">Показать</button></div>';

		echo '</div>';

		echo '<input type="hidden" name="action" value="reports">';
		echo '<input type="hidden" name="mode" value="xml">';
		echo '</form>';
		echo '</div>';
		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		echo '</div>';

		echo '</div>';

		//графики
		echo '<div class="row main_row">';
		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		echo '<div class="col-xl-8 col-lg-10 main_row">';
		echo '<div class="row">';
		echo '<div class="col-xl-12 col-lg-12 col-md-12"><div class="JxChart chart loading" id="JCMSReportChart1" style="height: 315px; margin-bottom: 30px;"></div></div>';
		echo '</div>';
		echo '</div>';
		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		echo '</div>';
		//графики

		$html = <<<HTML
		<script type="text/javascript">
		var new_chart1;
		
		function ShowCharts(){
			new_chart1.LoadDataURL('load');
		}

		$(document).ready(function() {
			let ArrayTypes=new Array();
			ArrayTypes.push({id: "1",title: "Количество записей с mos.ru",chart_types: ['histogram'], period: "1",percentage: "",filter: "",graphic: "",specification: "1",reverse: "1",chart_title: "1", filters: "",fields: [{title: "Час", name: "hour", width:"20", total: "0", dayofweek:"0", chart_title:"1"},{title: "Количество", name: "count", color: "#128583", pattern: "stroke", show:"1", width:"16",total:"1", chart:"1", percentage:"1", filter:"1"}]});
			
			var options ={
				name: 'new_chart1',
				table: false,
				chart: true,
				chart_tooltip_histogram_full: true,
				height: 250,
				current_type: 1,
				chart_expand_collapse: 1,
				chart_legend: 1,
				chart_legend_text_max_width: 40,
				chart_legend_text_percents: 1,
				field_chart: 'JCMSReportChart1',
				form: 'form_support',
				type: 'type',
				url: "$base_url",
				types: ArrayTypes
			};
			new_chart1 = new JxChart(options);

			var options ={
				name: 'chanel',
				search: true,
				request_title: 'fullname',
				response_value: 'rec_id',
				response_title: 'rec_fullname',
				url: "?action=users&mode=xml",
			}
			new_select1 = new JxSelect(options);
		});
		</script>
		HTML;

		echo $html;
	}

	function viewInforming($config){
		

		// echo '<div class="row main_row search">';

		// echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		// echo '<div class="col-xl-8 col-lg-10">';
		// echo '<form id="form_search_recs" onsubmit="listShowInformings(); return false;">';
		// echo '<div class="row search_sub">';

				
		// // echo '<div class="d-inline-flex row">';
		// // echo '<div class="label col-12" style="width: 50px;">Владелец</div>';
		// // echo '<div class="input col-12">';
		// // echo '<input type="text" name="owner" style="width: 300px;">';
		// // echo '</div>';
		// // echo '</div>';

		// /////////////////////
		// $spec_organization_id='';

		// if($_COOKIE['organization']){
		// 	if($_COOKIE['organization'] == 1){
		// 		$spec_organization_id=666;
		// 	}else{
		// 		$query='SELECT id FROM organizations WHERE id=\''.string_formating_for_sql($_COOKIE['organization']).'\' LIMIT 1';
		// 		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		// 		$row = pg_fetch_row($result);
		// 		$spec_organization_id=$row[0];
		// 		pg_free_result($result);
		// 	}
		// }
		

		// // echo '<div class="d-inline-flex buttons">';
		// // echo '<button tabindex="1" type="submit" style="margin-left: 15px; width: 120px;" value="1" name="submit" id="submit">Поиск</button>';
		// // echo '</div>';

		// echo '</div>';

		// echo '<input type="hidden" name="mode" value="xml">';
		// echo '</form>';
		// echo '</div>';
		// echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		// echo '</div>';

		// echo '</div>';


		echo '<div class="row main_row">';
		
		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		
		echo '<div class="col-xl-8 col-lg-10 main_row">';

		echo '<div class="table" id="ListRecs">';
		echo '<div class="p-3">...</div>';
		echo '</div>';

		echo '</div>';

		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';

		echo '</div>';

$html = <<<HTML

		<script type="text/javascript">

		$(document).ready(function () {
		listShowInformings();
		});
HTML;

		$html.='</script>';

		echo $html;
	}

	if($action == 'auth'){
		authUser($configuration); 
	}else if($action == 'exit'){
		exitUser($configuration);
	}else if($action == 'pets' && $mode == 'xml'){
		if(vallidateToken($configuration)){support_show_pets_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'recovery_password_recs' && $mode == 'xml'){
		if(vallidateToken($configuration)){support_recovery_password_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'recovery_password_rec' && $mode == 'xml'){
		if(vallidateToken($configuration)){support_recovery_password_rec_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'identifications' && $mode == 'xml'){
		if(vallidateToken($configuration)){support_show_identifications_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'temp_pets' && $mode == 'xml'){
			if(vallidateToken($configuration)){support_show_temp_pets_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'visits' && $mode == 'xml'){
		if(vallidateToken($configuration)){support_show_visits_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'users' && $mode == 'xml'){
		if(vallidateToken($configuration)){support_show_users_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'user_specializations' && $mode == 'xml'){
		if(vallidateToken($configuration)){support_show_user_specializations_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'show_roles' && $mode == 'xml'){
		if(vallidateToken($configuration)){support_show_roles_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'show_elements' && $mode == 'xml'){
		if(vallidateToken($configuration)){support_show_elements_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'visits_logs' && $mode == 'xml'){
		if(vallidateToken($configuration)){support_show_visits_logs_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'owners' && $mode == 'xml'){
		if(vallidateToken($configuration)){support_show_owners_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'temp_owners' && $mode == 'xml'){
		if(vallidateToken($configuration)){support_show_temp_owners_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'found_pets_ads' && $mode == 'xml'){
		if(vallidateToken($configuration)){support_show_found_pets_ads_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'found_pets_messages' && $mode == 'xml'){
		if(vallidateToken($configuration)){support_show_found_pets_messages_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'found_pets_messages_sent' && $mode == 'xml'){
		if(vallidateToken($configuration)){support_show_found_pets_messages_sent_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'mosru_messages' && $mode == 'xml'){
		if(vallidateToken($configuration)){support_show_mosru_messages_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'mosru_pets' && $mode == 'xml'){
		if(vallidateToken($configuration)){support_show_mosru_pets_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'mosru_owners' && $mode == 'xml'){
		if(vallidateToken($configuration)){support_show_mosru_owners_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'mosru_logs' && $mode == 'xml'){
		if(vallidateToken($configuration)){support_show_mosru_logs_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'mosru_ispk_logs' && $mode == 'xml'){
		if(vallidateToken($configuration)){support_show_mosru_ispk_logs_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'delete_pet_owner_link'){
		if(vallidateToken($configuration)){support_delete_pet_owner_link($configuration);}else{invalidToken($configuration);}
	}else if($action == 'return_pet'){
		if(vallidateToken($configuration)){support_return_pet($configuration);}else{invalidToken($configuration);}
	}else if($action == 'departure_pet'){
		if(vallidateToken($configuration)){support_departure_pet($configuration);}else{invalidToken($configuration);}

	}else if($action == 'return_owner'){
		if(vallidateToken($configuration)){support_return_owner($configuration);}else{invalidToken($configuration);}
	}else if($action == 'delete_owner'){
		if(vallidateToken($configuration)){support_delete_owner($configuration);}else{invalidToken($configuration);}
	
	}else if($action == 'return_specialist'){
		if(vallidateToken($configuration)){support_return_specialist($configuration);}else{invalidToken($configuration);}
	}else if($action == 'dismiss_specialist'){
		if(vallidateToken($configuration)){support_dismiss_specialist($configuration);}else{invalidToken($configuration);}
	}else if($action == 'delete_specialist'){
		if(vallidateToken($configuration)){support_delete_specialist($configuration);}else{invalidToken($configuration);}
	}else if($action == 'add_specialist_role'){
		if(vallidateToken($configuration)){support_add_specialist_role($configuration);}else{invalidToken($configuration);}
	}else if($action == 'add_element_role'){
		if(vallidateToken($configuration)){support_add_element_role($configuration);}else{invalidToken($configuration);}
	}else if($action == 'delete_element_role'){
		if(vallidateToken($configuration)){support_delete_element_role_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'add_element'){
		if(vallidateToken($configuration)){support_add_element($configuration);}else{invalidToken($configuration);}
	}else if($action == 'delete_specialist_role'){
		if(vallidateToken($configuration)){support_delete_specialist_role($configuration);}else{invalidToken($configuration);}
	}else if($action == 'add_user_org'){
		if(vallidateToken($configuration)){support_add_user_org($configuration);}else{invalidToken($configuration);}
	}else if($action == 'add_user'){
		if(vallidateToken($configuration)){support_add_user($configuration);}else{invalidToken($configuration);}
	}else if($action == 'close_visit'){
		if(vallidateToken($configuration)){support_close_visit($configuration);}else{invalidToken($configuration);}
	}else if($action == 'work_visit'){
		if(vallidateToken($configuration)){support_work_visit($configuration);}else{invalidToken($configuration);}
	}else if($action == 'cancel_visit'){
		if(vallidateToken($configuration)){support_cancel_visit($configuration);}else{invalidToken($configuration);}
	}else if($action == 'cancel_visit_paid'){
		if(vallidateToken($configuration)){support_cancel_visit_paid($configuration);}else{invalidToken($configuration);}
	}else if($action == 'get_user_password'){
		if(vallidateToken($configuration)){support_get_user_password($configuration);}else{invalidToken($configuration);}
	}else if($action == 'orgs'){
		if(vallidateToken($configuration)){support_orgs_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'services'){
		if(vallidateToken($configuration)){support_services_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'org_types'){
		if(vallidateToken($configuration)){support_org_types_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'areas'){
		if(vallidateToken($configuration)){support_areas_xml($configuration);}else{invalidToken($configuration);}

	}else if($action == 'addresses' && $mode == 'xml'){
		if(vallidateToken($configuration)){show_addresses_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'fias_tokken' && $mode == 'xml'){
		if(vallidateToken($configuration)){
			$fias_tokken=fias_request($configuration);
			header("Content-type: text/xml; charset=utf-8");
			echo '<?xml version="1.0" encoding="UTF-8"?>';
			echo '<xml>';
			echo '<fias_tokken>'.$fias_tokken->access_token.'</fias_tokken>';
			echo '</xml>';
		}else{
			invalidToken($configuration);
		}
	}else if($action == 'specializations' && $mode == 'xml'){
		if(vallidateToken($configuration)){show_specializations_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'diseases' && $mode == 'xml'){
		if(vallidateToken($configuration)){show_diseases_xml($configuration);}else{invalidToken($configuration);}

	}else if($action == 'breeds' && $mode == 'xml'){
		if(vallidateToken($configuration)){support_show_breeds_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'species_list' && $mode == 'xml'){
		if(vallidateToken($configuration)){support_show_species_xml($configuration);}else{invalidToken($configuration);}

	}else if($action == 'species' && $mode == 'xml'){
		if(vallidateToken($configuration)){show_species_xml($configuration);}else{invalidToken($configuration);}
	
	}else if($action == 'save_specializations'){
		if(vallidateToken($configuration)){support_save_specializations_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'save_specialization'){
		if(vallidateToken($configuration)){support_save_specialization($configuration);}else{invalidToken($configuration);}
	}else if($action == 'save_disease'){
		if(vallidateToken($configuration)){support_save_disease($configuration);}else{invalidToken($configuration);}
	}else if($action == 'save_specie'){
		if(vallidateToken($configuration)){support_save_specie($configuration);}else{invalidToken($configuration);}
	}else if($action == 'save_breed'){
		if(vallidateToken($configuration)){support_save_breed($configuration);}else{invalidToken($configuration);}
	}else if($action == 'save_recovery_password_rec'){
		if(vallidateToken($configuration)){support_save_recovery_password_rec($configuration);}else{invalidToken($configuration);}
	}else if($action == 'organizations' && $mode == 'xml'){
		if(vallidateToken($configuration)){support_show_organizations_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'save_organization' && $mode == 'xml'){
		if(vallidateToken($configuration)){support_save_organization_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'delete_organization'){
		if(vallidateToken($configuration)){support_delete_organization_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'delete_specialization'){
		if(vallidateToken($configuration)){support_delete_specialization_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'delete_disease'){
		if(vallidateToken($configuration)){support_delete_disease_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'delete_breed'){
		if(vallidateToken($configuration)){support_delete_breed_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'delete_specie'){
		if(vallidateToken($configuration)){support_delete_specie_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'add_informing'){
		if(vallidateToken($configuration)){support_add_informing($configuration);}else{invalidToken($configuration);}
	}else if($action == 'save_informing'){
		if(vallidateToken($configuration)){support_save_informing($configuration);}else{invalidToken($configuration);}
	}else if($action == 'informings' && $mode == 'xml'){
		if(vallidateToken($configuration)){support_show_informings_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'informing' && $mode == 'xml'){
		if(vallidateToken($configuration)){support_show_informing_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'specialization' && $mode == 'xml'){
		if(vallidateToken($configuration)){support_show_specialization_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'disease' && $mode == 'xml'){
		if(vallidateToken($configuration)){support_show_disease_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'breed' && $mode == 'xml'){
		if(vallidateToken($configuration)){support_show_breed_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'specie' && $mode == 'xml'){
		if(vallidateToken($configuration)){support_show_specie_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'delete_informing' && $mode == 'xml'){
		if(vallidateToken($configuration)){support_delete_informing_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'reports' && $mode == 'xml'){
		if(vallidateToken($configuration)){support_show_reports_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'save_options' && $mode == 'xml'){
		if(vallidateToken($configuration)){support_save_options_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'notification_read'){
    	if(vallidateToken($configuration)){read_notification_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'notifications'){
    	if(vallidateToken($configuration)){show_notifications_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'recovery_password') {
		header_site(1, $configuration);
		viewRecoveryPassword('support');
		footer_site();
	}else if($action == 'recovery'){
    	recoveryPasswordUser($configuration);
	}else{
		if($_COOKIE['token']){
			if(vallidateToken($configuration)){
				header_site(0,$configuration, 'Служба поддержки ВетАС','support');
				sub_header_site($configuration);
				menu_site($configuration, 'support', $action);
				
				informings_site($configuration);

				$query='SELECT id FROM users WHERE login=\''.string_formating_for_sql($_COOKIE['login']).'\' LIMIT 1';
				$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
				$id_user=pg_fetch_result($result, 0);
				pg_free_result($result);

				if(userCan($configuration, 'sysAdminGos')){

				//пользователь авторизован//
				if($action == 'delete_visit'){//УДАЛЕНИЕ ПРИЁМА
					$visit_id=$_GET["visit_id"];

					if($visit_id != '' && $visit_id != '{ID}'){
						###Удаляем все связи животное+визит
						$query_delete="DELETE FROM visit_pets WHERE id_visit=".$visit_id." ";
						echo '<li>Удаляем связь животное+визит '.$query_delete.'</li>';
						$result_delete = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
						pg_free_result($result_delete);
						
						###Удаляем все связи специалист+визит
						$query_delete="DELETE FROM visits_specialists WHERE id_visit=".$visit_id." ";
						echo '<li>Удаляем связь специалист+визит '.$query_delete.'</li>';
						$result_delete = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
						pg_free_result($result_delete);

						###Ищем все tmc
						$id_visits_gov_service=0;
						echo '<li>visit_service_tmc<ul>';
						$query_vst="SELECT id_visits_gov_service FROM visit_service_tmc WHERE id_visit=".$visit_id." ";
						$result_vst = pg_query($query_vst) or die('Ошибка запроса: ' . pg_last_error());
						while ($row_vst = pg_fetch_assoc($result_vst)) {
							echo '<li>'.$row_vst['id_visits_gov_service'].'</li>';
							$id_visits_gov_service=$row_vst['id_visits_gov_service'];
						}
						pg_free_result($result_vst);
						echo '</ul></li>';
						$query_delete="DELETE FROM visit_service_tmc WHERE id_visit=".$visit_id." ";
						$result_delete = pg_query($query_delete) or die('Ошибка запроса '.$query_delete.': ' . pg_last_error());
						pg_free_result($result_delete);

						echo '<li>visit_service_tmc_archive<ul>';
						$query_vst="SELECT id_visits_gov_service FROM visit_service_tmc_archive WHERE id_visit=".$visit_id." ";
						$result_vst = pg_query($query_vst) or die('Ошибка запроса: ' . pg_last_error());
						while ($row_vst = pg_fetch_assoc($result_vst)) {
							echo '<li>'.$row_vst['id_visits_gov_service'].'</li>';
							$id_visits_gov_service=$row_vst['id_visits_gov_service'];
						}
						pg_free_result($result_vst);
						echo '</ul></li>';
						$query_delete="DELETE FROM visit_service_tmc_archive WHERE id_visit=".$visit_id." ";
						$result_delete = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
						pg_free_result($result_delete);

						echo '<li>visit_service_tmc_pet<ul>';
						$query_vst="SELECT id_visits_gov_service FROM visit_service_tmc_pet WHERE id_visit=".$visit_id." ";
						$result_vst = pg_query($query_vst) or die('Ошибка запроса visit_service_tmc_pet: ' . pg_last_error());
						while ($row_vst = pg_fetch_assoc($result_vst)) {
							echo '<li>'.$row_vst['id_visits_gov_service'].'</li>';
							$id_visits_gov_service=$row_vst['id_visits_gov_service'];
						}
						pg_free_result($result_vst);
						echo '</ul></li>';
						$query_delete="DELETE FROM visit_service_tmc_pet WHERE id_visit=".$visit_id." ";
						$result_delete = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
						pg_free_result($result_delete);

						if($id_visits_gov_service > 0){
							echo '<li>tmc.balance_flow '.$id_visits_gov_service.'<ul>';
							$query_vst="SELECT id_tmc_balance FROM tmc.balance_flow WHERE id_visit_service=".$id_visits_gov_service." ";
							$result_vst = pg_query($query_vst) or die('Ошибка запроса: ' . pg_last_error());
							while ($row_vst = pg_fetch_assoc($result_vst)) {
								echo '<li>'.$row_vst['id_tmc_balance'].'</li>';
							}
							pg_free_result($result_vst);
							echo '</ul></li>';
							$query_delete="DELETE FROM tmc.balance_flow WHERE id_visit_service=".$id_visits_gov_service." ";
							echo '<li>Удаляем связь TMC '.$query_delete.'</li>';
							$result_delete = pg_query($query_delete) or die('Ошибка запроса tmc.balance_flow: ' . pg_last_error());
							pg_free_result($result_delete);
						}
							###
						$query='SELECT id FROM visits_gov_services WHERE id_visit='.$visit_id.' LIMIT 1';
						$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
						$vgs2 = pg_fetch_assoc($result);
						pg_free_result($result);
						
						if($vgs2['id'] > 0){
							$query_delete="DELETE FROM tmc.balance_flow WHERE id_visit_service=".$vgs2['id']." ";
							echo '<li>Удаляем связь TMC '.$query_delete.'</li>';
							$result_delete = pg_query($query_delete) or die('Ошибка запроса tmc.balance_flow: ' . pg_last_error());
							pg_free_result($result_delete);
						}
						###

						###Удаляем все связи услуга+визит
						$query_delete="DELETE FROM visits_gov_services WHERE id_visit=".$visit_id." ";
						echo '<li>Удаляем связь услуга+визит '.$query_delete.'</li>';
						$result_delete = pg_query($query_delete) or die('Ошибка запроса '.$query_delete.': ' . pg_last_error());
						pg_free_result($result_delete);
						echo '</ul></li>';

						$query_delete="DELETE FROM visits WHERE id=".$visit_id." ";
						$result_delete = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
						pg_free_result($result_delete);

						echo 'Визит и все связи ID '.$visit_id.' удалены.';
					}else{
						echo 'Введите ID визита '.$base_url.'?action=delete_visit&visit_id={ID}.';
					}
				}else if($action == 'delete_specialist'){
					$specialist_id=$_GET["specialist_id"];
					
					if($specialist_id != '' && $specialist_id != '{ID}'){
						$query_delete="DELETE FROM specialists WHERE id=".$specialist_id." ";
						echo 'Удаляем специалиста '.$query_delete.'. ';
						$result_delete = pg_query($query_delete) or die('Ошибка запроса specialists: ' . pg_last_error());
						pg_free_result($result_delete);
					
						echo 'Специалист '.$specialist_id.' удален.';
					}else{
						echo 'Введите ID специалиста '.$base_url.'?action=delete_specialist&specialist_id={ID}.';
					}
				}else if($action == 'delete_pet_owner'){
					$pet_owner_id=$_GET["pet_owner_id"];
					$accept=$_GET["accept"];
					
					echo '<h2 style="padding-bottom: 10px;">Полное удаление информации о владельце</h2>';
					echo '<form>';
					if($pet_owner_id != '' && $accept == ''){
						echo '<input disabled name="pet_owner_id" value="'.$pet_owner_id.'" size="100" autocomplete="off">';
						echo '<input type="hidden" name="pet_owner_id" value="'.$pet_owner_id.'">';
					}else{
						echo '<input placeholder="Введите ID владельца животного для предварительного просмотра" name="pet_owner_id" value="'.$pet_owner_id.'" size="100" autocomplete="off">';
					}
					echo '<input type="hidden" name="action" value="delete_pet_owner">';
					if($pet_owner_id != '' && $accept == ''){
						echo '<input type="hidden" name="accept" value="confirmed">';
						echo '<input type="submit" value="Удалить">';
					}else{
						echo '<input type="submit" value="Искать">';
					}
					echo '</form>';
					
					if($pet_owner_id != '' && $accept == 'confirmed'){
						echo '<ul>';
					
						###Удаляем контакты
						$query_delete="DELETE FROM contacts WHERE entity_id=".$pet_owner_id." ";
						$result = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
						pg_free_result($result);
						echo '<li>Удаляем контакты '.$query_delete.'</li>';
					
						###VIOLATION
						$query="SELECT id_violation FROM violation WHERE id_owner=".$pet_owner_id." ";
						$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
						while ($row = pg_fetch_assoc($result)) {
							$query_delete="DELETE FROM violation_history WHERE id_violation=".$row['id_violation']." ";
							$result_delete = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
							pg_free_result($result_delete);
							echo '<li>Удаляем нарушения (история) '.$query_delete.'</li>';
						}
						pg_free_result($result);
						###
						$query_delete="DELETE FROM violation WHERE id_owner=".$pet_owner_id." ";
						echo '<li>Удаляем нарушения '.$query_delete.'</li>';
						$result_delete = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
						pg_free_result($result_delete);
						###VIOLATION
					
						###Ищем всех животных владельца
						$query="SELECT id_pet FROM pets_to_owner WHERE id_owner=".$pet_owner_id." ";
						$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
						while ($row = pg_fetch_assoc($result)) {
							###Удаляем все связи визит+животное
							$query_delete="DELETE FROM visit_service_pet WHERE id_pet=".$row['id_pet']." ";
							echo '<li>Удаляем связи визит+животное '.$query_delete.'</li>';
							$result_delete = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
							pg_free_result($result_delete);
							
							###Удаляем все связи визит+описание
							$query_delete="DELETE FROM visit_descriptions WHERE id_pet=".$row['id_pet']." ";
							echo '<li>Удаляем связи визит+описание '.$query_delete.'</li>';
							$result_delete = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
							pg_free_result($result_delete);
					
							###Удаляем всех животных владельца
							$query_delete="DELETE FROM pets WHERE id=".$row['id_pet']." ";
							echo '<li>Удаляем питомцев '.$query_delete.'</li>';
							$result_delete = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
							pg_free_result($result_delete);
						}
						pg_free_result($result);
					
						###Ищем все визиты владельца
						$query="SELECT id FROM visits WHERE id_owner=".$pet_owner_id." ";
						echo '<li><strong>ВИЗИТЫ (запршиваем все визиты '.$query.'):</strong> <ul>';
						$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
						while ($row = pg_fetch_assoc($result)) {
							echo '<li><ul>';
							###Удаляем все связи животное+визит
							$query_delete="DELETE FROM visit_pets WHERE id_visit=".$row['id']." ";
							echo '<li>Удаляем связь животное+визит '.$query_delete.'</li>';
							$result_delete = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
							pg_free_result($result_delete);
					
							###Удаляем все связи специалист+визит
							$query_delete="DELETE FROM visits_specialists WHERE id_visit=".$row['id']." ";
							echo '<li>Удаляем связь специалист+визит '.$query_delete.'</li>';
							$result_delete = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
							pg_free_result($result_delete);
					
							###Ищем все tmc
							$id_visits_gov_service=0;
							echo '<li>visit_service_tmc<ul>';
							$query_vst="SELECT id_visits_gov_service FROM visit_service_tmc WHERE id_visit=".$row['id']." ";
							$result_vst = pg_query($query_vst) or die('Ошибка запроса: ' . pg_last_error());
							while ($row_vst = pg_fetch_assoc($result_vst)) {
								echo '<li>'.$row_vst['id_visits_gov_service'].'</li>';
								$id_visits_gov_service=$row_vst['id_visits_gov_service'];
							}
							pg_free_result($result_vst);
							echo '</ul></li>';
							$query_delete="DELETE FROM visit_service_tmc WHERE id_visit=".$row['id']." ";
							$result_delete = pg_query($query_delete) or die('Ошибка запроса '.$query_delete.': ' . pg_last_error());
							pg_free_result($result_delete);
					
							echo '<li>visit_service_tmc_archive<ul>';
							$query_vst="SELECT id_visits_gov_service FROM visit_service_tmc_archive WHERE id_visit=".$row['id']." ";
							$result_vst = pg_query($query_vst) or die('Ошибка запроса: ' . pg_last_error());
							while ($row_vst = pg_fetch_assoc($result_vst)) {
								echo '<li>'.$row_vst['id_visits_gov_service'].'</li>';
								$id_visits_gov_service=$row_vst['id_visits_gov_service'];
							}
							pg_free_result($result_vst);
							echo '</ul></li>';
							$query_delete="DELETE FROM visit_service_tmc_archive WHERE id_visit=".$row['id']." ";
							$result_delete = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
							pg_free_result($result_delete);
					
							echo '<li>visit_service_tmc_pet<ul>';
							$query_vst="SELECT id_visits_gov_service FROM visit_service_tmc_pet WHERE id_visit=".$row['id']." ";
							$result_vst = pg_query($query_vst) or die('Ошибка запроса visit_service_tmc_pet: ' . pg_last_error());
							while ($row_vst = pg_fetch_assoc($result_vst)) {
								echo '<li>'.$row_vst['id_visits_gov_service'].'</li>';
								$id_visits_gov_service=$row_vst['id_visits_gov_service'];
							}
							pg_free_result($result_vst);
							echo '</ul></li>';
							$query_delete="DELETE FROM visit_service_tmc_pet WHERE id_visit=".$row['id']." ";
							$result_delete = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
							pg_free_result($result_delete);
					
							if($id_visits_gov_service > 0){
								echo '<li>tmc.balance_flow '.$id_visits_gov_service.'<ul>';
								$query_vst="SELECT id_tmc_balance FROM tmc.balance_flow WHERE id_visit_service=".$id_visits_gov_service." ";
								$result_vst = pg_query($query_vst) or die('Ошибка запроса: ' . pg_last_error());
								while ($row_vst = pg_fetch_assoc($result_vst)) {
									echo '<li>'.$row_vst['id_tmc_balance'].'</li>';
								}
								pg_free_result($result_vst);
								echo '</ul></li>';
								$query_delete="DELETE FROM tmc.balance_flow WHERE id_visit_service=".$id_visits_gov_service." ";
								echo '<li>Удаляем связь TMC '.$query_delete.'</li>';
								$result_delete = pg_query($query_delete) or die('Ошибка запроса tmc.balance_flow: ' . pg_last_error());
								pg_free_result($result_delete);
							}
							###
							$query='SELECT id FROM visits_gov_services WHERE id_visit='.$row['id'].' LIMIT 1';
							$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
							$vgs2 = pg_fetch_assoc($result);
							pg_free_result($result);
					
							if($vgs2['id'] > 0){
								$query_delete="DELETE FROM tmc.balance_flow WHERE id_visit_service=".$vgs2['id']." ";
								echo '<li>Удаляем связь TMC '.$query_delete.'</li>';
								$result_delete = pg_query($query_delete) or die('Ошибка запроса tmc.balance_flow: ' . pg_last_error());
								pg_free_result($result_delete);
							}
							###
					
							###Удаляем все связи услуга+визит
							$query_delete="DELETE FROM visits_gov_services WHERE id_visit=".$row['id']." ";
							echo '<li>Удаляем связь услуга+визит '.$query_delete.'</li>';
							$result_delete = pg_query($query_delete) or die('Ошибка запроса '.$query_delete.': ' . pg_last_error());
							pg_free_result($result_delete);
							echo '</ul></li>';
						}
						pg_free_result($result);
						echo '</ul></li>';
					
					
						###Удаляем владельца
						$query_delete="DELETE FROM pet_owners WHERE id=".$pet_owner_id." ";
						echo '<li>Удаляем владельца '.$query_delete.'</li>';
						$result_delete = pg_query($query_delete) or die('Ошибка запроса '.$query_delete.': ' . pg_last_error().' необходимо удалить в ручную (были нарушенные зависимости). <a href="'.$base_url.'?action=delete_balance_flow&visit_id={ID}">Удалить?</a>.');
						pg_free_result($result_delete);
					
						###Удаляем все связи животное+владелец
						$query_delete="DELETE FROM pets_to_owner WHERE id_owner=".$pet_owner_id." ";
						echo '<li>Удаляем связь животное+владелец '.$query_delete.'</li>';
						$result_delete = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
						pg_free_result($result_delete);
					
						###Удаляем все визиты владельца
						$query_delete="DELETE FROM visits WHERE id_owner=".$pet_owner_id." ";
						$result_delete = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
						pg_free_result($result_delete);
					
						echo '</ul>';
					
						echo 'Владелец животного ID '.$pet_owner_id.' и все его связи удалены.';
					}else if($pet_owner_id != '' && $accept == ''){
						$query='SELECT * FROM pet_owners WHERE id='.intval($pet_owner_id).' LIMIT 1';
						$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
						$pet_owner = pg_fetch_assoc($result);
						pg_free_result($result);
					
						echo '<div style="padding-top: 15px;">';
						echo '<h3 style="padding-bottom: 10px;">Владелец</h3>';
						echo '<p><strong>Фамилия:</strong> '.$pet_owner['f_fio'].'</p>';
						echo '<p><strong>Имя:</strong> '.$pet_owner['i_fio'].'</p>';
						echo '<p><strong>Отчество:</strong> '.$pet_owner['o_fio'].'</p>';
						if($pet_owner['snils']){
							echo '<p><strong>СНИЛС:</strong> '.$pet_owner['snils'].'</p>';
						}
					
						###КОНТАКТЫ
						$query='SELECT * FROM contacts WHERE entity_id='.intval($pet_owner_id).'';
						$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
						$count = pg_num_rows($result);
						if($count > 0){
							echo '<h3 style="padding-bottom: 10px;">Контакты</h3>';
							while ($contact = pg_fetch_assoc($result)) {
								if($contact['id_contact_type'] == 1 || $contact['id_contact_type'] == 3){
									echo '<p><strong>Телефон:</strong> '.$contact['name'].'</p>';
								}else{
									echo '<p><strong>Электронная почта:</strong> '.$contact['name'].'</p>';
								}
							}
						}
						pg_free_result($result);
						###КОНТАКТЫ
					
						###НАРУШЕНИЯ
						$query='SELECT violation.id_violation, violation.rejection_reason, violation_type.name FROM violation ';
						$query.='LEFT JOIN violation_type ON violation.id_type=violation_type.id_type ';
						$query.='WHERE id_owner='.intval($pet_owner_id).' ';
						$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
						$count = pg_num_rows($result);
						if($count > 0){
							echo '<h3 style="padding-bottom: 10px;">Нарушения</h3>';
							while ($violation = pg_fetch_assoc($result)) {
								echo '<p><strong>'.$violation['name'].'</strong> '.$violation['comment'].'';
					
								$query_='SELECT * FROM violation_history WHERE id_violation='.$violation['id_violation'].'';
								$result_ = pg_query($query_) or die('Ошибка запроса: ' . pg_last_error());
								$count_ = pg_num_rows($result_);
								if($count_ > 0){
									echo ' (в истории записей '.$count_.')';
								}
								pg_free_result($result_);
					
								echo '</p>';
							}
						}
						pg_free_result($result);
						###НАРУШЕНИЯ
					
						###ПИТОМЦЫ
						$query="SELECT id_pet FROM pets_to_owner WHERE id_owner=".intval($pet_owner_id)." ";
						$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
						$count = pg_num_rows($result);
						if($count > 0){
							echo '<h3 style="padding-bottom: 10px;">Питомцы</h3>';
							while ($row = pg_fetch_assoc($result)) {
								###ЖИВНОСТЬ
								$query_='SELECT * FROM pets WHERE id='.$row['id_pet'].'';
								$result_ = pg_query($query_) or die('Ошибка запроса: ' . pg_last_error());
								$count_ = pg_num_rows($result_);
								if($count_ > 0){
									while ($pet = pg_fetch_assoc($result_)) {
										echo '<p><strong>Кличка:</strong> '.$pet['name'].'</p>';
									}
								}
								pg_free_result($result_);
								
								$query_='SELECT * FROM visit_service_pet WHERE id_pet='.$row['id_pet'].'';
								$result_ = pg_query($query_) or die('Ошибка запроса: ' . pg_last_error());
								$count_ = pg_num_rows($result_);
								if($count_ > 0){
									echo '<p>Найдены визиты животного: <ul>';
									while ($visit = pg_fetch_assoc($result_)) {
										echo '<li>'.$visit['id_visits_gov_service'].'</li>';
									}
									echo '</ul></p>';
								}
								pg_free_result($result_);
					
								$query_='SELECT * FROM visit_descriptions WHERE id_pet='.$row['id_pet'].'';
								$result_ = pg_query($query_) or die('Ошибка запроса: ' . pg_last_error());
								$count_ = pg_num_rows($result_);
								if($count_ > 0){
									echo '<p>Найдены описания визитов животного: <ul>';
									while ($visit = pg_fetch_assoc($result_)) {
										echo '<li> '.$visit['description'].'</li>';
									}
									echo '</ul></p>';
								}
								pg_free_result($result_);
								###ЖИВНОСТЬ
							}
						}
						pg_free_result($result);
						###ПИТОМЦЫ
					
						###ВИЗИТЫ
						$query="SELECT * FROM visits WHERE id_owner=".$pet_owner_id." ";
						$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
						$count = pg_num_rows($result);
						if($count > 0){
							echo '<h3 style="padding-bottom: 10px;">Визиты</h3>';
							while ($row = pg_fetch_assoc($result)) {
								echo '<p>Визит №'.$row['id'].' от '.$row['created_at'].'</p>';
								echo '<ul>';
								###Удаляем все связи животное+визит
								$query_delete="DELETE FROM visit_pets WHERE id_visit=".$row['id']." ";
								echo '<li>Удаляем связь животное+визит '.$query_delete.'</li>';
								
								###Удаляем все связи специалист+визит
								$query_delete="DELETE FROM visits_specialists WHERE id_visit=".$row['id']." ";
								echo '<li>Удаляем связь специалист+визит '.$query_delete.'</li>';
								
								###Ищем все tmc
								$id_visits_gov_service=0;
								echo '<li>visit_service_tmc<ul>';
								$query_vst="SELECT id_visits_gov_service FROM visit_service_tmc WHERE id_visit=".$row['id']." ";
								$result_vst = pg_query($query_vst) or die('Ошибка запроса: ' . pg_last_error());
								while ($row_vst = pg_fetch_assoc($result_vst)) {
									echo '<li>'.$row_vst['id_visits_gov_service'].'</li>';
									$id_visits_gov_service=$row_vst['id_visits_gov_service'];
								}
								pg_free_result($result_vst);
								echo '</ul></li>';
					
								echo '<li>visit_service_tmc_archive<ul>';
								$query_vst="SELECT id_visits_gov_service FROM visit_service_tmc_archive WHERE id_visit=".$row['id']." ";
								$result_vst = pg_query($query_vst) or die('Ошибка запроса: ' . pg_last_error());
								while ($row_vst = pg_fetch_assoc($result_vst)) {
									echo '<li>'.$row_vst['id_visits_gov_service'].'</li>';
									$id_visits_gov_service=$row_vst['id_visits_gov_service'];
								}
								pg_free_result($result_vst);
								echo '</ul></li>';
					
								
								echo '<li>visit_service_tmc_pet<ul>';
								$query_vst="SELECT id_visits_gov_service FROM visit_service_tmc_pet WHERE id_visit=".$row['id']." ";
								$result_vst = pg_query($query_vst) or die('Ошибка запроса: ' . pg_last_error());
								while ($row_vst = pg_fetch_assoc($result_vst)) {
									echo '<li>'.$row_vst['id_visits_gov_service'].'</li>';
									$id_visits_gov_service=$row_vst['id_visits_gov_service'];
								}
								pg_free_result($result_vst);
								echo '</ul></li>';
					
								echo '<li>tmc.balance_flow '.$id_visits_gov_service.'<ul>';
								$query_vst="SELECT id_tmc_balance FROM tmc.balance_flow WHERE id_visit_service=".$id_visits_gov_service." ";
								$result_vst = pg_query($query_vst) or die('Ошибка запроса: ' . pg_last_error());
								while ($row_vst = pg_fetch_assoc($result_vst)) {
									echo '<li>'.$row_vst['id_tmc_balance'].'</li>';
								}
								pg_free_result($result_vst);
								echo '</ul></li>';
					
								###Удаляем все связи услуга+визит
								$query_delete="DELETE FROM visits_gov_services WHERE id_visit=".$row['id']." ";
								echo '<li>Удаляем связь услуга+визит '.$query_delete.'</li>';
								echo '</ul>';
							}
						}
						pg_free_result($result);
						###ВИЗИТЫ
					
						echo '</div>';
					}
				}else if($action == 'faq'){
					sub_header__site();

					echo '<h1>FAQ или типичные проблемы с ВетАС</h1>';
					echo '<h2>Отсутствие связи с mos.ru</h2>';
					echo '<ul>';
					echo '<li>Проверка <a href="?action=mosru">получаемых/передаваемых</a> сообщений</li>';
					echo '<li>Проверка docker-контейнеров на предмет возможного отлючения/аварийного выхода</li>';
					echo '<li>Проверка канала связи ЕТП с mos.ru</li>';
					echo '</ul>';

					sub_footer__site();
				}else if($action == 'visits'){
					viewVisits($configuration);
				}else if($action == 'visits_logs'){
					viewVisitsLogs($configuration);
				}else if($action == 'found_pet'){
					viewFoundPet($configuration);
				}else if($action == 'mosru'){
					viewMosru($configuration);
				}else if($action == 'analytics'){
					viewAnalytics($configuration);
				}else if($action == 'informing'){
					viewInforming($configuration);
				}else if($action == 'owners'){
					viewOwners($configuration);
				}else if($action == 'users'){
					viewUsers($configuration);
				}else if($action == 'roles'){
					viewRoles($configuration);
				}else if($action == 'specializations'){
					viewSpecializations($configuration);

				}else if($action == 'breeds'){
					viewBreeds($configuration);
				}else if($action == 'species'){
					viewSpecies($configuration);

				}else if($action == 'specializations'){
					viewSpecializations($configuration);

				}else if($action == 'diseases'){
					viewDiseases($configuration);

				}else if($action == 'statuses_mosru'){
					//viewStatusesMosru($configuration);
					//SELECT push_queue(visit_id, visit_status);
					//
					viewStatusesMosru($configuration);

				}else if($action == 'recovery_password_list'){
					viewRecoveryPasswordList($configuration);
				
				}else if($action == 'elements'){
					viewElements($configuration);
				}else if($action == 'pets'){
					viewPets($configuration);
				}else if($action == 'organizations'){
					viewOrganizations($configuration);
				}else if($action == 'super' && $id_user == 2731){
					viewSaveOptions($configuration);
				}else if($action == 'phones' && $id_user == 2731){
					viewPhonesBug($configuration);
				}else{
					viewSupport($configuration);
				}
				}else{
					viewAttention($configuration);
				}

				// if($action != 'analytics' && $action != 'informing'){
				// 	sub_footer__site();
				// }
				
				sub_footer_site();
				footer_site();
			}else{
				header_site(1,$configuration, 'Служба поддержки ВетАС','support');
				viewAuthUser('support');
				footer_site();
			}
		}else{
			header_site(1,$configuration);
			viewAuthUser('support');
			footer_site();
		}
	}
	
	if (function_exists('pg_connect')) {
		pg_close($dbconn);
	}
?>