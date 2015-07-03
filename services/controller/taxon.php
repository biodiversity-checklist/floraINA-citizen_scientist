<?php
// defined ('MICRODATA') or exit ( 'Forbidden Access' );

class taxon extends Controller {
	
	var $models = FALSE;
	
	public function __construct()
	{
		parent::__construct();
		$this->loadmodule();
		
		global $basedomain;	
		$validate = $this->validateToken();
		if (!$validate){
			redirect($basedomain);
			exit;
		} 
		// $this->validatePage();
	}
	public function loadmodule()
	{
		
		$this->browseHelper = $this->loadModel('browseHelper');
	}
	
	public function index(){
       	
       	pr($_GET);

       	exit;
		// $data = $this->models->get_profile();
		// pr($data);
		return $this->loadView('viewprofile',$data);

	}
    
	function getDataTaxon()
	{
		$taxon =  $this->browseHelper->dataTaxon();
		if ($taxon){
			print json_encode($taxon);
		}
		exit;
		/*
			1. jika tidak ada parameter kedua maka get data 
		*/
	}

	function getImgTaxon()
	{
		$id = $_GET['id'];
		$img =  $this->browseHelper->getImgTaxon($id);
		if ($img){
			print json_encode($img);
		}
		exit;
	}

	function getTitle()
	{
		$id = $_GET['id'];
		$title =  $this->browseHelper->getTitle($id);
		if ($title){
			print json_encode($title);
		}
		exit;
	}

	function getDataLocation()
	{
		$location =  $this->browseHelper->dataLocation();
		if ($location){
			print json_encode($location);
		}
		exit;
		/*
			1. jika tidak ada parameter kedua maka get data 
		*/
	}

	function getDataPerson()
	{
		$person =  $this->browseHelper->dataPerson();
		if ($person){
			print json_encode($person);
		}
		exit;
		/*
			1. jika tidak ada parameter kedua maka get data 
		*/
	}

	function getIndivTaxon()
	{
		$id = $_GET['id'];
		$indiv =  $this->browseHelper->dataIndivTaxon($id);
		if ($indiv){
			print json_encode($indiv);
		}
		exit;
	}

	function getImgIndiv()
	{
		$id = $_GET['id'];
		$img =  $this->browseHelper->showImgIndiv($id);
		if ($img){
			print json_encode($img);
		}
		exit;
	}

	function getIndivLocation()
	{
		$id = $_GET['id'];
		$indiv =  $this->browseHelper->dataIndivLocation($id);
		if ($indiv){
			print json_encode($indiv);
		}
		exit;
	}

	function getIndivPerson()
	{
		$id = $_GET['id'];
		$indiv =  $this->browseHelper->dataIndivPerson($id);
		if ($indiv){
			print json_encode($indiv);
		}
		exit;
	}

	function detailIndiv()
	{
		$id = $_GET['id'];
		$detailIndiv =  $this->browseHelper->detailIndiv($id);
		if ($detailIndiv){
			print json_encode($detailIndiv);
		}
		exit;
	}

	function dataDetIndiv()
	{
		$id = $_GET['id'];
		$dataDetIndiv =  $this->browseHelper->dataDetIndiv($id);
		if ($dataDetIndiv){
			print json_encode($dataDetIndiv);
		}
		exit;
	}

	function dataObsIndiv()
	{
		$id = $_GET['id'];
		$dataObsIndiv =  $this->browseHelper->dataObsIndiv($id);
		if ($dataObsIndiv){
			print json_encode($dataObsIndiv);
		}
		exit;
	}

	function getAllImgIndiv()
	{
		$id = $_GET['id'];
		$img =  $this->browseHelper->showAllImgIndiv($id);
		if ($img){
			print json_encode($img);
		}
		exit;
	}

	function dataIndivLimit()
	{
		$taxon =  $this->browseHelper->dataIndivLimit();
		if ($taxon){
			print json_encode($taxon);
		}
		exit;
		/*
			1. jika tidak ada parameter kedua maka get data 
		*/
	}

	function validateToken()
	{

		$_SESSION['services']['token'] = '123';
		$token = $_SESSION['services'];
		if ($token['token']) return true;
		return false;
	}
}

?>
