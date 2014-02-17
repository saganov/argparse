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
        return $this->_name;
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

    public function addParser(Parser $parser)
    {
        return $this->parsers[$parser->_name()] = $parser;
    }

    public function getParser($name)
    {
        return (isset($this->parsers[$name]) ? $this->parsers[$name] : null);
    }

    public function parse($args = null)
    {
        $remainder = array();
        if(empty($args) && is_callable($this->_action))
        {
            call_user_func($this->_action);
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
        return sprintf($format, "{{$this->_name}}");
    }

    public function help($format = "%s\n%s\n")
    {
        return parent::help($format)
            . array_reduce($this->parsers,
                           function($help, $parser) use($format) {
                               return $help .= $parser->help("\t".$format);});
    }
}

class UndeclaredSubparserException extends \Exception {}