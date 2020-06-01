<?php

// TODO Look at taint issues and fix
$disableTaintCheck = true;

$cfg = require __DIR__ . '/../vendor/mediawiki/mediawiki-phan-config/src/config.php';

$cfg['directory_list'] = array_merge(
	$cfg['directory_list'],
	[
		'.', // our dir
		'../../extensions/SocialProfile',
		'../../extensions/Renameuser',
	]
);

$cfg['exclude_analysis_directory_list'] = array_merge(
	$cfg['exclude_analysis_directory_list'],
	[
		'vendor', // dear gods just no
		'../../extensions/SocialProfile',
		// We don't actually *depend on* Renameuser, we merely *support* it, but phan cannot tell the difference.
		'../../extensions/Renameuser',
	]
);

return $cfg;

