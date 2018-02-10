<?php

require_once dirname(__DIR__).'/src/ArgumentParser.php';
require_once dirname(__DIR__).'/src/Argument.php';
require_once dirname(__DIR__).'/src/Option.php';
require_once dirname(__DIR__).'/src/SubParsers.php';

$main = (new ArgumentParser('Test'))->_description('Test programm to test')
    ->add((new Option('--hhh -hh'))->_help('HHH optional argument'))
    ->add((new Option('--xxx -x'))->_help('XXX optional argument')->_nargs(0)->_default(false)->_const(true))

    ->add((new SubParsers('Commands'))->_description('To see details type COMMAND --help')
          ->addParser((new ArgumentParser('A'))->_description('command A')
                      ->add((new Option('--zzz -z'))->_help('ZZZ option with required value')->required(true)))

          ->addParser((new ArgumentParser('B'))->_description('command B')
                      ->add((new Argument('bar'))->_help('BAR position argument')))

          ->addParser((new ArgumentParser('C'))->_description('command C')
                      ->add((new Argument('baz'))->_help('BAZ position argument')->_nargs(3)))

          ->addParser((new SubParsers('D'))->_description('To see details type D --help')
                      ->addParser((new ArgumentParser('DDD-A'))->_description('subcommand DDD-A')
                                  ->add((new Argument('foo'))->_help('FOO position argument')->_required(false)->_nargs(2)))

                      ->addParser((new ArgumentParser('DDD-B'))->_description('subcommand DDD-B'))

                      ->addParser((new ArgumentParser('DDD-C'))->_description('subcommand DDD-C'))));

try{
  $remainder = $main->parse();
  echo "VALUE:\n";
  var_dump($main->value());
  echo "REMAINDER: ";
  var_dump($remainder);
} catch (UndeclaredSubparserException $e) {
  echo 'ERROR: '. $e->getMessage() ."\n";
  echo "\n";
  $main->commandHelp();
} catch (MissedArgumentException $e){
  echo 'ERROR: '. $e->getMessage() ."\n";
  echo "\n";
  $main->commandHelp();
}


var_dump($main->Commands);