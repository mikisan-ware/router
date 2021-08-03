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
use mikisan\core\util\yaml\YAML;
use mikisan\core\exception\FileNotFoundException;
use mikisan\core\exception\FileOpenFailedException;

class FETCHER
{
    /**
     * routes.yml を読み込み、ルーツリストを取得する
     * 
     * @param   string  $yml_path       読み込む routes.yml ファイルへのパス
     * @return  array                   ルート設定を示す連想配列
     */
    public static function fetch(string $yml_path) : array
    {
        if(!file_exists($yml_path))
        {
            throw new FileNotFoundException($yml_path);
        }
        $yml    = file_get_contents($yml_path);
        $temp   = YAML::parse($yml);
        $keys   = array_keys($temp["routes"]);
        rsort($keys);   // キーの逆順ソート
        
        $temp_routes    = [];
        foreach($keys as $key)
        {
            $temp_routes[$key]  = $temp["routes"][$key];
        }
        
        return  self::fetch_recursive($temp_routes, "");
    }
     
    /**
     * 再帰処理を用いて、階層構造のルーツをkey:value構造に展開する
     * 
     * @param   array       $temp_routes        展開中のルーツ
     * @param   string      $prev_route          親階層のパス
     * @return  array
     */
    private static function fetch_recursive(array $temp_routes, string $prev_route) : array
    {
        $routes = [];
        
        foreach($temp_routes as $temp_route => $setting)
        {
            $new_route  = (empty($prev_route))
                                ? $temp_route
                                : "{$prev_route}/{$temp_route}"
                                ;
            if(isset($setting["module"]) && isset($setting["action"]))
            {
                $key    = self::reformat_route($new_route);
                $routes[$key] = $setting;
                continue;
            }
            
            $routes = array_merge($routes, self::fetch_recursive($setting, $new_route));
        }
        
        return $routes;
    }
    
    /**
     * ルートの /index アクション表記を省略する
     * 
     * @param   string  $temp
     * @return  string
     */
    private static function reformat_route(string $temp): string
    {
        $route_key  = (mb_strpos($temp, "@") === false) ? "{$temp}@wild" : $temp; 
        return preg_replace("|/?index@|u", "@", $route_key);
    }
    
}
