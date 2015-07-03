<?php

class insertonebyone extends Database {
    
    function __construct()
	{
        global $basedomain;
		$this->loadmodule();
	}

	function loadmodule()
    {
        global $CONFIG;
        require_once (APP_MODELS.'activityHelper.php');
        $this->activityHelper = new activityHelper;
    }
	
    /**
     * @todo insert a record to database
     * 
     * @param $table = table name
     * @param $data = array data to insert
     * @param $db2 = boolean using second database or not
     * 
     * @return lastid = id of inserted data
     * @return status = boolean status of data
     * 
     * */
	function insertData($table=false, $data=array(), $db2=false)
	{
        global $CONFIG, $basedomain;
		if (!$table and empty($data)) return false;

		$return = array();
		$return['status'] = false;
		
		foreach ($data as $key=>$val){
            if(!empty($val)){
                
                $sanitize = str_replace(array('\'', '"'), '', $val);
                
                $tmpfield[] = "`$key`";
                $tmpvalue[] = "'{$sanitize}'";
            }
		}
		
		$field = implode (',',$tmpfield);
		$value = implode (',',$tmpvalue);
		
		$sql = "INSERT INTO {$table} ({$field}) VALUES ({$value})";
        
        if($db2){
            $res = $this->query($sql,1);
        }else{
            $res = $this->query($sql);
        }
        
		if ($res){
			$return['lastid'] = $this->insert_id();
			$return['status'] = true;
		}
		return $return;
	}
    
    /**
     * @todo insert data transaction
     * 
     * @param $table = table name
     * @param $data = array data to insert
     * @param $db2 = boolean using second database or not
     * 
     * @return $insert = status and last id of inserted data
     * 
     * */
    function insertTransaction($table=false, $data=array()){
        
        global $CONFIG, $basedomain;
        
        if (!$table and empty($data)) return false;
        
        $startTransaction = $this->begin();
		if (!$startTransaction) return false;
        
		logFile('====one by one: TRANSACTION READY====');
        /*if($table == 'person'){
            $username = $data['username'];
            unset($data['username']);
        }*/
        // if table det
        if($table == 'det'){
            
            $kewid = $data['kewid'];
            $fam = $data['family'];
            $gen = $data['genus'];
            $sp = $data['species'];
            
            unset($data['kewid'],$data['family'],$data['genus'],$data['species']);
            
            if(empty($data['taxonID'])){
                if(empty($data['taxonID']) && !empty($data['kewid'])){
                    $check_exist = $this->data_exist('taxon','kewid',$kewid);
                    
                    if($check_exist){
                        $data['taxonID'] = $check_exist['id'];
                    }else{
                        
                        $dataPlantlist = $this->data_exist('plantlist','kewid',$kewid);
                        
                        $insertPlantlist = $this->move_plantlist($dataPlantlist);
                        
                        $data['taxonID'] = $insertPlantlist['lastid'];
                    }
                }else{
                    $select = $this->select_plantlist($fam,$gen,$sp);
                    
                    $select_exist = $this->data_exist('taxon','kewid',$select['kewid']);
                    
                    if($select_exist){
                        $data['taxonID'] = $select_exist['id'];
                    }else{
                        $insertSelected = $this->move_plantlist($select);
                        
                        $data['taxonID'] = $insertSelected['lastid'];
                    }
                }
            }
        }
        // end if table det
        
	    $insert = $this->insertData($table,$data);
        
		if ($insert['status'] == 0){
			$this->rollback();
			logFile('====one by one: failed insert data====');
			$return['status'] = false;
		}else{
		  
            // if table person, insert generated password
            if($table == 'person'){
                $salt = $CONFIG['default']['salt'];
                $register_date = date('Y-m-d H:i:s');
                $token = sha1(CODEKIR.date('ymdhis'));
                
                //this is the generated password
                $genPass = $this->generate_pass();
                
                //this is the encrypted password
                $password = sha1($genPass.$salt);
                
                //this is the generated username
                $username = $this->generate_pass();
                
                //insert to table florakb_person
                $dataPass = array('id' => $insert['lastid'], 'password' => $password, 'username' => $username, 'salt' => $salt, 'register_date' => $register_date, 'email_token' => $token);
                $insert_dataPas = $this->insertData('florakb_person',$dataPass,true);
                
                if ($insert_dataPas['status'] == 0){
        			$this->rollback();
        			logFile('====onebyone: failed insert to florakb_person====');
        			$return['status'] = false;
        		}else{
                    
                    /* EMAIL */
                    // send mail before activate account
                    
					$dataArr['email'] = $data['email'];
                    $dataArr['username'] = $username;
                    
                    
					
					//logFile('onebyone: generate account '.serialize($dataArr));
					$generateMail = $this->activityHelper->generateEmail($dataArr['email'],$dataArr['username'],2,$token);
					if (is_array($generateMail)){
					   
                        logFile('onebyone: generated mail : '. serialize($generateMail));
                        
                        $dataArr['encode'] = $generateMail['encode'];
                        $dataArr['to'] = $generateMail['to'];
                        $dataArr['from'] = $generateMail['from'];
                        
                        $return['status'] = true;
                        $return['lastid'] = $insert['lastid'];
                        $return['dataEmail'] = $dataArr;
                        $this->commit();
                        
					}else{
						logFile('onebyone: generate email failed');
                        $this->rollback();
						$return['status'] = false;
					}
                    
                    /* EMAIL */
        		} // insert to table florakb_person
            }else{
                $this->commit();
    			logFile('====onebyone: success inserting data====');
    			$return['status'] = true;
                $return['lastid'] = $insert['lastid'];
            }
		}
        
        return $return;
		exit;
    }
    
    /**
     * @todo insert data from table plantlist into table taxon
     * 
     * @param $data = data to insert
     * 
     * @return $return['lastid'] = id of inserted data or return false
     * */
    function move_plantlist($data){
        $plantlist['kewid'] = $data['kewid'];
        $plantlist['fam'] = $data['family'];
        $plantlist['gen'] = $data['genus'];
        $plantlist['sp'] = $data['species'];
        $plantlist['subtype'] = $data['ssptype'];
        $plantlist['ssp'] = $data['ssp'];
        $plantlist['auth'] = $data['author'];
        
        $insert = $this->insertData('taxon',$plantlist);
        $this->begin();
        if($insert['status']){
            $this->commit();
            return $insert;
        }else{
            $this->rollback();
            return false;
        }
    }
    
    /**
     * @todo check data exist in table by one field specified
     * 
     * @param $table = name of table
     * @param $whereField = field name to be in where clause
     * @param $value = value of the field specified
     * 
     * @return return sql result or return false
     * */
    function data_exist($table,$whereField,$value){
        $sql = "SELECT * FROM {$table} WHERE {$whereField} = '{$value}'";
		$res = $this->fetch($sql,0);
        if($res) return $res;
        return false;
    }
    
    /**
     * @todo select data in plantlist table where fam, gen, and sp is empty
     * 
     * @param $fam = value of family field
     * @param $gen = value of genus field
     * @param $sp = value of species field
     * 
     * @return return sql result or return false
     * */
    function select_plantlist($fam,$gen,$sp){
        $sql = "SELECT * FROM plantlist WHERE family = '{$fam}' AND genus = '{$gen}' AND species = '{$sp}'";
		$res = $this->fetch($sql,0);
        if($res) return $res;
        return false;
    }
    
    /**
     * @todo generate a random password
     * 
     * @param $length = length of character
     * 
     * @return $result = random character
     * 
     * */
    function generate_pass($length = 8){
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $count = mb_strlen($chars);
    
        for ($i = 0, $result = ''; $i < $length; $i++) {
            $index = rand(0, $count - 1);
            $result .= mb_substr($chars, $index, 1);
        }
    
        return $result;
    }
    
    /**
     * @todo get list location
     * 
     * @return sql result
     * 
     * */
    function list_locn(){
        $sql = "SELECT id, locality FROM locn GROUP BY locality ORDER BY locality";
		$res = $this->fetch($sql,1);
        return $res;
    }
    
    /**
     * @todo get list person
     * 
     * @return sql result
     * 
     * */
    function list_person(){
        $sql = "SELECT * FROM person";
		$res = $this->fetch($sql,1);
        return $res;
    }
    
    /**
     * @todo get list taxon
     * 
     * @return sql result
     * 
     * */
    function list_taxon(){
        $sql = "SELECT * FROM taxon GROUP BY fam, gen, sp, morphotype ORDER BY fam, gen, sp, morphotype";
		$res = $this->fetch($sql,1);
        return $res;
    }
    
    /**
     * @todo get list specified field for auto taxon from plantlist table
     * 
     * @return sql result
     * 
     * */
    function list_autoTaxon($field,$data){
        if($field=='family'){
            $like = $data['family'];
            $sql = "SELECT kewid, {$field} FROM plantlist WHERE {$field} LIKE '%$like%' AND genus='' AND species='' GROUP BY {$field} ORDER BY {$field};";
    		$res = $this->fetch($sql,1);
        }elseif($field=='genus'){
            $family = $data['family'];
            $like = $data['genus'];
            $sql = "SELECT kewid, {$field} FROM plantlist WHERE family='{$family}' AND {$field} LIKE '%$like%' AND species='' GROUP BY {$field} ORDER BY {$field};";
    		$res = $this->fetch($sql,1);
        }elseif($field=='species'){
            $family = $data['family'];
            $genus = $data['genus'];
            $like = $data['species'];
            $sql = "SELECT kewid, {$field} FROM plantlist WHERE family='{$family}' AND genus='{$genus}' AND {$field} LIKE '%$like%' GROUP BY {$field} ORDER BY {$field};";
    		$res = $this->fetch($sql,1);
        }else{
            $res = false;
        }
        return $res;
    }
    
    /**
     * @todo get enum of confid field in table det
     * @param $table = table name
     * @param $field = field name
     * 
     * @return preg match of sql result
     * 
     * */
    function get_enum($table, $field){
        $sql = "SHOW COLUMNS FROM `$table` WHERE Field = '$field'";
		$res = $this->fetch($sql,0);
        
        preg_match('/^enum\((.*)\)$/', $res['Type'], $matches);
        foreach( explode(',', $matches[1]) as $value )
        {
             $enum[] = trim( $value, "'" );
        }
        return $enum;
    }
    
    /**
     * @todo start sql transaction
     * 
     * @return boolean true/false
     * 
     * */
    function startTransaction()
	{
		$startTransaction = $this->begin();
		if (!$startTransaction) return false;
		logFile('====TRANSACTION READY====');
		return true;
	}
	
    /**
     * @todo rollback sql transaction
     * 
     * @return boolean true/false
     * 
     * */
	function rollbackTransaction()
	{
		$this->rollback();
		logFile('====ROLLBACK TRANSACTION====');
		return true;
	}
	
    /**
     * @todo commit sql transaction
     * 
     * @return boolean true/false
     * 
     * */
	function commitTransaction()
	{
		$this->commit();
		logFile('====COMMIT TRANSACTION====');
		return true;
	}
}

?>