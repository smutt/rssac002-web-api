<?php
/* Copyright Andrew McConachie <andrew@depht.com> 2021 */

require_once "../../lib.php";
require_once "../../check_input.php";

$raw_metrics = handle_traffic_volume_request('traffic-volume', $_GET['rsi'], $_GET['start_date'], $_GET['end_date'], $divisor, $totals);
if( $raw_metrics === false){
  http_response_code(400);
  exit(1);
}

$output = json_encode($raw_metrics);
if( $output === false){
  http_response_code(500);
  exit(1);
}

print($output);
?>
