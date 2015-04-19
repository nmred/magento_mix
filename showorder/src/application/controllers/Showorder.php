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
class ShoworderController extends Controller_Base
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
		$this->getView()->display('showorder/add.phtml');
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
            'title' => $this->getPost('title'),
            'img' => $this->getPost('img'),
            'comment' => strip_tags($this->getPost('description', null, false), '<a>'),
            'product_ids'  => $this->getPost('productIds'),
            'enable' => ($this->getPost('status') == 'on') ? 1 : 0,
            'refer_url' => $this->getPost('refer_url'),
        );

        // 验证参数
        try {
            $model = new ShoworderModel($data);
            $id = $model->addShoworder();
        } catch (Exception $e) {
            return $this->errorAjaxRender($e->getMessage(), 1000);
        }
        return $this->ajaxRender(array('success' => '添加成功'));
	}

	// }}}
	// {{{ public function listAction()
	
	/**
	 * 列表 
	 * 
	 * @access public
	 * @return void
	 */
	public function listAction()
    {
		$this->checkLogin();
		$this->getView()->display('showorder/list.phtml');
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
            $model = new ShoworderModel();
            $count = $model->getShoworder(array('isCount' => true));
			if (isset($count[0]['num']) && $count[0]['num']) {
				$total = $count[0]['num'];	
			}
			$result = $model->getShoworder(array(
				'columns' => array('id', 'title', 'comment'),
				'limit' => $length,
				'offset' => $start
			));
			if (!empty($result)) {
				foreach ($result as $key => $value) {
					$result[$key]['comment'] = strip_tags($value['comment']);	
				}	
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
            $model = new ShoworderModel();
            $model->delShoworder($ids);
        } catch (Exception $e) {
            return $this->errorAjaxRender($e->getMessage(), 1000);
        }
        return $this->ajaxRender(array('success' => '删除成功'));
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
        try {
            $model = new ShoworderModel();
			$result = $model->showShoworder(array(
				'columns' => array('id', 'title', 'comment', 'img'),
				'limit' => 40,
				'offset' => 0
			));
        } catch (Exception $e) {
            return $this->errorAjaxRender($e->getMessage(), 1000);
        }

		if (!empty($result)) {
			foreach ($result as $key => $val) {
				$imgs = explode('::', $val['img']);
				$result[$key]['img'] = current($imgs);
			}	
		}

        return $this->ajaxRender($result);
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
		$model = new ShoworderModel();
		$info = $model->infoShoworder($this->getQuery('id'));
		$imginfo = array();
		if (!empty($info)) {
			$imgs = explode('::', $info['img']);	
			foreach ($imgs as $img) {
				$imginfo[] = $this->formatImg($img);	
			}
			$imgData = array('files' => $imginfo);
		}
		$this->getView()->display('showorder/mod.phtml', array('imgData' => $imgData, 'info' => $info));
	}

	// }}}
	// {{{ protected function formatImg()
	
	protected function formatImg($img) {
		$data = parse_url($img);
		$name = basename($data['path']);
		$thumbnail_url = 'http://show.mixbridal.com/files/thumbnail/' . $name;

		return array(
			'url' => $img,
			'thumbnail_url' => $thumbnail_url,
			'name' => $name,
		);
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
            'title' => $this->getPost('title'),
            'img' => $this->getPost('img'),
            'comment' => strip_tags($this->getPost('description', null, false), '<a>'),
            'product_ids'  => $this->getPost('productIds'),
            'enable' => ($this->getPost('status') == 'on') ? 1 : 0,
            'refer_url' => $this->getPost('refer_url'),
        );

		$id = $this->getPost('id');

        // 验证参数
        try {
            $model = new ShoworderModel($data);
            $id = $model->modShoworder($id);
        } catch (Exception $e) {
            return $this->errorAjaxRender($e->getMessage(), 1000);
        }
        return $this->ajaxRender(array('success' => '修改成功'));
	}

	// }}}
	// {{{ public function viewAction()
	
	/**
	 * 修改 
	 * 
	 * @access public
	 * @return void
	 */
	public function viewAction()
    {
		$model = new ShoworderModel();
		$info = $model->infoShoworder($this->getQuery('id'));
		$imginfo = array();
		if (!empty($info)) {
			$imgs = explode('::', $info['img']);	
			foreach ($imgs as $img) {
				$imginfo[] = $this->formatImg($img);	
			}
		}

		unset($info['img']);
		$products = explode(',', $info['product_ids']);
		foreach ($products as $key => $value) {
			$products[$key] = trim($value);	
		}
		unset($info['product_ids']);
		$info['products'] = $products;
		$info['images'] = $imginfo;

		return $this->ajaxRender($info);
	}

	// }}}
	// }}}
}
