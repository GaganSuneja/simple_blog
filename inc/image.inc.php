<?php
class ImageHandler
{
  public $save_dir;
  public $max_dims;
   
  public function __construct($save_dir,$max_dims=array(350,240))
  {
    $this->save_dir=$save_dir;
  	$this->max_dims=$max_dims;
  }

/**
 *Resizes/resamples images uploaded via a web form
 *
 *@param array $upload containg file information $_FILES
 *@param bool  $rename wheather or not image should be renamed
 *@return string the path to the resized uploaded file
 */
 public function processUploadedImage($file,$rename=TRUE)
 {
   //separate the uploaded file array
   list($name,$type,$tmp,$err,$size) = array_values($file);

  //finish the processing
    if($err != UPLOAD_ERR_OK)
    {
      throw new Exception('An error was encountered try again');
      return;
    }
    
    
    //	check if the directory exist
     $this->checkSaveDir();
 
     if($rename==TRUE)
     {
     	// get the image extension
     	$img_ext = $this->getImageExtension($type) ;
     	// rename the file
     	$name = $this->renameFile($img_ext);
     }

     // create the full path to save the file
     $filepath=$this->save_dir.$name;
     // store the absolute path to move the file
     $absolute= $_SERVER['DOCUMENT_ROOT'].$filepath;

     if(!move_uploaded_file($tmp, $absolute))
    {
      throw new Exception("Couldn't save the uploaded file!");
    }
	// resizes the image  	
  	$this->doImageResize($tmp);
    return $filepath;
 }

 /**
  *checks if the save dirctory exists
  *
  *checks for the existence of the save directory
  *and creates directory if does not exists.Creation is 
  *recursive
  * 
  *@param void 
  *@return void
  */

		public function checkSaveDir()
		{
			// determines the path to check
			$path = $_SERVER['DOCUMENT_ROOT'].$this->save_dir;
			// check if this path is a directory
			if(!is_dir($path))
			{
				//Create the Directory
				if(!mkdir($path,0777,TRUE))
				{
					// throws an error if could not create Directory
					throw new Exception('could not create directory');
				}
			}
		}

/**
 * Renaming the uploaded file
 *
 *Uses the current timestamp and a random numbe generated
 *to create a unique name to identify the images
 *This helps prevent the new file upload from overwriting an
 *existing file with the same name
 *
 *@param string $ext having the file extension to upload 
 *@return string the new file name
 */

	private function renameFile($ext)
	{
	 /*
	  *Return the curent time stamp and a random number
	  * to aviod duplicate filenames
	  */	
	 return time().'_'.mt_rand(1000,9999).$ext;
	}
/**
 * Gets the extension of uploaded file
 *
 *@param string $type the MIME type of the image
 *@return string the extension of the file to be used
 */

	private function getImageExtension($type)
	{
		switch($type)
		{
			case 'image/gif':
				return '.gif';
			case 'image/png':
			    return '.png';
			case 'image/jpeg':
				case 'image/pjpeg':
					return '.jpg';
			defaut:
			     throw new Exception('File type not recogonized');       
		}
	}

/**
 *Determines the dimensions for an image 
 *
 *@param  string $img the path to the upload
 *@return array the new  and the orignal dimensions of the image
 */
	private function getImageDimensions($img)
	{
		list($src_w,$src_h)=getimagesize($img);
		
		list($max_w,$max_h)=$this->max_dims;


		    if($src_w>$max_w||$src_h>$max_h)
		    {
			  	// determine the lowest ratio to multiply
			 	$s = min($max_w/$src_w,$max_h/$src_h);
		    }
		else{
				# code...
			 $s=1;
			}	
	
		   // get the new dimensions

			$new_h = $s*$src_h;
			$new_w = $s*$src_w;

		  // return the new dimensions

			return array($new_w,$new_h,$src_w,$src_h);
	}

/**
 * Determine how to process images
 *
 * Use MIME type of the provided image to detect 
 * which image handling function to use.This 
 * increases the performance as compared to i4
 * image create from string
 *
 * @param string $img the path to image upload  
 * @return array to the specific image-type function to use
*/

private function getImageFunction($img)
{
	$info = getImagesize($img);

	switch($info['mime'])
	{
		case 'image/jpeg':
	 	case 'image/pjpeg':
                return array("imagecreatefromjpeg",'imagejpg');
	            break; 
	    case 'image/png':
                return array("imagecreatefrompng","imagepng");
	            break; 
	    case 'image/gif':
	            return array("imagecreatefromgif","imagegif");
	            break;
	    default :return FALSE;
	             break;        
	}
}

/**
 * Generates the resampled and resized image
 *
 * Creates and saves the new image in new dimensions
 * and image type-specific functions determined by 
 * other class methods
 *
 *@param array $img the path to upload
 *@return VOID
*/

private function doImageResize($img)
{
	// Determine te new dimensions
	$d = $this->getImageDimensions($img);
	// Determine what function to use
	$func = $this->getImageFunction($img); 
	
	// Create the image resources for resampling
       $src_img = $func[0]($img);
       $new_img = imagecreatetruecolor($d[0], $d[1]);

	if(imagecopyresampled($new_img, $src_img, 0, 0, 0, 0, $d[0], $d[1], $d[2], $d[3]))
	{
		imagedestroy($src_img);
		if($new_img && $func[1]($new_img,$img))	
		{
			imagedestroy($new_img);
		}
		else
		{
			throw new Exception('Failed to save the new image');
		}
	}
	else
	{
		throw new Exception('Could not save image');
	}
}

}
?>
