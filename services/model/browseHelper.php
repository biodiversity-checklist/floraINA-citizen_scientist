<?php

class browseHelper extends Database {
	
    var $prefix;
    function __construct()
    {

        $this->prefix = "peerkalbar";
    }

    /**
     * @todo retrieve all data from table Taxon
     * @return id, rank, morphotype, fam, gen, sp, subtype, ssp, auth, notes
     */
    function dataTaxon($data, $start=0, $limit=20){

        /* start param datatables */
        $limit= $data['limit'];
        $order= $data['order'];
        $kondisi= trim($data['condition']);
        if($kondisi!="")$kondisi=" and $kondisi";
        /* end param datatables */

        $sql= "SELECT * FROM {$this->prefix}_taxon 
                WHERE id IN 
                (
                    SELECT {$this->prefix}_det.taxonID 
                    FROM {$this->prefix}_det 
                    INNER JOIN {$this->prefix}_indiv 
                    ON {$this->prefix}_indiv.id = {$this->prefix}_det.indivID 
                    WHERE {$this->prefix}_indiv.n_status = 0 
                ) 
                {$kondisi} {$order} 
                LIMIT {$limit} ";
        // pr($sql);
        $res = $this->fetch($sql,1);

        $rowsFilter = $this->fetch("SELECT FOUND_ROWS() AS total");

        $tQuery = "SELECT COUNT(id) AS total FROM {$this->prefix}_taxon 
                    WHERE id IN 
                    (
                        SELECT {$this->prefix}_det.taxonID 
                        FROM {$this->prefix}_det 
                        INNER JOIN {$this->prefix}_indiv 
                        ON {$this->prefix}_indiv.id = {$this->prefix}_det.indivID 
                        WHERE {$this->prefix}_indiv.n_status = 0 
                    ) 
                    {$kondisi}";
                    // pr($tQuery);
        $rowsTotal = $this->fetch($tQuery);
        // pr($rows);
        if ($res){

            foreach ($res as $key => $value) {
                $sql = "SELECT img.md5sum 
                        FROM `{$this->prefix}_det` AS det INNER JOIN `{$this->prefix}_img` AS img ON 
                            det.taxonID='{$value[id]}' AND det.indivID=img.indivID GROUP BY img.md5sum LIMIT 5";
                // pr($sql);
                $result = $this->fetch($sql,1);
                $img = array();
                if (is_array($result)){

                    foreach ($result as $val) {
                        if ($val['md5sum']) $img[] = $val['md5sum'];
                    }

                    $res[$key]['img'] = $img;
                }
                    
            }
            
        }
        
        $dataArray['dataset'] = $rowsFilter['total'];
        $dataArray['dataTotal'] = $rowsTotal['total'];
        $dataArray['data'] = $res;

        return $dataArray;
    }

    /**
     * @todo retrieve all data from table Taxon
     * @return id, rank, morphotype, fam, gen, sp, subtype, ssp, auth, notes
     */
    function dataIndivLimit(){
        $sql= "SELECT * FROM {$this->prefix}_img WHERE md5sum <> '' GROUP BY indivID ORDER BY id DESC LIMIT 10";
        $res = $this->fetch($sql,1);
        $return['result'] = $res;
        return $return;
    }

    /**
     * @todo retrieve all images from taxon data
     * @param $data = id taxon
     */
    function getImgTaxon($data, $start=0, $limit=5){
        $sql = "SELECT * 
                FROM `{$this->prefix}_det` INNER JOIN `{$this->prefix}_img` ON 
                    {$this->prefix}_det.taxonID='$data' AND {$this->prefix}_det.indivID={$this->prefix}_img.indivID GROUP BY {$this->prefix}_img.md5sum LIMIT {$start},{$limit}";
        $res = $this->fetch($sql,1);
        return $res;
    }

    /**
     * @todo retrieve title from selected species
     * @param $data = id title
     */
    function getTitle($data){
        $sql = "SELECT {$this->prefix}_sp FROM {$this->prefix}_taxon WHERE id = $data";
        $res = $this->fetch($sql,1);
        return $res;
    }

    /**
     * @todo retrieve all data from table location
     * @return 
     */
    function dataLocation($data, $start=0, $limit=20){

        /* start param datatables */
        $limit= $data['limit'];
        $order= $data['order'];
        $kondisi= trim($data['condition']);
        if($kondisi!="")$kondisi=" and $kondisi";
        /* end param datatables */

        $sql= "SELECT * FROM `{$this->prefix}_locn` 
                WHERE id IN 
                (
                    SELECT {$this->prefix}_indiv.locnID 
                    FROM {$this->prefix}_indiv 
                    INNER JOIN {$this->prefix}_det 
                    ON {$this->prefix}_indiv.id = {$this->prefix}_det.indivID 
                    WHERE {$this->prefix}_indiv.n_status = 0 
                ) 
                {$kondisi} {$order} 
                LIMIT {$limit}";

        $res = $this->fetch($sql,1);
        $rowsFilter = $this->fetch("SELECT FOUND_ROWS() AS total");
        $tQuery = "SELECT COUNT(id) AS total FROM `{$this->prefix}_locn` 
                    WHERE id IN 
                    (
                        SELECT {$this->prefix}_indiv.locnID 
                        FROM {$this->prefix}_indiv 
                        INNER JOIN {$this->prefix}_det 
                        ON {$this->prefix}_indiv.id = {$this->prefix}_det.indivID 
                        WHERE {$this->prefix}_indiv.n_status = 0 
                    ) 
                    {$kondisi}";
                    // pr($tQuery);
        $rowsTotal = $this->fetch($tQuery);

        if ($res)
        {
            $dataArray['dataset'] = $rowsFilter['total'];
            $dataArray['dataTotal'] = $rowsTotal['total'];
            $dataArray['data'] = $res;

            return $dataArray;
        }

        return false;
    }

    /**
     * @todo retrieve all data from table person
     * @return 
     */
    function dataPerson($data, $start=0, $limit=20){

        /* start param datatables */
        $limit= $data['limit'];
        $order= $data['order'];
        $kondisi= trim($data['condition']);
        if($kondisi!="")$kondisi=" and $kondisi";
        /* end param datatables */


        $sql= "SELECT * FROM `{$this->prefix}_person` {$kondisi} {$order} LIMIT {$limit}";
        $res = $this->fetch($sql,1);

        $rowsFilter = $this->fetch("SELECT FOUND_ROWS() AS total");
        $rowsTotal = $this->fetch("SELECT COUNT(id) AS total FROM {$this->prefix}_person");

        if ($res){

            $dataArray['dataset'] = $rowsFilter['total'];
            $dataArray['dataTotal'] = $rowsTotal['total'];
            $dataArray['data'] = $res;

            return $dataArray;
        }
        return false;
    }
	
    /**
     * @todo retrieve all data from table indiv from selected taxon
     * 
     * @param $value=id taxon
     * @return 
     */
    function dataIndivTaxon($value, $start=0, $limit=20){
        $sql = "SELECT * 
                FROM `{$this->prefix}_det` INNER JOIN `{$this->prefix}_indiv` ON 
                    {$this->prefix}_det.taxonID='$value' AND {$this->prefix}_det.indivID={$this->prefix}_indiv.id AND {$this->prefix}_indiv.n_status='0'
                INNER JOIN `{$this->prefix}_person` ON
                    {$this->prefix}_indiv.personID={$this->prefix}_person.id
                INNER JOIN `{$this->prefix}_locn` ON
                    {$this->prefix}_locn.id={$this->prefix}_indiv.locnID
                GROUP BY {$this->prefix}_det.indivID LIMIT {$start}, {$limit}";
        
        $res = $this->fetch($sql,1);
        $return['result'] = $res;
        return $return;
    }

    /**
     * @todo retrieve images from indiv data
     * @param $data = id indiv
     */
    function showImgIndiv($data){
        $sql = "SELECT * FROM `{$this->prefix}_img` WHERE indivID='$data' AND md5sum IS NOT NULL LIMIT 0,5";
        $res = $this->fetch($sql,1);
        return $res;
    }

    /**
     * @todo retrieve all data from table indiv from selected location
     * 
     * @param $value=id location
     * @return 
     */
    function dataIndivLocation($value, $start=0, $limit=20){
        $sql = "SELECT {$this->prefix}_indiv.id as indivID, {$this->prefix}_indiv.locnID, {$this->prefix}_indiv.plot, {$this->prefix}_indiv.tag, {$this->prefix}_indiv.personID, {$this->prefix}_locn.*, {$this->prefix}_person.*
                    FROM `{$this->prefix}_indiv` INNER JOIN `{$this->prefix}_locn` ON 
                        $value={$this->prefix}_indiv.locnID AND {$this->prefix}_indiv.n_status='0'
                    INNER JOIN `{$this->prefix}_person` ON
                        {$this->prefix}_indiv.personID={$this->prefix}_person.id
                    GROUP BY {$this->prefix}_indiv.id LIMIT {$start}, {$limit}";
        
        $res = $this->fetch($sql,1);
        $return['result'] = $res;
        return $return;
    }

    /**
     * @todo retrieve all data from table indiv from selected person
     * 
     * @param $value=id person
     * @return 
     */
    function dataIndivPerson($value, $start=0, $limit=20){
        $sql = "SELECT {$this->prefix}_indiv.id as indivID, {$this->prefix}_indiv.locnID, {$this->prefix}_indiv.plot, {$this->prefix}_indiv.tag, {$this->prefix}_indiv.personID, {$this->prefix}_locn.*, {$this->prefix}_person.*
                    FROM `{$this->prefix}_indiv` INNER JOIN `{$this->prefix}_locn` ON 
                        $value={$this->prefix}_indiv.personID AND {$this->prefix}_indiv.n_status='0'
                    INNER JOIN `{$this->prefix}_person` ON
                        $value={$this->prefix}_person.id
                    INNER JOIN `{$this->prefix}_det` ON
                        {$this->prefix}_indiv.id={$this->prefix}_det.indivID
                    GROUP BY {$this->prefix}_indiv.id LIMIT {$start}, {$limit}";
        
        $res = $this->fetch($sql,1);
        $return['result'] = $res;
        return $return;
    }

    /**
     * @todo retrieve all indiv detail
     * @param $data = id indiv
     */
    function detailIndiv($data, $start=0, $limit=20){
        $sql = "SELECT * 
                FROM `{$this->prefix}_indiv` INNER JOIN `{$this->prefix}_locn` ON 
                    {$this->prefix}_indiv.id='$data' AND {$this->prefix}_locn.id={$this->prefix}_indiv.locnID AND {$this->prefix}_indiv.n_status='0'
                INNER JOIN `{$this->prefix}_person` ON
                    {$this->prefix}_person.id={$this->prefix}_indiv.personID LIMIT {$start}, {$limit}";
        $res = $this->fetch($sql,1);
        return $res;
    }

    /**
     * @todo retrieve all images from indiv data
     * @param $data = id indiv
     */
    function showAllImgIndiv($data, $start=0, $limit=20){
        $sql = "SELECT * FROM `{$this->prefix}_img` WHERE indivID='$data' AND md5sum IS NOT NULL LIMIT {$start}, {$limit}";
        $res = $this->fetch($sql,1);
        return $res;
    }
    
    /**
     * @todo retrieve all det from indiv selected
     * @param $data = id indiv
     */
    function dataDetIndiv($data, $start=0, $limit=20){
        $sql = "SELECT {$this->prefix}_det.id as detID, det.*, taxon.*,person.* 
                FROM `{$this->prefix}_det` INNER JOIN `{$this->prefix}_taxon` ON 
                    indivID='$data' AND {$this->prefix}_taxon.id={$this->prefix}_det.taxonID AND {$this->prefix}_det.n_status='0'
                INNER JOIN `{$this->prefix}_person` ON
                    {$this->prefix}_person.id={$this->prefix}_det.personID LIMIT {$start}, {$limit}";
        $res = $this->fetch($sql,1);
        return $res;
    }
    
    /**
     * @todo retrieve all obs from indiv selected
     * @param $data = id indiv
     */
    function dataObsIndiv($data, $start=0, $limit=20){
        $sql = "SELECT {$this->prefix}_obs.id as obsID, {$this->prefix}_obs.*, {$this->prefix}_person.* 
                FROM `{$this->prefix}_obs` INNER JOIN `{$this->prefix}_person` ON 
                    indivID='$data' AND {$this->prefix}_person.id={$this->prefix}_obs.personID AND {$this->prefix}_obs.n_status='0' LIMIT {$start}, {$limit}";
        $res = $this->fetch($sql,1);
        return $res;
    }
}
?>