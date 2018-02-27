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

try{
	if(isset($_GET['position']) && is_numeric($_GET['position'])){
		$position = $_GET['position'];
	}else{
		$position = 0;
	}
	$linelimit = 100000; 
	$filename_afdelingen = dirname(__FILE__).'/icg/Afdelingen.csv';
	if(!is_file($filename_afdelingen)){
		//Trigger exception
		 throw new Exception('No Afdelingen synch file. Contact ICG'); 
	 }
	$filename_subafdelingen = dirname(__FILE__).'/icg/sub-afdeling.csv';
	if(!is_file($filename_subafdelingen)){
		//Trigger exception
		 throw new Exception('No Sub Afdelingen synch file. Contact ICG'); 
	 }
	$filename_families = dirname(__FILE__).'/icg/Families.csv';
	if(!is_file($filename_families)){
		//Trigger exception
		 throw new Exception('No Families synch file. Contact ICG'); 
	 }
	$filename_subfamilies = dirname(__FILE__).'/icg/sub-families.csv';
	if(!is_file($filename_subfamilies)){
		//Trigger exception
		 throw new Exception('No Sub Families synch file. Contact ICG'); 
	 }
	 
	$category_array = array();
	$afdelingen_array = array();
	$subafdelingen_array = array();
	$families_array = array();
	$subfamilies_array = array();
	/* 
	
	NOW THE ITERATIONS STARTS
	first start by going over the "Afdelingen"
	followed by "sub-afdelingen"
	and after the "Families"
	and last the "sub-families"
	
	*/
	
	//$startTime = microtime(true);
	if (($handle = fopen($filename_afdelingen, "r")) !== FALSE) {
		$previousrow = "";
		$currentrow = "";
		$row = 1;
		fseek($handle, $position);
		
		$linelength=0;
		$delimiter=',';
		$enclosure='"';
		$escape='\\';
		$fields=2;
		while (($data = fgetcsv($handle, $linelength, $delimiter, $enclosure, $escape)) !== FALSE) {
			$num = count($data);
			//Only continue when it has the required number of fields (and is according to format)
			if($num == $fields){
				$currentrow = $data[1];
				if(empty($currentrow)){
					continue;
				}
				$id = $data[0];
				$name = $data[1];
				$afdelingen_array[$id] = $name;
				$icg_id = $id.",0,0,0";
				array_push($category_array,array($icg_id,$name));
				$previousrow = $currentrow;    
				
			}else{
				//Log error in file
				error_log("Row ".$row." has not been pushed, not according to format ".count($data),0);
				error_log(print_r($data),0);
			}
			$row++;
		}
		fclose($handle);
		//error_log("".$row." rows are processed and took ". number_format(( microtime(true) - $startTime), 4) ." Seconds",0); 
	}else{
		//Failed to open file
		error_log("Failed to open CSV file.",0);
	}
	//Now the sub-afdelingen
	
	if (($handle = fopen($filename_subafdelingen, "r")) !== FALSE) {
		$previousrow = "";
		$currentrow = "";
		$row = 1;
		fseek($handle, $position);
		
		$linelength=0;
		$delimiter=',';
		$enclosure='"';
		$escape='\\';
		$fields=3;
		while (($data = fgetcsv($handle, $linelength, $delimiter, $enclosure, $escape)) !== FALSE) {
			$num = count($data);
			//Only continue when it has the required number of fields (and is according to format)
			if($num == $fields){
				$currentrow = $data[2];
				if(empty($currentrow)){
					continue;
				}
				$afdeling = $data[0];
				$id = $data[1];
				$name = $afdelingen_array[$afdeling] ." > ". $data[2];
				$subafdelingenid = $afdeling.",".$id;
				$subafdelingen_array[$subafdelingenid] = $data[2];
				$icg_id = $afdeling.",".$id.",0,0";
				array_push($category_array,array($icg_id,$name));
				$previousrow = $currentrow;    
				
			}else{
				//Log error in file
				error_log("Row ".$row." has not been pushed, not according to format ".count($data),0);
				error_log(print_r($data),0);
			}
			$row++;
		}
		fclose($handle);
		//error_log("".$row." rows are processed and took ". number_format(( microtime(true) - $startTime), 4) ." Seconds",0); 
	}else{
		//Failed to open file
		error_log("Failed to open CSV file.",0);
	}
	
	//Now the families
	
	if (($handle = fopen($filename_families, "r")) !== FALSE) {
		$previousrow = "";
		$currentrow = "";
		$row = 1;
		fseek($handle, $position);
		
		$linelength=0;
		$delimiter=',';
		$enclosure='"';
		$escape='\\';
		$fields=4;
		while (($data = fgetcsv($handle, $linelength, $delimiter, $enclosure, $escape)) !== FALSE) {
			$num = count($data);
			//Only continue when it has the required number of fields (and is according to format)
			if($num == $fields){
				$currentrow = $data[3];
				if(empty($currentrow)){
					continue;
				}
				$afdeling = $data[0];
				$subafdeling = $data[1];
				$id = $data[2];
				$name = $afdelingen_array[$afdeling] ." > ". $subafdelingen_array[$afdeling.",".$subafdeling] ." > ". $data[3];
				$familiesid = $afdeling.",".$subafdeling.",".$id;
				$families_array[$familiesid] = $data[3];
				$icg_id = $afdeling.",".$subafdeling.",".$id.",0";
				array_push($category_array,array($icg_id,$name));
				$previousrow = $currentrow;    
				
			}else{
				//Log error in file
				error_log("Row ".$row." has not been pushed, not according to format ".count($data),0);
				error_log(print_r($data),0);
			}
			$row++;
		}
		fclose($handle);
		//error_log("".$row." rows are processed and took ". number_format(( microtime(true) - $startTime), 4) ." Seconds",0); 
	}else{
		//Failed to open file
		error_log("Failed to open CSV file.",0);
	}	
	
	//Lastly the sub families
	
	if (($handle = fopen($filename_subfamilies, "r")) !== FALSE) {
		$previousrow = "";
		$currentrow = "";
		$row = 1;
		fseek($handle, $position);
		
		$linelength=0;
		$delimiter=',';
		$enclosure='"';
		$escape='\\';
		$fields=5;
		while (($data = fgetcsv($handle, $linelength, $delimiter, $enclosure, $escape)) !== FALSE) {
			$num = count($data);
			//Only continue when it has the required number of fields (and is according to format)
			if($num == $fields){
				$currentrow = $data[4];
				if(empty($currentrow)){
					continue;
				}
				$afdeling = $data[0];
				$subafdeling = $data[1];
				$familie = $data[2];
				$id = $data[3];
				$name = $afdelingen_array[$afdeling] ." > ". $subafdelingen_array[$afdeling.",".$subafdeling] ." > ". $families_array[$afdeling.",".$subafdeling.",".$familie] ." > ". $data[4];
				$subfamiliesid = $afdeling.",".$subafdeling.",".$familie.",".$id;
				$subfamilies_array[$subfamiliesid] = $data[4];
				$icg_id = $subfamiliesid;
				array_push($category_array,array($icg_id,$name));
				$previousrow = $currentrow;    
				
			}else{
				//Log error in file
				error_log("Row ".$row." has not been pushed, not according to format ".count($data),0);
				error_log(print_r($data),0);
			}
			$row++;
		}
		fclose($handle);
		//error_log("".$row." rows are processed and took ". number_format(( microtime(true) - $startTime), 4) ." Seconds",0); 
	}else{
		//Failed to open file
		error_log("Failed to open CSV file.",0);
	}
		
	//Now output the category array as a new CSV file.
	$fp = fopen(dirname(__FILE__).'/icg/Categories.csv', 'w');
	foreach ($category_array as $fields) {
		fputcsv($fp, $fields);
	}
	//foreach ($category_array as $key => $value) { 
   //    fputcsv($fp, array($key[0], $value[$key[0]]));
    //}
	fclose($fp);

	 $email = new email;
	 $to_email = "tjerk@firstaidmarketing.nl";
	 $to_name = "Tjerk Rintjema";
	 $subject = "Synchronization succesful";
	 $message = "The category file is successfully compiled";
	 //$email->send($to_name,$to_email,$subject,$message); 
	 //$to_email = "info@calexis.nl";
	 //$to_name = "Calexis Schoenmode";
	 //$email->send($to_name,$to_email,$subject,$message); 
}catch (Exception $e){
	//Log errors
	 //Send error message
	 $email = new email;
	 $to_email = "tjerk@firstaidmarketing.nl";
	 $to_name = "Tjerk Rintjema";
	 $subject = "Errors occurred during the compiling of the category file";
	 $message = "The following error(s) occurred:<br/>".$e->getMessage();
	 $email->send($to_name,$to_email,$subject,$message);     
	 error_log($e->getMessage(),0);
}

?>