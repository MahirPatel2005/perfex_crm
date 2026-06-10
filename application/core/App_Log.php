<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class App_Log extends CI_Log {
    public function __construct() {
        parent::__construct();
    }

    public function write_log($level, $msg) {
        $level = strtoupper($level);
        // Redirect log message directly to the PHP error log (system stderr)
        error_log("CodeIgniter - $level --> $msg");
        return TRUE;
    }
}
