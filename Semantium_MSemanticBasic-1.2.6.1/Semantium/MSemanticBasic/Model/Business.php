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
class Semantium_MSemanticBasic_Model_Business extends Mage_Core_Model_Abstract
{
	protected $_settings;
	protected $_generalSettings;
	protected $_baseURL;
	protected $_data;
	// helper
	
	private $div = NULL;		// helper
	private $sysinfo = NULL;	// helper
	
	protected function _construct()
	{
		$this->_init('msemanticbasic/business');
		$this->div = Mage::app()->getHelper('msemanticbasic/Div');
		$this->sysinfo = Mage::app()->getHelper('msemanticbasic/Sysinfo');
		$this->setSettings( Mage::getStoreConfig("semantium") );
		$this->setGeneralSettings( Mage::getStoreConfig("general") );
		$this->initData();
	}
	
	public function initData()
	{
		$this->initBaseURL();
		
		// version >=1.4
		if ($this->sysinfo->mageVersionBiggerThanOrEqual("1.4"))
		{
			$legalname = $this->getSettings("businessinformation/legalname") ? $this->getSettings("businessinformation/legalname") : @$this->getGeneralSettings("store_information/name"); 
		}
		else	// version <1.4
		{
			$legalname = $this->getSettings("businessinformation/legalname");
		}
			$this->setLegalName( $this->div->removeTagsAndNls($legalname) );
		
		$this->initAddresses();
		
		$this->initOfferingDescription();
		$this->initValidFromThrough();
		$this->initCountries();
		$this->initPaymentOptions();
		$this->initDeliveryMethods();
		$this->initCustomerTypes();
	}
	
	protected function initBaseURL()
	{
		$this->setBaseURL( Mage::getUrl('') );
	}
	
	protected function initAddresses() {
		// Business Address
		$this->setAddress( $this->getSettings("address") );
		// POS Address
		if ($this->getSettings("pos_address/haspos"))
		{
			if (@$this->getSettings("pos_address/usefromcompany"))
			{
				$this->setPOSAddress( $this->getAddress() );
			}
			else
			{
				$this->setPOSAddress( $this->getSettings("pos_address") );
			}
		}
		else $this->setPOSAddress(FALSE);
	}
	protected function initOfferingDescription() {
		$this->setOfferingDescription( $this->div->removeTagsAndNls($this->getSettings("offering/description")) );
	}
	protected function initValidFromThrough()
	{
		$validFrom = date("Y-m-d H:i:s", strtotime("now"));
		$validThrough = $validFrom;
		$validPeriod = $this->getSettings("validity/valid_period");	// in months
		if ($validPeriod > 0)
		{
			$validThrough = date("Y-m-d H:i:s", strtotime("+".$validPeriod." month"));
		}
		else
		{
			$validThrough = $this->getSettings("validity/valid_through");
		}
		if ($validThrough == "") $validThrough = date("Y-m-d H:i:s", strtotime("now"));	// valid till now
		$this->setValidFrom( $validFrom );
		$this->setValidThrough( $validThrough );
	}
	
	protected function initCountries() {
		$countries = @explode(",", $this->getGeneralSettings("country/allow"));
		$this->setAllowedCountries( $countries );
	}
	
	protected function initPaymentOptions()
	{
		$paymentMethods = array();
		$paymentMethodSettings = @$this->getSettings("payment_options");
		forEach ($paymentMethodSettings as $paymentMethod => $checked)
		{
			if ($checked) $paymentMethods[] = $paymentMethod;
		}
		$this->setPaymentMethods($paymentMethods);
		
	}
	
	protected function initDeliveryMethods()
	{
		$deliveryMethods = array();
		$deliveryMethodsSettings = @$this->getSettings("delivery_methods");
		forEach ($deliveryMethodsSettings as $deliveryMethod => $checked)
		{
			if ($checked) $deliveryMethods[] = $deliveryMethod;
		}
		$this->setDeliveryMethods($deliveryMethods);
	}
	
	protected function initCustomerTypes()
	{
		$customerTypes = array();
		$customerTypesSettings = @$this->getSettings("customer_types");
		forEach ($customerTypesSettings as $customerType => $checked)
		{
			if ($checked) $customerTypes[] = $customerType;
		}
		$this->setCustomerTypes($customerTypes);
	}
	
	/* Setter & Getter */
	
	public function setBaseURL($val)
	{
		$this->_baseURL = $val;
		return $this;
	}
	public function getBaseURL()
	{
		return $this->_baseURL;
	}
	public function setLegalName($val)
	{
		$this->_data['legalname'] = $val;
		return $this;
	}
	public function getLegalName()
	{
		return $this->_data['legalname'];
	}
	
	public function setAddress($val)
	{
		$this->_data['address'] = $val;
		return $this;
	}
	public function getAddress()
	{
		return $this->_data['address'];
	}
	public function setPOSAddress($val)
	{
		$this->_data['posaddress'] = $val;
		return $this;
	}
	public function getPOSAddress()
	{
		return $this->_data['posaddress'];
	}
	
	public function hasPOS()
	{
		if ($this->getPOSAddress() !== FALSE) return TRUE;
		else return FALSE;
	}
	
	public function setOfferingDescription($val)
	{
		$this->_data['offeringdescription'] = $val;
		return $this;
	}
	public function getOfferingDescription()
	{
		return $this->_data['offeringdescription'];
	}
	public function setValidFrom($val)
	{
		$this->_data['validfrom'] = $val;
		return $this;
	}
	public function getValidFrom()
	{
		return $this->_data['validfrom'];
	}
	public function setValidThrough($val)
	{
		$this->_data['validthrough'] = $val;
		return $this;
	}
	public function getValidThrough()
	{
		return $this->_data['validthrough'];
	}
	public function setAllowedCountries($val)
	{
		$this->_data['allowedcountries'] = $val;
		return $this;
	}
	public function getAllowedCountries()
	{
		return $this->_data['allowedcountries'];
	}
	public function setPaymentMethods($val)
	{
		$this->_data['paymentmethods'] = $val;
		return $this;
	}
	public function getPaymentMethods()
	{
		return $this->_data['paymentmethods'];
	}
	public function setDeliveryMethods($val)
	{
		$this->_data['deliverymethods'] = $val;
		return $this;
	}
	public function getDeliveryMethods()
	{
		return $this->_data['deliverymethods'];
	}
	public function setCustomerTypes($val)
	{
		$this->_data['customertypes'] = $val;
		return $this;
	}
	public function getCustomerTypes()
	{
		return $this->_data['customertypes'];
	}
	
	
	
	protected function setSettings($val)
	{
		$this->_settings = $val;
		return $this;
	}
	/**
	 * e.g. getSettings("hans/wurst")
	 * @param $what
	 * @return unknown_type
	 */
	protected function getSettings($what)
	{
		$settings = $this->_settings;
		$parts=explode("/", $what);
		forEach ($parts as $part)
		{
			if (is_string($part)) $part = trim($part);
			$settings = $settings[$part];
		}
		return $settings;
	}
	
	protected function setGeneralSettings($val)
	{
		$this->_gereralSettings = $val;
		return $this;
	}
	protected function getGeneralSettings($what)
	{
		$settings = $this->_gereralSettings;
		$parts=explode("/", $what);
		forEach ($parts as $part)
		{
			if (is_string($part)) $part = trim($part);
			$settings = @$settings[$part];
		}
		return $settings;
	}
	
	
}