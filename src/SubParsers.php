<?php
  /**
   * Command line argument parser
   *
   * @author Petr Saganov <saganoff@gmail.com>
   */

require_once __DIR__.'/Parser.php';
require_once __DIR__.'/IArgument.php';
require_once __DIR__.'/ArgumentParser.php';

class SubParsers extends Parser implements IArgument
{
    protected $parsers = array();

    public function addParser($name, $title = null, $description = '', $action = 'help')
    {
        return $this->parsers[$name] = new ArgumentParser($title, $description, $action);
    }

    public function getParser($name)
    {
        return (isset($this->parsers[$name]) ? $this->parsers[$name] : null);
    }

    public function parse($args)
    {
        $this->remainder = array();
        if(empty($args) && is_callable($this->action)) call_user_func($this->action);

        $arg = array_shift($args);
        $subparser = $this->getParser($arg);
        if(is_null($subparser)) throw new UndeclaredSubparserException("Unknown subparser '{$arg}'");

        $context = $subparser->parse($args);
        $this->remainder = $subparser->remainder();

        return $context;
    }

    public function remainder()
    {
        return $this->remainder;
    }

    public function listParsers()
    {
        return $this->parsers;
    }

    public function listNames($separator = null, $format = '%s')
    {
        $list = array_keys($this->parsers);
        if (is_null($separator)) return $list;
        else return sprintf($format, implode($separator, $list));
    }

    public function formatArgumentHelp($name, $help, $name_pad = "\t", $help_pad = "\t\t", $glue = "\n")
    {
        $help = $this->formatText($help, $help_pad, 75);
        return "{$name_pad}{$name}$glue{$help}\n";
    }

    public function formatText($text, $pad = "", $wrap = 75)
    {
        return $pad . implode("\n".$pad, explode("\n", wordwrap($text, $wrap - strlen($pad))));
    }

    public function help()
    {
        $help = '';
        foreach($this->parsers as $name => $parser)
        {
            $help .= $parser->formatArgumentHelp($name, $parser->getDescription());
        }
        return $help;
    }

    protected function printHelp()
    {
        $help = $this->formatText("USAGE: VM INSTANCE {{$this->title}}") ."\n";
        if (!empty($this->description)) $help .= "\n". $this->formatText($this->description) ."\n\n";
        $help .= $this->title .":\n". $this->help();
        print $help ."\n";
        exit (0);
    }
}

class UndeclaredSubparserException extends \Exception {}