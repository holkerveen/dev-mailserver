<?php

use App\MboxReader;

include(__DIR__.'/../src/init.php');

if ($_SERVER['REQUEST_URI'] === '/') {
	$wsTarget = getenv('WEBSOCKET_URL') ?: 'ws://localhost:81';
	exit(<<<EOF
		<html lang='en'>
		<head>
			<title>Mail</title>
			<script>window.websocketTarget='$wsTarget';</script>
			<script defer src='vue.js'></script>
			<script defer src='list.js'></script>
			<script defer src='detail.js'></script>
			<script defer src='main.js'></script>
			<script defer src='messagebody.js'></script>
			<script defer src="email.js"></script>
			<link rel='stylesheet' href='style.css'/>
		</head>
		<body>
			<main id="app">
				<list :emails="emails" @select="onSelect"></list>
				<detail :email="selectedEmail"></detail>
			</main>
		</body>
		</html>
		EOF);
}

if (preg_match('#/api/(?<command>[a-z]+)/?$#', $_SERVER['REQUEST_URI'], $matches)) {
	header('Content-Type: application/json');
	switch ($matches['command']) {
		case 'reset':
			file_put_contents('/var/mail/root', '');
			exit();
		case 'list':
			$filename = '/var/mail/root';

			if (!file_exists($filename) && !is_readable($filename)) {
				exit(json_encode([]));
			}

			$f = fopen('/var/mail/root', 'rb');
			$messages = (new MboxReader)->useFileHandle($f)->read();

			exit(json_encode($messages));
		default:
			break;
	}
}

http_response_code(404);
exit("<html lang='en'><head><title>File Not Found</title></head><body><h1>404 File Not Found</h1></body></html>");
