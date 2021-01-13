<?php
/* Copyright Andrew McConachie <andrew@depht.com> 2021 */
error_reporting(E_ALL);

require_once "../../lib.php";
//header('Content-Type: application/json');

$raw_metrics = get_metrics_by_date('load-time', $_GET['letters'], $_GET['start_date'], $_GET['end_date']);
if( $raw_metrics === false){
  http_response_code(400);
  exit(1);
}
var_dump($raw_metrics);


var_dump($_GET);
print("\nEnding");
?>
