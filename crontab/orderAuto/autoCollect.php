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
		$startTime = (int)(time() / 86400) * 86400 - 86400 - 3600 * 8 - 0 * 86400;
		$startTimeStr = date('Y-m-d H:i:s', $startTime);
		$endTimeStr = date('Y-m-d H:i:s', $startTime + 86400);
		$sql = 'select entity_id,customer_email,customer_firstname, increment_id, shipping_address_id'
			   . ' from sales_flat_order where status=\'' . $type . '\''
			   . ' and created_at>\'' . $startTimeStr . '\''
			   . ' and created_at<=\'' . $endTimeStr . '\'';	
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
		foreach ($processing as $order) {
			$order['id'] = $order['increment_id'];
			$items = $this->getOrderItems($order['entity_id']);		
			if (empty($items)) {
				continue;	
			}

			$resultItems = array();
			foreach ($items as $item) { 
				$ritem = array();
				$options = unserialize($item['product_options']);
				if (isset($options['info_buyRequest']['qty'])) {
					$ritem['qty'] = $options['info_buyRequest']['qty'];
				} else {
					$ritem['qty'] = 0;
				}
				$ritem['id'] = $item['sku'];
				$ritem['color'] = '';
				$ritem['size']  = $item['name'];
				if (isset($options['options']) && !empty($options['options'])) {
					foreach ($options['options'] as $option) {
						if (false !== strpos(strtolower($option['label']), 'color')) {
							$ritem['color'] = $option['value'];
						}
						if ($option['label'] == 'Size') {
							$ritem['size'] = $option['value'];
						}
					}
				}
				$resultItems[] = $ritem;
			}
			$address = $this->getShipAddr($order['shipping_address_id']);
			$address = $address[0];
			$country = $this->getCountry($address['country_id']);
			if (!empty($country) && $country['name']) {
				$country = $country['name'];		
			} else {
				$country = $address['country_id'];	
			}
			$addressp = "%s %s \n %s \n %s, %s \n %s \n T:%s";
			$address = sprintf($addressp, $address['firstname'], $address['lastname'],
							   $address['street'], $address['city'], $address['postcode'], $country,
							   $address['telephone']);
			$dressesCount = 0;
			foreach ($items as $item) {
				$categroy = $this->getProductCategory($item['product_id']);	
				$dressesCates = array(204, 208);
				$dresses = array_intersect($dressesCates, $categroy);
				$isDresses = false;
				if (!empty($dresses)) {
					$isDresses = true;	
					$dressesCount++;
				}
			}
			
			if ($dressesCount >= 1) {
				$dressesResult[$order['id']] = array(
					'id' => $order['id'],
					'items' => $resultItems,
					'address' => $address,
				);
			} else {
				$result[$order['id']] = array(
					'id' => $order['id'],
					'items' => $resultItems,
					'address' => $address,
				);
			}
		}
	
		$filename = date('Ymd', time()) . '_dresses';
		$this->createXsl($dressesResult, $filename);
		$filename = date('Ymd', time()) . '_swatches';
		$this->createXsl($result, $filename);
		var_dump($this->sendMail());
		foreach ($this->xslFiles as $filename) {
			unlink($filename);	
		}
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

		$mail->Subject = '每日订单汇总-' . date('Y-m-d', time());
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
		$date = date('M d, Y', time());
		$objWorksheet->setCellValue('A1', 'MIX BRIDAL ORDERS (' . $date . ')');
		$objWorksheet->getRowDimension('1')->setRowHeight(60);
		$objWorksheet->getStyle('A1')->getFont()->setBold(true);
		$objWorksheet->getStyle('A1')->getFont()->setSize(16);
		$objWorksheet->mergeCells('A1:E1');
		$objWorksheet->getColumnDimension('A')->setWidth(40);
		$objWorksheet->getColumnDimension('B')->setAutoSize(40);
		$objWorksheet->getColumnDimension('C')->setAutoSize(40);
		$objWorksheet->getColumnDimension('D')->setAutoSize(60);
		$objWorksheet->getColumnDimension('E')->setAutoSize(40);
		$objWorksheet->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objWorksheet->getStyle('A1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

		$currentLine = 2;
		$data = array_values($data);
		$columns = array('A', 'B', 'C', 'D', 'E');
		foreach ($data as $k => $info) {
			$itemCount = count($info['items']);
			$range = 'A' . $currentLine . ':E' . ($currentLine + $itemCount);
			if ($k % 2 == 1) {
				$objWorksheet->getStyle($range)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
				$objWorksheet->getStyle($range)->getFill()->getStartColor()->setARGB('91b27e');		
			}
			$styleThinBlackBorderOutline = array(
				'borders' => array (
					'outline' => array (
						'style' => PHPExcel_Style_Border::BORDER_THIN,   //设置border样式
						//'style' => PHPExcel_Style_Border::BORDER_THICK,  另一种样式
						'color' => array ('argb' => 'FF000000'),          //设置border颜色
					),
				),
			);
			foreach ($columns as $col) {
				$objWorksheet->getStyle($col . $currentLine)->applyFromArray($styleThinBlackBorderOutline);
			}

			$objWorksheet->getRowDimension(''. $currentLine)->setRowHeight(40);
			$objWorksheet->setCellValue('A'. $currentLine, $info['id']);	
			$objWorksheet->getStyle('A' . $currentLine)->getFont()->getColor()->setARGB("fc5007");
			$objWorksheet->getStyle('A' . $currentLine)
						 ->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objWorksheet->getStyle('A' . $currentLine)
						 ->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
			$range = 'A' . $currentLine . ':A';
			$range .= $itemCount + $currentLine;
			$objWorksheet->mergeCells($range);
			$objWorksheet->setCellValue('B'. $currentLine, 'Item No.:');	
			$objWorksheet->setCellValue('C'. $currentLine, 'Color');	
			$objWorksheet->setCellValue('D'. $currentLine, 'Size');	
			$objWorksheet->setCellValue('E'. $currentLine, 'Quantity');	
			foreach ($columns as $col) {
				$objWorksheet->getStyle($col . $currentLine)->getFont()->setBold(true);
				$objWorksheet->getStyle($col . $currentLine)->getFont()->setSize(12);
				$objWorksheet->getStyle($col . $currentLine)
							 ->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objWorksheet->getStyle($col . $currentLine)
							 ->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
			}
			$itemcurrentLine = $currentLine + 1;
			foreach ($info['items'] as $key => $val) {
				foreach ($columns as $col) {
					$objWorksheet->getStyle($col . $itemcurrentLine)->applyFromArray($styleThinBlackBorderOutline);
				}
				$objWorksheet->getRowDimension(''. $itemcurrentLine)->setRowHeight(40);
				$objWorksheet->setCellValue('B'. $itemcurrentLine, $val['id']);	
				$objWorksheet->setCellValue('C'. $itemcurrentLine, $val['color']);	
				$objWorksheet->setCellValue('D'. $itemcurrentLine, $val['size']);	
				$objWorksheet->setCellValue('E'. $itemcurrentLine, $val['qty']);	
				if ($val['qty'] >= 2) {
					$objWorksheet->getStyle('E' . $itemcurrentLine)
								 ->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
					$objWorksheet->getStyle('E' . $itemcurrentLine)
								 ->getFill()->getStartColor()->setARGB('fcf40d');		
					$objWorksheet->getStyle('E' . $itemcurrentLine)->getFont()->getColor()->setARGB("d1000a");
					$objWorksheet->getStyle('E' . $itemcurrentLine)->getFont()->setBold(true);
				}
				foreach ($columns as $col) {
					$objWorksheet->getStyle($col . $itemcurrentLine)
						->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$objWorksheet->getStyle($col . $itemcurrentLine)
						->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				}
				$itemcurrentLine++;
			}
			$currentLine = $itemCount + $currentLine + 1;
			$objWorksheet->getRowDimension(''. $currentLine)->setRowHeight(120);
			$objWorksheet->setCellValue('A'. $currentLine, 'Shipping Address:');
			$objWorksheet->setCellValue('B'. $currentLine, $info['address']);
			if ($k % 2 == 1) {
				$range = 'A' . $currentLine . ':B' . $currentLine;
				$objWorksheet->getStyle($range)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
				$objWorksheet->getStyle($range)->getFill()->getStartColor()->setARGB('91b27e');		
			}
			$objWorksheet->getStyle('A' . $currentLine)->applyFromArray($styleThinBlackBorderOutline);
			$objWorksheet->getStyle('B' . $currentLine)->applyFromArray($styleThinBlackBorderOutline);
			$objWorksheet->getStyle('A' . $currentLine)->getFont()->setBold(true);
			$objWorksheet->getStyle('A' . $currentLine)->getFont()->setSize(12);
			$objWorksheet->getStyle('A' . $currentLine)
						 ->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objWorksheet->getStyle('A' . $currentLine)
						 ->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
			$objWorksheet->getStyle('B' . $currentLine)
						 ->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objWorksheet->getStyle('B' . $currentLine)
						 ->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
			$range = 'B' . $currentLine . ':E' . $currentLine;
			$objWorksheet->mergeCells($range);
			$currentLine += 2;
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
