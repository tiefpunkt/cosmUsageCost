<?php
include("PachubeAPI/PachubeAPI.php");
include("config.php");

$pachube = new PachubeAPI($config->api_key);

$date_start = new DateTime("8 days ago");
$date_start->setTime(0,0,0);
$date_end = new DateTime();
$date_start->setTime(0,0,0);
$date_format = "Y-m-d\TH:i:s\Z";

$json = $pachube->getDatastreamHistory("json", $config->feed, $config->datastream, $date_start->format($date_format), $date_end->format($date_format), false, false, false, false, false, false, 86400);

$datastream = json_decode($json);

$output;
$current_value = 0;
//print_r($datastream["datapoints"]);
foreach ($datastream->datapoints as $datapoint) {
	if ($current_value != 0) {
		$date = substr($datapoint->at,0,10);
		$output[$date]["usage"] = $datapoint->value - $current_value;
		if ($output[$date]["usage"] >= 0) {
			$output[$date]["cost"] = round($output[$date]["usage"] * 0.215,2);
			$output[$date]["usage"] = round($output[$date]["usage"],2);
		} else {
			$output[$date]["usage"] = "n/a";
			$output[$date]["cost"] = "n/a";
		}
	}
	$current_value = $datapoint->value;
}


header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 19 Jul 1997 00:00:00 GMT');
header('Content-type: application/json');
echo json_encode($output);
?>
