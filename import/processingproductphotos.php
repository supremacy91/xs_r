#!/usr/local/bin/php -q
<?php
set_time_limit(3600);
//error_reporting(0);
date_default_timezone_set('Europe/Amsterdam');

require_once(dirname(__FILE__)."/classes/config.php");
$aConfig = new config(); 
 
$classes = $aConfig->uri['classes'];

function __autoload($className){
    global $classes;
    if(file_exists(dirname(__FILE__)."/classes/".$className.".php")){
        require_once(dirname(__FILE__)."/classes/".$className.".php");
    }
} 

function startsWith($haystack, $needle) {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
}

$referencearray = array();
$data = array();
error_reporting(E_ALL);
$datestring = date("ymd");
$files = scandir(dirname(__FILE__)."/photodropbox/");
foreach($files as $file){
	if(preg_match('/^.*\.(xml)$/i',$file)){
		$filename = dirname(__FILE__)."/photodropbox/".$file;
	} 
}
//$filename = dirname(__FILE__)."/photodropbox/".$datestring.'-rename.xml';
//if(!is_file($filename)){
//	$datestring = date('ymd', strtotime(' -1 day'));
//	$filename = dirname(__FILE__)."/photodropbox/".$datestring.'-rename.xml';
//}
if(isset($filename)){ 
if(is_file($filename)){
	try{
        	$datetimestring = date('ymdhis');
			$completedfilename = dirname(__FILE__)."/completed/".$datetimestring.'-rename-completed.xml';
			$dropboxdirectory = dirname(__FILE__)."/photodropbox/";
			$basedirectory = dirname(__FILE__).'/../pub/media/import/product-photos/';
			$dom = new DOMDocument();
			$dom->load($filename);
			$rows = $dom->getElementsByTagName( 'Row' );
			$first_row = true;
			foreach ($rows as $row){
			   if ( !$first_row ){
				 $rowdata = array();
				 $index = 1;
				 $cells = $row->getElementsByTagName( 'Cell' );
				 foreach( $cells as $cell ){
				   $ind = $cell->getAttribute( 'Index' );
				   if ( $ind != null ) $index = $ind;
				
				   array_push($rowdata,$cell->nodeValue);/*
				   if ( $index == 2 ) $middle = $cell->nodeValue;
				   if ( $index == 3 ) $last = $cell->nodeValue;
				   if ( $index == 4 ) $email = $cell->nodeValue;
					*/
				   $index += 1;
				 }
				 array_push($data,$rowdata);
			   }
			   $first_row = false;
			}
			$count = count($data);
			$productsarray = array();
			for($i=0;$i<$count;$i++){
				if(empty($data[$i][0]) || $data[$i][0] == "Referentie"){
					unset($data[$i]);
					continue;
				}else{
					$referencearray[] = $data[$i][0];
				}
				$itemname = str_replace(" ","-",$data[$i][2]);
				$iteminfo = explode(".",$itemname);
				$itemnewname = $iteminfo[0].date("dmy").".".$iteminfo[1];
				$itemref = $data[$i][0];
				
				$itemcolor = $data[$i][3];
				//Transform the names
				$itemref = str_replace(array(" ","/"),array("_","-"),$itemref);
				$itemcolor = str_replace(array(" ","/"),array("-","_"),$itemcolor);
				$imagenamebase = "IMG_".$itemref;
				$imagenameextended = $imagenamebase.".".$itemcolor;
				//error_log($itemname." ".$itemref." ".$itemcolor." ".$imagenamebase." ".$imagenameextended,0);
				//First check if the original image name exists
				if(is_dir($basedirectory.$imagenamebase."/".$imagenameextended)){
					//Loop the directory and remove all the content
					$files = glob($basedirectory.$imagenamebase."/".$imagenameextended."/*"); // get all file names
					foreach($files as $file){ // iterate files
  						if(is_file($file))
    						unlink($file); // delete file
					}
				}else{
					//Else create a folder structure for this product.
					mkdir($basedirectory.$imagenamebase."/".$imagenameextended, 0755, true);
				} 
			}
			$data = array_values($data);
			$count = count($data);
			//Loop through it again, but this time rename the photos
			for($i=0;$i<$count;$i++){
				$itemname = str_replace(" ","-",$data[$i][2]);
				$iteminfo = explode(".",$itemname);
				$itemnewname = $iteminfo[0].date("dmy").".".$iteminfo[1];
				$itemref = $data[$i][0];
				$itemcolor = $data[$i][3];
				//Transform the names
				$itemref = str_replace(array(" ","/"),array("_","-"),$itemref);
				$itemcolor = str_replace(array(" ","/"),array("-","_"),$itemcolor);
				$imagenamebase = "IMG_".$itemref;
				$imagenameextended = $imagenamebase.".".$itemcolor;
				//error_log($itemname." ".$itemref." ".$itemcolor." ".$imagenamebase." ".$imagenameextended,0);
				//First check if the original image name exists
				if(is_dir($basedirectory.$imagenamebase."/".$imagenameextended)){
					//add the photo and give the photo a unique name.
					rename($dropboxdirectory.$itemname, $basedirectory.$imagenamebase."/".$imagenameextended."/".$itemnewname);
					//error_log("YES",0);
				}else{
					//Else create a folder structure for this product.
					mkdir($basedirectory.$imagenamebase."/".$imagenameextended, 0755, true);
					rename($dropboxdirectory.$itemname, 
						$basedirectory.$imagenamebase."/".$imagenameextended."/".$itemnewname);	
				} 
			}
			echo "recovered ".$i." photos";
			rename($filename, $completedfilename);
			
			$email = new email;
	 		$to_email = "tjerk@firstaidmarketing.nl";
	 		$to_name = "Tjerk Rintjema";
	 		$subject = "Photo Rename Successful";
	 		$message = "The product photos have been renamed";
	 		$email->send($to_name,$to_email,$subject,$message); 
	 		$to_email = "info@calexis.nl";
	 		$to_name = "Calexis Schoenmode";
	 		$email->send($to_name,$to_email,$subject,$message); 
	 		$to_email = "marianneathijs@gmail.com";
	 		$to_name = "Marianne Thijs";
	 		$email->send($to_name,$to_email,$subject,$message); 
	} catch (Exception $e){
		//Log errors
	 	//Send error message
	 	$email = new email;
	 	$to_email = "tjerk@firstaidmarketing.nl";
	 	$to_name = "Tjerk Rintjema";
	 	$subject = "Photo Rename Errors";
	 	$message = "The following error(s) occurred:<br/>".$e->getMessage();
	 	$email->send($to_name,$to_email,$subject,$message);  
	 	$to_email = "info@calexis.nl";
	 	$to_name = "Calexis Schoenmode";
	 	$email->send($to_name,$to_email,$subject,$message);  
	 	$to_email = "marianneathijs@gmail.com";
	 	$to_name = "Marianne Thijs";
	 	$email->send($to_name,$to_email,$subject,$message);   
	 	error_log($e->getMessage(),0);
	}
}
}
?>