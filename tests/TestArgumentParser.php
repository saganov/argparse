<?php

require_once __DIR__.'/../src/ArgumentParser.php';
require_once __DIR__.'/../src/Argument.php';
require_once __DIR__.'/../src/Option.php';
require_once __DIR__.'/../src/SubParsers.php';

echo "====== Test Argument Parser help =====\n";
$main = (new ArgumentParser('Test'))->_description('Test programm to test')
    ->add((new Option('--hhh -hh'))->_help('HHH optional argument'))
    ->add((new Option('--xxx -x'))->_help('XXX optional argument')->_nargs(0)->_default(false)->_const(true))

    ->add((new SubParsers('Commands'))->_description('To see details type COMMAND --help')
          ->addParser((new ArgumentParser('A'))->_description('command A')
                      ->add((new Option('--zzz -z'))->_help('ZZZ optional argument')->required(true)))

          ->addParser((new ArgumentParser('B'))->_description('command B')
                      ->add((new Argument('bar'))->_help('BAR position argument')))

          ->addParser((new ArgumentParser('C'))->_description('command C')
                      ->add((new Argument('baz'))->_help('BAZ position argument')->_nargs(3)))

          ->addParser((new SubParsers('D'))->_description('To see details type D --help')
                      ->addParser((new ArgumentParser('DDD-A'))->_description('subcommand A')
                                  ->add((new Argument('foo'))->_help('FOO position argument')->_required(false)->_nargs(2)))

                      ->addParser((new ArgumentParser('DDD-B'))->_description('subcommand B'))

                      ->addParser((new ArgumentParser('DDD-C'))->_description('subcommand C'))));

echo "VALUE: ";
var_dump($main->parse());

echo "REMAINDER: ";
var_dump($main->_remainder());

