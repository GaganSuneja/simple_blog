<?php
include_once 'db.inc.php';
class Comments
{
// Our database connection
	public $db;
//Array containing all the comments
	public $comments;
// Upon class instantiation, open a database connection
	public function __construct()
	{
// Open a database connection and store it
		$this->db = new PDO(DB_INFO, DB_USER, DB_PASS);
	}

/**
 * Display a form for users to enter new comments with
 *
 * @param int $blog_id the id the of the blog
 * @return a Comment form in HERODOC 
 */
public function showCommentForm($blog_id)
{
	return <<<FORM
	<form action="/simple_blog/inc/update.inc.php"
	method="post" id="comment-form">
	<fieldset>
		<legend>Post a Comment</legend>
		<label>Name
			<input type="text" name="name" maxlength="75" />
		</label>
		<label>Email
			<input type="text" name="email" maxlength="150" />
		</label>
		<label>Comment
			<textarea rows="10" cols="45" name="comment"></textarea>
		</label>
		<input type="hidden" name="blog_id" value="$blog_id" />
		<input type="submit" name="submit" value="Save Comment" />
		<input type="submit" name="submit" value="Cancel" />
	</fieldset>
</form>
FORM;
}

/**
 *Save the comments to database
 *
 * @param array p having all the $_POST argments 
 * @return BOOLEAN TRUE||FALSE
 */
public function saveComments($p)
{
	// Sanitize the data and store in variables
	$blog_id = htmlentities(strip_tags($p['blog_id']),ENT_QUOTES);
	$name = htmlentities(strip_tags($p['name']),ENT_QUOTES);
	$email = htmlentities(strip_tags($p['email']),ENT_QUOTES);
	$comment= htmlentities(strip_tags($p['comment']),ENT_QUOTES);
	//keep the formatting of comments and remove whitespaces
	$comment = nl2br(trim($comment)); 
	// Generate the sql query
	$sql = "INSERT INTO comments (blog_id,name,email,comment) VALUES(?,?,?,?)";

	if($stmt = $this->db->prepare($sql))
	{
		$stmt->execute(array($blog_id,$name,$email,$comment));
		$stmt->closeCursor();
		return TRUE;
	}
	else
	{
		// If something went wrong
		return FALSE;
	}


}
/**
 *retrives the comments from comments table
 *
 * @param int blog_id the id of the blog 
 * @return void
 */

public function retrieveComments($blog_id)
{
	$sql="SELECT id, name, email, comment,date
FROM comments
WHERE blog_id=?
ORDER BY date DESC";

	$stmt = $this->db->prepare($sql);

	$stmt->execute(array($blog_id));
	//looping through all the comments
	while($comment=$stmt->fetch())
	{
		$this->comments[]=$comment;
	}
	 //set up default comment if no comment exists
	if(empty($this->comments))
	{
		$this->comments[]=array('name'=>NULL,'id'=>NULL,'comment'=>'NO comment Yet!','email'=>NULL,'date'=>NULL);
		
	}
}
/**
  *shows the comments on a page
  *
  *@param array blog_id
  *@return string 
  */


public function showComments($blog_id)
{
// Initialize the variable in case no comments exist
$display = NULL;
// Load the comments for the entry
$this->retrieveComments($blog_id);
// Loop through the stored comments
foreach($this->comments as $c)
{
// Prevent empty fields if no comments exists

// Outputs similar to: July 8, 2009 at 4:39PM
if(!empty($c['date']&&!empty($c['name'])))
{
$format = "F j, Y \a\\t g:iA";
// Convert $c['date'] to a timestamp, then format
$date = date($format,strtotime($c['date']));
// Generate a byline for the comment
$byline = "<span><strong>$c[name]</strong>
[Posted on $date]</span>";
		if(isset($_SESSION['loggedin'])&&$_SESSION['loggedin']==1)
		{
		// Generate delete link for the comment display
			$admin = "<a href=\"/simple_blog/inc/update.inc.php"
			. "?action=comment_delete&id=$c[id]\""
			. "class=\"admin\">delete</a>";
		}
		else
		{
			$admin = NULL;
		}
}
else
{
	$admin  = NULL;
	$byline = NULL;
	# code...
}

// Assemble the pieces into a formatted comment
$display .= "<p class=\"comment\">$byline$c[comment]$admin</p>";
}
// Return all the formatted comments as a string
return $display;
}
function confirmDelete($id)
{
	// Store teh entry url if available
	if(isset($_SERVER['HTTP_REFERER']))
	{
		$url = $_SERVER['HTTP_REFERER'];
	}
	else
	{
			# code...
		$url = "../";
	}

	return <<<FORM
<html>
<title>Confirm Delete</title>
<link rel="styesheet" href="/simple_blog/css/default.css" text="text/css"></link>
<body>
<form method="post" action="/simple_blog/inc/update.inc.php">
<fieldset>
<legend>Confirm Your Delete</legend>
<p>
Are Your Sure You want yo delete this comment
</p>
<input type="hidden" name="id" value="$id"/>
<input type="hidden" name="action" value="comment_Delete"/>
<input type="hidden" name="url" value="$url"/>
<input type="submit" name="submit" value="delete"/>
<input type="submit" name="submit" value="cancel"/>
</fieldset>
</form>
</body>
</html>
FORM;
}
public function DeleteComment($id)
{

	// Prepare the sql query
	$sql="DELETE FROM comments WHERE id=? LIMIT 1";

	//Prepare the sql query
	$stmt=$this->db->prepare($sql);
	if($stmt->execute(array($id)))
	{
		$stmt->closeCursor();
		return TRUE;
	}
	else
	{
		//iF Something went wrong
		return FALSE;
	}
}
}
?> 