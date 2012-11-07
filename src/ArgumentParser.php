<?php

class ArgumentParser
{
    protected $description;

    protected $arguments = array();

    public function __construct($description = NULL)
    {
        $this->description = $description;
    }
   
    public function addArgument(Argument $argument)
    {
        $this->arguments[$argument->getDest()] = $argument;

        foreach($argument->getAliases() as $alias)
        {
            $this->arguments[$alias] = $argument;
        }
    }

    public function parseArgs()
    {
        
    }

}


