<?php
/* Copyright Andrew McConachie <andrew@depht.com> 2021 */
//error_reporting(E_ALL);
header('Content-Type: application/json');

if( !isset($_GET['rsi']) || !isset($_GET['start_date']) || !isset($_GET['end_date'])){
  http_response_code(400);
  exit(1);
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