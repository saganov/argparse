<?php
  /**
   * Command line argument parser abstraction
   *
   * @author Petr Saganov <saganoff@gmail.com>
   */

require_once __DIR__."/IParser.php";

abstract class Parser implements IParser
{
    protected $_name;
    protected $_description;
    protected $_action = 'help';

    protected $_arguments  = array();

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
        foreach($options as $property => $value)
        {
            $property = '_'.$property;
            if(property_exists($this, $property))
            {
                $this->{$property} = $value;
            }
        }
        $this->_name = ($name ?: $_SERVER['argv'][0]);
        if (is_string($this->_action)) $this->_action = array($this, 'command'. ucfirst($this->_action));
        /** @todo action validation is needed */
    }

    public function __get($label)
    {
        return (array_key_exists($label, $this->_arguments) ? current($this->_arguments[$label]->value()) : null);
    }

    public function __isset($label)
    {
        return (array_key_exists($label, $this->_arguments) ? $this->_arguments[$label]->_isset() : false);
    }

    public function __invoke($args)
    {
        if (is_callable($this->_action)) return call_user_func($this->_action, $args);
    }

    public function description()
    {
        return $this->_description;
    }

    public function key()
    {
        return $this->_name;
    }

    protected function next()
    {
        return count(array_filter(array_keys($this->_arguments), 'is_numeric'));
    }

    /*
     * @brief Define how a single command-line argument should be parsed.
     *
     * @param IArgument $argument  Either a positioninig argument
     *                             or optional argumant or subparsers object.
     *
     */
    public function addArgument(IArgument $argument)
    {
        $key = (array)($argument->key() ?: $this->next());
        array_walk($key,
                   function($aliace) use($argument){
                       $this->_arguments[$aliace] = $argument;});

        return $argument;
    }

    protected function arguments($type = null)
    {
        if(is_null($type)) return array_unique($this->_arguments);

        $type = implode(',', func_get_args());
        $type = array_map('trim', explode(',', $type));

        return array_unique(
            array_filter($this->_arguments,
                         function($arg) use ($type) {
                             return in_array(get_class($arg), $type);}));
    }

    protected function missed()
    {
        return array_filter(
            $this->arguments(),
            function($arg) {
                return ($arg->isRequired() && !$arg->_isset()); });
    }

    public function help($format = "\t%s\n%s\n")
    {
        $pad = str_repeat("\t", strlen($format) - strlen(ltrim($format)) + 1);
        $help = $this->formatText($this->_description, $pad);
        return sprintf($format, $this->_name, $help);
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