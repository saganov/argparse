<?php

class Argument
{
    
    /**
     * @var name - an argument name, e.g. foo
     */
    protected $name;

    /**
     * @var flags - a list of option strings, e.g. -f, --foo.
     */
    protected $shortOption;
    protected $longOption;
    
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
        $args = func_num_args()
        
        if($args < 1)
        {
            throw new Exception("Too few method's argument");
        }
        else
        {
            $name = array_shift($args);
            $short = $dest = $help = NULL;
        }

        if(!empty($args))
        {
            $short = !$this->isOption($name) ? NULL: array_shift($args);            
        }
       
        if(!empty($args))
        {
            $dest = array_shift($args);
        }

        if(!empty($args))
        {
            $help = array_shift($args);
        }

        return const($name, $short, $dest, $help);
    }

    public function __construct($name, $short = NULL, $dest=NULL, $help=NULL)
    {
        if($this->isOption($name))
        {
            $this->longName = $this->trim($name); //ltrim($name, '-');
        }
        else
        {
            $this->name = $name;
        }

        if(!is_null($short))
        {
            $this->shortName = $this->trim($short);
        }

        $this->dest = !is_null($dest) ? $dest : $this->trim($name);

        $this->help = $help;
    }

}
