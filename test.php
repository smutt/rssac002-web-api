#!/usr/local/bin/php
<?php
/* Copyright Andrew McConachie <andrew@depht.com> 2021 */

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

// Test handle_request()
// TODO: need more test cases here
assert(handle_request('traffic-volume', 'l', '2016-02-03', '2016-02-04', 1, true) ===
       array('l' => array('2016-02-03' => 1986120, '2016-02-04' => 1787392)));
assert(handle_request('traffic-volume', 'b', '2016-02-03', '2016-02-04', 10, true) ===
       array('b' => array('2016-02-03' => 59405, '2016-02-04' => 70385)));
assert(handle_request('traffic-volume', 'd', '2016-02-03', '2016-02-04', 10, false) ===
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
?>
