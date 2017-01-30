<?php

namespace Kronos\Log\Writer;

class Memory extends \Kronos\Log\AbstractWriter {


	/**
	 * Contains all logged messages.
	 * @var array
	 */
	private $_content = [];

	/**
	 * Logs a message to the $_content array.
	 * @param string $level
	 * @param string $message
	 * @param array $context
	 */
	public function log($level, $message, array $context = []) {

		$interpolated_message = $this->interpolate($message, $context);
		$this->_content[] = strtoupper($level) . ': ' . $interpolated_message;
	}

	/**
	 * Returns all logged messages.
	 * @return array
	 */
	public function getContent() {

		return $this->_content;
	}
}