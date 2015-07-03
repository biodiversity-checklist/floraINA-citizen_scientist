<?php
if(!$_SESSION){
    header('Location: '.$basedomain);
}

class user extends Controller {
	
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
        //used for check name, twitter, and email only
        $this->loginHelper = $this->loadModel('loginHelper');
        
        $this->userHelper = $this->loadModel('userHelper');
	}
	
	function index(){
    	//return $this->loadView('home');
    }
    
    function setting(){
        $msg = $this->msg->display('all', false);
        $this->view->assign('msg', $msg);
        $ses_user = $this->isUserOnline();
        $this->view->assign('user', $ses_user);                
        return $this->loadView('user/setting');
    }
    
    function checkPassword(){
        $data = $_POST['password'];
        $ses_user = $this->isUserOnline();
        $checkPassword = $this->loginHelper->CheckPassword($ses_user['login'],$data);
        if($checkPassword){
            $return = true;
        }else{
            $return = false;
        }
        echo $return;
        exit;
    }
    
    function checkUsername(){
        $data = $_POST['username'];
        $checkUsername = $this->loginHelper->CheckUsername($data);
        if($checkUsername){
            $return = true;
        }else{
            $return = false;
        }
        echo $return;
        exit;
    }
    
    function doEditProfile(){
        $data = $_POST;
        $editProfile = $this->userHelper->editProfile($data);
        
        $ses_user = $this->isUserOnline();
        $getUserData = $this->userHelper->getUserData('id',$ses_user['login']['id']);
        $getUserappData = $this->userHelper->getUserappData('id',$ses_user['login']['id']);
        
        $data = array();
        $data[] = array('person'=>$getUserData,'person_app'=>$getUserappData);
        
        $startSession = $this->loginHelper->setSession($data,'login');
        
        if($editProfile){
            $this->msg->add('s', 'Sukses Memperbarui Data');
        }else{
            $this->msg->add('e', 'Gagal Memperbarui Data');
        }
        redirect($basedomain.'setting');
    }
    
    function doEditPassword(){
        $data = $_POST;
        
        $editPassword = $this->userHelper->editPassword($data);
        
        if($editPassword){
            $this->msg->add('s', 'Sukses Memperbarui Data');
        }else{
            $this->msg->add('e', 'Gagal Memperbarui Data');
        }
        redirect($basedomain.'setting');
    }
}

?>
