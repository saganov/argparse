<?php
  /**
   * Command line argument parser
   *
   * @author Petr Saganov <saganoff@gmail.com>
   */

class Argparse
{
    protected $_context = array();
    protected $_raw = array();
    protected $_arguments = array();
    protected $_options   = array();
    protected $_subparser = array();

    /**
     * Create a new ArgumentParser object.
     *
     * All parameters should be passed as keyword arguments.
     * Each parameter has its own more detailed description below,
     * but in short they are:
     *
     *  prog - The name of the program (default: sys.argv[0])
     *  usage - The string describing the program usage (default: generated from arguments added to parser)
     *  description - Text to display before the argument help (default: none)
     *  epilog - Text to display after the argument help (default: none)
     *  parents - A list of ArgumentParser objects whose arguments should also be included
     *  formatter_class - A class for customizing the help output
     *  prefix_chars - The set of characters that prefix optional arguments (default: ‘-‘)
     *  fromfile_prefix_chars - The set of characters that prefix files from which additional arguments should be read (default: None)
     *  argument_default - The global default value for arguments (default: None)
     *  conflict_handler - The strategy for resolving conflicting optionals (usually unnecessary)
     *  add_help - Add a -h/–help option to the parser (default: True)
     *
     */
    public function __construct($prog = null)
    {
        $this->prog = ($prog ?: $_SERVER['argv'][0]);
    }

    public function init(array $args = array())
    {
        $this->_raw = ($args ?: $this->getRemainder());
        $this->_context = array();
        $this->_arguments = array();
        $this->_options   = array();
        return $this;
    }

    public function __get($label)
    {
        return (array_key_exists($label, $this->_context) ? $this->_context[$label] : null);
    }

    public function __isset($label)
    {
        return (array_key_exists($label, $this->_context));
    }

    public function addSubcontext($name, Context $context)
    {
        $this->_subcontext[$name] = $context;
    }

    public function addArgument($name, $nargs = 1, $default = null, $required = null)
    {
        $arg = array('nargs'    => $nargs,
                     'default'  => $default,
                     'required' => $required);
        if (strpos($name, '-') !== false) // Optional argument specified
        {
            $arg['options'] = preg_split('/\s/', $name);
            $name = ltrim($arg['options'][0], '-');
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