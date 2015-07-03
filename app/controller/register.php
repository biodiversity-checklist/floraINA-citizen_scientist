<?php

class register extends Controller {
	
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
        $this->activityHelper = $this->loadModel('activityHelper');
        
	}
	
	function index(){
        global $DATA;

        // pr($DATA);
    	return $this->loadView('register');
    }
    
    function forgotPassword()
    {
        global $CONFIG;

        $token = _p('token');
        $this->view->assign('status',true);
        if ($token){

        	// pr($_POST);
        	$getToken = $this->loginHelper->getUserEmail(_p('email'), true);
            if ($getToken){

                // send mail before activate account
                $dataArr['email'] = $getToken['email'];
                $dataArr['username'] = $getToken['username'];
                $dataArr['password'] = $getToken['password'];
                $dataArr['token'] = sha1('reset'.$getToken['email']);
                $dataArr['validby'] = $getToken['email_token'];
                $dataArr['regfrom'] = 1;
                $dataArr['reset'] = 1;

                $inflatData = encode(serialize($dataArr));
                logFile($inflatData);


                $to = $getToken['email'];
                $from = $CONFIG['email']['EMAIL_FROM_DEFAULT'];
                // $msg = "To activate your account please <a href='{$basedomain}login/validate/?ref={$inflatData}'>click here</a>";
                $this->view->assign('email',$getToken['email']);
                $this->view->assign('username',$getToken['username']);
                $this->view->assign('encode',$inflatData);
                $this->view->assign('content',"reset");
                $msg = "<p>Hi ".$getToken['username']."!</p>";
                $msg .= $this->loadView('emailTemplate');
                // try to send mail 

                // pr($getToken);
                // exit;
                $sendMail = sendGlobalMail($to, $from, $msg,true);
                logFile('mail reset account send '.serialize($sendMail));
                $this->view->assign('status',true);
            }else{
                $this->view->assign('status',false);
            }
            

            // $this->activityHelper->updateEmailLog(false,$to,'account',0);


        	/*$verifiedData = $this->loginHelper->resetAccount($to);
        	if ($verifiedData){
        		echo 'true';
        	}*/
        }

        return $this->loadView('forgot-password');
    }

}

?>
