<?php

require_once('init.php');

/*$text = 'Отвлекись!\nОтвлекись на секунду,\nотвлекись на пару часов,\nотвлекись на один день от своих планшетов и ноутбуков – погода зовёт гулять возле моря и отдыхать в парках, а не прятаться в социальных сетях! \n\nВолонтёры #HUB_Volunteer_Service приглашают всех желающих на вело-прогулку! К черту виртуальность, пора выбираться из зимней спячки. \n\nГотовим велосипеды и выдвигаем!\n\nГде: ул. Греческая 1-а (внутренний двор).\nКогда: 28 марта, 15:00.\nПриглашены: волонтёры #HUB_Volunteer_Service, амбассадоры, резиденты, сотрудники #Impact_Hub_Odessa, студенты #Impact_Academy и все, кто хотел бы с нами познакомиться поближе!\n\nРегистрация: <a target="_blank" rel="nofollow" class="_553k" href="http://bit.ly/1BLHhqU" onmousedown="UntrustedLink.bootstrap(this, "HAQG51wti", event)">http://bit.ly/1BLHhqU</a>';
$result = preg_replace('/<a[^>]*>((https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w\.#-]*)*\/?)<\/a>/ui', '<a href="$1" target="_blank">$1</a>', $text);

print_r($result);
print_r("\n\r\n\r");
die(); */

try {
	$console -> handle(['task' => 'Tasks\Facebook\User\Observer',
						'action' => 'observe']);
		       
} catch (\Phalcon\Exception $e) {
        echo $e -> getMessage();
}
