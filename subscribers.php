<?php

require "Database.php";

$subscribers = [];

foreach (Database::getInstance()->getRows('subscriber') as $subscriber)
	$subscribers[$subscriber['id']] = $subscriber['name'];
	
echo json_encode($subscribers);