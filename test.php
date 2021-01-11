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

print("Starting tests\n");
assert(true == 1);

// Test parse_letters()
assert(parse_letters("a") == array("a"));
assert(parse_letters("a,c,b") == array("a", "b", "c"));
assert(parse_letters('m,b,l,a-f') == array('a','b','c','d','e','f','l','m'));
assert(parse_letters('b,a-k') == array('a','b','c','d','e','f','g','h','i','j','k'));
assert(parse_letters('h,,,a-c') == array('a','b','c','h'));
assert(parse_letters('-b') == false);
assert(parse_letters('b,a-k,') == false);
assert(parse_letters('n') == false);
assert(parse_letters('g-c') == false);

// Test get_dates()
assert(get_dates('2014-01-30', '2014-02-01') == array(2014 => array('2014-01-30','2014-01-31','2014-02-01')));
assert(get_dates('2014-asds', '2014-02-01') == false);

?>
