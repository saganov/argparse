<?php
  /**
   * Subparsers of Command line argument parser
   *
   * @author Petr Saganov <saganoff@gmail.com>
   */

require_once __DIR__.'/Parser.php';
require_once __DIR__.'/IArgument.php';

class SubParsers extends Parser implements IArgument
{
    protected $parsers = array();
    protected $parser;

    public function __toString()
    {
        return $this->_title;
    }

    public function isRequired()
    {
        return true;
    }

    public function _isset()
    {
        return isset($this->parser);
    }

    public function key()
    {
        return null;
    }

    public function addParser($name, Parser $parser)
    {
        return $this->parsers[$name] = $parser;
    }

    public function getParser($name)
    {
        return (isset($this->parsers[$name]) ? $this->parsers[$name] : null);
    }

    public function parse($args = null)
    {
        $remainder = array();
        if(empty($args) && is_callable($this->action))
        {
            call_user_func($this->action);
            return $args;
        }

        $arg = array_shift($args);
        $this->parser = $this->getParser($arg);
        if(is_null($this->parser)) throw new UndeclaredSubparserException("Unknown subparser '{$arg}'");
        return $this->parser->parse($args);
    }

    public function value()
    {
        return ($this->parser ? $this->parser->value() : array());
    }

    public function usage($format = '%s')
    {
        return sprintf($format, "{{$this->_title}}");
    }

    public function help()
    {
        $help = strtoupper($this->_title) .":\n";
        if (!empty($this->_description)) $help .= "\n". $this->formatText($this->_description) ."\n\n";
        return $help . array_reduce($this->parsers,
                                    function($help, $parser){return $help .= $parser->help();});
    }

    public function formatArgumentHelp($name, $help, $name_pad = "\t", $help_pad = "\t\t", $glue = "\n")
    {
        $help = $this->formatText($help, $help_pad, 75);
        return "{$name_pad}{$name}$glue{$help}\n";
    }
}

class UndeclaredSubparserException extends \Exception {}