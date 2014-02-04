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
    protected static $parser;

    public function __toString()
    {
        return $this->_title;
    }

    public function addParser($name, IParser $parser)
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
        //if(empty($args) && is_callable($this->action)) call_user_func($this->action);
        if(empty($args))
        {
            $parser = self::$parser;
            return $parser($args);
        }

        $arg = array_shift($args);
        self::$parser = $this->getParser($arg);
        if(is_null(self::$parser)) throw new UndeclaredSubparserException("Unknown subparser '{$arg}'");
        return self::$parser->parse($args);
    }

    public function value()
    {
        return self::$parser->value();
    }

    public function usage($format = '%s')
    {
        return sprintf($format, "{{$this->_title}}");
    }

    public function help()
    {
        $help = strtoupper($this->_title) .":\n";
        if (!empty($this->_description)) $help .= "\n". $this->formatText($this->_description) ."\n\n";
        foreach($this->parsers as $name => $parser)
        {
            $help .= $this->formatArgumentHelp($name, $parser->description());
        }

        return $help;
    }

    public function formatArgumentHelp($name, $help, $name_pad = "\t", $help_pad = "\t\t", $glue = "\n")
    {
        $help = $this->formatText($help, $help_pad, 75);
        return "{$name_pad}{$name}$glue{$help}\n";
    }
}

class UndeclaredSubparserException extends \Exception {}