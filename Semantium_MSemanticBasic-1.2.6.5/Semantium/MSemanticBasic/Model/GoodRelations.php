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
class Semantium_MSemanticBasic_Model_GoodRelations extends Mage_Core_Model_Abstract
{
	private $PAYMENT_METHODS = array (
		"byBankTransferInAdvance"	=> "http://purl.org/goodrelations/v1#ByBankTransferInAdvance",
		"byInvoice"				=> "http://purl.org/goodrelations/v1#ByInvoice",
		"cash"					=> "http://purl.org/goodrelations/v1#Cash",
		"checkinadvance"		=> "http://purl.org/goodrelations/v1#CheckInAdvance",
		"cod"					=> "http://purl.org/goodrelations/v1#COD",
		"directdebit"			=> "http://purl.org/goodrelations/v1#DirectDebit",
		"googleCheckout"		=> "http://purl.org/goodrelations/v1#GoogleCheckout",
		"paypal"				=> "http://purl.org/goodrelations/v1#PayPal",
		"americanexpress"		=> "http://purl.org/goodrelations/v1#AmericanExpress",
		"dinersclub"			=> "http://purl.org/goodrelations/v1#DinersClub",
		"discover"				=> "http://purl.org/goodrelations/v1#Discover",
		"jcb"					=> "http://purl.org/goodrelations/v1#JCB",
		"mastercard"			=> "http://purl.org/goodrelations/v1#MasterCard",
		"visa"					=> "http://purl.org/goodrelations/v1#VISA"
	);

	
	private $DELIVERY_METHODS = array(
		"dhl"					=> "http://purl.org/goodrelations/v1#DHL",
		"ups"					=> "http://purl.org/goodrelations/v1#UPS",
		"mail"					=> "http://purl.org/goodrelations/v1#DeliveryModeMail",
		"fedex"					=> "http://purl.org/goodrelations/v1#FederalExpress",
		"directdownload"		=> "http://purl.org/goodrelations/v1#DeliveryModeDirectDownload",
		"pickup"				=> "http://purl.org/goodrelations/v1#DeliveryModePickUp",
		"vendorfleet"			=> "http://purl.org/goodrelations/v1#DeliveryModeOwnFleet",
		"freight"				=> "http://purl.org/goodrelations/v1#DeliveryModeFreight"
	);
	private $CUSTOMER_TYPES = array (
		"enduser"				=> "http://purl.org/goodrelations/v1#Enduser",
		"reseller"				=> "http://purl.org/goodrelations/v1#Reseller",
		"business"				=> "http://purl.org/goodrelations/v1#Business",
		"publicinstitution"		=> "http://purl.org/goodrelations/v1#PublicInstitution"
	);
	
	private $BUSINESS_FUNCTIONS = array(
		"sell"					=> "http://purl.org/goodrelations/v1#Sell"
	);
	
	protected $_data = array();				// for vCards etc.
	protected $_URI = array();				// stores arrays of URIs
	protected $_attributeCodes = array();	// attribute codes of the product (strongId, description...)
	
	// Cache
	protected $cacheAddressVCard = NULL;	// VCard object cache
	protected $cachePOSAddressVCard = NULL;	// VCard object cache
	
	// Models
	private $RDFS = NULL;
	private $VCARD = NULL;
	private $Business = NULL;	// Business Model
	// Helper
	public $rdffClassName = "";	
	private $rdff = NULL;		// RDF Format Helper
	private $div = NULL;		// DIV functions
	private $sysinfo = NULL;	// Magento System Information Helper
	
	public function __call($method, $args)
	{
		$output = "GR->". $method." (";
		foreach ($args as $arg)
		{
			$output .= $arg.",";
		}
		$output .= ")";
		webdirx_div::message($output);
	}
	
	protected function _construct()
	{
		$this->_init('msemanticbasic/goodrelations');
		
		// helper
		$this->div = Mage::app()->getHelper('msemanticbasic/Div');
		$this->sysinfo = Mage::app()->getHelper('msemanticbasic/Sysinfo');
		// other ontologies
		$this->RDFS = Mage::getModel('msemanticbasic/RDFs');
		$this->VCARD = Mage::getModel('msemanticbasic/VCard');
	}
	
	/**
	 * rdf format helper
	 * @param $rdfformatHelpe string
	 * @return unknown_type
	 */
	public function setRdff($rdffClassName)
	{
		$this->rdffClassName = $rdffClassName;
		$this->rdff = Mage::app()->getHelper($this->rdffClassName);
		$this->RDFS->setRdff($this->rdffClassName);
		$this->VCARD->setRdff($this->rdffClassName);
	}
	
	/**
	 * connect to business
	 * @param $business object
	 * @return self
	 */
	public function setBusiness($business)
	{
		$this->Business = $business;
		$this->initBusinessURIs();
		$this->initBusinessData();	// creates all necessary data (e.g. vcard code)
		return $this;
	}
	public function getBusiness()
	{
		return $this->Business;
	}
	/**
	 * connect to product
	 * @param $product object
	 * @return selft
	 */
	public function setProduct($product)
	{
		$this->Product = $product;
		$this->initProductURIs();
		$this->initProductAttributeCodes();
		$this->initProductData();	//creates all necessary data
		return $this;
	}
	public function getProduct()
	{
		return $this->Business;
	}
	
	//setter for address vcard code
	public function setAddressVCard($val)
	{
		$this->_data['addressvcard'] = $val;
		return $this;
	}
	public function getAddressVCard()
	{
		return $this->_data['addressvcard'];
	}
	// setter for pos address vcard code
	public function setPOSAddressVCard($val)
	{
		$this->_data['posaddressvcard'] = $val;
		return $this;
	}
	public function getPOSAddressVCard()
	{
		return $this->_data['posaddressvcard'];
	}
	
	public function setURI($key, $val)
	{
		$this->_URI[$key] = $val;
		return $this;
	}
	public function getURI($key)
	{
		return $this->_URI[$key];
	}
	
	public function setAttributeCode($what, $val)
	{
		$this->_attributeCodes[$what] = $val;
		return $this;
	}
	public function getAttributeCode($what)
	{
		return $this->_attributeCodes[$what];
	}
	public function getReplacements()
	{
		$replacements = array(
			"{lang}"	=> $this->sysinfo->getLocaleCode()
		);
		return $replacements;
	}
	
	
	
	protected function initBusinessData()
	{
		$this->initAddressVCard();
		$this->initPOSAddressVCard();
	}
	
	/**
	 * creates a vcard for business address
	 * @param $URI
	 * @return void
	 */
	protected function initAddressVCard( $URI=NULL )
	{
		if (!isset($URI)) $URI = $this->getURI("address");
		
		if (!isset($this->cacheAddressVCard))	// cachable
		{
			$address = $this->Business->getAddress();
			$this->cacheAddressVCard = $this->getEmptyVCard();
			$this->cacheAddressVCard	-> setStreetaddress ( @$address["streetaddress"] )
										-> setPostalcode ( @$address["postalcode"] )
										-> setLocality ( @$address["locality"] )
										-> setCountryname ( @$address["countryname"] )
										-> setTel ( @$address["tel"] )
										-> setEmail ( @$address["email"] )
										;
		}
		$addressVCardCode = $this->cacheAddressVCard->toXHTML( $URI );
		$this->setAddressVCard($addressVCardCode);
	}
	
	/**
	 * creates vcard for pos address
	 * @param $URI
	 * @return void
	 */
	protected function initPOSAddressVCard( $URI=NULL )
	{
		if (!$this->Business->hasPOS()) return;	// company has no POS
		if (!isset($URI)) $URI = $this->getURI("posAddress");
		
		if (!isset($this->cachePOSAddressVCard))	// cachable
		{
			$address = $this->Business->getPOSAddress();
			$this->cachePOSAddressVCard = $this->getEmptyVCard();
			$this->cachePOSAddressVCard	-> setStreetaddress ( @$address["streetaddress"] )
										-> setPostalcode ( @$address["postalcode"] )
										-> setLocality ( @$address["locality"] )
										-> setCountryname ( @$address["countryname"] )
										-> setTel ( @$address["tel"] )
										-> setEmail ( @$address["email"] )
										;
		}
		$POSAddressVCardCode = $this->cachePOSAddressVCard->toXHTML( $URI );
		$this->setPOSAddressVCard($POSAddressVCardCode);
	}
	
	/**
	 * returns a new vCard instance with our formater
	 * @return unknown_type
	 */
	protected function getEmptyVCard()
	{
		$vCardModel = Mage::getModel('msemanticbasic/VCard');
		$vCardModel->setRdff($this->rdffClassName);
		return $vCardModel;
	}
	
	protected function initBusinessURIs()
	{
		$this->setURI('businessEntity', $this->Business->getBaseURL() . '#businessEntity');
		$this->setURI('posAddress', $this->Business->getBaseURL() . "#shopaddress" );
		$this->setURI('address', $this->Business->getBaseURL() . "#address" );
		$this->setURI('locationOfSale', $this->Business->getBaseURL() . "#shop");
	}
	
	protected function initProductData()
	{
		return;
	}
	
	protected function initProductURIs()
	{
		// $this->setURI('offering', $this->Product->getProductUrl(). '#offering_' . $this->Product->getID() );
		$this->setURI('offering', $this->Product->getProductUrl(). '#offering'  );
		$this->setURI("unitPriceSpecification", $this->Product->getProductUrl() . "#UnitPriceSpecification_" . $this->Product->getID());
		$this->setURI("regularUnitPriceSpecification", $this->Product->getProductUrl() . "#UnitPriceSpecification_" . $this->Product->getID() . "_regular");
		
		 $this->setURI("specialUnitPriceSpecification", $this->Product->getProductUrl() . "#UnitPriceSpecification_" . $this->Product->getID() . "_special");
		
		$this->setURI("typeAndQuantityNode", $this->Product->getProductUrl() . "#TypeAndQuantityNode_" . $this->Product->getID());
		//$this->setURI("product", "#product_" . $this->Product->getID());
		$this->setURI("product", "#product_data");
	
	}
	
	protected function initProductAttributeCodes()
	{
		$this->setAttributeCode("strongid", "gr_ean");	// changed in version 0.9.9.3.9
		$this->setAttributeCode("name_en", "gr_name_en");
		$this->setAttributeCode("description_en", "gr_description_en");
		$this->setAttributeCode("valid_through", "gr_valid_through");
	}
	
	/**
	 * ***********************************
	 *  public good relations functions
	 *  **********************************
	 *  
	 *  (1) Business
	 */
	
	public function businessEntity()
	{
		$statements = "";
		// $statements .= $this->RDFS->seeAlso( "" );
		$statements .= $this->bLegalName();
		$statements .= $this->getAddressVCard();	// gets complete vcard Code
		$statements .= $this->VCARD->url($this->Business->getBaseURL());
		$statements .= $this->businessOffers();
		$statements .= $this->hasPOS();
		
		
		// about the Business Entity
		$grBusinessEntity = $this->rdff->wrapStatements($statements, '#businessEntity', "gr:BusinessEntity");
		$product = Mage::getModel('catalog/product');
 
$attributes = Mage::getResourceModel('eav/entity_attribute_collection')
    ->setEntityTypeFilter($product->getResource()->getTypeId())
    ->addFieldToFilter('attribute_code', 'manufacturer') // This can be changed to any attribute code
    ->load(false);
 
$attribute = $attributes->getFirstItem()->setEntity($product->getResource());
 
/* @var $attribute Mage_Eav_Model_Entity_Attribute */
$manufacturers = $attribute->getSource()->getAllOptions(false);

		
		foreach($manufacturers as $manuf) {
        $grBusinessEntity .= "<div typeof=\"gr:BusinessEntity\" about=\"#manufacturer_".$manuf['value']."\">
    <div property=\"rdfs:label\" content=\"".$manuf['label']."\" xml:lang=\"".$this->sysinfo->getLocaleCode()."\"></div>
    </div>\n";

    }

		 
		
		
		return $grBusinessEntity;
	}
	
	protected function bLegalName()
	{
		//gr:legalName, rdfs:label, vcard:fn for gr:BusinessEntity
		$labelPropArray = array(
			"gr:legalName"	=> $this->Business->getLegalName(),
			"rdfs:label"	=> $this->Business->getLegalName(),
			"vcard:fn"		=> $this->Business->getLegalName()
		);
		$grLegalNameDefinitions = "";
		$grLegalNameDefinitions .= $this->rdff->properties($labelPropArray, $this->getReplacements());
		return $grLegalNameDefinitions;
	}
	
	protected function hasPOS()
	{
		if ($this->Business->hasPOS())
		{
			$statements = "";
			$statements .= $this->getPOSAddressVCard();
			// now gr:Location
			$class = "gr:Location";
			$hasPOS = $this->rdff->wrapStatements($statements ,$this->getURI('locationOfSale') , $class);
			#rel
			$hasPOSRel = $this->rdff->wrapRel($hasPOS, "gr:hasPOS");
			return $hasPOSRel;
		}
		else return;
	}
	
	protected function businessOffers($suffix="")
	{
		$statements = "";
		$statements .= $this->RDFS->pDesc( $this->Business->getOfferingDescription(), $this->sysinfo->getLocaleCode() );
		$statements .= $this->RDFS->isDefinedBy( $this->Business->getBaseURL() );
		$statements .= $this->availableAtOrFrom();
		$statements .= $this->validFromThrough();
		//
		$statements .= $this->eligibleRegions();
		$statements .= $this->eligibleCustomerTypes();
		$statements .= $this->acceptedPaymentMethods();
		$statements .= $this->deliveryMethods();
		
		
		#about
		$grOfferingAbout = $this->Business->getBaseURL() . "#offering".$suffix;
		$grOfferingDescription = $this->rdff->wrapStatements($statements, $grOfferingAbout, "gr:Offering");
		
		$grOffering = $this->rdff->wrapRel($grOfferingDescription ,"gr:offers");
		
		return $grOffering;
	}
	
	protected function availableAtOrFrom()
	{	 
		$settings = Mage::getStoreConfig("semantium");
		if (@$settings['pos_address']['haspos'] == 1) 
		$grAvailableAtOrFrom = $this->rdff->rel("gr:availableAtOrFrom", $this->getURI("locationOfSale"));
		else $grAvailableAtOrFrom = "";
		return $grAvailableAtOrFrom; 
		
	}
	
	protected function validFromThrough()
	{
		$properties = array(
			"gr:validFrom"		=> $this->div->dateToIso8601( $this->Business->getValidFrom() ),
			"gr:validThrough"	=> $this->div->dateToIso8601( $this->Business->getValidThrough() )
		);
		$validFromThrough = $this->rdff->properties($properties, $this->getReplacements());
		return $validFromThrough;
	}
	
	protected function eligibleRegions()
	{
		$countries = $this->Business->getAllowedCountries();
		$grEligibleRegions = "";
		if (!is_array($countries)) $grEligibleRegions = "";
		else
		{
			foreach ($countries as $countrycode)
			{
				$properties = array("gr:eligibleRegions" => $countrycode);
				$grEligibleRegions .= $this->rdff->properties($properties,$this->getReplacements());
			}
		}
		return $grEligibleRegions;
	}
	
	
	protected function eligibleCustomerTypes()
	{
		$grEligibleCustomerTypes = "";
		forEach ($this->Business->getCustomerTypes() as $cTypeCode)
		{
			$customerTypeRel = @$this->CUSTOMER_TYPES[$cTypeCode];
			$grEligibleCustomerTypes .= $this->rdff->rel("gr:eligibleCustomerTypes", $customerTypeRel);
		}
		return $grEligibleCustomerTypes;
	}
	
	protected function acceptedPaymentMethods()
	{
		$grAcceptedPaymentMethods = "";
		forEach ($this->Business->getPaymentMethods() as $pMCode)
		{
			$paymentMethodRel = @$this->PAYMENT_METHODS[$pMCode];
			// echo " [".$paymentMethodRel."] ";
			$grAcceptedPaymentMethods .= $this->rdff->rel("gr:acceptedPaymentMethods", $paymentMethodRel);
		}
		
		
		return $grAcceptedPaymentMethods;
	}
	
	protected function deliveryMethods()
	{
		$deliveryMethods = $this->Business->getDeliveryMethods();
		$grAvailableDeliveryMethods = "";
		foreach ($deliveryMethods as $deliveryMethodCode)
		{
			$deliveryMethod = @$this->DELIVERY_METHODS[$deliveryMethodCode];
			$grAvailableDeliveryMethods .= $this->rdff->rel("gr:availableDeliveryMethods", $deliveryMethod);
		}
		return $grAvailableDeliveryMethods;
	}
	
	/**
	 * (2) Products
	 */
	// for just 1 product on a page
	// ------------------------------------------------------------------------------------------------------------------------
	
	public function pOffering()
	{
		$productId = $this->Product->getId();
   		$product = $this->Product->load($productId);
		$statements = "";
		$grOffering = "";
		$statements .= $this->pOffers();
		$statements .= $this->rdff->rel("foaf:page", "");
		$statements .= $this->pValidFromThrough();
		$statements .= $this->pHasBusinessFunction();
		$statements .= $this->pHasPriceSpecification();
		$statements .= $this->availableAtOrFrom();
		$statements .= $this->pIncludesObject();
		
		$statements .= $this->eligibleRegions();
		// $statements .= $this->eligibleCustomerTypes();
		$statements .= $this->acceptedPaymentMethods();
		$statements .= $this->deliveryMethods();
		
		
		
		// Inventory - VALID
	   
	    
		if ($product->getTypeId() != "bundle" and $product->getTypeId() != "configurable") {
		
		if ($product->getStockItem()->getManageStock())
		{
		
		$inv = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getQty();
		}
		else {
		if ($product->isSaleable()) {/*$inv = 42;*/}
		}
		
	    if (isset($inv)) {
   					$statements .= "
<div rel=\"gr:hasInventoryLevel\">
	<div typeof=\"gr:QuantitativeValue\">
    	<div property=\"gr:hasMinValue\" content=\"".round($inv,0)."\" datatype=\"xsd:float\"></div>
        <div property=\"gr:hasUnitOfMeasurement\" datatype=\"xsd:string\" content=\"C62\"></div>
	</div>
</div>";
			}
		
		}
		
		
		
		
		$localeCode = $this->sysinfo->getLocaleCode();
			// product name
		$pName = $this->Product->getName();
		$statements .= $this->RDFS->pName( $pName, $localeCode );
		// product description
		$pDescr = $this->Product->getDescription();
		$pDescr = $this->div->removeTagsAndNls($pDescr); // no HTML-Tags, no newlines
	    $pDescr = htmlspecialchars($pDescr);
	    $statements .= $this->RDFS->pDesc( $pDescr, $localeCode );
	    
   
        //$statements .=  Mage::getStoreConfig('catalog/review');
		//review stuff
		$statements .= "
		<div rel=\"foaf:maker\" resource=\"urn:goodrelations_shop_extensions:msemantic:v1.2.6.5\"></div>";
	 	$array =  Mage::getStoreConfig('catalog/review');
		#if ($array['allow_guest'] == 1) // reviews active
        $statements .= $this->pReview();
		$grOffering .= $this->rdff->wrapStatements($statements, $this->getURI('offering'), "gr:Offering");
		$isSaleable = $this->Product->isSaleable();
		
		/* #This will be the related product code.
		$related = $product->getUpsellProducts();
		# print gettype($related);
        # $related->load();	
        foreach ($related as $item) {
			# $item->setDoNotUseCategoryId(true);
			print $item->getProductUrl();
			print $item->getName();
			# print gettype($item);
		}
		*/
		
		
		
		return $grOffering;
		
	}
	
	
	protected function pReview()
	{
		$productId = $this->Product->getId();
	    $product = $this->Product->load($productId);
		$storeId = Mage::app()->getStore()->getId();
		
		Mage::getModel('review/review')->getEntitySummary($product, $storeId);
		if ($product->getRatingSummary()->getReviewsCount())
		   $count = $product->getRatingSummary()->getReviewsCount();
        else $count = 0;
       
        
        $ratings = $product->getRatingSummary();
        if ($ratings['rating_summary']) { 
		if ($ratings['rating_summary'] == 1) $rating = 5;
		else $rating = round($ratings['rating_summary'] / 20);
		$statements = "<div rel=\"v:hasReview\"><div typeof=\"v:Review-aggregate\" about=\"";
		$statements .=  $this->Product->getProductUrl();
		$statements .= "#review_data\"><div property=\"v:rating\" datatype=\"xsd:string\" content=\"$rating\"></div> <div property=\"v:count\" datatype=\"xsd:string\" content=\"$count\"></div> 
		</div> 
	</div> ";
	
		
		return $statements;}
		else return "<!-- no review available -->";
	}
	
	protected function pOffers()
	{
		$pOffers = $this->rdff->rev("gr:offers", $this->getURI('businessEntity'));
		return $pOffers;
	}
	
	protected function pHasBusinessFunction($function="sell")
	{
		$pHasBusinessFunction = $this->rdff->rel("gr:hasBusinessFunction", $this->BUSINESS_FUNCTIONS[$function]);
		return $pHasBusinessFunction;
	}
	
	
	###################
	
	protected function pHasPriceSpecification()
	{
		$priceSpecifications = "";
		$productId = $this->Product->getId();
	    
		$product = $this->Product->load($productId);
		$storeId = Mage::app()->getStore()->getId();
		
		
		//check if tax is in price
        $tax = Mage::helper('tax');
        $priceIncludesTax = $tax->displayPriceIncludingTax();
        
        if ($priceIncludesTax) $propArray["gr:valueAddedTaxIncluded"] = 'true';
        else                   $propArray["gr:valueAddedTaxIncluded"] = 'false';
        
        // check if special price
        if ($this->Product->getSpecialPrice() && strtotime($this->Product->getSpecialToDate()) > strtotime("now")) {
		
          // Add tax if needed
          if ($priceIncludesTax) $price = $tax->getPrice($this->Product, $this->Product->getPrice(1));
          else                   $price = $this->Product->getPrice(1);
          $propArray_reg = array(
              "gr:valueAddedTaxIncluded" => $propArray["gr:valueAddedTaxIncluded"],
              "gr:hasCurrencyValue"     => round($price, 2), // 1 unit
              "gr:hasCurrency"          => Mage::getStoreConfig("currency/options/default"),
              "gr:hasUnitOfMeasurement" => "C62"    // price per unit
          );

          // Add tax if needed
          if ($priceIncludesTax) $price = $tax->getPrice($this->Product, $this->Product->getSpecialPrice(1));
          else                   $price = $this->Product->getSpecialPrice(1);
          $propArray_special = array(
              "gr:valueAddedTaxIncluded" => $propArray["gr:valueAddedTaxIncluded"],
              "gr:hasCurrencyValue"     => round($price, 2), // 1 unit
              "gr:hasCurrency"          => Mage::getStoreConfig("currency/options/default"),
              "gr:hasUnitOfMeasurement" => "C62"    // price per unit
          );
          
          $statements = $this->rdff->properties($propArray_special,$this->getReplacements());

          if ( $this->Product->getSpecialToDate() ){
            $validproperties = array(
              "gr:validFrom"    => $this->div->dateToIso8601( $this->Product->getSpecialFromDate() ), 
              "gr:validThrough" => $this->div->dateToIso8601( $this->Product->getSpecialToDate() ) 
            );
            $statements .= $this->rdff->properties($validproperties, $this->getReplacements());
          }
          else { $statements .= $this->validFromThrough();}

          $attributes = $this->rdff->wrapStatements($statements, $this->getURI("specialUnitPriceSpecification"), "gr:UnitPriceSpecification" );
        }
        
        
        else {
          // pricing
          if ($product->getTypeId() == "bundle") { 
            // get min price of bundle
            $minmax = $product->getPriceModel()->getPrices($product);
            $propArray["gr:hasMinCurrencyValue"] = round($minmax[0],2); 
            $propArray["gr:hasMaxCurrencyValue"] = round($minmax[1],2);
          }
          else {
            // Add tax if needed
            if ($priceIncludesTax) $price = $tax->getPrice($this->Product, $this->Product->getFinalPrice(1));
            else                   $price = $this->Product->getFinalPrice(1);
            $propArray["gr:hasCurrencyValue"] = round($price, 2);
          }

          // rest of array
          $propArray["gr:hasCurrency"] = Mage::app()->getStore()->getCurrentCurrencyCode();
          $propArray["gr:hasUnitOfMeasurement"] = "C62";

          $statements = $this->rdff->properties($propArray,$this->getReplacements());
          $statements .= $this->validFromThrough();
          
          $attributes = $this->rdff->wrapStatements($statements, $this->getURI("unitPriceSpecification"), "gr:UnitPriceSpecification" );
        }
		
		
		$priceSpecifications .= $this->rdff->wrapRel($attributes, "gr:hasPriceSpecification");
		
		return $priceSpecifications;
	}
	
	
	###########################
	
	
	
	protected function pValidFromThrough()
	{
		$validFrom =  date("Y-m-d", strtotime("now"));
		$validThrough = "";
		
		$now = strtotime("now");
		// 1: Special Price valid from through
		$specialFrom = strtotime( $this->Product->getSpecialFromDate() );
		$specialTo = strtotime( $this->Product->getSpecialToDate() );
		if ( $this->Product->getSpecialPrice() && ($specialFrom <= $now) && ($now <= $specialTo))	// for special prices check validity
		{
			$validFrom =  $this->Product->getSpecialFromDate();
			$validThrough = $this->Product->getSpecialToDate();
			if ($validFrom) $validFrom =  date("Y-m-d H:i:s", strtotime("now"));
			if (!$validThrough) $validThrough = date("Y-m-d H:i:s", strtotime("now")); // valid till now
		}
		// 2: product valid through
		elseif (($validThrough = $this->pValidThroughProduct()) != "")
		{
			if (strtotime($validThrough) < $now) $validFrom = $validThrough;	// expired
			else $validFrom =  date("Y-m-d", strtotime("now"));
		}
		// 3: business valid through
		else
		{
			$validThrough = $this->Business->getValidThrough();
		}
		$validFrom = $this->div->dateToIso8601($validFrom);
		$validThrough = $this->div->dateToIso8601($validThrough);
		$propArray = array(
			"gr:validFrom"		=> $validFrom,
			"gr:validThrough"	=> $validThrough
		);
		$pValidFromThrough = $this->rdff->properties($propArray,$this->getReplacements());
		return $pValidFromThrough;
	}
	
	protected function pValidThroughProduct()
	{
		$pValidThrough = $this->productGetAttributeValue('valid_through');
		return $pValidThrough;
	}
	
	protected function pIncludesObject()
	{
		$attributes = "";
		$attributes .= $this->pTypeAndQuantityNode();
		
		$pIncludesObject = $this->rdff->wrapRel($attributes, "gr:includesObject");
		return $pIncludesObject;
	}
	
	protected function pTypeAndQuantityNode()
	{
		$propArray = array(
			"gr:amountOfThisGood"		=> "1.0",	// 1
			"gr:hasUnitOfMeasurement"	=> "C62"	// price per unit
		);
		$statements = "";
		$statements .= $this->rdff->properties($propArray,$this->getReplacements());
		$statements .= $this->pTypeOfGood();
		
		$taqn = $this->rdff->wrapStatements($statements, $this->getURI("typeAndQuantityNode"), "gr:TypeAndQuantityNode" );
		return $taqn;
	}
	
	protected function pTypeOfGood()
	{
		$attributes = "";
		$attributes .= $this->pProduct();
		
		$pTypeOfGood = $this->rdff->wrapRel($attributes, "gr:typeOfGood");
		return $pTypeOfGood;
	}
	
	protected function pProduct()
	{
		$localeCode = $this->sysinfo->getLocaleCode();
		
		$statements = "";
		// link
		// following seelalso no rdf res.
		// $statements .= $this->RDFS->seeAlso($this->Product->getProductUrl()); 
		// product name
		$pName = $this->product->getName();
		$statements .= $this->RDFS->pName( $pName, $localeCode );
		// product description
		// product description
		$pDescr = $this->Product->getDescription();
		$pDescr = $this->div->removeTagsAndNls($pDescr); // no HTML-Tags, no newlines
	    $pDescr = htmlspecialchars($pDescr);
	    $statements .= $this->RDFS->pDesc( $pDescr, $localeCode );
   		
   		
   		$productId = $this->Product->getId();
   		$product = $this->Product->load($productId);
   		$storeId = Mage::app()->getStore()->getId();
   		
   		
   		if ($product->getData('condition')) {
   		$condition = $product->getResource()->getAttribute('condition')->getFrontend()->getValue($product);
		$statements .= $this->rdff->property("gr:condition",$condition, NULL,"en");
		}
		
		if ($product->getData('sku')) {
		$sku = $product->getData('sku');
	    $statements .= $this->rdff->property("gr:hasStockKeepingUnit",$sku, "xsd:string");
		}
		
		if ($product->getData('color')) {
		
		if (substr(Mage::getVersion(),2,1) > 4) $colorstr = "color";
		else $colorstr = "Color";
		
		$color = $product->getResource()->getAttribute($colorstr)->getFrontend()->getValue($product);
		
		$statements .= $this->rdff->property("gr:color",$color, NULL,"en");
		}
		
		if ($product->getData('manufacturer')) {
		$statements .= $this->rdff->rel("gr:hasManufacturer",$this->Business->getBaseURL() . '#manufacturer_'.$product->getData('manufacturer'), NULL,"en");
		}
		
				

		$categoryIds = $product->getCategoryIds();

		foreach($categoryIds as $categoryId) {
  			$category = Mage::getModel('catalog/category')->load($categoryId);
  
  			$i = $category->getLevel();
  			$parent = $category;
  			$category_name = "";
  			while($i > 2) {
      			$parent = Mage::getModel('catalog/category')->load($parent->getParentId());
      			if ($parent->getName() != "Hauptnavigation") {
      				$category_name = $parent->getName()."/".$category_name;
  	  				}
  				$i--;
  			}
  
  if ($category->getName()) $statements .= $this->rdff->property("gr:category",htmlentities($category_name.$category->getName(),ENT_QUOTES,"UTF-8"),NULL,$this->sysinfo->getLocaleCode());
  
}

		$settings = Mage::getStoreConfig("semantium");
		
		if (@$settings['strongid']['strongid_db']) {
		$strongId = $product->getData(@$settings['strongid']['strongid_dba']);
		}

		else {$strongId = $this->productGetAttributeValue('strongid');}

		if ($strongId)
		{
			$settings = Mage::getStoreConfig("semantium");
			$strongid_type = @$settings['strongid']['strongid_type'];
			
			$valid = false; // default validity of the strongId value
			switch ($strongid_type) {
				case 'gtin14':
					$strongid_property = "gr:hasGTIN-14";
					if(strlen($strongId) == 14) $valid = true;
					break;
				case 'gtin8':
					$strongid_property = "gr:hasGTIN-8";
					if(strlen($strongId) == 8) $valid = true;
					break;
				default: // ean13
					$strongid_property = "gr:hasEAN_UCC-13";
					if(strlen($strongId) == 13) $valid = true;
					break;
			}
			if ($valid) $statements .= $this->rdff->propertyAuto($strongid_property, $strongId, $this->getReplacements() );
			
		}
		// changes end in version 0.9.9.3.9
		
		// image - now foaf:logo
		
		// dawn of template style
		
		
		
		
		
		
		$statements .= $this->rdff->rel("foaf:depiction", $this->Product->getImageURL());
		// all
		$pProduct = "";
		//$pProduct .= $this->rdff->rel("product:Product", $this->getURI("product"));
		// now gr:SomeItems
		
		
		
		
		
		
		$pProduct .= $this->rdff->wrapStatements($statements, $this->Product->getProductUrl() . $this->getURI("product"), "gr:SomeItems");
		return $pProduct;
	}
	
	protected function productGetAttributeValue($what)
	{
		$attributeCode = $this->getAttributeCode($what);
		$attributeValue = $this->Product->getResource()->getAttribute($attributeCode)->getFrontend()->getValue($this->Product);
		return $attributeValue;
		
		
		

	}
	
	
	/**
	 * 
	 * @param $productCollection Products
	 * @return string
	 */
	public function businessEntityWithProducts($productCollection)
	{
		$statements = "";
		$statements .= $this->rdff->rel("foaf:page", "");
		$statements .= $this->bLegalName();
		$statements .= $this->getAddressVCard();	// gets complete vcard Code
		$statements .= $this->VCARD->url($this->Business->getBaseURL());
		$statements .= $this->hasPOS();
		$statements .= $this->businessOffersWithProducts($productCollection);
		
		// about the Business Entity
		$grBusinessEntity = $this->rdff->wrapStatements($statements, $this->getURI('businessEntity'), "gr:BusinessEntity");
		
		return $grBusinessEntity;
	}
	
	protected function businessOffersWithProducts($productCollection)
	{
		# generate multiple Offerings
		$grOfferings = "";
		forEach ($productCollection as $Product)
		{
			$this->setProduct($Product);
			$grOfferingDescription = $this->pOfferingDump();
			$grOffering = $this->rdff->wrapRel($grOfferingDescription ,"gr:offers");
			$grOfferings .= $grOffering;
		}
		
		
		return $grOfferings;
		
		return $output;
	}
	// for multiple products on 1 page
	public function pOfferingDump()
	{
		$statements = "";
		
		$statements .= $this->RDFS->isDefinedBy( $this->Business->getBaseURL() );
		
		// $statements .= $this->RDFS->seeAlso( $this->Product->getProductUrl() );
		$statements .= $this->pValidFromThrough();
		$statements .= $this->pHasBusinessFunction();
		$statements .= $this->pHasPriceSpecification();
		$statements .= $this->availableAtOrFrom();
		$statements .= $this->pIncludesObject();
		
		// common information
		//$statements .= $this->eligibleRegions();	// no eligible Regions -> to much data
		$statements .= $this->eligibleCustomerTypes();
		$statements .= $this->acceptedPaymentMethods();
		$statements .= $this->deliveryMethods();
		
		// about the Offering
		$grOffering = $this->rdff->wrapStatements($statements, $this->getURI('offering'), "gr:Offering");
		
		return $grOffering;
	}
}
