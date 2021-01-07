#!/usr/local/bin/php
<?php

// Constants
$RSSAC002_DATA_ROOT = '../RSSAC002-data';
$SERIALIZED_ROOT = '../serialized';
$METRICS = ['load-time', 'rcode-volume', 'traffic-sizes', 'unique-sources', 'zone-size', 'traffic-volume'];
$RSIS = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm'];
//$YEARS = ['2013', '2014', '2015', '2016', '2017', '2018', '2019', '2020'];
$YEARS = ['2020'];

// Functions
function parse_yaml_file(string $metric, string $contents) : array {
  $rv = array();
  array_push($rv, "HERP");
  return $rv;
}

if ( !is_dir($RSSAC002_DATA_ROOT)){
  error_log('Bad RSSAC002 root dir');
  exit(1);
}

if ( !is_dir($SERIALIZED_ROOT)){
  error_log('Bad SERIALIZED root dir');
  exit(1);
}

foreach ($METRICS as $metric){
  foreach ($RSIS as $rsi){
    foreach ($YEARS as $year){
      $data = array();
      $year_dir = $RSSAC002_DATA_ROOT . "/" . $year;
      foreach (scandir($year_dir) as $month) {
        $month_dir = $year_dir . "/" . $month;
        if (in_array($metric, scandir($month_dir))){
          $metric_dir = $month_dir . "/" . $metric;
          print("\nmetric_dir:" . $metric_dir);
          foreach (scandir($metric_dir) as $ff){
            if (strpos($ff, $rsi) === 0){
              $yaml_file = $metric_dir . "/" . $ff;
              if (is_readable($yaml_file)) {
                print("\nReading " . $yaml_file);
                $data[$year] = parse_yaml_file($metric, file_get_contents($yaml_file));
              }else{
                print("\nUnable to read file " . $yaml_file);
              }
            }
          }
        }
      }
      print("\n");
      var_dump($data);
      exit(0);
    }
  }
  exit(0);
}

?>
