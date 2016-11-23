<?php
class ControllerExtensionPaymentLiqPay extends Controller {
	public function index() {
		$data['button_confirm'] = $this->language->get('button_confirm');

		$order_id = $this->session->data['order_id'];
		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $result_url  = $this->url->link('checkout/success', '', true);
        $server_url  = $this->url->link('extension/payment/liqpay/callback', '', true);
        $private_key = $this->config->get('liqpay_signature');
        $public_key  = $this->config->get('liqpay_merchant');
        $sandbox     = ($this->config->get('liqpay_sandbox') == 'sandbox') ? '1' : '0' ;

        $liqpay = new LiqPay($public_key, $private_key);
        $html = $liqpay->cnb_form(array(
            'version'        => '3',
            'action'         => 'pay', 
            'amount'         => $order_info['total'],
            'currency'       => $order_info['currency_code'],
            'description'    => 'Order: '.$order_id,
            'order_id'       => $order_id,
            'result_url'     => $result_url,
            'server_url'     => $server_url,
            'sandbox'        => $sandbox
        ));

        

		return  $html;
	}

	public function callback() {

		$private_key = $this->config->get('liqpay_signature'); 

	    $data        = $this->request->post['data'];
	    $signature   = $this->request->post['signature'];

	    $sign_check  = base64_encode(sha1($private_key . $data . $private_key, 1));
	    $parsed_data = json_decode(base64_decode($data), true);

		if ($sign_check == $signature) {
		    $this->load->model('checkout/order');
		    $this->model_checkout_order->addOrderHistory($parsed_data['order_id'], $this->config->get('config_order_status_id'));			
		}
	    
	}
}