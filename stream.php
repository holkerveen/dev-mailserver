<?php

use App\MboxReader;

include(__DIR__ . "/src/init.php");

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
socket_bind($socket, '0.0.0.0', 81);
socket_listen($socket);

while($connection = socket_accept($socket)) {
	$pid = pcntl_fork();
	if($pid === -1) {
		exit("Could not fork");
	}
	elseif($pid) {
		echo "Created child process $pid\n";
		continue;
	}
	else {
		echo "Accepted new connection\n";
		break;
	}
}

$request = socket_read($connection, 5000);
preg_match('#Sec-WebSocket-Key: (.*)\r\n#', $request, $matches);
$key = base64_encode(pack('H*', sha1("{$matches[1]}258EAFA5-E914-47DA-95CA-C5AB0DC85B11")));
$headers = implode("\r\n", [
		"HTTP/1.1 101 Switching Protocols",
		"Upgrade: websocket",
		"Connection: Upgrade",
		"Sec-WebSocket-Version: 13",
		"Sec-WebSocket-Accept: $key",
	]) . "\r\n\r\n";
socket_write($connection, $headers, strlen($headers));

$f = null;
$reader = new MboxReader();
if (!file_exists('/var/mail/root')) {
	file_put_contents('/var/mail/root','');
	chmod('/var/mail/root', 0666);
}

function frame($message):string{
	$frame = chr(129);
	if(strlen($message) < 126) {
		$frame .= chr(strlen($message));
	}
	elseif(strlen($message) < 2**16) {
		$frame .= chr(126) . pack('n',strlen($message));
	}
	else {
		$frame .= chr(127) . pack('Q',strlen($message));
	}
	$frame .= $message;
	return $frame;
}

$filename = '/var/mail/root';
$messages = $reader->read($filename);
socket_write($connection, frame(json_encode($messages)));

$in = inotify_init();
stream_set_blocking($in, false);
$wd = inotify_add_watch($in, '/var/mail/root', IN_MODIFY | IN_CREATE);
while (true) {
	$r = [$in];
	$w = [];
	$e = [];
	$n = stream_select($r, $w, $e, 10);
	if ($n === 0) {
		$content = json_encode([]);
		if(socket_write($connection, frame(json_encode([])))===false) {
			break;
		}
	} else {
		inotify_read($in);
		$messages = $reader->read($filename);
		var_dump($messages);
		if(socket_write($connection, frame(json_encode($messages)))===false) {
			echo "Write error!";
			break;
		}
	}
}

exit("close connection\n");
socket_close($connection);
inotify_rm_watch($in, $wd);

