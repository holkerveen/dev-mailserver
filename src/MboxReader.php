<?php /** @noinspection PhpMissingFieldTypeInspection */

namespace App;

use Exception;


/**
 * Class MboxReader
 * Reads mbox files such as the files written to by postfix. Remembers the file offset in order to not return messages that have already been sentt.
 */
class MboxReader
{
	private const PATTERN_FROM = '#^From (?<from>[-.a-zA-Z0-9]+@[-.a-zA-Z0-9]+)\s+(?<datetime>.*)$#';

	/** @var int */
	private $currentMessageIndex = -1;

	/** @var Message */
	private $currentMessage = null;

	/** @var resource */
	private $f = null;

	private $fposarr = [];

	/**
	 * @param resource $f
	 * @return array
	 * @throws Exception
	 */
	public function read($filename): array
	{
		$f = fopen($filename, 'rb');
		if (array_key_exists($filename, $this->fposarr)) {
			fseek($f, $this->fposarr[$filename]);
		}

		$messages = $this->currentMessageIndex >= 0
			? ["m$this->currentMessageIndex" => $this->currentMessage]
			: [];

		$lineNumber = 0;
		while (false !== ($line = fgets($f))) {
			$lineNumber++;

			$line = rtrim($line, "\n");

			if ($this->currentMessage === null) {
				if (preg_match(self::PATTERN_FROM, $line, $matches)) {
					$this->currentMessage = new Message($matches['from'], $matches['datetime']);
					$messages["m" . (++$this->currentMessageIndex)] = $this->currentMessage;
				} else {
					throw new Exception("Invalid input file");
				}
			} elseif ($this->currentMessage->body === null) {
				if ($line === "") {
					$this->currentMessage->body = '';
					continue;
				}
				if (preg_match('/^\s+/', $line)) {
					$last = count($this->currentMessage->headers) - 1;
					$this->currentMessage->headers[$last]['value'] .= ltrim($line);
					continue;
				}
				if (!strchr($line, ':')) {
					exit('line:' . $line);
				}
				[$key, $value] = explode(':', $line, 2);
				$this->currentMessage->headers[] = ['key' => $key, 'value' => trim($value)];
			} else {
				if (preg_match(self::PATTERN_FROM, $line, $matches)) {
					// If the first line matches pattern, there is no need to include the last message
					if ($lineNumber === 1 && $this->currentMessageIndex >= 0) {
						unset($messages["m" . $this->currentMessageIndex]);
					}
					$this->currentMessage = new Message($matches['from'], $matches['datetime']);
					$messages["m" . (++$this->currentMessageIndex)] = $this->currentMessage;
				} else {
					// Handle quoted-printable soft line breaks
					if (substr($line, -1) === '=') {
						$this->currentMessage->body .= substr($line, 0, -1);
					} else {
						$this->currentMessage->body .= "$line\n";
					}
				}
			}

		}

		$this->fposarr[$filename] = ftell($f);
		fclose($f);
		return $messages;
	}
}

