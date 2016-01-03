<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 foldmethod=marker: */
 
/**
+------------------------------------------------------------------------------
* Bootstrap 
+------------------------------------------------------------------------------
* 
* @uses Yaf_Bootstrap_Abstract
* @package 
* @version $_SWANBR_VERSION_$
* @copyright Copyleft
* @author $_SWANBR_AUTHOR_$ 
+------------------------------------------------------------------------------
*/
class Bootstrap extends Yaf_Bootstrap_Abstract
{
    // {{{ functions
    // {{{ public function _initConfig()

    /**
     * 初始化配置 
     * 
     * @access public
     * @return void
     */
	public function _initConfig() {
		$config = Yaf_Application::app()->getConfig();
		Yaf_Registry::set("config", $config);
	}

    // }}}
    // {{{ public function _initConstant()

    /**
     * 初始化常量 
     * 
     * @access public
     * @return void
     */
    public function _initConstant() {
        define('NMRED_SHOWORDER', Yaf_Registry::get('config')->table->nmred_showorder);
        define('NMRED_NEWREVIEW', Yaf_Registry::get('config')->table->nmred_newreview);
        define('NMRED_NEWREVIEW_IMG', Yaf_Registry::get('config')->table->nmred_newreview_img);
    }

    // }}}
    // {{{ public function _initView()

    /**
     * 默认禁用模板 
     * 
     * @param Yaf_Dispatcher $dispatcher 
     * @access public
     * @return void
     */
	public function _initView(Yaf_Dispatcher $dispatcher) {
        $dispatcher->disableView();
	}

    // }}}
    // {{{ public function _initSeesion()

    /**
     * 初始化session 
     * 
     * @param Yaf_Dispatcher $dispatcher 
     * @access public
     * @return void
     */
	public function _initSeesion(Yaf_Dispatcher $dispatcher) {
		$session = \Yaf_Session::getInstance();
		$session->start();
	}

    // }}}
    // {{{ public function _initLoader()

    /**
     * 初始化自动加载器 
     * 
     * @param Yaf_Dispatcher $dispatcher 
     * @access public
     * @return void
     */
	public function _initLoader(Yaf_Dispatcher $dispatcher) {
        Yaf_Loader::import(Yaf_Registry::get('config')->application->vendor . '/autoload.php');
	}

    // }}}
    // }}}
}
