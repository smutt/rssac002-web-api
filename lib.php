<?php
/* Copyright Andrew McConachie <andrew@depht.com> 2021 */

// Globals
$RSSAC002_DATA_ROOT = '../RSSAC002-data';
$METRICS = ['rcode-volume', 'traffic-sizes', 'load-time', 'traffic-volume', 'unique-sources', 'zone-size'];
$RSIS = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm'];
$YEARS = ['2013', '2014', '2015', '2016', '2017', '2018', '2019', '2020'];
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

  case "rcode-volume":
    foreach($yaml as $key => $val){
      if( is_numeric($key)){
          $rv[$key] = $val;
        }
    }
    return $rv;

  case "traffic-sizes":
    $sizes = ['udp-request-sizes', 'udp-response-sizes', 'tcp-request-sizes', 'tcp-response-sizes'];
    foreach( $sizes as $size){
      $rv[$size] = array();
    }

    foreach( $sizes as $size){
      if( array_key_exists($size, $yaml)){
        if( is_array($yaml[$size])){
          foreach( $yaml[$size] as $key => $val){
            $rv[$size][$key] = $val;
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

// Get all dates between passed start and end dates
function parse_dates(string $start_input, string $end_input){
  // Check input
  if( strlen($start_input) > 10 || strlen($end_input) > 10) { return false; }
  if( preg_replace("/[0-9\-]+/", "", $start_input) !== ""){ return false; }
  if( preg_replace("/[0-9\-]+/", "", $end_input) !== ""){ return false; }
  $start = date_parse_from_format("Y-m-d", $start_input);
  $end = date_parse_from_format("Y-m-d", $end_input);
  if( !checkdate($start['month'], $start['day'], $start['year'])) { return false; }
  if( !checkdate($end['month'], $end['day'], $end['year'])){ return false; }

  $start = DateTime::createFromFormat("Y-m-d", $start_input);
  $end = DateTime::createFromFormat("Y-m-d", $end_input);
  if( $start > $end){ return false; }
  $interval = new DateInterval("P1D"); // 1 day
  $rv = array();

  $current_day = $start;
  do{
    $current_year = $current_day->format('Y');
    if( !array_key_exists($current_year, $rv)){
      $rv[$current_year] = array();
    }
    array_push($rv[$current_year], $current_day->format('Y-m-d'));
    $current_day->add($interval);
  }while($current_day <= $end);

  return $rv;
}

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

// TODO: make this work with more metrics, currently only works with 'traffic-volume'
function handle_request(string $metric, string $letters, string $start_date, string $end_date, int $divisor, bool $totals){
  // Check input
  if( !(is_int($divisor / 10) || $divisor == 1)) { return false; }
  if( !is_bool($totals)) { return false; }

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
            if( in_array($k_date, $rv[$k_let])){
              $rv[$k_let][$k_date] += $value;
            }else{
              $rv[$k_let][$k_date] = $value;
            }
          }else{
            $rv[$k_let][$k_date][$key] = intdiv($value, $divisor);
          }
        }
        if( $divisor != 1 && $totals){
          $rv[$k_let][$k_date] = intdiv($rv[$k_let][$k_date], $divisor);
        }
      }
    }
  }
  return $rv;
}
?>
