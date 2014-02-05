<?php

require_once __DIR__.'/../src/ArgumentParser.php';
require_once __DIR__.'/../src/Argument.php';
require_once __DIR__.'/../src/Option.php';
require_once __DIR__.'/../src/SubParsers.php';

echo "====== Test Argument Parser help =====\n";
$main = new ArgumentParser('Test', 'Test programm to test');

$main->addArgument(new Option('--hhh -hh', array('help'     => 'HHH optional argument')));
$main->addArgument(new Option('--zzz -z', array('help'     => 'ZZZ optional argument',
                                                'required' => true)));
$main->addArgument(new Option('--xxx -x', array('help'     => 'XXX optional argument',
                                                'nargs'    => 0)));

$main->addArgument(new Argument('bar', array('help'     => 'BAR position argument'))); 
$main->addArgument(new Argument('baz', array('help'     => 'BAZ position argument',
                                             'nargs'    =>  3)));
$main->addArgument(new Argument('foo', array('help'     => 'FOO position argument',
                                             'required' => false,
                                             'nargs'    =>  2)));
$subparsers = $main->addArgument(new SubParsers('Subcommands', 'To see details type COMMAND --help'));
$parserA = $subparsers->addParser('A', new ArgumentParser('A', 'subcommand A'));
$parserB = $subparsers->addParser('B', new ArgumentParser('B', 'subcommand B'));
$parserC = $subparsers->addParser('C', new ArgumentParser('C', 'subcommand C'));

$remainder = $main->parse();
$value     = $main->value();

echo "REMAINDER: ";
var_dump($remainder);

echo "VALUE: ";
var_dump($value);
