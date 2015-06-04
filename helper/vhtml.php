<?php
if ( ! defined('VBOT')) exit('No direct script access allowed');

//process xpath
class Html{
	private $xpath 	= null;
	private $doc = null;
	public function __construct(){
	}
	
	public function init($htmlContent){	
		//fix unicode - spend many time to find it to fix :(
		$contentType = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
		$htmlContent = str_replace('<head>', '<head>' . $contentType, $htmlContent);
		
		//hide warning when tag not recognize
		libxml_use_internal_errors(false);
		
		$doc = new DOMDocument();
		$doc->loadHTML($htmlContent);
		$this->xpath = new DOMXpath($doc);
		$this->doc = $doc;
		//set normal 
		libxml_clear_errors();
		libxml_use_internal_errors(true);
	}
	
	//get content from xpath
	public function get_xpath_content($xpath_str){
		$xpath 	  = $this->xpath;
		$elements = $xpath->query($xpath_str);

		$domDocument = new DOMDocument();
		foreach ($elements as $element) {
			$domDocument->appendChild($domDocument->importNode($element, true));
		}
		
		return $domDocument->saveHTML();
	}
	
	public function get_xpath_node_length($xpath_str){
		$xpath 	  = $this->xpath;
		$elements = $xpath->query($xpath_str);
		
		return $elements->length;
	}
	
	//get array attribute
	public function get_xpath_attr($xpath_str){
		$result	  = array(); //array to return
		
		$xpath 	  = $this->xpath;
		$elements = $xpath->query($xpath_str);

		//get all attribute
		foreach ($elements as $element) {
			if($element->hasAttributes()){
                $attributes = $element->attributes; 
			 	if(!is_null($attributes)){
                    foreach ($attributes as $index=>$attr){
                        $result[$attr->name] = $attr->value;
                    }
                } 
			}
		}
		
		return $result;		
	}
	
	public function remove_xpath_element($xpath_str){
		$xpath 	  = $this->xpath;
		$elements = $xpath->query($xpath_str);
		
		foreach ($elements as $element) {
			$element->parentNode->removeChild($element);
		}
		
		return $this->doc->saveHTML();
	}
}