<?php
/* Copyright Andrew McConachie <andrew@depht.com> 2021 */

// Constants
$RSSAC002_DATA_ROOT = '../RSSAC002-data';
$SERIALIZED_ROOT = '../serialized';
$METRICS = ['load-time', 'traffic-volume', 'rcode-volume', 'traffic-sizes', 'unique-sources', 'zone-size'];
$RSIS = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm'];
$YEARS = ['2013', '2014', '2015', '2016', '2017', '2018', '2019', '2020'];

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
  return null;
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
          $rv["time"] = array();
          foreach($yaml["time"] as $key => $val){
            $rv["time"][$key] = $val;
          }
          return $rv;
        }
      }
    }
    return false;

  case "rcode-volume":
    $rv["rcode-volume"] = array();
    foreach($yaml as $key => $val){
      if( is_numeric($key)){
          $rv["rcode-volume"][$key] = $val;
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
          return $rv;
        }
      }
    }
    return false;

  case "unique-sources":
    $rv["num-sources-ipv4"] = get_value($yaml, "num-sources-ipv4");
    $rv["num-sources-ipv6"] = get_value($yaml, "num-sources-ipv6");
    $rv["num-sources-ipv6-aggregate"] = get_value($yaml, "num-sources-ipv6-aggregate");
    return $rv;

  case "zone-size":
    $rv["size"] = array();
    if( array_key_exists("size", $yaml)){
      if( is_array($yaml["size"])){
        foreach($yaml["size"] as $key => $val){
          $rv["size"][$key] = $val;
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

function get_values_by_day(string $derp){
  return "\nHERP";
}


?>
