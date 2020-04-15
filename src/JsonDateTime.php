<?php

namespace App;

use DateTime;
use JsonSerializable;

/**
 * Class JsonDateTime provides more consistent JSON serialization than the default datetime object
 * @package App
 */
class JsonDateTime extends DateTime implements JsonSerializable
{
	public function jsonSerialize()
	{
		return $this->format('c');
	}

}

