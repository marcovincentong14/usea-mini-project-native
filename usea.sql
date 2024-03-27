create database usea_db;
use usea_db;

create table subscriber (
	id integer primary key not null auto_increment,
	name varchar(100) not null
);
create table musicbox (
	id integer primary key not null auto_increment,
	name varchar(100) not null
);
create table subscriber_musicbox (
	subscriber_id integer not null,
	musicbox_id integer not null
);

create table zone (
	id integer primary key not null auto_increment,
	code varchar(10) not null,
	name varchar(100) not null,
	imsak varchar(10) not null,
	fajr varchar(10) not null,
	syuruk varchar(10) not null,
	dhuhr varchar(10) not null,
	asr varchar(10) not null,
	maghrib varchar(10) not null,
	isha varchar(10) not null
);
create table zone_schedule (
	id integer primary key not null auto_increment,
	zone_id integer not null,
	date date not null,
	imsak time not null,
	fajr time not null,
	syuruk time not null,
	dhuhr time not null,
	asr time not null,
	maghrib time not null,
	isha time not null
);
create table musicbox_zone_schedule (
	musicbox_id integer not null,
	zone_schedule_id integer not null
);