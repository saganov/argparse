argparse
========

The PHP port of the Python argparse module that makes it easy to write
user-friendly command-line interfaces.

Plant UML:
@startuml
interface IParser {
 +usage()
 +help()
 +parse()
 +value()
}

abstract class Parser {
 #_title : String
 #_description : String
 #_action : Callback
 #_arguments : Array

 +__construct( title:String, description:String, action:String | Callback ) : Parser
 +__get( label:String ) : String
 +__isset( label:String ) : Boolean
 +__invoke( args:Array ) : Mixed
 +description() : String
 +key() : Int
 +addArgument( argument:IArgument ) : Parser
 +addSubParsers( subparsers:SubParsers ) : SubParsers

 #arguments( type:String ) : Array
 #missed() : Array
 #array2string( data:Array, callback:Callback, wrapper:String ) : String
 #formatText( text:String, pad:String, wrap:Int )
}

class Subparsers {
 +addParser( Parser parser )
}

interface IParser <|-- interface IArgument
interface IParser <|-- abstract class Parser
interface IArgument <|-- Argument
Argument <|-- Option
interface IArgument <|-- Subparsers
abstract class Parser <|-- Argparser
abstract class Parser <|-- Subparsers
@enduml

Direct link to the UML schema
http://www.plantuml.com:80/plantuml/png/VP113i8W44Ntd6AM6DCRk77PbIQUO4gnaY2I0TnKxwvbWgsnk7iP_dyC61SrdL5fQ8z8GHEC0hOfuA3bvaqNRNq6FvrckgDD4ps5m2v4GXL1MGm15WRi-pqDwQfTbD0M12oGwzmwfIxBAPGcUsJnyIbNpC--kqVJm69Sxgf5LtSMAmAEVtJVuuEFvkRgjVbHbKygSaxz2ysg5m00
