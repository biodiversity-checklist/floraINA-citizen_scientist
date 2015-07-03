<?php
class marticle extends Database {
	
	function article_inp($data)
	{
		
		$data['title'] = cleanText($data['title']);
		$data['brief'] = cleanText($data['brief']);
		$data['content'] = cleanText($data['content']);
		
		$date = date('Y-m-d H:i:s');
		$datetime = array();
		
		if(!empty($data['expiredate'])) $data['expiredate'] = date("Y-m-d",strtotime($data['expiredate'])); 
		
		if($data['action'] == 'insert'){
			$query = "INSERT INTO  
						cdc_news_content (title,brief,content,image,thumbnailimage,categoryid,articletype,
											tags,createdate,postdate,expiredate,fromwho,authorid,n_status)
					VALUES
						('".$data['title']."','".$data['brief']."','".$data['content']."','".$data['image']."','".$data['thumbnailimage']."','".

							$data['categoryid']."','".$data['articletype']."','".$data['tags']."','".$date."','".date("Y-m-d",strtotime($data['postdate']))."','".
							$data['expiredate']."','".$_SESSION['admin']['usertype']."','".$_SESSION['admin']['id']."',{$data['status']})";

		} else {
			$query = "UPDATE cdc_news_content
						SET 
							title = '{$data['title']}',
							brief = '{$data['brief']}',
							content = '{$data['content']}',
							image = '{$data['image']}',
							thumbnailimage = '{$data['thumbnailimage']}',
							categoryid = '{$data['categoryid']}',
							articletype = '{$data['articletype']}',
							tags = '{$data['tags']}',
							postdate = '".date("Y-m-d",strtotime($data['postdate']))."',
							expiredate = '".$data['expiredate']."',
							fromwho = '{$_SESSION['admin']['usertype']}',
							authorid = '{$_SESSION['admin']['id']}',
							n_status = {$data['status']}
						WHERE
							id = '{$data['id']}'";
		}
// pr($query);
		$result = $this->query($query);
		
		return $result;
	}
	
	function get_article($type=1)
	{
		$query = "SELECT nc.*, cc.category, ct.type FROM cdc_news_content nc LEFT JOIN cdc_news_content_category cc 
					ON nc.categoryid = cc.id LEFT JOIN cdc_news_content_type ct ON nc.articletype = ct.id 
					WHERE nc.n_status < 2 AND nc.categoryid = {$type} ORDER BY nc.createdate DESC";
		
		$result = $this->fetch($query,1);
		
		return $result;
	}
	
	function get_article_slide()
	{
		$query = "SELECT nc.*, cc.category, ct.type FROM cdc_news_content nc LEFT JOIN cdc_news_content_category cc 
					ON nc.categoryid = cc.id LEFT JOIN cdc_news_content_type ct ON nc.articletype = ct.id 
					WHERE nc.n_status < 2 AND nc.articletype > 0  ORDER BY nc.createdate DESC";
		
		$result = $this->fetch($query,1);
		
		return $result;
	}
	
	function get_article_trash($type=1)
	{
		$query = "SELECT nc.*, cc.category, ct.type FROM cdc_news_content nc LEFT JOIN cdc_news_content_category cc 
					ON nc.categoryid = cc.id LEFT JOIN cdc_news_content_type ct ON nc.articletype = ct.id 
					WHERE nc.n_status = 2 AND nc.articletype = {$type} ORDER BY nc.createdate DESC";
		
		$result = $this->fetch($query,1);
		
		return $result;
	}
	
	function article_del($id)
	{
		$query = "UPDATE cdc_news_content SET n_status = '2' WHERE id = '{$id}'";
		
		$result = $this->query($query);
		
		return $result;
		
	}
	
	function article_delpermanent($id)
	{
		$query = "DELETE FROM cdc_news_content WHERE id = '{$id}'";
		
		$result = $this->query($query);
		
		return $result;
		
	}
	
	function article_restore($id)
	{
		$query = "UPDATE cdc_news_content SET n_status = '0' WHERE id = '{$id}'";
		
		$result = $this->query($query);
		
		return $result;
		
	}
	
	function get_article_id($data)
	{
		$query = "SELECT * FROM cdc_news_content WHERE id= {$data}";
		
		$result = $this->fetch($query,1);

		if(isset($result[0]['postdate'])){
			$result[0]['postdate'] = date('d-m-Y',strtotime($result[0]['postdate']));
		}
		if(isset($result[0]['expiredate'])){
			$result[0]['expiredate'] = date('d-m-Y',strtotime($result[0]['expiredate']));
		}

		
		return $result;
	}
	
	function get_category()
	{
		$query = "SELECT * FROM cdc_news_content_category WHERE id < 3";
		
		$result = $this->fetch($query,1);
		
		return $result;
	}
	
	function slide_activator($id)
	{
		$query = "SELECT articletype FROM cdc_news_content WHERE id = {$id}";
		
		$result = $this->fetch($query,1);
		
		if($result[0]['articletype']==1){
			$articletype = 11;
		} else {
			$articletype = 1;
		}
		
		$query = "UPDATE cdc_news_content SET articletype={$articletype} WHERE id={$id}";
		
		$result = $this->query($query);
	}
	
	function slide_remove($id)
	{
		
		$query = "UPDATE cdc_news_content SET articletype=0 WHERE id={$id}";
		
		$result = $this->query($query);
	}
}
?>