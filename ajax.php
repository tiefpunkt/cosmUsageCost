<?php
include("PachubeAPI/PachubeAPI.php");
include("config.php");

$output= "";

// Try to see if there's some recent cached output available
if (file_exists($config->cache) && (time() < filemtime($config->cache) + $config->cache_expire)) {

	// It is. Let's use that as our output.
	$output = file_get_contents($config->cache);

} else {

	// Create Cosm API connector
	$pachube = new PachubeAPI($config->api_key);

	// Start- and enddates for Cosm query
	$date_start = new DateTime("8 days ago");
	$date_start->setTime(0,0,0);
	$date_end = new DateTime();
	$date_start->setTime(0,0,0);
	$date_format = "Y-m-d\TH:i:s\Z";

	// Get selected datastream history from cosm as json
	$json = $pachube->getDatastreamHistory("json", $config->feed, $config->datastream, $date_start->format($date_format), $date_end->format($date_format), false, false, false, false, false, false, 86400, $config->timezone);

	// Decode JSON into an object
	$datastream = json_decode($json);

	$days;
	$current_value = 0;
	$total_usage = 0;
	$counter = 0;

	// Calculate power usage, CO2 output and costs for each day
	foreach ($datastream->datapoints as $datapoint) {
		if ($current_value != 0) {
			$date = substr($datapoint->at,0,10);
			$days[$date]["usage"] = $datapoint->value - $current_value;
			
			// Only handle positive power usage
			// Negative power usage, such as after a counter reset, is ignored.
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

	// Generate ouput JSON
	$data["days"] = $days;
	$data["detail"]["price"] = $config->price;
	$data["detail"]["co2equivalents"] = $config->co2equivalents;
	$data["summary"]["total"]["usage"] = round($total_usage,2);
	$data["summary"]["total"]["cost"] = round($total_usage * $config->price,2);
	$data["summary"]["total"]["co2"] = round($total_usage * $config->co2equivalents,0);
	$avg_usage = $total_usage / $counter;
	$data["summary"]["avg"]["usage"] = round($avg_usage,2);
	$data["summary"]["avg"]["cost"] = round($avg_usage * $config->price,2);
	$data["summary"]["avg"]["co2"] = round($avg_usage * $config->co2equivalents,0);

	// Generate output
	$data["source"] = "live";
	$output = json_encode($data);
	
	// Write to cache
	$data["source"] = "cache";
	file_put_contents($config->cache, json_encode($data));
	
}

header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 19 Jul 1997 00:00:00 GMT');
header('Content-type: application/json');
echo $output;
?>
