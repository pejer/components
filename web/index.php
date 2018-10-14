<?php
/**
 * Created by Henrik Pejer ( mr@henrikpejer.com )
 * Date: 2018-10-02 19:15
 */
const ENV        = "local";
const CONFIG_DIR = __DIR__ . "/../config/";

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'bootstrap.php';

var_dump(memory_get_peak_usage(true) / 1024 / 1024 . ' MB');
var_dump(memory_get_usage() / 1024 / 1024 . ' MB');
