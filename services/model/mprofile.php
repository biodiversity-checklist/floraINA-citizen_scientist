<?php
class mprofile extends Database {
	
	function get_profile()
	{
		$query = "SELECT id,title,brief,content,tags FROM cdc_news_content WHERE n_status = '3'";
		$res = $this->fetch($query,1);
		if ($res) return $res;
		return false;
	}
	
	function profile_inp($brief,$content,$title,$id,$tags,$action)
	{
		if($action == 'insert'){
			$query = "INSERT INTO 
						cdc_news_content (title,brief,content,tags,n_status)
						VALUES
							('{$title}','{$brief}','{$content}','{$tags}','3')";
		} else {
			$query = "UPDATE cdc_news_content
						SET
							title = '{$title}',
							brief = '{$brief}',
							content = '{$content}'
						WHERE
							id = '{$id}'";
		}
		$result = $this->query($query);
		
		return $result;
	}
}
?>