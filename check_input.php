<?php
/* Copyright Andrew McConachie <andrew@depht.com> 2021 */

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

//error_reporting(E_ALL);
header('Content-Type: application/json');

if( !isset($_GET['rsi']) || !isset($_GET['start_date']) || !isset($_GET['end_date'])){
  http_response_code(400);
  exit(1);
}

if( !check_dates($_GET['start_date'], $_GET['end_date'])){
  http_response_code(400);
  exit(1);
}else{
  $start_date = $_GET['start_date'];
  $end_date = $_GET['end_date'];
}

$week = false;
if( isset($_GET['week'])){
  $week = true;
  list($start_date, $end_date) = weekify_dates($start_date, $end_date);
}

$totals = false;
if( isset($_GET['totals'])){
  if( $_GET['totals'] == 'sent'){
    $totals = 'sent';
  }elseif( $_GET['totals'] == 'received'){
    $totals = 'received';
  }
}

$divisor = 1;
if( isset($_GET['divisor'])){
  if( is_numeric($_GET['divisor'])){
    if( $_GET['divisor'] > 0 && is_int($_GET['divisor'] / 10)){
      $divisor = intval($_GET['divisor']);
    }
  }
}

?>