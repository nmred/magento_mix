<?php
require_once '../../showorder/src/vendor/autoload.php';
class autoCollect {
	// {{{ consts
	
	const ORDER_TYPE_PROCESSING = 'processing';
	const ORDER_TYPE_PENDING	= 'pending_payment';

	// }}}
	// {{{ members

	/**
	 * db connection 
	 * 
	 * @var mixed
	 * @access protected
	 */
	protected $connection = null;

	/**
	 * config 
	 * 
	 * @var string
	 * @access protected
	 */
	protected $config = array();

	/**
	 * sendAllOrderIds 
	 * 
	 * @var array
	 * @access protected
	 */
	protected $sendAllOrderIds = array();

	/**
	 * xslFiles 
	 * 
	 * @var array
	 * @access protected
	 */
	protected $xslFiles = array();

	/**
	 * timediff 
	 * 
	 * @var float
	 * @access protected
	 */
	protected $timediff = 0;

	// }}}
	// {{{ functions
	// {{{ public function __construct()

	public function __construct() {
		$data = parse_ini_file('app.ini', true);
		$config = isset($data['autoCollect']) ? $data['autoCollect'] : array();
		if (empty($config)) {
			throw new \Exception("Not autoCollect config.");
		}
		$this->config = $config;
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
	// {{{ protected function getOrderList()

	/**
	 * get order list order by status 
	 * 
	 * @param mixed $type 
	 * @access protected
	 * @return void
	 */
	protected function getOrderList($type = self::ORDER_TYPE_PROCESSING) {
		$startTime = (int)(time() / 86400) * 86400 - 86400 - 3600 * 8 - $this->timediff * 86400;
		$startTimeStr = date('Y-m-d H:i:s', $startTime);
		$endTimeStr = date('Y-m-d H:i:s', $startTime + 86400);
		$sql = 'select entity_id,customer_email,customer_firstname, increment_id, shipping_address_id'
			   . ' from sales_flat_order where status=\'' . $type . '\'';
		//$sql = 'select entity_id,customer_email,customer_firstname, increment_id, shipping_address_id'
		//	   . ' from sales_flat_order where increment_id=\'1100001386\'';
		$stmt = $this->connection->query($sql, PDO::FETCH_ASSOC);
		if (!$stmt) {
			return array();	
		}
		
		$list = array();
		foreach ($stmt as $row) {
			if (in_array($row['entity_id'], $this->sendAllOrderIds)) {
				continue;
			}
			$list[] = $row;
		}

		return $list;
	}

	// }}}
	// {{{ protected function getShipAddr()

	/**
	 * get order list order by status 
	 * 
	 * @param mixed $type 
	 * @access protected
	 * @return void
	 */
	protected function getShipAddr($addressId) {
		$sql = 'select postcode,lastname,street, city, telephone, country_id, firstname'
			   . ' from sales_flat_order_address where entity_id=' . $addressId;	
		$stmt = $this->connection->query($sql, PDO::FETCH_ASSOC);
		if (!$stmt) {
			return array();	
		}
		
		$list = array();
		foreach ($stmt as $row) {
			$list[] = $row;
		}

		return $list;
	}

	// }}}
	// {{{ protected function getCountry()

	/**
	 * get order list order by status 
	 * 
	 * @param mixed $type 
	 * @access protected
	 * @return void
	 */
	protected function getCountry($countryId) {
		$sql = 'select *'
			   . ' from country_map where id=\'' . $countryId . '\'';	
		$stmt = $this->connection->query($sql, PDO::FETCH_ASSOC);
		if (!$stmt) {
			return array();	
		}
		
		$list = array();
		foreach ($stmt as $row) {
			$list = $row;
		}

		return $list;
	}

	// }}}
	// {{{ protected function getOrderItems()

	/**
	 * get order items 
	 * 
	 * @param mixed $orderId 
	 * @access protected
	 * @return void
	 */
	protected function getOrderItems($orderId) {
		//$sql = 'select product_id,sku,name '
		//	   . ' from sales_flat_order_item where order_id=' . $orderId;		
		$sql = 'select * '
			   . ' from sales_flat_order_item where order_id=' . $orderId;		
		$stmt = $this->connection->query($sql, PDO::FETCH_ASSOC);
		if (!$stmt) {
			return array();	
		}
		
		$list = array();
		foreach ($stmt as $row) {
			$list[] = $row;
		}

		return $list;
	}

	// }}}
	// {{{ protected function getProductCategory()

	protected function getProductCategory($productId) {
		$sql = 'select category_id,is_parent '
			   . ' from catalog_category_product_index where product_id=' . $productId;		
		$stmt = $this->connection->query($sql, PDO::FETCH_ASSOC);
		if (!$stmt) {
			return array();	
		}
		
		$list = array();
		foreach ($stmt as $row) {
			$list[] = $row['category_id'];
		}

		return $list;
	}

	// }}}
	// {{{ protected function processProcessing()

	/**
	 * processProcessing 
	 * 
	 * @access protected
	 * @return void
	 */
	protected function processProcessing() {
		$processing = $this->getOrderList(self::ORDER_TYPE_PROCESSING);
		$result = array();
		$dressesResult = array();
		foreach ($processing as $key => $order) {
			$order['id'] = $order['increment_id'];
			$items = $this->getOrderItems($order['entity_id']);		
			if (empty($items)) {
				continue;	
			}

			$processing[$key]['price'] = 0;
			foreach ($items as $item) { 
				$processing[$key]['price'] += $item['row_total'];
			}
		}
		var_dump($processing);
	
		$this->createXsl($processing, 'orders');
		//foreach ($processing as $key => $value) {
		//	echo $value['customer_email'] . "\t" . $value['price'] . PHP_EOL;	
		//}
	}

	// }}}
	// {{{ protected function sendMail()
	
	/**
	 * 发送邮件 
	 * 
	 * @param mixed $title 
	 * @param mixed $body 
	 * @access protected
	 * @return void
	 */
	protected function sendMail() {
		$email = 'nmred_2008@126.com';	
		if (!$email) {
			return false;
		}
		$mail = new PHPMailer;
		$mail->isSMTP();                                      // Set mailer to use SMTP
		$mail->Host = $this->config['smtp_host'];  // Specify main and backup SMTP servers
		$mail->SMTPAuth = true;                              // Enable SMTP authentication
		$mail->Username = $this->config['smtp_user'];        // SMTP username
		$mail->Password = $this->config['smtp_pass'];        // SMTP password
		$mail->Port = 25;                                    // TCP port to connect to

		$mail->setFrom('service@mixbridal.com', 'Mixbridal Service');
		$mail->addAddress($email);               // Name is optional
		$mail->addAddress('service@mixbridal.com');               // Name is optional

		$mail->isHTML(true);                                  // Set email format to HTML

		$mail->Subject = '每日订单汇总-' . date('Y-m-d', time() - $this->timediff * 86400);
		$mail->Body    = '订单表见附件';
		$mail->AltBody = '订单表见附件';
		foreach ($this->xslFiles as $filename) {
			if (is_file($filename)) { 
				$mail->addAttachment($filename, basename($filename));         // Add attachments
			}
		}


		$ret = $mail->send();
		echo $mail->ErrorInfo;
		return $ret;
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
		$this->processProcessing();
	}

	// }}}
	// {{{ protected function createXsl()
		
	protected function createXsl($data, $filename) {
		$objPHPExcel = new PHPExcel();
		$objWorksheet = $objPHPExcel->getActiveSheet();

		$currentLine = 2;
		$data = array_values($data);
		$columns = array('A', 'B');
		foreach ($data as $k => $info) {
			$objWorksheet->setCellValue('A'. $currentLine, $info['customer_email']);	
			$objWorksheet->setCellValue('B'. $currentLine, $info['price']);	
			$currentLine++;
		}
		echo date('H:i:s') , " Write to Excel2007 format" , PHP_EOL;
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$filename = '/tmp/' . $filename . '.xlsx';
		$objWriter->save($filename);
		// Echo memory peak usage
		echo date('H:i:s') , " Peak memory usage: " , (memory_get_peak_usage(true) / 1024 / 1024) , " MB" , PHP_EOL;
		// Echo done
		echo date('H:i:s') , " Done writing file" , PHP_EOL;
		echo 'File has been created in ' , getcwd() , PHP_EOL;
		$this->xslFiles[] = $filename;
	}

	// }}}
	// }}}	
}
$auto = new autoCollect();
$auto->run();
