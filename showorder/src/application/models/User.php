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
* 用户 model 
+------------------------------------------------------------------------------
* 
* @uses BaseModel
* @package 
* @version $_SWANBR_VERSION_$
* @copyright Copyleft
* @author $_SWANBR_AUTHOR_$ 
+------------------------------------------------------------------------------
*/
class UserModel extends BaseModel
{
    // {{{ members

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
        $this->table = new Db_Table('admin_user', $this->getDbAdapter());
    }

    // }}}
    // {{{ public function getUser()
    
    /**
     * 获取用户 
     * 
     * @access public
     * @return void
     */
    public function getUser($name)
    { 
		$select = new Zend\Db\Sql\Select();
		$select->from(array('p' => 'admin_user'));
		$select->where->equalTo('p.username', $name);
		$result = $this->table->selectWith($select)->current();
		if (!$result) {
			return array();
		}

		$result = $result->getArrayCopy();
		return $result;
    }

    // }}}
    // }}}
}
