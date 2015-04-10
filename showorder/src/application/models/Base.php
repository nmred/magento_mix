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
* 数据模型基类 
+------------------------------------------------------------------------------
* 
* @abstract
* @package 
* @version $_SWANBR_VERSION_$
* @copyright Copyleft
* @author $_SWANBR_AUTHOR_$ 
+------------------------------------------------------------------------------
*/
abstract class BaseModel
{
    // {{{ members

    /**
     * 数据库适配器对象 
     */
    protected static $dbAdapter = null;

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
        if (!empty($property)) {
            foreach ($property as $key => $value) {
                if (!in_array($key, static::$initProperty)) {
                    continue;
                }

                if ($value) {
                    $this->{$key} = trim($value);
                }
            }
        }
    }

    // }}}
    // {{{ public function getDbAdapter()

    /**
     * getDbAdapter 
     * 
     * @access public
     * @return void
     */
    public function getDbAdapter()
    {
        if (is_null(self::$dbAdapter)) {
            $dbModel  = Yaf_Registry::get('config')->db->model;
            if ($dbModel) { // 读写分离模式
                $dbConfig = Yaf_Registry::get('config')->mutildb->toArray();
            } else {
                $dbConfig = Yaf_Registry::get('config')->db->toArray();
                unset($dbConfig['model']); 
            }
            self::$dbAdapter = new Db_Adapter($dbConfig); 
        } 

        return self::$dbAdapter;
    }

    // }}}
    // {{{ public function getProperty()
    
    /**
     * 将所有字段转化为数组 
     * 
     * @access public
     * @return void
     */
    public function getProperty($tableName, $isBlank = false)
    {
        $data = array();
        $propertyKey = isset(static::$addProperty[$tableName]) ? static::$addProperty[$tableName] : array();
        foreach ($propertyKey as $key => $isAllow) {
            if ($isAllow && !is_null($this->{$key})
                && (!$isBlank || $this->{$key} != '')) {
                $data[$key] = $this->{$key}; 
            } 
        } 

        return $data;
    }

    // }}}
    // {{{ public function getModProperty()
    
    /**
     * 将所有字段转化为数组 
     * 
     * @access public
     * @return void
     */
    public function getModProperty($tableName, $isBlank = false)
    {
        $data = array();
        $propertyKey = isset(static::$modProperty[$tableName]) ? static::$modProperty[$tableName] : array();
        foreach ($propertyKey as $key => $isAllow) {
            if ($isAllow && !is_null($this->{$key})
                && (!$isBlank || $this->{$key} != '')) {
                $data[$key] = $this->{$key}; 
            } 
        } 

        return $data;
    }

    // }}}
    // {{{ public function getPropertyByOptions()
    
    /**
     * 将所有字段转化为数组 
     * 
     * @access public
     * @return void
     */
    public function getPropertyByOptions($options, $tableName, $isBlank = false)
    {
        $data = array();
        $propertyKey = isset($options[$tableName]) ? $options[$tableName] : array();
        foreach ($propertyKey as $key => $isAllow) {
            if ($isAllow && !is_null($this->{$key})
                && (!$isBlank || $this->{$key} != '')) {
                $data[$key] = $this->{$key}; 
            } 
        } 

        return $data;
    }

    // }}}
    // }}}
}
