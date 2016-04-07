<?php

namespace MailHog\v1;

use stdClass;

class Email {

	protected $email;

	/**
	 * MailHogEmail constructor.
	 *
	 * @param $email
	 */
	public function __construct(stdClass $email) {
		$this->email = $email;
	}

	/**
	 * @return string
	 */
	public function getId() {
		return $this->email->ID;
	}

	/**
	 * @return array();
	 */
	public function getFromEmailAddresses() {
		return $this->getHeader('From');
	}

	/**
	 * @return array();
	 */
	public function getToEmailAddresses() {
		return $this->getHeader('To');
	}

	/**
	 * @return array();
	 */
	public function getCcEmailAddresses() {
		return $this->getHeader('Cc')[0];
    }

	/**
	 * 
	 * @return int
	 */
	public function getTotalRecipients() {
		return count($this->email->To);
	}

	/**
	 * @return string
	 */
	public function getReplyToEmailAddress() {
		return $this->getHeader('Reply-To');
	}

	/**
	 * @return string
	 */
	public function getReturnPathEmailAddress() {

		return $this->getHeader('Return-Path');
	}

	/**
	 * @return string
	 */
	public function getSubject() {
		return $this->getHeader('Subject')[0];
	}

	/**
	 * @return string
	 */
	public function getHeader($name) {
		return $this->email->Content->Headers->{$name};
	}

	/**
	 * @return string
	 */
	public function getContentType() {
		return $this->getHeader('Content-Type')[0];
	}

	/**
	 * @return string|NULL
	 */
	public function getHTMLBody() {

		if ($this->getContentType() === 'text/html') {
			return $this->email->Content->Body;
		}

		$parts = $this->email->MIME->Parts;
		$part = NULL;

		// usually, HTML mime part is located at the 2 array item
		// check its header to make sure it is an html body
		if ($this->hasPartHeaderAndValue($parts[2], 'Content-Type', 'html')) {
			$part = $parts[2];
		}

		// if there is no part defined,
		// try to loop through all parts and find the correct html part
		if (!$part) {
			$part = $this->getPartFromPartsByHeaderAndValue('Content-Type', 'html');
		}

		// if html part found
		if ($part) {
			return $part->Body;
		}

		return NULL;
	}

	/**
	 * @return string|NULL
	 */
	public function getTextBody() {

		if ($this->getContentType() === 'text/plain') {
			return $this->email->Content->Body;
		}

		$parts = $this->email->MIME->Parts;
		$part = NULL;

		// usually, Text mime part is located at the 1 array item
		// check its header to make sure it is an txt body
		if ($this->hasPartHeaderAndValue($parts[1], 'Content-Type', 'plain')) {
			$part = $parts[1];
		}
		
		// if there is no part defined,
		// try to loop through all parts and find the correct html part
		if (!$part) {
			$part = $this->getPartFromPartsByHeaderAndValue('Content-Type', 'plain');
		}

		// if html part found
		if ($part) {
			return $part->Body;
		}

		return NULL;
	}

	/**
	 * 
	 * @param string $header
	 * @param string $value
	 * 
	 * @return stdClass|NULL
	 */
	public function getPartFromPartsByHeaderAndValue($header, $value) {
		$parts = $this->email->MIME->Parts;
		$part = NULL;
		foreach ($parts as $_part) {
			if ($this->hasPartHeaderAndValue($_part, $header, $value)) {
				$part = $_part;
				break;
			}
		}
		return $part;
	}

	/**
	 * 
	 * @param stdClass $part
	 * @param string $header
	 * @param string $value
	 * 
	 * @return boolean
	 */
	private function hasPartHeaderAndValue($part, $header, $value) {
		if (!empty($part->Headers->{$header})) {
			foreach ($part->Headers->{$header} as $header_line) {
				if (FALSE !== strpos($header_line, $value)) {
					return TRUE;
				}
			}
		}
		return FALSE;
	}

}
