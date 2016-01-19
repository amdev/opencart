<?php
class ControllerOpenbayEtsy extends Controller {
	public function install() {
		$this->load->language('openbay/etsy');
		$this->load->model('openbay/etsy');
		$this->load->model('setting/setting');
		$this->load->model('extension/extension');

		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'openbay/etsy_product');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'openbay/etsy_product');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'openbay/etsy_shipping');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'openbay/etsy_shipping');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'openbay/etsy_shop');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'openbay/etsy_shop');

		$this->model_openbay_etsy->install();
	}

	public function uninstall() {
		$this->load->model('openbay/etsy');
		$this->load->model('setting/setting');
		$this->load->model('extension/extension');

		$this->model_openbay_etsy->uninstall();
		$this->model_extension_extension->uninstall('openbay', $this->request->get['extension']);
		$this->model_setting_setting->deleteSetting($this->request->get['extension']);
	}

	public function index() {
		$this->load->language('openbay/etsy');

		$data = $this->language->all();

		$this->document->setTitle($this->language->get('text_dashboard'));
		$this->document->addScript('view/javascript/openbay/js/faq.js');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'href' => $this->url->ssl('common/dashboard', 'token=' . $this->session->data['token'], true),
			'text' => $this->language->get('text_home'),
		);

		$data['breadcrumbs'][] = array(
			'href' => $this->url->ssl('extension/openbay', 'token=' . $this->session->data['token'], true),
			'text' => $this->language->get('text_openbay'),
		);

		$data['breadcrumbs'][] = array(
			'href' => $this->url->ssl('openbay/ebay', 'token=' . $this->session->data['token'], true),
			'text' => $this->language->get('text_dashboard'),
		);

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['validation'] 	= $this->openbay->etsy->validate();
		$data['links_settings'] = $this->url->ssl('openbay/etsy/settings', 'token=' . $this->session->data['token'], true);
		$data['links_products'] = $this->url->ssl('openbay/etsy_product/links', 'token=' . $this->session->data['token'], true);
		$data['links_listings'] = $this->url->ssl('openbay/etsy_product/listings', 'token=' . $this->session->data['token'], true);

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('openbay/etsy', $data));
	}

	public function settings() {
		$this->load->model('setting/setting');
		$this->load->model('openbay/etsy');
		$this->load->model('localisation/order_status');

		$this->load->language('openbay/etsy_settings');

		$data = $this->language->all();


		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {
			$this->model_setting_setting->editSetting('etsy', $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->ssl('openbay/etsy/index&token=' . $this->session->data['token']));
		}

		$this->document->setTitle($this->language->get('heading_title'));
		$this->document->addScript('view/javascript/openbay/js/faq.js');
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'href' => $this->url->ssl('common/dashboard', 'token=' . $this->session->data['token'], true),
			'text' => $this->language->get('text_home'),
		);

		$data['breadcrumbs'][] = array(
			'href' => $this->url->ssl('extension/openbay', 'token=' . $this->session->data['token'], true),
			'text' => $this->language->get('text_openbay'),
		);

		$data['breadcrumbs'][] = array(
			'href' => $this->url->ssl('openbay/etsy', 'token=' . $this->session->data['token'], true),
			'text' => $this->language->get('text_etsy'),
		);

		$data['breadcrumbs'][] = array(
			'href' => $this->url->ssl('openbay/etsy/settings', 'token=' . $this->session->data['token'], true),
			'text' => $this->language->get('heading_title'),
		);

		$data['action'] = $this->url->ssl('openbay/etsy/settings', 'token=' . $this->session->data['token'], true);
		$data['cancel'] = $this->url->ssl('openbay/etsy', 'token=' . $this->session->data['token'], true);

		$data['token'] = $this->session->data['token'];

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->request->post['etsy_status'])) {
			$data['etsy_status'] = $this->request->post['etsy_status'];
		} else {
			$data['etsy_status'] = $this->config->get('etsy_status');
		}

		if (isset($this->request->post['etsy_token'])) {
			$data['etsy_token'] = $this->request->post['etsy_token'];
		} else {
			$data['etsy_token'] = $this->config->get('etsy_token');
		}

		if (isset($this->request->post['etsy_enc1'])) {
			$data['etsy_enc1'] = $this->request->post['etsy_enc1'];
		} else {
			$data['etsy_enc1'] = $this->config->get('etsy_enc1');
		}

		if (isset($this->request->post['etsy_enc2'])) {
			$data['etsy_enc2'] = $this->request->post['etsy_enc2'];
		} else {
			$data['etsy_enc2'] = $this->config->get('etsy_enc2');
		}

		if (isset($this->request->post['etsy_address_format'])) {
			$data['etsy_address_format'] = $this->request->post['etsy_address_format'];
		} else {
			$data['etsy_address_format'] = $this->config->get('etsy_address_format');
		}

		if (isset($this->request->post['etsy_order_status_new'])) {
			$data['etsy_order_status_new'] = $this->request->post['etsy_order_status_new'];
		} else {
			$data['etsy_order_status_new'] = $this->config->get('etsy_order_status_new');
		}

		if (isset($this->request->post['etsy_order_status_paid'])) {
			$data['etsy_order_status_paid'] = $this->request->post['etsy_order_status_paid'];
		} else {
			$data['etsy_order_status_paid'] = $this->config->get('etsy_order_status_paid');
		}

		if (isset($this->request->post['etsy_order_status_shipped'])) {
			$data['etsy_order_status_shipped'] = $this->request->post['etsy_order_status_shipped'];
		} else {
			$data['etsy_order_status_shipped'] = $this->config->get('etsy_order_status_shipped');
		}

		$data['api_server'] = $this->openbay->etsy->getServer();
		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		$data['account_info'] = $this->model_openbay_etsy->verifyAccount();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('openbay/etsy_settings', $data));
	}

	public function settingsUpdate() {
		$this->openbay->etsy->settingsUpdate();

		$response = array('header_code' => 200);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($response));
	}

	public function getOrders() {
		$response = $this->openbay->etsy->call('v1/etsy/order/get/all/', 'GET');

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($response));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'openbay/etsy')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}