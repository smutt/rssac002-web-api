<?php
/* Copyright Andrew McConachie <andrew@depht.com> 2021 */
//error_reporting(E_ALL);

require_once "../../lib.php";
header('Content-Type: application/json');

if( !isset($_GET['letters']) || !isset($_GET['start_date']) || !isset($_GET['end_date'])){ http_response_code(400); }

$raw_metrics = get_metrics_by_date('traffic-sizes', $_GET['letters'], $_GET['start_date'], $_GET['end_date']);
if( $raw_metrics === false){ http_response_code(400); }

$output = json_encode($raw_metrics);
if( $output === false){ http_response_code(500); }

print($output);
?>
