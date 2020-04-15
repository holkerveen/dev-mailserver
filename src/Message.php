<?php

namespace App;

class Message
{
	/** @var string */
	public $from;
	/** @var JsonDateTime */
	public $received;
	/** @var array */
	public $headers;
	/** @var string */
	public $body = null;

	public function __construct(string $from, string $received)
	{
		$this->from = $from;
		$this->received = $received;
	}
}
