<?php

interface IArgument extends IParser
{
    public function __toString();
    public function _isset();
    public function isRequired();
    public function key();
}