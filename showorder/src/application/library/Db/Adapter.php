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

use Zend\Db\ResultSet;

/**
+------------------------------------------------------------------------------
* 扩展 ZF2 DB 支持读写分离 
+------------------------------------------------------------------------------
* 
* @abstract
* @package 
* @version $_SWANBR_VERSION_$
* @copyright Copyleft
* @author $_SWANBR_AUTHOR_$ 
+------------------------------------------------------------------------------
*/
class Db_Adapter extends Zend\Db\Adapter\Adapter 
{
    // {{{ const

    const DB_RW   = 1;
    const DB_MURW = 2;

    const SQL_WRITE = 1;
    const SQL_READ  = 2;

    // }}}
    // {{{ members

    /**
     * 写库 driver 
     */
    protected static $wDriver = null;

    /**
     * 读库 driver 
     */
    protected static $rDriver = null;

    /**
     *  数据库读写类型 
     */
    protected static $model = self::DB_RW; 

    /**
     * sql 语句类型 
     * 
     * @var mixed
     * @access protected
     */
    protected $currentSql = self::SQL_WRITE; 

    // }}}
    // {{{ functions
    // {{{ public function __construct()
    
    /**
     * __construct 
     * 
     * @access public
     * @return void
     */
    public function __construct($driverOptions, Platform\PlatformInterface $platform = null, ResultSet\ResultSetInterface $queryResultPrototype = null, Profiler\ProfilerInterface $profiler = null)
    {
        if (isset($driverOptions['read']) && is_array($driverOptions['read'])) { // 读写分离
            self::$model   = self::DB_MURW; 
            self::$wDriver = $this->createDriver($driverOptions['write']);
            self::$rDriver = $this->createDriver($driverOptions['read']);

            // 默认将 driver 指向写库
            parent::__construct(self::$wDriver);
        } else {
            parent::__construct($driverOptions);  
        }
    }

    // }}}
    // {{{ public function setSqlModel()

    /**
     * 设置 SQL 模式 
     * 
     * @param mixed $model 
     * @access public
     * @return void
     */
    public function setSqlModel($model = self::SQL_WRITE)
    {
        if (self::$model == self::DB_RW) { // 如果不是读写分离 不更改 driver
            return;
        }

        if ($model == self::SQL_WRITE) {
            if ($this->currentSql == self::SQL_READ) {
                self::$rDriver = $this->driver;
            }
            $this->driver = self::$wDriver;
        } else {
            if ($this->currentSql == self::SQL_WRITE) {
                self::$wDriver = $this->driver;
            }
            $this->driver = self::$rDriver;
        }

        $this->currentSql = $model;
    }

    // }}}
    // {{{ public function beginTransaction()
    
    /**
     * 开始事务，如果是读写分离，操作的是主库 
     * 
     * @access public
     * @return void
     */
    public function beginTransaction()
    {
        $this->setSqlModel(self::SQL_WRITE);
        $conn = $this->getDriver()->getConnection();
        $conn->beginTransaction();
    }

    // }}} 
    // {{{ public function commit()
    
    /**
     * 提交事务，如果是读写分离，操作的是主库 
     * 
     * @access public
     * @return void
     */
    public function commit()
    {
        $this->setSqlModel(self::SQL_WRITE);
        $conn = $this->getDriver()->getConnection();
        $conn->commit();
    }

    // }}} 
    // {{{ public function rollback()
    
    /**
     * 回滚事务，如果是读写分离，操作的是主库 
     * 
     * @access public
     * @return void
     */
    public function rollback()
    {
        $this->setSqlModel(self::SQL_WRITE);
        $conn = $this->getDriver()->getConnection();
        $conn->rollback();
    }

    // }}} 
    // }}}
}
