<?php

require_once __DIR__.'/../src/ArgumentParser.php';
require_once __DIR__.'/../src/Argument.php';
require_once __DIR__.'/../src/Option.php';
require_once __DIR__.'/../src/SubParsers.php';

echo "====== Test Argument Parser help =====\n";
$main = new ArgumentParser('Test', array('description' => 'Test programm to test'));

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
$commands = $main->addArgument(new SubParsers('Commands', array('description' => 'To see details type COMMAND --help')));
$commandA = $commands->addParser(new ArgumentParser('A', array('description' => 'command A')));
$commandB = $commands->addParser(new ArgumentParser('B', array('description' => 'command B')));
$commandC = $commands->addParser(new ArgumentParser('C', array('description' => 'command C')));
$subcommands = $commands->addParser(new SubParsers('Subcommand', array('description' => 'To see details type SUBCOMMAND --help')));
$subcommandA = $subcommands->addParser(new ArgumentParser('DDD-A', array('description' => 'subcommand A')));
$subcommandB = $subcommands->addParser(new ArgumentParser('DDD-B', array('description' => 'subcommand B')));
$subcommandC = $subcommands->addParser(new ArgumentParser('DDD-C', array('description' => 'subcommand C')));

$remainder = $main->parse();
$value     = $main->value();

echo "REMAINDER: ";
var_dump($remainder);

echo "VALUE: ";
var_dump($value);
