<?php
/**
 * ShopShark Blog Extension
 * @version   1.0 12.09.2013
 * @author    ShopShark http://www.shopshark.net <info@shopshark.net>
 * @copyright Copyright (C) 2010 - 2013 ShopShark
 */

class Nmred_Newreview_Block_Show extends Mage_Core_Block_Template
{
	/**
     * Initialize block's cache
     */
    protected function _construct()
    {
        parent::_construct();
    }

	public function getShowData() {
		$url = str_replace('.html', '', $_SERVER['REQUEST_URI']);
		$splitArr = explode('-', $url);
		$productId = (int)end($splitArr);
		if (!$productId) {
			return array();	
		}
		
		$mobile = Mage_Util::isMobile();
		$count = $mobile ? -1 : 3;
		$result = Mage_Curl::run('http://show.mixbridal.com/newreview/show?id=' . $productId . '&count=' . $count, 'GET', 5);
		$result = json_decode($result, true);
		if (isset($result['data']) && !empty($result['data'])) {
			$result['data']['product_id'] = $productId;
			foreach ($result['data'] as $key => $val) {
				if (!is_array($val)) {
					continue;	
				}
				$context = trim(strip_tags($val['context']));
				if (strlen($context) > 300) {
					$context = substr($context, 0, 300) . '... ';
				}
				$result['data'][$key]['context'] = $context;
			}
			return $result['data'];	
		} else {
			return array();	
		}
	}
}
