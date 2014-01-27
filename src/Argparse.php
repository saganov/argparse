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

        $this->addArgument('--help -h', 0, null, false, 'show this help message and exit');
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

            $this->_options[$name] = $arg;
        }
        else                              // Positional argument specified
        {
            if(is_null($required)) $arg['required'] = true;
            $arg['position'] = count($this->_arguments);

            $this->_arguments[$name] = $arg;
        }

        $this->_context[$name] = $default;

        return $this;
    }

    protected function array2string(array $data, $callback, $wrapper = '%s')
    {
        return ($data ? sprintf($wrapper, array_reduce($data, $callback)) : '');
    }

    protected function usage()
    {
        return $this->array2string(
            $this->_options,
            function($res, $opt){ return $res .= '['. ($opt['short'] ?: $opt['long']) .'] '; },
            "usage: {$this->_prog} %s"
                                   )
            . $this->array2string(
                $this->_arguments,
                function($res, $arg){
		  if(isset($arg['subparsers']) && is_a($arg['subparsers'], 'Subparsers'))
		  {
		      return $res .= $arg['subparsers']->listNames(', ', '{%s}');
		  }
		  else
		  {
		      return $res .= "{$arg['name']} ";
		  }
		}
                                  );
    }

    protected function help()
    {
        $arguments = $this->array2string(
            $this->_arguments,
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
            $this->_options,
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

    public function parse(array $args = array())
    {
        $this->_raw = ($args ?: array_slice($_SERVER['argv'], 1));

        // Optional Arguments processed
        foreach ($this->_options as $name => $data)
        {
            foreach($data['options'] as $option)
            {
                if(false !== $index = array_search($option, $this->_raw))
                {
                    $this->_context[$name] = true;
                    unset($this->_raw[$index]);
                    if($data['nargs'] == 1)
                    {
                        $index++;
                        if(!isset($this->_raw[$index])) throw new MissedArgumentException("Option '{$name}' requires argument");
                        $this->_context[$name] = $this->_raw[$index];
                        unset($this->_raw[$index]);
                    }
                    elseif($data['nargs'] > 1)
                    {
                        while($data['nargs']--)
                        {
                            $index++;
                            if(!isset($this->_raw[$index])) throw new MissedArgumentException("Missed {$data['nargs']} argument(s) of '{$name}'");
                            $this->_context[$name][] = $this->_raw[$index];
                            unset($this->_raw[$index]);
                        }
                    }
                    break;
                }
            }

            if($data['required'] && (!isset($this->_context[$name]) || is_null($this->_context[$name])))
            {
                throw new MissedOptionException("Option '{$name}' is required");
            }
        }

        // Process Positional arguments
        foreach ($this->_arguments as $name => $data)
        {
            $rawOptions = array();
            $index = 0;
            foreach($this->_raw as $argument)
            {
                if (strpos($argument, '-') !== false)
                {
                    $rawOptions[] = $argument;
                    continue;
                }
                if($index == $data['position'])
                {
                    $this->_context[$name] = $argument;
                    if($data['nargs'] > 1)
                    {
                        $this->_context[$name] = (array)$this->_context[$name];
                        $idx = $index;
                        while($data['nargs']--)
                        {
                            $idx++;
                            if(!isset($this->_raw[$idx])) throw new MissedArgumentException("Missed {$data['nargs']} argument(s) of the {$name}");
                            $this->_context[$name][] = $this->_raw[$idx];
                        }
                    }
                }
                $index++;
            }

            if($data['required'] && (!isset($this->_context[$name]) || is_null($this->_context[$name])))
            {
                throw new MissedArgumentException("Argument '{$name}' is required");
            }
        }
        $this->_raw = $rawOptions + array_slice($this->_raw, count($this->_arguments) + count($rawOptions));

        // Process Sub Contextes
        foreach($this->_raw as $argument)
        {
            if (strpos($argument, '-') !== false)
            {
                $rawOptions[] = $argument;
                continue;
            }

            if(isset($this->_subcontext[$argument]))
            {
                $this->_context += $this->_subcontext[$argument]->parse();
            }
            break;
        }

        return $this->_context;
    }

    public function getRemainder()
    {
        return $this->_raw;
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