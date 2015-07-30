<?php

class browseHelper extends Database {

    var $prefix = "peerkalbar";
	
    /**
     * @todo retrieve all data from table Taxon
     * 
     * @param $condition = true/false
     * @param $field = field name
     * @param $value = value
     * @return id, rank, morphotype, fam, gen, sp, subtype, ssp, auth, notes
     */
    function dataTaxon($condition,$field,$value){
        if($condition==true){
            $sql = "SELECT * FROM `{$this->prefix}_taxon` WHERE $field='$value'";
            $res = $this->fetch($sql,1);
            return $res;
        }
        elseif($condition==false){
            $sql="SELECT * FROM {$this->prefix}_taxon WHERE id IN (SELECT {$this->prefix}_det.taxonID FROM {$this->prefix}_det INNER JOIN {$this->prefix}_indiv on {$this->prefix}_indiv.id = {$this->prefix}_det.indivID WHERE {$this->prefix}_indiv.n_status = 0)";
            $res = $this->fetch($sql,1);
            $return['result'] = $res;
            return $return;
        }
    }
    
    /**
     * @todo retrieve all images from taxon data
     * @param $data = id taxon
     */
    function showImgTaxon($data){
        $sql = "SELECT * 
                FROM `{$this->prefix}_det` INNER JOIN `{$this->prefix}_img` ON 
                    {$this->prefix}_det.taxonID='$data' AND {$this->prefix}_det.indivID = {$this->prefix}_img.indivID GROUP BY {$this->prefix}_img.md5sum LIMIT 0,5";
        $res = $this->fetch($sql,1);
        return $res;
    }
    
    /**
     * @todo retrieve all data from table indiv from selected taxon
     * 
     * @param $action=action selected taxon/locn/person
     * @param $field=field name in db
     * @param $value=id taxon
     * @return 
     */
    function dataIndiv($action,$field,$value){
        if($action=='indivTaxon'){
            $sql = "SELECT * 
                    FROM `{$this->prefix}_det` INNER JOIN `{$this->prefix}_indiv` ON 
                        {$this->prefix}_det.$field='$value' AND {$this->prefix}_det.indivID = {$this->prefix}_indiv.id AND {$this->prefix}_indiv.n_status='0'
                    INNER JOIN `{$this->prefix}_person` ON
                        {$this->prefix}_indiv.personID = {$this->prefix}_person.id
                    INNER JOIN `{$this->prefix}_locn` ON
                        {$this->prefix}_locn.id = {$this->prefix}_indiv.locnID
                    GROUP BY {$this->prefix}_det.indivID";
        }
        
        if($action=='indivLocn'){
            $sql = "SELECT {$this->prefix}_indiv.id as indivID, {$this->prefix}_indiv.locnID, {$this->prefix}_indiv.plot, {$this->prefix}_indiv.tag, {$this->prefix}_indiv.personID, {$this->prefix}_locn.*, {$this->prefix}_person.*
                    FROM `{$this->prefix}_indiv` INNER JOIN `{$this->prefix}_locn` ON 
                        $value = {$this->prefix}_indiv.locnID AND {$this->prefix}_indiv.n_status='0'
                    INNER JOIN `{$this->prefix}_person` ON
                        {$this->prefix}_indiv.personID = {$this->prefix}_person.id
                    GROUP BY {$this->prefix}_indiv.id";
        }
        
        if($action=='indivPerson'){
            $sql = "SELECT {$this->prefix}_indiv.id as indivID, {$this->prefix}_indiv.locnID, {$this->prefix}_indiv.plot, {$this->prefix}_indiv.tag, {$this->prefix}_indiv.personID, {$this->prefix}_locn.*, {$this->prefix}_person.*
                    FROM `{$this->prefix}_indiv` INNER JOIN `{$this->prefix}_locn` ON 
                        $value = {$this->prefix}_indiv.personID AND {$this->prefix}_indiv.n_status = '0'
                    INNER JOIN `{$this->prefix}_person` ON
                        $value = {$this->prefix}_person.id
                    INNER JOIN `{$this->prefix}_det` ON
                        {$this->prefix}_indiv.id = {$this->prefix}_det.indivID
                    GROUP BY {$this->prefix}_indiv.id";                   
        }
        
        $res = $this->fetch($sql,1);
        $return['result'] = $res;
        return $return;
    }
    
    /**
     * @todo delete selected image
     */
    function deleteImg($data){
        foreach ($data['id'] as $id){
            $sql="DELETE FROM `{$this->prefix}_img` WHERE `id`='$id' AND indivID='".$data['indivID']."'";
            $res = $this->query($sql,0);
        }
        return true;
    }
    
    /**
     * @todo delete all image in one individual
     */
    function deleteImgIndiv($data){
        $sql="DELETE FROM `{$this->prefix}_img` WHERE indivID='$data'";
        $res = $this->query($sql,0);
        return true;
    }
    
    /**
     * @todo retrieve all data from table Location
     * 
     * @param $condition = true/false
     * @param $field = field name
     * @param $value = value
     * @return id, rank, morphotype, fam, gen, sp, subtype, ssp, auth, notes
     */
    function dataLocation($condition,$field,$value){
        if($condition==true){
            $sql = "SELECT * FROM `{$this->prefix}_locn` WHERE $field='$value'";
            $res = $this->fetch($sql,1);
            return $res;
        }
        elseif($condition==false){
            $sql="SELECT * FROM `{$this->prefix}_locn` WHERE id IN (SELECT {$this->prefix}_indiv.locnID FROM {$this->prefix}_indiv INNER JOIN {$this->prefix}_det ON {$this->prefix}_indiv.id = {$this->prefix}_det.indivID WHERE {$this->prefix}_indiv.n_status = 0)";
            $res = $this->fetch($sql,1);
            $return['result'] = $res;
            return $return;
        }
    }
    
    /**
     * @todo retrieve all data from table person
     * 
     * @param $condition = true/false
     * @param $field = field name
     * @param $value = value
     * @return id, rank, morphotype, fam, gen, sp, subtype, ssp, auth, notes
     */
    function dataPerson($condition,$field,$value){
        if($condition==true){
            $sql = "SELECT * FROM `{$this->prefix}_person` WHERE $field='$value'";
            $res = $this->fetch($sql,1);
            return $res;
        }
        elseif($condition==false){
            $sql = "SELECT * FROM `{$this->prefix}_person`";           
            $res = $this->fetch($sql,1);
            $return['result'] = $res;
            return $return;
        }
    }
    
    /**
     * @todo retrieve all images from indiv data
     * @param $data = id indiv
     */
    function showImgIndiv($data,$limit,$limitVal){
        if($limit==TRUE){
            $sql = "SELECT * FROM `{$this->prefix}_img` WHERE indivID='$data' AND md5sum IS NOT NULL LIMIT $limitVal";
        }
        elseif($limit==FALSE){
            $sql = "SELECT * FROM `{$this->prefix}_img` WHERE indivID='$data'";
        }
        $res = $this->fetch($sql,1);
        return $res;
    }
    
    /**
     * @todo retrieve all indiv detail
     * @param $data = id indiv
     */
    function detailIndiv($data){
        $sql = "SELECT * 
                FROM `{$this->prefix}_indiv` INNER JOIN `{$this->prefix}_locn` ON 
                    {$this->prefix}_indiv.id='$data' AND {$this->prefix}_locn.id = {$this->prefix}_indiv.locnID AND {$this->prefix}_indiv.n_status = '0'
                INNER JOIN `{$this->prefix}_person` ON
                    {$this->prefix}_person.id = {$this->prefix}_indiv.personID";
        $res = $this->fetch($sql,1);
        return $res;
    }
    
    /**
     * @todo retrieve all det from indiv selected
     * @param $data = id indiv
     */
    function dataDetIndiv($data){
        $sql = "SELECT {$this->prefix}_det.id as detID, {$this->prefix}_det.notes as detNotes, {$this->prefix}_det.*, {$this->prefix}_taxon.*, {$this->prefix}_person.* 
                FROM `{$this->prefix}_det` INNER JOIN `{$this->prefix}_taxon` ON 
                    indivID = '$data' AND {$this->prefix}_taxon.id = {$this->prefix}_det.taxonID AND {$this->prefix}_det.n_status = '0'
                INNER JOIN `{$this->prefix}_person` ON
                    {$this->prefix}_person.id = {$this->prefix}_det.personID";
        $res = $this->fetch($sql,1);
        return $res;
    }
    
    /**
     * @todo retrieve all obs from indiv selected
     * @param $data = id indiv
     */
    function dataObsIndiv($data){
        $sql = "SELECT {$this->prefix}_obs.id as obsID, {$this->prefix}_obs.*, {$this->prefix}_person.* 
                FROM `{$this->prefix}_obs` INNER JOIN `{$this->prefix}_person` ON 
                    indivID = '$data' AND {$this->prefix}_person.id = {$this->prefix}_obs.personID AND {$this->prefix}_obs.n_status = '0'";
        $res = $this->fetch($sql,1);
        return $res;
    }
    
    /**
     * @todo update indiv data selected
     * @param $data = POST indiv
     * @param $id = id indiv
     */
    function updateIndiv($data,$id){
        $sql = "UPDATE `{$this->prefix}_indiv` SET `locnID` = '".$data['locnID']."', `plot` = '".$data['plot']."', `tag` = '".$data['tag']."' WHERE `id` = $id;";
        $res = $this->query($sql,0);
        if($res){return true;}
    }
    
    /**
     * @todo update n_status indiv,obs,det,img,coll data selected into 1
     * @param $id = id indiv
     */
    function deleteIndiv($condition,$table,$field,$data){
        if($condition ==''){ 
            $sql = "UPDATE `{$this->prefix}_$table` SET `n_status` = '1' WHERE `$field`='".$data['indivID']."';";
            $res = $this->query($sql,0);
        }
        elseif($condition == 'AND'){
            $sql = "UPDATE `{$this->prefix}_$table` SET `n_status` = '1' WHERE `$field`='".$data['indivID']."' AND `id` = '".$data['id']."';";
            $res = $this->query($sql,0);
        }
        if($res){
            logFile('====Update table '.$this->prefix.'_'.$table.' id='.$data['indivID'].'n_status = 1====');
            return true;    
        }
        else{
            logFile('====Failed table '.$this->prefix.'_'.$table.' id='.$data['indivID'].'n_status = 1====');
            return false;}
    }
    
    /**
     * @todo searching from selected table
     * 
     * @param $table= table name
     * @param $data = data to search
     * 
     */
    function search($table,$data){
        if($table == $this->prefix.'_taxon'){
            $sql = "SELECT * 
                    FROM `$table` INNER JOIN `{$this->prefix}_det` ON 
                    $table.id = {$this->prefix}_det.taxonID AND {$this->prefix}_det.n_status='0' WHERE
                    $table.fam LIKE '%$data%' OR $table.gen LIKE '%$data%' OR $table.sp LIKE '%$data%' OR $table.morphotype LIKE '%$data%'";
            //pr($sql);exit;
        }
        elseif($table == $this->prefix.'_locn'){
            $sql = "SELECT * FROM `$table` WHERE `longitude` LIKE '%$data%' OR `latitude` LIKE '%$data%' OR `elev` LIKE '%$data%' OR `geomorph` LIKE '%$data%' OR `locality` LIKE '%$data%' OR `county` LIKE '%$data%' OR `province` LIKE '%$data%' OR `island` LIKE '%$data%' OR `country` LIKE '%$data%'";
        }
        elseif($table == $this->prefix.'_person'){
            $sql = "SELECT * FROM `$table` WHERE `name` LIKE '%$data%' OR `email` LIKE '%$data%' OR `twitter` LIKE '%$data%' OR `website` LIKE '%$data%' OR `phone` LIKE '%$data%' OR `institutions` LIKE '%$data%' OR `project` LIKE '%$data%'";
        }
        $res = $this->fetch($sql,1);
        
        //PAGINATION
            if (isset($_GET['pageno'])) {
               $pageno = $_GET['pageno'];
            } else {
               $pageno = 1;
            } // if
            $rows_per_page = 10;
            $lastpage      = ceil(count($res)/$rows_per_page);
            $pageno = (int)$pageno;
            if ($pageno > $lastpage) {
               $pageno = $lastpage;
            } // if
            if ($pageno < 1) {
               $pageno = 1;
            } // if
            $limit = 'LIMIT ' .($pageno - 1) * $rows_per_page .',' .$rows_per_page;
            $sqlLimit = $sql.' '.$limit;
            $resLimit = $this->fetch($sqlLimit,1);
            if($resLimit){
                $return['result'] = $resLimit;
                $return['pageno'] = $pageno;
                $return['lastpage'] = $lastpage;
                $return['countdata'] = $res;
                return $return;
            }
            else{return false;}                
    }
	
}
?>