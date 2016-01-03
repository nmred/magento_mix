<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 foldmethod=marker: */
// +---------------------------------------------------------------------------
// | SWAN [ $_SWANBR_SLOGAN_$ ]
// +---------------------------------------------------------------------------
// | Copyright $_SWANBR_COPYRIGHT_$
// +---------------------------------------------------------------------------
// | Version  $_SWANBR_VERSION_$
// +---------------------------------------------------------------------------
// | Licensed ( $_SWANBR_LICENSED_URL_$ )
// +---------------------------------------------------------------------------
// | $_SWANBR_WEB_DOMAIN_$
// +---------------------------------------------------------------------------
 
/**
+------------------------------------------------------------------------------
* 晒单 model 
+------------------------------------------------------------------------------
* 
* @uses BaseModel
* @package 
* @version $_SWANBR_VERSION_$
* @copyright Copyleft
* @author $_SWANBR_AUTHOR_$ 
+------------------------------------------------------------------------------
*/
class NewreviewModel extends BaseModel
{
    // {{{ members

    /**
     * entity_id 
     * 
     * @var mixed
     * @access protected
     */
    protected $entity_id = null;

    /**
     * product_id 
     * 
     * @var mixed
     * @access protected
     */
    protected $product_id = null;

    /**
     * review_id 
     * 
     * @var mixed
     * @access protected
     */
    protected $review_id = null;

    /**
     * create_time 
     * 
     * @var mixed
     * @access protected
     */
    protected $create_time = null;

    /**
     * context 
     * 
     * @var mixed
     * @access protected
     */
    protected $context = null;

    /**
     * 数据表对应列 
     */
    protected static $initProperty = array(
        'entity_id'   => true,
        'product_id'  => true,
        'review_id'   => true,
        'create_time' => true,       
        'context'     => true,       
    );

    /**
     * 数据表对象 
     * 
     * @var mixed
     * @access protected
     */
    protected $table = null;

    // }}}
    // {{{ functions
    // {{{ public function __construct()

    /**
     * __construct 
     * 
     * @access public
     * @return void
     */
    public function __construct($property = array())
    {
        parent::__construct($property); 
        $this->table = new Db_Table(NMRED_NEWREVIEW, $this->getDbAdapter());
    }

    // }}}
    // {{{ public function showReview()
    
    /**
     * 添加晒单 
     * 
     * @access public
     * @return void
     */
    public function showReview($condition)
    { 
		$select = new Zend\Db\Sql\Select();
		$select->from(array('p' => NMRED_NEWREVIEW));
		if (isset($condition['total']) && $condition['total']) {
			$select->columns(array('num' => new Zend\Db\Sql\Expression('count(*)')));
		} else {
			$select->columns($condition['columns'], true);
		}
		$select->where(array('product_id' => $condition['product_id']));


		// 分页处理
		if (isset($condition['limit'])) {
			$select->limit((int)$condition['limit']);
		}
		if (isset($condition['offset'])) {
			$select->offset((int)$condition['offset']);
		}

		$result = $this->table->selectWith($select);
		if (!$result) {
			return array();
		}
		
		$list = $result->toArray();
		if (isset($condition['total']) && $condition['total']) {
			return $list;
		}
        $imgtable = new Db_Table(NMRED_NEWREVIEW_IMG, $this->getDbAdapter());
		foreach ($list as $key => $item) {
			$select = new Zend\Db\Sql\Select();
			$select->from(array('p' => NMRED_NEWREVIEW_IMG));
			$select->columns(array('big_img', 'img', 'review_id'), true);
			$select->where(array('review_id' => $item['review_id']));
			$imgs = $imgtable->selectWith($select);
			$list[$key]['imgs'] = $imgs->toArray();	
		}

		return $list;
    }

    // }}}
    // {{{ public function infoReview()
    
    /**
     * 评论详细信息 
     * 
     * @access public
     * @return void
     */
    public function infoReview($condition)
    { 
		$select = new Zend\Db\Sql\Select();
		$select->from(array('p' => NMRED_NEWREVIEW));
		$select->columns($condition['columns'], true);
		$select->where(array('review_id' => $condition['review_id']));
		$result = $this->table->selectWith($select);
		
		if (!$result) {
			return array();
		}
		
		$list = $result->toArray();
        $imgtable = new Db_Table(NMRED_NEWREVIEW_IMG, $this->getDbAdapter());
		foreach ($list as $key => $item) {
			$select = new Zend\Db\Sql\Select();
			$select->from(array('p' => NMRED_NEWREVIEW_IMG));
			$select->columns(array('big_img', 'img', 'review_id'), true);
			$select->where(array('review_id' => $item['review_id']));
			$imgs = $imgtable->selectWith($select);
			$list[$key]['imgs'] = $imgs->toArray();	
		}

		return $list;
    }

    // }}}
    // }}}
}
