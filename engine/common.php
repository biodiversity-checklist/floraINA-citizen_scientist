<?php

/* Kumpulan fungsi umum yang sering digunakan */

function base_url() {
	global $config;
	
	$base_url = $config['base_url'];
	
	return $base_url;
}

function change_date_simple($date_data, $type, $order_by)
{
	/* ex : change_date_to_slash('2012-01-01', 'slash', 'by_date') */
	
	($date_data !='') ? $status = 1 : exit ('Date not complete');
	
	if ($type == 'slash')
	{
		list ($tahun, $bulan, $tanggal) = explode ('-',$date_data);
	
		if ($order_by == 'by_year') $new_date = "$tahun/$bulan/$tanggal";
		if ($order_by == 'by_month') $new_date = "$bulan/$tanggal/$tahun";
		if ($order_by == 'by_date') $new_date = "$tanggal/$bulan/$tahun";
	}
	else if ($type == 'strip')
	{
		list ($tahun, $bulan, $tanggal) = explode ('/',$date_data);
	
		if ($order_by == 'by_year') $new_date = "$tahun-$bulan-$tanggal";
		if ($order_by == 'by_month') $new_date = "$bulan-$tanggal-$tahun";
		if ($order_by == 'by_date') $new_date = "$tanggal-$bulan-$tahun";
	}
	
}
	
function simple_paging($paging_data, $limit)
{
	if ($paging_data==0)
	{
		echo '<script type=text/javascript>alert("Page Not Found"); window.location.href="?pid=1";</script>';
	}
	if ($paging_data== 1)
	{
		$paging = ((($paging_data - 1) * $limit));
	}else
	{
		$paging = ((($paging_data - 1) * $limit) + 1);
	}
	
	return $paging;
}
	
function form_validation($data)
{
	if (!$data) return false;
	$valid_post_vars = $data;
						
	$dataArr = array ();			
	foreach ($valid_post_vars as $key => $value) {
		//echo $key;
		//echo $value;
		//$prefix_post_vars = "p_";
		//$valid_post_var_name = $prefix_post_vars . $i_vpv;
		
		$valid_post_var_value = trim(htmlspecialchars($value));
		
		//$$valid_post_var_name = $valid_post_var_value;

		$dataArr[$key] = $valid_post_var_value;
		
	}
	
	return $dataArr;
	//print_r($dataArr);
}
	
function clear_var($data)
{
	return $$data = '';
}

function under_development() {

	echo 'Maaf, Situs ini sedang dalam perbaikan';
	
	exit;
}

function redirect($data) {
	
	echo "<meta http-equiv=\"Refresh\" content=\"0; url={$data}\">";

}

/**
 * @todo upload file function
 * @param String $data = name attribut of file to be upload
 * @param String $path = string path to upload file
 * 
 * @return Array $result = array('status' => '', 'message' => '', 'full_path' => '', 'full_name' => '', 'raw_name' => '');
 * @return int status = 0/1
 * @return String message = message to print at view
 * @return String full_path = to process the uploaded file
 * @return String full_name = full name of uploaded file, include extension
 * @return String raw_name = raw name of uploaded file
 * */
function uploadFile($data,$path=null,$ext){
	global $CONFIG;
	
	if (array_key_exists('admin',$CONFIG)) $key = 'admin';
	if (array_key_exists('default',$CONFIG)) $key = 'default';
    
    /* result template
    $result = array(
        'status' => '',
        'message' => '',
        'full_path' => '',
        'full_name' => '',
        'raw_name' => '',
        'real_name' => ''
    );
    */
    
    if (!in_array($_FILES[$data]['type'], $CONFIG[$key][$ext])){
        $result = array(
            'status' => '0',
            'message' => 'File type is not allowed.',
            'full_path' => '',
            'full_name' => '',
            'raw_name' => '',
            'real_name' => ''
        );
        return $result;
    }
	
	if ($path!='') $path = $path.'/';
	$pathFile = $CONFIG[$key]['upload_path'].$path;
	$ext = explode ('.',$_FILES[$data]["name"]);
	$countExt = count($ext);
	$getExt = $ext[$countExt-1];
	$shufflefilename = md5(str_shuffle('codekir-v0.3'.$CONFIG[$key]['max_filesize']));
	$filename = $shufflefilename.'.'.$getExt;
	
	if ($_FILES[$data]["error"] > 0){
	
		echo "Return Code: " . $_FILES[$data]["error"] . "<br>";
	
	}else{
	
		$_FILES[$data]["name"];
		($_FILES[$data]["size"] / $CONFIG[$key]['max_filesize']);
		$_FILES[$data]["tmp_name"];

		if (file_exists($pathFile. $_FILES[$data]["name"])){
			$result = array(
				'status' => '0',
				'message' => 'File exist.',
				'full_path' => $pathFile,
				'full_name' => $filename,
				'raw_name' => $shufflefilename,
                'real_name' => $_FILES[$data]["name"]
			);
			return $result;
		}else{
		
			move_uploaded_file($_FILES[$data]["tmp_name"],$pathFile . $filename);
			$result = array(
				'status' => '1',
				'message' => 'Upload Succeed.',
				'full_path' => $pathFile,
				'full_name' => $filename,
				'raw_name' => $shufflefilename,
                'real_name' => $_FILES[$data]["name"]
			);
			return $result;
		}
	}
	
	return $filename;
}

function encode($data=false)
{
	$hasil = base64_encode(serialize($data));
	return $hasil;
}

function decode($data=false)
{
	$hasil = unserialize(base64_decode($data));
	return $hasil;
}

function getindexzip($name=null)
{
	
	if ($name==null) return false;
	
	$zip = new ZipArchive;

	if ($zip->open($name) == TRUE) {
		for ($i = 0; $i < $zip->numFiles; $i++) {
			$filename[] = $zip->getNameIndex($i);
		 
		}
	}
	
	if (is_array($filename)) return $filename;
	return false;
}

function unzip($file=null, $path=null)
{
	global $CONFIG;

	if ($file==null) return false;
	
	$zip = new ZipArchive;
	if ($zip->open($file) === TRUE) {
		$zip->extractTo($path);
		$zip->close();
        unlink($file);
		return true;
	}else{
        unlink($file);
        return false;
	}
}

/**
 * @todo unzip file
 * @param String $file = full path to file that will be extract, including extension
 * @param String $path_extract = path to folder where $file will be extract
 * 
 * @return true
 * */
function s_linux_unzip($file, $path_extract){
    mkdir($path_extract, 0755);

    //extract and delete zip file             
    shell_exec("unzip -jo $file  -d $path_extract");
    //unlink($file);
    return true;
}

/**
 * @todo create folder
 * @param array $path_array = array contain folder that will be created array($path_1, $path_2)
 * @param int $permission = value of permission access to folder
 * */
function createFolder($path_array, $permissions){
    foreach ($path_array as $dir) {
        if (!is_dir($dir)){
            mkdir($dir, $permissions, TRUE);
        }
    }
}

/**
 * @todo calculate excecution time
 * @param int $timeStart = start time in microtime
 * @param int $timeEnd = end time in microtime
 * @return int/float
 * */
function execTime($timeStart, $timeEnd)
{
	$time = $timeEnd  - $timeStart ;
	
	$runTime = number_format($time,3) . ' Seconds';
	return $runTime;
}

/**
* @todo Delete a file, or a folder and its contents
* @param string $dirPath Directory to delete
* @return bool Returns TRUE on success, FALSE on failure
*/
function deleteDir($dirPath) {
    if (! is_dir($dirPath)) {
        throw new InvalidArgumentException("$dirPath must be a directory");
    }
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
        $dirPath .= '/';
    }
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) {
            deleteDir($file);
        } else {
            unlink($file);
        }
    }
    return rmdir($dirPath);
}

/**
 * @todo create log file
 * @param string $comment = comment log
 * */
function logFile($comment, $fileName=false, $method=false)
{
	
	/*
		method false = "a"
		method (true)1 = w
	*/
	$path = LOGS;
	
	if (!$fileName) $fileName = 'Log-'.date('d-m-Y').'.txt';
	
	if ($method){
		$handle = fopen($path.$fileName, "w");
	}else{
		$handle = fopen($path.$fileName, "a");
	}
	
	fwrite($handle, "{$comment}"."\n");
	fclose($handle);
}

function sftpServices($host="localhost", $user=false, $pass=false, $filename=false, $singleAccount=false)
{

	global $CONFIG, $sftpConfig;

	logFile('host ='.$host.'user='.$user.'pass='.$pass.'filename='.$filename.'single='.$singleAccount);

	if (!$user && !$pass && !$filename) return false;
	$folderTmp = $user."/".$CONFIG['default']['zip_foldername']."/";
	$pathFile = $CONFIG['default']['upload_path_temporary'].$folderTmp.$filename;

	if ($singleAccount){

		$user = $sftpConfig['user'];
		$pass = $sftpConfig['pass'];
		$host = $sftpConfig['host'];

		$folderTmp = $user."/".$CONFIG['default']['zip_foldername']."/";
		$pathFile = $CONFIG['default']['upload_path_temporary'].$folderTmp.$filename;
		
	}

	logFile(serialize($sftpConfig));

	if ($sftpConfig['mode']=='1'){

		$shellExec = "cd ".$CONFIG['default']['upload_path']." && sftp ".$user."@".$host.":".$pathFile;
		logFile($shellExec);
		exec($shellExec);

		return true;
		exit;
	}

	// if using single account to upload zip file 
	

	$portDefine = $sftpConfig['port'];

	
	logFile("begin connection ssh2");
	$connection = ssh2_connect($host, $portDefine);


	logFile("connect=".$connection." user=".$user." pass=".$pass);
	
	if (ssh2_auth_password($connection, $user, $pass)){
		logFile('sftp connected'); 
	}else{
		logFile('sftp failed connected');
		return false; 
	}

	$sftp = ssh2_sftp($connection);

	
	if (ssh2_scp_recv($connection, $pathFile, $CONFIG['default']['upload_path'].$filename)){

		logFile('sftp move file to tmp');
	}else{
		logFile('sftp failed move file to tmp');
		return false;
	}

	// ssh2_scp_recv($connection, $pathFile, $CONFIG['default']['upload_path'].$filename);
	logFile('source='.$pathFile.' target='.$CONFIG['default']['upload_path'].$filename);
	// unlink($pathFile);
	logFile('delete current file');

	return true;

}

function createAccount($data=array())
{
	global $CONFIG;
	
	$host = $CONFIG['default']['hostname'];
	$port = "12345";
	
	logFile(serialize($data));
	exec("echo '".$data['username']. " ".$data['password']."' | nc ".$host." ".$port);

}

function sendGlobalMail($to,$from,$msg,$config=true){


	GLOBAL $CONFIG, $LOCALE;
	
	if (!$config){

		@mail($to,"[ NOTIFICATION ] Flora Kalbar",$msg,"From: $from\n");

		return array('message'=>'success send mail','result'=>true);

	}

	require_once LIBS."PHPMailer/class.phpmailer.php";
	
	if ($from !='') $from = $from;
	else $from = $CONFIG['EMAIL_FROM_DEFAULT'];
	
	$mail = new PHPMailer(true);
	$mail->IsSMTP(); // telling the class to use SMTP

	try {
		logFile('ready to send mail');
		$mail->Host       = $CONFIG['email']['EMAIL_SMTP_HOST']; // SMTP server
		$mail->SMTPDebug  = 0;                     // enables SMTP debug information (for testing)
		$mail->SMTPAuth   = true;                  // enable SMTP authentication
		$mail->SMTPSecure = "ssl";                 // sets the prefix to the servier
		$mail->Host       = "smtp.gmail.com";      // sets GMAIL as the SMTP server
		$mail->Port       = 465;                   // set the SMTP port for the GMAIL server
		$mail->Username   = $CONFIG['email']['EMAIL_FROM_DEFAULT'];  // GMAIL username
		$mail->Password   = $CONFIG['email']['EMAIL_SMTP_PASSWORD'];            // GMAIL password
		$mail->AddAddress($to);
		$mail->SetFrom($CONFIG['email']['EMAIL_FROM_DEFAULT'], 'No Reply Account');
		$mail->Subject = "[ NOTIFICATION ] Flora Kalbar";
		$mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; // optional - MsgHTML will create an alternate automatically
		$mail->MsgHTML($msg);
		$result = $mail->Send();
		
		logFile('status send = '.$result);

		if($result) return array('message'=>'success send mail','result'=>true,'res'=>$result);
		else return array('message'=>'error mail setting','result'=>false,'res'=>$mail->ErrorInfo);
	
	}catch (phpmailerException $e) {
	  // echo $e->errorMessage(); //Pretty error messages from PHPMailer
	} catch (Exception $e) {
	  // echo $e->getMessage(); //Boring error messages from anything else!
	}

	
}


?>
