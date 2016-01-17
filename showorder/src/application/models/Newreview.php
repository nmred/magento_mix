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
     * 添加属性  
     */
    protected static $addProperty = array(
        NMRED_NEWREVIEW => array(
            'username' => true,
            'context'  => true,
            'review_id'   => true,
            'product_id'  => true,       
            'entity_id'   => true,       
            'create_time' => true,       
        ),                      
        NMRED_NEWREVIEW_IMG => array(
            'review_id'=> true,
            'big_img'  => true,       
            'img'	   => true,       
        ),                      
    );

    /**
     * 修改属性  
     */
    protected static $modProperty = array(
        NMRED_NEWREVIEW => array(
            'username' => true,
            'context'  => true,
            'review_id'   => true,
            'product_id'  => true,       
            'entity_id'   => true,       
            'create_time' => true,       
        ),                      
        NMRED_NEWREVIEW_IMG => array(
            'review_id'=> true,
            'big_img'  => true,       
            'img'	   => true,       
        ),                      
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
    // {{{ public function showProduct()
    
    /**
     * 产品列表 
     * 
     * @access public
     * @return void
     */
    public function getProduct($condition)
    { 
        $table = new Db_Table('catalog_product_entity', $this->getDbAdapter());
		$select = new Zend\Db\Sql\Select();
		$select->from(array('e' => 'catalog_product_entity'))
			   ->join(array('v' => 'catalog_product_entity_varchar'), 'e.entity_id=v.entity_id', array('value'),
					  \Zend\Db\Sql\Select::JOIN_LEFT)
			   ->where(array('v.attribute_id' => 97));

		if (isset($condition['isCount']) && $condition['isCount']) {
			$select->columns(array('num' => new Zend\Db\Sql\Expression('count(*)')));
		} else {
			$select->columns($condition['columns'], true);
		}


		// 分页处理
		if (isset($condition['limit'])) {
			$select->limit((int)$condition['limit']);
		}
		if (isset($condition['offset'])) {
			$select->offset((int)$condition['offset']);
		}

		$result = $table->selectWith($select);
		if (!$result) {
			return array();
		}
		
		$list = $result->toArray();

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
    // {{{ public function addReview()
    
    /**
     * 添加评论 
     * 
     * @access public
     * @return void
     */
    public function addReview()
    { 
        $result = $this->table->insert($this->getProperty(NMRED_NEWREVIEW, true)); 
		$imgInfo = $this->getProperty(NMRED_NEWREVIEW_IMG, true);
		if (isset($imgInfo['img']) && !empty($imgInfo['img'])) {
			$table = new Db_Table(NMRED_NEWREVIEW_IMG, $this->getDbAdapter());
			foreach ($imgInfo['img'] as $info) {
				$data = array(
					'review_id' => $imgInfo['review_id'],
					'big_img'   => $info['big_img'],		
					'img'   => $info['img'],		
				);
				$table->insert($data); 
			}
		}

        return $result;
    }

    // }}}
    // {{{ public function delReviewImg()
    
    /**
     * 删除评论图片 
     * 
     * @access public
     * @return void
     */
    public function delReviewImg($ids)
    { 
		$table = new Db_Table(NMRED_NEWREVIEW_IMG, $this->getDbAdapter());
		$where = new \Zend\Db\Sql\Where();
		$where->in('review_id', $ids);
        $result = $table->delete($where); 
        return $result;
    }

    // }}}
    // {{{ public function modReview()
    
    /**
     * 修改评论 
     * 
     * @access public
     * @return void
     */
    public function modReview($id)
    { 
        //$this->checkProperty();
        $result = $this->table->update($this->getModProperty(NMRED_NEWREVIEW, true), array('review_id' => $id)); 
		$this->delReviewImg(array($id));
		$imgInfo = $this->getModProperty(NMRED_NEWREVIEW_IMG, true);
		if (isset($imgInfo['img']) && !empty($imgInfo['img'])) {
			$table = new Db_Table(NMRED_NEWREVIEW_IMG, $this->getDbAdapter());
			foreach ($imgInfo['img'] as $info) {
				$data = array(
					'review_id' => $imgInfo['review_id'],
					'big_img'   => $info['big_img'],		
					'img'   => $info['img'],		
				);
				$table->insert($data); 
			}
		}
        return $result;
    }

    // }}}
    // {{{ public function delReview()
    
    /**
     * 删除评论 
     * 
     * @access public
     * @return void
     */
    public function delReview($ids)
    { 
		$where = new \Zend\Db\Sql\Where();
		$where->in('review_id', $ids);
        $result = $this->table->delete($where); 
		$this->delReviewImg($ids);
        return $result;
    }

    // }}}
    // }}}
}
