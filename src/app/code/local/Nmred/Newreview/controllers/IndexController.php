<?php
/**
 * ShopShark Blog Extension
 * @version   1.0 12.09.2013
 * @author    ShopShark http://www.shopshark.net <info@shopshark.net>
 * @copyright Copyright (C) 2010 - 2013 ShopShark
 */

class Nmred_Newreview_IndexController extends Mage_Core_Controller_Front_Action
{
	// {{{ public function reviewAction()

    public function reviewAction() {
		$page = $_GET['page'];
		$productId = $_GET['product_id'];
		$result = Mage_Curl::run('http://show.mixbridal.com/newreview/show?id=' . $productId . '&page=' . $page, 'GET', 5);
		$result = json_decode($result, true);
		$data = array();
		if (isset($result['data']) && !empty($result['data'])) {
			unset($result['data']['total']);
			foreach ($result['data'] as $key => $val) {
				$context = trim(strip_tags($val['context']));
				if (strlen($context) > 300) {
					$context = substr($context, 0, 300) . '... ';	
				}
				$result['data'][$key]['context'] = $context;
				$result['data'][$key]['create_time'] = date('Y-m-d', $val['create_time']);
			}
			$data = $result['data'];
		} else {
			$data = array();	
		}
		
		header('Content-type: application/json;charset=utf-8');	
		$status = !empty($data) ? 1 : 0;
		echo json_encode(array('status' => 1, 'data' => $data));
		return 0;
    }

	// }}}
	// {{{ public function infoAction()

    public function infoAction() {
		$reviewId = $_GET['review_id'];
		$result = Mage_Curl::run('http://show.mixbridal.com/newreview/info?review_id=' . $reviewId, 'GET', 5);
		$result = json_decode($result, true);
		$data = array();
		if (isset($result['data']) && !empty($result['data'])) {
			$data = $result['data'];
			$data['create_time'] = date('Y-m-d H:i:s', $data['create_time']);
			$data['context'] = trim(strip_tags($data['context']));
		} else {
			$data = array();	
		}
		
		header('Content-type: application/json;charset=utf-8');	
		$status = !empty($data) ? 1 : 0;
		echo json_encode(array('status' => 1, 'data' => $data));
		return 0;
    }

	// }}}
	// {{{ public function infoAction()

    public function colorAction() {
		$colorId = $_GET['color_id'];
		$id = $_GET['product_id'];
		$result = Mage_Curl::run('http://show.mixbridal.com/productcolor/info?id=' . $id . '&color_id=' . $colorId, 'GET', 5);
		$result = json_decode($result, true);
		$data = array();
		if (isset($result['data']) && !empty($result['data'])) {
			$data = $result['data'];
		} else {
			$data = array();	
		}
		
		header('Content-type: application/json;charset=utf-8');	
		$status = !empty($data) ? 1 : 0;
		echo json_encode(array('status' => 1, 'data' => $data));
		return 0;
    }

	// }}}
}
