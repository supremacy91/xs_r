<?php

class config{
    //Variable with URIs (Paths)
    public $uri = array();
    //Variable with URLs
    public $url = array();
    //Variable with database connection
    public $database = array();
    //Variable with languages
    public $locale = array();
    //Variable with e-mail settings
    public $email = array();
    
    function __construct(){
        
        $this->uri['base'] = '/data/web/import/';
        $this->uri['admin'] = $this->uri['base'].'admin/';
        $this->uri['classes'] = $this->uri['admin'].'classes/';
        $this->uri['files'] = $this->uri['base'].'userfiles/';
        $this->uri['pdf'] = $this->uri['base'].'dompdf/dompdf_config.inc.php';
        
        $this->url['ssl'] = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on");
        $this->url['protocol'] = ($this->url['ssl'] ? 'https' : 'http');
        $this->url['domain'] = (isset($_SERVER["SERVER_NAME"]) ? $_SERVER["SERVER_NAME"] : 'calexis.nl');
        $this->url['port'] = (isset($_SERVER["SERVER_PORT"]) ? $_SERVER["SERVER_PORT"] : '80');
        $this->url['insertport'] = (isset($_SERVER["SERVER_PORT"]) && ((!$this->url['ssl'] && $this->url['port'] != "80") || ($this->url['ssl'] && $this->url['port'] != "443")));
        $this->url['base'] = $this->url['protocol']."://".$this->url['domain'].($this->url['insertport'] ? ":".$this->url['port'] : '');
        $this->url['admin'] = $this->url['base']."/admin/";
        $this->url['files'] = $this->url['base']."/userfiles/";
        
        $this->locale['front_uri'] = $this->uri['base'].'locale/';
        $this->locale['back_uri'] = $this->uri['admin'].'locale/';
        $this->locale['default'] = 'nl_NL';
        
        $this->database['connection'] = array(  "debugmode" => true,
                                                "hostname" => 'localhost',
                                                "database" => 'calexis_cms',
                                                "username" => 'calexis_cms',
                                                "password" => 'schoenmode');
        $this->email['host'] = 'mail.calexis.nl';
        $this->email['port'] = 25; 
        $this->email['username'] = 'info@calexis.nl';
        $this->email['password'] = 'schoenmode';
        $this->email['langcode'] = 'nl';
        $this->email['smtpauth'] = true;
        $this->email['ishtml'] = true;
        $this->email['mergehtml'] = false;
        
    }
}

?>