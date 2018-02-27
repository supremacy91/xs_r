<?php 

class template { 

    var $output; 

    function getPage($template){ 
        if(file_exists($template)){
            ob_start();
            include($template);
            $this->output = ob_get_contents();
            ob_end_clean();
        }else{
            die('Error: Template file '.$template.' not found'); 
        }
    }
    
    function parsePage($template='default_template.htm', $tags=array(), $lang=array(), $pagevar=array()){ 
        
        $this->getPage($template);
        
        $parsingTags = $tags;
        if(count($parsingTags)>0){ 
        
            foreach($parsingTags as $key=>$data){ 
                $data=(file_exists($data))?$this->parseContent($data, $tags, $lang, $pagevar):$data; 
                $this->output=str_replace('{'.$key.'}',$data,$this->output); 
            } 
        
        }else { 
        
            die('Error: No tags were provided for replacement'); 
        
        } 
    
    } 
    
    function parseContent($file, $tags=array(), $lang=array(), $pagevar=array()){ 
        
        ob_start(); 
        include($file); 
        $content=ob_get_contents(); 
        ob_end_clean();
        
        if(count($tags)>0){ 
        
            foreach($tags as $key=>$data){ 
                $content=str_replace('{'.$key.'}',$data,$content); 
            } 
        
        }
        
        if(count($lang)>0){ 
        
            foreach($lang as $key=>$data){ 
                $content=str_replace('{locale:'.$key.'}',$data,$content); 
            } 
        
        }
        
        return $content; 
    
    } 
    
    function display(){ 
    
        return $this->output; 
    
    } 
    
    function getList(){
        $list = array();
        if(is_dir("templates/frontend/")){
            $directory = opendir("templates/frontend/");
            while (false !== ($file = readdir($directory))){
                if ($file != "." && $file != "..") {
                    array_push($list, $file);
                }
            }
            closedir($directory);
        }else{
            die("Fout: Er zijn geen templates aanwezig!");  
        }
        return $list;
    }
    
    function getFileList($folder){
        $list = array();
        if(is_dir("templates/frontend/".$folder) && is_dir("css/frontend/".$folder)){
            $directory = opendir("templates/frontend/".$folder);
            while (false !== ($file = readdir($directory))){
                if ($file != "." && $file != "..") {
                    array_push($list, $file);
                }
            }
            closedir($directory);
            $directory = opendir("css/frontend/".$folder);
            while (false !== ($file = readdir($directory))){
                if ($file != "." && $file != "..") {
                    array_push($list, $file);
                }
            }
            closedir($directory);
        }else{
            die("Fout: Er zijn geen templates aanwezig!");  
        }
        return $list;
    }
    
    function add($copyfrom, $naam){
        if(!empty($copyfrom)){
            if(!is_dir("templates/frontend/". $copyfrom)) $_SESSION['CreateTemplate']['Errors'] .= "De directory waarvan gekopieerd moet worden bestaat niet.<br/>";;   
        }
        if(empty($naam)) $_SESSION['CreateTemplate']['Errors'] .= "Er is geen naam opgegeven<br/>";
        if(is_dir("templates/frontend/". $naam)) $_SESSION['CreateTemplate']['Errors'] .= "De opgegeven naam bestaat al.<br/>";
        if(!empty($_SESSION['CreateTemplate'])){
            $_SESSION['CreateTemplate']['copyfrom'] = $copyfrom;
            $_SESSION['CreateTemplate']['naam'] = $naam;
            return "Location: admin.php?p=templates/nieuwetemplate.php";
        }else{
            $source = "templates/frontend/".$copyfrom;
            $target = "templates/frontend/".$naam;
            $csssource = "css/frontend/".$copyfrom;
            $csstarget = "css/frontend/".$naam;
            $this->dircopy($source,$target);
            $this->dircopy($csssource,$csstarget);
            return "Location: admin.php?p=templates/index.php";
        }
        
    }
    
    function delete($dir, $check=false){
        if(!empty($dir) && $check == true){
            //Verwijder de directory.
            if(is_dir("css/frontend/". $dir)) $this->SureRemoveDir("css/frontend/". $dir, true);
            if(is_dir("templates/frontend/". $dir)) $this->SureRemoveDir("templates/frontend/". $dir, true);
            $message = 'De template is met succes verwijderd.<br/> Klikt u hieronder om het venster te sluiten.<br/>
            <br/>
            <input type="button" name="SluitKnop" value="Sluit venster" onclick="Link(\'admin.php?p=templates/index.php\');" />';
            return $message;
            
        }elseif(!empty($dir) && $check != true){
            //De gebruiker heeft nog net op de knop verwijderen gedrukt en zal dus de melding krijgen of diegene het wel echt wil verwijderen.
            $message = 'Weet u zeker dat u deze template wilt verwijderen? Alle producten en categorieen die hier gelinkt aan zijn zullen nu verkeerd worden getoont. Verandert u dit eerst voordat u de template verwijderd.<br />
            <br/>
           <input type="button" name="JaKnop" value="Ja" onclick="removeRecord(\'admin.php?req=deleteTemplate&dir='. $dir .'&check=true\');" />&nbsp;<input type="button" name="NeeKnop" value="Nee" onclick="Link(\'admin.php?p=templates/index.php\');" />';
            return  $message;
        }else{
            //Onjuiste aanroep
            return "Onjuiste aanroep <a href='admin.php'>Ga Terug</a>";
        }
    }
    
    function modifyFile($post){
        if(empty($post['folder'])){
            $header = "Location: admin.php?p=templates/index.php";
        }else{
            $map = explode("/",$post['folder']);
            if($map[0] == "default"){
                $header = "Location: admin.php?p=templates/index.php";
            }else{
                if(file_exists("templates/frontend/".$post['folder'])){
                    file_put_contents("templates/frontend/".$post['folder'], $post['content']);
                    $header = "Location: admin.php?p=templates/bewerklijst.php&folder=".$map[0];
                }elseif(file_exists("css/frontend/".$post['folder'])){
                    file_put_contents("css/frontend/".$post['folder'], $post['content']);
                    $header = "Location: admin.php?p=templates/bewerklijst.php&folder=".$map[0];
                }else{
                    $header = "Location: admin.php?p=templates/index.php";
                }
            }
        }
        return $header;
                    
    }
    
    function fullCopy( $source, $target ){
        if ( is_dir( $source ) )
        {
            @mkdir( $target );
            
            $d = dir( $source );
            
            while ( FALSE !== ( $name = $d->read() ) ) 
            {
                if ( $name == '.' || $name == '..' )
                {
                    continue;
                }
                
                $Entry = $source . '/' . $name;            
                if ( is_dir( $Entry ) )
                {
                    $this->fullCopy( $Entry, $target . '/' . $name );
                    continue;
                }
                copy( $Entry, $target . '/' . $name );
            }
            
            $d->close();
        }else
        {
            copy( $source, $target );
        }
    }
    
    function dircopy($src_dir, $dst_dir, $verbose = false, $use_cached_dir_trees = false) 
    {    
        static $cached_src_dir;
        static $src_tree; 
        static $dst_tree;
        $num = 0;

        if (($slash = substr($src_dir, -1)) == "\\" || $slash == "/") $src_dir = substr($src_dir, 0, strlen($src_dir) - 1); 
        if (($slash = substr($dst_dir, -1)) == "\\" || $slash == "/") $dst_dir = substr($dst_dir, 0, strlen($dst_dir) - 1);  

        if (!$use_cached_dir_trees || !isset($src_tree) || $cached_src_dir != $src_dir)
        {
            $src_tree = $this->get_dir_tree($src_dir);
            $cached_src_dir = $src_dir;
            $src_changed = true;  
        }
        if (!$use_cached_dir_trees || !isset($dst_tree) || $src_changed)
            $dst_tree = $this->get_dir_tree($dst_dir);
        if (!is_dir($dst_dir)) mkdir($dst_dir, 0777, true);  

          foreach ($src_tree as $file => $src_mtime) 
        {
            if (!isset($dst_tree[$file]) && $src_mtime === false) // dir
                mkdir("$dst_dir/$file"); 
            elseif (!isset($dst_tree[$file]) && $src_mtime || isset($dst_tree[$file]) && $src_mtime > $dst_tree[$file])  // file
            {
                if (copy("$src_dir/$file", "$dst_dir/$file")) 
                {
                    if($verbose) echo "Copied '$src_dir/$file' to '$dst_dir/$file'<br>\r\n";
                    touch("$dst_dir/$file", $src_mtime); 
                    $num++; 
                } else 
                    echo "<font color='red'>File '$src_dir/$file' could not be copied!</font><br>\r\n";
            }        
        }

        return $num; 
    }

    /* Creates a directory / file tree of a given root directory
     *
     * @param $dir str Directory or file without ending slash
     * @param $root bool Must be set to true on initial call to create new tree. 
     * @return Directory & file in an associative array with file modified time as value. 
     */
    function get_dir_tree($dir, $root = true) 
    {
        static $tree;
        static $base_dir_length; 

        if ($root)
        { 
            $tree = array();  
            $base_dir_length = strlen($dir) + 1;  
        }

        if (is_file($dir)) 
        {
            //if (substr($dir, -8) != "/CVS/Tag" && substr($dir, -9) != "/CVS/Root"  && substr($dir, -12) != "/CVS/Entries")
            $tree[substr($dir, $base_dir_length)] = filemtime($dir); 
        } elseif (is_dir($dir) && $di = dir($dir)) // add after is_dir condition to ignore CVS folders: && substr($dir, -4) != "/CVS"
        {
            if (!$root) $tree[substr($dir, $base_dir_length)] = false;  
            while (($file = $di->read()) !== false) 
                if ($file != "." && $file != "..")
                    $this->get_dir_tree("$dir/$file", false);  
            $di->close(); 
        }

        if ($root)
            return $tree;     
    }
    
    function SureRemoveDir($dir, $DeleteMe) {
        if(!$dh = @opendir($dir)) return;
        while (false !== ($obj = readdir($dh))) {
            if($obj=='.' || $obj=='..') continue;
            if (!@unlink($dir.'/'.$obj)) $this->SureRemoveDir($dir.'/'.$obj, true);
        }
    
        closedir($dh);
        if ($DeleteMe){
            @rmdir($dir);
        }
    }
}

?>