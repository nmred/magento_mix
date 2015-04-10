<?php
/**
 * ShopShark Blog Extension
 * @version   1.0 12.09.2013
 * @author    ShopShark http://www.shopshark.net <info@shopshark.net>
 * @copyright Copyright (C) 2010 - 2013 ShopShark
 */

class Nmred_Showorder_Block_Show extends Mage_Core_Block_Template
{
	/**
     * Initialize block's cache
     */
    protected function _construct()
    {
        parent::_construct();
    }

	public function getShowData() {
		$result = Mage_Curl::run('http://show.mixbridal.com/showorder/show', 'GET', 5);
		$result = json_decode($result, true);
		$mobile = Mage_Util::isMobile();
		$num = $mobile ? 2 : 5;
		$data = array();
		foreach ($result['data'] as $key => $value) {
			$index = $key / $num;
			$cIndex = $key % $num;
			$data[$index][$cIndex] = $value;
		}
		
		foreach ($data as $key => $value) {
			if (count($value) != $num) {
				unset($data[$key]);	
			}	
		}
		return $data;	
	}
}
