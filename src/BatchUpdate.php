<?php
namespace Sabercode\LaravelBatchUpdate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class BatchUpdate
{

    protected static object $self;

    public function __construct()
    {
        self::$self = new self();
    }


    /**
     * @param $table
     * @param $data
     * @param $whereField
     * @param $whereSame
     * @return false|void
     */
    public static function doUpdate($table,$data,$whereField,$whereSame)
    {
        if (!Schema::hasTable($table) ){
            Log::error('批量更新-表不存在');
            return false;
        }
        if(!Schema::hasColumn($table, $whereField)){
            Log::error('批量更新-更新条件不存在');
            return false;
        }
        if(!is_array($data) && !$data){
            Log::error('批量更新-更新数据错误');
            return false;
        }
        $sql = self::$self->getUpdateSql($table,$data,$whereField,$whereSame);
        DB::update($sql);
    }



    /**
     * 组装批量更新sql
     * @param string $table 需要更新的表
     * @param array $data 待更新的数据，二维数组格式
     * @param array $params array 值相同的条件，键值对应的一维数组
     * @param string $field string 值不同的条件，默认为id
     * @return bool|string
     */
    private function getUpdateSql(string $table, array $data, string $field,array $params = [])
    {
        if (!is_array($data) || !$field || !is_array($params)) {
            return false;
        }

        $updates = $this->parseUpdate($data, $field);
        $where = $this->parseParams($params);

        // 获取所有键名为$field列的值，值两边加上单引号，保存在$fields数组中
        $fields = array_column($data, $field);
        $fields = implode(',', array_map(function($value) {
            return "'".$value."'";
        }, $fields));

        $sql = sprintf("UPDATE `%s` SET %s WHERE `%s` IN (%s) %s", $table, $updates, $field, $fields, $where);

        return $sql;
    }

    /**
     * 将二维数组转换成CASE WHEN THEN的批量更新条件
     * @param $data array 二维数组
     * @param $field string 列名
     * @return string sql语句
     */
   private function parseUpdate($data, $field): string
   {
        $sql = '';
        $keys = array_keys(current($data));
        foreach ($keys as $column) {

            $sql .= sprintf("`%s` = CASE `%s` \n", $column, $field);
            foreach ($data as $line) {
                $sql .= sprintf("WHEN '%s' THEN '%s' \n", $line[$field], $line[$column]);
            }
            $sql .= "END,";
        }

        return rtrim($sql, ',');
    }

    /**
     * 解析额外where条件
     * @param $params
     * @return string
     */
    private function parseParams($params): string
    {
        $where = [];
        foreach ($params as $key => $value) {
            $where[] = sprintf("`%s` = '%s'", $key, $value);
        }

        return $where ? ' AND ' . implode(' AND ', $where) : '';
    }


}
