<?php
  /**
   * Command line argument parser
   *
   * @author Petr Saganov <saganoff@gmail.com>
   */

class Argparse
{
    protected $_prog;
    protected $_description;
    protected $_action;

    protected $_arguments  = array();
    protected $_options    = array();
    protected $_subparsers = array();

    protected $_context = array();
    protected $_remainder = array();

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
     * @ brief Create a new ArgumentParser object.
     *
     *  @param string $prog        name of the program (default: $argv[0])
     *  @param string $description text to display before the argument help (default: empty)
     *  @param string $action      action that invoked if no arguments are given (default: help)
     */
    public function __construct($prog = null, $description = '', $action = 'help')
    {
        $this->_prog = ($prog ?: $_SERVER['argv'][0]);
        $this->_description = $description;
        if (is_string($action)) $action = array($this, $action);
        $this->_action = $action;

        $this->addArgument('--help -h',
                           array(
                               'action'  => 'help',
                               'nargs'   => 0,
                               'default' => false,
                               'help'    => 'show this help message and exit'));
    }

    public function __get($label)
    {
        return (array_key_exists($label, $this->_context) ? $this->_context[$label] : null);
    }

    public function __isset($label)
    {
        return (array_key_exists($label, $this->_context));
    }

    public function getDescription()
    {
        return $this->_description;
    }

    public function addSubparsers($title = 'subcommands', $description = '', $help = '', $prefix = '')
    {
        $subparsers = new Subparsers($title, $description, $help, $prefix);
        $this->_arguments[] = array(
            'type'       => 'subparsers',
            'name'       => "{{$title}}",
            'usage'      => "{{$title}}",
            'subparsers' => $subparsers,
            'position'   => count($this->_arguments),
            'help'       => $help
                                    );
        return $subparsers;
    }

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
    public function addArgument($name, array $options = array())
    {
        $default = array('action'   => array($this, 'store'),
                         'nargs'    => 1,
                         'const'    => null,
                         'default'  => null,
                         'type'     => 'string',
                         'choices'  => null,
                         'required' => null,
                         'help'     => '');

        $options += $default;
        if(is_string($options['action'])) $options['action'] = array($this, $options['action']);
        if(!is_callable($options['action'])) throw new InvalidArgumentException("Invalid action: {$options['action'][1]}");

        $options['name'] = $name;
        if (strpos($name, '-') === 0) // Optional argument specified
        {
            $options['type'] = 'option';
            $flags = preg_split('/\s/', $name);
            if(count($flags) == 1)
            {
                $options['long'] = $flags[0];
                $options['short'] = false;
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
            $name = $options['name'] = ltrim($options['long'], '-');
            if(is_null($options['required'])) $options['required'] = false;
            $options['usage'] = ($options['short'] ?: $options['long']) . str_repeat(' arg ', $options['nargs']);
            if(!$options['required'])
            {
                $options['usage'] = '['.$options['usage'].']';
            }

            $this->_arguments[$name] = $options;

            $this->_options[$options['long']] = $name;
            if($options['short']) $this->_options[$options['short']] = $name;
        }
        else                              // Positional argument specified
        {
            $options['type'] = 'argument';
            if(is_null($options['required'])) $options['required'] = true;
            $options['usage'] = $name . str_repeat(" $name ", $options['nargs']-1);
            if(!$options['required'])
            {
                $options['usage'] = '['.$options['usage'].']';
            }

            $this->_arguments[$this->nextPosition()] = $options;
        }

        return $this;
    }

    protected function nextPosition()
    {
        return count(array_filter(array_keys($this->_arguments), 'is_numeric'));
    }

    protected function arguments($type = null)
    {
        $type = implode(',', func_get_args());
        $type = array_map('trim', explode(',', $type));

        if(empty($type)) return $this->_arguments;
        else return array_filter($this->_arguments,
                                 function($arg) use ($type) {
                                     return in_array($arg['type'], $type);});
    }

    protected function array2string(array $data, $callback, $wrapper = '%s')
    {
        return ($data ? sprintf($wrapper, array_reduce($data, $callback)) : '');
    }

    protected function usage($format = '%s')
    {
        return sprintf($format,
                       array_reduce(
                           $this->arguments('option'),
                           function($str, $arg){ return $str .= $arg['usage'] .' '; })
                       . array_reduce(
                           $this->arguments('argument', 'subparsers'),
                           function($str, $arg){ return $str .= $arg['usage'] .' '; }));
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

    protected function helpString()
    {
        $arguments = $this->array2string(
            $this->arguments('argument'),
            function($str, $arg){ return $str .= $this->formatArgumentHelp($arg['name'], $arg['help']); },
            "ARGUMENTS:\n%s");
        $options = $this->array2string(
            $this->arguments('option'),
            function($str, $opt){ return $str .= $this->formatArgumentHelp(($opt['short'] ? $opt['short'] .', ' : '') . $opt['long'], $opt['help']); },
            "OPTIONS:\n%s"
                                         );
        $subparsers = $this->array2string(
            $this->arguments('subparsers'),
            function($str, $arg){
                $str .= $arg['subparsers']->title().":\n";
                $description = $arg['subparsers']->description();
                if ($description) $str .= $this->formatText($description, "\t")."\n\n";
                return $str .= $arg['subparsers']->help();
            }
                                          );
        $help = $this->formatText($this->usage("USAGE: {$this->_prog} %s")) ."\n";
        if (!empty($this->_description)) $help .= "\n". $this->formatText($this->_description) ."\n\n";
        if (!empty($arguments))          $help .= $arguments ."\n";
        if (!empty($options))            $help .= $options   ."\n";
        if (!empty($subparsers))         $help .= $subparsers."\n";

        return $help;
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
            if (strpos($arg, '-') === 0) // Optional Argument
            {
                array_shift($args);
                if(isset($this->_options[$arg]))
                {
                    $option = $this->_arguments[$this->_options[$arg]];
                    call_user_func($option['action'],
                                   $option,
                                   array_slice($args, 0, $option['nargs']));
                    $args = array_slice($args, $option['nargs']);
                }
                else // Option has not been specified
                {
                    $remainder[] = $arg;
                }
            }
            else  // Positional Argument
            {
                if (isset($this->_arguments[$position]) && $this->_arguments[$position]['type'] == 'subparsers')
                {
                    $subparsers = $this->_arguments[$position]['subparsers'];
                    $this->_context += $subparsers->parse($args);
                    $args = $subparsers->remainder();
                }
                elseif (isset($this->_arguments[$position]))
                {
                    $argument = $this->_arguments[$position];
                    call_user_func($argument['action'],
                                   $argument,
                                   array_slice($args, 0, $argument['nargs'])
                                   );
                    $this->args = array_slice($args, $argument['nargs']);
                }
                else // Argument has not been specified
                {
                    $remainder[] = $arg;
                    array_shift($args);
                }
                $position++;
            }
        }

        foreach($this->_arguments as $argument)
        {
            if($argument['type'] == 'subparsers') continue;
            $name = $argument['name'];
            if($argument['required'] && !isset($this->_context[$name]))
            {
                throw new MissedRequiredArgumentException("Argument '{$name}' required");
            }
            elseif(!isset($this->_context[$name]) && $argument['default'])
            {
                $this->_context[$name] = $argument['default'];
            }
        }

        $this->_remainder = $remainder;
        return $this->_context;
    }

    public function remainder()
    {
        return $this->_remainder;
    }

    public function debug($property)
    {
        $propert = '_'.$property;
        return (property_exists($this, $property) ? $this->{$property} : null);
    }

    /**
     * Internal commands
     */

    protected function store($argument, $value)
    {
        if(count($value) === 0 && $argument['required'])
        {
            throw new MissedArgumentException("Argument '{$argument['name']}' is required");
        }
        elseif(count($value) === 0)
        {
            $value = $argument['default'];
        }
        elseif(count($val) === 1)
        {
            $value = $value[0];
        }
        $this->_context[$argument['name']] = $value;
    }

    public function help()
    {
        print $this->helpString();
        exit (0);
    }
}

//class InvalidArgumentException extends \Exception {}
class MissedOptionException extends \Exception {}
class MissedArgumentException extends \Exception {}
class MissedRequiredArgumentException extends \Exception {}

class Subparsers
{
    protected $title;
    protected $description;
    protected $help;
    protected $prefix;

    protected $action;
    protected $remainder;

    protected $parsers = array();

    public function __construct($title = 'subcommands', $description = '', $help = '', $prefix = '')
    {
        $this->title = $title;
        $this->description = $description;
        $this->help = $help;
        $this->prefix = $prefix;

        $this->action = function() { $this->printHelp();};
    }

    public function title()
    {
        return $this->title;
    }

    public function description()
    {
        return $this->description;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function addParser($name, $prog = null, $description = '', $action = 'help')
    {
        return $this->parsers[$name] = new Argparse($this->prefix . $prog, $description, $action);
    }

    public function addSubParsers($name, $title = 'subcommands', $description = '', $help = '', $prefix = '')
    {
        return $this->parsers[$name] = new self($title, $description, $help, $prefix);
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