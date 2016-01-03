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
class NewreviewController extends Controller_Base
{
	// {{{ functions
	// {{{ public function showAction()
	
	/**
	 * 添加 
	 * 
	 * @access public
	 * @return void
	 */
	public function showAction()
    {
		$page = $this->getQuery('page', 0);
		$id = $this->getQuery('id');
        try {
            $model = new NewreviewModel();
			$total = $model->showReview(array(
				'total' => true,
				'product_id' => $id,
			));
			$result = $model->showReview(array(
				'columns' => array('entity_id', 'product_id', 'review_id', 'create_time', 'context', 'username'),
				'limit' => 3,
				'offset' => $page * 3,
				'product_id' => $id,
			));
			$result['total'] = $total[0]['num'];
        } catch (Exception $e) {
            return $this->errorAjaxRender($e->getMessage(), 1000);
        }

        return $this->ajaxRender($result);
	}

	// }}}
	// {{{ public function infoAction()
	
	/**
	 * 添加 
	 * 
	 * @access public
	 * @return void
	 */
	public function infoAction()
    {
		$id = $this->getQuery('review_id');
        try {
            $model = new NewreviewModel();
			$result = $model->infoReview(array(
				'columns' => array('entity_id', 'product_id', 'review_id', 'create_time', 'context', 'username'),
				'review_id' => $id,
			));
        } catch (Exception $e) {
            return $this->errorAjaxRender($e->getMessage(), 1000);
        }

		if (!empty($result)) {
			$result = $result[0];	
			$imgs = $result['imgs'];
			foreach ($imgs as $key => $img) {
				if ($img['img'] == '' && $img['big_img'] == '') {
					unset($imgs[$key]);	
				}	
			}
			$result['imgs'] = $imgs;
		}

        return $this->ajaxRender($result);
	}

	// }}}
	// }}}
}
