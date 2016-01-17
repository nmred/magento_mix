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
	// {{{ public function addAction()
	
	/**
	 * 添加 
	 * 
	 * @access public
	 * @return void
	 */
	public function addAction()
    {
		$this->checkLogin();
		$this->getView()->assign('product_id', $this->getQuery('product_id'));
		$this->getView()->assign('entity_id', $this->getQuery('entity_id'));
		$this->getView()->display('newreview/add.phtml');
	}

	// }}}
	// {{{ public function doaddAction()
	
	/**
	 * 添加 
	 * 
	 * @access public
	 * @return void
	 */
	public function doaddAction()
    {
		$this->checkLogin();
        $data = array(
            'username' => $this->getPost('username'),
            'context' => strip_tags($this->getPost('context', null, false), '<a>'),
            'product_id' => $this->getPost('product_id'),
            'entity_id'  => $this->getPost('entity_id'),
        );

		$data['review_id'] = $data['entity_id'] . substr(time(), 4);
        $img  = $this->getPost('img');
		$imgs = explode('::', $img);
		$imgFromMats = array();
		foreach ($imgs as $val) {
			if (!trim($val)) {
				continue;
			}
			$tmp = parse_url($val);
			$name = basename($tmp['path']);
			$thumbnail_url = 'http://show.mixbridal.com/files/thumbnail/' . $name;
			$item['big_img'] = $val;	
			$item['img'] = $thumbnail_url;	
			$imgFromMats[] = $item;
		}
		$data['img'] = $imgFromMats;
		$data['create_time'] = time();
        // 验证参数
        try {
            $model = new NewreviewModel($data);
            $id = $model->addReview();
        } catch (Exception $e) {
            return $this->errorAjaxRender($e->getMessage(), 1000);
        }
        return $this->ajaxRender(array('success' => '添加成功'));
	}

	// }}}
	// {{{ public function modAction()
	
	/**
	 * 修改 
	 * 
	 * @access public
	 * @return void
	 */
	public function modAction()
    {
		$this->checkLogin();
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
		if (empty($result)) {
            return $this->errorAjaxRender('非法评论', 1000);
		}
		$info = $result[0];

		$imginfo = array();
		if (!empty($info)) {
			$imgs = $info['imgs'];	
			foreach ($imgs as $img) {
				if (!trim($img['big_img'])) {
					continue;	
				}
				$imginfo[] = $this->formatImg($img['big_img'], $img['img']);	
			}
			$imgData = array('files' => $imginfo);
		}
		$this->getView()->display('newreview/mod.phtml', array('imgData' => $imgData, 'info' => $info));
	}

	// }}}
	// {{{ public function domodAction()
	
	/**
	 * 添加 
	 * 
	 * @access public
	 * @return void
	 */
	public function domodAction()
    {
		$this->checkLogin();
        $data = array(
            'username' => $this->getPost('username'),
            'context' => strip_tags($this->getPost('context', null, false), '<a>'),
            'product_id' => $this->getPost('product_id'),
            'entity_id'  => $this->getPost('entity_id'),
            'review_id'  => $this->getPost('review_id'),
        );
		$review_id = $this->getPost('review_id');

        $img  = $this->getPost('img');
		$imgs = explode('::', $img);
		$imgFromMats = array();
		foreach ($imgs as $val) {
			list($img, $thumbnail_url) = explode('|', $val);
			$item['big_img'] = $img;	
			$item['img'] = $thumbnail_url;	
			$imgFromMats[] = $item;
		}
		$data['img'] = $imgFromMats;
		$data['create_time'] = time();
        // 验证参数
        try {
            $model = new NewreviewModel($data);
            $id = $model->modReview($review_id);
        } catch (Exception $e) {
            return $this->errorAjaxRender($e->getMessage(), 1000);
        }
        return $this->ajaxRender(array('success' => '修改成功'));
	}

	// }}}
	// {{{ public function uploadAction()
	
	/**
	 * 添加 
	 * 
	 * @access public
	 * @return void
	 */
	public function uploadAction()
    {
		$this->checkLogin();
		new UploadHandler();
	}

	// }}}
	// {{{ public function listAction()
	
	/**
	 * 商品列表 
	 * 
	 * @access public
	 * @return void
	 */
	public function listAction()
    {
		$this->checkLogin();
		$this->getView()->display('newreview/list.phtml');
	}

	// }}}
	// {{{ public function listAction()
	
	/**
	 * 评论列表 
	 * 
	 * @access public
	 * @return void
	 */
	public function reviewlistAction()
    {
		$this->checkLogin();
		$this->getView()->assign('product_id', $this->getQuery('product_id'));
		$this->getView()->assign('entity_id', $this->getQuery('entity_id'));
		$this->getView()->display('newreview/reviewlist.phtml');
	}

	// }}}
	// {{{ public function dolistAction()
	
	/**
	 * 列表 
	 * 
	 * @access public
	 * @return void
	 */
	public function dolistAction()
    {
		$this->checkLogin();
		$draw = $this->getPost('draw', 1);
		$start = $this->getPost('start', 0);
		$length = $this->getPost('length', 10);
        // 验证参数
		$total = 0;
        try {
            $model = new NewreviewModel();
            $count = $model->getProduct(array('isCount' => true));
			if (isset($count[0]['num']) && $count[0]['num']) {
				$total = $count[0]['num'];	
			}
			$result = $model->getProduct(array(
				'columns' => array('entity_id'),
				'limit' => $length,
				'offset' => $start
			));
			foreach ($result as $key => $val) {
				$splitProfuctInfo = explode('-', $val['value']);
				$productId = (int)end($splitProfuctInfo);
				if (!$productId) {
					unset($result[$key]);
				} else {
					$result[$key]['product_id'] = $productId;
				}
			}
			$result = array_values($result);
        } catch (Exception $e) {
            return $this->errorAjaxRender($e->getMessage(), 1000);
        }

		$data = array(
			'draw' => $draw,
			'recordsTotal' => $total,
			'recordsFiltered' => $total,
			'data' => $result,	
		);
        return $this->jsonRender($data);
	}

	// }}}
	// {{{ public function doreviewlistAction()
	
	/**
	 * 列表 
	 * 
	 * @access public
	 * @return void
	 */
	public function doreviewlistAction()
    {
		$this->checkLogin();
		$draw = $this->getPost('draw', 1);
		$start = $this->getPost('start', 0);
		$length = $this->getPost('length', 10);
		$productId = $this->getQuery('product_id');
        // 验证参数
		$total = 0;
        try {
            $model = new NewreviewModel();
            $count = $model->showReview(array('total' => true, 'product_id' => $productId));
			if (isset($count[0]['num']) && $count[0]['num']) {
				$total = $count[0]['num'];
			}
			$result = $model->showReview(array(
				'columns' => array('create_time', 'context', 'username', 'review_id', 'product_id', 'entity_id'),
				'limit'	  => $length,
				'offset'  => $start,
				'product_id' => $productId,
			));
			foreach ($result as $key => $val) {
				$result[$key]['create_time'] = date('Y-m-d H:i:s', $val['create_time']);
				$result[$key]['context'] = substr($val['context'], 0, 200);
			}
        } catch (Exception $e) {
            return $this->errorAjaxRender($e->getMessage(), 1000);
        }

		$data = array(
			'draw' => $draw,
			'recordsTotal' => $total,
			'recordsFiltered' => $total,
			'data' => $result,	
		);
        return $this->jsonRender($data);
	}

	// }}}
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
		$id   = $this->getQuery('id');
		$count = $this->getQuery('count', 3);
        try {
            $model = new NewreviewModel();
			$total = $model->showReview(array(
				'total' => true,
				'product_id' => $id,
			));
			if ($count > 0) {
				$result = $model->showReview(array(
					'columns' => array('entity_id', 'product_id', 'review_id', 'create_time', 'context', 'username'),
					'limit' => $count,
					'offset' => $page * $count,
					'product_id' => $id,
				));
			} else {
				$result = $model->showReview(array(
					'columns' => array('entity_id', 'product_id', 'review_id', 'create_time', 'context', 'username'),
					'product_id' => $id,
				));
			}
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
	// {{{ protected function formatImg()
	
	protected function formatImg($img, $thumbnail_url = null) {
		$data = parse_url($img);
		$name = basename($data['path']);
		if (is_null($thumbnail_url)) {
			$thumbnail_url = 'http://show.mixbridal.com/files/thumbnail/' . $name;
		}

		return array(
			'url' => $img,
			'thumbnail_url' => $thumbnail_url,
			'name' => $name,
		);
	}

	// }}}
	// {{{ public function dodelAction()
	
	/**
	 * 删除 
	 * 
	 * @access public
	 * @return void
	 */
	public function dodelAction()
    {
		$this->checkLogin();
		$ids = $this->getPost('ids');
		$ids = explode(',', $ids);
		if (empty($ids)) {
			return $this->errorAjaxRender('服务端参数非法', 1000);		
		}
        // 验证参数
        try {
            $model = new NewreviewModel();
            $model->delReview($ids);
        } catch (Exception $e) {
            return $this->errorAjaxRender($e->getMessage(), 1000);
        }
        return $this->ajaxRender(array('success' => '删除成功'));
	}

	// }}}
	// }}}
}
