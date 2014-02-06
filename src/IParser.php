<?php

interface IParser
{
    public function usage($format = '%s');
    public function help();
    public function parse($args = null);
    public function value();
    public function key();
}