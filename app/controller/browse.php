<?php

class browse extends Controller {
	
	var $models = FALSE;
	var $view;

	
	function __construct()
	{
    	global $basedomain;
    	$this->loadmodule();
    	$this->view = $this->setSmarty();
    	$this->view->assign('basedomain',$basedomain);
        $this->msg = new Messages();
    }
	
	function loadmodule()
	{
        $this->insertonebyone = $this->loadModel('insertonebyone');
        $this->browseHelper = $this->loadModel('browseHelper');
        //used for update image data and validate email input in insertImage function
        $this->imagezip = $this->loadModel('imagezip');
	}
	
	function index(){
    }
    
    /**
     * @todo show all taxon
     * 
     */
    function dataTaxon(){
        $listAll = array();
        
        //Get all data taxon
        $taxon = $this->browseHelper->dataTaxon(false,'','');
        
        for($i=0;$i<count($taxon['result']);$i++){
            //Get taxon's 'images
            $img = $this->browseHelper->showImgTaxon($taxon['result'][$i]['id']);
            $listAll[]= array('taxon'=>$taxon['result'][$i],'img'=>$img);
        }   
        
        if(empty($listAll)){
            $this->view->assign('noData','empty');
        }
        else{
            $this->view->assign('noData','data existed');
        }
        
        //$this->view->assign('pageno',$taxon['pageno']);
        //$this->view->assign('lastpage',$taxon['lastpage']);
        $this->view->assign('data',$listAll);
        return $this->loadView('browse/allTaxon');
    }
    
    /**
     * @todo show all location
     * 
     */
    function dataLocation(){
        $listAll = array();
        
        //Get all data location
        $location = $this->browseHelper->dataLocation(false,'','');
        
        if(empty($location)){
            $this->view->assign('noData','empty');
        }
        else{
            $this->view->assign('noData','data existed');
        }
        
        //$this->view->assign('pageno',$location['pageno']);
        //$this->view->assign('lastpage',$location['lastpage']);
        $this->view->assign('result',$location['result']);
        return $this->loadView('browse/allLocation');
    }
    
    /**
     * @todo show all person
     * 
     */
    function dataPerson(){
        $listAll = array();
        
        //Get all data person
        $person = $this->browseHelper->dataPerson(false,'','');
        
        if(empty($person)){
            $this->view->assign('noData','empty');
        }
        else{
            $this->view->assign('noData','data existed');
        }
        
        //$this->view->assign('pageno',$person['pageno']);
        //$this->view->assign('lastpage',$person['lastpage']);
        $this->view->assign('result',$person['result']);
        return $this->loadView('browse/allPerson');
    }
    
    /**
     * @todo show all indiv from selected taxon/location/person
     * 
     */
    function indiv(){
        $id = $_GET['id'];
        $action = $_GET['action'];
        
        if($action=='indivTaxon'){
            //get taxon name
            $title = $this->browseHelper->dataTaxon(true,'id',$id);
            //get data indiv
            $getIndiv = $this->browseHelper->dataIndiv($action,'taxonID',$id);
        }
        if($action=='indivLocn'){
            $title='';
            //get data indiv
            $getIndiv = $this->browseHelper->dataIndiv($action,'locnID',$id);
        }
        if($action=='indivPerson'){
            $title='';
            //get data indiv
            $getIndiv = $this->browseHelper->dataIndiv($action,'personID',$id);
        }
        $listAll = array();
        for($i=0;$i<count($getIndiv['result']);$i++){
            //Get indiv's 'images
            $img = $this->browseHelper->showImgIndiv($getIndiv['result'][$i]['indivID'],true,'0,5');
            $listAll[]= array('indiv'=>$getIndiv['result'][$i],'img'=>$img);
        }
        
        if(empty($listAll)){
            $this->view->assign('noData','empty');
        }
        else{
            $this->view->assign('noData','data existed');
        }
        //$this->view->assign('pageno',$getIndiv['pageno']);
        //$this->view->assign('lastpage',$getIndiv['lastpage']);        
        $this->view->assign('title',$title);
        $this->view->assign('data',$listAll);
        return $this->loadView('browse/allIndiv');
    }
    
    /**
     * @todo show all indiv from selected taxon/location/person
     * 
     */
    function allPost(){
        $id = $_GET['id'];
        $action = 'indivPerson';
        
        $title='';
        //get data indiv
        $getIndiv = $this->browseHelper->dataIndiv($action,'personID',$id);
        
        $listAll = array();
        for($i=0;$i<count($getIndiv['result']);$i++){
            //Get indiv's 'images
            $img = $this->browseHelper->showImgIndiv($getIndiv['result'][$i]['indivID'],true,'0,5');
            $listAll[]= array('indiv'=>$getIndiv['result'][$i],'img'=>$img);
        }
        
        if(empty($listAll)){
            $this->view->assign('noData','empty');
        }
        else{
            $this->view->assign('noData','data existed');
        }
        //$this->view->assign('pageno',$getIndiv['pageno']);
        //$this->view->assign('lastpage',$getIndiv['lastpage']);        
        $this->view->assign('title',$title);
        $this->view->assign('data',$listAll);
        return $this->loadView('browse/allPost');
    }
    
    /**
     * @todo show all detail indiv from selected indiv
     * 
     */
    function indivDetail(){
        $indivID = $_GET['id'];
        //get whole data indiv detail
        $indivDetail = $this->browseHelper->detailIndiv($indivID);
        //get determinant from selected indiv
        $indivDeterminant = $this->browseHelper->dataDetIndiv($indivID);
        //get all images from indiv selected
        $indivImages = $this->browseHelper->showImgIndiv($indivID,false,'');
        //get all observations from indiv selected
        $indivObs = $this->browseHelper->dataObsIndiv($indivID);
        
        //get list enum habit
        $habit_enum = $this->insertonebyone->get_enum('obs','habit');
        $this->view->assign('habit_enum', $habit_enum);
        
        //get list person
        $listPerson = $this->insertonebyone->list_person();
        $this->view->assign('person', $listPerson);
        
        //get list taxon
        $listTaxon = $this->insertonebyone->list_taxon();
        $this->view->assign('taxon', $listTaxon);
        
        //get list enum confid
        $confid_enum = $this->insertonebyone->get_enum('det','confid');
        $this->view->assign('confid_enum', $confid_enum);
        
        if(empty($indivDetail)){
            $this->view->assign('noData','empty');
        }
        else{
            $this->view->assign('noData','data existed');
        }
        
        $msg = $this->msg->display('all', false);
        $this->view->assign('msg', $msg);
        
        $this->view->assign('indiv',$indivDetail);
        $this->view->assign('indivID',$indivID);
        $this->view->assign('det',$indivDeterminant);
        $this->view->assign('img',$indivImages);
        $this->view->assign('obs',$indivObs);
        $ses_user = $this->isUserOnline();
        $this->view->assign('user', $ses_user); 
        return $this->loadView('browse/indivDetail');
    }
    
    /**
     * @todo edit individual view
     * 
     */
    function editIndiv(){
        //get data user from session
        $ses_user = $this->isUserOnline();
        global $basedomain;
        if(!$ses_user){
            redirect($basedomain);
            exit;
        }
        $indivID = $_GET['id'];
        //get whole data indiv detail
        $indivDetail = $this->browseHelper->detailIndiv($indivID);
        //get determinant from selected indiv
        $indivDeterminant = $this->browseHelper->dataDetIndiv($indivID);
        //get all observations from indiv selected
        $indivObs = $this->browseHelper->dataObsIndiv($indivID);
        //get all images from indiv selected
        $indivImages = $this->browseHelper->showImgIndiv($indivID,false,'');
        
        if(empty($indivDetail)){
            $this->view->assign('noData','empty');
        }
        else{
            $this->view->assign('noData','data existed');
        }
        //Get list location
        $listlocn = $this->insertonebyone->list_locn();
        $this->view->assign('locn', $listlocn);
        
        //get list person
        $listPerson = $this->insertonebyone->list_person();
        $this->view->assign('person', $listPerson);
        
        //get list taxon
        $listTaxon = $this->insertonebyone->list_taxon();
        $this->view->assign('taxon', $listTaxon);
        
        //get list enum confid
        $confid_enum = $this->insertonebyone->get_enum('det','confid');
        $this->view->assign('confid_enum', $confid_enum);
        
        //get list enum habit
        $habit_enum = $this->insertonebyone->get_enum('obs','habit');
        $this->view->assign('habit_enum', $habit_enum);
        
        //get plantpart enum
        $plantpart_enum = $this->insertonebyone->get_enum('img','plantpart');
        $this->view->assign('plantpart_enum', $plantpart_enum);
        
        $msg = $this->msg->display('all', false);
        $this->view->assign('msg', $msg);
        
        $this->view->assign('user', $ses_user); 
        $this->view->assign('indivID',$indivID);
        $this->view->assign('obs',$indivObs);
        $this->view->assign('img',$indivImages);
        $this->view->assign('indiv',$indivDetail);
        $this->view->assign('det',$indivDeterminant);
        return $this->loadView('editIndiv/editIndiv');
    }
    
    
    /**
     * @todo edit Indiv proccess
     * 
     */
    function doEditIndiv(){
        $data = $_POST;
        
        //get data user from session
        $ses_user = $this->isUserOnline();
        
        $idIndiv = $_GET['id'];
        $personID = $ses_user['login']['id'];
        $data['personID'] = $personID;
        
        //pr($idIndiv);exit;
        $updateData = $this->browseHelper->updateIndiv($data,$idIndiv);
        
        if($updateData){
            $this->msg->add('s', 'Sukses Memperbarui Individu');
        }else{
            $this->msg->add('e', 'Gagal Memperbarui Individu');
        }
        
        //header('Location: ../../browse/editIndiv/?id='.$idIndiv);
        redirect($basedomain.'../editIndiv/?id='.$idIndiv);
    }
    
    /**
     * @todo update all indiv selected n_status into '1'
     * 
     */
    function deleteIndiv(){
        $idIndiv = $_GET['id'];
        $data['indivID'] = $idIndiv;        
        $deleteIndiv = $this->browseHelper->deleteIndiv('','indiv','id',$data);
        $deleteColl = $this->browseHelper->deleteIndiv('','coll','indivID',$data);
        $deleteObs = $this->browseHelper->deleteIndiv('','obs','indivID',$data);
        $deleteImg = $this->browseHelper->deleteImgIndiv($idIndiv);
        $deleteDet = $this->browseHelper->deleteIndiv('','det','indivID',$data);
        
        if($deleteIndiv && $deleteColl && $deleteObs && $deleteImg && $deleteDet){
            $this->msg->add('s', 'Sukses Menghapus Individu');
        }else{
            $this->msg->add('e', 'Gagal Menghapus Individu');
        }
        
        //header('Location: ../../browse/indivDetail/?id='.$idIndiv);
        redirect($basedomain.'../indivDetail/?id='.$idIndiv);
    }
    
    /**
     * @todo update all det selected n_status into '1'
     * 
     */
    function deleteDet(){
        $idIndiv = $_GET['indivID'];
        $idDet = $_GET['id'];
        $data['indivID'] = $idIndiv;
        $data['id'] = $idDet;
        $deleteDet = $this->browseHelper->deleteIndiv('AND','det','indivID',$data);
        
        if($deleteDet){
            $this->msg->add('s', 'Sukses Menghapus Determinasi');
        }else{
            $this->msg->add('e', 'Gagal Menghapus Determinasi');
        }
        
        if($_GET['action']=='delDetOwn'){
            //header('Location: ../../browse/editIndiv/?id='.$idIndiv);
            redirect($basedomain.'../editIndiv/?id='.$idIndiv.'#det');
        }
        else{
            //header('Location: ../../browse/indivDetail/?id='.$idIndiv);
            redirect($basedomain.'../indivDetail/?id='.$idIndiv.'#det');
        }
        
    }
    
    /**
     * @todo update all det selected n_status into '1'
     * 
     */
    function deleteObs(){
        $idIndiv = $_GET['indivID'];
        $idObs = $_GET['id'];
        $data['indivID'] = $idIndiv;
        $data['id'] = $idObs;
        $deleteDet = $this->browseHelper->deleteIndiv('AND','obs','indivID',$data);
        
        if($deleteDet){
            $this->msg->add('s', 'Sukses Menghapus Observasi');
        }else{
            $this->msg->add('e', 'Gagal Menghapus Observasi');
        }
        
        if($_GET['action']=='delObsOwn'){
            //header('Location: ../../browse/editIndiv/?id='.$idIndiv);
            redirect($basedomain.'../editIndiv/?id='.$idIndiv.'#obs');
        }
        else{
            //header('Location: ../../browse/indivDetail/?id='.$idIndiv);
            redirect($basedomain.'../indivDetail/?id='.$idIndiv.'#obs');
        }
    }
    
    /**
     * @todo delete all img selected
     * 
     */
    function deleteImg(){
        $idIndiv = $_GET['indivID'];
        $data['indivID'] = $idIndiv;
        $data['id'] = $_POST['idImg'];
        $deleteImg = $this->browseHelper->deleteImg($data);
        
        if($deleteImg){
            $this->msg->add('s', 'Sukses Menghapus Gambar');
        }else{
            $this->msg->add('e', 'Gagal Menghapus Gambar');
        }
        
        if($_GET['action']=='delImgOwn'){
            //header('Location: ../../browse/editIndiv/?id='.$idIndiv);
            redirect($basedomain.'../editIndiv/?id='.$idIndiv.'#img');
        }
        else{
            //header('Location: ../../browse/indivDetail/?id='.$idIndiv);
            redirect($basedomain.'../indivDetail/?id='.$idIndiv.'#img');
        }
    }
    
    /**
     * @todo insert determinant from posted data
     * */
    public function addDet(){
        $data = $_POST;
        
        $data['indivID'] = $_GET['id'];
        $data['det_date'] = date("Y-m-d");
        
        $insertData = $this->insertonebyone->insertTransaction('det',$data);
        
        if($insertData){
            $this->msg->add('s', 'Sukses Menambah Determinasi');
        }else{
            $this->msg->add('e', 'Gagal Menambah Determinasi');
        }
        
        if($_GET['action']=='addOnly'){
            //header('Location: ../../browse/indivDetail/?id='.$data['indivID']);
            redirect($basedomain.'../indivDetail/?id='.$data['indivID'].'#det');
        }
        else{
            //header('Location: ../../browse/editIndiv/?id='.$data['indivID']);
            redirect($basedomain.'../editIndiv/?id='.$data['indivID'].'#det');
        }
    }
    
    /**
     * @todo insert observation from posted data
     * */
    public function addObs(){
        $data = $_POST;
        $ses_user = $this->isUserOnline(); 
        $personID = $ses_user['login']['id'];
        
        $data['personID'] = $personID;  
        $data['date'] = date("Y-m-d");     
        $data['indivID'] = $_GET['id'];
        
        $insertData = $this->insertonebyone->insertTransaction('obs',$data);
        
        if($insertData){
            $this->msg->add('s', 'Sukses Menambah Observasi');
        }else{
            $this->msg->add('e', 'Gagal Menambah Observasi');
        }
        
        if($_GET['action']=='addOnly'){
            //header('Location: ../../browse/indivDetail/?id='.$data['indivID']);
            redirect($basedomain.'../indivDetail/?id='.$data['indivID'].'#obs');
        }
        else{
            //header('Location: ../../browse/editIndiv/?id='.$data['indivID']);
            redirect($basedomain.'../editIndiv/?id='.$data['indivID'].'#obs');
        }
        exit;
    }
    
    /**
     * @todo insert image from posted data
     * */
    public function addImg(){
        global $CONFIG;
        $data = $_POST;
        $indivID = $_GET['id'];
        
        $name = 'filename';
        $path = '';
        
        $uploaded_file = uploadFile($name, $path, 'image');
        
        //if uploaded
        if($uploaded_file['status'] != '0'){
            logFile('Upload Success');
            
            if (extension_loaded('gd') && function_exists('gd_info')) {
            logFile('GD2 is installed. Checking image data.');
            //validate email and get short_namecode
            $ses_user = $this->isUserOnline(); 
            $username = $ses_user['login']['username'];
            $personID = $ses_user['login']['id'];
        
            $tmp_name = $uploaded_file['full_name'];
            $entry = str_replace(array('\'', '"'), '', $uploaded_file['real_name']);
            $image_name_encrypt = md5($entry);
            
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
                    $data['filename'] = $entry;
                    $data['md5sum'] = $image_name_encrypt;
                    $data['mimetype'] = $fileinfo['mime'];
                    $data['indivID'] = $indivID;
                    $data['personID'] = $personID;
                    
                    $insertData = $this->insertonebyone->insertTransaction('img',$data);
                    
                    if($insertData){
                        logFile('Insert Data Success');
                        $this->msg->add('s', 'Sukses Memperbarui Gambar');
                        $session = new Session;
                        
                        $dataSession = array();
                        
                        $sess_image = $session->get_session();
                        if(isset($sess_image['image_sess'])){
                            logFile('Fetch image session');
                            foreach ($sess_image['image_sess'] as $data_before){
                                array_push($dataSession,$data_before);
                            }
                        }
                        array_push($dataSession, $data);
                        $session->set_session($dataSession,'image_sess');
                        //$session->delete_session('onebyone');
                    }else{
                        logFile('Insert Data Failed');
                        $this->msg->add('e', 'Gagal Memperbarui Gambar');
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
        //header('Location: ../../browse/editIndiv/?id='.$indivID);
        redirect($basedomain.'../editIndiv/?id='.$indivID.'#img');
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
     * @todo search from table taxon
     * 
     */
    function searchTaxon(){
        $data=$_GET['search'];
        
        $search=$this->browseHelper->search('taxon',$data);
        
        if(empty($search)){
            $this->view->assign('noData','empty');
        }
        else{
            $totalSearch = count($search['countdata']);
            $this->view->assign('noData',$totalSearch);
        }
        $this->view->assign('pageno',$search['pageno']);
        $this->view->assign('lastpage',$search['lastpage']);
        $this->view->assign('data',$search['result']);
        return $this->loadView('Search/searchTaxon');       
    }
    
    /**
     * @todo search from table taxon
     * 
     */
    function searchLocn(){
        $data=$_GET['search'];
        
        $search=$this->browseHelper->search('locn',$data);
        
        if(empty($search)){
            $this->view->assign('noData','empty');
        }
        else{
            $totalSearch = count($search['countdata']);
            $this->view->assign('noData',$totalSearch);
        }
        $this->view->assign('pageno',$search['pageno']);
        $this->view->assign('lastpage',$search['lastpage']);
        $this->view->assign('data',$search['result']);
        return $this->loadView('Search/searchLocn');       
    }
    
    /**
     * @todo search from table taxon
     * 
     */
    function searchPerson(){
        $data=$_GET['search'];
        
        $search=$this->browseHelper->search('person',$data);
        
        if(empty($search)){
            $this->view->assign('noData','empty');
        }
        else{
            $totalSearch = count($search['countdata']);
            $this->view->assign('noData',$totalSearch);
        }
        $this->view->assign('pageno',$search['pageno']);
        $this->view->assign('lastpage',$search['lastpage']);
        $this->view->assign('data',$search['result']);
        return $this->loadView('Search/searchPerson');       
    }
	
}

?>
