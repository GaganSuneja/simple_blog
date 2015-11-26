<?php

function  retrieveEntries($db,$page,$url=NULL)
{
	if(isset($url))
	{
		$e=NULL;
		$sql = "SELECT id,image,page,title,entry,created
		FROM entries
		WHERE url=?
		LIMIT 1";
		$stmt = $db->prepare($sql);
		$stmt->execute(array($url));
// Save the returned entry array
		$e = $stmt->fetch();
// Set the fulldisp flag for a single entry
		$fulldisp = 1;
	}
	else
	{
				$sql = "SELECT id,image,page,title,entry,url,created FROM entries WHERE page=? ORDER BY created DESC";
			    /* error in sq query* above*/
			    $stmt = $db->prepare($sql);
				
				$stmt->execute(array($page));
				
				$e =NULL;
				
					while($row = $stmt->fetch())
					 {
							if($page=='blog')
							{
								$e[] = $row;
								$fulldisp = 0;
							}
							else
							{
								$e = $row;
								$fulldisp = 1;
							}
					 }
						
			
				if(!is_array( $e ) )
				{
					$fulldisp = 1;
					$e = array('url'=>NULL,'title'=>'No Entries Yet','image'=>'NO image Yet!','entry'=>'This page has no entries yet!!!');
				}
			
    }	
		
		array_push($e,$fulldisp);
		
		return $e;
	
}
 function adminLinks($page , $url)
 {
 	

 	$editUrl="/simple_blog/admin/$page/$url";
 	$deleteUrl="/simple_blog/admin/delete/$url/";
 	
 	$admin['edit']= "<a href=\"$editUrl\">Edit!</a>";
 	$admin['delete']="<a href=\"$deleteUrl\">Delete!</a>";
 	
 	return $admin;

 }

function sanitizeData($data)
{

	if(!is_array($data))
	{
		return strip_tags($data,"<a>");
	}
	else
	{

		return array_map('sanitizeData',$data);

	}
}
function makeUrl($title)
{

	$patterns = array('/\s+/','/(?!-)\W+/');
	
	$replacement = array('-','');

	return preg_replace($patterns,$replacement,strtolower($title));
} 

function confirmDelete($db,$url)
{
	$e = retrieveEntries($db,'',$url);

	return <<<FORM
    <form action="/simple_blog/admin.php" method="post">
	   <fieldset>
	  		<legend>Are you Sure!</legend>
	  		<p>Are you sure you want to delete an entry "$e[title]"?</p>
	  		<input type="submit" name="submit" value="Yes"/>
	   		<input type="submit" name="submit" value="No"/>
	   		<input type="hidden" name="action" value="delete" />
	   		<input type="hidden" name="url" value="$url"/>
	   </fieldset>
	</form>
	
FORM;
}

function deleteEntry($db,$url)
{
	$sql =" DELETE FROM entries
			WHERE url=?
			LIMIT 1";
	
	$stmt=$db->prepare($sql);
	
	return $stmt->execute(array($url));			
}

function FormatImages($img=NULL,$alt=NULL)
{
	if(isset($img))
	{
		return '<img src="'.$img.'"alt="'.$alt.'"/>';
	}
	else
	{
		return NULL;
	}
}

function createUserForm()
{
return <<<FORM
<fieldset>
<legend>Add new user</legend>
<form action="/simple_blog/inc/update.inc.php" method="post">
<label>Username:
<input type="text" name="username" />
</label>
<label>Password:
<input type="password" name="password"/>
</label>
<input type="hidden" name="action"  value="createuser"/>
<input type="submit" name ="submit" value="sign up"/>
<input type="submit" name ="submit" value="Cancel"/>
</form>
</fieldset>	
FORM;
}

function  shortenUrl($url)
{
// Format a call  to bit.ly API
$api = 'http://api.bit.ly/shorten';
$param = 'version=2.0&longUrl='.urlencode($url).'&login=o_5de1dgtk9d'.'&apiKey=R_55b4d5db7a8d46039dfb1db11dc7f4c2&format=xml';

// Open a aconnection and load the response

$uri = $api."?".$param;

$response = file_get_contents($uri);

//Parse the output and return the url

$bitly = simplexml_load_string($response);
 
return $bitly->results->nodeKeyVal->shortUrl; 
}

function postToTwitter($title) {

$full ='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

$short = shortenUrl($full);

$status = $title.''.$short;

return 'http://twitter.com/?status='.urlencode($status);
}

?>