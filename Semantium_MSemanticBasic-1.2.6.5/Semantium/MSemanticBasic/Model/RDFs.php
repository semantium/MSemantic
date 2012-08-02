<?php
/**
 * MSemanticBasic Magento Extension
 * @package Semantium_MSemanticBasic
 * @copyright (c) 2010 Semantium, Uwe Stoll <stoll@semantium.de>
 * @author Michael Lambertz <michael@digitallifedesign.net>
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 3 of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with this program; if not, see <http://www.gnu.org/licenses/>
**/
class Semantium_MSemanticBasic_Model_RDFs extends Mage_Core_Model_Abstract
{
	// Helper
	private $rdff = NULL;	// RDF Format Helper
	
	private $baseURL;
	
	
	public function __call($method, $args)
	{
		$output = "RDFs->". $method." (";
		foreach ($args as $arg)
		{
			$output .= $arg.",";
		}
		$output .= ")";
		webdirx_div::message($output);
	}
	
	protected function _construct()
	{
		$this->_init('msemanticbasic/rdfs');
	}
	
	public function setRdff($rdffClassName)
	{
		$this->rdff = Mage::app()->getHelper($rdffClassName);
	}
	
	/** RDFs ***********************************/
	public function isDefinedBy($url)
	{
		
		$rdfsIsDefinedBy = $this->rdff->rel("rdfs:isDefinedBy", $url);
		return $rdfsIsDefinedBy;
	}
	public function seeAlso($url)
	{
		$rdfsSeeAlso = $this->rdff->rel("rdfs:seeAlso", $url);
		return $rdfsSeeAlso;
	}
	
	public function label($value, $lang="en")
	{
		$value = htmlspecialchars($value);
		$replacements = array(
			"{lang}"	=> $lang
		);	
		#rdfs:label for gr:hasPOS
		$labelPropArray = array(
			"rdfs:label" => $value
		);
		$rdfsLabel = $this->rdff->properties($labelPropArray, $replacements);
		return $rdfsLabel;
	}
	
	public function comment($value, $lang="en")
	{
	    
		$rdfsComment = $this->rdff->property("rdfs:comment", htmlspecialchars($value), NULL, $lang);
		return $rdfsComment;
	}
	
	public function pName($value, $lang="en")
	{
		$rdfsComment = $this->rdff->property("gr:name", htmlspecialchars($value), NULL, $lang);
		return $rdfsComment;
	}
	
	public function pDesc($value, $lang="en")
	{
	    
		$rdfsComment = $this->rdff->property("gr:description", htmlspecialchars($value), NULL, $lang);
		return $rdfsComment;
	}
	
	
}