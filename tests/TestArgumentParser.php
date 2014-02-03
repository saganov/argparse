<?php

require_once __DIR__.'/../src/ArgumentParser.php';
require_once __DIR__.'/../src/Argument.php';
require_once __DIR__.'/../src/Option.php';

echo "====== Test Argument Parser help =====\n";
$test = new ArgumentParser('Test', 'Test programm to test');

$test->addArgument(new Option('--hhh -hh', array('help'     => 'HHH optional argument')));
$test->addArgument(new Option('--zzz -z', array('help'     => 'ZZZ optional argument',
                                                'required' => true)));
$test->addArgument(new Option('--xxx -x', array('help'     => 'XXX optional argument',
                                                'nargs'    => 0)));

$test->addArgument(new Argument('bar', array('help'     => 'BAR position argument'))); 
$test->addArgument(new Argument('baz', array('help'     => 'BAZ position argument',
                                             'nargs'    =>  3)));
$test->addArgument(new Argument('foo', array('help'     => 'FOO position argument',
                                             'required' => false,
                                             'nargs'    =>  2)));

$remainder = $test->parse();
$value     = $test->value();

echo "REMAINDER: ";
var_dump($remainder);

echo "VALUE: ";
var_dump($value);
