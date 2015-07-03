<?php
defined ('MICRODATA') or exit ( 'Forbidden Access' );

class profile extends Controller {
	
	var $models = FALSE;
	
	public function __construct()
	{
		parent::__construct();
		$this->loadmodule();
		
		// $this->validatePage();
	}
	public function loadmodule()
	{
		
		$this->models = $this->loadModel('mprofile');
	}
	
	public function index(){
       
		$data = $this->models->get_profile();
		// pr($data);
		return $this->loadView('viewprofile',$data);

	}
    
	public function profileinp(){
		global $CONFIG;

		if(isset($_POST)){
                // validasi value yang masuk
               $x = form_validation($_POST);

			   $action_tentang = 'insert';
			   $action_sejarah = 'insert';
			   $action_visi = 'insert';
			   $action_misi = 'insert';

			   try
			   {
			   		if(isset($x) && count($x) != 0)
			   		{
						//update or insert tentang
						if($x['id_tentang'] != ''){
							$action_tentang = 'update';
						}
						$data = $this->models->profile_inp($x['header_tentang'],$x['tentang'],$x['title_tentang'],$x['id_tentang'],$x['tags_tentang'],$action_tentang);
						
						//update or insert sejarah
						if($x['id_sejarah'] != ''){
							$action_sejarah = 'update';
						}
						$data = $this->models->profile_inp($x['header_sejarah'],$x['sejarah'],$x['title_sejarah'],$x['id_sejarah'],$x['tags_sejarah'],$action_sejarah);
						
						//update or insert visi
						if($x['id_visi'] != ''){
							$action_visi = 'update';
						}
						$data = $this->models->profile_inp($x['header_visi'],$x['visi'],$x['title_visi'],$x['id_visi'],$x['tags_visi'],$action_visi);
						
						//update or insert sejarah
						if($x['id_misi'] != ''){
							$action_misi = 'update';
						}
						$data = $this->models->profile_inp($x['header_visi'],$x['misi'],$x['title_visi'],$x['id_misi'],$x['tags_misi'],$action_misi);
			   		}
				   	
			   }catch (Exception $e){}
			   
            echo "<script>alert('Data berhasil di simpan');window.location.href='".$CONFIG['admin']['base_url']."profile/index'</script>";
            }
	}
}

?>
