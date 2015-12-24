<?php
require_once '../../showorder/src/vendor/autoload.php';

class ConfirmSize {
	// {{{ consts
	
	const DB_NAME = 'magento';

	const DB_HOST = '172.16.197.128';

	const DB_PORT = 3306;

	const DB_USER = 'nmred';

	const DB_PASS = '123456';

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
	 * sendAllOrderIds 
	 * 
	 * @var array
	 * @access protected
	 */
	protected $sendAllOrderIds = array();

	// }}}
	// {{{ functions
	// {{{ public function __construct()

	public function __construct() {
		$dsn = 'mysql:dbname=%s;host=%s;port=%s';
		$dsn = sprintf($dsn, self::DB_NAME, self::DB_HOST, self::DB_PORT);

		try {
			$this->connection = new PDO($dsn, self::DB_USER, self::DB_PASS);
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
		if (empty($this->sendAllOrderIds)) {
			$sql = 'select order_id from send_confirm_sucess';		
			$stmt = $this->connection->query($sql, PDO::FETCH_ASSOC);
			if (!$stmt) {
				$this->sendAllOrderIds = array();	
			}
			
			foreach ($stmt as $row) {
				$this->sendAllOrderIds[] = $row['order_id'];
			}
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
			if (in_array($row['entity_id'], $this->sendAllOrderIds)) {
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
				$dressesCates = array(223, 222, 221, 220, 219, 218, 216, 208, 204, 203);
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
				$this->sendSuccess($order);
			} else {
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
		$pending = $this->getOrderList(self::ORDER_TYPE_PENDING, time() - 3600);
		foreach ($pending as $order) {
			$order['id'] = $order['increment_id'];
			$rev = $this->sendPending($order);
			if ($rev) {
				$this->sendSuccess($order);
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
Thanks for your order with us, we've confirmed your payment, but before we start tailoring the dress for you, could you firstly confirm with us if you compared your body measurements with our size chart before deciding the size for your dress? As we are running US sizes for the current FHFH bridesmaid dresses we carry. Thanks!<br/>
<br/>
FHFH size chart:<br/>
<br/>
http://www.mixbridal.com/how-to-measure-size-chart.html<br/>
<br/>
Also, is this dress for one of your bridesmaids and if you like it, you will purchase more for the rest of your maids?<br/>
<br/>
Your earliest reply is appreciated.<br/>
<br/>
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
FHFH size chart:<br/>
<br/>
http://www.mixbridal.com/how-to-measure-size-chart.html<br/>
<br/>
Your earliest reply is appreciated.<br/>
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
Thanks for your order with us, we've confirmed your payment, but before we start tailoring the dresses for you, could you firstly confirm with us if you guys compared your body measurements with our size chart before deciding the sizes for your dresses? As we are running US sizes for the current FHFH bridesmaid dresses we carry. Thanks!<br/>
<br/>
FHFH Size Chart:<br/>
<br/>
http://www.mixbridal.com/how-to-measure-size-chart.html<br/>
<br/>
BTW, are all the dresses for you and you plan to keep the one that you like the best?<br/>
<br/>
Your earliest reply is appreciated.<br/>
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
Thanks for your recent order with Mix Bridal! We noticed that your recent bridesmaid dress order is pending, which means the payment was not successfully completed. Did you have any difficulty when doing the payment? Is there anything we can help you please?<br/>
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

	protected function sendSuccess($orderInfo) {	
		$sql = 'insert into send_confirm_sucess (order_id) VALUES(' . $orderInfo['entity_id'] . ')';	
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
		$email = 'service@mixbridal.com';	
		if (!$email) {
			return false;
		}
		$mail = new PHPMailer;
		$mail->isSMTP();                                      // Set mailer to use SMTP
		$mail->Host = '';  // Specify main and backup SMTP servers
		$mail->SMTPAuth = true;                               // Enable SMTP authentication
		$mail->Username = '';                 // SMTP username
		$mail->Password = '';                           // SMTP password
		$mail->Port = 25;                                    // TCP port to connect to

		$mail->setFrom('nmred_2008@126.com', 'Mailer');
		$mail->addAddress($email);               // Name is optional
		$mail->addAddress('nmred_2008@126.com');               // Name is optional

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
