<?php
namespace Sabercode\LaravelBatchUpdate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BatchUpdate
{

    protected $table;
    protected $data;
    protected $whereField;
    protected $error;


    protected function setError($error)
    {
        $this->error = $error;
    }

    private function getError()
    {
        return $this->error;
    }

    public function setTable($table)
    {
        if(!is_string($table) || !$table){
            $this->setError('批量更新-表名错误');
            return false;
        }
        $this->table = $table;
        return $this;
    }

    public function setData($data)
    {
        if(!is_array($data) || !$data){
            $this->setError('批量更新-更新数据错误');
            return false;
        }
        $this->data = $data;
        return $this;
    }

    public function setWhereField($whereField)
    {
        if(!is_string($whereField) || !$whereField){
            $this->setError('批量更新-更新条件错误');
            return false;
        }
        $this->whereField = $whereField;
        return $this;
    }

    /**
     * @return false|void
     */
    public function doUpdate()
    {
        if($this->getError()){
            Log::error($this->getError());
            return false;
        }
        $sql = $this->getUpdateSql($this->table,$this->data,$this->whereField);
        DB::update($sql);
    }



    /**
     * 组装批量更新sql
     * @param $table string 需要更新的表
     * @param $data array 待更新的数据，二维数组格式
     * @param array $params array 值相同的条件，键值对应的一维数组
     * @param string $field string 值不同的条件，默认为id
     * @return bool|string
     */
    public function getUpdateSql($table, $data, $field, $params = [])
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
   private function parseUpdate($data, $field)
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
     * 解析where条件
     * @param $params
     * @return string
     */
    private function parseParams($params)
    {
        $where = [];
        foreach ($params as $key => $value) {
            $where[] = sprintf("`%s` = '%s'", $key, $value);
        }

        return $where ? ' AND ' . implode(' AND ', $where) : '';
    }


}
