<?php
error_reporting(E_ALL);

session_start();
include_once 'functions.inc.php';

if($_SERVER['REQUEST_METHOD']=='POST'
	&&$_POST['submit']=='Save Entry'
	&&!empty($_POST['title'])
	&&!empty($_POST['entry'])
	&&!empty($_POST['page']))
{
    

    include_once 'image.inc.php';
	include_once 'db.inc.php';


    	if(isset($_FILES['image']['tmp_name']))
		{
				try
				{
					// Instantiate the object
					$img = new ImageHandler("/simple_blog/images/");
					
					// save the filepath of the object
					$image_path = $img->processUploadedImage($_FILES['image']);
					
			
				}
				catch(Exception $e)
				{
				// If an error occurred, output your custom error message
				   die($e->getMessage());
				}
		}
		else
		{
			$image_path = NULL;		
		}
			



	$url = makeUrl($_POST['title']);

	$db = new PDO(DB_INFO, DB_USER, DB_PASS);

	

	if(!empty($_POST['id']))
	{
		$sql="UPDATE entries
			  set title=?,image=?,entry=?,url=?
			  WHERE id=?
		      LIMIT 1";
		$stmt = $db->prepare($sql);
	    $stmt->execute(array($_POST['title'],$image_path,$_POST['entry'],$url,$_POST['id']));

		$stmt->closeCursor();
	}
	else
	{

		$sql = "INSERT INTO entries (title,image,entry,page,url) VALUES (?,?,?,?,?)";

		$stmt = $db->prepare($sql);

		$page = $_POST['page'];

		$page = preg_replace('/(?!-)\W+/','', $page);

		$stmt->execute(array($_POST['title'],$image_path,$_POST['entry'],$page,$url));

		$stmt->closeCursor();
	}

	 $page = $_POST['page'];

     $page = preg_replace('/(?!-)\W+/','', $page);

	 $page = htmlentities(strip_tags($page));

	header('Location: /simple_blog/'.$page.'/'.$url);
	exit;

}
// If a comment is being posted handle it here
else if($_SERVER['REQUEST_METHOD']=='POST'
	&&$_POST['submit']=='Save Comment')
{
	
	include_once 'comments.inc.php';
	// Instantiate the object of the Comment Class
	$comments =  new Comments();
	if($comments->saveComments($_POST))
	{
		if(isset($_SERVER['HTTP_REFERER']))
		{
			$loc = $_SERVER['HTTP_REFERER'];
		}
		else
		{
			$loc = "../";
		}
		header('Location:'.$loc);
		exit;
	}
	else
	{
	 	
		exit('Something Went Wrong Looking for the solution');

	}
}
elseif ($_GET['action']=='comment_delete') 
{
	# code...
	include_once 'comments.inc.php';
	$comment_delete =  new Comments();
	echo $comment_delete->confirmDelete($_GET['id']);
	exit;
}

elseif($_SERVER['REQUEST_METHOD']=='POST' && $_POST['action']=='comment_Delete')
{
    // If set store the entry from which it came
    $loc = isset($_POST['url'])?$_POST['url']:'../';

    //
    if($_POST['submit']=='delete')
    {
    	include_once 'comments.inc.php';
    	
    	$comments = new Comments();
    	if($comments->DeleteComment($_POST['id']))
    	{
    		header('Location:'.$loc);
    		exit;
    	}
    	else
    	{
  		 exit("could not delete the comment");
    	}

    }
    else
    {

    	header('Location:'.$loc);
    	exit;
    }
}
// If a user is trying to log in, check it here
else if($_SERVER['REQUEST_METHOD'] == 'POST'
&& $_POST['action'] == 'Login'
&& !empty($_POST['username'])
&& !empty($_POST['password']))
{
// Include database credentials and connect to the database
include_once 'db.inc.php';
$db = new PDO(DB_INFO, DB_USER, DB_PASS);
$sql = "SELECT COUNT(*) AS num_users
FROM admin
WHERE username=?
AND password=SHA1(?)";
$stmt = $db->prepare($sql);
$stmt->execute(array($_POST['username'], $_POST['password']));
$response = $stmt->fetch();
if($response['num_users'] > 0)
{
$_SESSION['loggedin'] = 1;
}
else
{
$_SESSION['loggedin'] = NULL;
}
header('Location: /simple_blog/');
exit;
}

// If an admin is being created, save it here
else if($_SERVER['REQUEST_METHOD'] == 'POST'
&& $_POST['action'] == 'createuser'
&& !empty($_POST['username'])
&& !empty($_POST['password']))
{
// Include database credentials and connect to the database
include_once 'db.inc.php';
$db = new PDO(DB_INFO, DB_USER, DB_PASS);
$sql = "INSERT INTO admin (username, password)
VALUES(?, SHA1(?))";
$stmt = $db->prepare($sql);
$stmt->execute(array($_POST['username'], $_POST['password']));
header('Location: /simple_blog/');
exit;
}
elseif ($_GET['action']=='logout') {
	session_destroy();
	header('Location: ../');
	exit();
}
else
{
	header('Location: /simple_blog/admin/');
	exit;
}

?>
