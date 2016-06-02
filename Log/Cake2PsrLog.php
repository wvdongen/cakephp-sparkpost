<?php

/**
 *
 */
use Psr\Log\LogLevel;

/**
 *
 */
class Cake2PsrLog implements Psr\Log\LoggerInterface {

	public function emergency($message, array $context = array()) {
		CakeLog::emergency($message, $context);
	}

	public function alert($message, array $context = array()) {
		CakeLog::alert($message, $context);
	}

	public function critical($message, array $context = array()) {
		CakeLog::critical($message, $context);
	}

	public function error($message, array $context = array()) {
		CakeLog::error($message, $context);
	}

	public function warning($message, array $context = array()) {
		CakeLog::warning($message, $context);
	}

	public function notice($message, array $context = array()) {
		CakeLog::notice($message, $context);
	}

	public function info($message, array $context = array()) {
		CakeLog::info($message, $context);
	}

	public function debug($message, array $context = array()) {
		CakeLog::debug($message, $context);
	}

	/**
	 * Logs with an arbitrary level.
	 *
	 * @param mixed $level
	 * @param string $message
	 * @param array $context
	 * @return null
	 */
	public function log($level, $message, array $context = array()) {
		CakeLog::write($level, $message, $context);
	}

}
