<?php
require_once '../../showorder/src/vendor/autoload.php';

use PHPHtmlParser\Dom;

class SyncReview {
	// {{{ functions
	// {{{ public function __construct()

	public function __construct() {
		$data = parse_ini_file('app.ini', true);
		$config = isset($data['syncReview']) ? $data['syncReview'] : array();
		if (empty($config)) {
			throw new \Exception("Not syncReview config.");
		}
        $dsn = 'mysql:dbname=%s;host=%s;port=%s';
        $dsn = sprintf($dsn, $config['mysql_dbname'], $config['mysql_host'], $config['mysql_port']);

        try {
            $this->connection = new PDO($dsn, $config['mysql_user'], $config['mysql_pass']);
        } catch (PDOException $e) {
            echo 'Connection failed: ' . $e->getMessage();
            exit(1);
        }
	}

	// }}}
	// {{{ protected function getProductIds()

	/**
	 * getProductIds 
	 * 
	 * @access protected
	 * @return void
	 */
	protected function getProductIds() {
		$sql = 'select e.entity_id, v.value from catalog_product_entity as e join catalog_product_entity_varchar as v on e.entity_id=v.entity_id where v.attribute_id=97';
		$stmt = $this->connection->query($sql, PDO::FETCH_ASSOC);
		if (!$stmt) {
			return array();
		}
		
		$productIds = array();
		foreach ($stmt as $row) {
			$splitVal = explode('-', $row['value']);
			$productId = (int)end($splitVal);
			if (!$productId) {
				continue;
			}
			
			$item['entity_id']  = $row['entity_id'];
			$item['product_id'] = $productId;
			$productIds[] = $item;
		}

		return $productIds;
	}

	// }}}
	// {{{ protected function nonExists()

	/**
	 * isExists 
	 * 
	 * @access protected
	 * @return void
	 */
	protected function nonExists($reviewIds) {
		$sql = 'select review_id from syncreview_entity where in (' . implode(',', $reviewIds) . ')';;
		$stmt = $this->connection->query($sql, PDO::FETCH_ASSOC);
		if (!$stmt) {
			return array();
		}
		
		foreach ($stmt as $row) {
			unset($reviewIds[$row['review_id']]);
		}

		return $reviewIds;
	}

	// }}}
	// {{{ protected function paserList()

	/**
	 * paserList 
	 * 
	 * @param mixed $productId 
	 * @access protected
	 * @return void
	 */
	protected function paserList($url) {
		$html = $this->curl($url);
		$dom = new Dom;
		$dom->load($html);
		$more = $dom->find('.colbox .ct a.readmore');
		$nextLinks = $dom->find('.links a');
		$links = array();
		$result = array();
		foreach ($nextLinks as $link) {
			$links[] = $link->getAttribute('href');	
		}
		foreach ($more as $content) {
			$detailUrl = 'http://www.forherandforhim.com/index.php?route=product/product/reviewdetail';
			$reviewId = $content->getAttribute('rel');
			$params = array(
				'review_id' => $reviewId,
			);
			$detailHtml = $this->curl($detailUrl, 'POST', $params);
			$items = array();
			$detailDom = new Dom;
			$detailDom->load($detailHtml);
			$images = $detailDom->find('.reviewdetail .ct a.reviewthumb');
			$text = $detailDom->find('.detailtext');
			$items['text'] = $text[0]->innerHtml;
			$date = $detailDom->find('.date');
			$items['date'] = $date[0]->innerHtml;
			$username = $detailDom->find('.username');
			$items['username'] = $username[0]->innerHtml;
			foreach ($images as $img) {
				$imgInfo['big'] = $img->getAttribute('href');
				$imgInfo['small'] = $img->firstChild()->getAttribute('src');
				$items['img'][] = $imgInfo;
			}

			$result[$reviewId] = $items;
		}
	
		return array('data' => $result, 'links' => array_unique($links));
	}

	// }}}
	// {{{ protected function curl()

	/**
	 * curl 
	 * 
	 * @param mixed $url 
	 * @param string $type 
	 * @param array $data 
	 * @access protected
	 * @return void
	 */
	protected function curl($url, $type = 'GET', $data = array()) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		if ($type == 'POST') {
			curl_setopt($ch, CURLOPT_POST, 1 );
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data) );
		}
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}

	// }}}
	// {{{ public function run()

	/**
	 * run 
	 * 
	 * @access public
	 * @return void
	 */
	public function run() {
		$productIds = $this->getProductIds();
		//$productIds = array_slice($productIds, 0, 1);
		foreach ($productIds as $product) {
			$lists	= array();
			$url = 'http://www.forherandforhim.com/productreview_r' . $product['product_id'] . '.html';		
			$result = $this->paserList($url);
			$lists  = $result['data'];
			if (!empty($result['links'])) {
				foreach ($result['links'] as $link) {
					$result = $this->paserList($link);
					foreach ($result['data'] as $key => $item) {
						$lists[$key] = $item;	
					}
				}
			}

			$this->insertData($lists, $product);
		}
	}

	// }}}
	// {{{ protected function insertData()
		
	/**
	 * insertData 
	 * 
	 * @param mixed $lists 
	 * @access protected
	 * @return void
	 */
	protected function insertData($lists, $productInfo) {
		if (empty($lists)) {
			return;
		}
		$nonExists = $this->nonExists(array_keys($lists));
		foreach ($lists as $reviewId => $list) {
			if (!in_array($reviewId, $nonExists)) {
				continue;	
			}
			$createTime = strtotime($list['date']);
			$sql = 'insert into syncreview_entity (entity_id, product_id, review_id, 
					create_time, context, username) 
					VALUES(' . $productInfo['entity_id'] . ', ' . $productInfo['product_id']
							 . ',' . $reviewId . ',' . $createTime . ',\'' . $list['text'] . '\',\'' 
							 . $list['username'] . '\')';
			$affected = $this->connection->exec($sql);		
			if (isset($list['img']) && !empty($list['img'])) {
				foreach ($list['img'] as $img) {
					$sql = 'insert into syncreview_entity_img (review_id, big_img, img) 
					VALUES(' . $reviewId . ', \'' . $img['big']
							 . '\',\'' . $img['small'] . '\')';
					$affected = $this->connection->exec($sql);		
				}
			}
		}
	}

	// }}}
	// }}}
}

$sync = new SyncReview();
$sync->run();
