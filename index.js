var musicBoxContainer = '', musicBoxTemplate = '', zoneTemplate = '';
var currentMusicBoxes, nextPrayTime = '', nextPrayMusic = '', times = [];
var currentDate = new Date();

const loadSettings = function()	{
	$.ajax({
		url: 'zones.php',
		type: 'get',
		success: function(response)	{
			times = JSON.parse(response);
			
			$.ajax({
				url: 'subscribers.php',
				type: 'get',
				success: function(response)	{
					$.each(JSON.parse(response), function(id, name)	{
						$('select[name=subscribers]').append($('<option>', { value: id, text: name }));
					});
					
					$('select[name=subscribers]').trigger('change');
				}
			});
		}
	});
}
const loadMusicboxes = function()	{
	let today = new Date();
	
	$.ajax({
		url: 'musicboxes.php',
		type: 'get',
		data: { subscriber: $('select[name=subscribers]').val() },
		success: function(response)	{
			currentMusicBoxes = {};
		
			musicBoxContainer.html('');
			
			$.each(JSON.parse(response), function(musicboxname, zones)	{
				currentMusicBoxes[musicboxname] = {};
				
				let musicbox = musicBoxTemplate.clone();
				musicbox.find('[name=music-box-name]').html(musicboxname);
				
				$.each(zones, function(code, schedule)	{
					currentMusicBoxes[musicboxname][code] = { name: schedule.name };
					
					let zone = zoneTemplate.clone();
					zone.find('[name=zone]').text(code + ' - ' + schedule.name);
					
					$.each(times, function(time, name)	{
						let timeparts = schedule[time + '_time'].split(':');
						let scheduleTime = new Date();
						scheduleTime.setHours(timeparts[0]);
						scheduleTime.setMinutes(timeparts[1]);
						scheduleTime.setSeconds(0);
						
						currentMusicBoxes[musicboxname][code][time] = scheduleTime.getTime();
						currentMusicBoxes[musicboxname][code][time + '_music'] = schedule[time + '_music'];
						
						zone.find('[name=' + time + ']').text(formatTime(scheduleTime));
					});
					
					musicbox.append(zone);
				});
				
				musicBoxContainer.append(musicbox);
			});
			
			loadMusicbox();
		}
	});
}
const loadMusicbox = function()	{
	let currenttime = new Date().getTime();
	let currentMusicBox = '', currentZone, currentTime, prayTime = '';
	
	$.each(currentMusicBoxes, function(musicboxname, zones)	{
		$.each(zones, function(zone, schedule)	{
			$.each(times, function(time, name)	{
				if (currenttime < schedule[time])	{
					if (prayTime === '' || prayTime > schedule[time])	{
						currentMusicBox = musicboxname;
						currentZone = zone;
						currentTime = time;
						prayTime = schedule[time];
					}
				}
			});
		});
	});
	
	if (prayTime === '')	{
		nextPrayTime = '';
		
		$('#countdown').addClass('hidden').next().removeClass('hidden');
	} else	{
		$('#countdown [name=time-name]').text(times[currentTime]);
		$('#countdown [name=time]').text(formatTime(prayTime))
		$('#countdown [name=zone]').text(currentZone + ' - ' + currentMusicBoxes[currentMusicBox][currentZone].name);
		$('#countdown').removeClass('hidden').next().addClass('hidden');
		
		nextPrayTime = prayTime;
		nextPrayMusic = currentMusicBoxes[currentMusicBox][currentZone][currentTime + '_music'];
	}
}
const checkPrayTime = function()	{
	if (currentDate.toDateString() !== new Date().toDateString())
		return loadMusicboxes();
	if (nextPrayTime === '')
		return;
	
	let currentTime = new Date().getTime();
	let countdown = nextPrayTime - currentTime;
	
	if (countdown < 0)	{
		let audio = new Audio('audios/' + nextPrayMusic + '.mp3');
		audio.play();
		
		nextPrayTime = '';
		
		loadMusicbox();
	}
	
	let hours = parseInt(countdown / (1000 * 60 * 60));
	countdown = countdown % (1000 * 60 * 60);
	let minutes = parseInt(countdown / (1000 * 60));
	countdown = countdown % (1000 * 60);
	let seconds = parseInt(countdown / 1000);
	
	$('#countdown [name=hours]').text(hours.toString().padStart(2, '0'));
	$('#countdown [name=minutes]').text(minutes.toString().padStart(2, '0'));
	$('#countdown [name=seconds]').text(seconds.toString().padStart(2, '0'));
}
const formatTime = function(time)	{
	let datetime = new Date(time);
	let timeunit = 'am';
	let hours = datetime.getHours();
	
	if (hours > 12)	{
		timeunit = 'pm';
		hours -= 12;
	}
	
	return hours.toString().padStart(2, '0') + ':' + datetime.getMinutes().toString().padStart(2, '0') + ' ' + timeunit;
}

$(function()	{
	musicBoxContainer = $('#subscription').parent();
	zoneTemplate = $('#subscription #zone-schedule').remove();
	musicBoxTemplate = $('#subscription').remove();
	
	$('select[name=subscribers]').change(loadMusicboxes);
	
	setInterval(checkPrayTime, 1000);
});