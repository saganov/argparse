argparse
========

The PHP port of the Python argparse module that makes it easy to write
user-friendly command-line interfaces.

Plant UML:
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
 +_isset() : Boolean
 +isRequired() : Boolean
}

abstract class Parser {
 #_name : String
 #_description : String
 #_action : Callback
 #_arguments : Array

 +__construct( name:String, options:Array ) : Parser
 +__get( label:String ) : String
 +__isset( label:String ) : Boolean
 +__invoke( args:Array ) : Mixed

 +description() : String
 +next() : Int

 +help() : String
 +key() : String

 +addArgument( argument:IArgument ) : IArgument

 #arguments( type:String ) : Array
 #missed() : Array

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
 +_isset() : Boolean
 +isRequired() : Boolean
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
 +_isset() : Boolean
 +isRequired() : Boolean
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

interface IParser <|-- interface IArgument
interface IParser <|-- abstract class Parser
interface IArgument <|-- Argument
Argument <|-- Option
interface IArgument <|-- SubParsers
abstract class Parser <|-- ArgumentParser
abstract class Parser <|-- SubParsers
@enduml

Direct link to the UML schema
http://www.plantuml.com:80/plantuml/png/VP113i8W44Ntd6AM6DCRk77PbIQUO4gnaY2I0TnKxwvbWgsnk7iP_dyC61SrdL5fQ8z8GHEC0hOfuA3bvaqNRNq6FvrckgDD4ps5m2v4GXL1MGm15WRi-pqDwQfTbD0M12oGwzmwfIxBAPGcUsJnyIbNpC--kqVJm69Sxgf5LtSMAmAEVtJVuuEFvkRgjVbHbKygSaxz2ysg5m00
