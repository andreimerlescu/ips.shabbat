<?php
if(isset($_GET['debug']) && $_GET['debug'] == "1"){
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
}
date_default_timezone_set('America/New_York');

function getHebrewDate() {
    $date = new DateTime('now', new DateTimeZone('America/New_York'));
    $jd = gregoriantojd($date->format('m'), $date->format('d'), $date->format('Y'));
    $hebrew_date = cal_from_jd($jd, CAL_JEWISH);
    $hebrew_months = [
         1 => 'Tishri',
         2 => 'Heshvan',
         3 => 'Kislev',
         4 => 'Tevet',
         5 => 'Shevat',
         6 => 'Adar',
         7 => 'Adar II',
         8 => 'Nisan',
         9 => 'Iyar',
        10 => 'Sivan',
        11 => 'Tammuz',
        12 => 'Av',
        13 => 'Elul'
    ];
    return sprintf("%d %s %d", 
        $hebrew_date['day'], 
        $hebrew_months[$hebrew_date['month']], 
        $hebrew_date['year']
    );
}

$current_time = new DateTime();
$friday_730pm = (new DateTime('friday this week 19:30'))->setTimezone(new DateTimeZone('America/New_York'));
$saturday_830pm = (new DateTime('saturday this week 20:30'))->setTimezone(new DateTimeZone('America/New_York'));

if ($current_time >= $friday_730pm && $current_time < $saturday_830pm) {
	if (!empty($_SERVER['HTTP_X_SHABBAT_CHECK'])) {
	    return; // Exit if this header is already set
	}
	header('X-Shabbat-Check: true');
	header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
	header('Cache-Control: post-check=0, pre-check=0', false);
	header('Pragma: no-cache');
	header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
	header('Content-Type: text/html; charset=utf-8');
	$remaining_time = $saturday_830pm->getTimestamp() - $current_time->getTimestamp();
	$end_date = $saturday_830pm->format('l, F j, Y');
	$current_hebrew_date = getHebrewDate();
	?>
	<!DOCTYPE html>
	<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Shabbat Shalom</title>
		<style>
	    	body {
				  background-color: #2F4F4F; /* Slate Steel Gray */
				  color: #2C2C2C; /* Noir Graphite Dark Gray */
				  font-family: Arial, sans-serif;
				  display: flex;
				  flex-direction: column;
				  justify-content: center;
				  align-items: center;
				  height: 100vh;
				  margin: 0;
	    	}	
	    	.container {
				  background-color: rgba(255, 255, 255, 0.9);
				  padding: 2rem;
				  border-radius: 10px;
				  text-align: center;
				  box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
				  max-width: 600px;
				  width: 90%;
	    	}
	    	.countdown {
				  font-size: 2rem;
				  margin: 1rem 0;
				  font-weight: bold;
	    	}
	    	h1 {
				  margin-top: 0;
	    	}
		</style>
		<script>
	    	function startCountdown(seconds) {
				  const countdownElem = document.getElementById('countdown');
				  function updateCountdown() {
		    		const days = Math.floor(seconds / 86400);
		    		const hours = Math.floor((seconds % 86400) / 3600);
		    		const minutes = Math.floor((seconds % 3600) / 60);
		    		const secs = seconds % 60;
				    countdownElem.textContent = 
						  `${String(days).padStart(2, '0')}d ` +
						  `${String(hours).padStart(2, '0')}h ` +
						  `${String(minutes).padStart(2, '0')}m ` +
						  `${String(secs).padStart(2, '0')}s`;
				    if (seconds > 0) {
						  seconds--;
						  setTimeout(updateCountdown, 1000);
		    		}
				  }
				  updateCountdown();
	    	}
		    document.addEventListener('DOMContentLoaded', () => {
				  const remainingTime = <?php echo $remaining_time; ?>;
				  startCountdown(remainingTime);
	    	});
		</script>
	</head>
	<body>
		<div class="container">
	    	<h1>SHABBAT SHALOM!</h1>
	    	<p>Our community is honoring Yeshua's Sabbath for Shabbat.</p>
	    	<p>Our site will be back online in:</p>
	    	<div class="countdown" id="countdown"></div>
	    	<p>We will reopen at 8:30pm on <?php echo $end_date; ?>.</p>
	    	<p>Current Date: <?php echo $current_hebrew_date; ?></p>
		</div>
	</body>
	</html>
	<?php
	exit;
} else {
	header("Location: index.php");
}
