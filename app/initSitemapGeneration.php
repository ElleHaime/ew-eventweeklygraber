<?php

require_once('init.php');

try {
	$console -> handle(['task' => 'Tasks\Seo\Sitemap\Generate',
			    		'action' => 'generate']);
} catch (\Phalcon\Exception $e) {
	echo $e -> getMessage();
}
