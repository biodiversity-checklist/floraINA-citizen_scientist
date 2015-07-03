<?php

class activate extends Controller {
	
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


    	return $this->loadView('home');
    }
    
    function validate()
    {

        $data = _g('ref');
        
        // exit;
        logFile($data);
        if ($data){

            $decode = unserialize(decode($data));
           
            // check if token is valid
           
            $salt = "register";
            $userMail = $decode['email'];
            $origToken = sha1($salt.$userMail);

            $token = sha1('reset'.$decode['email']);

            $getToken = $this->loginHelper->getEmailToken($decode['username']);
            
            if ($getToken['email_token']==$decode['validby']){

                if ($decode['reset']){

                    $msg = $this->msg->display('all', false);
                    $this->view->assign('msg', $msg);
                    $ses_user = $this->loginHelper->getUserEmail($userMail, true);
                    $newData['login'] = $ses_user;
                    $this->view->assign('user', $newData);
                    $this->view->assign('reset', true);

                    return $this->loadView('user/reset');

                }else{

                    if ($decode['token']==$origToken){
                        // is valid, then create account and set status to validate

                        if($decode['regfrom']==1){
                            
                            $this->view->assign('enterAccount',false);  
                            $updateAccount = $this->loginHelper->updateUserStatus($decode['username']);

                            if ($updateAccount){

                                $this->activityHelper->updateEmailLog(true, $userMail,'account',2);

                                $dataUSer['username'] = $decode['username'];
                                $dataUSer['password'] = $decode['password']; 
                                
                                createAccount($dataUSer);
                                logFile('account ftp user '.$decode['email']. ' created');

                                $this->view->assign('validate','Validate account success');
                                

                            }else{
                                
                                $this->view->assign('validate','Validate account error');
                                logFile('update n_status user '.$decode['email'].' failed');
                            }

                        }else{

                            $this->view->assign('email',$decode['email']);
                            $this->view->assign('enterAccount',true);         
                            return $this->loadView('validateProfile');
                        }

                    }else{

                        // invalid token
                        $this->view->assign('validate','Validate account error');
                        logFile('token mismatch');
                    }   
                }

                

            }else{

                // invalid token
                $this->view->assign('validate','Validate account error');
                logFile('token mismatch');
            }

            

        }
        
        return $this->loadView('home');
    }

    function accountValid()
    {
       
        $token = _p('token');
        if ($token){

            $data['email'] = _p('email');
            $data['username'] = _p('username');
            $data['password'] = _p('newPassword');

            $updateAccount = $this->loginHelper->updateUserAccount($data);
            if ($updateAccount){
                $this->activityHelper->updateEmailLog(true, $data['email'],'account',2);
                createAccount($data);
                logFile('account ftp user '.$data['email']. ' created');

                $this->view->assign('validate','Validate account success');
                
            }else{
                $this->view->assign('validate','Validate account error');
                logFile('update n_status user '.$data['email'].' failed');
            }
        }

        $this->view->assign('enterAccount',false);  
        return $this->loadView('home');
    }


}

?>
