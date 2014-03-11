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
    public function __construct($name = null, array $options = array())
    {
        parent::__construct($name, $options);
        $this->add((new Option('--help -h'))
                   ->_action(array($this, 'commandHelp'))
                   ->_nargs(0)
                   ->_help('show this help message and exit'));
    }

    public function parse($args = null)
    {
        if(is_null($args)) $args = array_slice($_SERVER['argv'], 1);
        $args = (array)$args;

        if(empty($args)) $this($args);

        $position = 0;
        while (count($args))
        {
            $arg = $args[0];
            if (strpos($arg, '-') === 0 && isset($this->arguments[$arg])) // Optional Argument
            {
                $args = $this->arguments[$arg]->parse($args);
            }
            elseif (isset($this->arguments[$position]))                   // Positional Argument
            {
                $args = $this->arguments[$position]->parse($args);
            }
            else                                               // Argument has not been specified
            {
                $this->remainder[] = $arg;
                array_shift($args);
            }

            if (strpos($arg, '-') !== 0) $position++;
        }

        return $this->value();
    }

    public function value()
    {
        $values = array();
        foreach($this->arguments as $arg)
        {
            $values += $arg->value();
        }
        return $values;
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

    /**
     * Internal commands
     */

    public function commandHelp()
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

        $help = $this->formatText($this->usage("USAGE: {$this->name} %s")) ."\n";
        if (!empty($this->description)) $help .= "\n". $this->formatText($this->description) ."\n\n";
        if (!empty($arguments))          $help .= $arguments ."\n";
        if (!empty($options))            $help .= $options   ."\n";
        if (!empty($subparsers))         $help .= $subparsers."\n";

        print $help;
        exit(0);
    }
}

class MissedArgumentException extends \Exception {}