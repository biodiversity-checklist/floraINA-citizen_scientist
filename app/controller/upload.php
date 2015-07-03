<?php

class upload extends Controller {
	
	var $models = FALSE;
	var $view;
	var $user;
	
    public function __construct()
	{
        global $basedomain;
		$this->loadmodule();
        $this->view = $this->setSmarty();
        $this->view->assign('basedomain',$basedomain);
        $this->user = $this->isUserOnline();
        if (!$this->isUserOnline()){ redirect($basedomain); exit;}
	}
	
    public function loadmodule()
	{
		$this->collectionHelper = $this->loadModel('collectionHelper');
        $this->excelHelper = $this->loadModel('excelHelper');
        $this->activityHelper = $this->loadModel('activityHelper');
        $this->loginHelper = $this->loadModel('loginHelper');
        $this->userHelper = $this->loadModel('userHelper');
    }
	
	public function index(){
        $username = $this->user['login']['username'];
		return $this->loadView('batchUpload/index');
    }

    public function excel(){
        $username = $this->user['login']['username'];
		$this->log('surf','upload excel');
		// logFile("Begin upload", $username);
        return $this->loadView('batchUpload/excel');
    }
	
	function parseExcel()
	{
		/*
		New scenario !
		1. Parse data xls 
		2. Validate data before upload
		3. Store data to tmp table
		3. Try to move data from tmp table to real table
		4. Done
		
		*/
		
		global $EXCEL;
		
		$username = $this->user['login']['username'];
		
		logFile(serialize($_FILES));

		if ($_FILES){
			
			$numberOfSheet = 4;
			$startRowData = 1;
			$startColData = 1;
			$formNametmp = array_keys($_FILES);
			$formName = $formNametmp[0];
			
			if (empty($formName)) die;
			
			$startTime = microtime(true);
			/* parse data excel */
			
			logFile('load excel begin');

			// empty log file
			

			$parseExcel = $this->excelHelper->fetchExcel($formName, $numberOfSheet,$startRowData,$startColData);
			
			
			if ($parseExcel){
				// logFile('Extract File ', $username);
				foreach ($parseExcel as $key => $val){
					
					$field = implode(',',$val['field_name']);
					
					$data = array();

					if ($val['data']){

						foreach ($val['data'] as $keys => $value){
						
							foreach ($value as $k => $v){
								$data[$val['field_name'][$k]] = $v;
							}
							
							$newData[$val['sheet']]['data'][] = $data; 
							
						}
					}else{
						print json_encode(array('status'=>false, 'msg'=>'Data tidak tersedia'));
						exit;
					}
					
					
				}
				
				// pr($newData);exit;
				/* here begin process */
				if ($newData){
					
					$emptyTmptable = $this->collectionHelper->truncateData(false,true);
					
					if ($emptyTmptable){
						
						logFile('empty tmp table before insert');

						// sleep(1);
						$referenceQuery = $this->collectionHelper->tmp_data($newData);

						logFile('store data from xls to tmp table');

						if ($referenceQuery){

							// pr($newData);exit;
					
							// logFile('Preparing database ', $username);
							$insertData = false;
							// $referenceQuery = true;
							if ($referenceQuery){
								
								$this->collectionHelper->startTransaction();
								
								$getRef = $this->collectionHelper->getRefData($newData);
								
								$referenceQuery = $this->excelHelper->referenceData($getRef);
								
								$insertRef = $this->collectionHelper->storeRefData($referenceQuery);
								
								if (!$insertRef){
									
									$this->collectionHelper->rollbackTransaction();
									print json_encode(array('status'=>false, 'msg'=>'Upload data gagal'));
									exit;
								}

								
								$getMaster = $this->collectionHelper->getMasterData();
								// insert indiv
								// pr($getMaster);
								$indivQuery = $this->excelHelper->parseMasterData($getMaster,true);
								// pr($indivQuery);
								// exit;
								$insertIndiv = $this->collectionHelper->storeIndivData($indivQuery);
								
								// insert det,obs,coll
								sleep(1);
								$getMaster = $this->collectionHelper->getMasterData();
								$masterQuery = $this->excelHelper->parseMasterData($getMaster);
								
								$insertIndiv = $this->collectionHelper->storeMasterData($masterQuery);
								
								// update tmp photo
								$updateTmpPhoto = $this->collectionHelper->updateTmpPhoto();

								// exit;
								// insert collector
								$getMaster = $this->collectionHelper->getMasterData();
								$collectorQuery = $this->excelHelper->parseMasterData($getMaster,true,5,'collector');
								
								$insertCollector = $this->collectionHelper->storeSingleData($collectorQuery);
								
								$getMaster = $this->collectionHelper->getMasterData(true,'tmp_photo');
								

								$imgQuery = $this->excelHelper->parseMasterData($getMaster,true,4,'img');
								
								// pr($imgQuery);
								// exit;
								$insertImage = $this->collectionHelper->storeSingleData($imgQuery,'img');
								// pr($imgQuery);
								if ($insertImage){

									$this->collectionHelper->commitTransaction();

									
									$insertData = true;

								}else{
									$this->collectionHelper->rollbackTransaction();
								}
								
							}else{
								print json_encode(array('status'=>false, 'msg'=>'Gagal mengunggah data'));
								exit;
							}
							

							// exit;
							/*
							[Old script]
							
							$masterQuery = $this->excelHelper->parseMasterData($newData);
							$masterQuery['rawdata']['img'] =  $referenceQuery['rawdata']['img'];
							
							$priority = array('taxon','locn','person');
							$masterPriority = array('indiv','img','det','obs','coll','collector');
							
							$param['ref'] = $referenceQuery;
							$param['ref_priority'] = $priority;
							$param['master'] = $masterQuery;
							$param['master_priority'] = $masterPriority;

							
							$insertData = $this->collectionHelper->insertCollFromExcel($param);
							*/ 
							$endTime = microtime(true);
							
							if ($insertData){
								sleep(1);
								logFile('Insert xls success');
								$this->log('upload','success upload xls');
								print json_encode(array('status'=>true, 'finish'=>true, 'msg'=>'Insert success  ('. execTime($startTime,$endTime).')'));
								// echo 'Insert success  ('. execTime($startTime,$endTime).')';	
								
								// send mail to user
								

								$sendMail = $this->sendMail();
								// if ($sendMail){
								// 	logFile('Send mail success');
								// 	print json_encode(array('status'=>true, 'finish'=>true, 'msg'=>'Insert success  ('. execTime($startTime,$endTime).')'));
								// exit;
								// }else{
								// 	logFile('Send mail failed');
								// 	print json_encode(array('status'=>true, 'msg'=>'Kirim akun gagal'));
								// exit;
								// }	
									
								exit;
							}else{
								logFile('Insert xls failed');
								// echo 'Insert data failed';	
								print json_encode(array('status'=>false, 'msg'=>'Insert data gagal'));
								exit;
							} 

						}else{
							logFile('failed insert to tmp database');
							print json_encode(array('status'=>false, 'msg'=>'Insert data gagal'));
							exit;
						}
					}
					

				}else{
					print json_encode(array('status'=>false, 'msg'=>'Tidak ada data yang tersedia'));
					exit;
				}
			}else{
				print json_encode(array('status'=>false, 'msg'=>'Ekstrak gagal'));
				exit;
			}
		}else{
			logFile('File xls empty');
			// echo "File is empty";
			print json_encode(array('status'=>true, 'msg'=>'File kosong'));
		}
		
		
		exit;
	}
	

	/**
     * @todo send user mail
     * 
     * @return boolean true/false
     * 
     * */
	function sendMail()
	{
		ob_start();
		// pr('ada');
		$checkBefore = $this->activityHelper->getEmailLog('');
		// pr($checkBefore);exit;
		if ($checkBefore){
			
			foreach ($checkBefore as $key => $value) {

				$dataArr['email'] = $value['receipt'];

				$getEmail = $this->userHelper->getUserData('email',$value['receipt']);

				if ($getEmail){
					$getUserName = $this->userHelper->getUserappData('id',$getEmail['id'],0);

					// $dataArr['username'] = substr(str_shuffle('abcdefghjkmn123456789'), 0, 8) ;
					$dataArr['username'] = $getUserName['username'];

					logFile('generate account '.serialize($dataArr));
				
					$getToken = $this->loginHelper->getEmailToken($dataArr['username']);
					logFile('get email token '. serialize($getToken));
					$generateMail = $this->activityHelper->generateEmail($dataArr['email'],$dataArr['username'],2,$getToken['email_token']);
					if (is_array($generateMail)){

						logFile(' generate mail : '. serialize($generateMail));

						$msg = null;
						$this->view->assign('username',$dataArr['username']);
						$this->view->assign('email',$dataArr['email']);
						$this->view->assign('encode',$generateMail['encode']);
		                $msg .= "<p>Hi ".$dataArr['username']."!</p>";
		                $msg .= $this->loadView('emailTemplate');
		                // try to send mail 
		                $sendMail = sendGlobalMail($to, $from, $msg,true);


						logFile('generate account status ');
						$sendUserAccount = sendGlobalMail($generateMail['to'],$generateMail['from'],$msg);
						logFile('generate account success '.serialize($sendUserAccount));
						if ($sendUserAccount['result']){

							// usleep(500);
							$this->activityHelper->updateEmailLog(true, $generateMail['to'],'account',1);
							logFile('send account to email via xls success');
						}else{
							logFile('send account to email via xls failed');
							// echo "Person data not complete"; exit;
							return false;
						}
					}else{
						logFile('generate email failed');
						// echo "Person data not complete"; exit;
						return false;
					}
				}else{
					logFile('user not exist');
				}
				
				
				
			}

			
			
		}else{
			logFile('email status 1');
			// echo "Person data not complete"; exit;
			return false;
		}
	}


	function showUploadProcess()
	{
		
		global $CONFIG;

		$dataArr['file'] = "PHPTail";
		$dataArr['path'] = LIBS.'phptail/';

		require './'.LIBS.'phptail/PHPTail.php';
		// $phpTail = $this->load($dataArr);

		$fileName = $CONFIG['default']['root_path']."/logs/".$this->user['login']['username'];
		
		
		$phpTail = new PHPTail($fileName);
		// pr($phpTail);
		if(isset($_GET['ajax']))  {

		        echo $phpTail->getNewLines($_GET['lastsize']);
		        die();
		}else{
			
			echo json_encode(array('size'=> filesize($fileName)));
		}
		
		// echo $phpTail->getNewLines();
		// $phpTail->ada();
		// $phpTail->generateTemplate();

		exit;
	}

	function truncate()
	{
		$this->collectionHelper->truncateData(false,true);
	}
	
	function logUploadUser($file)
	{

		global $CONFIG;
		$fileName = $CONFIG['default']['root_path']."/logs/".$this->user['login']['username'];
		$data = json_encode(array('data'=>file_get_contents($fileName)));
		
		$storeLog = $this->activityHelper->storeUserUploadLog($data, $file);
		return true;
	}

	
	function debug()
	{

		$param = _g('param');
		if ($param){

			switch ($param) {
				case '1':
				{
					phpinfo();
				}
				break;
				case '2':
				{
					sendGlobalMail('ovan89@gmail.com','noreply@flora-kalbar.com','testing send mail');
				}
				break;
				default:
					echo 'debug mode not specified';
					break;
			}
		}else{
			echo 'param : <br>1. Php Info</br>2. Send Mail';
		}

		exit;

	}
}

?>
