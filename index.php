<?php
	

	session_start();

	include_once 'inc/db.inc.php';
	include_once 'inc/functions.inc.php';
	include_once 'inc/comments.inc.php';
	
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
		<link rel="alternate" type="application/rss+xml"
title="My Simple Blog - RSS 2.0"
href="/simple_blog/feeds/rss.php" />
		<title> Simple Blog </title>
	</head>
	<body>
		<h1> Simple Blog Application </h1>
		<ul id="menu">
			<li><a href="/simple_blog/blog/">Blog</a></li>
			<li><a href="/simple_blog/about/">About the Author</a></li>
		</ul>
		<?php if(isset($_SESSION['loggedin']) && $_SESSION['loggedin']==1): ?>
			<p id="control_panel">
								You are logged in!
				<a href="/simple_blog/inc/update.inc.php?action=logout">Log
									out</a>.
				</p>
		<?php endif; ?>
		<div id="entries">
<?php
			// If the full display flag is set, show the entry
			
			if($fulldisp==1)
			{

			// Get the url if was not passed
			 $url = (isset($url)) ? $url : $e['url'];
			 if(isset($_SESSION['loggedin'])&&$_SESSION['loggedin']==1)
			 {
			 	$admin=adminLinks($page,$url); 
			 }
			 else
			 {
			 	$admin = array('edit'=>NULL,'delete'=>NULL);
			 }

			 $imagee = FormatImages($e['image'],$e['title']);

			 if($page=='blog')
			 {
			 	// Load the comment object
			 	include_once 'inc/comments.inc.php';
			 	$comments = new Comments();
			 	$comment_disp = $comments->showComments($e['id']);
			    $comments_form =$comments->showCommentForm($e['id']);
			 	
			 	//Generate a post to twitter link;
			 	$twitter = postToTwitter($e['title']);
			 }
			 else
			 {
			 	$comments_form = NULL;
			 	$twitter 	   = NULL;
			 }

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
					<a href="<?php echo $twitter?>">Post TO Twitter!</a></br>
				</p>
				<h3> Comments for This Entry </h3>
				<?php echo $comment_disp,	$comments_form; endif; ?>
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
				<?php
				if($page=='blog'&&isset($_SESSION['loggedin'])&&$_SESSION['loggedin']==1)
			    {
			    ?>
				<a href="/simple_blog/admin/">
				Post a New Entry
				</a>
			   <?php
				}
			    else
			    {
			    
			    ?>
			 	<a href="/simple_blog/admin/">Login!</a>
			 	<?php
			    }
			 	?>
			 </p>
			 <a href="/simple_blog/admin/createUser">SignUp</a>
			 </div>			
			<p>
			<a href="/simple_blog/feeds/rss.php">
				Suscribe via RSS!
			</a>	
			</p>
		</div>
	</body>
	</html>