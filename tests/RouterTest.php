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

use \mikisan\core\util\autoload\Autoload;
use \PHPUnit\Framework\TestCase;
use \mikisan\core\util\router\Router;
use \mikisan\core\exception\InvalidParameterAccessException;
use \mikisan\core\exception\NotInitializedException;

$project_root = realpath(__DIR__ . "/../../../../");
require "{$project_root}/vendor/autoload.php";
require "{$project_root}/tests/TestCaseTrait.php";
Autoload::register(__DIR__ . "/../src", true);

class RouterTest extends TestCase
{
    use TestCaseTrait;
    
    private $class_name = "mikisan\\core\\util\\router\\Router";
    
    /**
     * 先頭と末尾の / を取り除くテスト
     */
    public function test_to_naked()
    {
        $this->assertEquals("home/index", $this->callMethod($this->class_name, "to_naked", ["/home/index"]));
        $this->assertEquals("home/index", $this->callMethod($this->class_name, "to_naked", ["home/index/"]));
        $this->assertEquals("home/index", $this->callMethod($this->class_name, "to_naked", ["/home/index/"]));
        //
        $this->assertEquals("home/index", $this->callMethod($this->class_name, "to_naked", ["///home///index"]));
        $this->assertEquals("home/index", $this->callMethod($this->class_name, "to_naked", ["home///index///"]));
        $this->assertEquals("home/index", $this->callMethod($this->class_name, "to_naked", ["///home//index///"]));
        //
        $this->assertEquals("", $this->callMethod($this->class_name, "to_naked", ["/"]));
        $this->assertEquals("", $this->callMethod($this->class_name, "to_naked", ["////"]));
    }
    
    public function test_route_not_initialized()
    {
        $this->expectException(NotInitializedException::class);
        $this->expectExceptionMessage("オブジェクトが初期化されていません。[mikisan\\core\\util\\router\\Router]");
        
        $obj    = Router::route();
        $this->assertEquals($this->class_name, get_class($obj));
    }
    
    public function test_resolve()
    {
        $yml_path   = __DIR__ . "/routes.yml";
        $_SERVER["SERVER_NAME"]     = "striking-forces.jp";
        $_SERVER["REQUEST_METHOD"]  = "GET";
        $_SERVER["REQUEST_URI"]     = "/";
        //
        $obj    = Router::resolve($yml_path);
        $this->assertEquals($this->class_name, get_class($obj));
    }
    
    public function test_get()
    {
        $this->expectException(InvalidParameterAccessException::class);
        $this->expectExceptionMessage("mikisan\\core\\util\\router\\Router::hogehage はアクセス許可されていません。");
    
        $yml_path   = __DIR__ . "/routes.yml";
        $_SERVER["SERVER_NAME"]     = "striking-forces.jp";
        $_SERVER["REQUEST_METHOD"]  = "GET";
        $_SERVER["REQUEST_URI"]     = "/";
        //
        $obj    = Router::resolve($yml_path);
        $value  = $obj->hogehage;
    }
    
    public function test_route()
    {
        $obj    = Router::route();
        $this->assertEquals($this->class_name, get_class($obj));
    }
    
    public function test_resolve_web001()
    {
        $yml_path   = __DIR__ . "/routes.yml";
        $_SERVER["SERVER_NAME"]     = "striking-forces.jp";
        $_SERVER["REQUEST_METHOD"]  = "GET";
        $_SERVER["REQUEST_URI"]     = "/";
        //
        $route      = Router::resolve($yml_path);
        //
        $this->assertTrue($route->resolved);
        $this->assertEquals("@get",     $route->route);
        $this->assertEquals("GET",      $route->method);
        $this->assertEquals("home",     $route->module);
        $this->assertEquals("index",    $route->action);
        $this->assertCount(0,           $route->params);
        $this->assertCount(0,           $route->args);
    }
    
    public function test_resolve_web002()
    {
        $yml_path   = __DIR__ . "/routes.yml";
        $_SERVER["SERVER_NAME"]     = "striking-forces.jp";
        $_SERVER["REQUEST_METHOD"]  = "POST";
        $_SERVER["REQUEST_URI"]     = "/admin/service/sort_service";
        //
        $route      = Router::resolve($yml_path);
        //
        $this->assertTrue($route->resolved);
        $this->assertEquals("admin/service/sort_service@post",  $route->route);
        $this->assertEquals("POST",             $route->method);
        $this->assertEquals("admin/service",    $route->module);
        $this->assertEquals("sort_service",     $route->action);
        $this->assertCount(0,                   $route->params);
        $this->assertCount(0,                   $route->args);
    }
    
    public function test_resolve_cli001()
    {
        $yml_path   = __DIR__ . "/routes.yml";
        $_SERVER["SERVER_NAME"]     = null;
        $_SERVER["REQUEST_METHOD"]  = null;
        $_SERVER["REQUEST_URI"]     = null;
        $_SERVER["argv"]            = ["pine", "make", "controller", "test"];
        //
        $route      = Router::resolve($yml_path);
        //
        $this->assertTrue($route->resolved);
        $this->assertEquals("make/{target_structure}/{target_module}@cli",     $route->route);
        $this->assertEquals("CLI",          $route->method);
        $this->assertEquals("build",        $route->module);
        $this->assertEquals("make",         $route->action);
        $this->assertCount(2,               $route->params);
        $this->assertEquals("controller",   $route->params["target_structure"]);
        $this->assertEquals("test",         $route->params["target_module"]);
        $this->assertCount(0,               $route->args);
    }
    
}
