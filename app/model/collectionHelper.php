<?php
class collectionHelper extends Database {

	/* generate reference query */

	var $user = null;
	var $salt;
	function __construct()
	{

		$session = new Session;
		$getSessi = $session->get_session();
		$this->user = $getSessi['login'];
		$this->loadmodule();
		$this->salt = '12345678PnD';
	}

	function loadmodule()
    {
        include APP_MODELS.'activityHelper.php';
    	// pr(APP_MODELS);
    	// pr($GLOBALS);
        // $this->helper_model = new helper_model;
       $this->activityHelper = new activityHelper;
    }

	function insertReference($newData=array(),$priority=array())
	{
		if (empty($newData)) return false;
		
		$ignore = array('img');
		$query = false;
		
		$sequence = $this->secqInsert($newData['query'],$priority, $ignore);
		// pr($sequence);exit;
		if ($sequence){
			
			$query = $this->runQuery($sequence);
			logFile('query references success =>'.serialize($sequence));
		}
		return $query;
	}
	
	/* generate master query */
	function insertMaster($newData=array(),$priority=array())
	{
		if (empty($newData)) return false;
		
		$ignore = array('img');
		$query = false;
		
		$param['indiv']['convertkey'] = array('locnID');
		$param['indiv']['table'] = array('locn');
		$param['indiv']['field'] = array('id');
		$param['indiv']['condition'] = array('short_namecode');
		
		$param['det']['convertkey'] = array('personID','taxonID');
		$param['det']['table'] = array('person','taxon');
		$param['det']['field'] =  array('id','id');
		$param['det']['condition'] =  array('short_namecode','short_namecode');
		
		$param['obs']['convertkey'] = array('personID');
		$param['obs']['table'] = array('person');
		$param['obs']['field'] =  array('id');
		$param['obs']['condition'] =  array('short_namecode');
		
		$param['coll']['convertkey'] = array('personID');
		$param['coll']['table'] = array('person');
		$param['coll']['field'] =  array('id');
		$param['coll']['condition'] =  array('short_namecode');
		
		$param['img']['convertkey'] = array('personID');
		$param['img']['table'] = array('person');
		$param['img']['field'] =  array('id');
		$param['img']['condition'] =  array('short_namecode');
		
		// $param['collector']['convertkey'] = array('personID','collID');
		// $param['collector']['table'] = array('person','coll');
		// $param['collector']['field'] =  array('id','id');
		// $param['collector']['condition'] =  array('short_namecode','indivID');
		
		$data['indiv'] = $this->parseRef($newData['rawdata'], 'indiv', $param['indiv']);
		$data['det'] = $this->parseRef($newData['rawdata'], 'det', $param['det']);
		$data['obs'] = $this->parseRef($newData['rawdata'], 'obs', $param['obs']);
		$data['coll'] = $this->parseRef($newData['rawdata'], 'coll', $param['coll']);
		$data['img'] = $this->parseRef($newData['rawdata'], 'img', $param['img']);
		// $data['collector'] = $this->parseRef($newData['rawdata'], 'collector', $param['collector']);
		
		$sequence = $this->secqInsert($data,$priority, $ignore);
		// pr($sequence);exit;
		
		if ($sequence){
			$query = $this->runQuery($sequence);
			logFile('query master success =>'.serialize($sequence));
		}
		return $query;
	}
	
	
	function weakMasterData($newData=array(),$priority=array())
	{
		if (empty($newData)) return false;
		
		$ignore = array('img');
		$query = false;
		$priority=array('collector');
		
		$param['collector']['convertkey'] = array('personID','collID');
		$param['collector']['table'] = array('person','coll');
		$param['collector']['field'] =  array('id','id');
		$param['collector']['condition'] =  array('short_namecode','indivID');
		
		$data['collector'] = $this->parseRef($newData['rawdata'], 'collector', $param['collector']);
		
		$sequence = $this->secqInsert($data,$priority, $ignore);
		// pr($sequence);exit;
		
		if ($sequence){
			$query = $this->runQuery($sequence);
			logFile('query weak master success =>'.serialize($sequence));
		}
		return $query;
	}
	
	function translateField($table, $field)
	{
		$arrFields['tmp_location'] = array(
										'nama_tempat_lengkap'=>'unique_key',
										'longitude'=>'long',
										'latitude'=>'lat',
										'ketinggian'=>'elev',
										'medan'=>'geomorphology',
										'kabupaten'=>'kabupaten',
										'provinsi'=>'province',
										'catatan'=>'notes',
										);
		$arrFields['tmp_person'] = array(
										'nama_orang'=>'unique_key',
										'email'=>'email',
										);
		$arrFields['tmp_plant'] = array(
										'kode_tumbuhan'=>'unique_key',
										'tanggal'=>'date',
										'obs_oleh'=>'obs_by',
										'lokasi'=>'locn',
										'mikrohab'=>'microhab',
										'habit'=>'habit',
										'dbh'=>'dbh',
										'tinggi'=>'height',
										'bud'=>'bud',
										'flower'=>'flower',
										'fruit'=>'fruit',
										'catatan_tumbuhan'=>'indiv_notes',
										'plantlist_kode'=>'plantlist_kode',
										'jenis'=>'det',
										'confid'=>'confid',
										'det_by'=>'det_by',
										'det_date'=>'det_date',
										'det_using'=>'det_using',
										'det_notes'=>'det_notes',
										'nama_lokal'=>'local_name',
										'manfaatan'=>'benefit'
										);
		$arrFields['tmp_photo'] = array(
										'filename'=>'filename',
										'kode_tumbuhan'=>'tree_id',
										'photographer'=>'photographer',
										'plant_part'=>'plant_part',
										'notes'=>'notes',
										);
		$arrFields['tmp_taxon'] = array(
										'plantlist_kode'=>'unique_key',
										'jenis'=>'gen',
										);

		return $arrFields[$table][$field];
	}
	
	/* insert data to tmp table */
	function tmp_data($newData=array())
	{
		if (!is_array($newData)) return false;
		// pr($newData);exit;
		$defineTable = array('tmp_location', 'tmp_person', 'tmp_plant','tmp_photo');
		
		foreach ($defineTable as $k =>$val){
			$fields = array();
			$datas = array();
			$fieldsTaxa = array();
			$datasTaxa = array();

			foreach ($newData[$k]['data'] as $value){
				
				foreach ($value as $key => $v){
					
					if ($key){
						$translateField = $this->translateField($val, $key);
						$fields[] = "`$translateField`";
						$cleanData = addslashes($v);
						$datas[] = "'$cleanData'";

						if ($val == 'tmp_location'){

							
							if ($translateField == 'unique_key'){

								$fields[] = "`locality`";
								$datas[] = "'$cleanData'";
							} 
						}	

						$updateTmp[] = "`$key` = '$cleanData'";

						if ($val == 'tmp_plant'){

							$translateFieldTaxa = $this->translateField('tmp_taxon', $key);
							if ($translateFieldTaxa){
								$fieldsTaxa[] = "`$translateFieldTaxa`";
								$cleanData = htmlentities(addslashes($v));
								$datasTaxa[] = "'$cleanData'";
								
								if ($translateFieldTaxa == 'unique_key'){

									$fieldsTaxa[] = "`kewid`";
									$datasTaxa[] = "'$cleanData'";
								}

								$updateTmpTaxa[] = "`$key` = '$cleanData'";
							}
							
						}
					}
					
				}
				
				
				
				$tmpField = implode(',',$fields);
				$tmpData = implode(',',$datas);
				$updateFIeld = implode(',', $updateTmp);
				
				$sql[] = "INSERT INTO {$val} ({$tmpField}) VALUES ({$tmpData})"; 
				$fields = null;
				$datas = null;
				
				if ($k==2){ // taxon

					$tmpFieldTaxa = implode(',',$fieldsTaxa);
					$tmpDataTaxa = implode(',',$datasTaxa);
					$updateFIeldTaxa = implode(',', $updateTmpTaxa);
					
					$sql[] = "INSERT INTO tmp_taxon ({$tmpFieldTaxa}) VALUES ({$tmpDataTaxa})"; 
					$fieldsTaxa = null;
					$datasTaxa = null;
				}
				

				
			}
			
		}
		
		// pr($sql);exit;
		if ($sql){
		
			
			$startTransaction = $this->begin();
			if (!$startTransaction) return false;
			logFile('====TRANSACTION READY====');
			$failed = false;
			
			foreach ($sql as $key => $val){
				logFile('Insert tmp data '.$key);
				logFile($val);
				$res = $this->query($val,1);
				logFile($res);
				if (!$res) $failed = true;
			}
			
			if ($failed){
				$this->rollback();
				logFile('====ROLLBACK TRANSACTION====');
				return false;
			}else{
				$this->commit();
				logFile('====COMMIT TRANSACTION====');
				return true;
			}
			
			echo 'success upload data to tmp';
		}
		
		return false;
		
	}
	
	/* try to insert ref data */
	function storeRefData($data)
	{
		
		$tmpTable = array('tmp_taxon','tmp_person','tmp_location');
		$defineTable = array('taxon','person','locn');
		$defineFieldIndiv = array('person'=>'tmp_person_key','locn'=>'tmp_location_key');
		
		$query = $data['query'];
		$unique = $data['uniqkey'];
		$rawdataPerson = $data['rawdata']['person']['data'];

		// pr($data);exit;
		if ($query){
			$i = 0;
			
			// $startTransaction = $this->begin();
			// if (!$startTransaction) return false;
			logFile('====TRANSACTION READY====');
			
			$failed = false;
			foreach ($defineTable as $val){
				
				$j = 0;
				foreach ($query[$val] as $key => $value){
					logFile($value);
					$sql = $this->query($value);
					logFile($sql);
					if (!$sql) $failed = true;
					// usleep(50);
					$lastID = $this->insert_id();
					
					$update = "UPDATE {$tmpTable[$i]} SET tmp_unique_key = '{$lastID}' WHERE 
								unique_key = '{$unique[$val][$j]}' LIMIT 1";
					// pr($update);
					$res = $this->query($update,1);
					if (!$res) $failed = true;
					
					// pr($tmpTable[$i]);
					if ($defineTable[$i] == 'taxon'){

						if (!$lastID){ echo "Taxon not complete"; exit;}
						$updateTaxon = "UPDATE tmp_plant SET tmp_taxon_key = '{$lastID}' WHERE 
								plantlist_kode = '{$unique[$val][$j]}' ";
						// pr($updateTaxon);
						$res = $this->query($updateTaxon,1);

						$updateCreate = "UPDATE tmp_plant SET tmp_creator_key = '{$this->user['id']}' WHERE 
								plantlist_kode = '{$unique[$val][$j]}' ";
						// pr($updateTaxon);
						usleep(500);
						$res = $this->query($updateCreate,1);

					}
					
					if ($defineTable[$i] == 'person'){
						if (!$lastID){ echo "Person not complete"; exit;}
						$updatePerson = "UPDATE tmp_plant SET tmp_person_key = '{$lastID}' WHERE 
								obs_by = '{$unique[$val][$j]}' ";
						// pr($updatePerson);
						usleep(500);
						$res = $this->query($updatePerson,1);
						// $updatePhoto = "UPDATE  tmp_photo SET tmp_person_key = '{$lastID}' WHERE 
						// 		tree_id = '{$unique[$val][$j]}' ";
						// pr($updatePhoto);
						// $res = $this->query($updatePhoto,1);

						// store account 
						$date = date('Y-m-d H:i:s');
						$username = substr(str_shuffle('abcdefghjkmn123456789'), 0, 8) ;
						$password = "1234512345";
						$email_token = sha1(CODEKIR.date('ymdhis'));

						$storeAccount = "INSERT IGNORE INTO florakb_person (id, password, username, salt, n_status,register_date,email_token)
										VALUES ({$lastID}, '{$password}','{$username}', '{$this->salt}',0,'{$date}','{$email_token}')";
						logFile($storeAccount);
						$resAccount = $this->query($storeAccount,1);


						// check if system never send mail account to user
						$to = $rawdataPerson[$j]['email'];

						$storeLogEmail = $this->activityHelper->updateEmailLog(false,$to,'account',0);

						
					}
					
					if ($defineTable[$i] == 'locn'){

						if (!$lastID){ echo "Location not complete"; exit;}
						$updateLocn = "UPDATE tmp_plant SET tmp_location_key = '{$lastID}' WHERE 
								locn = '{$unique[$val][$j]}' ";
						// pr($updateLocn);
						usleep(500);
						$res = $this->query($updateLocn,1);
					}
					
					$j++;
				}
				
				$i++;

				logFile('Store data '.$val.' success', $this->user['username']);
			}
			
			if ($failed){
				// $this->rollback();
				logFile('====ROLLBACK TRANSACTION====');
				return false;
			}else{
				// $this->commit();
				logFile('====COMMIT TRANSACTION====');
				return true;
			}
		}
		
	}
	
	/* try to insert indiv data */
	function storeIndivData($data)
	{
		
		$tmpTable = array('tmp_photo');
		$defineTable = array('indiv');
		$defineFieldIndiv = array('indiv'=>'tmp_indiv_key');
		
		$query = $data['query'];
		$unique = $data['uniqkey'];
		
		if ($query){
			$i = 0;
			
			// $startTransaction = $this->begin();
			// if (!$startTransaction) return false;
			logFile('====TRANSACTION READY====');
			
			$failed = false;
			foreach ($defineTable as $val){
				
				$j = 0;
				foreach ($query[$val] as $key => $value){
					logFile($value);
					$sql = $this->query($value);
					logFile($sql);
					if (!$sql) $failed = true;
					usleep(50);
					$lastID = $this->insert_id();
					logFile($update);
					
					if ($defineTable[$i]=='indiv'){
						$updateIndiv = "UPDATE  tmp_plant SET tmp_indiv_key = '{$lastID}' WHERE 
								unique_key = '{$unique[$val][$j]}'";
						// pr($updateIndiv);
						$res = $this->query($updateIndiv,1);
						
						$updateImg = "UPDATE {$tmpTable[$i]} SET tmp_indiv_key = '{$lastID}' WHERE 
								tree_id = '{$unique[$val][$j]}'";
						// pr($update);
						$res = $this->query($updateImg,1);
						if (!$res) $failed = true;
					}
					
					
					
					$j++;
				}
				
				$i++;
			}
			
			if ($failed){
				// $this->rollback();
				logFile('Store data individu failed', $this->user['username']);
				logFile('====ROLLBACK TRANSACTION====');
				return false;
			}else{
				$this->commit();
				logFile('Store data individu success', $this->user['username']);
				logFile('====COMMIT TRANSACTION====');
				return true;
			}
		}
		
	}
	
	/* try to insert indiv data */
	function storeSingleData($data,$table='collector')
	{
		
		
		$defineTable = array($table);
		$query = $data['query'];
		$unique = $data['uniqkey'];
		
		if ($query){
			$i = 0;
			
			// $startTransaction = $this->begin();
			// if (!$startTransaction) return false;
			logFile('====TRANSACTION READY====');
			
			$failed = false;
			foreach ($defineTable as $val){
				
				$j = 0;
				foreach ($query[$val] as $key => $value){
					logFile($value);
					$sql = $this->query($value);
					logFile($sql);
					if (!$sql) $failed = true;
					
					$j++;
				}
				
				$i++;

				logFile('Store data '.$val.' success', $this->user['username']);
				
			}
			
			if ($failed){
				// $this->rollback();
				logFile('====ROLLBACK TRANSACTION====');
				return false;
			}else{
				// $this->commit();
				logFile('====COMMIT TRANSACTION====');
				return true;
			}
		}
		
	}
	
	function storeMasterData($data)
	{
		
		$tmpTable = array('tmp_plant');
		$defineTable = array('det','obs','coll');
		$defineFieldIndiv = array('coll'=>'tmp_coll_key');
		
		$query = $data['query'];
		$unique = $data['uniqkey'];
		
		if ($query){
			$i = 0;
			
			// $startTransaction = $this->begin();
			// if (!$startTransaction) return false;
			logFile('====TRANSACTION READY====');
			
			$failed = false;
			foreach ($defineTable as $val){
				
				$j = 0;
				foreach ($query[$val] as $key => $value){
					logFile($value);
					$sql = $this->query($value);
					logFile($sql);
					if (!$sql) $failed = true;
					usleep(50);
					$lastID = $this->insert_id();
					logFile($update);
					
					if ($defineTable[$i]=='coll'){
						$updateIndiv = "UPDATE tmp_plant SET tmp_coll_key = '{$lastID}' WHERE 
								tmp_indiv_key = '{$unique[$val][$j]}'";
						// pr($update);
						logFile($updateIndiv);
						$res = $this->query($updateIndiv,1);
						if (!$res) $failed = true;
					}
					
					
					
					$j++;
				}
				
				$i++;

				logFile('Store data '.$val.' success', $this->user['username']);
			}
			
			if ($failed){
				// $this->rollback();
				logFile('====ROLLBACK TRANSACTION====');
				return false;
			}else{
				// $this->commit();
				logFile('====COMMIT TRANSACTION====');
				return true;
			}
		}
		
	}
	
	/* get ref data */
	function getRefData()
	{
		/* Scenario 
		1. get data from tmp table
		2. store to real table
		3. return insert id
		4. update tmp table id with new id
		*/
		$defineTable = array('tmp_taxon','tmp_photo','tmp_person','tmp_location');
		$realTable = array('img','person','locn');
		
		$sql = array();
		$dataArr = array();
		$index = 1;
		foreach ($defineTable as $k =>$val){
		
			$sqlP = "SELECT * FROM {$val}";
			$resP = $this->fetch($sqlP,1,1);
			// pr($resP);
			if ($resP){
				
				$dataArr[$index]['data'] = $resP;
				
			}
			
			$index++;
		}
		
		return $dataArr;
	}
	
	/* get ref data */
	function getMasterData($single=false, $table='tmp_plant')
	{
		/* Scenario 
		1. get data from tmp table
		2. store to real table
		3. return insert id
		4. update tmp table id with new id
		*/
		$defineTable = array($table);
		if ($single){
			$realTable = array('img');
		}else{
			$realTable = array('indiv','det','obs','coll','collector');
		}
		
		
		$sql = array();
		$dataArr = array();
		$index = 0;
		foreach ($defineTable as $k =>$val){
		
			$sqlP = "SELECT * FROM {$val}";
			logFile($sqlP);
			$resP = $this->fetch($sqlP,1,1);
			// pr($resP);
			if ($resP){
				
				$dataArr[$index]['data'] = $resP;
				
			}
			
			$index++;
		}
		
		return $dataArr;
	}
	
	/* insert data from excel */
	function insertCollFromExcel($newData=array())
	{
		
		$referenceQuery = $newData['ref'];
		$masterQuery = $newData['master'];
		$priority = $newData['ref_priority'];
		$masterPriority = $newData['master_priority'];
		
		$insertRefData = false;
		$insertMasterData = false;
		
		
		$startTransaction = $this->begin();
		if (!$startTransaction) return false;
		logFile('====TRANSACTION READY====');
		
		$insertRefData = $this->insertReference($referenceQuery,$priority);
		$insertMasterData = $this->insertMaster($masterQuery,$masterPriority);
		$insertWeakMasterData = $this->weakMasterData($masterQuery,$masterPriority);
	
		
		if ($insertRefData or $insertMasterData or $insertWeakMasterData){
			$this->rollback();
			logFile('====ROLLBACK TRANSACTION====');
			return false;
		}else{
			$this->commit();
			logFile('====COMMIT TRANSACTION====');
			return true;
		}

		exit;
		
	}
	
	/* run sql query */
	function runQuery($data,$last=false)
	{
		
		if ($data){
			
			try {
			
				
				$failed = true;
				$count = 0;
				foreach ($data as $val){
					logFile('excecute query =>'.serialize($val));
					$sql = $this->query($val);
					logFile($sql);
					if (!$sql) $failed = false;
					$count++;
					if ($count==100){
						usleep(500);
						$count = 0;
					}
					
					
				}
				
			} catch (Exception $e) {
				
			}
			
		}
		
		
		
		
		return $failed;
	}
	
	/* reverse data array */
	function secqInsert($newData=array(), $priority=array(),$ignore=array()){
		
		if (empty($newData)) return false;
		
		$seq = array();
		
		// looping priority
		foreach ($priority as $val){
			
			// get record from array $newData if priority value match
			if (!empty($newData[$val])){
			
				foreach ($newData[$val] as $value){
					$seq[] = $value;
				}
			}
			
		}
		
		return $seq;
	}
	
	/* parse reference field, before searching */
	function parseRef($newData=array(), $index=false,$param=array())
	{
		if (!$index) return false;
		
		$data = $newData[$index];
		
		foreach ($data['data'] as $key => $val){
			
			foreach ($param['convertkey'] as $i =>$o){
				if (array_key_exists($o, $val)){
					$data['data'][$key][$param['convertkey'][$i]] = $this->checkDataRef($param['table'][$i],$param['field'][$i],$param['condition'][$i],$val[$param['convertkey'][$i]]);
				}
				
				
			}
			
		}
		
		if ($data){
			
			$ignoreImageField = array('indivID','personID');
			$sql = array();
			foreach ($data['data'] as $key => $val){
				
				$field = array();
				$datas = array();
				$tmpForUpdate = array();
				foreach ($val as $k => $v){
					$field[] = $k;
					$datas[] = "'$v'";
					
					if (!in_array($k, $ignoreImageField)){
						$tmpForUpdate[] = $k .'='. "'$v'";
					}
					
					$indivID = $val['indivID'];
					$personID = $val['personID'];
				}
				
				$imp = implode(',',$field);
				$imps = implode(',',$datas);
				$update = implode(',',$tmpForUpdate);
				/*
				if ($index=='img'){
					$sql[] = "UPDATE {$index} SET ({$update}) WHERE indivID = {$indivID} AND personID = {$personID}"; 
				}else{
					$sql[] = "INSERT INTO {$index} ({$imp}) VALUES ({$imps})"; 
				}
				*/
				$sql[] = "INSERT INTO {$index} ({$imp}) VALUES ({$imps}) ON DUPLICATE KEY UPDATE {$update}"; 
			}
			
			
		}
		return $sql;
	}
	
	/* look for references id */
	function checkDataRef($table=false,$field=false,$cond="short_namecode", $id=false)
	{
		
		if (!$id && !$table && !$field) return false;
		
		$sql = "SELECT {$field} FROM {$table} WHERE {$cond} = '{$id}' LIMIT 1";
		// pr($sql);
		$res = $this->fetch($sql);
		if ($res) return $res['id'];
		return false;
	}
	
	/* end excel helper */
	
	
	function insertData($table=false, $data=array())
	{
		if (!$table and empty($data)) return false;
		
		$data = array();
		$data['status'] = false;
		
		foreach ($data as $key=>$val){
			$tmpfield[] = $key;
			$tmpvalue[] = "'{$val}'";
			$tmpUpdate[] = "`$key` = '{$val}'";
		}
		
		$field = implode (',',$tmpfield);
		$value = implode (',',$tmpvalue);
		$update = implode(',', $tmpUpdate);
		
		$sql = "INSERT INTO {$table} ({$field}) VALUES ({$value}) ON DUPLICATE KEY UPDATE {$update}";
		$res = $this->query($sql);
		logFile($sql);
		if ($res){
			
			$data['lastid'] = $this->insert_id();
			$data['status'] = true;
		}
		return $data;
	}

	function updateTmpPhoto($data=array())
	{

		$sql = "SELECT f.tree_id, p.tmp_person_key FROM tmp_photo AS f LEFT JOIN tmp_plant AS p 
				ON f.tree_id = p.unique_key GROUP BY f.tree_id";
		$res = $this->fetch($sql,1,1);
		if ($res){
			// pr($res);
			foreach ($res as $key => $val) {
				$update = $this->query("UPDATE tmp_photo SET tmp_person_key = '{$val['tmp_person_key']}' WHERE tree_id = '{$val['tree_id']}'", 1);
			}
		}
	}

	function truncateData($ori=false, $tmp=false)
	{
		$data1 = array('collector','det','img','obs','coll','indiv','locn','person','taxon');
		$data2 = array('tmp_location','tmp_person','tmp_photo','tmp_plant','tmp_taxon');
		$success = true;

		if ($ori){
			foreach ($data1 as $val){
				
				$sql = $this->query("DELETE FROM ".$val);
				if (!$sql) $success = false;
			}

		}
		
		if ($tmp){
			foreach ($data2 as $val){
				
				$sqltmp = $this->query("DELETE FROM ".$val,1);
				if (!$sqltmp) $success = false;
			}
		}

		if ($success) return true;
		return false;
		
	}
	
	
	/**
     * @todo start sql transaction
     * 
     * @return boolean true/false
     * 
     * */
    function startTransaction()
	{
		$startTransaction = $this->begin();
		if (!$startTransaction) return false;
		logFile('====TRANSACTION READY====');
		return true;
	}
	
    /**
     * @todo rollback sql transaction
     * 
     * @return boolean true/false
     * 
     * */
	function rollbackTransaction()
	{
		$this->rollback();
		logFile('====ROLLBACK TRANSACTION====');
		return true;
	}
	
    /**
     * @todo commit sql transaction
     * 
     * @return boolean true/false
     * 
     * */
	function commitTransaction()
	{
		$this->commit();
		logFile('====COMMIT TRANSACTION====');
		return true;
	}


}

?>