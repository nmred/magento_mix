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
* Index 
+------------------------------------------------------------------------------
* 
* @uses Yaf
* @uses _Controller_Abstract
* @package 
* @version $_SWANBR_VERSION_$
* @copyright Copyleft
* @author $_SWANBR_AUTHOR_$ 
+------------------------------------------------------------------------------
*/
class IndexController extends Controller_Base
{
	// {{{ functions
	// {{{ public function indexAction()
	
	/**
	 * 管理中心 
	 * 
	 * @access public
	 * @return void
	 */
	public function indexAction()
    {
		$this->checkLogin();
		$this->getView()->display('index/base.phtml');
	}

	// }}}
	// {{{ public function startAction()
	
	/**
	 * 管理中心 
	 * 
	 * @access public
	 * @return void
	 */
	public function startAction()
    {
		$this->checkLogin();
		$this->getView()->display('index/start.phtml');
	}

	// }}}
	// {{{ public function loginAction()
	
	/**
	 * 登录页面 
	 * 
	 * @access public
	 * @return void
	 */
	public function loginAction()
    {
		$this->getView()->display('index/login.phtml');
	}

	// }}}
	// {{{ public function dologinAction()
	
	/**
	 * 登录页面 
	 * 
	 * @access public
	 * @return void
	 */
	public function dologinAction()
    {
		$name = $this->getPost("username");
		$password = $this->getPost("password");
		$model = new UserModel();
		$info = $model->getUser($name);
		if (empty($info)) {
			$this->forward('login');
		}
		$prefix = substr($info['password'], -2);
		$checkPass = md5($prefix . $password) . ':' . $prefix;
		if ($checkPass == $info['password']) {
			$session = \Yaf_Session::getInstance();
			$session->set("usertoken", md5($name . time()) . time());
			$session->set("name", $name);
		} else {
			$this->forward('login');
		}
	}

	// }}}
	// }}}
}
