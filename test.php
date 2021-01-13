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
assert(parse_dates('2014-01-30', '2014-02-01') == array(2014 => array('20140130','20140131','20140201')));
assert(parse_dates('2014-asds', '2014-02-01') == false);
assert(parse_dates('2014-02-03', '2014-02-01') == false);

// Test get_metrics_by_date()
assert(get_metrics_by_date('load-time', 'a', '2015-06-30', '2015-07-01') ===
       array('a' => array(20150630 => array('time' => array(2015063000 => 1183, 2015063001 => 267)),
                          20150701 => array('time' => array(2015070100 => 1205, 2015070101 => 530))
                          )));
assert(get_metrics_by_date('load-time', 'b-c', '2017-02-04', '2017-02-05') ===
       array('b' => array(20170204 => NULL, 20170205 => NULL),
             'c' => array(20170204 => array('time' => array(2017020400 => 2, 2017020401 => 2)),
                          20170205 => array('time' => array(2017020500 => 13, 2017020501 => 2))
                          )));
?>
