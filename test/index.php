<?php
require_once '../vendor/autoload.php';
use Sabercode\LaravelBatchUpdate\BatchUpdate;

$model = new BatchUpdate();
$model->setTable('')->setWhereField('')->setData([])->doUpdate();
