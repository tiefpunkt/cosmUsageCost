<?php
include("PachubeAPI/PachubeAPI.php");
include("config.php");

$pachube = new PachubeAPI($config->api_key);

$date_start = new DateTime("8 days ago");
$date_start->setTime(0,0,0);
$date_end = new DateTime();
$date_start->setTime(0,0,0);
$date_format = "Y-m-d\TH:i:s\Z";

$json = $pachube->getDatastreamHistory("json", $config->feed, $config->datastream, $date_start->format($date_format), $date_end->format($date_format), false, false, false, false, false, false, 86400, $config->timezone);

$datastream = json_decode($json);

$days;
$current_value = 0;
$total_usage = 0;
$counter = 0;
//print_r($datastream["datapoints"]);
foreach ($datastream->datapoints as $datapoint) {
	if ($current_value != 0) {
		$date = substr($datapoint->at,0,10);
		$days[$date]["usage"] = $datapoint->value - $current_value;
		if ($days[$date]["usage"] >= 0) {
			$total_usage += $days[$date]["usage"];
			$days[$date]["cost"] = round($days[$date]["usage"] * $config->price,2);
			$days[$date]["co2"] = round($days[$date]["usage"] * $config->co2equivalents,0);
			$days[$date]["usage"] = round($days[$date]["usage"],2);
			$counter++;
		} else {
			$days[$date]["usage"] = "n/a";
			$days[$date]["cost"] = "n/a";
			$days[$date]["co2"] = "n/a";
		}
	}
	$current_value = $datapoint->value;
}

$output["days"] = $days;
$output["detail"]["price"] = $config->price;
$output["detail"]["co2equivalents"] = $config->co2equivalents;
$output["summary"]["total"]["usage"] = round($total_usage,2);
$output["summary"]["total"]["cost"] = round($total_usage * $config->price,2);
$output["summary"]["total"]["co2"] = round($total_usage * $config->co2equivalents,0);
$avg_usage = $total_usage / $counter;
$output["summary"]["avg"]["usage"] = round($avg_usage,2);
$output["summary"]["avg"]["cost"] = round($avg_usage * $config->price,2);
$output["summary"]["avg"]["co2"] = round($avg_usage * $config->co2equivalents,0);

header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 19 Jul 1997 00:00:00 GMT');
header('Content-type: application/json');
echo json_encode($output);
?>
