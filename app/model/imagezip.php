<?php

class imagezip extends Database {

	var $configkey = "default";
	
    /**
     * @todo insert image data to database
     * 
     * @param $personID = id of a user
     * @param $data['filename'] = filename ori of image file
     * @param $data['mimetype'] = mimetype of image file
     * @param $data['directory'] = directory of image file in zip folder
     * @param $data['md5sum'] = md5sum of image filename
     * 
     * @return result sql
     * 
     * */
	function updateImagePerson($personID, $data){
        $sql = "UPDATE img SET md5sum = '$data[md5sum]', directory = '$data[directory]', mimetype = '$data[mimetype]' WHERE filename = '$data[filename]' AND personID = '$personID'";
		$res = $this->query($sql,0);
        return $res;
	}
    
    /**
     * @todo insert image data to database
     * 
     * @param $personID = id of a user
     * @param $data['filename'] = filename ori of image file
     * @param $data['mimetype'] = mimetype of image file
     * @param $data['directory'] = directory of image file in zip folder
     * @param $data['md5sum'] = md5sum of image filename
     * 
     * @return result sql
     * 
     * */
	function updateImage($personID, $data){
        $sql = "UPDATE img SET md5sum = '$data[md5sum]', directory = '$data[directory]', mimetype = '$data[mimetype]' WHERE filename = '$data[filename]'";
		$res = $this->query($sql,0);
        return $res;
	}
    
    /**
     * @todo get id of a user from database 
     * 
     * @param $username = short name code from user input
     * @return result sql id user
     * 
     * */
    function validateUser($username){
        $sql = "SELECT id FROM person WHERE short_namecode= '$username'";
		$res = $this->fetch($sql,0);
        return $res;
    }
    
    /**
     * @todo get id and short name of a user from database 
     * 
     * @param $email = email from user input
     * @return result sql id and short name user
     * 
     * */
    function validateEmail($email){
        $sql = "SELECT * FROM person WHERE email= '$email'";
		$res = $this->fetch($sql,0);
        return $res;
    }
    
    /**
     * @todo check data image in database (exist or not)
     * 
     * @param $personID = id of a user
     * @param $filename = filename ori of image file
     * @return boolean true false
     * 
     * */
    function dataExist($personID, $filename){
        $sql = "SELECT id FROM img WHERE filename = '$filename' AND personID = '$personID'";
		$res = $this->fetch($sql,0);
        if($res) return true;
        return false;
        
    }
    
    /**
     * @todo check data image in database (exist or not)
     * 
     * @param $personID = id of a user
     * @param $filename = filename ori of image file
     * @return boolean true false
     * 
     * */
    function imageExist($filename){
        $sql = "SELECT id FROM img WHERE filename = '$filename'";
		$res = $this->fetch($sql,0);
        if($res) return true;
        return false;
        
    }
}

?>