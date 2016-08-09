<?php
require_once '../../showorder/src/vendor/autoload.php';

class InsertPrice {
	// {{{ functions
	// {{{ public function __construct()

	public function __construct() {
		$data = parse_ini_file('app.ini', true);
		$config = isset($data['insertPrice']) ? $data['insertPrice'] : array();
		if (empty($config)) {
			throw new \Exception("Not insertPrice config.");
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
			
			if (!$this->getCategroy($row['entity_id'])) {
				continue;
			}

			$price = false;
			$price = $this->getPrice($row['entity_id'], 76);
			if (!$price) {
				$price = $this->getPrice($row['entity_id'], 75);
			}
			if (!$price) {
				continue;
			}
			$productIds[] = array(
				'id' => $row['entity_id'],
				'price' => $price,
			);
		}

		return $productIds;
	}

	// }}}
	// {{{ protected function getCategroy()

	/**
	 * getCategroy 
	 * 
	 * @access protected
	 * @return void
	 */
	protected function getCategroy($productId) {
		$sql = 'select * from catalog_category_product where product_id=' . $productId;
		$stmt = $this->connection->query($sql, PDO::FETCH_ASSOC);
		if (!$stmt) {
			return array();
		}
		
		$cates = array();
		foreach ($stmt as $row) {
			$cates[] = $row['category_id'];
		}

		if (!in_array(204, $cates)) {
			return false;	
		}
		return true;
	}

	// }}}
	// {{{ protected function getPrice()

	/**
	 * getPrice 
	 * 
	 * @access protected
	 * @return void
	 */
	protected function getPrice($productId, $type) {
		$sql = 'select * from catalog_product_entity_decimal where entity_id=' . $productId  . ' and attribute_id=' .  $type;
		$stmt = $this->connection->query($sql, PDO::FETCH_ASSOC);
		if (!$stmt) {
			return false;
		}
		
		foreach ($stmt as $row) {
			if (isset($row['value'])) {
				return $row['value'];
			}
		}

		return false;
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
		foreach ($productIds as $product) {
			$this->insertData($product);
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
	protected function insertData($productInfo) {
		$price = $productInfo['price'] - 20;
		$isflag = false;
		if ($price >= 159.99) {
			$price = $price - 10;	
			$isflag = true;
		}
		$sql = 'insert into catalog_product_entity_group_price (entity_id,all_groups,customer_group_id,value,website_id) values (' . $productInfo['id'] . ',0,7,' . $price. ',0);';
		$affected = $this->connection->exec($sql);		

		$price = $productInfo['price'] - 10;
		if ($isflag) {
			$price -= 10;
		}
		$sql = 'insert into catalog_product_entity_group_price (entity_id,all_groups,customer_group_id,value,website_id) values (' . $productInfo['id'] . ',0,5,' . $price. ',0);';
		$affected = $this->connection->exec($sql);	
		$sql = 'insert into catalog_product_entity_group_price (entity_id,all_groups,customer_group_id,value,website_id) values (' . $productInfo['id'] . ',0,6,' . $price . ',0);';
		$affected = $this->connection->exec($sql);		
	}

	// }}}
	// }}}
}

$insert = new InsertPrice();
$insert->run();
