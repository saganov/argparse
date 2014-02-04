<?php
  /**
   * Command line argument parser
   *
   * @author Petr Saganov <saganoff@gmail.com>
   */

require_once __DIR__."/Parser.php";
require_once __DIR__."/Option.php";

class ArgumentParser extends Parser
{
    public function __construct($title = null, $description = '', $action = 'help')
    {
        parent::__construct($title, $description, $action);

        $this->addArgument(new Option('--help -h',
                                      array('action'  => array($this, 'CommandHelp'),
                                            'nargs'   => 0,
                                            'default' => false,
                                            'help'    => 'show this help message and exit')));
    }

    public function parse($args = null)
    {
        if(is_null($args)) $args = array_slice($_SERVER['argv'], 1);
        $args = (array)$args;

        if(empty($args) && is_callable($this->_action)) call_user_func($this->_action);

        $position = 0;
        $remainder = array();
        while (count($args))
        {
            $arg = $args[0];
            if (strpos($arg, '-') === 0 && isset($this->_arguments[$arg])) // Optional Argument
            {
                $args = $this->_arguments[$arg]->parse($args);
            }
            elseif (isset($this->_arguments[$position]))                   // Positional Argument
            {
                $args = $this->_arguments[$position]->parse($args);
            }
            else                                               // Argument has not been specified
            {
                $remainder[] = $arg;
                array_shift($args);
            }

            if (strpos($arg, '-') !== 0) $position++;
        }

        $missed = $this->missed();
        if(count($missed))
        {
            throw new MissedArgumentException('Missed required argument(s):'. implode(', ', $missed));
        }
        return $remainder;
    }

    public function value()
    {
        return array_reduce($this->_arguments,
                            function($values, $arg){return $values += $arg->value();},
                            array());
    }

    public function description()
    {
        return $this->_description;
    }

    public function usage($format = '%s')
    {
        return $this->formatText(
            sprintf($format,
                    array_reduce(
                        $this->arguments('Option'),
                        function($str, $arg){ return $str .= $arg->usage() .' '; })
                    . array_reduce(
                        $this->arguments('Argument', 'SubParsers'),
                        function($str, $arg){ return $str .= $arg->usage() .' '; })));
    }

    public function help($print = false)
    {
        $arguments = $this->array2string(
            $this->arguments('Argument'),
            function($str, $arg){ return $str .= $arg->help(); },
            "ARGUMENTS:\n%s");

        $options = $this->array2string(
            $this->arguments('Option'),
            function($str, $opt){ return $str .= $opt->help(); },
            "OPTIONS:\n%s");

        $subparsers = $this->array2string(
            $this->arguments('SubParsers'),
            function($str, $arg){ return $str .= $arg->help(); });

        $help = $this->formatText($this->usage("USAGE: {$this->_title} %s")) ."\n";
        if (!empty($this->_description)) $help .= "\n". $this->formatText($this->_description) ."\n\n";
        if (!empty($arguments))          $help .= $arguments ."\n";
        if (!empty($options))            $help .= $options   ."\n";
        if (!empty($subparsers))         $help .= $subparsers."\n";

        if($print) print $help;
        return $help;
    }

    public function CommandHelp()
    {
        $this->help(true);
        exit(0);
    }
}

class MissedArgumentException extends \Exception {}