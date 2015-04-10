<?php
/**
 * ShopShark Blog Extension
 * @version   1.0 12.09.2013
 * @author    ShopShark http://www.shopshark.net <info@shopshark.net>
 * @copyright Copyright (C) 2010 - 2013 ShopShark
 */

class Nmred_Showorder_IndexController extends Mage_Core_Controller_Front_Action
{
    public function reviewAction() {
		$id = $_GET['id'];
		$data = Mage_Curl::run("http://show.mixbridal.com/showorder/view?id=$id");
		$data = json_decode($data, true);
		
		header('Content-type: application/json;charset=utf-8');	
		if (!isset($data['data'])) {
			echo json_encode(array('status' => 1, 'data' => array()));
			return 0;
		}

		$products = $data['data']['products'];
		$productInfos = array();
		foreach ($products as $productId) {
			if (trim($productId) && !$productId) {
				continue;	
			}
			try {
				$productInfos[] = $this->getProduct($productId);	
			} catch(Exception $e) {
			}	
		}
		$data['data']['product_infos'] = $productInfos;
		echo json_encode(array('status' => 0, 'data' => $data['data']));
		return 0;
    }

	protected function getProduct($id) {
		$model = Mage::getModel('catalog/product');
		$_product = $model->load($id);	
		$info = array(
			'url' => $_product->getProductUrl(),
			'img' => $_product->getThumbnailUrl(),
		);
		return $info;
	}
}
