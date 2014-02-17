<?php

require_once __DIR__.'/../src/ArgumentParser.php';
require_once __DIR__.'/../src/Argument.php';
require_once __DIR__.'/../src/Option.php';
require_once __DIR__.'/../src/SubParsers.php';

echo "====== Test Argument Parser help =====\n";
$main = new ArgumentParser('Test');
$main->_description('Test programm to test');
$main->addArgument(new Option('--hhh -hh'))->_help('HHH optional argument');
$main->addArgument(new Option('--xxx -x'))->_help('XXX optional argument')->_nargs(0);

$commands = $main->addArgument(new SubParsers('Commands'))->_description('To see details type COMMAND --help');

$commands->addParser(new ArgumentParser('A'))->_description('command A')
    ->addArgument(new Option('--zzz -z'))->_help('ZZZ optional argument')->required(true);

$commands->addParser(new ArgumentParser('B'))->_description('command B')
    ->addArgument(new Argument('bar'))->_help('BAR position argument');

$commands->addParser(new ArgumentParser('C'))->_description('command C')
    ->addArgument(new Argument('baz'))->_help('BAZ position argument')->_nargs(3);

$subcommands = $commands->addParser(new SubParsers('D'))->_description('To see details type SUBCOMMAND --help');

$subcommands->addParser(new ArgumentParser('DDD-A'))->_description('subcommand A')
    ->addArgument(new Argument('foo'))->_help('FOO position argument')->_required(false)->_nargs(2);

$subcommands->addParser(new ArgumentParser('DDD-B'))->_description('subcommand B');

$subcommands->addParser(new ArgumentParser('DDD-C'))->_description('subcommand C');

$remainder = $main->parse();
$value     = $main->value();

echo "REMAINDER: ";
var_dump($remainder);

echo "VALUE: ";
var_dump($value);
