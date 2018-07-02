<?php
/**
 * Created by PhpStorm.
 * User: Artem
 * Date: 02.07.2018
 * Time: 15:59
 */

namespace TeamSpeakBackaup\lib;

use  WHMCS\Module\Addon\Setting;

class Config implements \ArrayAccess{
	private $config;

	function __construct() {
		array_walk( $this->load(), function ( $val, $key ) {
			$this->config[ $val['setting'] ] = $val['value'];
		} );
	}

	function load() {
		return Setting::Module( 'TeamSpeakBackaup' )->get()->toArray();
	}

	function toArray() {
		return (array) $this->config;
	}

	public function offsetSet($offset, $value) {
		if (is_null($offset)) {
			$this->config[] = $value;
		} else {
			$this->config[$offset] = $value;
		}
	}

	public function offsetExists($offset) {
		return isset($this->config[$offset]);
	}

	public function offsetUnset($offset) {
		unset($this->config[$offset]);
	}

	public function offsetGet($offset) {
		return isset($this->config[$offset]) ? $this->config[$offset] : null;
	}

}