<?php

use Mpdf\Tag\Tr;

	$configuration = require $_SERVER['DOCUMENT_ROOT'].'/callcenter/config.php';

	include_once $_SERVER['DOCUMENT_ROOT'].'/callcenter/functions.php';
	include_once $_SERVER['DOCUMENT_ROOT'].'/callcenter/header.php';
	include_once $_SERVER['DOCUMENT_ROOT'].'/callcenter/footer.php';

	if (!function_exists('pg_connect')) {echo xml('<message>Функция pg_connect отсутсвует!</message>');exit;}else{$dbconn = pg_connect("host=".$configuration['host']." port=".$configuration['port']." dbname=".$configuration['dbname']." user=".$configuration['user']." password=".$configuration['password']."") or die('Не удалось соединиться: ' . pg_last_error());$result = pg_query('SET TIME ZONE \'Europe/Moscow\';') or die('Ошибка запроса: ' . pg_last_error());pg_free_result($result);}

	if(isset($_POST["action"])){$action=$_POST["action"];}else{$action=$_GET["action"];}
	if(isset($_POST["mode"])){$mode=$_POST["mode"];}else{$mode=$_GET["mode"];}

	function duplicates_recs_by_time(){
		$query_delete='DELETE FROM duplicates WHERE temp=true AND created_at < NOW() - interval \'1\' DAY';
		$result = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
		pg_free_result($result);
	}

	function duplicates_delete_duplicate_xml($configuration){
		$id=$_GET["id"];
		$duplicate=$_GET["duplicate"];


		$query='SELECT id_main, array_to_string(ids, \',\', \'*\') AS ids FROM duplicates WHERE id='.string_formating_for_sql($duplicate).' ';
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		$row = pg_fetch_assoc($result);
		pg_free_result($result);

		if($row['id_main'] == $id){
			$idsArray = explode(",", $row['ids']);


			$query='UPDATE duplicates SET id_main = '.$idsArray[1].',ids = array_remove(ids, '.$id.') WHERE id='.string_formating_for_sql($duplicate).';';
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			pg_free_result($result);
		}else{
			$query='UPDATE duplicates SET ids = array_remove(ids, '.$id.') WHERE id='.string_formating_for_sql($duplicate).';';
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			pg_free_result($result);
		}

		$message='<message>duplicate_deleted</message>';
		echo xml($message);
	}

	function duplicates_add_duplicate_xml($configuration){
		$id=$_GET["id"];
		$duplicate=$_GET["duplicate"];

		//проверка на существование владельца
		$query='SELECT type FROM duplicates WHERE id='.string_formating_for_sql($duplicate).' ';
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		$row = pg_fetch_assoc($result);
		pg_free_result($result);

		if($row['type'] == 1){
			$query='SELECT id FROM pet_owners WHERE id='.string_formating_for_sql($id).' ';
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			$row = pg_fetch_assoc($result);
			pg_free_result($result);

			if(!$row['id']){
				$message='<message>wrong_id</message>';
				echo xml($message);

				return true;
			}
		}else if($row['type'] == 2){
			$query='SELECT id FROM pets WHERE id='.string_formating_for_sql($id).' ';
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			$row = pg_fetch_assoc($result);
			pg_free_result($result);

			if(!$row['id']){
				$message='<message>wrong_id</message>';
				echo xml($message);

				return true;
			}
		}

		$query='UPDATE duplicates SET ids = array_append(ids, '.$id.') WHERE id='.string_formating_for_sql($duplicate).';';
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		pg_free_result($result);

		$message='<message>duplicate_added</message>';
		echo xml($message);
	}

	function duplicates_merge_duplicates_xml($configuration){
		$id=$_GET["id"];
		$data=$_GET["data"];
		$id_user = user_id($configuration);

		set_time_limit(3000);

		//узнаём основного владельца
		$query='SELECT *, array_to_string(ids, \',\', \'*\') AS ids FROM duplicates WHERE id='.string_formating_for_sql($id).' ';
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		$row = pg_fetch_assoc($result);
		pg_free_result($result);

		//узнаем что обновлять, а что клеить
		$data_array = json_decode(urldecode("".$data.""), true);

		$main_rec = $data_array['main'];//с чем идёт все объединение

		$idsArray = explode(",", $row['ids']);

		if($row['type'] == 1){//владельцы
			//переносим нужные контакты, адреса и т.д. ДР, ФИО
			if($data_array['fullname']){
				$query='SELECT * FROM pet_owners WHERE id='.string_formating_for_sql($data_array['fullname']).' ';
				$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
				$row_o = pg_fetch_assoc($result);
				pg_free_result($result);

				$query='UPDATE pet_owners SET ';
				$query.='f_fio=\''.$row_o['f_fio'].'\', ';
				$query.='i_fio=\''.$row_o['i_fio'].'\', ';
				$query.='o_fio=\''.$row_o['o_fio'].'\', ';
				$query.='fullname=\''.$row_o['fullname'].'\' ';
				$query.=' WHERE id='.$main_rec.';';
				$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
				$row = pg_fetch_row($result);
				pg_free_result($result);
			}

			if($data_array['birthday']){
				$query='SELECT * FROM pet_owners WHERE id='.string_formating_for_sql($data_array['birthday']).' ';
				$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
				$row_o = pg_fetch_assoc($result);
				pg_free_result($result);

				$query='UPDATE pet_owners SET ';
				$query.='birthday=\''.$row_o['birthday'].'\' ';
				$query.=' WHERE id='.$main_rec.';';
				$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
				$row = pg_fetch_row($result);
				pg_free_result($result);
			}

			if($data_array['address']){
				$query='SELECT * FROM pet_owners WHERE id='.string_formating_for_sql($data_array['address']).' ';
				$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
				$row_o = pg_fetch_assoc($result);
				pg_free_result($result);

				$query='UPDATE pet_owners SET ';
				$query.='id_fias_address=\''.$row_o['id_fias_address'].'\' ';
				$query.=' WHERE id='.$main_rec.';';
				$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
				$row = pg_fetch_row($result);
				pg_free_result($result);
			}

			if($data_array['factadd']){
				$query='SELECT * FROM pet_owners WHERE id='.string_formating_for_sql($data_array['factadd']).' ';
				$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
				$row_o = pg_fetch_assoc($result);
				pg_free_result($result);

				$query='UPDATE pet_owners SET ';
				$query.='id_fact_fias_address=\''.$row_o['id_fact_fias_address'].'\' ';
				$query.=' WHERE id='.$main_rec.';';
				$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
				$row = pg_fetch_row($result);
				pg_free_result($result);
			}

			if($data_array['description']){
				$query='SELECT * FROM pet_owners WHERE id='.string_formating_for_sql($data_array['description']).' ';
				$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
				$row_o = pg_fetch_assoc($result);
				pg_free_result($result);

				$query='UPDATE pet_owners SET ';
				$query.='description=\''.$row_o['description'].'\' ';
				$query.=' WHERE id='.$main_rec.';';
				$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
				$row = pg_fetch_row($result);
				pg_free_result($result);
			}

			if($data_array['telephone']){
				$query='UPDATE contacts SET ';
				$query.='entity_id='.$main_rec.' ';
				$query.=' WHERE entity_id='.$data_array['telephone'].' AND main_flag=true AND id_contact_type=1 AND entity_type=\'pet_owner\';';
				//echo $query."\n\n";
				$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
				$row = pg_fetch_row($result);
				pg_free_result($result);
			}

			if($data_array['email']){
				$query='UPDATE contacts SET ';
				$query.='entity_id='.$main_rec.' ';
				$query.=' WHERE entity_id='.$data_array['email'].' AND main_flag=true AND id_contact_type=6 AND entity_type=\'pet_owner\';';
				//echo $query."\n\n";
				$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
				$row = pg_fetch_row($result);
				pg_free_result($result);
			}

			$snils = '';
			if($data_array['snils']){
				//СНИЛС УНИКАЛЬНЫЙ, НЕЛЬЗЯ ДВУХ ПОЛЬЗОВАТЕЛЕЙ С ОДИНАКОВЫМ
				$query='SELECT * FROM pet_owners WHERE id='.string_formating_for_sql($data_array['snils']).' ';
				$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
				$row_o = pg_fetch_assoc($result);
				pg_free_result($result);
				$snils = $row_o['snils'];
			}

			for ($j=0; $j<count($idsArray); $j++) {
				if($idsArray[$j] != '' && $idsArray[$j] != $main_rec){
					//переносим все приёмы на владельца
					//visits
					$query='UPDATE visits SET ';
					$query.='id_owner='.$main_rec.', ';
					$query.="updated_by=".$id_user.",";#updated_by
					$query.="updated_at=NOW()::timestamp(0)";#updated_at
					$query.=' WHERE id_owner='.$idsArray[$j].';';
					//echo $query;
					$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
					$row = pg_fetch_row($result);
					pg_free_result($result);

					//удаляем ненужные контакты, неосновных владельцев
					$query_delete="DELETE FROM contacts WHERE entity_id=".$idsArray[$j]." ";
					$result = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
					pg_free_result($result);

					//переносим всех животных
					//pets_to_owner

					//проверка на существование связи
					$query_check='SELECT id FROM pets_to_owner WHERE id_owner='.string_formating_for_sql($id).' ';
					$result_check = pg_query($query_check) or die('Ошибка запроса: ' . pg_last_error());
					$row_check = pg_fetch_assoc($result_check);
					pg_free_result($result_check);
					//

					$query='UPDATE pets_to_owner SET ';
					$query.='id_owner='.$main_rec.' ';
					$query.=' WHERE id_owner='.$idsArray[$j].';';
					//echo $query."\n\n";
					$result = pg_query($query);
					if(pg_last_error()){
						//напоролись на FK, чистим предварительно
						$query_delete="DELETE FROM pets_to_owner WHERE id_owner=".$idsArray[$j]." ";
						$result_delete = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
						pg_free_result($result_delete);
					}else{
						// or die('Ошибка запроса: ' . pg_last_error());
						$row = pg_fetch_row($result);
						pg_free_result($result);
					}

					//переносим все нарушения
					//violation
					$query='UPDATE violation SET ';
					$query.='id_owner='.$main_rec.' ';
					$query.=' WHERE id_owner='.$idsArray[$j].';';
					//echo $query."\n\n";
					$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
					$row = pg_fetch_row($result);
					pg_free_result($result);

					//переносим все соглашения
					//agreements
					$query='UPDATE agreements SET ';
					$query.='id_pet_owner='.$main_rec.' ';
					$query.=' WHERE id_pet_owner='.$idsArray[$j].';';
					//echo $query."\n\n";
					$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
					$row = pg_fetch_row($result);
					pg_free_result($result);

					//переносим все сертификаты
					//reg_certificates
					$query='UPDATE reg_certificates SET ';
					$query.='id_owner='.$main_rec.' ';
					$query.=' WHERE id_owner='.$idsArray[$j].';';
					//echo $query."\n\n";
					$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
					$row = pg_fetch_row($result);
					pg_free_result($result);

					//удаляем дубль
					$query_delete="DELETE FROM pet_owners WHERE id=".$idsArray[$j]." ";
					//echo $query_delete."\n\n";
					$result_delete = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
					pg_free_result($result_delete);

					if($snils && $data_array['snils'] == $idsArray[$j]){
						$query='UPDATE pet_owners SET ';
						$query.='snils=\''.$snils.'\' ';
						$query.=' WHERE id='.$main_rec.';';
						$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
						pg_free_result($result);
					}
				}
			}
		}

		if($row['type'] == 2){//питомцы
			if($data_array['name']){
				$query='SELECT * FROM pets WHERE id='.string_formating_for_sql($data_array['name']).' ';
				$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
				$row_o = pg_fetch_assoc($result);
				pg_free_result($result);

				$query='UPDATE pets SET ';
				$query.='name=\''.$row_o['name'].'\' ';
				$query.=' WHERE id='.$main_rec.';';
				$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
				$row = pg_fetch_row($result);
				pg_free_result($result);
			}
			if($data_array['birthday']){
				$query='SELECT * FROM pets WHERE id='.string_formating_for_sql($data_array['birthday']).' ';
				$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
				$row_o = pg_fetch_assoc($result);
				pg_free_result($result);

				$query='UPDATE pets SET ';
				$query.='birthday=\''.$row_o['birthday'].'\' ';
				$query.=' WHERE id='.$main_rec.';';
				$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
				$row = pg_fetch_row($result);
				pg_free_result($result);
			}
			if($data_array['sex']){
				$query='SELECT * FROM pets WHERE id='.string_formating_for_sql($data_array['sex']).' ';
				$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
				$row_o = pg_fetch_assoc($result);
				pg_free_result($result);

				$query='UPDATE pets SET ';
				$query.='sex=\''.$row_o['sex'].'\' ';
				$query.=' WHERE id='.$main_rec.';';
				$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
				$row = pg_fetch_row($result);
				pg_free_result($result);
			}
			if($data_array['description']){
				$query='SELECT * FROM pets WHERE id='.string_formating_for_sql($data_array['description']).' ';
				$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
				$row_o = pg_fetch_assoc($result);
				pg_free_result($result);

				$query='UPDATE pets SET ';
				$query.='description=\''.$row_o['description'].'\' ';
				$query.=' WHERE id='.$main_rec.';';
				$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
				$row = pg_fetch_row($result);
				pg_free_result($result);
			}
			if($data_array['specie']){
				$query='SELECT id_species FROM pets WHERE id='.string_formating_for_sql($data_array['specie']).' ';
				$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
				$row_o = pg_fetch_assoc($result);
				pg_free_result($result);

				$query='UPDATE pets SET ';
				$query.='id_species=\''.$row_o['id_species'].'\' ';
				$query.=' WHERE id='.$main_rec.';';
				$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
				$row = pg_fetch_row($result);
				pg_free_result($result);
			}
			if($data_array['breed']){
				$query='SELECT id_breed FROM pets WHERE id='.string_formating_for_sql($data_array['breed']).' ';
				$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
				$row_o = pg_fetch_assoc($result);
				pg_free_result($result);

				$query='UPDATE pets SET ';
				$query.='id_breed=\''.$row_o['id_breed'].'\' ';
				$query.=' WHERE id='.$main_rec.';';
				$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
				$row = pg_fetch_row($result);
				pg_free_result($result);
			}
			if($data_array['chip']){
				$query='UPDATE pet_identification SET ';
				$query.='id_pet='.$main_rec.' ';
				$query.=' WHERE id_pet='.$data_array['chip'].' AND id_ident_type=1;';
				//echo $query."\n\n";
				$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
				$row = pg_fetch_row($result);
				pg_free_result($result);
			}
			// if($data_array['reg_certificate']){
			// 	$query='UPDATE reg_certificates SET ';
			// 	$query.='id_pet='.$main_rec.' ';
			// 	$query.=' WHERE id_pet='.$data_array['reg_certificate'].';';
			// 	//echo $query."\n\n";
			// 	$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			// 	$row = pg_fetch_row($result);
			// 	pg_free_result($result);
			// }

			if($data_array['label']){
				$query='UPDATE pet_identification SET ';
				$query.='id_pet='.$main_rec.' ';
				$query.=' WHERE id_pet='.$data_array['label'].' AND id_ident_type=2;';
				//echo $query."\n\n";
				$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
				$row = pg_fetch_row($result);
				pg_free_result($result);
			}

			for ($j=0; $j<count($idsArray); $j++) {
				if($idsArray[$j] != '' && $idsArray[$j] != $main_rec){
					//visits
					$query='UPDATE visit_pets SET ';
					$query.='id_pet='.$main_rec.', ';
					$query.="updated_by=".$id_user.",";#updated_by
					$query.="updated_at=NOW()::timestamp(0)";#updated_at
					$query.=' WHERE id_pet='.$idsArray[$j].';';
					//echo $query;
					$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
					$row = pg_fetch_row($result);
					pg_free_result($result);


					$query='UPDATE visits SET ';
					$query.='id_pet='.$main_rec.', ';
					$query.="updated_by=".$id_user.",";#updated_by
					$query.="updated_at=NOW()::timestamp(0)";#updated_at
					$query.=' WHERE id_pet='.$idsArray[$j].';';
					//echo $query;
					$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
					$row = pg_fetch_row($result);
					pg_free_result($result);

					//violation
					$query='UPDATE violation SET ';
					$query.='id_pet='.$main_rec.' ';
					$query.=' WHERE id_pet='.$idsArray[$j].';';
					//echo $query."\n\n";
					$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
					$row = pg_fetch_row($result);
					pg_free_result($result);

					//переносим все соглашения
					//agreements
					$query='UPDATE agreements SET ';
					$query.='id_pet='.$main_rec.' ';
					$query.=' WHERE id_pet='.$idsArray[$j].';';
					//echo $query."\n\n";
					$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
					$row = pg_fetch_row($result);
					pg_free_result($result);

					//перенос вакцинаций
					$query='UPDATE pet_rabies_vaccination SET ';
					$query.='id_pet='.$main_rec.' ';
					$query.=' WHERE id_pet='.$idsArray[$j].';';
					//echo $query."\n\n";
					$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
					$row = pg_fetch_row($result);
					pg_free_result($result);

					$query='UPDATE pet_other_vaccinations SET ';
					$query.='id_pet='.$main_rec.' ';
					$query.=' WHERE id_pet='.$idsArray[$j].';';
					//echo $query."\n\n";
					$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
					$row = pg_fetch_row($result);
					pg_free_result($result);

					$query='UPDATE pet_dehelmintization SET ';
					$query.='id_pet='.$main_rec.' ';
					$query.=' WHERE id_pet='.$idsArray[$j].';';
					//echo $query."\n\n";
					$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
					$row = pg_fetch_row($result);
					pg_free_result($result);

					$query='UPDATE pet_ectoparasites SET ';
					$query.='id_pet='.$main_rec.' ';
					$query.=' WHERE id_pet='.$idsArray[$j].';';
					//echo $query."\n\n";
					$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
					$row = pg_fetch_row($result);
					pg_free_result($result);

					//visit_descriptions
					$query='UPDATE visit_descriptions SET ';
					$query.='id_pet='.$main_rec.' ';
					$query.=' WHERE id_pet='.$idsArray[$j].';';
					//echo $query."\n\n";
					$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
					$row = pg_fetch_row($result);
					pg_free_result($result);

					$query_delete="DELETE FROM pets WHERE id=".$idsArray[$j]." ";
					//echo $query_delete."\n\n";
					$result_delete = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
					pg_free_result($result_delete);

					//переносим все сертификаты
					//reg_certificates
					$query='UPDATE reg_certificates SET ';
					$query.='id_pet='.$main_rec.' ';
					$query.=' WHERE id_pet='.$idsArray[$j].';';
					//echo $query."\n\n";
					$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
					$row = pg_fetch_row($result);
					pg_free_result($result);
				}
			}
		}

		//удаляем запись дубля
		$query_delete="DELETE FROM duplicates WHERE id=".$id." ";
		$result_delete = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
		pg_free_result($result_delete);

		$message='<message>duplicates_merged</message>';
		echo xml($message);
	}

	function duplicates_create_rec_xml($configuration){
		$type=$_GET["type"];
		//1 - владельцы - 3 флаг
		//2 - животные - 4 флаг
		$id1=$_GET["id1"];
		$id2=$_GET["id2"];
		//$id_user = user_id($configuration);
		//$id_org = user_org_id($configuration);

		//проверка на существование владельцев

		$query='INSERT INTO duplicates (';
		$query.='type, ';
		$query.='id_main, ';
		$query.='temp, ';
		$query.='ids, ';
		$query.='created_at) ';
		$query.='VALUES (';
		$query.="".$type.",";
		$query.="".$id1.",";
		$query.="true,";
		$query.="'{".$id1.", ".$id2."}',";
		$query.="NOW()::timestamp(0)";
		$query.=') RETURNING id;';
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		$row = pg_fetch_row($result);
		$id=$row[0];
		pg_free_result($result);

		$message='<id>'.$id.'</id>';
		$message.='<message>rec_added</message>';
		echo xml($message);
	}

function duplicates_show_auto_recs_xml($configuration){

    $page=intval($_GET["page"]);//страница
    if($page == ''){$page=1;}
    $sort=$_GET["sort"];//сортировка
    if($sort == ''){$sort='fullname';}
    $direction=$_GET["direction"];//направление

    $tab=$_GET["tab"];//направление

    $recs_on_page=50;//кол-во на странице

    if($tab == 1){
        if(
            $sort != 'fullname' &&
            $sort != 'address' &&
            $sort != 'fact_address' &&
            $sort != 'birthday' &&
            $sort != 'telephone' &&
            $sort != 'email' &&
            $sort != 'jon_date' &&
            $sort != 'duplicates'
        ){
            $sort='fullname';
        }

        header("Content-type: text/xml; charset=utf-8");
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<xml>';

        //ВЛАДЕЛЬЦЫ

        $fullname=string_formating_for_sql($_GET["fullname"]);
        $telephone=string_formating_for_sql($_GET["telephone"]);
        $id_auto_duplicate_owner=string_formating_for_sql($_GET["id_auto_duplicate_owner"]);
        $join_owner_date_from=string_formating_for_sql($_GET["join_owner_date_from"]);
        $join_owner_date_to=string_formating_for_sql($_GET["join_owner_date_to"]);

        $query = 'SELECT ';
        $query .= 'po.id AS pet_owners_id, po.created_at AS join_date, po.fullname AS fullname, ';
        $query .= 'ARRAY_AGG(DISTINCT p.id) AS pet_ids, Ad.full_address AS address, ';
        $query .= 'Fad.full_address AS fact_address, po.birthday, C.name AS telephone, E.name AS email, ';
        $query .= 'ARRAY_AGG(DISTINCT a.id) AS duplicates  ';
        $query .= 'FROM public.pet_owners po ';
        $query .= 'RIGHT JOIN archive.pet_owners a ON po.id = a.result_id ';
        $query .= 'LEFT JOIN public.duplicates_groups dg ON a.id = ANY(dg.duplicates) ';
        $query .= "AND dg.type = 'owner' AND status = 'complete' ";
        $query .= 'LEFT JOIN public.pets_to_owner pto ON po.id = pto.id_owner ';
        $query .= 'LEFT JOIN public.pets p ON p.id = pto.id_pet ';
        $query .= 'LEFT JOIN public.fias_addresses Ad ON Ad.id = po.id_fias_address ';
        $query .= 'LEFT JOIN public.fias_addresses Fad ON Fad.id = po.id_fact_fias_address ';
        $query .= 'LEFT JOIN public.contacts C ON C.entity_id = po.id AND C.id_contact_type = 1 ';
        $query .= 'LEFT JOIN public.contacts E ON E.entity_id = po.id AND E.id_contact_type = 6 ';

        $whereClauses = [];
        if (!empty($fullname)) {
            $whereClauses[] = "public.pet_owners.fullname ILIKE '%" . pg_escape_string($fullname) . "%'";
        }
        if (!empty($telephone)) {
            $whereClauses[] = "C.name ILIKE '%" . pg_escape_string($telephone) . "%'";
        }
        if (!empty($id_auto_duplicate_owner)) {
            $whereClauses[] = "public.pet_owners.id = " . pg_escape_string($id_auto_duplicate_owner) . " ";
        }
        if (!empty($join_owner_date_from)) {
            $whereClauses[] = "public.pet_owners.created_at >='" . string_formating_for_sql($join_owner_date_from) . "'";
        }
        if (!empty($join_owner_date_to)) {
            $whereClauses[] = "public.pet_owners.created_at <='" . string_formating_for_sql($join_owner_date_to) . "'";
        }

        if (!empty($whereClauses)) {
            $query .= 'WHERE ' . implode(' AND ', $whereClauses) . ' ';
        }

        $query .= 'GROUP BY po.id, po.created_at, po.fullname, Ad.full_address, ';
        $query .= 'Fad.full_address,  po.birthday, C.name, E.name ';

        //Пагинация
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $recs_counter = pg_num_rows($result);//количество записей
        pg_free_result($result);

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
        //Пагинация

        //сортировка по умолчанию
        $query.='ORDER BY ';
        if($sort == 'fullname'){
            $query.='fullname ';
        }else if($sort == 'address'){
            $query.='address ';
        }else if($sort == 'fact_address'){
            $query.='fact_address ';
        }else if($sort == 'telephone'){
            $query.='telephone ';
        }else if($sort == 'email'){
            $query.='email ';
        }else if($sort == 'join_date'){
            $query.='join_date ';
        } else if($sort == 'id_rec'){
            $query.='pet_owners_id ';
        }else{
            $query.='fullname ';
        }

        if($direction){
            $query.='ASC NULLS LAST ';
        }else{
            $query.='DESC NULLS LAST ';
        }
        //сортировка по умолчанию`

        echo '<recs>';
        //
        $query.='LIMIT '.$recs_on_page.' OFFSET '.intval(($page-1)*$recs_on_page).'';
        //

        $Data='';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        while ($row = pg_fetch_assoc($result)) {
            $Data.='<rec>';
            $Data.='<rec_id>'.$row['pet_owners_id'].'</rec_id>';
            $Data.='<rec_fullname>'.$row['fullname'].'</rec_fullname>';
            $Data.='<rec_address>'.$row['address'].'</rec_address>';
            $Data.='<rec_fact_address>'.$row['fact_address'].'</rec_fact_address>';
            $Data.='<rec_telephone>'.$row['telephone'].'</rec_telephone>';
            $Data.='<rec_email>'.$row['email'].'</rec_email>';
            $Data.='<rec_join_date>'.$row['join_date'].'</rec_join_date>';
            $Data.='<rec_card_duplicates>'.$row['duplicates'].'</rec_card_duplicates>';
            $Data.='<rec_animals_duplicates>'.$row['pet_ids'].'</rec_animals_duplicates>';
            $Data.='</rec>';
        }
        pg_free_result($result);
        echo $Data;
        echo '</recs>';
        echo '</xml>';
    }
    //Заготовка под дубликаты животных
    else{
        if(
            $sort != 'name' &&
            $sort != 'address' &&
            $sort != 'age' &&
            $sort != 'specie' &&
            $sort != 'breed' &&
            $sort != 'join_date' &&
            $sort != 'duplicates'
        ){
            $sort='name';
        }

        header("Content-type: text/xml; charset=utf-8");
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<xml>';

        // ЖИВОТНЫЕ

        $name=string_formating_for_sql($_GET["name"]);
        $specie=string_formating_for_sql($_GET["specie"]);
        $id_auto_duplicate_pet=string_formating_for_sql($_GET["id_auto_duplicate_pet"]);
        $join_pet_date_from=string_formating_for_sql($_GET["join_pet_date_from"]);
        $join_pet_date_to=string_formating_for_sql($_GET["join_pet_date_to"]);
        $Data='';

        //////////////////////
        $query='SELECT ';
        $query.='duplicates.id, duplicates.id_main, pet_owners.id as owner_id, pets.name, pets.birthday, species.name AS specie, breeds.name AS breed, duplicates.created_at as join_date, ';
        $query.='array_length(duplicates.ids,1)-1 AS num_duplicates ';
        $query.='FROM duplicates ';

        $query.='LEFT JOIN pets ON pets.id=duplicates.id_main ';
        $query.='LEFT JOIN species ON pets.id_species = species.id ';
        $query.='LEFT JOIN breeds ON pets.id_breed = breeds.id ';

        $query.='LEFT JOIN pets_to_owner ON pets.id=pets_to_owner.id_pet ';
        $query.='LEFT JOIN pet_owners ON pet_owners.id=pets_to_owner.id_owner ';

        $query.='WHERE duplicates.temp=false AND duplicates.type=2 ';
        if($name){$query.="AND pets.name ILIKE '%".$name."%' ";}
        if($specie){$query.="AND species.id=".$specie." ";}
        if($id_auto_duplicate_pet){$query.="AND duplicates.id_main=".$id_auto_duplicate_pet." ";}
        if (!empty($join_pet_date_from)) { $query.= "duplicates.created_at >='" . string_formating_for_sql($join_pet_date_from) . "'";}
        if (!empty($join_pet_date_to)) {$query.= "duplicates.created_at <='" . string_formating_for_sql($join_pet_date_to) . "'";}

        $query.='GROUP BY duplicates.id, duplicates.id_main, pets.name, pets.birthday, species.name, breeds.name, pet_owners.id, duplicates.created_at ';

        //Пагинация
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $recs_counter = pg_num_rows($result);//количество записей
        pg_free_result($result);

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
        //Пагинация

        //сортировка по умолчанию
        $query.='ORDER BY ';
        if($sort == 'name'){
            $query.='name ';
        }else if($sort == 'address'){
            $query.='address ';
        }else if($sort == 'breed'){
            $query.='breed ';
        }else if($sort == 'specie'){
            $query.='specie ';
        }else if($sort == 'join_date'){
            $query.='join_date ';
        }else if($sort == 'owner_id'){
            $query.='owner_id ';
        }else if($sort == 'id_main'){
            $query.='id_main ';
        } else{
            $query.='name ';
        }

        if($direction){
            $query.='ASC NULLS LAST ';
        }else{
            $query.='DESC NULLS LAST ';
        }
        //сортировка по умолчанию
        //echo $query;

        echo '<recs>';
        //
        $query.='LIMIT '.$recs_on_page.' OFFSET '.intval(($page-1)*$recs_on_page).'';
        //

        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        while ($row = pg_fetch_assoc($result)) {
            $Data.='<rec>';
            $Data.='<rec_id_pet>'.$row['id_main'].'</rec_id_pet>';
            $Data.='<rec_id_owner>'.$row['owner_id'].'</rec_id_owner>';
            $Data.='<rec_join_date>'.$row['join_date'].'</rec_join_date>';
            $Data.='<rec_name>'.$row['name'].'</rec_name>';
            $Data.='<rec_address>'.$row['address'].'</rec_address>';
            $Data.='<rec_breed>'.$row['breed'].'</rec_breed>';
            $Data.='<rec_specie>'.$row['specie'].'</rec_specie>';

            $Data.='</rec>';
        }
        pg_free_result($result);

        echo $Data;
        echo '</recs>';
        echo '</xml>';
    }
}
function duplicates_show_archive_recs_xml($configuration){
    $page=intval($_GET["page"]);//страница
    if($page == ''){$page=1;}
    $sort=$_GET["sort"];//сортировка
    if($sort == ''){$sort='arrival_date';}
    $direction=$_GET["direction"];//направление

    $tab=$_GET["tab"];//направление

    $recs_on_page=50;//кол-во на странице

    if($tab == 1){
        if(
            $sort != 'fullname' &&
            $sort != 'address' &&
            $sort != 'fact_address' &&
            $sort != 'birthday' &&
            $sort != 'telephone' &&
            $sort != 'email' &&
            $sort != 'jon_date' &&
            $sort != 'duplicates'
        ){
            $sort='fullname';
        }

        header("Content-type: text/xml; charset=utf-8");
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<xml>';

        //ВЛАДЕЛЬЦЫ

        $archive_duplicate_owner_fullname=string_formating_for_sql($_GET["archive_duplicate_owner_fullname"]);
        $archive_duplicate_owner_telephone=string_formating_for_sql($_GET["archive_duplicate_owner_telephone"]);
        $id_archive_duplicate_owner=string_formating_for_sql($_GET["id_archive_duplicate_owner"]);
        $archive_join_owner_date_from=string_formating_for_sql($_GET["archive_join_owner_date_from"]);
        $archive_join_owner_date_to=string_formating_for_sql($_GET["archive_join_owner_date_to"]);

        $query = 'SELECT ';
        $query .= 'archive.pet_owners.id as pet_owners_id, archive.pet_owners.created_at as archive_date, ';
        $query .= 'archive.pet_owners.fullname as fullname, ';
        $query .= 'archive.pet_owners.result_id as main_card, ';
        $query .= 'Ad.full_address AS address, Fad.full_address AS fact_address ';
        $query .= 'FROM archive.pet_owners ';
        $query .= 'LEFT JOIN public.fias_addresses Ad ON Ad.id = archive.pet_owners.id_fias_address ';
        $query .= 'LEFT JOIN public.fias_addresses Fad ON Fad.id = archive.pet_owners.id_fact_fias_address ';

        $whereClauses = [];
        if (!empty($archive_duplicate_owner_fullname)) {
            $whereClauses[] = "archive.pet_owners.fullname ILIKE '%" . pg_escape_string($archive_duplicate_owner_fullname) . "%'";
        }
        if (!empty($archive_duplicate_owner_telephone)) {
            $whereClauses[] = "C.name ILIKE '%" . pg_escape_string($archive_duplicate_owner_telephone) . "%'";
        }
        if($id_archive_duplicate_owner){
            $whereClauses[] = "archive.pet_owners.id=".$id_archive_duplicate_owner." ";
        }
        if (!empty($archive_join_owner_date_from)) {
            $whereClauses[] = "public.pet_owners.created_at >='" . string_formating_for_sql($archive_join_owner_date_from) . "'";
        }
        if (!empty($archive_join_owner_date_to)) {
            $whereClauses[] = "public.pet_owners.created_at <='" . string_formating_for_sql($archive_join_owner_date_to) . "'";
        }

        if (!empty($whereClauses)) {
            $query .= 'WHERE ' . implode(' AND ', $whereClauses) . ' ';
        }

        $query .= 'GROUP BY archive.pet_owners.id, archive.pet_owners.created_at, archive.pet_owners.fullname, ';
        $query .= 'Ad.full_address, Fad.full_address, archive.pet_owners.birthday, archive.pet_owners.result_id ';

        //Пагинация
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $recs_counter = pg_num_rows($result);//количество записей
        pg_free_result($result);

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
        //Пагинация

        //сортировка по умолчанию
        $query.='ORDER BY ';
        if($sort == 'fullname'){
            $query.='fullname ';
        }else if($sort == 'address'){
            $query.='address ';
        }else if($sort == 'fact_address'){
            $query.='fact_address ';
        }else if($sort == 'archive_date'){
            $query.='archive_date ';
        }else if($sort == 'id_rec'){
            $query.='pet_owners_id ';
        }else if($sort == 'main_card'){
            $query.='main_card ';
        }else{
            $query.='fullname ';
        }

        if($direction){
            $query.='ASC NULLS LAST ';
        }else{
            $query.='DESC NULLS LAST ';
        }
        //сортировка по умолчанию`

        echo '<recs>';
        //
        $query.='LIMIT '.$recs_on_page.' OFFSET '.intval(($page-1)*$recs_on_page).'';
        //

        $Data='';
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        while ($row = pg_fetch_assoc($result)) {
            $Data.='<rec>';
            $Data.='<rec_id>'.$row['pet_owners_id'].'</rec_id>';
            $Data.='<rec_fullname>'.$row['fullname'].'</rec_fullname>';
            $Data.='<rec_address>'.$row['address'].'</rec_address>';
            $Data.='<rec_fact_address>'.$row['fact_address'].'</rec_fact_address>';
            $Data.='<rec_archive_date>'.$row['archive_date'].'</rec_archive_date>';
            $Data.='<rec_main_card>'.$row['main_card'].'</rec_main_card>';

            $Data.='</rec>';
        }
        pg_free_result($result);
        echo $Data;
        echo '</recs>';
        echo '</xml>';
    }
    // Заготовка под дубликаты животных
    else{
        if(
            $sort != 'name' &&
            $sort != 'address' &&
            $sort != 'age' &&
            $sort != 'specie' &&
            $sort != 'breed' &&
            $sort != 'join_date' &&
            $sort != 'duplicates'
        ){
            $sort='name';
        }

        header("Content-type: text/xml; charset=utf-8");
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<xml>';

        // ЖИВОТНЫЕ

        $name=string_formating_for_sql($_GET["name"]);
        $specie=string_formating_for_sql($_GET["specie"]);
        $id_archive_duplicate=string_formating_for_sql($_GET["id_archive_duplicate"]);
        $archive_join_pet_date_from=string_formating_for_sql($_GET["archive_join_pet_date_from"]);
        $archive_join_pet_date_to=string_formating_for_sql($_GET["archive_join_pet_date_to"]);

        $Data='';

        //////////////////////
        $query='SELECT ';
        $query.='duplicates.id, duplicates.id_main, pet_owners.id as owner_id, pets.name, pets.birthday, species.name AS specie, breeds.name AS breed, duplicates.created_at as join_date, ';
        $query.='array_length(duplicates.ids,1)-1 AS num_duplicates ';
        $query.='FROM duplicates ';

        $query.='LEFT JOIN pets ON pets.id=duplicates.id_main ';
        $query.='LEFT JOIN species ON pets.id_species = species.id ';
        $query.='LEFT JOIN breeds ON pets.id_breed = breeds.id ';

        $query.='LEFT JOIN pets_to_owner ON pets.id=pets_to_owner.id_pet ';
        $query.='LEFT JOIN pet_owners ON pet_owners.id=pets_to_owner.id_owner ';

        $query.='WHERE duplicates.temp=false AND duplicates.type=2 ';
        if($name){$query.="AND pets.name ILIKE '%".$name."%' ";}
        if($specie){$query.="AND species.id=".$specie." ";}
        if($id_archive_duplicate){$query.="AND duplicates.id_main=".$id_archive_duplicate." ";}
        if (!empty($archive_join_pet_date_from)) { $query.= "duplicates.created_at >='" . string_formating_for_sql($archive_join_pet_date_from) . "'";}
        if (!empty($archive_join_pet_date_to)) {$query.= "duplicates.created_at <='" . string_formating_for_sql($archive_join_pet_date_to) . "'";}

        $query.='GROUP BY duplicates.id, duplicates.id_main, pets.name, pets.birthday, species.name, breeds.name, pet_owners.id, duplicates.created_at ';

        //Пагинация
        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        $recs_counter = pg_num_rows($result);//количество записей
        pg_free_result($result);

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
        //Пагинация

        //сортировка по умолчанию
        $query.='ORDER BY ';
        if($sort == 'name'){
            $query.='name ';
        }else if($sort == 'address'){
            $query.='address ';
        }else if($sort == 'breed'){
            $query.='breed ';
        }else if($sort == 'specie'){
            $query.='specie ';
        }else if($sort == 'id_pet'){
            $query.='id_main ';
        }else if($sort == 'owner_id'){
            $query.='owner_id ';
        }else if($sort == 'join_date'){
            $query.='join_date ';
        }else{
            $query.='name ';
        }

        if($direction){
            $query.='ASC NULLS LAST ';
        }else{
            $query.='DESC NULLS LAST ';
        }
        //сортировка по умолчанию
        //echo $query;

        echo '<recs>';
        //
        $query.='LIMIT '.$recs_on_page.' OFFSET '.intval(($page-1)*$recs_on_page).'';
        //

        $result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
        while ($row = pg_fetch_assoc($result)) {
            $Data.='<rec>';
            $Data.='<rec_id_pet>'.$row['id_main'].'</rec_id_pet>';
            $Data.='<rec_owner_id>'.$row['owner_id'].'</rec_owner_id>';
            $Data.='<rec_join_date>'.$row['join_date'].'</rec_join_date>';
            $Data.='<rec_name>'.$row['name'].'</rec_name>';
            $Data.='<rec_address>'.$row['address'].'</rec_address>';
            $Data.='<rec_breed>'.$row['breed'].'</rec_breed>';
            $Data.='<rec_specie>'.$row['specie'].'</rec_specie>';

            $Data.='</rec>';
        }
        pg_free_result($result);

        echo $Data;
        echo '</recs>';
        echo '</xml>';
    }
}

	function duplicates_show_recs_xml($configuration){
		$page=intval($_GET["page"]);//страница
		if($page == ''){$page=1;}
		$sort=$_GET["sort"];//сортировка
		if($sort == ''){$sort='arrival_date';}
		$direction=$_GET["direction"];//направление

		$tab=$_GET["tab"];//направление

		$recs_on_page=50;//кол-во на странице

		if($tab == 1){
			if(
				$sort != 'fullname' &&
				$sort != 'address' &&
				$sort != 'fact_address' &&
				$sort != 'birthday' &&
				$sort != 'telephone' &&
				$sort != 'email' &&
				$sort != 'last_visit' &&
				$sort != 'num_duplicates'
			){
				$sort='fullname';
			}

			header("Content-type: text/xml; charset=utf-8");
			echo '<?xml version="1.0" encoding="UTF-8"?>';
			echo '<xml>';

			$fullname=string_formating_for_sql($_GET["fullname"]);
			$telephone=string_formating_for_sql($_GET["telephone"]);

			$query='SELECT ';
			$query.='duplicates.id, duplicates.id_main, pet_owners.fullname, Ad.full_address AS address, Fad.full_address AS fact_address, pet_owners.birthday, C.name AS telephone, ';
			$query.='E.name AS email, MAX((LOWER(VISITS.TIME_RANGE))) AS last_visit, array_length(duplicates.ids,1)-1 AS num_duplicates ';
			$query.='FROM duplicates ';
			$query.='LEFT JOIN pet_owners ON pet_owners.id=duplicates.id_main ';
			$query.='LEFT JOIN visits ON visits.id_owner=duplicates.id_main ';
			$query.='LEFT JOIN fias_addresses Ad ON Ad.id=pet_owners.id_fias_address ';
			$query.='LEFT JOIN fias_addresses Fad ON Fad.id=pet_owners.id_fact_fias_address ';
			$query.='LEFT JOIN contacts C ON C.entity_id=pet_owners.id AND C.main_flag=true AND C.id_contact_type=1 ';
			$query.='LEFT JOIN contacts E ON E.entity_id=pet_owners.id AND E.main_flag=true AND E.id_contact_type=6 ';
			$query.='WHERE duplicates.temp=false AND duplicates.type=1 ';
			if($fullname){$query.="AND pet_owners.fullname ILIKE '%".$fullname."%' ";}
			if($telephone){$query.="AND C.name ILIKE '%".$telephone."%' ";}

			$query.='GROUP BY duplicates.id, duplicates.id_main, pet_owners.fullname, pet_owners.birthday, Ad.full_address, Fad.full_address, C.name, E.name, array_length(duplicates.ids,1)';

			//Пагинация
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			$recs_counter = pg_num_rows($result);//количество записей
			pg_free_result($result);

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
			//Пагинация

			//сортировка по умолчанию
			$query.='ORDER BY ';
			if($sort == 'fullname'){
				$query.='fullname ';
			}else if($sort == 'address'){
				$query.='address ';
			}else if($sort == 'fact_address'){
				$query.='fact_address ';
			}else if($sort == 'birthday'){
				$query.='birthday ';
			}else if($sort == 'telephone'){
				$query.='telephone ';
			}else if($sort == 'email'){
				$query.='email ';
			}else if($sort == 'last_visit'){
				$query.='last_visit ';
			}else if($sort == 'num_duplicates'){
				$query.='num_duplicates ';
			}else{
				$query.='fullname ';
			}

			if($direction){
				$query.='ASC NULLS LAST ';
			}else{
				$query.='DESC NULLS LAST ';
			}
			//сортировка по умолчанию`

			echo '<recs>';
			//
			$query.='LIMIT '.$recs_on_page.' OFFSET '.intval(($page-1)*$recs_on_page).'';
			//


			$Data='';
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			while ($row = pg_fetch_assoc($result)) {
				$Data.='<rec>';
				$Data.='<rec_id>'.$row['id'].'</rec_id>';

				$Data.='<rec_fullname>'.$row['fullname'].'</rec_fullname>';
				$Data.='<rec_address>'.$row['address'].'</rec_address>';
				$Data.='<rec_fact_address>'.$row['fact_address'].'</rec_fact_address>';
				$Data.='<rec_telephone>'.$row['telephone'].'</rec_telephone>';
				$Data.='<rec_email>'.$row['email'].'</rec_email>';
				$Data.='<rec_last_visit>'.$row['last_visit'].'</rec_last_visit>';
				$Data.='<rec_num_duplicates>'.$row['num_duplicates'].'</rec_num_duplicates>';

				if($row['birthday']){
					$Data.='<rec_birthday>'.date_format(new \DateTime($row['birthday']), "d.m.Y").'</rec_birthday>';
				}else{
					$Data.='<rec_birthday></rec_birthday>';
				}

				$Data.='</rec>';
			}
			pg_free_result($result);
			echo $Data;
			echo '</recs>';
			echo '</xml>';
		}else{
			if(
				$sort != 'name' &&
				$sort != 'address' &&
				$sort != 'age' &&
				$sort != 'specie' &&
				$sort != 'breed' &&
				$sort != 'last_visit' &&
				$sort != 'num_duplicates'
			){
				$sort='name';
			}

			header("Content-type: text/xml; charset=utf-8");
			echo '<?xml version="1.0" encoding="UTF-8"?>';
			echo '<xml>';

			$name=string_formating_for_sql($_GET["name"]);
			$specie=string_formating_for_sql($_GET["specie"]);

			$Data='';

			//////////////////////
			$query='SELECT ';
			$query.='duplicates.id, duplicates.id_main, pets.name, pets.birthday, species.name AS specie, breeds.name AS breed, fias_addresses.full_address AS address, ';
			$query.='MAX((LOWER(VISITS.TIME_RANGE))) AS last_visit, array_length(duplicates.ids,1)-1 AS num_duplicates ';
			$query.='FROM duplicates ';

			$query.='LEFT JOIN pets ON pets.id=duplicates.id_main ';
			$query.='LEFT JOIN visit_pets ON visit_pets.id_pet=duplicates.id_main ';
			$query.='LEFT JOIN visits ON visit_pets.id_visit=visits.id ';
			$query.='LEFT JOIN species ON pets.id_species = species.id ';
			$query.='LEFT JOIN breeds ON pets.id_breed = breeds.id ';
			$query.='LEFT JOIN fias_addresses ON fias_addresses.id=pets.id_fias_address ';

			$query.='WHERE duplicates.temp=false AND duplicates.type=2 ';
			if($name){$query.="AND pets.name ILIKE '%".$name."%' ";}
			if($specie){$query.="AND species.id=".$specie." ";}

			$query.='GROUP BY duplicates.id, duplicates.id_main, pets.name, pets.birthday, species.name, breeds.name, fias_addresses.full_address ';

			//Пагинация
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			$recs_counter = pg_num_rows($result);//количество записей
			pg_free_result($result);

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
			//Пагинация

			//сортировка по умолчанию
			$query.='ORDER BY ';
			if($sort == 'name'){
				$query.='name ';
			}else if($sort == 'address'){
				$query.='address ';
			}else if($sort == 'age'){
				$query.='age ';
			}else if($sort == 'breed'){
				$query.='breed ';
			}else if($sort == 'specie'){
				$query.='specie ';
			}else if($sort == 'last_visit'){
				$query.='last_visit ';
			}else if($sort == 'num_duplicates'){
				$query.='num_duplicates ';
			}else{
				$query.='name ';
			}

			if($direction){
				$query.='ASC NULLS LAST ';
			}else{
				$query.='DESC NULLS LAST ';
			}
			//сортировка по умолчанию
			//echo $query;

			echo '<recs>';
			//
			$query.='LIMIT '.$recs_on_page.' OFFSET '.intval(($page-1)*$recs_on_page).'';
			//

			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			while ($row = pg_fetch_assoc($result)) {
				$Data.='<rec>';
				$Data.='<rec_id>'.$row['id'].'</rec_id>';

				$Data.='<rec_name>'.$row['name'].'</rec_name>';
				$Data.='<rec_address>'.$row['address'].'</rec_address>';
				$Data.='<rec_breed>'.$row['breed'].'</rec_breed>';
				$Data.='<rec_specie>'.$row['specie'].'</rec_specie>';
				$Data.='<rec_last_visit>'.$row['last_visit'].'</rec_last_visit>';
				$Data.='<rec_num_duplicates>'.$row['num_duplicates'].'</rec_num_duplicates>';

				if($row['birthday']){
					$Data.='<rec_age>'.calculate_age($row['birthday']).'</rec_age>';
					$Data.='<rec_birthday>'.date_format(new \DateTime($row['birthday']), "d.m.Y").'</rec_birthday>';
				}else{
					$Data.='<rec_birthday></rec_birthday>';
					$Data.='<rec_age></rec_age>';
				}

				$Data.='</rec>';
			}
			pg_free_result($result);

			echo $Data;
			echo '</recs>';
			echo '</xml>';
		}
	}

	function duplicates_create_rec_auto_xml($config){
		$mode=$_GET["mode"];

		$limit = 500;

		//старт

		//1 - владельцы - 3 флаг
		if($mode == 'owners'){
			//max_id_main = выбираем max id_main from duplicates нужны флаговые записи, т.е. запись на которой остановились последний раз//3/4 тип
			$query='SELECT MAX(id_main) AS id_main FROM duplicates WHERE type=3 ';
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			$row = pg_fetch_assoc($result);
			pg_free_result($result);

			//с чего начинаем
			$id_last_rec = 1;
			if($row['id_main'] != ''){
				$id_last_rec = $row['id_main'];
			}

			//выбираем pet_owners id > max_id_main + LIMIT число строк для проверки
			$query='SELECT id_main_owner, pet_owners.fullname, ';
			$query.='pet_owners.id, ';
			$query.='fias_addresses.cityguid, ';//cityguid
			$query.='fias_addresses.streetguid, ';//streetguid
			$query.='fias_addresses.houseguid, ';//houseguid
			$query.='fias_addresses.roomguid ';//roomguid
			$query.='FROM pet_owners ';
			$query.='LEFT JOIN fias_addresses ON fias_addresses.id=pet_owners.id_fact_fias_address ';
			$query.='WHERE pet_owners.id > '.$id_last_rec.' AND ';
			$query.='fias_addresses.roomguid IS NOT NULL ORDER BY pet_owners.id LIMIT '.$limit.' ';
			//echo $query;
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());

			//запускаем цикл по сущностям
			while ($row = pg_fetch_assoc($result)) {
				//проверяем есть ли он в дублях
				$query_='SELECT id FROM duplicates ';
				$query_.='WHERE ids @> \'{'.$row['id'].'}\'::int[]; ';
				$result_ = pg_query($query_) or die('Ошибка запроса: ' . pg_last_error());
				$row_ = pg_fetch_assoc($result_);
				pg_free_result($result_);

				$id_main = $row['id'];
				//у записи есть родитель
				$id_main_owner = $row['id_main_owner'];

				if($row_['id']){
					//да-->
					//отбой
				}else{
					//нет-->
					//сравниваем по fias со всей таблицей (цикл)->
					//cityguid//streetguid//houseguid//roomguid

					#############
					$query_='SELECT pet_owners.id, pet_owners.fullname, ';
					$query_.='id_main_owner, ';
					$query_.='fias_addresses.cityguid, ';//cityguid
					$query_.='fias_addresses.streetguid, ';//streetguid
					$query_.='fias_addresses.houseguid, ';//houseguid
					$query_.='fias_addresses.roomguid ';//roomguid
					$query_.='FROM pet_owners ';
					$query_.='LEFT JOIN fias_addresses ON fias_addresses.id=pet_owners.id_fact_fias_address ';
					$query_.='WHERE ';
					//$query_.='WHERE pet_owners.id > '.$id_last_rec.' AND ';
					$query_.='fias_addresses.roomguid = \''.$row['roomguid'].'\' AND pet_owners.fullname = \''.string_formating_for_sql($row['fullname']).'\' AND pet_owners.id <> '.$id_main.'';
					//echo $query;
					$result_ = pg_query($query_) or die('Ошибка запроса: ' . pg_last_error());
					//запускаем цикл по сущностям
					$ids='';
					$idsArray = array();
					while ($row_ = pg_fetch_assoc($result_)) {
						if($ids){$ids.=',';}
						//echo "*".$row['id']."---".$row_['id']."<br />";
						$ids.=$row_['id'];
						array_push($idsArray, $row_['id']);

						//ищем еще его родителя с дочками
						if($row_['id_main_owner']){
							$query__='SELECT pet_owners.id, id_main_owner FROM pet_owners WHERE id='.$row_['id_main_owner'].' OR id_main_owner='.$row_['id_main_owner'].' ';
							$result__ = pg_query($query__) or die('Ошибка запроса: ' . pg_last_error());
							while ($row__ = pg_fetch_assoc($result__)) {
								if($ids){$ids.=',';}
								$ids.='*'.$row__['id'].'*';
								array_push($idsArray, $row__['id']);
							}
							pg_free_result($result__);
						}
						// id_main_owner
						//ищем еще его родителя с дочками
					}
					pg_free_result($result_);
					##############

					if($ids){
						//
						#############
						//его надо сделать главным
						//и взять его дочек
						if($id_main_owner){
							$query__='SELECT pet_owners.id, id_main_owner FROM pet_owners WHERE id='.$id_main.' OR id_main_owner='.$id_main.' ';
							$result__ = pg_query($query__) or die('Ошибка запроса: ' . pg_last_error());
							while ($row__ = pg_fetch_assoc($result__)) {
								if($row__['id'] != $id_main){
									if($ids){$ids.=',';}
									$ids.=$row__['id'];
									array_push($idsArray, $row__['id']);
								}
							}
							pg_free_result($result__);
						}
						//
						$idsArray = array_unique($idsArray);
						$ids_='';
						for($i=0;$i<=count($idsArray);$i++){
							if($idsArray[$i] && $id_main != $idsArray[$i]){
								if($ids_){$ids_.=',';}
								$ids_.=''.$idsArray[$i].'';
							}
						}
						if($id_main_owner){
							$id_main = $id_main_owner;
						}

						if($id_main != $ids_){
							$query_save='INSERT INTO duplicates (';
							$query_save.='type, ';
							$query_save.='id_main, ';
							$query_save.='temp, ';
							$query_save.='ids, ';
							$query_save.='created_at) ';
							$query_save.='VALUES (';
							$query_save.="1,";
							$query_save.="".$id_main.",";
							$query_save.="false,";
							$query_save.="'{".$id_main.','.$ids_."}',";
							$query_save.="NOW()::timestamp(0)";
							$query_save.=') RETURNING id;';
							//echo $query_save."<br />";
							$result_save = pg_query($query_save) or die('Ошибка запроса: ' . pg_last_error());
							pg_free_result($result_save);

							//чистим дубликаты
							$query_delete='DELETE FROM duplicates WHERE type=3';
							$result_delete = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
							pg_free_result($result_delete);

							//добавляем маркер
							$query_save='INSERT INTO duplicates (';
							$query_save.='type, ';
							$query_save.='id_main, ';
							$query_save.='temp, ';
							$query_save.='created_at) ';
							$query_save.='VALUES (';
							$query_save.="3,";
							$query_save.="".$id_main.",";
							$query_save.="false,";
							$query_save.="NOW()::timestamp(0)";
							$query_save.=') RETURNING id;';
							$result_save = pg_query($query_save) or die('Ошибка запроса: ' . pg_last_error());
							pg_free_result($result_save);
						}
					}else{

					}
				}
			}
			pg_free_result($result);
		}

		//2 - животные - 4 флаг
		if($mode == 'pets'){
			//max_id_main = выбираем max id_main from duplicates нужны флаговые записи, т.е. запись на которой остановились последний раз//3/4 тип
			$query='SELECT MAX(id_main) AS id_main FROM duplicates WHERE type=4 ';
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			$row = pg_fetch_assoc($result);
			pg_free_result($result);

			//с чего начинаем
			$id_last_rec = 1;
			if($row['id_main'] != ''){
				$id_last_rec = $row['id_main'];
			}

			//выбираем pet_owners id > max_id_main + LIMIT число строк для проверки
			$query='SELECT pets.id_main_pet, pets.id, pets.id_species AS specie, pets.name AS name, ';
			$query.='fias_addresses.cityguid, ';//cityguid
			$query.='fias_addresses.streetguid, ';//streetguid
			$query.='fias_addresses.houseguid, ';//houseguid
			$query.='fias_addresses.roomguid ';//roomguid
			$query.='FROM pets ';
			$query.='LEFT JOIN fias_addresses ON fias_addresses.id=pets.id_fias_address ';
			$query.='WHERE pets.id > '.$id_last_rec.' AND ';
			$query.='fias_addresses.roomguid IS NOT NULL ORDER BY pets.id LIMIT '.$limit.' ';
			//echo $query;
			$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());

			//запускаем цикл по сущностям
			while ($row = pg_fetch_assoc($result)) {
				//проверяем есть ли он в дублях
				$query_='SELECT id FROM duplicates ';
				$query_.='WHERE ids @> \'{'.$row['id'].'}\'::int[]; ';
				$result_ = pg_query($query_) or die('Ошибка запроса: ' . pg_last_error());
				$row_ = pg_fetch_assoc($result_);
				pg_free_result($result_);

				$id_main = $row['id'];
				$name_main = $row['name'];
				$specie_main = $row['specie'];
				$id_main_pet = $row['id_main_pet'];

				if($row_['id']){
					//да-->
					//отбой
				}else{
					//нет-->
					//сравниваем по fias со всей таблицей (цикл)->
					//cityguid//streetguid//houseguid//roomguid

					#############
					$query_='SELECT pets.id, ';
					$query_.='fias_addresses.cityguid, ';//cityguid
					$query_.='fias_addresses.streetguid, ';//streetguid
					$query_.='fias_addresses.houseguid, ';//houseguid
					$query_.='fias_addresses.roomguid ';//roomguid
					$query_.='FROM pets ';
					$query_.='LEFT JOIN fias_addresses ON fias_addresses.id=pets.id_fias_address ';
					$query_.='WHERE ';
					$query_.='fias_addresses.roomguid = \''.$row['roomguid'].'\' AND pets.id <> '.$id_main.'';
					$query_.=' AND pets.name = \''.string_formating_for_sql($name_main).'\' AND pets.id_species = \''.$specie_main.'\'';
					//echo $query;
					$result_ = pg_query($query_) or die('Ошибка запроса: ' . pg_last_error());
					//запускаем цикл по сущностям
					$ids='';
					$idsArray = array();
					while ($row_ = pg_fetch_assoc($result_)) {
						if($ids){$ids.=',';}
						echo "*".$row['id']."---".$row_['id']."<br />";
						$ids.=$row_['id'];
						array_push($idsArray, $row_['id']);

						//ищем еще его родителя с дочками
						if($row_['id_main_pet']){
							$query__='SELECT pets.id, id_main_pet FROM pets WHERE id='.$row_['id_main_pet'].' OR id_main_pet='.$row_['id_main_pet'].' ';
							$result__ = pg_query($query__) or die('Ошибка запроса: ' . pg_last_error());
							while ($row__ = pg_fetch_assoc($result__)) {
								if($ids){$ids.=',';}
								$ids.='*'.$row__['id'].'*';
								array_push($idsArray, $row__['id']);
							}
							pg_free_result($result__);
						}
						// id_main_owner
						//ищем еще его родителя с дочками
					}
					pg_free_result($result_);
					##############

					if($ids){
						//
						#############
						//его надо сделать главным
						//и взять его дочек
						if($id_main_pet){
							$query__='SELECT pets.id, id_main_pet FROM pets WHERE id='.$id_main.' OR id_main_pet='.$id_main.' ';
							$result__ = pg_query($query__) or die('Ошибка запроса: ' . pg_last_error());
							while ($row__ = pg_fetch_assoc($result__)) {
								if($row__['id'] != $id_main){
									if($ids){$ids.=',';}
									$ids.=$row__['id'];
									array_push($idsArray, $row__['id']);
								}
							}
							pg_free_result($result__);
						}
						//
						$idsArray = array_unique($idsArray);
						$ids_='';
						for($i=0;$i<=count($idsArray);$i++){
							if($idsArray[$i] && $id_main != $idsArray[$i]){
								if($ids_){$ids_.=',';}
								$ids_.=''.$idsArray[$i].'';
							}
						}
						if($id_main_pet){
							$id_main = $id_main_pet;
						}
						//

						if($id_main != $ids_){
							$query_save='INSERT INTO duplicates (';
							$query_save.='type, ';
							$query_save.='id_main, ';
							$query_save.='temp, ';
							$query_save.='ids, ';
							$query_save.='created_at) ';
							$query_save.='VALUES (';
							$query_save.="2,";
							$query_save.="".$id_main.",";
							$query_save.="false,";
							$query_save.="'{".$id_main.','.$ids_."}',";
							$query_save.="NOW()::timestamp(0)";
							$query_save.=') RETURNING id;';
							$result_save = pg_query($query_save) or die('Ошибка запроса: ' . pg_last_error());
							//echo $query_save;
							pg_free_result($result_save);

							//чистим дубликаты
							$query_delete='DELETE FROM duplicates WHERE type=4';
							$result_delete = pg_query($query_delete) or die('Ошибка запроса: ' . pg_last_error());
							pg_free_result($result_delete);

							//добавляем маркер
							$query_save='INSERT INTO duplicates (';
							$query_save.='type, ';
							$query_save.='id_main, ';
							$query_save.='temp, ';
							$query_save.='created_at) ';
							$query_save.='VALUES (';
							$query_save.="4,";
							$query_save.="".$id_main.",";
							$query_save.="false,";
							$query_save.="NOW()::timestamp(0)";
							$query_save.=') RETURNING id;';
							$result_save = pg_query($query_save) or die('Ошибка запроса: ' . pg_last_error());
							pg_free_result($result_save);
						}
					}
				}
			}
			pg_free_result($result);
		}

		//запускаем цикл по сущностям
			//проверяем есть ли он в дублях
			//select * from duplicates WHERE ids @> '{444}'::int[]; //1/2

			//нет-->
				//сравниваем по fias со всей таблицей (цикл)->
				//cityguid//streetguid//houseguid//roomguid

					//проверяем есть ли каждый в таблице дублей
					//select * from duplicates WHERE ids @> '{444}'::int[];

					//нет+
					//копим в массив

					//да-
					//ничего не делаем
				//<-

				//складываем накопленное в таблицу
				//insert into duplicates(id_main, type, ids) values(ID_REC, 1, '{222, 333, 444, 555}');
			//да-->
			//отбой

			//сохраняем ID_LAST_REC
			//записываем id последней проверенной записи
			//insert into duplicates(id_main, type, ids) values(ID_LAST_REC, 3/4, NULL);

		//<-конец цикла

		//ЧИСТИМ 3/4

		//финал
	}

	function duplicates_show_rec_xml($config){
		$id=$_GET["id"];

		header("Content-type: text/xml; charset=utf-8");
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<xml>';
		echo '<rec>';

		$query='SELECT id, type, id_main, array_to_string(ids, \',\', \'*\') AS ids ';
		$query.='FROM duplicates ';
		$query.='WHERE duplicates.id='.string_formating_for_sql($id).' ';
		$result = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
		$row = pg_fetch_assoc($result);

		echo '<rec_type>'.$row['type'].'</rec_type>';

		$idsArray = explode(",", $row['ids']);

		if($row['type'] == 1){
			echo '<rec_owners>';

			$query="SELECT agreements.id AS accept, MAX((LOWER(VISITS.TIME_RANGE)::date)) AS last_visit, MAX(elk.owners.sso_id) AS sso_id, COUNT(DISTINCT pets.id) AS pets_count, COUNT(DISTINCT visits.id) AS visits_count, ";
			$query.="Ad.full_address AS address, Fad.full_address AS factadd, pet_owners.birthday, C.name AS telephone, E.name AS email, ";
			$query.="pet_owners.* FROM pet_owners ";
			$query.="LEFT JOIN pets_to_owner ON pet_owners.id=pets_to_owner.id_owner ";
			$query.="LEFT JOIN pets ON pets_to_owner.id_pet=pets.id AND pets.id_pet_tmp IS NULL AND pets.reg_expire_date IS NULL AND pets.id_main_pet IS NULL ";

			$query.="LEFT JOIN visit_pets ON visit_pets.id_pet=pets.id ";
			$query.="LEFT JOIN visits ON (visits.id=visit_pets.id_visit AND visits.status='F')";

			$query.="LEFT JOIN agreements ON agreements.id_pet_owner=pet_owners.id AND agreements.is_agree=true AND agreements.id_type=1 ";

			$query.="LEFT JOIN elk.owners ON elk.owners.id_owner=pet_owners.id ";

			$query.='LEFT JOIN fias_addresses Ad ON Ad.id=pet_owners.id_fias_address ';
			$query.='LEFT JOIN fias_addresses Fad ON Fad.id=pet_owners.id_fact_fias_address ';

			$query.='LEFT JOIN contacts C ON C.entity_id=pet_owners.id AND C.main_flag=true AND C.id_contact_type=1 ';
			// 6	"Электронная почта"
			// 5	"Факс"
			// 3	"Домашний телефон"
			// 2	"Рабочий телефон"
			// 1	"Мобильный телефон"

			$query.='LEFT JOIN contacts E ON E.entity_id=pet_owners.id AND E.main_flag=true AND E.id_contact_type=6 ';

			$query.="WHERE (";
			$k=0;
			for ($j=0; $j<count($idsArray); $j++) {
				if($idsArray[$j] != ''){
					if($k>0){$query.=' OR ';}
					$query.='pet_owners.id = '.string_formating_for_sql($idsArray[$j]).' ';
					$k++;
				}
			}
			$query.=") ";

			$query.="GROUP BY pet_owners.id, Ad.full_address, Fad.full_address, C.name, E.name, agreements.id ";

			$query.="ORDER BY (";
			for ($j=0; $j<count($idsArray); $j++) {
				if($j > 0){$query.=",";}
				$query.="pet_owners.id='".$idsArray[$j]."'";
			}
			$query.=") DESC";
			//echo $query;

			$result_o = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());
			while ($row_o = pg_fetch_assoc($result_o)) {
				$fields = 0;
				echo '<owner>';
				echo '<id>'.$row_o['id'].'</id>';
				echo '<fullname>'.$row_o['fullname'].'</fullname>';

				if($row_o['accept']){
					echo '<accept>Да</accept>';
				}else{
					echo '<accept>Нет</accept>';
				}

				if($row_o['fullname']){$fields++;}

				if($row_o['id'] == $row['id_main']){
					echo '<main>1</main>';
				}else{
					echo '<main>0</main>';
				}

				if($row_o['birthday']){
					echo '<birthday>'.date_format(new \DateTime($row_o['birthday']), "d.m.Y").'</birthday>';
					$fields++;
				}else{
					echo '<birthday></birthday>';
				}
				echo '<snils>'.$row_o['snils'].'</snils>';
				if($row_o['snils']){$fields++;}

				echo '<sso_id>'.$row_o['sso_id'].'</sso_id>';
				if($row_o['sso_id']){$fields++;}

				echo '<address>'.$row_o['address'].'</address>';
				if($row_o['address']){$fields++;}

				echo '<factadd>'.$row_o['factadd'].'</factadd>';
				if($row_o['factadd']){$fields++;}

				echo '<telephone>'.$row_o['telephone'].'</telephone>';
				if($row_o['telephone']){$fields++;}

				echo '<email>'.$row_o['email'].'</email>';
				if($row_o['email']){$fields++;}

				echo '<description>'.$row_o['description'].'</description>';
				if($row_o['description']){$fields++;}

				echo '<pets_count>'.$row_o['pets_count'].'</pets_count>';
				if($row_o['last_visit']){
					echo '<last_visit>'.date_format(new \DateTime($row_o['last_visit']), "d.m.Y").'</last_visit>';
					$fields++;
				}else{
					echo '<last_visit></last_visit>';
				}
				echo '<visits_count>'.$row_o['visits_count'].'</visits_count>';
				if($row_o['visits_count'] > 0){$fields++;}

				$percent = ceil(($fields/(11/100)));
				echo '<percent>'.$percent.'</percent>';

				echo '</owner>';
			}
			pg_free_result($result_o);

			echo '</rec_owners>';
		}
		if($row['type'] == 2){
			$query='SELECT ';

			$query.='public.pet_owners.fullname, ';
			$query.='Ad.full_address AS address, Fad.full_address AS fact_address, ';

			$query.='MAX(pet_rabies_vaccination.valid_until) AS vac_r_date, ';
			$query.='MAX(pet_other_vaccinations.valid_until) AS vac_o_date, ';
			$query.='MAX(pet_ectoparasites.valid_until) AS ecto_date, ';

			$query.='reg_certificates.number AS reg_certificate, ';

			$query.='COUNT(DISTINCT pet_rabies_vaccination.id) AS vac_r_count, ';
			$query.='COUNT(DISTINCT pet_other_vaccinations.id) AS vac_o_count, ';
			$query.='COUNT(DISTINCT pet_ectoparasites.id) AS ecto_count, ';

			$query.='MAX((LOWER(VISITS.TIME_RANGE)::date)) AS last_visit, COUNT(visits.id) AS visits_count, public.pets.*,breeds.name AS breed_name,species.name AS species_name, MAX(elk.pets.ext_id) AS ext_id, ';
			$query.='chip.identification_code AS chip, ';
			$query.='label.identification_code AS label ';
			$query.='FROM public.pets ';
			$query.='LEFT JOIN visit_pets ON visit_pets.id_pet=public.pets.id ';
			$query.="LEFT JOIN visits ON visits.id=visit_pets.id_visit AND visits.status = 'F' ";
			$query.='LEFT JOIN breeds ON public.pets.id_breed = breeds.id ';
            $query.='LEFT JOIN species ON public.pets.id_species = species.id ';
			$query.="LEFT JOIN elk.pets ON elk.pets.id_pet=public.pets.id ";

			$query.="LEFT JOIN pet_rabies_vaccination ON pet_rabies_vaccination.id_pet=public.pets.id ";
			$query.="LEFT JOIN pet_other_vaccinations ON pet_other_vaccinations.id_pet=public.pets.id ";
			$query.="LEFT JOIN pet_ectoparasites ON pet_ectoparasites.id_pet=public.pets.id ";
			$query.="LEFT JOIN reg_certificates ON reg_certificates.id_pet=public.pets.id ";

			$query.='LEFT JOIN public.pets_to_owner ON public.pets.id=public.pets_to_owner.id_pet ';
			$query.='LEFT JOIN public.pet_owners ON public.pets_to_owner.id_owner=public.pet_owners.id ';
			// $query.='LEFT JOIN public.fias_addresses ON public.fias_addresses.id=pet_owners.id_fact_fias_address ';
			$query.='LEFT JOIN fias_addresses Ad ON Ad.id=pet_owners.id_fias_address ';
			$query.='LEFT JOIN fias_addresses Fad ON Fad.id=pet_owners.id_fact_fias_address ';

			$query.='LEFT JOIN public.pet_identification AS chip ON chip.id_pet=public.pets.id AND chip.id = (SELECT max(c1.id) FROM public.pet_identification c1 WHERE chip.id_pet = c1.id_pet AND c1.id_ident_type=1) ';
			$query.='LEFT JOIN public.pet_identification AS label ON label.id_pet=public.pets.id AND label.id = (SELECT max(l1.id) FROM public.pet_identification l1 WHERE label.id_pet = l1.id_pet AND l1.id_ident_type=2) ';

			$query.="WHERE (";
			$k=0;
			for ($j=0; $j<count($idsArray); $j++) {
				if($idsArray[$j] != ''){
					if($k>0){$query.=' OR ';}
					$query.='public.pets.id = '.string_formating_for_sql($idsArray[$j]).' ';
					$k++;
				}
			}
			$query.=") ";

			$query.="GROUP BY public.pets.id, breeds.name,species.name, chip.identification_code, label.identification_code, public.pet_owners.fullname, Ad.full_address, Fad.full_address, reg_certificates.number ";

			$query.="ORDER BY (";
			for ($j=0; $j<count($idsArray); $j++) {
				if($j > 0){$query.=",";}
				$query.="public.pets.id='".$idsArray[$j]."'";
			}
			$query.=") DESC";
			//echo $query;

			echo '<rec_pets>';
			$result_p = pg_query($query) or die('Ошибка запроса: ' . pg_last_error());

			while ($row_p = pg_fetch_assoc($result_p)) {
				$fields = 0;

				echo '<pet>';
				echo '<id>'.$row_p['id'].'</id>';

				echo '<name>'.$row_p['name'].'</name>';
				if($row_p['name']){$fields++;}

				echo '<fullname>'.$row_p['fullname'].'</fullname>';
				if($row_p['fullname']){$fields++;}

				if($row_p['fact_address']){
					echo '<factadd>'.$row_p['fact_address'].'</factadd>';
				}else{
					echo '<factadd>'.$row_p['address'].'</factadd>';
				}
				if($row_p['fact_address'] || $row_p['address']){$fields++;}

				echo '<reg_certificate>'.$row_p['reg_certificate'].'</reg_certificate>';
				if($row_p['reg_certificate']){$fields++;}


				echo '<chip>'.$row_p['chip'].'</chip>';
				if($row_p['chip']){$fields++;}
				echo '<label>'.$row_p['label'].'</label>';
				if($row_p['label']){$fields++;}

				echo '<specie>'.$row_p['species_name'].'</specie>';
				if($row_p['species_name']){$fields++;}

				echo '<breed>'.$row_p['breed_name'].'</breed>';
				if($row_p['breed_name']){$fields++;}

				if($row_p['sex'] == 'f'){
					echo '<sex>женский</sex>';
				}else if($row_p['sex'] == 'm'){
					echo '<sex>мужской</sex>';
				}

				if($row_p['sex']){$fields++;}

				echo '<ext_id>'.$row_p['ext_id'].'</ext_id>';
				if($row_p['ext_id']){$fields++;}

				if($row_p['id'] == $row['id_main']){
					echo '<main>1</main>';
				}else{
					echo '<main>0</main>';
				}

				echo '<description>'.$row_p['description'].'</description>';
				if($row_p['description']){$fields++;}

				if($row_p['birthday']){
					$fields++;
					echo '<birthday>'.date_format(new \DateTime($row_p['birthday']), "d.m.Y").'</birthday>';
				}else{
					echo '<birthday></birthday>';
				}
				echo '<age>'.calculate_age($row_p['birthday']).'</age>';

				if($row_p['last_visit']){
					echo '<last_visit>'.date_format(new \DateTime($row_p['last_visit']), "d.m.Y").'</last_visit>';
					$fields++;
				}else{
					echo '<last_visit></last_visit>';
				}
				echo '<visits_count>'.$row_p['visits_count'].'</visits_count>';
				if($row_p['visits_count'] > 0){$fields++;}


				//
				echo '<vac_r_count>'.$row_p['vac_r_count'].'</vac_r_count>';
				if($row_p['vac_r_count'] > 0){$fields++;}
				if($row_p['vac_r_date']){
					echo '<vac_r_date>'.date_format(new \DateTime($row_p['vac_r_date']), "d.m.Y").'</vac_r_date>';
				}else{
					echo '<vac_r_date></vac_r_date>';
				}

				echo '<vac_o_count>'.$row_p['vac_o_count'].'</vac_o_count>';
				if($row_p['vac_o_count'] > 0){$fields++;}
				if($row_p['vac_o_date']){
					echo '<vac_o_date>'.date_format(new \DateTime($row_p['vac_o_date']), "d.m.Y").'</vac_o_date>';
				}else{
					echo '<vac_o_date></vac_o_date>';
				}

				echo '<ecto_count>'.$row_p['ecto_count'].'</ecto_count>';
				if($row_p['ecto_count'] > 0){$fields++;}
				if($row_p['ecto_date']){
					echo '<ecto_date>'.date_format(new \DateTime($row_p['ecto_date']), "d.m.Y").'</ecto_date>';
				}else{
					echo '<ecto_date></ecto_date>';
				}
				//


				$percent = ceil(($fields/(17/100)));
				echo '<percent>'.$percent.'</percent>';

				echo '</pet>';
			}
			pg_free_result($result_p);
			echo '</rec_pets>';
		}

		pg_free_result($result);

		echo '</rec>';
		echo '</xml>';
	}

	function viewDuplicate($config){
		$id=$_GET["id"];

		//
		echo '<div class="row main_row search">';

		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		echo '<div class="col-xl-8 col-lg-10">';
		// echo '<form id="form_search_recs" onsubmit="listShowDuplicates(); return false;">';
		echo '<div class="row search_sub">';

		echo '<div class="col-12 main_row title" id="title"></div>';

		echo '<div class="col-12 main_row"><hr /></div>';

		echo '<div class="col-12 main_row search_tabs">';
		echo '<div id="fields_1" class="active" onclick="showDuplicateFields(1);">Все параметры</div>';
		echo '<div id="fields_2" style="margin-left: 30px;" onclick="showDuplicateFields(2);">Только различия</div>';
		echo '<div id="fields_3" style="margin-left: 30px;" onclick="showDuplicateFields(3);">Основные</div>';


		echo '<div class="button add_compare" style="margin-left: 30px;" onclick="showDuplicatesAddDuplicateWindow();">Добавить к сравнению</div>';

		echo '<div class="buttons">';
		echo '<button class="button" style="margin-left: 30px; width: 250px;" value="1" onclick="mergeDuplicatesAccept('.$id.');">Объединить с основным</button>';
		echo '</div>';

		echo '</div>';

		echo '</div>';

		echo '<input type="hidden" name="duplicate" id="duplicate" value="'.$id.'">';
		// echo '</form>';
		echo '</div>';
		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';


		echo '</div>';

		echo '</div>';
		//

		echo '<div class="row main_row" style="margin-top: 15px;">';
		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		//

		echo '<div class="col-xl-8 col-lg-10 main_row">';
		echo '<div class="main_row row duplicates" id="Duplicates"></div>';
		echo '</div>';

		//
		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		echo '</div>';

		echo '<div class="duplicates arrow_left" id="arrow_left" onclick="showDuplicatesArrow(\'left\');"></div>';
		echo '<div class="duplicates arrow_right" id="arrow_right" onclick="showDuplicatesArrow(\'right\');"></div>';

$html = <<<HTML

		<script type="text/javascript">

		$(document).ready(function () {
			showDuplicate($id);
			
			//$('#specie').multiselect({enableClickableOptGroups: true, maxHeight: 300, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '100%', filterPlaceholder: 'Выбрать вид животного...'});
			// $('#breed').multiselect({enableClickableOptGroups: true, maxHeight: 300, enableFiltering: true, enableCaseInsensitiveFiltering : true, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '100%', filterPlaceholder: 'Выбрать породу животного...'});
			// $('#sex').multiselect({enableClickableOptGroups: true, maxHeight: 300, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '100%', filterPlaceholder: 'Выбрать пол животного...'});
			// $('#color').multiselect({enableClickableOptGroups: true, maxHeight: 300, enableFiltering: true, enableCaseInsensitiveFiltering : true, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '100%', filterPlaceholder: 'Выбрать окрас животного...'});
			// $('#wool').multiselect({enableClickableOptGroups: true, maxHeight: 300, enableFiltering: true, enableCaseInsensitiveFiltering : true, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '100%', filterPlaceholder: 'Выбрать тип шерсти животного...'});
			// $('#size').multiselect({enableClickableOptGroups: true, maxHeight: 300, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '100%', filterPlaceholder: 'Выбрать размер животного...'});
			// $('#tail').multiselect({enableClickableOptGroups: true, maxHeight: 300, enableFiltering: true, enableCaseInsensitiveFiltering : true, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '100%', filterPlaceholder: 'Выбрать тип ушей животного...'});
			// $('#ear').multiselect({enableClickableOptGroups: true, maxHeight: 300, enableFiltering: true, enableCaseInsensitiveFiltering : true, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '100%', filterPlaceholder: 'Выбрать тип хвоста животного...'});


			// var options ={
			// 	name: 'skill',
			// 	mode: 'normal',
			// 	new_tags: 0,
			// 	request_min: 2,
			// 	request_title: 'title',
			// 	response_id: 'rec_id',
			// 	response_title: 'rec_title',
			// 	width: '100%',
			// 	height: 250,
			// 	max_tags_win: 10,
			// 	url: "?action=skills&mode=xml"
			// };
			// skill_input = new JxTag(options);
			
			// $('.chip').mask("999999999999999", {placeholder: "_______________"});
			// $('.birthday').mask("AB.CD99",
			// 	{
			// 		translation: {
			// 			'A': {
			// 				pattern: /[0-1]/, optional: false
			// 			},
			// 			'B': {
			// 				pattern: /[0-9]/, optional: false
			// 			},
			// 			'C': {
			// 				pattern: /[1-2]/, optional: false
			// 			},
			// 			'D': {
			// 				pattern: /[9|0]/, optional: false
			// 			}
			// 		},
			// 		placeholder: "__.____"
			// 	}
			// );
		});
HTML;

		$html.='</script>';

		echo $html;
	}

	function viewDuplicates($config){
		$id=$_GET["id"];

		//Поиск
		echo '<div class="row main_row search">';

		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
		echo '<div class="col-xl-8 col-lg-10">';
		echo '<form id="form_search_recs" onsubmit="listShowDuplicates(); return false;">';
		echo '<div class="row search_sub">';

		echo '<div class="col-12 main_row title">Поиск</div>';

		echo '<div class="d-inline-flex row" id="owners_field1">';
		echo '<div class="label col-12" style="width: 50px;">Владелец</div>';
		echo '<div class="input col-12">';echo '<input type="text" name="fullname" style="width: 300px;">';echo '</div>';
		echo '</div>';

		echo '<div class="d-inline-flex row" id="owners_field2">';
		echo '<div class="label col-12" style="width: 50px;">Телефон</div>';
		echo '<div class="input col-12">';echo '<input type="text" name="telephone">';echo '</div>';
		echo '</div>';


		echo '<div class="d-inline-flex row hidden" id="pets_field1">';
		echo '<div class="label col-12" style="width: 50px;">Вид</div>';
		echo '<div class="input col-12">';
		echo '<select name="specie" id="specie" style="width: 300px;">';
		$species_array = array();
		array_push($species_array, array('id' => '','title' => 'Все'));
		array_push($species_array, array('id' => '25','title' => 'Собаки'));
		array_push($species_array, array('id' => '9','title' => 'Кошки'));
		for ($i = 0; $i < count($species_array); $i++) {
            echo '<option value="'.$species_array[$i]['id'].'">'.$species_array[$i]['title'].'</option>';
	    }
		echo '</select>';

		echo '</div>';
		echo '</div>';

		echo '<div class="d-inline-flex row hidden" id="pets_field2">';
		echo '<div class="label col-12" style="width: 50px;">Кличка</div>';
		echo '<div class="input col-12">';echo '<input type="text" name="name">';echo '</div>';
		echo '</div>';


		echo '<div class="d-inline-flex buttons">';
		echo '<button tabindex="1" type="submit" style="width: 120px;" value="1" name="submit" id="submit">Поиск</button>';
		echo '</div>';


		echo '<div class="col-12 main_row"><hr /></div>';

		echo '<div class="col-12 main_row search_tabs">';
		echo '<div class="active" onclick="showDuplicatesTab(1);" id="tab_1">По владельцу</div>';
		echo '<div style="margin-left: 30px;" onclick="showDuplicatesTab(2);" id="tab_2">По животному</div>';


		echo '<div class="button compare" style="margin-left: 30px;" onclick="showDuplicatesAddRecWindow();">Сравнить по ID</div>';
		echo '</div>';

		echo '</div>';

		echo '<input type="hidden" name="tab" id="tab" value="1">';
		echo '<input type="hidden" name="mode" value="xml">';

		echo '</form>';
		echo '</div>';
		echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';


		echo '</div>';

		echo '</div>';
		//Поиск

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
			listShowDuplicates();
			$('#specie').multiselect({enableClickableOptGroups: true, maxHeight: 300, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '300', filterPlaceholder: 'Выбрать вид животного...'});
		});
HTML;

		$html.='</script>';

		echo $html;
	}

function viewAutoDuplicates($config){
    //Заготовка под поиск
    //Поиск
    echo '<div style="display: none" class="row main_row search">';
    echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
    echo '<div class="col-xl-8 col-lg-10">';
    echo '<form id="form_search_auto_recs" onsubmit="listShowAutoDuplicates(); return false;">';
    echo '<div class="row search_sub">';
    echo '<div class="col-12 main_row title">Поиск</div>';

    //владельцы
//    echo '<div class="d-inline-flex row" id="owners_field3">';
//    echo '<div class="label col-2" style="width: 50px;">Дата склейки';
//    echo '</div>';
//    echo '<div class="input col-3">';
//    echo '<div class="label col-1" style="width: 50px;">c';
//    echo '</div>';
//    echo '<input type="date" name="join_owner_date_from" style="width: 300px;">';
//    echo '<div class="label col-1" style="width: 50px; margin-right: 10px">по';
//    echo '</div>';
//    echo '<input type="date" name="join_owner_date_to" style="width: 300px;">';
//    echo '</div>';
//    echo '</div>';

    echo '<div class="d-inline-flex row" id="owners_field4">';
    echo '<div class="label col-12" style="width: 50px;">ID</div>';
    echo '<div class="input col-12">';echo '<input type="text" name="id_auto_duplicate_owner" style="width: 300px;">';echo '</div>';
    echo '</div>';

    echo '<div class="d-inline-flex row" id="owners_field5">';
    echo '<div class="label col-12" style="width: 50px;">ФИО владельца</div>';
    echo '<div class="input col-12">';echo '<input type="text" name="fullname" style="width: 300px;">';echo '</div>';
    echo '</div>';

    echo '<div class="d-inline-flex row" id="owners_field6">';
    echo '<div class="label col-12" style="width: 50px;">Телефон владельца</div>';
    echo '<div class="input col-12">';echo '<input type="text" name="telephone">';echo '</div>';
    echo '</div>';

    //животные
//    echo '<div class="d-inline-flex row hidden" id="pets_field3">';
//    echo '<div class="label col-2" style="width: 50px;">Дата склейки';
//    echo '</div>';
//    echo '<div class="input col-3">';
//    echo '<div class="label col-1" style="width: 50px;">c';
//    echo '</div>';
//    echo '<input type="date" name="join_pet_date_from" style="width: 300px;">';
//    echo '<div class="label col-1" style="width: 50px; margin-right: 10px">по';
//    echo '</div>';
//    echo '<input type="date" name="join_pet_date_to" style="width: 300px;">';
//    echo '</div>';
//    echo '</div>';

    echo '<div class="d-inline-flex row hidden" id="pets_field4">';
    echo '<div class="label col-12" style="width: 50px;">ID</div>';
    echo '<div class="input col-12">';echo '<input type="text" name="id_auto_duplicate_pet" style="width: 300px;">';echo '</div>';
    echo '</div>';

    echo '<div class="d-inline-flex row hidden" id="pets_field4">';
    echo '<div class="label col-12" style="width: 50px;">Кличка</div>';
    echo '<div class="input col-12">';echo '<input type="text" name="name">';echo '</div>';
    echo '</div>';

    echo '<div class="d-inline-flex row hidden" id="pets_field5">';
    echo '<div class="label col-12" style="width: 50px;">Вид</div>';
    echo '<div class="input col-12">';
    echo '<select name="specie" id="specie" style="width: 300px;">';
    $species_array = array();
    array_push($species_array, array('id' => '','title' => 'Все'));
    array_push($species_array, array('id' => '25','title' => 'Собаки'));
    array_push($species_array, array('id' => '9','title' => 'Кошки'));
    for ($i = 0; $i < count($species_array); $i++) {
        echo '<option value="'.$species_array[$i]['id'].'">'.$species_array[$i]['title'].'</option>';
    }
    echo '</select>';
    echo '</div>';
    echo '</div>';

    echo '<div class="d-inline-flex row hidden" id="pets_field6">';
    echo '<div class="label col-12" style="width: 50px;">Порода</div>';
    echo '<div class="input col-12">';
    echo '<select name="specie" id="specie" style="width: 300px;">';
    $species_array = array();
    array_push($species_array, array('id' => '','title' => 'Все'));
    array_push($species_array, array('id' => '25','title' => 'Собаки'));
    array_push($species_array, array('id' => '9','title' => 'Кошки'));
    for ($i = 0; $i < count($species_array); $i++) {
        echo '<option value="'.$species_array[$i]['id'].'">'.$species_array[$i]['title'].'</option>';
    }
    echo '</select>';
    echo '</div>';
    echo '</div>';

    echo '<div class="d-inline-flex buttons">';
    echo '<button tabindex="1" type="submit" style="width: 120px;" value="1" name="submit" id="submit">Поиск</button>';
    echo '</div>';

    // Заготовка под Статистическая информация
//    echo '<div class="col-12 main_row"><hr /></div>';
//    echo '<div class="main_row col-12" style="width: 50px;">Общее количество обработанных записей: 00 </div>';
//    echo '<div class="main_row col-12" style="width: 50px;">Количество архивированных записей: 00 </div>';
//    echo '<div class="main_row col-12" style="width: 50px;">Количество созданных новых записей: 00 </div>';
//    echo '<div class="col-12 main_row"><hr /></div>';
    //Статистическая информация

    echo '<div class="col-12 main_row search_tabs">';
    echo '<div class="active" onclick="showAutoDuplicatesTab(1);" id="tab_1">По владельцу</div>';
//    echo '<div style="margin-left: 30px;" onclick="showAutoDuplicatesTab(2);" id="tab_2">По животному</div>';
    echo '</div>';
    echo '</div>';
    echo '<input type="hidden" name="tab" id="tab" value="1">';
    echo '<input type="hidden" name="mode" value="xml">';
    echo '</form>';
    echo '</div>';
    echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
    echo '</div>';
    echo '</div>';
    //Поиск

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
			listShowAutoDuplicates();
			$('#specie').multiselect({enableClickableOptGroups: true, maxHeight: 300, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '300', filterPlaceholder: 'Выбрать вид животного...'});
		});
HTML;

    $html.='</script>';

    echo $html;
}

function viewArchiveDuplicates($config){
// Заготовка под поиск
    //Поиск
    echo '<div style="display: none" class="row main_row search">';
    echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
    echo '<div class="col-xl-8 col-lg-10">';
    echo '<form id="form_search_archive_recs" onsubmit="listShowArchiveDuplicates(); return false;">';
    echo '<div class="row search_sub">';
    echo '<div class="col-12 main_row title">Поиск</div>';

//    владельцы
//    echo '<div class="d-inline-flex row" id="owners_field3">';
//    echo '<div class="label col-2" style="width: 50px;">Дата склейки';
//    echo '</div>';
//    echo '<div class="input col-3">';
//    echo '<div class="label col-1" style="width: 50px;">c';
//    echo '</div>';
//    echo '<input type="date" name="archive_join_owner_date_from" style="width: 300px;">';
//    echo '<div class="label col-1" style="width: 50px; margin-right: 10px">по';
//    echo '</div>';
//    echo '<input type="date" name="archive_join_owner_date_to" style="width: 300px;">';
//    echo '</div>';
//    echo '</div>';

    echo '<div class="d-inline-flex row" id="owners_field4">';
    echo '<div class="label col-12" style="width: 50px;">ID</div>';
    echo '<div class="input col-12">';echo '<input type="text" name="id_archive_duplicate_owner" style="width: 300px;">';echo '</div>';
    echo '</div>';

    echo '<div class="d-inline-flex row" id="owners_field5">';
    echo '<div class="label col-12" style="width: 50px;">ФИО владельца</div>';
    echo '<div class="input col-12">';echo '<input type="text" name="archive_duplicate_owner_fullname" style="width: 300px;">';echo '</div>';
    echo '</div>';

    echo '<div class="d-inline-flex row" id="owners_field6">';
    echo '<div class="label col-12" style="width: 50px;">Телефон владельца</div>';
    echo '<div class="input col-12">';echo '<input type="text" name="archive_duplicate_owner_telephone">';echo '</div>';
    echo '</div>';

    //животные
//    echo '<div class="d-inline-flex row hidden" id="pets_field3">';
//    echo '<div class="label col-2" style="width: 50px;">Дата склейки';
//    echo '</div>';
//    echo '<div class="input col-3">';
//    echo '<div class="label col-1" style="width: 50px;">c';
//    echo '</div>';
//    echo '<input type="date" name="archive_join_pet_date_from" style="width: 300px;">';
//    echo '<div class="label col-1" style="width: 50px; margin-right: 10px">по';
//    echo '</div>';
//    echo '<input type="date" name="archive_join_pet_date_to" style="width: 300px;">';
//    echo '</div>';
//    echo '</div>';

    echo '<div class="d-inline-flex row hidden" id="pets_field4">';
    echo '<div class="label col-12" style="width: 50px;">ID</div>';
    echo '<div class="input col-12">';echo '<input type="text" name="id_auto_duplicate_owner_pet" style="width: 300px;">';echo '</div>';
    echo '</div>';

    echo '<div class="d-inline-flex row hidden" id="pets_field4">';
    echo '<div class="label col-12" style="width: 50px;">Кличка</div>';
    echo '<div class="input col-12">';echo '<input type="text" name="name">';echo '</div>';
    echo '</div>';

    echo '<div class="d-inline-flex row hidden" id="pets_field5">';
    echo '<div class="label col-12" style="width: 50px;">Вид</div>';
    echo '<div class="input col-12">';
    echo '<select name="specie" id="specie" style="width: 300px;">';
    $species_array = array();
    array_push($species_array, array('id' => '','title' => 'Все'));
    array_push($species_array, array('id' => '25','title' => 'Собаки'));
    array_push($species_array, array('id' => '9','title' => 'Кошки'));
    for ($i = 0; $i < count($species_array); $i++) {
        echo '<option value="'.$species_array[$i]['id'].'">'.$species_array[$i]['title'].'</option>';
    }
    echo '</select>';
    echo '</div>';
    echo '</div>';

    echo '<div class="d-inline-flex row hidden" id="pets_field6">';
    echo '<div class="label col-12" style="width: 50px;">Порода</div>';
    echo '<div class="input col-12">';
    echo '<select name="specie" id="specie" style="width: 300px;">';
    $species_array = array();
    array_push($species_array, array('id' => '','title' => 'Все'));
    array_push($species_array, array('id' => '25','title' => 'Собаки'));
    array_push($species_array, array('id' => '9','title' => 'Кошки'));
    for ($i = 0; $i < count($species_array); $i++) {
        echo '<option value="'.$species_array[$i]['id'].'">'.$species_array[$i]['title'].'</option>';
    }
    echo '</select>';
    echo '</div>';
    echo '</div>';

    echo '<div class="d-inline-flex buttons">';
    echo '<button tabindex="1" type="submit" style="width: 120px;" value="1" name="submit" id="submit">Поиск</button>';
    echo '</div>';

    // Заготовка под Статистическая информация
//    echo '<div class="col-12 main_row"><hr /></div>';
//    echo '<div class="main_row col-12" style="width: 50px;">Общее количество обработанных записей: 00 </div>';
//    echo '<div class="main_row col-12" style="width: 50px;">Количество архивированных записей: 00 </div>';
//    echo '<div class="main_row col-12" style="width: 50px;">Количество созданных новых записей: 00 </div>';
//    echo '<div class="col-12 main_row"><hr /></div>';
    //Статистическая информация

    echo '<div class="col-12 main_row search_tabs">';
    echo '<div class="active" onclick="showArchiveDuplicatesTab(1);" id="tab_1">По владельцу</div>';
//    echo '<div style="margin-left: 30px;" onclick="showArchiveDuplicatesTab(2);" id="tab_2">По животному</div>';
    echo '</div>';
    echo '</div>';
    echo '<input type="hidden" name="tab" id="tab" value="1">';
    echo '<input type="hidden" name="mode" value="xml">';
    echo '</form>';
    echo '</div>';
    echo '<div class="col-xl-2 col-lg-1 d-none d-sm-none d-md-none d-lg-block d-xl-block"></div>';
    echo '</div>';
    echo '</div>';
    //Поиск

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
			listShowArchiveDuplicates();
			$('#specie').multiselect({enableClickableOptGroups: true, maxHeight: 300, nonSelectedText: 'Выберите...', allSelectedText: "Выбраны все", nSelectedText  : "выбрано", buttonWidth: '300', filterPlaceholder: 'Выбрать вид животного...'});
		});
HTML;

    $html.='</script>';

    echo $html;
}

	if($action == 'auth'){
		authUser($configuration);
	}else if($action == 'exit'){
		exitUser($configuration);
	}else if($action == 'merge_duplicates'){
		if(vallidateToken($configuration)){duplicates_merge_duplicates_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'create_rec_auto'){
		duplicates_create_rec_auto_xml($configuration);
	}else if($action == 'delete_duplicate'){
		if(vallidateToken($configuration)){duplicates_delete_duplicate_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'add_duplicate'){
		if(vallidateToken($configuration)){duplicates_add_duplicate_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'create_rec'){
		if(vallidateToken($configuration)){duplicates_create_rec_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'addresses' && $mode == 'xml'){
		if(vallidateToken($configuration)){show_addresses_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'auto_recs' && $mode == 'xml'){
		if(vallidateToken($configuration)){duplicates_show_auto_recs_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'auto_rec' && $mode == 'xml'){
		if(vallidateToken($configuration)){duplicates_show_auto_rec_xml($configuration);}else{invalidToken($configuration);}
	} else if($action == 'archive_recs' && $mode == 'xml'){
        if(vallidateToken($configuration)){duplicates_show_archive_recs_xml($configuration);}else{invalidToken($configuration);}
    }else if($action == 'archive_rec' && $mode == 'xml'){
        if(vallidateToken($configuration)){duplicates_show_archive_rec_xml($configuration);}else{invalidToken($configuration);}
    }else if($action == 'recs' && $mode == 'xml'){
        if(vallidateToken($configuration)){duplicates_show_recs_xml($configuration);}else{invalidToken($configuration);}
    }else if($action == 'rec' && $mode == 'xml'){
        if(vallidateToken($configuration)){duplicates_show_rec_xml($configuration);}else{invalidToken($configuration);}
    }else if($action == 'species'){
		if(vallidateToken($configuration)){show_species_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'breeds'){
		if(vallidateToken($configuration)){show_breeds_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'notification_read'){
    	if(vallidateToken($configuration)){read_notification_xml($configuration);}else{invalidToken($configuration);}
	}else if($action == 'notifications'){
    	if(vallidateToken($configuration)){show_notifications_xml($configuration);}else{invalidToken($configuration);}
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
	}else if($action == 'recovery_password') {
		header_site(1, $configuration);
		viewRecoveryPassword('duplicates');
		footer_site();
	}else{
		if($_COOKIE['token']){
			if(vallidateToken($configuration)){
				header_site(0,$configuration, 'Дубликаты','duplicates');
				sub_header_site($configuration);
				menu_site($configuration, 'duplicates', $action);
				if(userCan($configuration, 'sysAdminGos') || userCan($configuration, 'managementGos') || userCan($configuration, 'vetSpecGos')){
					informings_site($configuration);

					if($action == 'edit'){
						viewDuplicate($configuration);
					}else if($action == 'autoduplicate'){
                        viewAutoDuplicates($configuration);
                    }else if($action == 'duplicate_archive') {
                        viewArchiveDuplicates($configuration);
                    }else{
						viewDuplicates($configuration);
					}
					sub_footer_site();


					duplicates_recs_by_time();//чистим временные записи
				}else{
					viewAttention($configuration);
				}
				footer_site();
			}else{
				header_site(1,$configuration);
				viewAuthUser('duplicates');
				footer_site();
			}
		}else{
			header_site(1,$configuration);
			viewAuthUser('duplicates');
			footer_site();
		}
	}

	if (function_exists('pg_connect')) {
		pg_close($dbconn);
	}
?>