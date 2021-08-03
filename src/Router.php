<?php

/**
 * Project Name: mikisan-ware
 * Description : 汎用ルーター
 * Start Date  : 2021/07/17
 * Copyright   : Katsuhiko Miki   https://striking-forces.jp
 * 
 * @author Katsuhiko Miki
 */
declare(strict_types=1);

namespace mikisan\core\util\router;

use \mikisan\core\exception\InvalidParameterAccessException;
use \mikisan\core\exception\NotInitializedException;

require __DIR__ . "/subclasses/FETCHER.php";
require __DIR__ . "/subclasses/ANALYZER.php";

class EntryPoint
{
    const WEB = "web", CLI = "cli";
}

class Router
{

    private static $instance;
    private static $entrypoint;
    private $accessable = ["resolved", "route", "method", "module", "action", "params", "args"];
    private $resolved   = false;
    private $route      = "";
    private $method     = "";
    private $module     = "";
    private $action     = "";
    private $params     = [];
    private $args       = [];

    /**
     * getter
     * 
     * @param   mixed    $key
     * @return  mixed
     * @throws  InvalidParameterAccessException
     */
    public function __get($key)
    {
        if(!in_array($key, $this->accessable, true))
        {
            throw new InvalidParameterAccessException($this, $key);
        }

        return $this->{$key};
    }
    
    /**
     * Singleton
     * 
     * @return ROUTER
     */
    public static function route(): Router
    {
        if(self::$instance === null)
        {
            throw new NotInitializedException(Router::class);
        }
        return self::$instance;
    }
    
    /**
     * routes.yml を解析し、リクエストに対応するルートを決定する
     * 
     * @param string $yml_path
     * @return string
     * @throws FileNotFoundException
     */
    public static function resolve(string $yml_path): Router
    {
        if(self::$instance === null)
        {
            self::$instance = new self;
        }
        //
        self::$entrypoint   = self::get_entrypoint();
        //
        $routes     = FETCHER::fetch($yml_path);
        $route_obj  = ANALYZER::analyze(self::get_method(), self::get_request_parts(), $routes);
        //
        self::$instance->resolved   = $route_obj->resolved;
        self::$instance->route      = $route_obj->route;
        self::$instance->method     = $route_obj->method;
        self::$instance->module     = $route_obj->module;
        self::$instance->action     = $route_obj->action;
        self::$instance->params     = $route_obj->params;
        self::$instance->args       = $route_obj->args;
        //
        return self::$instance;
    }

    private static function get_entrypoint(): string
    {
        return isset($_SERVER["SERVER_NAME"])
                    ? EntryPoint::WEB
                    : EntryPoint::CLI
                    ;
    }

    private static function get_method(): string
    {
        switch(self::$entrypoint)
        {
            case EntryPoint::CLI:

                return "CLI";

            case  EntryPoint::WEB:
            default:

                return $_SERVER["REQUEST_METHOD"];
        }
    }

    private static function get_request_parts(): array
    {
        switch(self::$entrypoint)
        {
            case EntryPoint::CLI:

                $argv = $_SERVER["argv"] ?? [];
                array_shift($argv);
                return $argv;

            case EntryPoint::WEB:
            default:

                $request_path = self::to_naked($_SERVER["REQUEST_URI"]);
                return explode("/", $request_path);
        }
    }
    
    /**
     * 先頭と末尾の / を取り除く
     * 
     * @param   string  $request_uri
     * @return  string
     */
    private static function to_naked(string $request_uri): string
    {
        $temp   = preg_replace("|/+|u", "/", $request_uri);
        if($temp === "/")   { return ""; }
        return preg_replace("|^/?([^/].+[^/])/?|u", "$1", $temp);
    }
    
}
