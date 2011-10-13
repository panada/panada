<?php
namespace Models;
use Libraries;

class SampleData {
    
    public function __construct(){
        
        $this->myLibs = new Libraries\MyLibs;
    }
    
    public function getData(){
        
        return $this->myLibs->test();
    }
}