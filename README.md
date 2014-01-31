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
 +addArgument( IArgument argument )
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
