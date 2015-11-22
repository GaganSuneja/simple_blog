<?php
error_reporting(E_ALL);

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
else
{
	header('Location: /simple_blog/admin/');
	exit;
}

?>
