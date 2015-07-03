<?php
defined ('CODEKIR') or exit ( 'Forbidden Access' );

//------------------------------------------------------------------------------
// A session is required for the messages to work
//------------------------------------------------------------------------------
if( !session_id() ) session_start();
if(!$_SESSION){
    //header('Location: '.$basedomain);
    redirect($basedomain);
}
class onebyone extends Controller {
	
	var $models = FALSE;
	var $view;
    
    /**
     * @todo asign variable basedomain, call function load module
     * 
     * */
	public function __construct()
	{
        global $basedomain;
        $this->loadmodule();
        $this->view = $this->setSmarty();
        //$this->view->assign('basedomain',$basedomain);
        $this->msg = new Messages();
	}
    
    /**
     * @todo load database module
     * 
     * */
	public function loadmodule()
	{
        $this->insertonebyone = $this->loadModel('insertonebyone');
        
        //only used for check name, twitter, and email
        $this->loginHelper = $this->loadModel('loginHelper');
        
        //used for update image data and validate email input in insertImage function
        $this->imagezip = $this->loadModel('imagezip');
        
        //loaded to send email in update person
        $this->activityHelper = $this->loadModel('activityHelper');
	}
	
    /**
     * @todo load master view onebyone
     * 
     * */
	public function index(){
	    global $basedomain;
        $this->view->assign('msg', '');        	   
		//header('Location: '.$basedomain.'onebyone/indivContent');
        redirect($basedomain.'onebyone/indivContent');
	}
    
    /**
     * @todo show view for individu and location form
     * */
    public function indivContent(){
        $session = new Session;
        
        //$sess_user = $session->get_session();
        //$sess_data = $sess_user['ses_user'];
        $sess_data = $session->get_session();
        
        if(isset($sess_data['onebyone'])){
            $session->delete_session('onebyone');
            $session->delete_session('image_sess');
        }
        
        $msg = $this->msg->display('all', false);
        $this->view->assign('msg', $msg);
        
        //get list location
        $listlocn = $this->insertonebyone->list_locn();
        $this->view->assign('locn', $listlocn);
        
        return $this->loadView('onebyone/formContentIndiv');
    }
    
    /**
     * @todo show view for determinant, taxon, and person form
     * */
    public function detContent(){
        $msg = $this->msg->display('all', false);
        $this->view->assign('msg', $msg);
        
        //get list person
        $listPerson = $this->insertonebyone->list_person();
        $this->view->assign('person', $listPerson);
        
        //get list taxon
        $listTaxon = $this->insertonebyone->list_taxon();
        $this->view->assign('taxon', $listTaxon);
        
        //get list enum confid
        $confid_enum = $this->insertonebyone->get_enum('det','confid');
        $this->view->assign('confid_enum', $confid_enum);
        
        //get list enum subtype
        $subtype_enum = $this->insertonebyone->get_enum('taxon','subtype');
        $this->view->assign('subtype_enum', $subtype_enum);
        
        return $this->loadView('onebyone/formContentDet');
    }
    
    /**
     * @todo show view for determinant, taxon, and person form
     * */
    public function obsContent(){
        $msg = $this->msg->display('all', false);
        $this->view->assign('msg', $msg);
        
        //get list person
        $listPerson = $this->insertonebyone->list_person();
        $this->view->assign('person', $listPerson);
        
        //get list taxon
        $listTaxon = $this->insertonebyone->list_taxon();
        $this->view->assign('taxon', $listTaxon);
        
        //get list enum confid
        $habit_enum = $this->insertonebyone->get_enum('obs','habit');
        $this->view->assign('habit_enum', $habit_enum);
        
        //get list enum subtype
        $subtype_enum = $this->insertonebyone->get_enum('taxon','subtype');
        $this->view->assign('subtype_enum', $subtype_enum);
        
        return $this->loadView('onebyone/formContentObs');
    }
    
    /**
     * @todo show view for image form
     * */
    public function imageContent(){
        $msg = $this->msg->display('all', false);
        $this->view->assign('msg', $msg);
        
        $session = new Session;
        $image_sess = $session->get_session();
        if(isset($image_sess['image_sess'])){
            $this->view->assign('image_sess', $image_sess['image_sess']);
        }
        
        //get plantpart enum
        $plantpart_enum = $this->insertonebyone->get_enum('img','plantpart');
        $this->view->assign('plantpart_enum', $plantpart_enum);
        
        return $this->loadView('onebyone/formContentImage');
    }
    
    /**
     * @todo insert person from posted data
     * */
    public function insertPerson(){
        $data = $_POST;
        ob_start();
        $insertData = $this->insertonebyone->insertTransaction('person',$data);
        
        //manual submission form
        /*if($insertData){
            if($insertData['status']){
                $this->msg->add('s', 'Update Person Success');
            }else{
                $this->msg->add('e', 'Update Person Failed');
            }
        }else{
            $this->msg->add('e', 'Update Person Failed');
        }
        header('Location: ../onebyone/detContent');*/
        
        //ajax form
        
        if($insertData){
            if(isset($insertData['msg'])){
                $data['msg'] = $insertData['msg'];
            }
            if($insertData['status']){
                
                //sending email
                $email = $insertData['dataEmail'];
                
                $msg = null;
				$this->view->assign('username',$email['username']);
				$this->view->assign('email',$email['email']);
				$this->view->assign('encode',$email['encode']);
                $msg .= "<p>Hi ".$email['username']."!</p>";
                $msg .= $this->loadView('emailTemplate');
                
                // try to send mail
                $sendUserAccount = sendGlobalMail($email['to'],$email['from'],$msg,true);
                
				logFile('onebyone: generate account success '.serialize($sendUserAccount));

                ob_end_clean();

				if ($sendUserAccount['result']){
					$this->activityHelper->updateEmailLog(false,$email['email'],'account',1);
					logFile('onebyone: send account to email success');
                    
                    $data['id'] = $insertData['lastid'];
                    $data['status'] = 'success';
                    echo json_encode($data);
                    
                    //$this->insertonebyone->commitTransaction();
				}else{
				    $this->activityHelper->updateEmailLog(false,$email['email'],'account',0);
					logFile('onebyone: send account to email failed');
                    
                    $data['status'] = 'error';
                    $data['msg'] = 'Gagal mengirim akun data melalui email';
                    echo json_encode($data);
                    
                    //$this->insertonebyone->rollbackTransaction();
				}
                //sending email
            }else{
                $data['status'] = 'error';
                echo json_encode($data);
            }
        }else{
            if(isset($insertData['msg'])){
                $data['msg'] = $insertData['msg'];
            }
            $data['status'] = 'error';
            echo json_encode($data);
        }
        exit;
    }
    
    /**
     * @todo insert location from posted data
     * */
    public function insertLocation(){
        $data = $_POST;
        $insertData = $this->insertonebyone->insertTransaction('locn',$data);
        
        /*
        //manual submission form
        if($insertData){
            if($insertData['status']){
                $this->msg->add('s', 'Update Location Success');
            }else{
                $this->msg->add('e', 'Update Location Failed');
            }
        }else{
            $this->msg->add('e', 'Update Location Failed');
        }
        header('Location: ../onebyone/indivContent');*/
        
        //ajax form
        if($insertData){
            if($insertData['status']){
                $data['id'] = $insertData['lastid'];
                $data['status'] = 'success';
                echo json_encode($data);
            }else{
                $data['status'] = 'error';
                echo json_encode($data);
            }
        }else{
            $data['status'] = 'error';
            echo json_encode($data);
        }
        exit;
    }
    
    /**
     * @todo insert individu from posted data
     * */
    public function insertIndiv(){

        global $basedomain;
        $data = $_POST;
        
        //get data user from session
        $session = new Session;
        //$login = $session->get_session();
        //$userData = $login['ses_user'];
        $userData = $session->get_session();
        
        $personID = $userData['login']['id'];
        $data['personID'] = $personID;
        
        $insertData = $this->insertonebyone->insertTransaction('indiv',$data);
        
        if($insertData){
            if($insertData['status']){
                $sess_onebyone = array('indivID' => $insertData['lastid']);
                $session->set_session($sess_onebyone,'onebyone');
        
                $this->msg->add('s', 'Sukses Memperbarui Individu');
                // header('Location: ../onebyone/detContent');
                redirect($basedomain.'onebyone/detContent');
            }else{
                $this->msg->add('e', 'Gagal Memperbarui Individu');
                // header('Location: ../onebyone/indivContent');
                redirect($basedomain.'onebyone/indivContent');
            }
        }else{
            $this->msg->add('e', 'Gagal Memperbarui Individu');
            // header('Location: ../onebyone/indivContent');
            redirect($basedomain.'onebyone/indivContent');
        }
    }
    
    /**
     * @todo insert individu from posted data
     * */
    public function insertDet(){
        $data = $_POST;
        
        //get data user from session
        $session = new Session;
        //$login = $session->get_session();
        //$userData = $login['ses_user'];
        
        $userData = $session->get_session();
        
        $indivID = $userData['onebyone']['indivID'];
        
        $data['indivID'] = $indivID;
        $data['det_date'] = date("Y-m-d");
        
        $insertData = $this->insertonebyone->insertTransaction('det',$data);
        
        if($insertData){
            if($insertData['status']){
                $this->msg->add('s', 'Sukses Memperbarui Determinasi');
                redirect($basedomain.'obsContent');
                //header('Location: ../onebyone/obsContent');
            }else{
                $this->msg->add('e', 'Gagal Memperbarui Determinasi');
                redirect($basedomain.'detContent');
                //header('Location: ../onebyone/detContent');
            }
        }else{
            $this->msg->add('e', 'Gagal Memperbarui Determinasi');
            redirect($basedomain.'detContent');
            //header('Location: ../onebyone/detContent');
        }
    }
    
    /**
     * @todo insert observation from posted data
     * */
    public function insertObs(){
        $data = $_POST;
        
        //get data user from session
        $session = new Session;
        //$login = $session->get_session();
        //$userData = $login['ses_user'];
        
        $userData = $session->get_session();
        
        $personID = $userData['login']['id'];
        $indivID = $userData['onebyone']['indivID'];
        
        $data['indivID'] = $indivID;
        $data['date'] = date("Y-m-d");
        $data['personID'] = $personID;
        
        $insertData = $this->insertonebyone->insertTransaction('obs',$data);
        
        if($insertData){
            if($insertData['status']){
                $this->msg->add('s', 'Sukses Memperbarui Observasi');
                redirect($basedomain.'imageContent');
                //header('Location: ../onebyone/imageContent');
            }else{
                $this->msg->add('e', 'Gagal Memperbarui Observasi');
                redirect($basedomain.'obsContent');
                //header('Location: ../onebyone/obsContent');
            }
        }else{
            $this->msg->add('e', 'Gagal Memperbarui Observasi');
            redirect($basedomain.'obsContent');
            //header('Location: ../onebyone/obsContent');
        }
    }
    
    /**
     * @todo insert taxon from posted data
     * */
    public function insertTaxon(){
        $data = $_POST;
        $insertData = $this->insertonebyone->insertTransaction('taxon',$data);
        
        //manual submission form
        /*if($insertData){
            if($insertData['status']){
                $this->msg->add('s', 'Update Taxon Success');
            }else{
                $this->msg->add('e', 'Update Taxon Failed');
            }
        }else{
            $this->msg->add('e', 'Update Taxon Failed');
        }
        header('Location: ../onebyone/detContent');*/
        
        $data['id'] = $insertData['lastid'];
        
        //ajax form
        if($insertData){
            if($insertData['status']){
                $data['status'] = 'success';
                echo json_encode($data);
            }else{
                $data['status'] = 'error';
                echo json_encode($data);
            }
        }else{
            $data['status'] = 'error';
            echo json_encode($data);
        }
        exit;
    }
    
    /**
     * @todo insert image from posted data
     * */
    public function insertImage(){
        global $CONFIG;
        $data = $_POST;
        //pr($data);exit;
        
        $name = 'filename';
        $path = '';
        
        $uploaded_file = uploadFile($name, $path, 'image');
        
        //if uploaded
        if($uploaded_file['status'] != '0'){
            logFile('Upload Success');
            
            if (extension_loaded('gd') && function_exists('gd_info')) {
            logFile('GD2 is installed. Checking image data.');
            //validate email and get short_namecode
            /*$validateEmail = $this->validateEmail($data['email']);
            if($validateEmail['status'] != 'success'){
                $this->msg->add('e', 'Email validation Failed');
                header('Location: ../onebyone/image');
                exit;
            }
            
            $personID = $validateEmail['personID'];
            $username = $validateEmail['short_namecode'];*/
            
            
            $session = new Session;
            //$login = $session->get_session();
            //$userData = $login['ses_user'];
            
            $userData = $session->get_session();
            
            $username = $userData['login']['username'];
            $personID = $userData['login']['id'];
            $indivID = $userData['onebyone']['indivID'];
        
            $tmp_name = $uploaded_file['full_name'];
            $entry = str_replace(array('\'', '"'), '', $uploaded_file['real_name']);
            //$entry = $uploaded_file['real_name'];
            $image_name_encrypt = md5(str_shuffle($CONFIG['default']['salt'].$entry));
            
            //check filename
            $dataExist = $this->imagezip->dataExist($personID, $entry);            
            
            $path_entry = $CONFIG['default']['upload_path'];
            $src_tmp = $path_entry."/".$tmp_name;
            
            if(!$dataExist){
                
                logFile('Prepare to cropping image');
                
                $path_data = 'public_assets/';
                //$path_user = $path_data.$username;
                $path_img = $path_data.'/img';
                $path_img_1000px = $path_img.'/1000px';
                $path_img_500px = $path_img.'/500px';
                $path_img_100px = $path_img.'/100px';
                
                $fileinfo = getimagesize($path_entry.'/'.$tmp_name);
                
                $toCreate = array($path_img, $path_img_1000px, $path_img_500px, $path_img_100px);
                createFolder($toCreate, 0755);
                
                copy($path_entry."/".$tmp_name, $path_img_1000px.'/'.$image_name_encrypt.'.1000px.jpg');
                if(!@ copy($path_entry."/".$tmp_name, $path_img_1000px.'/'.$image_name_encrypt.'.1000px.jpg')){
                    logFile('Copy file failed');
                    $status = "error";
                    $msg= error_get_last();
                }
                else{
                    logFile('Copy file success');
                    $dest_1000px = $CONFIG['default']['root_path'].'/'.$path_img_1000px.'/'.$image_name_encrypt.'.1000px.jpg';
                    $dest_500px = $CONFIG['default']['root_path'].'/'.$path_img_500px.'/'.$image_name_encrypt.'.500px.jpg';
                    $dest_100px = $CONFIG['default']['root_path'].'/'.$path_img_100px.'/'.$image_name_encrypt.'.100px.jpg';
                    
                    if ($fileinfo[0] >= 1000 || $fileinfo[1] >= 1000 ) {
                        if ($fileinfo[0] > $fileinfo[1]) {
                            $percentage = (1000/$fileinfo[0]);
                            $config['width'] = $percentage*$fileinfo[0];
                            $config['height'] = $percentage*$fileinfo[1];
                        }else{
                            $percentage = (1000/$fileinfo[1]);
                            $config['width'] = $percentage*$fileinfo[0];
                            $config['height'] = $percentage*$fileinfo[1];
                        }
                        
                        $this->resize_pic($src_tmp, $dest_1000px, $config);
                        unset($config);
                    }
                    
                    logFile('Cropping to 1000px image');
                    //Set cropping for y or x axis, depending on image orientation
                    if ($fileinfo[0] > $fileinfo[1]) {
                        $config['width'] = $fileinfo[1];
                        $config['height'] = $fileinfo[1];
                        $config['x_axis'] = (($fileinfo[0] / 2) - ($config['width'] / 2));
                        $config['y_axis'] = 0;
                    }
                    else {
                        $config['width'] = $fileinfo[0];
                        $config['height'] = $fileinfo[0];
                        $config['x_axis'] = 0;
                        $config['y_axis'] = (($fileinfo[1] / 2) - ($config['height'] / 2));
                    }

                    $this->cropToSquare($src_tmp, $dest_500px, $config);
                    unset($config);
                    
                    logFile('Cropping to square image');
                    
                    //set new config
                    $config['width'] = 500;
                    $config['height'] = 500;
                    $this->resize_pic($dest_500px, $dest_500px, $config);
                    unset($config);
                    
                    logFile('Cropping to 500px image');
                    
                    $config['width'] = 100;
                    $config['height'] = 100;
                    $this->resize_pic($dest_500px, $dest_100px, $config);
                    unset($config);
                    
                    logFile('Cropping to 100px image');
                    
                    //add file information to array
                    /*$fileToInsert = array('filename' => $entry,'md5sum' => $image_name_encrypt, 'directory' => '', 'mimetype' => $fileinfo['mime']);
                    
                    $insertImage = $this->imagezip->updateImage($personID, $fileToInsert);*/
                    
                    $data['filename'] = $entry;
                    $data['md5sum'] = $image_name_encrypt;
                    $data['mimetype'] = $fileinfo['mime'];
                    $data['indivID'] = $indivID;
                    $data['personID'] = $personID;
                    
                    $insertData = $this->insertonebyone->insertTransaction('img',$data);
                    
                    if($insertData){
                        logFile('Insert Data Success');
                        $this->msg->add('s', 'Sukses Memperbarui Data Gambar');
                        $session = new Session;
                        
                        $dataSession = array();
                        
                        //$sess_image = $session->get_session();
                        //$sess_user = $sess_image['ses_user'];
                        
                        $sess_user = $session->get_session();
                        
                        if(isset($sess_user['image_sess'])){
                            logFile('Fetch image session');
                            foreach ($sess_user['image_sess'] as $data_before){
                                array_push($dataSession,$data_before);
                            }
                        }
                        array_push($dataSession, $data);
                        $session->set_session($dataSession,'image_sess');
                        //$session->delete_session('onebyone');
                    }else{
                        logFile('Insert Data Failed');
                        $this->msg->add('e', 'Gagal Memperbarui Data Gambar');
                    }
                } // end if copy
                
            }else{
                logFile('File Image exist');
                $this->msg->add('e', 'Data Gambar Telah Ada');
            }
            unlink($src_tmp);
            }else{
                logFile('GD2 is not installed');
                $this->msg->add('e', 'Sistem Error. Harap menghubungi tim developer kami.');
            }
        }else{
            logFile('Upload Image Failed');
            $this->msg->add('e', $uploaded_file['message']);
        }
        redirect($basedomain.'imageContent');
        //header('Location: ../onebyone/imageContent');
    }
    
    /**
     * @todo check input name exist from input
     * 
     * @return boolean true/false
     * */
    public function check_Name(){
        $data = $_POST['name'];
        $check = $this->loginHelper->checkName($data);
        if($check){
            $return = true;
        }else{
            $return = false;
        }
        echo $return;
        exit;
    }
    
    /**
     * @todo check input twitter exist from input
     * 
     * @return boolean true/false
     * */
    public function check_Twitter(){
        $data = $_POST['twitter'];
        $check = $this->loginHelper->checkTwitter($data);
        if($check){
            $return = true;
        }else{
            $return = false;
        }
        echo $return;
        exit;
    }
    
    /**
     * @todo check input email exist from input
     * 
     * @return boolean true/false
     * */
    public function check_Email(){
        $return = false;
    
        $data = $_POST['email'];
        $check = $this->loginHelper->checkEmail($data);
        
        if($check){
            $return = true;
        }else{
            $return = false;
        }
        echo $return;
        exit;
    }
    
    /**
     * @todo get id and shortname of user
     * 
     * @param email = email from user input
     * @return status = a status of success/error validate
     * @return short_namecode = short name of user (if success)
     * @return personID = id for person (if success)
     * 
     * */
    function validateEmail($email){
        $validate = $this->imagezip->validateEmail($email);
        if($validate['id'] != ''){
            $return = array('status' => "success", 'short_namecode' => $validate['short_namecode'], 'personID' => $validate['id']);
        }else{
            $return = array('status' => "error", 'short_namecode' => $validate['short_namecode'], 'personID' => $validate['id']);
        }  
        return $return;
        exit;
    }
    
    /**
     * @todo crop image to square from center
     * 
     * @param string $src = full image path with file name
     * @param string $dest = path destination for new image
     * @param array $config = array contain configuration to crop image
     * 
     * @param int $config['width']
     * @param int $config['height']
     * @param int $config['x_axis']
     * @param int $config['y_axis']
     * 
     * @return bool Returns TRUE on success, FALSE on failure
     * 
     * */
    function cropToSquare($src, $dest, $config){
        list($current_width, $current_height) = getimagesize($src);
        $canvas = imagecreatetruecolor($config['width'], $config['height']);
        $current_image = imagecreatefromjpeg($src);
        if (!@ imagecopy($canvas, $current_image, 0, 0, $config['x_axis'], $config['y_axis'], $current_width, $current_height)){
            return false;
        }else{
            if (!@ imagejpeg($canvas, $dest, 100)){
                return false;
            }else{
                return true;
            }
        }
    }
    
    /**
     * @todo resize image
     * 
     * @param string $src = full image path with file name
     * @param string $dest = path destination for new image
     * @param array $config = array contain configuration to crop image
     * 
     * @param int $config['width']
     * @param int $config['height']
     * 
     * @return bool Returns TRUE on success, FALSE on failure
     * 
     * */
    function resize_pic($src, $dest, $config){
        list($current_width, $current_height) = getimagesize($src);
        $canvas = imagecreatetruecolor($config['width'], $config['height']);
        $current_image = imagecreatefromjpeg($src);
        
        // Resize
        if (!@ imagecopyresized($canvas, $current_image, 0, 0, 0, 0, $config['width'], $config['height'], $current_width, $current_height)){
            return false;
        }else{
            // Output
            if (!@ imagejpeg($canvas, $dest, 100)){
                return false;
            }else{
                return true;
            }
        }
    }
    
    /*function autoTaxon(){
        $like = $_POST['autoTaxon'];
        $taxons = $this->insertonebyone->list_autoTaxon($like);
        
        $auto = array();
        
        foreach($taxons as $taxon){
            if($taxon['gen']){
                if($taxon['fam']){
                    $auto[] = array('id' => $taxon['id'], 'label' => '('.$taxon['family'].' '.$taxon['gen'].' '.$taxon['sp']);
                }else{
                    $auto[] = array('id' => $taxon['id'], 'label' => $taxon['gen'].' '.$taxon['sp']);
                }
            }elseif($taxon['morphotype']){
                $auto[] = array('id' => $taxon['id'], 'label' => $taxon['morphotype']);
            }
        }
        echo json_encode($auto);
        exit;
    }*/
    
    /**
     * @todo get list of taxon from posted data
     * 
     * @return array $auto = list taxon
     * 
     * */
    function autoTaxon(){
        $like = $_POST['autoTaxon'];
        $taxons = $this->insertonebyone->list_autoTaxon($like);
        
        $auto = array();
        
        foreach($taxons as $taxon){
            if($taxon['taxonName']){
                $auto[] = array('id' => $taxon['kewid'], 'label' => $taxon['taxonName']);
            }
        }
        echo json_encode($auto);
        exit;
    }
    
    /**
     * @todo get list of family from plantlist
     * 
     * @return array $auto = list family
     * 
     * */
    function autoFamily(){
        $data = $_POST;
        $field = 'family';
        $taxons = $this->insertonebyone->list_autoTaxon($field,$data);
        
        $auto = array();
        
        foreach($taxons as $taxon){
            if($taxon[$field]){
                $auto[] = array('id' => $taxon['kewid'], 'label' => $taxon[$field]);
            }
        }
        echo json_encode($auto);
        exit;
    }
    
    /**
     * @todo get list of genus from plantlist
     * 
     * @return array $auto = list genus
     * 
     * */
    function autoGenus(){
        $data = $_POST;
        $field = 'genus';
        $taxons = $this->insertonebyone->list_autoTaxon($field,$data);
        
        $auto = array();
        
        foreach($taxons as $taxon){
            if($taxon[$field]){
                $auto[] = array('id' => $taxon['kewid'], 'label' => $taxon[$field]);
            }
        }
        echo json_encode($auto);
        exit;
    }
    
    /**
     * @todo get list of species from plantlist
     * 
     * @return array $auto = list species
     * 
     * */
    function autoSpecies(){
        $data = $_POST;
        $field = 'species';
        $taxons = $this->insertonebyone->list_autoTaxon($field,$data);
        
        $auto = array();
        
        foreach($taxons as $taxon){
            if($taxon[$field]){
                $auto[] = array('id' => $taxon['kewid'], 'label' => $taxon[$field]);
            }
        }
        echo json_encode($auto);
        exit;
    }
}

?>
