<?php 

namespace ScraperComparison;

class Scraper {

	//These are three variables 
	protected $typeUniqueElement = [];
	protected $valueUniqueElement = [];
	protected $verifyUniqueElement = [];
	protected $countUniqueElement = [];
	//This variables are for scanning purpose, if you find this variable, you don't have to scan the node anymore
	protected $typeNotMoreScanning = [];
	protected $valueNotMoreScanning = [];
	protected $sameStructure = true;

    function verifyDomStructure($node1, $node2, $uniques_element = null, $limit = null) {
	    $repeatedStructureFound = false;
	    //We verify if nodes has attributes
	    if ($node1->hasAttributes() && $node2->hasAttributes()) {
	        $nodeAttr1 = $node1->attributes;
	        $nodeAttr2 = $node2->attributes;
	        //If there are attr, we have a foreach to verify if every attr is the same
	        if ($nodeAttr1->length == $nodeAttr2->length) {
	            for ($i = 0; $i < $nodeAttr1->length; $i++) {
	                $nameAttrNode1 = $nodeAttr1[$i]->nodeName;
	                $nameAttrNode2 = $nodeAttr2[$i]->nodeName;
	                $valueAttrNode1 = $nodeAttr1[$i]->nodeValue;
	                $valueAttrNode2 = $nodeAttr2[$i]->nodeValue;

	                if ($nameAttrNode1 != $nameAttrNode2) {
	                    $this->sameStructure = false;
	                    break;
	                }
	                if ($valueAttrNode1 != $valueAttrNode2) {
	                    $this->sameStructure = false;
	                    break;
	                }

	                if ($this->sameStructure) {
	                	//We verify if the node is repeated with typeUniqueElements
	                	//If it is a repetitive structure, we don't verify if the node is the same more than once
	                	$uniqueStructureFound = $this->nodeRepeated($nameAttrNode1, $valueAttrNode1);
	                	if (!empty($uniqueStructureFound) && $uniqueStructureFound > 1) {
	                		$repeatedStructureFound = true;
	                		break;
	                	}
	                	
	                }
	                /*$attributes_values_node1 = $node1->getAttribute($name_node1);
	                $attributes_values_node2 = $node3->getAttribute($name_node2);
	                if ($attributes_values_node1 != $attributes_values_node2) {
	                    $sameStructure = false;
	                    break;
	                }*/
	            }
	        }
	        else if ($nodeAttr1->length != $nodeAttr2->length) {
	        	$this->sameStructure = false;
	        }
	        
	    }
	    else if ($node1->hasAttributes() && !$node2->hasAttributes()) {
	        $this->sameStructure = false;
	    }
	    else if (!$node1->hasAttributes() && $node2->hasAttributes()) {
	        $this->sameStructure = false;
	    }

	    if ($this->sameStructure && !$repeatedStructureFound)  {
	        if ($node1->hasChildNodes() && $node2->hasChildNodes()) {
	            $limit = 0;
	            $childrenNode1 = $node1->childNodes;
	            $childrenNode2 = $node2->childNodes;
	            /*$attr_node1 = $node1->getAttribute($uniques_element[0]);
	            $attr_node2 = $node2->getAttribute($uniques_element[0]);
	            $type_node1 = $node1->nodeName;
	            if ($type_node1 == $uniques_element[1] && $attr_node1 == $uniques_element[2]) {
	                $limit_children = $limit;
	            }
	            else {*/
	            $limit_children = $childrenNode1->length;
	            if ($childrenNode1->length == $childrenNode2->length) {
	            	for ($i = 0; $i < $limit_children; $i++) {
		                if (!$this->sameStructure) {
		                    break;
		                }
		                verifyDomStructure($childrenNode1[$i], $childrenNode2[$i], $uniques_element, $limit);
		            }
	            }
	            else {
	            	$this->sameStructure = false;
	            }
	            //}
	            
	        }
	        else if (!$node1->hasChildNodes() && $node2->hasChildNodes()) {
	        	$this->sameStructure = false;
	        }
	        else if ($node1->hasChildNodes() && !$node2->hasChildNodes()) {
	        	$this->sameStructure = false;
	        }
	    }
	    //return $sameStructure;
	}

	function nodeRepeated ($nameAttrNode1 , $valueAttrNode1) {
		$uniqueStructureFound = null;
		for ($i = 0; $i < count($typeUniqueElement); $i++) {
			if ($typeUniqueElement[$i] == $nameAttrNode1) {
				if ($valueUniqueElement[$i] == $valueAttrNode1) {
					$countUniqueElement[$i]++;
					$uniqueStructureFound = $countUniqueElement[$i];
					break;
				}
			}
		}
		return $uniqueStructureFound;
	}

	/**
	Function to delete unnecessary elements before we compared the two dom elements
	*/
	function cleanAttributes($dom, $elementsToSearch, $attributesToClean) {
		foreach ($elementsToSearch as $element) {
			$nodes = $this->getElementsToClean($dom, $element["typeSearch"], $element["tag"], $element["value"]);
			foreach ($nodes as $node) {              // Iterate over found elements
				foreach ($attributesToClean as $attr) {
					if ($node->hasAttribute($attr)) {
						$node->removeAttribute($attr);    // Remove style attribute
					}
				}
			    
			}
		}
		return $dom;
	}

	
	public function getElementsToClean($dom, $typeSearch, $type, $value = null) {
		$list = array();
		$attributeTrimmed = trim($attribute);
		$tagTrimmed = trim($tag);
	    libxml_use_internal_errors(true);
		if ($typeSearch == "attribute") {
			$xpath = new DOMXPath($dom);            // create a new XPath
			$elements = $xpath->query("//*[contains(concat(' ', normalize-space(@$tagTrimmed), ' '), ' $value ')]");
		}
		else if ($typeSearch == "tag") {
			$elements = $dom->getElementsByTagName($tagTrimmed);
		}

		return $elements;
	}

	public function cleanElements($dom, $elementsToSearch, $elementsToClean) {
		foreach ($elementsToSearch as $element) {
			$nodes = $this->getElementsToClean($dom, $element["typeSearch"], $element["tag"], $element["value"]);
			foreach ($nodes as $node) {              // Iterate over found elements
				$node->parentNode->removeChild($node);
			}
		}
		return $dom;
	}

	public function tryVerifyDom() {
		$dom = new DOMDocument();
		$dom->formatOutput = true;
		$sameStructure = true;
		$dom->loadHTMLFile("test.html");
		$content = $dom->getElementById("portada");
		//$content->saveHTMLFile("test1.html");
		$dom2 = new DOMDocument();
		$dom2->loadHTMLFile("test2.html");
		$content2 = $dom2->getElementById("portada");
		//$web2 = $content2->saveHTML(); 
		//echo $web2;
		verifyDomStructure($content, $content2);
		if ($this->sameStructure) {
			echo "Son iguales";
		}
		else {
			echo "No son iguales";
		}
	}


}