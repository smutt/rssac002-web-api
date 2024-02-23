<?php
/* Copyright Andrew McConachie <andrew@depht.com> 2021 2024 */

/*
    This file is part of the rssac002-web-api.

    The rssac002-web-api is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    The rssac002-web-api is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with the rssac002-web-api.  If not, see <https://www.gnu.org/licenses/>.
*/

require_once "../../lib.php";
require_once "../../check_input.php";

$raw_metrics = handle_traffic_volume_request('traffic-volume', $_GET['rsi'], $start_date, $end_date, $totals);
if( $raw_metrics === false){
  http_response_code(400);
  exit(1);
}

if( $week === true){
  $raw_metrics = weekify_output($raw_metrics);
}

if( $sum === true){
  $raw_metrics = summify_output($raw_metrics);
}

$output = json_encode($raw_metrics);
if( $output === false){
  http_response_code(500);
  exit(1);
}

print($output);
?>
