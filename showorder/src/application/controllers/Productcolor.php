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
//
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
 
/**
+------------------------------------------------------------------------------
* 展示数据 
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
class ProductcolorController extends Controller_Base
{
	// {{{ functions
	// {{{ public function infoAction()
	
	/**
	 * 添加 
	 * 
	 * @access public
	 * @return void
	 */
	public function infoAction()
    {
		$id = $this->getQuery('id');
		$colorId = $this->getQuery('color_id');
		$result  = array();
        try {
            $model = new ProductcolorModel();
			$result = $model->infoColor(array(
				'columns' => array('entity_id', 'color_id', 'image_id', 'url'),
				'entity_id' => $id,
				'color_id'  => $colorId,
			));
        } catch (Exception $e) {
            return $this->errorAjaxRender($e->getMessage(), 1000);
        }

        return $this->ajaxRender($result);
	}

	// }}}
	// }}}
}
