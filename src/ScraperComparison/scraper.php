<?php 

namespace ScraperComparison;

class Scraper {

	//These are three variables 
	protected $type_unique_element = [];
	protected $value_unique_element = [];
	protected $verify_unique_element = [];
	protected $count_unique_element = [];
	//This variables are for scanning purpose, if you find this variable, you don't have to scan the node anymore
	protected $type_not_more_scanning = [];
	protected $value_not_more_scanning = [];
	protected $same_structure = true;

    function verify_dom_structure($node1, $node2, $uniques_element = null, $limit = null) {
	    $repeated_structure_found = false;
	    //We verify if nodes has attributes
	    if ($node1->hasAttributes() && $node2->hasAttributes()) {
	        $node1_attr = $node1->attributes;
	        $node2_attr = $node2->attributes;
	        //If there are attr, we have a foreach to verify if every attr is the same
	        if ($node1_attr->length == $node2_attr->length) {
	            for ($i = 0; $i < $node1_attr->length; $i++) {
	                $name_attr_node1 = $node1_attr[$i]->nodeName;
	                $name_attr_node2 = $node2_attr[$i]->nodeName;
	                $value_attr_node1 = $node1_attr[$i]->nodeValue;
	                $value_attr_node2 = $node2_attr[$i]->nodeValue;

	                if ($name_attr_node1 != $name_attr_node2) {
	                    $this->same_structure = false;
	                    break;
	                }
	                if ($value_attr_node1 != $value_attr_node2) {
	                    $this->same_structure = false;
	                    break;
	                }

	                if ($this->same_structure) {
	                	//We verify if the node is repeated with type_unique_elements
	                	//If it is a repetitive structure, we don't verify if the node is the same more than once
	                	$unique_structure_found = $this->node_repeated($name_attr_node1, $value_attr_node1);
	                	if (!empty($unique_structure_found) && $unique_structure_found > 1) {
	                		$repeated_structure_found = true;
	                		break;
	                	}
	                	
	                }
	                /*$attributes_values_node1 = $node1->getAttribute($name_node1);
	                $attributes_values_node2 = $node3->getAttribute($name_node2);
	                if ($attributes_values_node1 != $attributes_values_node2) {
	                    $same_structure = false;
	                    break;
	                }*/
	            }
	        }
	        else if ($node1_attr->length != $node2_attr->length) {
	        	$this->same_structure = false;
	        }
	        
	    }
	    else if ($node1->hasAttributes() && !$node2->hasAttributes()) {
	        $this->same_structure = false;
	    }
	    else if (!$node1->hasAttributes() && $node2->hasAttributes()) {
	        $this->same_structure = false;
	    }

	    if ($this->same_structure && !$repeated_structure_found)  {
	        if ($node1->hasChildNodes() && $node2->hasChildNodes()) {
	            $limit = 0;
	            $children_node1 = $node1->childNodes;
	            $children_node2 = $node2->childNodes;
	            /*$attr_node1 = $node1->getAttribute($uniques_element[0]);
	            $attr_node2 = $node2->getAttribute($uniques_element[0]);
	            $type_node1 = $node1->nodeName;
	            if ($type_node1 == $uniques_element[1] && $attr_node1 == $uniques_element[2]) {
	                $limit_children = $limit;
	            }
	            else {*/
	            $limit_children = $children_node1->length;
	            //}
	            for ($i = 0; $i < $limit_children; $i++) {
	                if (!$this->same_structure) {
	                    break;
	                }
	                verify_dom_structure($children_node1[$i], $children_node2[$i], $uniques_element, $limit);
	            }
	        }
	        else if (!$node1->hasChildNodes() && $node2->hasChildNodes()) {
	        	$this->same_structure = false;
	        }
	        else if ($node1->hasChildNodes() && !$node2->hasChildNodes()) {
	        	$this->same_structure = false;
	        }
	    }
	    //return $same_structure;
	}

	function node_repeated ($name_attr_node1 , $value_attr_node1) {
		$unique_structure_found = null;
		for ($i = 0; $i < count($type_unique_element); $i++) {
			if ($type_unique_element[$i] == $name_attr_node1) {
				if ($value_unique_element[$i] == $value_attr_node1) {
					$count_unique_element[$i]++;
					$unique_structure_found = $count_unique_element[$i];
					break;
				}
			}
		}
		return $unique_structure_found;
	}

	/**
	Function to delete unnecessary elements before we compared the two dom elements
	*/
	function clean_dom($dom, $elements_to_search, $elements_to_clean) {
		foreach ($elements_to_search as $element) {
			$nodes = $this->getElementsToClean($dom, $element["typeSearch"], $element["tag"], $element["value"]);
			foreach ($nodes as $node) {              // Iterate over found elements
				foreach ($attributes_to_clean as $attr) {
					if ($node->hasAttribute($attr)) {
						$node->removeAttribute($attr);    // Remove style attribute
					}
				}
			    
			}
		}
		return $dom;
	}

	
	public function get_elements_to_clean($dom, $typeSearch, $tag, $value = null) {
		$list = array();
		$attributeTrimmed = trim($attribute);
		$tagTrimmed = trim($tag);
	    libxml_use_internal_errors(true);
		if ($typeSearch == "attribute") {
			$xpath = new DOMXPath($dom);            // create a new XPath
			$elements = $xpath->query("//*[contains(concat(' ', normalize-space(@$tagTrimmed), ' '), ' $value ')]");
		}
		else if ($typeSearch == "element") {
			$elements = $dom->getElementsByTagName($tagTrimmed);
		}
		return $elements;
	}

	public function cleanElement() {
		
	}

	public function tryVerifyDom() {
		$dom = new DOMDocument();
		$dom->formatOutput = true;
		$same_structure = true;
		$dom->loadHTMLFile("test.html");
		$content = $dom->getElementById("portada");
		//$content->saveHTMLFile("test1.html");
		$dom2 = new DOMDocument();
		$dom2->loadHTMLFile("test2.html");
		$content2 = $dom2->getElementById("portada");
		//$web2 = $content2->saveHTML(); 
		//echo $web2;
		verify_dom_structure($content, $content2);
		if ($this->same_structure) {
			echo "Son iguales";
		}
		else {
			echo "No son iguales";
		}
	}


}