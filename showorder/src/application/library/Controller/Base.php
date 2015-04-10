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
* 控制器基类 
+------------------------------------------------------------------------------
* 
* @uses Yaf_Controller_Abstract
* @package 
* @version $_SWANBR_VERSION_$
* @copyright Copyleft
* @author $_SWANBR_AUTHOR_$ 
+------------------------------------------------------------------------------
*/
abstract class Controller_Base extends Yaf_Controller_Abstract
{
    // {{{ const
    // }}}
    // {{{ members
    // }}}
    // {{{ functions
    // {{{ public function jsonRender()

    /**
     * 返回 Json 字符串 
     * 
     * @access public
     * @return string
     */
    public function jsonRender($data) {
        header('Content-Type:application/json; charset=utf-8');
        echo json_encode($data);
    }

    // }}}
    // {{{ public function ajaxRender()

    /**
     * 带状态码的 Json 结构 
     * 
     * @param array $data 
     * @access public
     * @return string
     */
    public function ajaxRender($data, $status = null, $msg = null)
    {
        $data = array('status' => 0, 'data' => $data, 'msg' => $msg);
        if ($status) {
            $data['status'] = $status;
        }

        return $this->jsonRender($data);
    }

    // }}}
    // {{{ public function errorAjaxRender()

    /**
     * 错误时返回 Json 结构 
     * 
     * @param array $data 
     * @access public
     * @return string
     */
    public function errorAjaxRender($msg = null, $status = null)
    {
        $data = array('status' => 0, 'data' => array(), 'msg' => $msg);
        if ($status) {
            $data['status'] = $status;
        }

        return $this->jsonRender($data);
    }

    // }}}
    // {{{ public function getPost()

    /**
     * 获取 post 数据
     * @param      $name
     * @param null $if_not_exist
     * @param bool $security
     *
     * @return null|string
     */
    public function getPost($name, $if_not_exist=null, $security=true) {
        $string = isset($_POST[$name]) ? trim($_POST[$name]) : $if_not_exist;
        if ($security) {
            $string = htmlspecialchars($string, ENT_QUOTES);
        }
        return $string;
    }
    
    // }}}
    // {{{ public function getQuery()

    /**
     * 获取 get 数据
     * @param      $name
     * @param null $if_not_exist
     * @param bool $security
     *
     * @return null|string
     */
    public function getQuery($name, $if_not_exist=null, $security=true) {
        $string = isset($_GET[$name]) ? urldecode(trim($_GET[$name])) : $if_not_exist;
        if ($security) {
            $string = htmlspecialchars($string, ENT_QUOTES);
        }
        return $string;
    }
    
    // }}} 
    // {{{ public function getParam()

    /**
     * 获取 request 数据
     * @param      $name
     * @param null $if_not_exist
     * @param bool $security
     *
     * @return null|string
     */
    public function getParam($name, $if_not_exist=null, $security=true) {
        $pathinfo = $this->getRequest()->getParams();
        $data   = array_merge($pathinfo, $_REQUEST);
        $string = isset($data) ? trim($data[$name]) : $if_not_exist;
        if ($security) {
            $string = htmlspecialchars($string, ENT_QUOTES);
        }
        return $string;
    }

    // }}}
    // {{{ protected function getCurrentUrl()

    /**
     * 获取当前的URL 
     * 
     * @access protected
     * @return void
     */
    protected function getCurrentUrl()
    {
        if (!isset($_SERVER['SERVER_NAME'], $_SERVER['SERVER_PORT'], $_SERVER['REQUEST_URI'])) {
            return false;
        }

        $url = 'http';
        if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) === 'on') {
            $url .= 's';
        }
        $url .= '://';

        if ((int)$_SERVER['SERVER_PORT'] !== 80) {
            $url .= $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'];
        } else {
            $url .= $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
        }

        return $url;
    }

    // }}o}
	// }}}
	// {{{ public function checkLogin()

	/**
	 * checkLogin 
	 * 
	 * @access public
	 * @return void
	 */
	public function checkLogin() {
		$session = \Yaf_Session::getInstance();
		$name = $session->get("name");	
		$token = $session->get('usertoken');
		$time = substr($token, -10);
		$passToken = md5($name . $time) . $time;
		if ($passToken == $token) {
			return true;
		} else {
			$this->forward('index', 'login');	
		}
	}

	// }}}
    // }}}
}
