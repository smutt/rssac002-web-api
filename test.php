#!/usr/bin/env php
<?php
/* Copyright Andrew McConachie <andrew@depht.com> 2021 */

// Only allow execution via the CLI
if( !php_sapi_name() == 'cli'){
  exit();
}
require_once "lib.php";

// assertion handler
function assert_handler($file, $line, $code, $desc = null){
  echo "Assertion failed at $file:$line: $code";
  if ($desc){
    echo ": $desc";
  }
  echo "\n";
}
assert_options(ASSERT_CALLBACK, 'assert_handler');
//assert_options(ASSERT_BAIL, true); // Halt execution if assertion fails
assert_options(ASSERT_ACTIVE, true);
assert_options(ASSERT_WARNING, true);

print("Running tests\n");
assert(true == 1);

// Test parse_letters()
assert(parse_letters("a") == array("a"));
assert(parse_letters("a,c,b") == array("a", "b", "c"));
assert(parse_letters('d-g') == array('d','e','f','g'));
assert(parse_letters('m,b,l,a-f') == array('a','b','c','d','e','f','l','m'));
assert(parse_letters('b,a-k') == array('a','b','c','d','e','f','g','h','i','j','k'));
assert(parse_letters('h,,,a-c') == array('a','b','c','h'));
assert(parse_letters('-b') === false);
assert(parse_letters('b,a-k,') === false);
assert(parse_letters('n') === false);
assert(parse_letters('g-c') === false);
assert(parse_letters('f,k,-l') === false);

// Test parse_dates()
assert(parse_dates('2014-01-30', '2014-02-01') == array(2014 => array('2014-01-30','2014-01-31','2014-02-01')));
assert(parse_dates('2014-asds', '2014-02-01') == false);
assert(parse_dates('2014-02-03', '2014-02-01') == false);

// Test get_metrics_by_date()
// TODO: Need more test cases here
assert(get_metrics_by_date('load-time', 'a', '2015-06-30', '2015-07-01') ===
       array('a' => array('2015-06-30' => array(2015063000 => 1183, 2015063001 => 267),
                          '2015-07-01' => array(2015070100 => 1205, 2015070101 => 530))));

assert(get_metrics_by_date('load-time', 'b-c', '2017-02-04', '2017-02-05') ===
       array('b' => array('2017-02-04' => NULL, '2017-02-05' => NULL),
             'c' => array('2017-02-04' => array(2017020400 => 2, 2017020401 => 2),
                          '2017-02-05' => array(2017020500 => 13, 2017020501 => 2))));

assert(get_metrics_by_date('unique-sources', 'l', '2019-06-06', '2019-06-01') === false);
assert(get_metrics_by_date('unique-sources', 'l', '2019-03-06', '2019-03-15') ===
       array('l' => array('2019-03-06' => NULL, '2019-03-07' =>
                          array('num-sources-ipv4' => 3496378, 'num-sources-ipv6' => 296395,
                                'num-sources-ipv6-aggregate' => 195281),
                          '2019-03-08' => NULL, '2019-03-09' => NULL, '2019-03-10' => NULL,
                          '2019-03-11' => NULL, '2019-03-12' => NULL, '2019-03-13' =>
                          array('num-sources-ipv4' => 0.0, 'num-sources-ipv6' => 0.0, // These are floats
                                'num-sources-ipv6-aggregate' => 0.0), '2019-03-14' =>
                          array('num-sources-ipv4' => 0.0, 'num-sources-ipv6' => 0.0,
                                'num-sources-ipv6-aggregate' => 0.0), '2019-03-15' => NULL)));

// Test handle_request()
// TODO: need more test cases here
assert(handle_traffic_volume_request('traffic-volume', 'l', '2019-02-10', '2016-03-01', 1, 'received') === false);
assert(handle_traffic_volume_request('traffic-volume', 'd', '2016-02-03', '2016-02-04', 10, false) ===
       array('d' => array('2016-02-03' => array('dns-udp-queries-received-ipv4' => 511273576,
                                                'dns-udp-queries-received-ipv6' => 43377922,
                                                'dns-tcp-queries-received-ipv4' => 2161459,
                                                'dns-tcp-queries-received-ipv6' => 62967,
                                                'dns-udp-responses-sent-ipv4' => 510078255,
                                                'dns-udp-responses-sent-ipv6' => 43360121,
                                                'dns-tcp-responses-sent-ipv4' => 2160221,
                                                'dns-tcp-responses-sent-ipv6' => 62926),
                          '2016-02-04' => array('dns-udp-queries-received-ipv4' => 483264374,
                                                'dns-udp-queries-received-ipv6' => 43066933,
                                                'dns-tcp-queries-received-ipv4' => 2244825,
                                                'dns-tcp-queries-received-ipv6' => 63334,
                                                'dns-udp-responses-sent-ipv4' => 481613324,
                                                'dns-udp-responses-sent-ipv6' => 43050662,
                                                'dns-tcp-responses-sent-ipv4' => 2242554,
                                                'dns-tcp-responses-sent-ipv6' => 63302))));
assert(handle_traffic_volume_request('traffic-volume', 'f', '2020-09-29', '2020-09-30', 1000, 'sent') ===
       array('f' => array('2020-09-29' => 22728495, '2020-09-30' => 22098756)));
assert(handle_traffic_volume_request('traffic-volume', 'l', '2019-02-10', '2019-02-15', 1, 'sent') ===
       array('l' => array('2019-02-10' => NULL, '2019-02-11' => NULL, '2019-02-12' => NULL,
                          '2019-02-13' => NULL, '2019-02-14' => NULL, '2019-02-15' => 10443937460)));
assert(handle_traffic_volume_request('traffic-volume', 'l', '2016-02-03', '2016-02-04', 1, 'received') ===
       array('l' => array('2016-02-03' => 4809988763, '2016-02-04' => 5136536322)));
assert(handle_traffic_volume_request('traffic-volume', 'b', '2016-02-03', '2016-02-04', 10, 'sent') ===
       array('b' => array('2016-02-03' => 237999435, '2016-02-04' => 236101522)));
assert(handle_traffic_volume_request('traffic-volume', 'a-m', '2020-06-01', '2016-06-31', 1, false) === false);


?>
