<?php
namespace Opencart\Admin\Controller\Extension\CustomCategoryProduct\Module;
/**
 * Class CustomCategoryProduct
 *
 * @package Opencart\Admin\Controller\Extension\CustomCategoryProduct\Module
 */
class CustomCategoryProduct extends \Opencart\System\Engine\Controller {
	/**
	 * @return void
	 */
	public function index(): void {
		$this->load->language('extension/custom_category_product/module/custom_category_product');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module')
		];

		if (!isset($this->request->get['module_id'])) {
			$data['breadcrumbs'][] = [
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/custom_category_product/module/custom_category_product', 'user_token=' . $this->session->data['user_token'])
			];
		} else {
			$data['breadcrumbs'][] = [
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/custom_category_product/module/custom_category_product', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'])
			];
		}
		
		if (!isset($this->request->get['module_id'])) {
			$data['save'] = $this->url->link('extension/custom_category_product/module/custom_category_product|save', 'user_token=' . $this->session->data['user_token']);
		} else {
			$data['save'] = $this->url->link('extension/custom_category_product/module/custom_category_product|save', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id']);
		}

		$data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module');
		
		if (isset($this->request->get['module_id'])) {
			$this->load->model('setting/module');
			$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
		}
		
		if (isset($module_info['name'])) {
			$data['name'] = $module_info['name'];
		} else {
			$data['name'] = '';
		}
		
		if (isset($module_info['width'])) {
			$data['width'] = $module_info['width'];
		} else {
			$data['width'] = -1;
		}

		if (isset($module_info['height'])) {
			$data['height'] = $module_info['height'];
		} else {
			$data['height'] = -1;
		}
		
		if (isset($module_info['twig_name'])) {
			$data['twig_name'] = $module_info['twig_name'];
		} else {
			$data['twig_name'] = '';
		}
		
		$this->load->model('catalog/category');
		
		$data['categories'] = [];

		if (!empty($module_info['category'])) {
			$categories = $module_info['category'];
		} else {
			$categories = [];
		}

		foreach ($categories as $category_id) {
			$category_info = $this->model_catalog_category->getCategory($category_id);

			if ($category_info) {
				$data['categories'][] = [
					'category_id' => $category_info['category_id'],
					'name'       => $category_info['name']
				];
			}
		}

		if (isset($module_info['status'])) {
			$data['status'] = $module_info['status'];
		} else {
			$data['status'] = '';
		}
		
		if (isset($this->request->get['module_id'])) {
			$data['module_id'] = (int)$this->request->get['module_id'];
		} else {
			$data['module_id'] = 0;
		}

		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		
		$this->response->setOutput($this->load->view('extension/custom_category_product/module/custom_category_product', $data));
	}

	/**
	 * @return void
	 */
	public function save(): void {
		$this->load->language('extension/custom_category_product/module/custom_category_product');

		$json = [];

		if (!$this->user->hasPermission('modify', 'extension/custom_category_product/module/custom_category_product')) {
			$json['error']['warning'] = $this->language->get('error_permission');
		}
		
		if ((oc_strlen($this->request->post['name']) < 3) || (oc_strlen($this->request->post['name']) > 64)) {
			$json['error']['name'] = $this->language->get('error_name');
		}
		
		if (!$json) {
			$this->load->model('setting/module');
			
			if (!$this->request->post['module_id']) {
				$json['module_id'] = $this->model_setting_module->addModule('custom_category_product.custom_category_product', $this->request->post);
			} else {
				$this->model_setting_module->editModule($this->request->post['module_id'], $this->request->post);
			}
			
			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
