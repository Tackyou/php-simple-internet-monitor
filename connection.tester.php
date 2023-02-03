<?php
$ctx = stream_context_create([
	'http'=> [
		'timeout' => 5
	],
	'ssl'=> [
		'verify_peer' => false,
		'verify_peer_name' => false
	]
]);
$logFile = __DIR__.'/internet.log';
echo "Internet Monitoring started\n";
echo "Log file: $logFile\n";
$successfulTests = 0;
$connectionLostTime = null;
while(true){
	$response = @file_get_contents('https://vodafone.de/', false, $ctx);
	if($response === false){
		if($connectionLostTime == null){
			$successfulTests = 0;
			$connectionLostTime = date_create();
			$status = 'Verbindungsfehler '.date_format($connectionLostTime, 'd.m.Y H:i:s');
			echo $status;
			file_put_contents($logFile, $status, FILE_APPEND);
		}
	} else {
		if($connectionLostTime != null){
			$connectionBackTime = date_create();
			$connectionDeadTime = date_diff($connectionLostTime, $connectionBackTime);
			$connectionLostTime = null;
			$status = ' wieder verbunden '.date_format($connectionBackTime, 'd.m.Y H:i:s')."\n";
			$status .= '!! Ausfallzeit ~ '.$connectionDeadTime->format('%a Tage %h Stunden %i Minuten %s Sekunden')."\n";
			echo $status;
			file_put_contents($logFile, $status, FILE_APPEND);
		}
		$successfulTests++;
		echo "> {$successfulTests}x\r";
	}
	sleep($connectionLostTime != null ? 1 : 12);
}