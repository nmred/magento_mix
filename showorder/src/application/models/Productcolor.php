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
* 修改颜色 model 
+------------------------------------------------------------------------------
* 
* @uses BaseModel
* @package 
* @version $_SWANBR_VERSION_$
* @copyright Copyleft
* @author $_SWANBR_AUTHOR_$ 
+------------------------------------------------------------------------------
*/
class ProductcolorModel extends BaseModel
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
        $this->table = new Db_Table(NMRED_PRODUCT_COLOR, $this->getDbAdapter());
    }

    // }}}
    // {{{ public function infoColor()
    
    /**
     * 颜色详细信息 
     * 
     * @access public
     * @return void
     */
    public function infoColor($condition)
    { 
		$select = new Zend\Db\Sql\Select();
		$select->from(array('p' => NMRED_PRODUCT_COLOR));
		$select->columns($condition['columns'], true);
		$select->where(array('entity_id' => $condition['entity_id'], 'color_id' => $condition['color_id']));
		$result = $this->table->selectWith($select);
		
		if (!$result) {
			return array();
		}
		
		$list = $result->toArray();
		return $list;
    }

    // }}}
    // }}}
}
