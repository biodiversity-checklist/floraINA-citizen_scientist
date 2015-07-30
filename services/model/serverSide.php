<?php

class ServerSide extends Database {

	function loadAPI($api)
	{

		$API = new $api;

		return $API;
	}

	function dTableData($data=array())
	{

		$API = $this->loadAPI($data['APIHelper']);

		// $dataParam['jenisaset'][0]=$data['jenisaset'];
		// if($data['jenisaset']=="2")$merk="m.Merk";
		// else $merk="";

		$aColumns = $data['searchField'];

		/* Indexed column (used for fast and accurate table cardinality) */
		$sIndexColumn = $data['primaryField'];

		/* DB table to use */
		$sTable = $data['primaryTable'];

		if ($data['filter']){
			foreach ($data['filter'] as $key => $value) {
				
				$dataParam[$key] = $value;
			}
		}

		
		$sLimit = "";
		if (isset($_GET['iDisplayStart']) && $_GET['iDisplayLength'] != '-1') {
		     $sLimit = " " . intval($_GET['iDisplayStart']) . ", " .
		             intval($_GET['iDisplayLength']);
		}


		// Ordering
		 
		$sOrder = "";
		if (isset($_GET['iSortCol_0'])) {
		     $sOrder = "ORDER BY  ";
		     for ($i = 0; $i < intval($_GET['iSortingCols']); $i++) {
		          if ($_GET['bSortable_' . intval($_GET['iSortCol_' . $i])] == "true") {
		               //$sOrder .= "'" . $aColumns[intval($_GET['iSortCol_' . $i])] . "' " .
		               $sOrder .= "" . $aColumns[intval($_GET['iSortCol_' . $i])] . " " .
		                       ($_GET['sSortDir_' . $i] === 'asc' ? 'asc' : 'desc') . ", ";
		          }
		     }

		     $sOrder = substr_replace($sOrder, "", -2);
		     if ($sOrder == "ORDER BY") {
		          $sOrder = "";
		     }
		}


		/*
		 * Filtering
		 * NOTE this does not match the built-in DataTables filtering which does it
		 * word by word on any field. It's possible to do here, but concerned about efficiency
		 * on very large tables, and MySQL's regex functionality is very limited
		 */
		//$sWhere = "";
		if (isset($_GET['sSearch']) && $_GET['sSearch'] != "") {
		     //$sWhere = "WHERE (";
		     $sWhere ="(";
		     for ($i = 0; $i < count($aColumns); $i++) {
		          if (isset($_GET['bSearchable_' . $i]) && $_GET['bSearchable_' . $i] == "true") {
		               $sWhere .= "" . $aColumns[$i] . " LIKE '%" . $this->escape_string($_GET['sSearch']) . "%' OR ";
		          }
		     }
		     $sWhere = substr_replace($sWhere, "", -3);
		     $sWhere .= ')';
		}

		/* Individual column filtering */
		for ($i = 0; $i < count($aColumns); $i++) {
		     if (isset($_GET['bSearchable_' . $i]) && $_GET['bSearchable_' . $i] == "true" && $_GET['sSearch_' . $i] != '') {
		          if ($sWhere == "") {
		        //       $sWhere = "WHERE ";
		               $tidakdipakai=0;
		          } else {
		               $sWhere .= " AND ";
		          }
		          $sWhere .= "`" . $aColumns[$i] . "` LIKE '%" . $this->escape_string($_GET['sSearch_' . $i]) . "%' ";
		     }
		}


		// echo $sLimit;
		$dataParam['condition']="$sWhere ";
		$dataParam['order']=$sOrder;  
		$dataParam['limit']="$sLimit";

		$SSData = $data;
		$alldata = $API->$data['APIFunction']($dataParam);	
		
		$data = $alldata['data'];

		/* Data set length after filtering */
		$sQuery = " SELECT FOUND_ROWS() AS total";
		// $rResultFilterTotal = $this->query($sQuery);
		// $aResultFilterTotal = $this->fetch($sQuery);
		// pr($aResultFilterTotal);
		$iFilteredTotal = $alldata['dataset'];
		// pr($iFilteredTotal);
		// pr($aResultFilterTotal);
		/* Total data set length */
		$sQuery = "
				SELECT COUNT(`" . $sIndexColumn . "`) AS total
				FROM   $sTable
			";
		// pr($sQuery);
		$aResultTotal = $this->fetch($sQuery);
		$iTotal = $alldata['dataTotal'];
		/*
		 * Output
		 */
		$output = array(
		    "sEcho" => intval($_GET['sEcho']),
		    "iTotalRecords" => $iTotal,
		    "iTotalDisplayRecords" => $iTotal,
		    "aaData" => array()
		);

		// pr($data);
		$no=$_GET['iDisplayStart']+1;
		if (!empty($data)){

		  	foreach ($data as $key => $value){
				                               
             	$row = array();
		        
		        foreach ($SSData['view'] as $key => $val) {
		            
		        	$tmp = explode('|', $val);
		        	
		        	if (count($tmp)>1){

		        		if ($tmp[0] == 'detail'){

			            	$expl = explode('|', $val);

			            	if (count($expl)>2){
			            		$concate = array();
			            		$getParam = explode('&', $expl[2]);
			            		for ($i=0; $i < count($getParam); $i++) { 

			            			if ($i==0){
			            				$expParam = explode('=', $getParam[$i]);
			            				$concate[] = $expParam[0] . '=' .$value[$expParam[1]];
			            			}else{
			            				$expParam = explode('=', $getParam[$i]);
			            				$concate[] = $expParam[0] . '=' .$expParam[1];
			            			}
			            			
			            		}

			            		$impl = implode('&', $concate);
			            		$completeURi = $impl; 
								// pr($expl);
								$row[] = $this->additional('detail', array('url'=>$expl[1]. "?" . $completeURi));
			            	
			            	}else{
			            		$row[] = $this->additional('detail', array('url'=>$expl[1]));
			            	}
			            	
			            	
			            }else if ($tmp[0] == 'checkbox'){

			            	$expl = explode('|', $val);

			            	if (count($expl)>2){
			            		$concate = array();
			            		$getParam = explode('&', $expl[2]);
			            		for ($i=0; $i < count($getParam); $i++) { 
			            			
			            			$concate[] = $value[$getParam[$i]];
			            		}


			            		$impl = implode('_', $concate);
			            		$completeURi = $impl; 
								// pr($value);
								$checked = false;
								
								if ($value['checked']) $checked = true;
								// echo $tmp[1]; 
								if ($tmp[1]=="Layanan"){
									if ($value['noKontrak']!=""){
										$row[] = $this->span();
										
									}else{
										$row[] = $this->additional('checkbox', array('name'=>$expl[1], 'value'=>$completeURi, 'checked'=>$checked));
									} 
								}else{

									$row[] = $this->additional('checkbox', array('name'=>$expl[1], 'value'=>$completeURi, 'checked'=>$checked));
								}
								

								
			            	
			            	}else{
			            		$row[] = $this->additional('checkbox', array('name'=>$expl[1]));
			            	}

			            }else if ($tmp[0] == 'image'){

			            	$expl = explode('|', $val);

			            	// pr($value);
			            	$concate = array();
			            	$concate['file'] = $value[$expl[1]];
			            	$concate['title'] = $value[$expl[2]];
			            	$concate['id'] = $value[$expl[3]];

		            		$row[] = $this->additional('image', $concate);
			            	
			            }

			            else $row[]=$value[$tmp[0]] . ' / ' . $value[$tmp[1]];

		        	}else{

		        		
		            	$row[] = $value[$val];
			            
		        	}

		            

	            }    

	            
		        $output['aaData'][] = $row;
		        $no++;
		  	}
		}

		return ($output);

	}

	function additional($id=false, $data)
	{

		// pr($data);
		global $app_domain, $portaldomain;
		// pr($portaldomain);
		if ($id){
			switch ($id) {
				case 'checkbox':
					$checked = "";
					$disabled = "";
					if ($data['checked']) $checked = "checked = checked";
					// if ($data['disabled']) $disabled = "disabled = disabled";
					return "<input type=\"checkbox\" id=\"checkbox\" class=\"icheck-input checkbox\" onchange=\"return AreAnyCheckboxesChecked();\" name=\"{$data['name']}[]\" value=\"{$data['value']}\" {$checked} >";
					break;
				
				
				case 'detail':
					return "<a href=$data[url] class='btn flora-btn'>Detail</a>";
					break;

				case 'image':
				
				if ($data['file']){
					foreach ($data['file'] as $key => $value) {
						if ($value){
							$html .= "<a href='{$app_domain}public_assets/img/500px/{$value}.500px.jpg' title='{$data[sp]}' data-gallery='#blueimp-gallery-links-{$data[id]}'>
		                                <img src='{$app_domain}public_assets/img/100px/{$value}.100px.jpg' />
		                            </a>";
						}
						
					}
				}
				
				return $html;
            		break;
				default:
					return false;
					break;
			}
		}

		return false;
	}

	function span()
	{
		return "<span>&nbsp;</span>";
	}
}
?>