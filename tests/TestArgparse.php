<?php

require __DIR__.'/../src/Argparse.php';

echo "====== Test argparser =====\n";
$test = new Argparse('Test');
var_dump($test);


echo "====== Anonimous argparser =====\n";
$anonim = new Argparse();
var_dump($anonim);






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