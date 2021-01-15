<?php
/* Copyright Andrew McConachie <andrew@depht.com> 2021 */
//error_reporting(E_ALL);
header('Content-Type: application/json');

if( !isset($_GET['letters']) || !isset($_GET['start_date']) || !isset($_GET['end_date'])){
  http_response_code(400);
  exit(1);
}

$totals = false;
if( isset($_GET['totals'])){ $totals = true; }

$divisor = 1;
if( isset($_GET['divisor'])){
  if( is_int($_GET['divisor'])){
    if( $_GET['divisor'] > 0 && is_int($_GET['divisor'] / 10)){
      $divisor = $_GET['divisor'];
    }
  }
}

?>