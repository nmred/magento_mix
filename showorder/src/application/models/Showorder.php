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
class ShoworderModel extends BaseModel
{
    // {{{ members

    /**
     * id 
     * 
     * @var mixed
     * @access protected
     */
    protected $id = null;

    /**
     * 描述信息 
     * 
     * @var string
     * @access protected
     */
    protected $comment = ''; 

    /**
     * 图片 
     * 
     * @var string
     * @access protected
     */
    protected $img = '';

    /**
     * 关联产品ID 
     * 
     * @var string
     * @access protected
     */
    protected $product_ids = '';

    /**
     * 数据表对应列 
     */
    protected static $initProperty = array(
        'id'   => true,
        'img' => true,
        'comment'   => true,
        'product_ids' => true,       
        'title' => true,       
        'refer_url' => true,       
        'enable' => true,       
    );

    /**
     * 添加属性  
     */
    protected static $addProperty = array(
        NMRED_SHOWORDER => array(
            'title' => true,
            'comment' => true,
            'img' => true,
            'refer_url'   => true,       
            'product_ids' => true,       
            'enable' => true,       
        ),                                      
    );

    /**
     * 修改的属性  
     */
    protected static $modProperty = array(
        NMRED_SHOWORDER => array(
            'title' => true,
            'comment' => true,
            'img' => true,
            'refer_url'   => true,       
            'product_ids' => true,       
            'enable' => true,       
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
        $this->table = new Db_Table(NMRED_SHOWORDER, $this->getDbAdapter());
    }

    // }}}
    // {{{ public function isExist()

    /**
     * 判断该用户的基本信息是否存在 
     * 
     * @access public
     * @return boolean
     */
    public function isExist()
    { 
        $info = $this->getCustomerInfo();
        if ($info && $info->customer_id == $this->customer_id) {
            return true;
        }
        return false;
    }

    // }}}
    // {{{ public function addShoworder()
    
    /**
     * 添加晒单 
     * 
     * @access public
     * @return void
     */
    public function addShoworder()
    { 
        //$this->checkProperty();
        $result = $this->table->insert($this->getProperty(NMRED_SHOWORDER, true)); 
        return $result;
    }

    // }}}
    // {{{ public function getShoworder()
    
    /**
     * 添加晒单 
     * 
     * @access public
     * @return void
     */
    public function getShoworder($condition)
    { 
		$select = new Zend\Db\Sql\Select();
		$select->from(array('p' => NMRED_SHOWORDER));
		if ($condition['isCount']) {
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

		$result = $this->table->selectWith($select);
		if (!$result) {
			return array();
		}

		return $result->toArray();
    }

    // }}}
    // {{{ public function delShoworder()
    
    /**
     * 添加晒单 
     * 
     * @access public
     * @return void
     */
    public function delShoworder($ids)
    { 
		$where = new \Zend\Db\Sql\Where();
		$where->in('id', $ids);
        $result = $this->table->delete($where); 
        return $result;
    }

    // }}}
    // {{{ public function showShoworder()
    
    /**
     * 添加晒单 
     * 
     * @access public
     * @return void
     */
    public function showShoworder($condition)
    { 
		$select = new Zend\Db\Sql\Select();
		$select->from(array('p' => NMRED_SHOWORDER));
		$select->columns($condition['columns'], true);
		$select->where(array('enable' => 1));


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

		return $result->toArray();
    }

    // }}}
    // {{{ public function infoShoworder()
    
    /**
     * 添加晒单 
     * 
     * @access public
     * @return void
     */
    public function infoShoworder($id)
    { 
		$select = new Zend\Db\Sql\Select();
		$select->from(array('p' => NMRED_SHOWORDER));
		$select->where->equalTo('p.id', $id);
		$result = $this->table->selectWith($select)->current();
		if (!$result) {
			return array();
		}

		$result = $result->getArrayCopy();
		return $result;
    }

    // }}}
    // {{{ public function modShoworder()
    
    /**
     * 添加晒单 
     * 
     * @access public
     * @return void
     */
    public function modShoworder($id)
    { 
        //$this->checkProperty();
        $result = $this->table->update($this->getModProperty(NMRED_SHOWORDER, true), array('id' => $id)); 
        return $result;
    }

    // }}}
    // }}}
}
