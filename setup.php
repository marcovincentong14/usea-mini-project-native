<?php

require "Database.php";

$db = Database::getInstance();
$names = require 'names.php';
$times = require 'config.php';

echo 'Cleaning up old data<br/>';
$db->clear('musicbox_zone_schedule');
$db->clear('zone_schedule');
$db->clear('zone');
$db->clear('subscriber_musicbox');
$db->clear('musicbox');
$db->clear('subscriber');

echo 'Generating subscribers<br />';
for ($counter = 0; $counter < 20; $counter ++)
	$db->insert('subscriber', [ 'name' ], [ $names['given'][rand(0, count($names['given']) - 1)] . ' ' . $names['given'][rand(0, count($names['given']) - 1)] . ' ' . $names['last'][rand(0, count($names['last']) - 1)] ]);

echo 'Generating music boxes<br/>';
for ($counter = 0; $counter < 20; $counter ++)
	$db->insert('musicbox', [ 'name' ], [ $names['given'][rand(0, count($names['given']) - 1)] . ' ' . $names['given'][rand(0, count($names['given']) - 1)] . ' ' . $names['last'][rand(0, count($names['last']) - 1)] ]);

echo 'Assigning music boxes to subscribers<br/>';
$subscribers = $db->getRows('subscriber');
$musicboxes = $db->getRows('musicbox');

foreach ($subscribers as $subscriber)	{
	$subscribermusicboxes = [];
	
	for ($counter = 0; $counter < rand(1, 10); $counter ++)	{
		do
			$musicbox = $musicboxes[rand(0, count($musicboxes) - 1)]['id'];
		while (in_array($musicbox, $subscribermusicboxes));
		
		$db->insert('subscriber_musicbox', [ 'subscriber_id', 'musicbox_id' ], [ $subscriber['id'], $musicbox ]);
		
		$subscribermusicboxes[] = $musicbox;
	}
	
	echo '<span style="padding-left: 10px">' . count($subscribermusicboxes) . ' assigned to subscriber ' . $subscriber['name'] . '</span><br/>';
}

echo 'Generating zones<br/>';
foreach ($times['list'] as $groups)
	foreach ($groups as $zone => $name)	{
		$row = [ $zone, $name ];
		
		foreach ($times['types'] as $type => $name)
			$row[] = rand(1, 20);
			
		$zoneid = $db->insert('zone', array_merge([ 'code', 'name' ], array_keys($times['types'])), $row);
	}
	
echo 'Getting zone schedules<br/>';
$zones = $db->getRows('zone');
$curldata = [
	'r' => 'esolatApi/takwimsolat',
	'period' => 'week'
];

$curl = curl_init();
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

foreach ($zones as $zone)	{
	echo '<span style="padding-left: 10px">Getting zone schedule for ' . $zone['code'] . ' - ' . $zone['name'] . '</span><br/>';
	
	$curldata['zone'] = $zone['code'];
	
	curl_setopt($curl, CURLOPT_URL, 'https://www.e-solat.gov.my?' . http_build_query($curldata));
	
	$result = curl_exec($curl);
	$response = json_decode($result, true);
	
	foreach ($response['prayerTime'] as $prayerTime)	{
		$row = [ $zone['id'], date('Y-m-d', strtotime($prayerTime['date'])) ];
		
		foreach ($times['types'] as $type => $name)
			$row[] = $prayerTime[$type];
		
		$db->insert('zone_schedule', array_merge([ 'zone_id', 'date' ], array_keys($times['types'])), $row);
	}
}

echo 'Assigning zone prayers to music boxes<br/>';
$schedules = $db->getRows('zone_schedule');

foreach ($musicboxes as $musicbox)	{
	$musicboxschedules = [];
	
	for ($counter = 0; $counter < rand(1, count($schedules)); $counter ++)	{
		do
			$zoneschedule = $schedules[rand(0, count($schedules) - 1)]['id'];
		while (in_array($zoneschedule, $musicboxschedules));
		
		$db->insert('musicbox_zone_schedule', [ 'musicbox_id', 'zone_schedule_id' ], [ $musicbox['id'], $zoneschedule ]);
		
		$musicboxschedules[] = $zoneschedule;
	}
	
	echo '<span style="padding-left: 10px">' . count($musicboxschedules) . ' assigned to music box ' . $musicbox['name'] . '</span><br/>';
}