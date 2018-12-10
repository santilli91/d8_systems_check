<?php
namespace Drupal\d8_systems_check\Controller;
use \Drupal\Core\Controller\ControllerBase;
use \Drupal\Core\Routing\TrustedRedirectResponse;
use \Drupal\node\Entity\Node;
use \Drupal\taxonomy\Entity\Term;
use \Drupal\file\Entity\File;
use \Drupal\paragraphs\Entity\Paragraph;
class SystemsCheck extends ControllerBase {

	public function check() {
		\Drupal::service('page_cache_kill_switch')->trigger();

		$status = array();
		$status['status'] = 100;
		$status['info'] = $this->getInfo();
		$status['cert'] = $this->getCert();
		echo json_encode($status);exit;

		return array(
			'#type' => 'markup',
			'#markup' => t('Output Here will json'),
		);
	}

	private function getInfo() {
		$config = \Drupal::config('system.site');
		$info = array(
			'name' => $config->get('name'),
			'url' => $_SERVER['HTTP_HOST'],
			'admin_email' => $config->get('mail'),
			'version' => \Drupal::VERSION,
		);
		return $info;
	}

	private function getCert() {
		$url = 'https://' . $_SERVER['HTTP_HOST'];
		//$url = 'https://consultant360.com';
		$orignal_parse = parse_url($url, PHP_URL_HOST);
	    $get = stream_context_create(array("ssl" => array("capture_peer_cert" => TRUE)));
	    $read = stream_socket_client("ssl://".$orignal_parse.":443", $errno, $errstr, 5, STREAM_CLIENT_CONNECT, $get);
	    $cert = stream_context_get_params($read);
	    $certinfo = openssl_x509_parse($cert['options']['ssl']['peer_certificate']);

	    /*echo '<pre>';
	    print_r($certinfo);
	    echo '</pre>';*/
	    $cert = array(
	    	'issuer' => $certinfo['issuer']['CN'],
	    	'valid_from' => $certinfo['validFrom_time_t'],
	    	'valid_to' => $certinfo['validTo_time_t'],
	    	'valid' => $certinfo['validTo_time_t'] && $certinfo['validTo_time_t'] > time()?100:0,
	    );
	   	return $cert;
	}
}
