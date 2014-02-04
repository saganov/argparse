<?php
  /**
   * Command line argument parser abstraction
   *
   * @author Petr Saganov <saganoff@gmail.com>
   */

require_once __DIR__."/IParser.php";

abstract class Parser implements IParser
{
    protected $_title;
    protected $_description;
    protected $_action;

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
     *  @param string $title       name of the program (default: $argv[0])
     *  @param string $description text to display before the argument help (default: empty)
     *  @param string $action      action that invoked if no arguments are given (default: help)
     */
    public function __construct($title = null, $description = '', $action = 'help')
    {
        $this->_title = ($title ?: $_SERVER['argv'][0]);
        $this->_description = $description;
        if (is_string($action)) $action = array($this, 'Command'. ucfirst($action));
        /** @todo action validation is needed */
        $this->_action = $action;
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

    protected function key()
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
        if(is_a($argument, 'Option'))
        {
            $this->_arguments[$argument->key('long')] = $argument;
            if ($argument->key('short')) $this->_arguments[$argument->key('short')] = $argument;
        }
        else
        {
            $this->_arguments[$this->key()] = $argument;
        }

        return $this;
    }

    public function addSubParsers(SubParsers $subparsers)
    {
        $this->addArgument($subparsers);
        return $subparsers;
    }

    protected function arguments($type = null)
    {
        $type = implode(',', func_get_args());
        $type = array_map('trim', explode(',', $type));

        if(empty($type)) return array_unique($this->_arguments);
        else return array_unique(
            array_filter($this->_arguments,
                         function($arg) use ($type) {
                             return in_array(get_class($arg), $type);}));
    }

    protected function missed()
    {
        return array_unique(
            array_filter($this->_arguments,
                         function($arg) { return ($arg->isRequired() && !$arg->_isset()); }));
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


    public function debug($property)
    {
        $propert = '_'.$property;
        return (property_exists($this, $property) ? $this->{$property} : null);
    }

    /**
     * Internal commands
     */

    protected function CommandStore($argument, $value)
    {
        /** No action is needed, because value already stored during parsing */
    }

    public function CommandHelp()
    {
        print $this->help();
        exit (0);
    }
}