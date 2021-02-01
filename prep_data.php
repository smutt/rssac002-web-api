#!/usr/local/bin/php
<?php
/* Copyright Andrew McConachie <andrew@depht.com> 2021 */

// Only allow execution via the CLI
if( !php_sapi_name() == 'cli'){
  exit();
}
require_once "lib.php";

if ( !is_dir($RSSAC002_DATA_ROOT)){
  error_log('Bad RSSAC002 root dir');
  exit(1);
}

if ( !is_dir($SERIALIZED_ROOT)){
  error_log('Bad SERIALIZED root dir');
  exit(1);
}

foreach( $METRICS as $metric){
  foreach( $RSIS as $rsi){
    print("\nProcessing " . $metric . " for " . $rsi);
    foreach( $YEARS as $year){
      $data = array();
      $year_dir = $RSSAC002_DATA_ROOT . "/" . $year;
      foreach( scandir($year_dir) as $month) {
        $month_dir = $year_dir . "/" . $month;
        if (in_array($metric, scandir($month_dir))){
          $metric_dir = $month_dir . "/" . $metric;
          foreach( scandir($metric_dir) as $ff){
            if( strpos($ff, $rsi) === 0){
              $yaml_file = $metric_dir . "/" . $ff;
              if( is_readable($yaml_file)) {
                $day = explode("-", $ff)[2];
                if( strpos($day, $year) === 0){
                  $day_data = parse_yaml_file($metric, file_get_contents($yaml_file));
                  if( $day_data === false){
                    print("\nError parsing YAML file" . $yaml_file);
                  }else{
                    $dtime = DateTime::createFromFormat("Ymd", $day);
                    $data[$dtime->format('Y-m-d')] = $day_data;
                  }
                }else{
                  print("\nBad date in file format " . $yaml_file);
                }
              }else{
                print("\nUnable to read file " . $yaml_file);
              }
            }
          }
        }
      }
    $fname = $SERIALIZED_ROOT . "/" . $metric . "/" . $rsi . "/" . $year . ".ser";
    write_serialized_file($fname, $data);
    }
  }
}

?>
