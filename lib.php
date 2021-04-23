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

// Globals
$RSSAC002_DATA_ROOT = '../RSSAC002-data';
$METRICS = ['udp-request-sizes', 'udp-response-sizes', 'tcp-request-sizes', 'tcp-response-sizes',
            'rcode-volume', 'load-time', 'traffic-volume', 'unique-sources', 'zone-size'];
$RSIS = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm'];
$YEARS = ['2013', '2014', '2015', '2016', '2017', '2018', '2019', '2020', '2021'];
if( php_sapi_name() == 'cli'){
  $SERIALIZED_ROOT = '../serialized';
}else{
  $SERIALIZED_ROOT = '/htdocs/rssac002.depht.com/serialized';
}

// Writes a serialized version of passed $data to $fname
function write_serialized_file(string $fname, &$data){
  print("\nWriting " . $fname);
  if( is_writable(dirname($fname))) {
    $fh = fopen($fname, 'w');
    $err = fwrite($fh, serialize($data));
    if( $err === false){
      print("\nError writing to file " . $fname);
      exit(1);
    }else{
      fclose($fh);
      chmod($fname, 0644);
    }
  }else{
    print("\nUnable to write to file " . $fname);
  }
}

// Either return the value at $arr[$key] or return NULL
function get_value(&$arr, $key) {
  if( array_key_exists($key, $arr)){
    if (strlen($arr[$key]) > 0){
      return $arr[$key];
    }
  }
  print("\nFound NULL for " . $key);
  return NULL;
}

// Parses RSSAC002 YAML file and returns the stuff we care about
// Will either return an array or false on error
function parse_yaml_file(string $metric, string $contents) {
  $rv = array();
  $yaml = yaml_parse($contents);
  if( $yaml === false){
    return false;
  }

  switch ($metric) {
  case "load-time":
    if( array_key_exists("time", $yaml)){
      if( is_array($yaml["time"])) {
        if( count($yaml["time"]) > 0){
          $rv = array();
          foreach($yaml["time"] as $key => $val){
            $rv[$key] = $val;
          }
          return $rv;
        }
      }
    }
    return false;

  // Valid DNS RCODES
  // https://www.iana.org/assignments/dns-parameters/dns-parameters.xhtml#dns-parameters-6
  case "rcode-volume":
    foreach($yaml as $key => $val){
      if( $key === "rcodes"){ // Handle broken YAML
        if( is_array($val)){
          foreach( $val as $rcode => $count){
            if( is_numeric($rcode)){
              if( $rcode >= 0 && $rcode <= 23){
                $rv[$rcode] = $count;
              }
            }
          }
          return $rv;
        }
      }

      if( is_numeric($key)){
        if( $key >= 0 && $key <= 23){
          $rv[$key] = $val;
        }
      }
    }
    return $rv;

  case 'udp-request-sizes':
    if( array_key_exists('udp-request-sizes', $yaml)){
      if( is_array($yaml['udp-request-sizes'])){
        foreach( $yaml['udp-request-sizes'] as $key => $val){
          if( $val != 0){
            $rv[$key] = $val;
          }
        }
      }
    }
    return $rv;

  case 'udp-response-sizes':
    if( array_key_exists('udp-response-sizes', $yaml)){
      if( is_array($yaml['udp-response-sizes'])){
        foreach( $yaml['udp-response-sizes'] as $key => $val){
          if( $val != 0){
            $rv[$key] = $val;
          }
        }
      }
    }
    return $rv;

  case 'tcp-request-sizes':
    if( array_key_exists('tcp-request-sizes', $yaml)){
      if( is_array($yaml['tcp-request-sizes'])){
        foreach( $yaml['tcp-request-sizes'] as $key => $val){
          if( $val != 0){
            $rv[$key] = $val;
          }
        }
      }
    }
    return $rv;

  case 'tcp-response-sizes':
    if( array_key_exists('tcp-response-sizes', $yaml)){
      if( is_array($yaml['tcp-response-sizes'])){
        foreach( $yaml['tcp-response-sizes'] as $key => $val){
          if( $val != 0){
            $rv[$key] = $val;
          }
        }
      }
    }
    return $rv;

  case "unique-sources":
    $rv["num-sources-ipv4"] = get_value($yaml, "num-sources-ipv4");
    $rv["num-sources-ipv6"] = get_value($yaml, "num-sources-ipv6");
    $rv["num-sources-ipv6-aggregate"] = get_value($yaml, "num-sources-ipv6-aggregate");
    return $rv;

  case "zone-size":
    if( array_key_exists("size", $yaml)){
      if( is_array($yaml["size"])){
        foreach($yaml["size"] as $key => $val){
          $rv[$key] = $val;
        }
        return $rv;
      }
    }
    return false;

  case "traffic-volume":
    $rv["dns-udp-queries-received-ipv4"] = get_value($yaml, "dns-udp-queries-received-ipv4");
    $rv["dns-udp-queries-received-ipv6"] = get_value($yaml, "dns-udp-queries-received-ipv6");
    $rv["dns-tcp-queries-received-ipv4"] = get_value($yaml, "dns-tcp-queries-received-ipv4");
    $rv["dns-tcp-queries-received-ipv6"] = get_value($yaml, "dns-tcp-queries-received-ipv6");
    $rv["dns-udp-responses-sent-ipv4"] = get_value($yaml, "dns-udp-responses-sent-ipv4");
    $rv["dns-udp-responses-sent-ipv6"] = get_value($yaml, "dns-udp-responses-sent-ipv6");
    $rv["dns-tcp-responses-sent-ipv4"] = get_value($yaml, "dns-tcp-responses-sent-ipv4");
    $rv["dns-tcp-responses-sent-ipv6"] = get_value($yaml, "dns-tcp-responses-sent-ipv6");
    return $rv;
  }
}

// Takes a string representing some sequence of letter
// Returns an array of letters, all valid RSIs
// Return false if string is invalid
function parse_letters(string $input){
  global $RSIS;

  // Check input
  $input = trim(strtolower($input));
  if( strlen($input) > 50 || strlen($input) < 1){ return false; }
  $allowed_chars = array_merge($RSIS, array(",", "-"));
  if( str_replace($allowed_chars, "", $input) !== ""){ return false; }
  if( trim($input, ",-") !== $input){ return false; }
  if( strpos($input, ",-") !== false){ return false; }
  if( strpos($input, "-,") !== false){ return false; }

  $input = str_split($input);
  if( $input[0] === "," || $input[0] === "-"){ return false; }
  if( end($input) === "," || end($input) === "-"){ return false; }

  $rv = [];
  $range_begin = "";
  foreach( $input as $tok){
    if( $range_begin){ // Previous character was a '-'
      if( !in_array($tok, $RSIS)){ // $tok must be a letter here
        return false;
      }elseif( $range_begin >= $tok){ // range must be ascending
        return false;
      }else{
        foreach($RSIS as $rsi){
          if( $rsi > $range_begin && $rsi < $tok){
            if( !in_array($rsi, $rv)){
              array_push($rv, $rsi);
            }
          }
        }
      }
      $range_begin = "";
    }

    if( in_array($tok, $RSIS)){
      if( !in_array($tok, $rv)){
        array_push($rv, $tok);
      }
    }elseif( $tok === ","){
      continue;
    }elseif( $tok === "-"){
      $range_begin = end($rv);
    }
  }
  sort($rv, SORT_STRING);
  return $rv;
}

// Checks if passed strings are valid dates and if $start comes before $end
// Returns true if they are, otherwise false
function check_dates(string $start, string $end){
  if( strlen($start) > 10 || strlen($end) > 10) { return false; }
  if( preg_replace("/[0-9\-]+/", "", $start) !== ""){ return false; }
  if( preg_replace("/[0-9\-]+/", "", $end) !== ""){ return false; }
  $start_date = date_parse_from_format("Y-m-d", $start);
  $end_date = date_parse_from_format("Y-m-d", $end);
  if( !checkdate($start_date['month'], $start_date['day'], $start_date['year'])) { return false; }
  if( !checkdate($end_date['month'], $end_date['day'], $end_date['year'])){ return false; }

  $start_date = DateTime::createFromFormat("Y-m-d", $start);
  $end_date = DateTime::createFromFormat("Y-m-d", $end);
  if( $start_date > $end_date){ return false; }

  return true;
}

// Return all dates between passed $start and $end dates
// Returns false if dates are bad
function parse_dates(string $start, string $end){
  if( !check_dates($start, $end)){
    return false;
  }

  $interval = new DateInterval("P1D"); // 1 day
  $rv = array();

  $start_date = DateTime::createFromFormat("Y-m-d", $start);
  $end_date = DateTime::createFromFormat("Y-m-d", $end);
  $current_day = $start_date;
  do{
    $current_year = $current_day->format('Y');
    if( !array_key_exists($current_year, $rv)){
      $rv[$current_year] = array();
    }
    array_push($rv[$current_year], $current_day->format('Y-m-d'));
    $current_day->add($interval);
  }while($current_day <= $end_date);

  return $rv;
}

// Adjust $start and $end so they start and end on a Monday and Sunday respectively
// Returns false if dates are bad
// Returns start date of Monday immediately before $start
// Returns end date of Sunday immediately after $end
function weekify_dates(string $start, string $end){
  if( !check_dates($start, $end)){
      return false;
  }

  $interval = new DateInterval("P1D"); // 1 day
  $start_date = DateTime::createFromFormat("Y-m-d", $start);
  $end_date = DateTime::createFromFormat("Y-m-d", $end);

  while(intval($start_date->format('N')) > 1){
    $start_date->sub($interval);
  }

  while(intval($end_date->format('N')) < 7){
    $end_date->add($interval);
  }

  return compact($start_date, $end_date);
}

// Takes day based metrics and returns week based metrics
/*
function weekify_output($metrics){
  // Check if dates are weekified
  $start_date = DateTime::createFromFormat("Y-m-d", array_key_first($metrics[0]));
  $end_date = DateTime::createFromFormat("Y-m-d", array_key_last($metrics[0]));
  if( $start_date->format('N') != '1' || $end_date->format('N') != '7'){
    return false; // This should never happen
  }

  $rv = array();
  foreach( $metrics as $rsi){
    rv[$rsi] = array();
    while(count($rsi) > 0){
      if( is_array($rsi[0])){
        $tmp = array();
      }else{
        $tmp = 0;
      }
      for($ii = 0; $ii <= 7; $ii++){


      }

    }


  }
  return $rv;
}
*/

function get_metrics_by_date(string $metric, string $letters, string $start_date, string $end_date){
  global $METRICS;
  global $SERIALIZED_ROOT;

  $metric = trim(strtolower($metric));
  if( !in_array($metric, $METRICS)) { return false; }
  $letters = parse_letters($letters);
  if( $letters === false) { return false; }
  $dates = parse_dates($start_date, $end_date);
  if( $dates === false) { return false; }

  $rv = array();
  foreach( $letters as $let){
    $rv[$let] = array();
    foreach( $dates as $year => $year_days){
      $fname = $SERIALIZED_ROOT . '/' . $metric . '/' . $let . '/' . $year . '.ser';
      if( !is_readable($fname)){ return false; }
      $year_data = file_get_contents($fname);
      if( $year_data === false) { return false; }
      $year_data = unserialize($year_data);

      foreach( $year_days as $day){
        if( array_key_exists($day, $year_data)){
          $rv[$let][$day] = $year_data[$day];
        }else{
          $rv[$let][$day] = NULL;
        }
      }
    }
  }
  return $rv;
}

// Specific handler for traffic-volume
function handle_traffic_volume_request(string $metric, string $letters, string $start_date, string $end_date, int $divisor, $totals){
  // Check input
  if( !(is_int($divisor / 10) || $divisor == 1)) { return false; }
  if( !(is_bool($totals) || $totals === 'sent' || $totals === 'received')) { return false; }

  $sent = ['dns-tcp-responses-sent-ipv4', 'dns-tcp-responses-sent-ipv6', 'dns-udp-responses-sent-ipv4', 'dns-udp-responses-sent-ipv6'];
  $received = ['dns-tcp-queries-received-ipv4', 'dns-tcp-queries-received-ipv6', 'dns-udp-queries-received-ipv4', 'dns-udp-queries-received-ipv6'];

  $metrics = get_metrics_by_date($metric, $letters, $start_date, $end_date);
  if( $metrics === false){ return false; }
  if( $divisor === 0 && $totals === false){ return $metrics; }

  $rv = array();
  foreach( $metrics as $k_let => $v_let){
    $rv[$k_let] = array();
    foreach( $v_let as $k_date => $v_date){
      if( $v_date === NULL) {
        $rv[$k_let][$k_date] = NULL;
      }else{
        foreach( $v_date as $key => $value){
          if( $totals){
            if( $totals == 'sent'){
              if( in_array($key, $sent)){
                if( array_key_exists($k_date, $rv[$k_let])){
                  $rv[$k_let][$k_date] += $value;
                }else{
                  $rv[$k_let][$k_date] = $value;
                }
              }
            }else{ // $totals == received
              if( in_array($key, $received)){
                if( array_key_exists($k_date, $rv[$k_let])){
                  $rv[$k_let][$k_date] += $value;
                }else{
                  $rv[$k_let][$k_date] = $value;
                }
              }
            }
          }else{
            $rv[$k_let][$k_date][$key] = intdiv($value, $divisor);
          }
        }
        if( $divisor != 1 && $totals !== false){
          $rv[$k_let][$k_date] = intdiv($rv[$k_let][$k_date], $divisor);
        }
      }
    }
  }
  return $rv;
}
?>
