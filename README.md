argparse
========

The PHP port of the Python argparse module that makes it easy to write
user-friendly command-line interfaces.

Plant UML:
```uml
@startuml
interface IParser {
 +usage( format:String ) : String
 +help()
 +parse( args:Array ) : Array
 +value() : Array
 +key()
}

interface IArgument {
 +__toString() : String
}

abstract class Parser {
 #name : String
 #description : String
 #action : Callback
 #remainder : Array

 #arguments : Array

 +__construct( name:String, options:Array ) : Parser
 +__get( label:String ) : String
 +__isset( label:String ) : Boolean
 +__call( name:String, arguments:Array ) : IArgument | Mixed
 +__invoke( args:Array ) : Mixed

 +key() : String
 +next() : Int

 +help() : String

 +addArgument( argument:IArgument ) : IArgument

 #arguments( type:String ) : Array

 #array2string( data:Array, callback:Callback, wrapper:String ) : String
 #formatText( text:String, pad:String, wrap:Int ) : String

 #commandStore( argument, value ) : void
 +commandHelp()
}

class ArgumentParser {
 +__construct( name:String, options:Array ) : ArgumentParser

 +usage( format:String ) : String
 +parse( args:Array ) : Array
 +value() : Array

 +commandHelp()
}

class SubParsers {
 #parsers : Array
 #parser : Parser

 +__toString() : String
 +key()

 +usage() : String
 +help() : String
 +parse( args:Array ) : Array
 +value() : Array

 +addParser( parser:Parser ) : Parser
 +getParser( name:String ) : Parser

 +formatArgumentHelp( name:String, help:String, name_pad:String, help_pad:String, glue:String ) : String
}

class Argument {
 #name : String
 #value : String
 #action = 'store' : String|Callback
 #nargs  = 1 : Int
 #const
 #default
 #type     = 'string' : String
 #choices
 #required = true : Boolean
 #help     = '' : String
 #metavar
 #dest

 +__construct( name:String, options:Array ) : Argument
 +__toString() : String
 +__call( name:String, arguments:Array ) : IArgument | Mixed
 +key()

 +usage( format:String ) : String
 +help( format:String ) : String
 +parse( args:Array ) : Array
 +value() : Array

 +formatText( text:String, pad:String, wrap:Int ) : String

 +store( value ) : void
}

class Option {
 #required = false : Boolean
 #short = false : String
 #long : String

 +__construct( name:String, options:Array ) : Argument
 +key() : String | Arrray

 +usage( format:String ) : String
 +help( format:String ) : String
 +parse( args:Array ) : Array
}

IParser <|-- IArgument
IParser <|-- Parser
IArgument <|-- Argument
Argument <|-- Option
IArgument <|-- SubParsers
Parser <|-- ArgumentParser
Parser <|-- SubParsers
@enduml
```

Direct link to the UML schema: https://goo.gl/5Ehc3q
