<?php

require_once __DIR__.'/IArgument.php';

class Argument implements IArgument
{
    protected $name;
    protected $value;
    protected $action   = 'store';
    protected $nargs    = 1;
    protected $const    = null;
    protected $default  = null;
    protected $type     = 'string';
    protected $choices  = null;
    protected $required = true;
    protected $help     = '';
    protected $metavar  = null;
    protected $dest     = null;

    /*
     * @brief Define how a single command-line argument should be parsed.
     *
     * @param string $name    Either a name or a list of option strings, e.g. foo or -f --foo.
     * @param array  $options array of argument options:
     *               - action   - The basic type of action to be taken when
     *                           this argument is encountered at the command line.
     *               - nargs    - The number of command-line arguments that
     *                           should be consumed.
     *               - const    - A constant value required by some action
     *                           and nargs selections.
     *               - default  - The value produced if the argument is absent
     *                           from the command line.
     *               - type     - The type to which the command-line argument
     *                           should be converted.
     *               - choices  - A container of the allowable values for
     *                           the argument.
     *               - required - Whether or not the command-line option
     *                           may be omitted (optionals only).
     *               - help     - A brief description of what the argument does.
     *               - metavar  - A name for the argument in usage messages.
     *               - dest     - The name of the attribute to be added to
     *                           the object returned by parse_args().
     */
    public function __construct($name, array $options = array())
    {
        foreach($options as $property => $value)
        {
            if(property_exists($this, $property))
            {
                $this->{$property} = $value;
            }
        }
        $this->name = $name;

        if(is_null($this->metavar))
        {
            $this->metavar = $this->name;
        }
    }

    public function __toString()
    {
        return $this->name;
    }

    public function isRequired()
    {
        return (bool)$this->required;
    }

    public function _isset()
    {
        return isset($this->value);
    }

    /** Helpers */
    public function formatText($text, $pad = "", $wrap = 75)
    {
        return $pad . implode("\n".$pad, explode("\n", wordwrap($text, $wrap - strlen($pad))));
    }
    /** End Helpers */

    public function usage($format = '%s')
    {
        $usage = str_repeat(" {$this->metavar} ", $this->nargs);
        if(!$this->required)
        {
            $usage = '['.$usage.']';
        }
        return sprintf($format, $usage);
    }

    public function help($format = "\t%s\n%s\n")
    {
        $help = $this->formatText($this->help, "\t\t", 75);
        return sprintf($format, $this->name, $help);
    }

    public function parse($args = NULL)
    {
        if (is_callable($this->action))
        {
            call_user_func($this->action,
                           $this,
                           array_slice($args, 0, $this->nargs));
        }
        elseif($this->action == 'store')
        {
            $this->store(array_slice($args, 0, $this->nargs));
        }
        else
        {
            throw new InvalidActionException("Invalid action of the argument '{$this->name}'");
        }
        return array_slice($args, $this->nargs);
    }

    public function value()
    {
        if(!isset($this->value))
        {
            $this->value = $this->default;
        }

        return array($this->name => $this->value);
    }

    /**
     * Internal commands
     */
    protected function store($value)
    {
        if(count($value) === 0 && $this->required)
        {
            throw new MissedArgumentException("Argument '{$this->name}' is required");
        }
        elseif(count($value) === 0)
        {
            $value = $this->default;
        }
        elseif(count($value) === 1)
        {
            $value = $value[0];
        }
        $this->value = $value;
    }
}

class InvalidActionException extends \Exception {}
