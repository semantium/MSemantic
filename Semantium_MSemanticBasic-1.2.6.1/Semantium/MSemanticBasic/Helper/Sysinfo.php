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

class Semantium_MSemanticBasic_Helper_Sysinfo extends Mage_Core_Helper_Abstract
{
	protected $_localCode;
	protected $_mageVersionInfo;
	
	// gets the localeCode from Magento
	public function getLocaleCode()
	{
		if (!isset($this->_localeCode))
		{
			$this->_localeCode = substr(Mage::app()->getLocale()->getLocaleCode(), 0, 2);
		} 
		return $this->_localeCode;
	}
	
	protected function setMageVersionInfo($val)
	{
		$this->_mageVersionInfo = $val;
	}
	/**
	 * same function as Mage::getVersionInfo() in Mage v1.4+
	 * @return unknown_type
	 */
	protected function getMageVersionInfo()
	{
		if (!is_array($this->_mageVersionInfo))
		{
			$mageVersion = Mage::getVersion();
			$vParts = explode(".", $mageVersion);
			$versionInfo = array(
				"major" => $vParts[0],
				"minor"	=> $vParts[1],
				"revision" => $vParts[2],
				"patch" => $vParts[3]
			);
			$this->setMageVersionInfo($versionInfo);
		}
		return $this->_mageVersionInfo;
	}
	/**
	 * returns a version detail
	 * @param $keyword
	 * @return int
	 */
	public function getMageVersionDetail($keyword)
	{
		if (in_array($keyword, array("major", "minor", "revision", "patch")))
		{
			$versionInfo = $this->getMageVersionInfo();
			return $versionInfo[$keyword];
		}
		else return NULL;
		
	}
	
	/**
	 * checks if installed magento is bigger or equal a special version
	 * @param $version string
	 * @return boolean
	 */
	public function mageVersionBiggerThanOrEqual($version)
	{
		$versionParts = explode(".", $version);
		$major		= isset($versionParts[0]) ? $versionParts[0] : 0;
		$minor		= isset($versionParts[1]) ? $versionParts[1] : 0;
		$revision	= isset($versionParts[2]) ? $versionParts[2] : 0;
		$patch		= isset($versionParts[3]) ? $versionParts[3] : 0;
		$condition1 = ($this->getMageVersionDetail("major") >= $major);
		$condition2 = ($this->getMageVersionDetail("minor") >=  $minor);
		$condition3 = ($this->getMageVersionDetail("revision") >= $revision);
		$condition4 = ($this->getMageVersionDetail("patch") >= $patch);
		if ($condition1 && $condition2 && $condition3 && $condition4) return true;
		else return false;
	}
}