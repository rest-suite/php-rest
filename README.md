[![Build Status](https://travis-ci.org/rest-suite/generator.svg?branch=master)](https://travis-ci.org/rest-suite/generator)

# php-rest (work in progress)
RESTapi server generator based on [openapi specs](https://openapis.org/) 
and [slim framework](http://www.slimframework.com/)

# Install
Command line:
`composer require rest-suite/generator`  
  -or-  
In `composer.json`    
````json
{
  "require-dev": {
    "rest-suite/generator" : "~0"
  }
}
````
# Usage

## Generate code
`./vendor/bin/apigen generate --namespace 'Example\Api' --output ./examples ./tests/_data/swagger.yml`

````
Usage:
  generate [options] [--] [<spec>]
  gen
  g

Arguments:
  spec                        path to swagger specs [default: "swagger.yml"]

Options:
  -o, --output[=OUTPUT]       output path for generated code [default: "./"]
  -ns, --namespace=NAMESPACE  base namespace for generated code
  -m, --models                only generate files for models
  -c, --controllers           only generate files for controllers
  -t, --tests                 only generate files for tests
  -s, --settings              only generate dist settings files
      --override              override existing files  
      --sync                  uses with "--override" option, sync client code between re-generations
````

## Synchronization
TODO: Two-way sync (schema <=> code) with user-code preservation

# Examples
Examples of generated code:

* Simple api - [specs](https://github.com/rest-suite/generator/blob/master/tests/_data/swagger.yml),
[code](https://github.com/rest-suite/generator/tree/master/examples/Simple/)  

* Pet Shop - [specs](https://github.com/rest-suite/generator/blob/master/tests/_data/petshop.yml),
[code](https://github.com/rest-suite/generator/tree/master/examples/Petshop/)

# Documentation
TODO

# Contribution
Feel free to PR, fork and whatever
