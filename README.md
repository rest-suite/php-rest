[![Build Status](https://travis-ci.org/theinpu/php-rest.svg?branch=master)](https://travis-ci.org/theinpu/php-rest)

# php-rest (work in progress)
RESTapi server generator based on [openapi specs](https://openapis.org/) 
and [slim framework](http://www.slimframework.com/)

# Install
Command line:
`composer require bc/php-rest`  
  -or-  
In `composer.json`    
````json
{
  "require-dev": {
    "bc/php-rest" : "dev-master"
  }
}
````
### Important notice
While waiting for some [PR](https://github.com/gossi/swagger/pull/11) to `gossy/swagger`, 
repository section need to be added to `composer.json`  
````json
{
  "repositories": [
    {
      "type": "git",
      "url": "https://github.com/theinpu/swagger.git"
    }
  ]
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
````

## Synchronization
TODO: Two-way sync (schema <=> code) with user-code preservation

# Examples
Examples of generated code:

* Simple api - [specs](https://github.com/theinpu/php-rest/blob/master/tests/_data/swagger.yml),
[code](https://github.com/theinpu/php-rest/tree/master/examples/simple/)  

* Pet Shop - [specs](https://github.com/theinpu/php-rest/blob/master/tests/_data/petshop.yml),
[code](https://github.com/theinpu/php-rest/tree/master/examples/petshop/)

# Documentation
TODO

# Contribution
Feel free to PR, fork and whatever