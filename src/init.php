<?php

/**
 * Simple PSR-4 autoloader
 */
spl_autoload_register(function ($class) {
	$nsPrefix = 'App\\';
	if (strncmp($nsPrefix, $class, strlen($nsPrefix)) !== 0) {
		return;
	}
	require __DIR__ . '/' . str_replace('\\', '/', substr($class, strlen($nsPrefix))) . '.php';
});

