<?php

require __DIR__.'/../src/Argparse.php';

echo "====== Test argparser =====\n";
$test = new Argparse('Test', 'Test programm to test');
//var_dump($test);
$test->addArgument('bar');
$test->addArgument('zab');
$test->addArgument('--foo');
$test->addArgument('--hah');


$subparsers = $test->addSubparsers('subcommands',
                                   'valid subcommands',
                                   'Subcommands help');
// create the parser for the "a" command
$parser_a = $subparsers->addParser('a'); //('a', help='a help');
$parser_a->addArgument('bazaa'); //('bar', type=int, help='bar help');

// create the parser for the "b" command
$parser_b = $subparsers->addParser('b'); //('b', help='b help');
$parser_b->addArgument('--bazbb'); //('--baz', choices='XYZ', help='baz help');

$test->printHelp();
$input = array('BAR', 'ZAB', '--hah', 'HAH', 'a', 'BAZAA');
printf("INPUT: %s\n", implode(' ', $input));
$test->parse($input);
printf("CONTEXT: ");
var_dump($test->debug());


echo "====== Anonimous argparser =====\n";
$anonim = new Argparse();
//var_dump($anonim);
$anonim->addArgument('bar');

$subparsers = $anonim->addSubparsers('subcommands',
                                   'valid subcommands',
                                   'Subcommands help');
// create the parser for the "a" command
$parser_a = $subparsers->addParser('a'); //('a', help='a help');
$parser_a->addArgument('bazaa'); //('bar', type=int, help='bar help');

// create the parser for the "b" command
$parser_b = $subparsers->addParser('b'); //('b', help='b help');
$parser_b->addArgument('--bazbb'); //('--baz', choices='XYZ', help='baz help');

$anonim->printHelp();

$input = array('BAR', 'b', '--bazbb', 'BAZBB');
printf("INPUT: %s\n", implode(' ', $input));
$anonim->parse($input);
printf("CONTEXT: ");
var_dump($anonim->debug());








/*
$context->addArgument('command');

$install = new ImplNS\Lib\Cli\Context();

$context->addSubcontext($install);
$context->addSubcontext($instance);

$context->addArgument('sub_command');
$context->addArgument('--force -f', 0, false);
$context->addArgument('instance');
$context->addArgument('version', 1, 'v4.5.11', false);
$context->addArgument('dump', 1, null, false);


var_dump($context->parse());

echo "COMMAND: {$context->command}\n";
echo "SUB-COMMAND: {$context->sub_command}\n";
echo "INSTANCE: {$context->instance}\n";
echo "--FORCE: {$context->force}\n";

var_dump('REMAINDER: ', $context->getRemainder());
*/
?>