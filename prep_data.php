#!/usr/bin/env php
<?php
/* Copyright Andrew McConachie <andrew@depht.com> 2021, 2024 */

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

// Only allow execution via the CLI
if( !php_sapi_name() == 'cli'){
  exit();
}
require_once "lib.php";

if( !is_dir($RSSAC002_DATA_ROOT)){
  error_log('Bad RSSAC002 root dir');
  exit(1);
}

if( !is_dir($INSTANCE_DATA_ROOT)){
  error_log('Bad instance root dir');
  exit(1);
}

if( !is_dir($SERIALIZED_ROOT)){
  error_log('Bad SERIALIZED root dir');
  exit(1);
}

// Create any necessary directories and set permissions
foreach($METRICS as $metric){
  foreach($RSIS as $rsi){
    $path = $SERIALIZED_ROOT . '/' . $metric . '/' . $rsi;
    if( !is_dir($path)){
      if( !mkdir($path, 0755, true)){
        error_log("Unable to mkdir " . $path);
        exit(1);
      }else{
        print("\nCreated directory " . $path);
        // permissions won't always work with mkdir if umask is set
        if( !chmod($SERIALIZED_ROOT . '/' . $metric, 0755)){
          error_log("Unable to change permissions on " . $SERIALIZED_ROOT . '/' . $metric);
          exit(1);
        }
        if( !chmod($path, 0755)){
          error_log("Unable to change permissions on " . $path);
          exit(1);
        }
      }
    }

    if( !is_writable($path)){
      if( !chmod($path, 0755)){
        error_log("Unable to chmod " . $path);
        exit(1);
      }else{
        print("\nSet permissions 0755 on " . $path);
      }
    }
  }
}

foreach( $METRICS as $metric){
  print("\nSerializing " . $metric);

  if(in_array($metric, array('instances-count', 'instances-detail'))){ // Handle instance metrics
    $interval = new DateInterval("P1D"); // 1 day
    foreach( $RSIS as $rsi){
      print("\nProcessing " . $metric . " for " . $rsi);
      for($year = $INSTANCE_START_YEAR; $year <= date("Y"); $year++){
        $data = array();
        $start_date = DateTime::createFromFormat("Y-m-d", $year . "-01-01");
        $end_date = DateTime::createFromFormat("Y-m-d", $year . "-12-31");
        $active_day = $start_date;
        do{
          if( $active_day < DateTime::createFromFormat('Y-m-d', $INSTANCE_START_DATE)){
            $active_day->add($interval);
            continue;
          }
          if( $active_day >= DateTime::createFromFormat('Y-m-d', date('Y-m-d'))){
            break;
          }
          $left_yaml_file = $INSTANCE_DATA_ROOT . $active_day->format('/Y/m/d/') . $rsi . '-root';

          if( is_readable($left_yaml_file . '.yml')){
            $yaml_file = $left_yaml_file . '.yml';
          }elseif( is_readable($left_yaml_file . '.yaml')){
            $yaml_file = $left_yaml_file . '.yaml';
          }else{
            print("\nMissing YAML " . $left_yaml_file);
            $active_day->add($interval);
            continue;
          }

          $day_data = parse_yaml_file($metric, file_get_contents($yaml_file));
          if( $day_data === false){
            print("\nError parsing YAML file" . $yaml_file);
          }else{
            $data[$active_day->format('Y-m-d')] = $day_data;
          }
          $active_day->add($interval);
        }while($active_day <= $end_date);

        $fname = $SERIALIZED_ROOT . "/" . $metric . "/" . $rsi . "/" . $year . ".ser";
        write_serialized_file($fname, $data);
      }
    }
  }elseif($metric == 'zone-size'){ // Handle zone-size
    print("\nProcessing zone-size from RZM");
    for($year = $RSSAC002_START_YEAR; $year <= date("Y"); $year++){
      $data = array();
      $year_dir = $RZM_DATA_ROOT . "/" . $year;
      foreach( scandir($year_dir) as $month) {
        $month_dir = $year_dir . "/" . $month;
        if( in_array('zone-size', scandir($month_dir))){
          $metric_dir = $month_dir . "/zone-size";
          foreach( scandir($metric_dir) as $ff){
            if( !str_starts_with($ff, '.')){
              $yaml_file = $metric_dir . "/" . $ff;
              if( is_readable($yaml_file)) {
                if( strpos($ff, 'a-root') === 0){
                  $day = explode("-", $ff)[2];
                }else{
                  $day = explode("-", $ff)[1];
                }
                if( strpos($day, $year) === 0){
                  $day_data = parse_yaml_file('zone-size', file_get_contents($yaml_file));
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
      $fname = $SERIALIZED_ROOT . "/zone-size/a/" . $year . ".ser";
      write_serialized_file($fname, $data);
    }
  }else{ // Handle RSSAC002 metrics
    // Handle traffic-sizes special case
    if(in_array($metric, array('udp-request-sizes', 'udp-response-sizes', 'tcp-request-sizes', 'tcp-response-sizes'))){
      $metric_file = 'traffic-sizes';
    }else{
      $metric_file = $metric;
    }

    foreach( $RSIS as $rsi){
      print("\nProcessing " . $metric . " for " . $rsi);
      for($year = $RSSAC002_START_YEAR; $year <= date("Y"); $year++){
        $data = array();
        $year_dir = $RSSAC002_DATA_ROOT . "/" . $year;
        foreach( scandir($year_dir) as $month) {
          $month_dir = $year_dir . "/" . $month;
          if( in_array($metric_file, scandir($month_dir))){
            $metric_dir = $month_dir . "/" . $metric_file;
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
}

print("\nFinshed\n");
?>
