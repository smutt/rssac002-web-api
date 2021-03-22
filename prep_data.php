#!/usr/local/bin/php
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

// Only allow execution via the CLI
if( !php_sapi_name() == 'cli'){
  exit();
}
require_once "lib.php";

if( !is_dir($RSSAC002_DATA_ROOT)){
  error_log('Bad RSSAC002 root dir');
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
  // Handle traffic-sizes special case
  if(in_array($metric, array('udp-request-sizes', 'udp-response-sizes', 'tcp-request-sizes', 'tcp-response-sizes'))){
    $metric_file = 'traffic-sizes';
  }else{
    $metric_file = $metric;
  }

  foreach( $RSIS as $rsi){
    print("\nProcessing " . $metric . " for " . $rsi);
    foreach( $YEARS as $year){
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

print("\nFinshed\n");
?>
