<?php
require_once '../../showorder/src/vendor/autoload.php';

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
	// {{{ protected function getReviews()

	/**
	 * getReviews 
	 * 
	 * @access protected
	 * @return void
	 */
	protected function getReviews() {
		$sql = 'select * from syncreview_entity';
		$stmt = $this->connection->query($sql, PDO::FETCH_ASSOC);
		if (!$stmt) {
			return array();
		}
		
		$reviews = array();
		foreach ($stmt as $row) {
			$item['review_id']  = $row['review_id'];
			$item['context'] = $row['context'];
			$finds = array(
				'for her and for him',
				'for him and for her',
				'for her & for him'	
			);
			$isfind = false;
			foreach ($finds as $find) {
				if (false !== strpos($item['context'], $find)) {
					$isfind = true;
				}
			}
			if (!$isfind) {
				continue;
			}
			$item['context'] = str_replace($finds, 'FHFH', $item['context']);
			$reviews[] = $item;
		}

		return $reviews;
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
		$reviews = $this->getReviews();
		foreach ($reviews as $review) {
			$this->updateData($review);
		}
	}

	// }}}
	// {{{ protected function updateData()
		
	/**
	 * updateData 
	 * 
	 * @param mixed $lists 
	 * @access protected
	 * @return void
	 */
	protected function updateData($data) {
		$sql = 'update syncreview_entity set context=\'' . $data['context']. '\' where review_id=' . $data['review_id'];
		var_dump($sql);
		$stmt = $this->connection->exec($sql);
		//if (!$stmt) {
		//	return array();
		//}
	}

	// }}}
	// }}}
}

$sync = new SyncReview();
$sync->run();
