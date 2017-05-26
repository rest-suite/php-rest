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
    public static function isThereSecurityInPaths(Swagger $swagger)
    {

        /** @var Path $path */
        foreach ($swagger->getPaths() as $path) {
            foreach ($path->getMethods() as $method) {
                if ($path->getOperation($method)->getSecurity()->valid()) {
                    return true;
                };
            };
        }
        return false;
    }

    public static function createAuth($namespace, Swagger $swagger)
    {
        $auth = new PhpClass('Auth');
        $auth
            ->setNamespace($namespace . "\\Auth")
            ->setDescription('Auth class')
            ->setLongDescription('Authorize users')
            ->setDocblock(Docblock::create()->appendTag(TagFactory::create('package', $namespace)))
            ->addUseStatement('Slim\App');

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


        /** @var SecurityScheme $securityDefinition * */
        foreach ($swagger->getSecurityDefinitions() as $securityDefinition) {

            switch ($securityDefinition->getType()) {
                case 'apiKey':

                    $auth->addUseStatement('Slim\Middleware\TokenAuthentication');

                    $method = PhpMethod::create("checkApiKey" . ucfirst($securityDefinition->getIn()));

                    if (in_array($securityDefinition->getId(), $security)) {
                        $checkAuthMethods[] = $method->getName();
                    }
                    $methodBody = [];
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
                    $methodBody[] = self::getApiKeyPathsData($swagger, $securityDefinition->getId());

                    $methodBody[] = '    \'authenticator\' => $authenticator,';


                    if ($securityDefinition->getIn() == "header") {
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

                    $checkBasicAuthBody[] = '//TODO: implement proper check login and password';
                    $checkBasicAuthBody[] = '$this->app->add(new HttpBasicAuthentication([';
                    $checkBasicAuthBody[] = '    "users" => [';
                    $checkBasicAuthBody[] = '        "testuser" => "testpassword",';
                    $checkBasicAuthBody[] = '     ],';

                    $checkBasicAuthBody = array_merge(
                        $checkBasicAuthBody,
                            self::getBasicAuthPathsData(
                                $swagger, $securityDefinition->getId()
                            )
                    );

                    $checkBasicAuthBody[] = ']));';
                    $checkBasicAuthBody[] = '';

                    $checkBasicAuthBodyStr = implode("\n", $checkBasicAuthBody);

                    $method->setBody($checkBasicAuthBodyStr);
                    $auth->setMethod($method);
                    $auth->setConstant('isBasicEnabled', true);
                    if (in_array($securityDefinition->getId(), $security)) {
                        $checkAuthMethods[] = $method->getName();
                    }

                    break;

                case 'oauth2':
                    $method = PhpMethod::create('checkOauth2Auth');

                    $method->setBody('//TODO: implement proper check oath2 auth');

                    $auth->setMethod($method);
                    $auth->setConstant('isOauth2Enabled', true);
                    if (in_array($securityDefinition->getId(), $security)) {
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

        if (!is_null($createRequestPathMethodRule)) {
            $authClassesArr['RequestPathMethodRule'] = $createRequestPathMethodRule;
        }
        return $authClassesArr;
    }

    /**
     * @param Swagger $swagger
     * @return array
     */
    private static function getSecuritiesFromPaths(Swagger $swagger)
    {
        $securities = [];

        /** @var Path $path */
        foreach ($swagger->getPaths() as $path) {
            foreach ($path->getMethods() as $method) {
                if ($path->getOperation($method)->getSecurity()->valid()) {
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
    private static function getApiKeyPathsData(Swagger $swagger, $securityName)
    {

        $data = var_export(self::calculateAuthPathsAndMethods($swagger, $securityName), true);

        preg_match('/\((([^()]*|(?R))*)\)/', $data, $matches);

        return trim($matches[1], "\n ");

    }

    private static function getBasicAuthPathsData(Swagger $swagger, $securityName)
    {
        $pathData[] = "    'rules' => [";
        $pathData[] = "new RequestPathMethodRule(";

        $pathData[] = var_export(self::calculateAuthPathsAndMethods($swagger, $securityName), true);

        $pathData[] = ")]";

        return $pathData;
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
            ->setProperty((new PhpProperty('options'))->setVisibility('protected')->setDescription('Stores all the options passed to the rule'));

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

        $invokeMethod[] = '$uri = "/" . $request->getUri()->getPath();';
        $invokeMethod[] = '$uri = preg_replace("#/+#", "/", $uri);';
        $invokeMethod[] = '';
        $invokeMethod[] = '/** If request path is matches passthrough should not authenticate. */';
        $invokeMethod[] = 'foreach ((array)$this->options["passthrough"] as $passthrough => $method) {';
        $invokeMethod[] = '    $passthrough = rtrim($passthrough, "/");';
        $invokeMethod[] = '';
        $invokeMethod[] = '    if ($passthrough === \'0\') {';
        $invokeMethod[] = '        $passthrough    = $method;';
        $invokeMethod[] = '        $method         = null;';
        $invokeMethod[] = '    }';
        $invokeMethod[] = '';
        $invokeMethod[] = '    if (preg_match("@^{$passthrough}(/.*)?$@", $uri)) {';
        $invokeMethod[] = '        if ((in_array(strtolower($request->getMethod()), (array)$method)) || empty((array)$method)) {';
        $invokeMethod[] = '            return false;';
        $invokeMethod[] = '        }';
        $invokeMethod[] = '    }';
        $invokeMethod[] = '}';
        $invokeMethod[] = '';
        $invokeMethod[] = '/** Otherwise check if path matches and we should authenticate. */';
        $invokeMethod[] = 'foreach ((array)$this->options["path"] as $path => $method) {';
        $invokeMethod[] = '    $path = rtrim($path, "/");';
        $invokeMethod[] = '';
        $invokeMethod[] = '    if ($path === \'0\') {';
        $invokeMethod[] = '        $path   = $method;';
        $invokeMethod[] = '        $method = null;';
        $invokeMethod[] = '    }';
        $invokeMethod[] = '    if (preg_match("@^{$path}(/.*)?$@", $uri)) {';
        $invokeMethod[] = '        if ((in_array(strtolower($request->getMethod()), (array)$method)) || empty((array)$method)) {';
        $invokeMethod[] = '            return true;';
        $invokeMethod[] = '        }';
        $invokeMethod[] = '    }';
        $invokeMethod[] = '}';
        $invokeMethod[] = '';
        $invokeMethod[] = 'return false;';


        $rule->setMethod(
            (new PhpMethod('__invoke'))
                ->setParameters([
                    (new PhpParameter('request'))
                        ->setType('RequestInterface')

                ])
                ->setBody(implode("\n", $invokeMethod))
                ->setDocblock('@return bool')
        );

        return $rule;
    }

    private static function calculateAuthPathsAndMethods(Swagger $swagger, $securityName)
    {
        //TODO: implement passthrough

        $ruleArr = [];

        /** @var Path $path */
        foreach ($swagger->getPaths() as $path) {
            foreach ($path->getMethods() as $method) {
                if (!empty($path->getOperation($method)->getSecurity()->toArray())){
                    if (isset($path->getOperation($method)->getSecurity()->toArray()[0])) {
                        if (in_array($securityName, array_keys($path->getOperation($method)->getSecurity()->toArray()[0]))) {
                            $ruleArr['path'][preg_replace('/\{\D+/', '*', $path->getPath())][] = $method;
                        }
                    }
                }
            };
        }

        return $ruleArr;
    }
}