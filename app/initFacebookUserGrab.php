<?php

require_once('init.php');


/*$text = '"ОФИЦИАЛЬНАЯ СТРАНИЦА ФЕСТИВАЛЯ\n<a target="_blank" rel="nofollow" class="_553k" href="https://www.facebook.com/nadnefest">https://www.facebook.com/nadnefest</a>\n\nФестиваль состоится 21 марта в Государственном музее авиации в Жулянах (Киев, ул. Медовая, 1).\n\nКУПИТЬ БИЛЕТ (только на kasa.in.ua): <a target="_blank" rel="nofollow" class="_553k" href="http://goo.gl/wbl5yQ">http://goo.gl/wbl5yQ</a>\n\n"';
$result = preg_replace('/<a.*href.*>\s?(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?<\/a>/i', '<a href="$1" target="_blank">$1</a>', $text);

print_r($result);
die();*/


try {
	$console -> handle(['task' => 'Tasks\Facebook\User\Observer',
						'action' => 'observe']);
		       
} catch (\Phalcon\Exception $e) {
        echo $e -> getMessage();
}
