<?php

require_once __DIR__.'/Argument.php';

class Option extends Argument
{
    protected $required = false;
    protected $short = false;
    protected $long;

    public function __construct($name, array $options = array())
    {
        if (strpos($name, '-') === 0) // Flags in the name
        {
            $flags = preg_split('/\s/', $name);
            if(count($flags) == 1)
            {
                $options['long'] = $flags[0];
            }
            elseif(strlen($flags[0]) > strlen($flags[1]))
            {
                $options['long'] = $flags[0];
                $options['short'] = $flags[1];
            }
            else
            {
                $options['long'] = $flags[1];
                $options['short'] = $flags[0];
            }
            $name = ltrim($options['long'], '-');
        }

        parent::__construct($name, $options);
        if(!isset($this->long)) $this->long = "--{$name}";
        $this->metavar = strtoupper($this->metavar);
    }

    public function key($label)
    {
        if (in_array($label, array('short', 'long'))) return $this->{$label};
    }

    public function parse($args = NULL)
    {
        array_shift($args);
        return parent::parse($args);
    }

    public function usage($format = '%s')
    {
        $usage = ($this->short ?: $this->long) . str_repeat(" {$this->metavar} ", $this->nargs);
        if(!$this->required)
        {
            $usage = '['.$usage.']';
        }
        return sprintf($format, $usage);
    }

    public function help($format = "\t%s\n%s\n")
    {
        $help = $this->formatText($this->help, "\t\t", 75);
        $name = ($this->short ? $this->short .', ' : '') . $this->long;
        return sprintf($format, $name, $help);
    }
}