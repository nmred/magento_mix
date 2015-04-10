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

use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Update;
use Zend\Db\Sql\Where;

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
class Db_Table extends Zend\Db\TableGateway\TableGateway 
{
    // {{{ const
    // }}}
    // {{{ members
    // }}}
    // {{{ functions
    // {{{ protected function executeSelect()

    /**
     * @param Select $select
     * @return ResultSet
     * @throws Exception\RuntimeException
     */
    protected function executeSelect(Select $select)
    {
        $this->adapter->setSqlModel(Db_Adapter::SQL_READ);
        return parent::executeSelect($select); 
    }

    // }}}
    // {{{ protected function executeDelete()

    /**
     * @todo add $columns support
     *
     * @param Delete $delete
     * @return mixed
     * @throws Exception\RuntimeException
     */
    protected function executeDelete(Delete $delete)
    {
        $this->adapter->setSqlModel(Db_Adapter::SQL_WRITE);
        return parent::executeDelete($delete); 
    }

    // }}}
    // {{{ protected function executeInsert()

    /**
     * @todo add $columns support
     *
     * @param Insert $insert
     * @return mixed
     * @throws Exception\RuntimeException
     */
    protected function executeInsert(Insert $insert)
    {
        $this->adapter->setSqlModel(Db_Adapter::SQL_WRITE);
        return parent::executeInsert($insert); 
    }

    // }}}
    // {{{ protected function executeUpdate()

    /**
     * @todo add $columns support
     *
     * @param Update $update
     * @return mixed
     * @throws Exception\RuntimeException
     */
    protected function executeUpdate(Update $update)
    {
        $this->adapter->setSqlModel(Db_Adapter::SQL_WRITE);
        return parent::executeUpdate($update); 
    }

    // }}}
    // }}}
}
