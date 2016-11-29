<?php
abstract class Abstract_Nice_Checkplus {

	var $MODULE = 'nice_ckeckplus';

	var $plugin;

	var $client = false;

	function __construct() {
	}

	function exec($cmd) {
		return exec($this->get_client() . ' ' . $cmd);
	}

	function get_client() {
		if (!$this->client)
			$this->client = $this->plugin->DIR . 'module/nice_checkplus/' .
				(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'Windows/CPClient.exe' :
					'Linux/' . (PHP_INT_SIZE * 8) . '/CPClient');
		return $this->client;
	}
}