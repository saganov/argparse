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

        $this->addArgument('--help -h',
                           array('nargs'   => 0,
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
        $default = array('action'   => 'store',
                         'nargs'    => 1,
                         'const'    => null,
                         'default'  => null,
                         'type'     => 'string',
                         'choices'  => null,
                         'required' => null,
                         'help'     => '');

        $options += $default;

        $options['name'] = $name;
        if (strpos($name, '-') !== false) // Optional argument specified
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

            $this->_arguments[$name] = $options;

            $this->_options[$options['long']] = $name;
            if($options['short']) $this->_options[$options['short']] = $name;
        }
        else                              // Positional argument specified
        {
            $options['type'] = 'argument';
            if(is_null($options['required'])) $options['required'] = true;

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

    protected function usage()
    {
        return
            $this->array2string(
                $this->arguments('option'),
                function($res, $arg){ return $res .= '['. ($arg['short'] ?: $arg['long']) .'] '; },
                "usage: {$this->_prog} %s")
            . $this->array2string(
                $this->arguments('argument', 'subparsers'),
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
            $this->arguments('argument', 'subparsers'),
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
            $this->arguments('option'),
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

    public function debug()
    {
      return $this->_context;
    }
}

class MissedOptionException extends \Exception {}
class MissedArgumentException extends \Exception {}
class MissedRequiredArgumentException extends \Exception {}

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