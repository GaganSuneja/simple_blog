<?php
	include_once 'inc/db.inc.php';
	include_once 'inc/functions.inc.php';
	
	$db = new PDO(DB_INFO, DB_USER, DB_PASS);

  // Determine if an entry ID was passed in the URL
	
	if(isset($_GET['page']))
	{
		$page = htmlentities(strip_tags($_GET['page']));
	}
	else
	{
		$page = 'blog';
	}
	

	$url = (isset($_GET['url'])) ?  $_GET['url'] : NULL;


	$e=NULL;
	
	$e = retrieveEntries($db,$page,$url);
	
	$fulldisp = array_pop($e);
    // Sanitize the entry data
	$e = sanitizeData($e);
	//echo $e['title'];
?>
	<!DOCTYPE html
	PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type"
		content="text/html;charset=utf-8" />
		<link rel="stylesheet" href="css/default.css" type="text/css"/>
		<title> Simple Blog </title>
	</head>
	<body>
		<h1> Simple Blog Application </h1>
		<ul id="menu">
			<li><a href="/simple_blog/blog/">Blog</a></li>
			<li><a href="/simple_blog/about/">About the Author</a></li>
		</ul>
		<div id="entries">
<?php
			// If the full display flag is set, show the entry
			
			if($fulldisp==1)
			{

			// Get the url if was not passed
			 
			 $url = (isset($url)) ? $url : $e['url'];
			 
			 $admin=adminLinks($page,$url); 

			 $imagee = FormatImages($e['image'],$e['title']);

?>				
				<h2> <?php  echo $e['title']; ?> </h2>
				<p>  <?php  echo $e['entry']; ?> </p>
				<p>  <?php  echo $imagee; ?> </p>
				<p>
					 <?php  echo $admin['edit'];?>
					<?php 
						if($page=='blog') 
						 {
						 	echo $admin['delete'];
						 }
					?>
				</p>

				<?php if($page=='blog'): ?>
				<p class="backlink">
					<a href="/simple_blog/">Back to Latest Entries</a>
				</p>
				<?php endif;?>
				<?php
			} // End the if statement
			else
			{
					foreach( $e as $entry)
					{
				?>		
					<p>
					   <a href="/simple_blog/<?php echo $entry['page'] ?>/<?php echo $entry['url'] ?>"><?php echo $entry['title'] ;?></a>
					</p>
			<?php
					} // End the foreach loop
				} // End the else
			?>
			
			<p class="backlink">
				<a href="/simple_blog/admin.php">
				Post a New Entry
				</a>
			</p>
		</div>
	</body>
	</html>