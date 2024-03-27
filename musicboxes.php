<?php

require "Database.php";

$musicboxes = [];
$times = require 'config.php';
$query = 'select d.name musicboxname, e.code zone, e.name zonename';

foreach ($times['types'] as $type => $name)
	$query .= ", e.$type {$type}_music, c.$type {$type}_time";

$query .= ' from subscriber_musicbox a '
	. 'inner join musicbox_zone_schedule b using (musicbox_id) '
	. 'inner join zone_schedule c on c.id = b.zone_schedule_id '
	. 'inner join musicbox d on d.id = a.musicbox_id '
	. 'inner join zone e on e.id = c.zone_id '
	. 'where a.subscriber_id = ? and c.date = ?';

foreach (Database::getInstance()->getRowsByQuery($query, [ $_GET['subscriber'], date('Y-m-d') ]) as $musicbox)	{
	$musicboxname = $musicbox['musicboxname'];
	$schedule = [ 'name' => $musicbox['zonename'] ];
	
	if (!array_key_exists($musicboxname, $musicboxes))
		$musicboxes[$musicboxname] = [];
	
	foreach ($times['types'] as $type => $name)
		$schedule = array_merge($schedule, [
			$type . '_time' => $musicbox[$type . '_time'],
			$type . '_music' => $musicbox[$type . '_music']
		]);
	
	$musicboxes[$musicboxname][$musicbox['zone']] = $schedule;
}
	
echo json_encode($musicboxes);