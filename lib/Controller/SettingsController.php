<?php
/**
 *
 * @copyright Copyright (c) 2021, RCDevs (info@rcdevs.com)
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 */

namespace OCA\OpenOTPSign\Controller;

use OCP\IRequest;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IConfig;

class SettingsController extends Controller {

	private $userId;
	private $config;

	public function __construct($AppName, IRequest $request, IConfig $config, $UserId){
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
		$this->config = $config;
	}

	public function saveSettings() {
		$this->config->setAppValue('openotpsign', 'server_url', $this->request->getParam('server_url'));
		$this->config->setAppValue('openotpsign', 'ignore_ssl_errors', $this->request->getParam('ignore_ssl_errors'));
		$this->config->setAppValue('openotpsign', 'client_id', $this->request->getParam('client_id'));
		$this->config->setAppValue('openotpsign', 'default_domain', $this->request->getParam('default_domain'));
		$this->config->setAppValue('openotpsign', 'user_settings', $this->request->getParam('user_settings'));
		$this->config->setAppValue('openotpsign', 'use_proxy', $this->request->getParam('use_proxy'));
		$this->config->setAppValue('openotpsign', 'proxy_host', $this->request->getParam('proxy_host'));
		$this->config->setAppValue('openotpsign', 'proxy_port', $this->request->getParam('proxy_port'));
		$this->config->setAppValue('openotpsign', 'proxy_username', $this->request->getParam('proxy_username'));
		$this->config->setAppValue('openotpsign', 'proxy_password', $this->request->getParam('proxy_password'));
		$this->config->setAppValue('openotpsign', 'signed_file', $this->request->getParam('signed_file'));

		return new JSONResponse([
			'code' => 1,
		]);
	}

	public function checkServerUrl() {
		$opts = array('location' => $this->request->getParam('server_url'));

		if ($this->request->getParam('ignore_ssl_errors')) {
			$context = stream_context_create([
				'ssl' => [
					// set some SSL/TLS specific options
					'verify_peer' => false,
					'verify_peer_name' => false,
					'allow_self_signed' => true
				]
			]);

			$opts['stream_context'] = $context;
		}

		if ($this->request->getParam('use_proxy')) {
			$opts['proxy_host'] = $this->request->getParam('proxy_host');
			$opts['proxy_port'] = $this->request->getParam('proxy_port');
			$opts['proxy_login'] = $this->request->getParam('proxy_username');
			$opts['proxy_password'] = $this->request->getParam('proxy_password');
		}


		$client = new \SoapClient(__DIR__.'/openotp.wsdl', $opts);
		$resp = $client->openotpStatus();

		return new JSONResponse([
			'status' => $resp['status'],
			'message' => $resp['message']
		]);
	}

	/**
	 * @NoAdminRequired
	 */
	public function checkSettings() {
		return new JSONResponse(!empty($this->config->getAppValue('openotpsign', 'server_url')));
	}
}
