<?php
/**
 * @author  Christoph Bessei
 * @version 0.01
 */

//TODO Make main class a singleton
class Singleton {
	public $foo = '';
	static private $instance = null;

	static public function getInstance() {
		if ( null === self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	private function __construct() {
	}

	private function __clone() {
	}
}