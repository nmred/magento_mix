<?php
require_once '../../showorder/src/vendor/autoload.php';

use PHPHtmlParser\Dom;

class ProductColor {
	// {{{ functions
	// {{{ public function __construct()

	public function __construct() {
		$data = parse_ini_file('app.ini', true);
		$config = isset($data['productColor']) ? $data['productColor'] : array();
		if (empty($config)) {
			throw new \Exception("Not productColor config.");
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
			array_pop($splitVal);
			if (!$productId) {
				continue;
			}
			
			$item['entity_id']  = $row['entity_id'];
			$item['product_id'] = $productId;
			$item['url'] = implode('-', $splitVal) . '_' . $productId . '.html';
			$productIds[] = $item;
		}

		return $productIds;
	}

	// }}}
	// {{{ protected function getProductColorList()

	/**
	 * getProductColorList 
	 * 
	 * @access protected
	 * @return void
	 */
	protected function getProductColorList($id) {
		$sql = "select * from catalog_product_option as o join catalog_product_option_title as t on t.option_id=o.option_id where product_id=$id and title='Color'";
		$stmt = $this->connection->query($sql, PDO::FETCH_ASSOC);
		if (!$stmt) {
			return array();
		}
		
		// option id
		$options = array();
		foreach ($stmt as $row) {
			$options[] = $row;
		}
		if (empty($options)) {
			return array();
		}

		$optionId = $options[0]['option_id'];
		$sql = "select v.option_type_id,t.title from catalog_product_option_type_value as v join catalog_product_option_type_title as t on v.option_type_id=t.option_type_id where option_id=$optionId";
		$stmt = $this->connection->query($sql, PDO::FETCH_ASSOC);
		if (!$stmt) {
			return array();
		}
		$colors = array();
		foreach ($stmt as $row) {
			$colors[strtolower($row['title'])] = $row['option_type_id'];
		}

		return $colors;
	}

	// }}}
	// {{{ protected function nonExists()

	/**
	 * isExists 
	 * 
	 * @access protected
	 * @return void
	 */
	protected function nonExists($id, $colorId, $imageId) {
		$sql = 'select entity_id, color_id, image_id from product_color where entity_id=' . $id 
				. ' and color_id=' . $colorId . ' and image_id=' . $imageId;
		$stmt = $this->connection->query($sql, PDO::FETCH_ASSOC);
		if (!$stmt || empty($stmt)) {
			return array();
		}
		
		$result = array();
		foreach ($stmt as $row) {
			$result[] = $row;
		}

		return count($result);
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
	protected function paserList($product) {
		$url = 'http://www.forherandforhim.com/' . $product['url'];		
		$html = $this->curl($url);
		$dom = new Dom;
		$dom->load($html);
		//$dom = new Dom;
		//$dom->loadFromFile('aa');
		$idDom = $dom->find('.slidecontentbox a.showmessage');
		$ids = array();
		foreach ($idDom as $id) {
			$tmp = $id->getAttribute('rel');	
			list($key, $title) = explode('_', $tmp);
			$ids[$key] = strtolower($title);
		}
		$colors = $this->getProductColorList($product['entity_id']);
		$result = array();
		foreach ($ids as $id => $title) {
			$detailUrl = 'http://www.forherandforhim.com/index.php?route=product/product/getcolorimg';
			$params = array(
				'value_id' => $id,
			);
			if (!isset($colors[$title])) {
				continue;
			}
			$rev = $this->curl($detailUrl, 'POST', $params);
			$data = json_decode($rev, true);
			$imgs = array();
			if (!isset($data['imglist']) || empty($data['imglist'])) {
				continue;
			}
			foreach ($data['imglist'] as $val) {
				$imgs[$val['image_id']]	= $val['image'];
			}
			$result[$colors[$title]] = $imgs;
		}
	
		return $result;
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
			$colors = $this->getProductColorList($product['entity_id']);
			//$lists	= array();
			$result = $this->paserList($product);

			$this->insertData($result, $product);
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
		foreach ($lists as $colorId => $imgs) {
			foreach ($imgs as $imageId => $url) {
				if ($this->nonExists($productInfo['entity_id'], $colorId, $imageId)) {		
					continue;
				}
				$sql = 'insert into product_color (entity_id, color_id, image_id, url) 
						VALUES(' . $productInfo['entity_id'] . ', ' . $colorId 
								 . ',' . $imageId . ',\'' . $url . '\')';
				$affected = $this->connection->exec($sql);		
			}
		}
	}

	// }}}
	// }}}
}

$sync = new ProductColor();
$sync->run();
