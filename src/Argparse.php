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

    protected $_arguments  = array();
    protected $_options    = array();
    protected $_subparsers = array();

    protected $_raw     = array();
    protected $_context = array();
    protected $_remainder = array();

    /**
     * @ brief Create a new ArgumentParser object.
     *
     *  @param string $prog        name of the program (default: $argv[0])
     *  @param srring $description text to display before the argument help (default: empty)
     */
    public function __construct($prog = null, $description = '')
    {
        $this->_prog = ($prog ?: $_SERVER['argv'][0]);
        $this->_description = ($description ? "\n\n$description" : '');

        $this->addArgument('--help -h', 0, false, false, 'show this help message and exit');
    }

    public function __get($label)
    {
        return (array_key_exists($label, $this->_context) ? $this->_context[$label] : null);
    }

    public function __isset($label)
    {
        return (array_key_exists($label, $this->_context));
    }

    public function addSubparsers($title = 'subcommands', $description = '', $help = '')
    {
        $subparsers = new Subparsers($title, $description, $help);
        $this->_arguments[] = array(
	    'type'       => 'subparsers',
            'name'       => "{SUBCOMMAND}",
            'subparsers' => $subparsers,
            'position'   => count($this->_arguments),
            'help'       => $help
                                    );
        return $subparsers;
    }

    /*
     * @brief Define how a single command-line argument should be parsed.
     *
     * name or flags - Either a name or a list of option strings, e.g. foo or -f, --foo.
     * action - The basic type of action to be taken when this argument is encountered at the command line.
     * nargs - The number of command-line arguments that should be consumed.
     * const - A constant value required by some action and nargs selections.
     * default - The value produced if the argument is absent from the command line.
     * type - The type to which the command-line argument should be converted.
     * choices - A container of the allowable values for the argument.
     * required - Whether or not the command-line option may be omitted (optionals only).
     * help - A brief description of what the argument does.
     * metavar - A name for the argument in usage messages.
     * dest - The name of the attribute to be added to the object returned by parse_args().
     *
     */
    public function addArgument($name, $nargs = 1, $default = null, $required = null, $help = '')
    {
        $arg = array(
            'name'     => $name,
            'nargs'    => $nargs,
            'default'  => $default,
            'required' => $required,
            'help'     => $help);
        if (strpos($name, '-') !== false) // Optional argument specified
        {
	    $arg['type'] = 'option';
            $option = preg_split('/\s/', $name);
            if(count($option) == 1)
            {
                $arg['long'] = $option[0];
                $arg['short'] = false;
            }
            elseif(strlen($option[0]) > strlen($option[1]))
            {
                $arg['long'] = $option[0];
                $arg['short'] = $option[1];
            }
            else
            {
                $arg['long'] = $option[1];
                $arg['short'] = $option[0];
            }
            $name = $arg['name'] = ltrim($arg['long'], '-');
            if(is_null($required)) $arg['required'] = false;

            $this->_arguments[$name] = $arg;
	    $this->_options[$arg['long']] = $name;
            if($arg['short']) $this->_options[$arg['short']] = $name;
        }
        else                              // Positional argument specified
        {
	    $arg['type'] = 'argument';
            if(is_null($required)) $arg['required'] = true;
            $this->_arguments[$this->nextPosition()] = $arg;
            if(!$arg['required']) $this->_context[$name] = $arg['default'];
        }

        return $this;
    }

    protected function nextPosition()
    {
      return count(array_filter(array_keys($this->_arguments), 'is_numeric'));
    }

    protected function options()
    {
      return array_filter($this->_arguments, function($arg){return $arg['type'] == 'option';});
    }

    protected function positions()
    {
      return array_filter($this->_arguments, function($arg){return in_array($arg['type'], array('argument', 'subparsers'));});
    }

    protected function array2string(array $data, $callback, $wrapper = '%s')
    {
        return ($data ? sprintf($wrapper, array_reduce($data, $callback)) : '');
    }

    protected function usage()
    {
        return
            $this->array2string(
		$this->options(),
                function($res, $arg){ return $res .= '['. ($arg['short'] ?: $arg['long']) .'] '; },
                "usage: {$this->_prog} %s")
            . $this->array2string(
		$this->positions(),
                function($res, $arg){
                    if($arg['type'] == 'subparsers' && is_a($arg['subparsers'], 'Subparsers'))
                    {
                        return $res .= $arg['subparsers']->listNames(', ', '{%s}');
                    }
                    else
                    {
                        return $res .= "{$arg['name']} ";
                    }
                });
    }

    protected function help()
    {
        $arguments = $this->array2string(
	    $this->positions(),
            function($res, $arg){
                if(isset($arg['subparsers']) && is_a($arg['subparsers'], 'Subparsers'))
                {
                    return $res .= "\t". $arg['subparsers']->listNames(', ', '{%s}') ."\t\t{$arg['help']}\n";
                }
                else
                {
                    return $res .= "\t{$arg['name']}\t\t{$arg['help']}\n";
                }
            },
            "positional arguments:\n%s"
                                         );
        $options   = $this->array2string(
	    $this->options(),
            function($res, $opt){ return $res .= "\t". ($opt['short'] ? $opt['short'] .', ' : '') . $opt['long'] ."\t\t{$opt['help']}\n"; },
            "optional arguments:\n%s"
                                         );
        $help = <<<EOD
{$this->usage()}{$this->_description}

{$arguments}
{$options}

EOD;
        return $help;
    }

    public function printHelp()
    {
        echo $this->help();
    }

    protected function extractArgument($argument, &$remainder, $default)
    {
        $val = array();
        $nargs = $argument['nargs'];
        while ($nargs--)
        {
            if(!isset($remainder[0])) throw new MissedArgumentException("Argument '{$argument['name']}' requires '{$argument['nargs']}' argument(s)");
            $val[] = $remainder[0];
            $remainder = array_slice($remainder, 1);
        }

        if(count($val) === 0 && $argument['required'])
        {
            throw new MissedArgumentException("Argument '{$argument['name']}' is required");
        }
        elseif(count($val) === 0)
        {
            $val = $default;
        }
        elseif(count($val) === 1)
        {
            $val = $val[0];
        }

        return $val;
    }

    public function parse(array $args = array())
    {
        $this->_raw = ($args ?: array_slice($_SERVER['argv'], 1));

        $position = 0;
	$remainder = array();
        while (count($this->_raw))
        {
            $arg = $this->_raw[0];
            if (strpos($arg, '-') !== false) // Optional Argument
            {
                if(isset($this->_options[$arg]))
                {
		    $option = $this->_arguments[$this->_options[$arg]];
		    $this->_raw = array_slice($this->_raw, 1);
                    $this->_context[$option['name']] = $this->extractArgument($option, $this->_raw, true);
                }
                else
                { // Option has not been specified
		  $remainder[] = $arg;
		  $this->_raw = array_slice($this->_raw, 1);
                }
            }
            else  // Positional Argument
            {
                if (isset($this->_arguments[$position]) && isset($this->_arguments[$position]['subparsers']) && is_a($this->_arguments[$position]['subparsers'], 'Subparsers'))
                {
		    $subparser = $this->_arguments[$position]['subparsers']->getParser($arg);
		    if(is_null($subparser)) throw new UndeclaredSubparserException("Unknown subparser '{$arg}'");
		    $this->_raw = array_slice($this->_raw, 1);
                    $this->_context += $subparser->parse($this->_raw);
		    $this->_raw = $subparser->remainder();
                }
                elseif (isset($this->_arguments[$position]))
                {
                    $this->_context[$this->_arguments[$position]['name']] = $this->extractArgument($this->_arguments[$position], $this->_raw, $arg);
                }
                else
                { // Argument has not been specified
		  $remainder[] = $arg;
		  $this->_raw = array_slice($this->_raw, 1);
                }
                $position++;
            }
        }

	$this->_remainder = $remainder;
        return $this->_context;
    }

    public function remainder()
    {
        return $this->_remainder;
    }

    public function debug()
    {
      return $this->_context;
    }
}

class MissedOptionException extends \Exception {}
class MissedArgumentException extends \Exception {}

class Subparsers
{
    protected $title;
    protected $description;
    protected $help;

    protected $parsers = array();

    public function __construct($title = 'subcommands', $description = '', $help = '')
    {
        $this->title = $title;
        $this->description = $description;
        $this->help = $help;
    }

    public function addParser($name, $prog = null, $description = '')
    {
        return $this->parsers[$name] = new Argparse($prog, $description);
    }

    public function getParser($name)
    {
        return (isset($this->parsers[$name]) ? $this->parsers[$name] : null);
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
}

class UndeclaredSubparserException extends \Exception {}