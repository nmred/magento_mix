<?php
require_once '../../showorder/src/vendor/autoload.php';

class ConfirmSize {
	// {{{ consts
	
	const ORDER_TYPE_PROCESSING = 'processing';
	const ORDER_TYPE_PAYMENT_REVIEW = 'payment_review';
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
	 * @var array
	 * @access protected
	 */
	protected $config = array();

	// }}}
	// {{{ functions
	// {{{ public function __construct()

	public function __construct() {
		$data = parse_ini_file('app.ini', true);
		$config = isset($data['confimSize']) ? $data['confimSize'] : array();
		if (empty($config)) {
			throw new \Exception("Not confimSize config.");
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
	protected function getOrderList($type = self::ORDER_TYPE_PROCESSING, $time = '') {
		$typeId = ($type == self::ORDER_TYPE_PROCESSING || $type == self::ORDER_TYPE_PAYMENT_REVIEW) ? 1 : 2;
		$sql = 'select order_id from send_confirm_sucess where type=' . $typeId;		
		$stmt = $this->connection->query($sql, PDO::FETCH_ASSOC);
		$sendAllOrderIds = array();
		if (!$stmt) {
			return array();
		}
		
		foreach ($stmt as $row) {
			$sendAllOrderIds[] = $row['order_id'];
		}

		$sql = 'select entity_id,customer_email,customer_firstname, increment_id'
			   . ' from sales_flat_order where status=\'' . $type . '\'';		
		if ($time) {
			$endTimeStr = date('Y-m-d H:i:s', $time);
			$sql .= ' and created_at < \'' . $endTimeStr . '\'';	
		}
		$stmt = $this->connection->query($sql, PDO::FETCH_ASSOC);
		if (!$stmt) {
			return array();	
		}
		
		$list = array();
		foreach ($stmt as $row) {
			if (in_array($row['entity_id'], $sendAllOrderIds)) {
				continue;
			}
			$list[] = $row;
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
		$sql = 'select product_id,sku,name '
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
		$payment_review = $this->getOrderList(self::ORDER_TYPE_PAYMENT_REVIEW);
		foreach ($payment_review as $val) {
			$processing[] = $val;		
		}
		foreach ($processing as $order) {
			$order['id'] = $order['increment_id'];
			$items = $this->getOrderItems($order['entity_id']);		
			if (empty($items)) {
				continue;	
			}
			
			$dressesCount = 0;
			$isLace = true;
			foreach ($items as $item) {
				$categroy = $this->getProductCategory($item['product_id']);	
				if (in_array(209, $categroy)) {
					continue;	
				}
				$dressesCates = array(221, 220, 219, 218, 208, 204, 203);
				$dresses = array_intersect($dressesCates, $categroy);
				$isDresses = false;
				if (!empty($dresses)) {
					$isDresses = true;	
					$dressesCount++;
				}

				if (false === strpos($item['name'], 'Lace')) {
					$isLace = false;	
				}
			}
			
			if ($dressesCount == 1) {
				if ($isLace) { // 裙子个数为1，并且商品名称包含 Lace
					$rev = $this->sendOneDressesAndLaceNotice($order);		
				} else {
					$rev = $this->sendOneDressesNotice($order);		
				}	
			} else if ($dressesCount >= 2){ // 裙子格式大于2
				$rev = $this->sendMoreDressesNotice($order);		
			}

			if ($rev) {
				$this->sendSuccess(1, $order);
			} else if ($dressesCount) {
				echo "Send Mail fail, " . var_export($order, true), PHP_EOL;
			}
		}
	}

	// }}}
	// {{{ protected function processPending()

	/**
	 * processPending 
	 * 
	 * @access protected
	 * @return void
	 */
	protected function processPending() {
		$pending = $this->getOrderList(self::ORDER_TYPE_PENDING, time() - 9 * 3600);
		foreach ($pending as $order) {
			$order['id'] = $order['increment_id'];
			$rev = $this->sendPending($order);
			if ($rev) {
				$this->sendSuccess(2, $order);
			} else {
				echo "Send Mail fail, " . var_export($order, true), PHP_EOL;
			}
		}
	}

	// }}}
	// {{{ protected function sendOneDressesAndLaceNotice()

	protected function sendOneDressesAndLaceNotice($orderInfo) {	
		$title = 'Urgent: Size confirmation request from Mix Bridal (' . $orderInfo['id'] . ')';
		$body = <<<EOT
Hi {$orderInfo['customer_firstname']},<br/>
<br/>
Thanks for your order with us, we've confirmed your payment, but before we start tailoring the dress for you, could you firstly confirm with us if you compared your body measurements with our size chart before deciding the size for your dress? As we are running US sizes for the current FHFH bridesmaid dresses we carry. Thanks!
<br/>
FHFH size chart:
<br/>
<br/>

http://www.mixbridal.com/how-to-measure-size-chart.html
<br/>

<br/>
If you chose custom size, please simply reply to us with the exact measurements for your dress, we will help add the measurements to your order for you!
<br/>
<br/>

Also, is this dress for one of your bridesmaids and if you like it, you will purchase more for the rest of your maids?
<br/>
<br/>

Your earliest reply is appreciated.
<br/>
<br/>
Best, <br/> Sally <br/>
Account Manager<br/>
Mix Bridal<br/>
EOT;

		return $this->sendMail($orderInfo['customer_email'], $title, $body);
	}

	// }}}
	// {{{ protected function sendOneDressesNotice()

	protected function sendOneDressesNotice($orderInfo) {	
		$title = 'Urgent: Size confirmation request from Mix Bridal (' . $orderInfo['id'] . ')';
		$body = <<<EOT
Hi {$orderInfo['customer_firstname']},<br/>
<br/>
Thanks for your order with us, we've confirmed your payment, but before we start tailoring the dress for you, could you firstly confirm with us if you compared your body measurements with our size chart before deciding the size for your dress? As we are running US sizes for the current FHFH bridesmaid dresses we carry. Thanks!<br/>
<br/>
FHFH size chart:
<br/>

http://www.mixbridal.com/how-to-measure-size-chart.html
<br/>
<br/>

If you chose custom size, please simply reply to us with the exact measurements for your dress, we will help add the measurements to your order for you!
<br/>
<br/>

Your earliest reply is appreciated.
<br/>
<br/>
Best,<br/>
Sally<br/>
Account Manager<br/>
Mix Bridal<br/>
EOT;

		return $this->sendMail($orderInfo['customer_email'], $title, $body);
	}

	// }}}
	// {{{ protected function sendMoreDressesNotice()

	protected function sendMoreDressesNotice($orderInfo) {	
		$title = 'Urgent: Size confirmation request from Mix Bridal (' . $orderInfo['id'] . ')';
		$body = <<<EOT
Hi {$orderInfo['customer_firstname']},<br/>
<br/>
Thanks for your order with us, we've confirmed your payment, but before we start tailoring the dresses for you, could you firstly confirm with us if your bridesmaids compared your body measurements with our size chart before deciding their sizes? As we are running US sizes for the current FHFH bridesmaid dresses we carry. Thanks!<br/>
<br/>
FHFH Size Chart:
<br/>

http://www.mixbridal.com/how-to-measure-size-chart.html
<br/>
<br/>

If you chose custom size for all or some of your dresses, please simply reply to us with the exact measurements for each dress, we will help add the measurements to your order for you!
<br/>
<br/>

Your earliest reply is appreciated.
<br/>
<br/>
Best,<br/>
Sally<br/>
Account Manager<br/>
Mix Bridal<br/>
EOT;

		return $this->sendMail($orderInfo['customer_email'], $title, $body);
	}

	// }}}
	// {{{ protected function sendPending()

	protected function sendPending($orderInfo) {	
		$title = 'Important: About your pending order with Mix Bridal (' . $orderInfo['id'] . ')';
		$body = <<<EOT
Hi {$orderInfo['customer_firstname']},<br/>
<br/>
Thanks for your recent order with Mix Bridal! We noticed that your order is pending, which means the payment was not successfully completed. Did you have any difficulty when doing the payment? Is there anything we can help you please?<br/>
<br/>
Look forward to your reply.<br/>
<br/>
<br/>
Best,<br/>
Sally<br/>
Account Manager<br/>
Mix Bridal<br/>
EOT;
		return $this->sendMail($orderInfo['customer_email'], $title, $body);
	}

	// }}}
	// {{{ protected function sendSuccess()

	protected function sendSuccess($typeId, $orderInfo) {	
		$sql = 'insert into send_confirm_sucess (order_id, type) VALUES(' . $orderInfo['entity_id'] . ', ' . $typeId. ')';	
		$affected = $this->connection->exec($sql);
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
	protected function sendMail($email, $title, $body) {
		if (!$email) {
			return false;
		}
		$mail = new PHPMailer;
		$mail->isSMTP();                                      // Set mailer to use SMTP
		$mail->Host = $this->config['smtp_host'];  // Specify main and backup SMTP servers
		$mail->SMTPAuth = true;                               // Enable SMTP authentication
		$mail->Username = $this->config['smtp_user'];                 // SMTP username
		$mail->Password = $this->config['smtp_pass'];                           // SMTP password
		$mail->Port = 25;                                    // TCP port to connect to

		$mail->setFrom('service@mixbridal.com', 'Mix Bridal');
		$mail->addAddress($email);               // Name is optional
		$mail->addAddress('service@mixbridal.com');               // Name is optional

		$mail->isHTML(true);                                  // Set email format to HTML

		$mail->Subject = $title;
		$mail->Body    = $body;
		$mail->AltBody = $body;

		return $mail->send();
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
		$this->processPending();
	}

	// }}}
	// }}}	
}

$confirm = new ConfirmSize();
$confirm->run();
