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
use PHPUnit\Framework\TestCase;
use mikisan\core\util\router\FETCHER;
use mikisan\core\exception\FileNotFoundException;
use mikisan\core\exception\FileOpenFailedException;

$project_root = realpath(__DIR__ . "/../../../../");
require_once "{$project_root}/vendor/autoload.php";
require_once "{$project_root}/tests/TestCaseTrait.php";
Autoload::register(__DIR__ . "/../src", true);


class FETCHER_Test extends TestCase
{
    use TestCaseTrait;
    
    private $class_name = "mikisan\\core\\util\\router\\FETCHER";
    
    /**
     * ルートの /index アクション表記を省略する　のテスト
     */
    public function test_reformat_route()
    {
        $this->assertEquals("service/sort_service@post", $this->callMethod($this->class_name, "reformat_route", ["service/sort_service@post"]));
        $this->assertEquals("service@get", $this->callMethod($this->class_name, "reformat_route", ["service/index@get"]));
        $this->assertEquals("@get", $this->callMethod($this->class_name, "reformat_route", ["index@get"]));
        $this->assertEquals("@get", $this->callMethod($this->class_name, "reformat_route", ["@get"]));
    }
    
    public function test_fetch_file_not_exists()
    {
        $yml_path   = __DIR__ . "/routes_fake.yml";
        
        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage("引数で渡されたファイルは存在しません。[{$yml_path}]");
        
        $r  = FETCHER::fetch($yml_path);
    }
    
    public function test_fetch_file_open_failed()
    {
        // すべてのエラー処理でExceptionをスローする。
        set_error_handler(
            function($errno, $errstr, $errfile, $errline)   { throw new \ErrorException($errstr, 0, $errno, $errfile, $errline); }
            , E_ALL
        );
        
        $yml_path   = __DIR__;
        
        $this->expectException(ErrorException::class);
        
        $r  = FETCHER::fetch($yml_path);
    }
    
    /**
     * routes.yml を読み込み、ルートリストを取得する　のテスト
     */
    public function test_fetch()
    {
        $yml_path   = realpath(__DIR__ . "/routes.yml");
        $r          = FETCHER::fetch($yml_path);
        //
        $this->assertIsArray($r);
        $this->assertCount(8, $r);
        //
        $this->assertArrayHasKey("@get", $r);
        $this->assertEquals("home", $r["@get"]["module"]);
        $this->assertEquals("index", $r["@get"]["action"]);
        //
        $this->assertArrayHasKey("check@wild", $r);
        $this->assertEquals("home", $r["check@wild"]["module"]);
        $this->assertEquals("check", $r["check@wild"]["action"]);
        //
        $this->assertArrayHasKey("admin/service@get", $r);
        $this->assertEquals("admin/service", $r["admin/service@get"]["module"]);
        $this->assertEquals("index", $r["admin/service@get"]["action"]);
        //
        $this->assertArrayHasKey("admin/service/sort_service@post", $r);
        $this->assertEquals("admin/service", $r["admin/service/sort_service@post"]["module"]);
        $this->assertEquals("sort_service", $r["admin/service/sort_service@post"]["action"]);
        //
        $this->assertArrayHasKey("admin/service/master/{id}/register/{num}@post", $r);
        $this->assertEquals("admin/service/master", $r["admin/service/master/{id}/register/{num}@post"]["module"]);
        $this->assertEquals("register", $r["admin/service/master/{id}/register/{num}@post"]["action"]);
        //
        $this->assertArrayHasKey("blog/writer/**@get", $r);
        $this->assertEquals("blog", $r["blog/writer/**@get"]["module"]);
        $this->assertEquals("writer", $r["blog/writer/**@get"]["action"]);
        //
        $this->assertArrayHasKey("blog/{id}/category/{cat_id}@get", $r);
        $this->assertEquals("blog", $r["blog/{id}/category/{cat_id}@get"]["module"]);
        $this->assertEquals("category", $r["blog/{id}/category/{cat_id}@get"]["action"]);
        //
        $this->assertArrayHasKey("make/{target_structure}/{target_module}@cli", $r);
        $this->assertEquals("build", $r["make/{target_structure}/{target_module}@cli"]["module"]);
        $this->assertEquals("make", $r["make/{target_structure}/{target_module}@cli"]["action"]);
    }
    
}
