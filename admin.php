<?php

include_once 'inc/functions.inc.php';
include_once 'inc/db.inc.php';

$db = new PDO(DB_INFO,DB_USER,DB_PASS);

if(isset($_GET['page']))
{
	$page = htmlentities(strip_tags($_GET['page']));
}
else
{
	$page = 'blog' ;
}

if(isset($_POST['action'])&&$_POST['action']=='delete')
{
	 if($_POST['submit']=='Yes')
	 {
	 	$url = htmlentities(strip_tags($_POST['url']));
	 	if(deleteEntry($db,$url))
	 	{
	 		header("Location: /simple_blog/");	
	 		exit;
	 	}
	 	else
	 	{
	 		exit("Error deleting Entry");
	 	}
	 }
	 else
	 { 
		header("Location: /simple_blog/blog/$url");
	 	exit;
	 }
}

if(isset($_GET['url']))
{
  
  $url=htmlentities(strip_tags($_GET['url']));

  $legend="Edit this Entry";

	if($page=='delete')
	{
	 $confirm= confirmDelete($db,$url);
	}

  $e=retrieveEntries($db,$page,$url);

  $id= $e['id'];
  $entry = $e['entry'];
  $title = $e['title'];
}
else
{

	 $legend = "New Entry Submission";

	 $id = NULL;
	 $entry = NULL;
  	 $title = NULL;
}


?>
<!DOCTYPE html
	PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type"
	content="text/html;charset=utf-8" />
	<link rel="stylesheet" href="/css/default.css" type="text/css"/>
	<title> Simple Blog </title>
</head>
<body>
	<h1> Simple Blog Application </h1>
<?php
		  if($page=='delete')
		  {
		  	echo $confirm;
		  }		  
		  
		  else{

 ?>
			 <form method="post" enctype="multipart/form-data"action="/simple_blog/inc/update.inc.php">
				<fieldset>
					<legend><?php  echo $legend ?></legend>
					<label>Title
						<input type="text" value="<?php echo htmlentities($title) ; ?>" name="title" maxlength="150" />
					</label>
					<label>Image
						<input type="file" name="image">	
					</label>
					<label>Entry
						<textarea name="entry"  cols="45" rows="10"><?php echo sanitizeData($entry); ?></textarea>
					</label>
					<input type="hidden" name="id"   value="<?php echo $id ?>">
					<input type="hidden" name="page" value="<?php echo $page ?>" />
					<input type="submit" name="submit" value="Save Entry" />
					<input type="submit" name="submit" value="Cancel" />
				</fieldset>
			</form>

<?php  
   	         }
?>

</body>
</html>