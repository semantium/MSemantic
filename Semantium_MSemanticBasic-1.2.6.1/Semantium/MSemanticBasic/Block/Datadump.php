<?php
 /**
 * MSemanticBasic Magento Extension
 * @package Semantium_PackageName
 * @copyright (c) 2010 Semantium, Uwe Stoll <stoll@semantium.de>
 * @author Michael Lambertz <michael@digitallifedesign.net>
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 3 of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with this program; if not, see <http://www.gnu.org/licenses/>
**/

//class Semantium_MSemanticBasic_Block_Datadump extends Mage_Core_Block_Template
class Semantium_MSemanticBasic_Block_Datadump extends Mage_Catalog_Block_Product_Abstract
{
	// Helper
	public $rdffClassName = "msemanticbasic/Rdfformat";
	private $rdff = NULL;		// rdf format helper
	// Models
    private $Business = NULL;
	private $Product = NULL;
	private $ProductCollection = NULL;
	private $GR = NULL;			// GoodRelations Ontology (gr)
	// 
	private $settings;			// Semantium MSemanticBasic Settings
	private $generalSettings;	// 
	
    
	public function __construct()
    {
    	@include_once("../../EssentiaLib/includeAll.php");
    	parent::__construct();
    }
    
	protected function initData()
	{
		$this->generalSettings = Mage::getStoreConfig("general");
		$this->settings = Mage::getStoreConfig("semantium");
		// Helper
		$this->rdff = $this->helper($this->rdffClassName);
		// Shop Models
		$this->Business = Mage::getModel('msemanticbasic/Business');
		$this->initProductCollection();
		// Semantic Web Models
		$this->GR = Mage::getModel('msemanticbasic/GoodRelations');
		$this->GR->setRdff($this->rdffClassName);
	}
    
	protected function _toHtml()
	{
		$this->initData();
		if ($this->settings['basicsettings']['active'])
		{
			$html = parent::_toHtml();
			
			// $html .= "<h1>RDFa Data Dump of all Products</h1>";
			
			$this->rdff->useRdfNamespaces("rdf,rdfs,xsd,dc,owl,vcard,gr,product,foaf,media");
			$html .= $this->rdff->startRdfa($this->Business->getLegalName());
			
			$this->GR->setBusiness($this->Business);
			$html .= $this->GR->businessEntityWithProducts($this->ProductCollection);
			
			$html .= $this->rdff->endRdfa();
			
			//@webdirx_div::debug(htmlentities($html));
			return $html;
		}
	}
	protected function initProductCollection()
	{
		//*****************************************************
		// 2010-03-11
		//*****************************************************
		$storeId    = Mage::app()->getStore()->getId();
		
		// $visibility = Mage_Catalog_Model_Product_Status::STATUS_DISABLED 
       
		
		$visibility = array(  
            Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,  
           Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG  
        
        );
        
		$this->ProductCollection = Mage::getModel('catalog/product')
				->setStoreId($storeId)
				->getCollection()
				->addAttributeToFilter("visibility", $visibility)
				->addAttributeToFilter('status', 1)
				->addAttributeToSelect(array('name', 'small_image', 'visibility', 'is_saleable', 'status', 'qty', 'short_description','description', 'price'))
				//->setOrder("name", "asc")
				;
		//*****************************************************
		return $this->ProductCollection;
	}
	
	protected function pingSindice()
	{
		
	}
	
	protected function pingTheSemanticWeb()
	{
		
	}
	
}
