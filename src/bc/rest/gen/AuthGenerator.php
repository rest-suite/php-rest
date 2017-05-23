<?php

namespace bc\rest\gen;


use gossi\codegen\model\PhpClass;
use gossi\codegen\model\PhpMethod;
use gossi\codegen\model\PhpParameter;
use gossi\codegen\model\PhpProperty;
use gossi\docblock\Docblock;
use gossi\docblock\tags\TagFactory;
use gossi\swagger\Path;
use gossi\swagger\SecurityScheme;
use gossi\swagger\Swagger;

class AuthGenerator
{
    const TYPE_BASIC = 'basic';
    const TYPE_APIKEY = 'apiKey';
    const TYPE_OAUTH2 = 'oauth2';

    public static function getAuthAvailableTypes()
    {
        return [
            self::TYPE_BASIC,
            self::TYPE_APIKEY,
            self::TYPE_OAUTH2
        ];
    }


    /**
     * @param Swagger $swagger
     * @return bool
     */
    public static function isThereSecurityInPaths(Swagger $swagger) {

        /** @var Path $path */
        foreach ($swagger->getPaths() as $path) {
            foreach ($path->getMethods() as $method) {
                if($path->getOperation($method)->getSecurity()->valid()) {
                    return true;
                };
            };
        }
        return false;
    }

    /**
     * @param Swagger $swagger
     * @return array
     */
    private static function getSecuritiesFromPaths(Swagger $swagger) {
        $securities = [];

        /** @var Path $path */
        foreach ($swagger->getPaths() as $path) {
            foreach ($path->getMethods() as $method) {
                if($path->getOperation($method)->getSecurity()->valid()) {
                    foreach ($path->getOperation($method)->getSecurity()->toArray() as $securityName => $content) {
                        $securities = array_merge($securities, array_keys($content));
                    };
                };
            };
        }

        return $securities;
    }

    /**
     * @param Swagger $swagger
     * @return array
     */
    private static function getApiKeyPathsData(Swagger $swagger) {

        //TODO: implement path-specific security - analyze paths and security options in it and so on and so on


        return ['    \'path\' => \'/item/*\','];
    }


    public static function createAuth($namespace, Swagger $swagger)
    {
        $auth = new PhpClass('Auth');
        $auth
            ->setNamespace($namespace . "\\Auth")
            ->setDescription('Auth class')
            ->setLongDescription('Authorize users')
            ->setDocblock(Docblock::create()->appendTag(TagFactory::create('package', $namespace)))
            ->addUseStatement('Slim\App')
        ;

        $construct = PhpMethod::create("__construct");
        $construct->setParameters([
            (new PhpParameter('app'))->setType('App')
        ]);
        $construct->setBody('$this->app = $app;');


        $appProperty = new PhpProperty('app');
        $appProperty->setVisibility('private');
        $appProperty->setDocblock(Docblock::create()->appendTag(TagFactory::create('var', 'App $app')));

        $auth->setProperty($appProperty);

        $checkAuthMethods = [];

        $security = !empty($swagger->getSecurity()->toArray()) ? array_keys($swagger->getSecurity()->toArray()[0]) : [];

        $security = array_merge($security, self::getSecuritiesFromPaths($swagger));

        $checkAuth = new PhpMethod('checkAuth');
        $checkAuthBody = [];
        $createRequestPathMethodRule = null;


        /** @var SecurityScheme $securityDefinition **/
        foreach ($swagger->getSecurityDefinitions() as $securityDefinition) {


            switch ($securityDefinition->getType()) {
                case 'apiKey':

                    $auth->addUseStatement('Slim\Middleware\TokenAuthentication');

                    $method = PhpMethod::create("checkApiKey" . ucfirst($securityDefinition->getIn()));

                    if(in_array($securityDefinition->getId(), $security)){
                        $checkAuthMethods[] = $method->getName();
                    }

                    $methodBody[] = "//TODO: implement proper check Api key query";
                    $methodBody[] = '$authenticator = function($request, TokenAuthentication $tokenAuth){';
                    $methodBody[] = '   $token = $tokenAuth->findToken($request);';
                    $methodBody[] = '   return $token == "testtoken";';
                    $methodBody[] = '};';

                    $methodBody[] = '$error = function($request, $response, TokenAuthentication $tokenAuth) {';
                    $methodBody[] = '    $output[\'error\'] = [';
                    $methodBody[] = '       \'msg\'    => $tokenAuth->getResponseMessage(),';
                    $methodBody[] = '       \'token\'  => $tokenAuth->getResponseToken(),';
                    $methodBody[] = '       \'status\' => 401,';
                    $methodBody[] = '       \'error\'  => true';
                    $methodBody[] = '    ];';
                    $methodBody[] = '';

                    $methodBody[] = '/** @var \Slim\Http\Response $response  */';
                    $methodBody[] = 'return $response->withJson($output, 401);';
                    $methodBody[] = '};';
                    $methodBody[] = '';

                    $methodBody[] = '$this->app->add(new TokenAuthentication([';

                    // TODO: compute paths and excludes
                    $methodBody = array_merge($methodBody, self::getApiKeyPathsData($swagger));

                    $methodBody[] = '    \'authenticator\' => $authenticator,';
                    

                    if($securityDefinition->getIn() == "header") {
                        $methodBody[] = '    \'header\' => \'Token-Authorization-X\',';
                        $methodBody[] = '    \'regex\' => \'/(.*)$/i\',';
                    } elseif ($securityDefinition->getIn() == "query") {
                        $methodBody[] = '    \'parameter\' => \'token\',';
                    }

                    $methodBody[] = '    \'error\' => $error,';
                    $methodBody[] = ']));';

                    $method->setBody(implode("\n", $methodBody));

                    $auth->setMethod($method);
                    $auth->setConstant('isApiKeyEnabled', true);

                    break;
                case 'basic':

                    $auth->addUseStatement('Slim\Middleware\HttpBasicAuthentication');

                    $method = PhpMethod::create('checkBasicAuth');

                    // TODO: compute paths and excludes
                    $method->setBody('
                        //TODO: implement proper check login and password
$this->app->add(new HttpBasicAuthentication([
    "users" => [
        "testuser" => "testpassword",
    ]
]));');
                    $auth->setMethod($method);
                    $auth->setConstant('isBasicEnabled', true);
                    if(in_array($securityDefinition->getId(), $security)){
                        $checkAuthMethods[] = $method->getName();
                    }

                    break;

                case 'oauth2':
                    $method = PhpMethod::create('checkOauth2Auth');

                    $method->setBody('
                        //TODO: implement proper check oath2 auth');

                    $auth->setMethod($method);
                    $auth->setConstant('isOauth2Enabled', true);
                    if(in_array($securityDefinition->getId(), $security)){
                        $checkAuthMethods[] = $method->getName();
                    }

                    $createRequestPathMethodRule = self::createRequestPathMethodRule($namespace);

                    break;
                default:
                    throw new \InvalidArgumentException("Unknown auth type");
            }
        }

        $auth->setMethod($construct);

        foreach ($checkAuthMethods as $checkAuthMethod) {
            $checkAuthBody[] = '$this->' . $checkAuthMethod . "();";
        }

        $checkAuth->setBody(implode("\n", $checkAuthBody));
        $auth->setMethod($checkAuth);


        $authClassesArr['Auth'] = $auth;

        if(!is_null($createRequestPathMethodRule)) {
            $authClassesArr['RequestPathMethodRule'] = $createRequestPathMethodRule;
        }
        return $authClassesArr;
    }


    private static function createRequestPathMethodRule($namespace)
    {
        $rule = new PhpClass();
        $rule
            ->setName('RequestPathMethodRule')
            ->setNamespace($namespace . "\\Auth")
            ->setDescription('Request path method rule')
            ->setLongDescription('Rule to decide by request path and method whether the request should be authenticated or not.')
            ->setDocblock(Docblock::create()->appendTag(TagFactory::create('package', $namespace . "\\Auth")))
            ->addUseStatement('Psr\Http\Message\RequestInterface')
            ->setProperty((new PhpProperty('options'))->setVisibility('protected')->setDescription('Stores all the options passed to the rule'))
        ;

        $rule->setMethod((new PhpMethod('getDefaultOptions'))->setBody('return ["path" => ["/"], "passthrough"=> []];'));


        $rule->setMethod(
            (new PhpMethod('__construct'))
                ->setParameters([
                    (new PhpParameter('options'))
                    ->setExpression('[]')
                    ->setType('array')
                ])
                ->setBody('$this->options = array_merge($this->getDefaultOptions(), $options);')
                ->setDescription('Create new rule instance')

        );

        $rule->setMethod()

        return $rule;
    }



}