<?php
  /**
   * Command line argument parser abstraction
   *
   * @author Petr Saganov <saganoff@gmail.com>
   */

require_once __DIR__."/IParser.php";

abstract class Parser implements IParser
{
    protected $name;
    protected $description;
    protected $action = 'help';
    protected $values = array();

    protected $arguments  = array();

    /*
      prog - The name of the program (default: sys.argv[0])
      usage - The string describing the program usage (default: generated from arguments added to parser)
      description - Text to display before the argument help (default: none)
      epilog - Text to display after the argument help (default: none)
      parents - A list of ArgumentParser objects whose arguments should also be included
      formatter_class - A class for customizing the help output
      prefix_chars - The set of characters that prefix optional arguments (default: ‘-‘)
      fromfile_prefix_chars - The set of characters that prefix files from which additional arguments should be read (default: None)
      argument_default - The global default value for arguments (default: None)
      conflict_handler - The strategy for resolving conflicting optionals (usually unnecessary)
      add_help - Add a -h/–help option to the parser (default: True)
     */

    /**
     * @ brief Create a new Parser object.
     *
     *  @param string $name        name of the program (default: $argv[0])
     *  @param string $description text to display before the argument help (default: empty)
     *  @param string $action      action that invoked if no arguments are given (default: help)
     */
    public function __construct($name = null, array $options = array())
    {
        $this->name = ($name ?: $_SERVER['argv'][0]);
        array_walk($options, function($value, $property){
                $this->__call($property, array($value));});
    }

    public function __get($label)
    {
      //return (array_key_exists($label, $this->arguments) ? current($this->arguments[$label]->value()) : null);
      return (array_key_exists($label, $this->arguments) ? $this->arguments[$label]->value() : null);
    }

    public function __isset($label)
    {
      return (array_key_exists($label, $this->arguments) ? $this->arguments[$label]->isset() : false);
    }

    public function __call($name, $arguments)
    {
        $name = strtolower(ltrim($name, '_'));
        if(!property_exists($this, $name))
        {
            throw new ParserException('Unknown method/property: '. $name);
        }
        elseif(empty($arguments) && method_exists($this, 'get'. ucfirst($name)))
        {
            return call_user_func(array($this, 'get'. ucfirst($name)));
        }
        elseif(empty($arguments))
        {
            return $this->{$name};
        }
        elseif(method_exists($this, 'set'. ucfirst($name)))
        {
            call_user_func_array(array($this, 'set'. ucfirst($name)), $arguments);
        }
        elseif(count($arguments) === 1)
        {
            $this->{$name} = $arguments[0];
        }
        else
        {
            throw new ParserException('To many arguments');
        }

        return $this;
    }

    protected function setAction($action)
    {
        if (is_string($action)) $action = array($this, 'command'. ucfirst($action));
        $this->action = $action;
    }

    public function __invoke($args = null)
    {
        if (is_string($this->action)) $this->setAction($this->action);
        if (is_callable($this->action))
        {
            call_user_func($this->action, (object)$this->value());
            exit (0);
        }
    }

    public function key()
    {
        return $this->name;
    }

    protected function next()
    {
        return count(array_filter(array_keys($this->arguments), 'is_numeric'));
    }

    /*
     * @brief Define how a single command-line argument should be parsed.
     *
     * @param IArgument $argument  Either a positioninig argument
     *                             or optional argumant or subparsers object.
     *
     */
    public function add(IArgument $argument)
    {
        $key = (array)($argument->key() ?: array($this->next(), $argument->_name()));
        array_walk($key,
                   function($aliace) use($argument){
                       $this->arguments[$aliace] = $argument;});

        return $this;
    }

    protected function arguments($type = null)
    {
        if(is_null($type)) return array_unique($this->arguments);

        $type = implode(',', func_get_args());
        $type = array_map('trim', explode(',', $type));

        return array_unique(
            array_filter($this->arguments,
                         function($arg) use ($type) {
                             return in_array(get_class($arg), $type);}));
    }

    public function help($format = "\t%s\n%s\n")
    {
        $pad = str_repeat("\t", strlen($format) - strlen(ltrim($format)) + 1);
        $help = $this->formatText($this->description, $pad);
        return sprintf($format, $this->name, $help);
    }

    /** Helpers */
    protected function array2string(array $data, $callback, $wrapper = '%s')
    {
        return ($data ? sprintf($wrapper, array_reduce($data, $callback)) : '');
    }

    public function formatText($text, $pad = "", $wrap = 75)
    {
        return $pad . implode("\n".$pad, explode("\n", wordwrap($text, $wrap - strlen($pad))));
    }
    /** End Helpers */

    /**
     * Internal commands
     */

    protected function commandStore($argument, $value)
    {
        /** No action is needed, because value already stored during parsing */
    }

    public function commandHelp()
    {
        print $this->help();
        exit(0);
    }
}

class ParserException extends \Exception{}
