<?php

class login extends Controller {
	
	var $models = FALSE;
	var $sessi = null;
	
	public function __construct()
	{
		$this->loadmodule();
		$this->setSmarty();
	}
	public function loadmodule()
	{
		$this->loginHelper = $this->loadModel('loginHelper');
		$this->userHelper = $this->loadModel('userHelper');
		
	}
	
	
	
	public function index()
	{
		global $CONFIG;
		echo 'ada';
		return $this->loadView('login');
	}
	
	function local()
	{
		global $CONFIG,$basedomain;
		
		
		if (_p('token')){
			
			// echo $this->loadView('login-loader');
			
			
			$data = $_POST;
        
	        //query data
	        $getUserappData = $this->userHelper->getUserappData('username',$data['username']);
	        $getUserData = $this->userHelper->getUserData('id',$getUserappData['id']);
	        pr($data);
	        $pwd = $data['password'];
	        
	        if(count($getUserData['id'])==1){ 
	            $checkPassword = $this->loginHelper->checkPassword($getUserData,$pwd);
	            if($checkPassword){
	                // echo json_encode('success');
	                $data = array();
	                $data[] = array('person'=>$getUserData,'person_app'=>$getUserappData);
	                $startSession = $this->loginHelper->setSession($data, $pwd);

	                redirect($basedomain.'home');
	                // header('location:home');
	                exit;
	            }
	            else{
	                echo json_encode('error1');
	            }
	        }
			else{
				echo json_encode('error2');
			}
	        exit; 
		}
		
		return $this->loadView('login');
	}
	
	function register()
	{
		global $CONFIG;
		$data['username'] = _p('email');
		$data['name'] = _p('name');
		$data['email'] = _p('email');
		$data['salt'] = 1234567890;

		if($_POST['password']!=''){
			$data['password'] = sha1(_P('password').$data['salt']);;
		}
		$getUser = $this->loginHelper->createUser($data);
		if ($getUser){
			echo "<script>alert('Data anda akan segera di proses oleh admin');window.location.href='".$CONFIG['admin']['base_url']."'</script>";
		}
		return false;
	}
	
	public function checkuser(){
		$new_user = $_POST['new_user'];

		$result = $this->loginHelper->user_check($new_user);

		if($result[0]['count'] > 0){
			$getData = array('status'=>"1");
		} else {
			$getData = array('status'=>"0");
		}
		
		echo json_encode($getData);
		
		exit;
	}
  
}

?>
