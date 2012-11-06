<?php

require_once "src/ArgumentParser.php";

$parser = new ArgumentParser('Process some integers.');
$parser->addArgument(Argument::create('integers')
                                ->setMetavar('N')
                                ->setType('int') // @todo: Type: INT
                                ->setNargs('+')
                                ->setHelp('an integer for the accumulator'));
$parser->addArgument(Argument::create('--sum')
                                ->setDest('accumulate')
                                ->setAction('store_const')
                                ->setConst('sum') // @todo: Callback SUM
                                ->setDdefault('max') // @todo: Callback MAX
                                ->setHelp('sum the integers (default: find the max)'));

$args = $parser->parseArgs();
print $args->accumulate($args->integers);
