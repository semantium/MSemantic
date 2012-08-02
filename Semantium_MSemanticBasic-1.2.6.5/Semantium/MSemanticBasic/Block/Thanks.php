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
class Semantium_MSemanticBasic_Block_Thanks extends Mage_Core_Block_Template
{
	protected function _toHtml()
	{
		$this->settings = Mage::getStoreConfig("semantium");
		if ($this->settings['basicsettings']['setfooterlink'])
		{
			$html = parent::_toHtml();
			$html .= '<div id="msemantic">Semantic Web - ready thanks to <a href="http://www.msemantic.com">MSemantic</a></div>';
			return $html;
		}
	}
}