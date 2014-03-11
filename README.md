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

interface IParser <|-- interface IArgument
interface IParser <|-- abstract class Parser
interface IArgument <|-- Argument
Argument <|-- Option
interface IArgument <|-- SubParsers
abstract class Parser <|-- ArgumentParser
abstract class Parser <|-- SubParsers
@enduml
```

Direct link to the UML schema
http://www.plantuml.com:80/plantuml/png/jLR1Rjim3BtxAmZa41TD3zlHs0xjBijXs83i7Z2suXYn9K-IimRD_dsKPCjIutHhi_LGADIJ-4W-qgguPdABEOFbDvIAINXAuBPJMB8Kja8sgBELbXKluGOoS4j2x5ZTfZUqQCsf552MAhkN4eyMPrUqUy2wOsbesRCZdVkL9DNe-LwMNSEujhxNQosScpJmI0TmexJ4N4DUer8mqfrnR5X0RbOmbSkgrPNWePdEEiidhEiDvdkoITPWnGkwQA1eW3qV5HY9LouuCUXodOBnsETb0S9w2iDttEoXaX6ynWsh9_EuNbTAJK8UXAWPSkUP23zpwZa6RiT4dk1BzSWAvu0VnFwyH0umL2JanDcZjfObruclzOWW4nR5u2lrLBBHVyGcIcaA-jYoCD0WxRHuhrpjeK2DZku2yhvWsL2v1VoKsBPCJYLrvdJxtOG1clxxfBLO-BMv85lsN7rairmq3V9YfOLaOsWBi4gss8EeJ69xu6VN2AHF9yiXwA2RNgES-7ZoLxtukXQyJ7tLRPnRPRkgxTV-gjuowljYjmvjxkbFp8wh8Y3zEGufE59Pd--e-wZt1bIGzX1389VO8Uys9t6H35t_m-oiGncPtSXG4jc9Cfq9P79oEPMTpwoFC5T6aNE_Tmg663U90mAzwvlMo9WKPqVX5hlQh4pRWVcpTvahvg6ZV2Ugd2ax4dzqbMG50KcpB1n5CnFjS4bql64Q3oZTyDN96rN_Wg2k6eBFrFZ73zi_xROhfj6jScFesV3nMlhgld5FSTMsMAkuR6edf0vsVDbgGU653jzOjVWRGfadL1_-Vqvv_9Befy27qzqTJ3mmBa4ddnQJBnGBzzV5PbUDoyV6-NhXBHDTtfDu0JdUzni0
