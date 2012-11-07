<?php

class Argument
{
    
    /**
     * @var name - an argument or a long option name, e.g. foo, --foo
     */
    protected $name;

    /**
     * @var flags - a list of option aliases,  e.g. -f, --foo-bar.
     */
    protected $aliases = array();
    
    /**
     * @var dest - The name of the attribute to be added to the object returned by parse_args().
     */
    protected $dest;

    /**
     * @var action - The basic type of action to be taken when this argument is encountered at the command line.
     */
    protected $action;

    /**
     * @var nargs - The number of command-line arguments that should be consumed.
     */
    protected $nargs;

    /**
     * @var const - A constant value required by some action and nargs selections.
     */
    protected $const;

    /**
     * @var default - The value produced if the argument is absent from the command line.
     */
    protected $default;

    /**
     * @var type - The type to which the command-line argument should be converted.
     */
    protected $type;

    /**
     * @var choices - A container of the allowable values for the argument.
     */
    protected $choices;
    
    /**
     *  @var required - Whether or not the command-line option may be omitted (optionals only).
     */
    protected $required;

    /**
     * @var help - A brief description of what the argument does.
     */
    protected $help;
    
    /**
     * @var metavar - A name for the argument in usage messages.
     */
    protected $metavar;


    public static function create($name)
    {
        return call_user_func_array(array(static, '__construct'), func_get_args());
    }

    public function __construct($name)
    {
        foreach(func_get_args() as $argv)
        {
            if($this->isLongOption($argv))
            {
                if(empty($this->name))
                {
                    $this->name = $this->trim($argv); //ltrim($name, '-');
                }
                else
                {
                    $this->aliases[] = $this->trim($argv); //ltrim($name, '-');
                }
            }
            elseif($this->isShortOption($argv))
            {
                $this->aliases[] = $this->trim($argv); //ltrim($name, '-');
            }
            else // this is a positional argument
            {
                $this->name = $argv;
                break; // all the rest function arguments are skipped
                $this->aliases = array(); // the positional argument can't have any aliases
            }   
        }

        // if there aren't any long option
        if(!empty($this->aliases) && is_null($this->name))
        {
            $this->name = array_shift($this->aliases);
        }
    }

    public function getDest()
    {
        if(is_null($this->dest))
        {
            $this->dest = $this->name;
        }
        
        return $this->dest;
    }

}
