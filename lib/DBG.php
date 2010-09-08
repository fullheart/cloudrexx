<?php

// Basic flags
define('DBG_NONE',        0);
define('DBG_PHP',         1<<0);
define('DBG_ADODB',       1<<1);
define('DBG_ADODB_TRACE', 1<<2);
define('DBG_ADODB_ERROR', 1<<3);
define('DBG_LOG_FILE',    1<<4);
define('DBG_LOG_FIREPHP', 1<<5);
// Full debugging (quite pointless really)
define('DBG_ALL',
      DBG_PHP
    | DBG_ADODB | DBG_ADODB_TRACE | DBG_ADODB_ERROR
    | DBG_LOG_FILE | DBG_LOG_FIREPHP);
// Common debugging modes (add more as required)
define('DBG_ERROR_FIREPHP',
      DBG_PHP | DBG_ADODB_ERROR | DBG_LOG_FIREPHP);
define('DBG_DB_FIREPHP',
      DBG_PHP | DBG_ADODB | DBG_LOG_FIREPHP);


class DBG
{
    private static $dbg_fh = null;
    private static $fileskiplength = 0;
    private static $enable_msg   = null;
    private static $enable_trace = null;
    private static $enable_dump  = null;
    private static $enable_time  = null;
    private static $firephp      = null;
    private static $log_file     = null;
    private static $log_firephp  = null;
    private static $log_adodb    = null;
    private static $log_php      = 0;
    private static $last_time    = null;
    private static $start_time   = null;
    private static $mode         = 0;
    private static $sql_query_cache = null;

    public function __construct()
    {
        throw new Exception('This is a static class! No need to create an object!');
    }


    public static function activate($mode = null)
    {
        if (!self::$fileskiplength) {
            self::$fileskiplength = strlen(dirname(dirname(__FILE__))) +1;
        }

        if (empty($mode)) {
            self::$mode = DBG_ALL & ~DBG_LOG_FILE & ~DBG_LOG_FIREPHP;
        } else {
            self::$mode = self::$mode | $mode;
        }

        self::__internal__setup();

        self::enable_all();
    }


    public static function deactivate($mode = null)
    {
        if (empty($mode)) {
            self::$mode = DBG_NONE;
            self::disable_all();
        } else {
            self::$mode = self::$mode  & ~$mode;
        }

        self::__internal__setup();
    }


    public static function __internal__setup()
    {
        // log to file dbg.log
        if (self::$mode & DBG_LOG_FILE) {
            self::enable_file();
        } else {
            self::disable_file();
        }

        // log to FirePHP
        if (self::$mode & DBG_LOG_FIREPHP) {
            self::enable_firephp();
        } else {
            self::disable_firephp();
        }

        // log mysql queries
        if ((self::$mode & DBG_ADODB) || (self::$mode & DBG_ADODB_TRACE) || (self::$mode & DBG_ADODB_ERROR)) {
            self::enable_adodb();
        } else {
            self::disable_adodb_debug();
        }

        // log php warnings/erros/notices...
        if (self::$mode & DBG_PHP) {
            self::enable_error_reporting();
        } else {
            self::disable_error_reporting();
        }
    }


    public static function getMode()
    {
        return self::$mode;
    }


    private static function enable_file()
    {
        if (self::$log_file) return;

        // disable firephp first
        self::disable_firephp();

        self::$log_file = true;
        self::setup('dbg.log', 'w');
    }


    private static function disable_file()
    {
        if (!self::$log_file) return;

        self::$log_file = false;
        fclose(self::$dbg_fh);
        self::$dbg_fh = null;
        restore_error_handler();
    }


    private static function enable_firephp()
    {
        if (self::$log_firephp) return;

        $file = $line = '';
        if (headers_sent($file, $line)) {
            trigger_error("Can't activate FirePHP! Headers already sent in $file on line $line'", E_USER_NOTICE);
            return;
        }

        // disable file first
        self::disable_file();

        ob_start();
        if (!isset(self::$firephp)) {
            require_once 'firephp/FirePHP.class.php';
            self::$firephp = FirePHP::getInstance(true);
        }
        self::$firephp->registerErrorHandler(false);
        self::$firephp->setEnabled(true);
        self::$log_firephp = true;
    }


    private static function disable_firephp()
    {
        if (!self::$log_firephp) return;

        self::$firephp->setEnabled(false);
        self::$log_firephp = false;
        ob_end_clean();
        restore_error_handler();
    }


    static function enable_all()
    {
        self::enable_msg  ();
        self::enable_trace();
        self::enable_dump ();
        self::enable_time ();
    }


    static function disable_all()
    {
        self::disable_msg();
        self::disable_trace();
        self::disable_dump();
        self::disable_time();
    }


    static function time()
    {
        if (self::$enable_time) {
            $t = self::$last_time;
            self::$last_time = microtime(true);
            $diff_last  = round(self::$last_time  - $t, 5);
            $diff_start = round(self::$last_time-self::$start_time, 5);
            $callers = debug_backtrace();
            $f = self::_cleanfile($callers[0]['file']);
            $l = $callers[0]['line'];
            $d = date('H:i:s');
            self::_log("TIME AT: $f:$l $d (diff: $diff_last, startdiff: $diff_start)");
        }
    }


    static function setup($file, $mode='a')
    {
        if (self::$dbg_fh) fclose(self::$dbg_fh);
        if (self::$log_firephp) return true; //no need to setup ressources, we're using firephp
        $suffix = '';
        /*$nr = 0;
        while (file_exists($file.$suffix)) {
            $suffix = '.'.++$nr;
        }*/
        self::$dbg_fh = fopen($file.$suffix, $mode);
        return true;
    }


    static function enable_trace()
    {
        if (self::$enable_trace) return;

        //self::_log('XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX ENABLING TRACE XXXXXXXXXXXXXXXX');
        self::$enable_trace = 1;
    }


    // Redirect ADODB output to us instead of STDOUT.
    static function enable_adodb()
    {
        if (!self::$log_adodb) {
            if (!(self::$mode & DBG_LOG_FILE)) self::setup('php://output');
            if (!defined('ADODB_OUTP')) define('ADODB_OUTP', 'DBG_log_adodb');
            self::$log_adodb = true;
        }
        self::enable_adodb_debug(self::$mode & DBG_ADODB_TRACE);
    }


    static function enable_adodb_debug($flagTrace=false)
    {
        global $objDatabase;

        if (!isset($objDatabase)) return;

        if ($flagTrace) {
            $objDatabase->debug = 99;
        } else {
            $objDatabase->debug = 1;
        }
    }


    static function disable_adodb_debug()
    {
        global $objDatabase;

        if (!isset($objDatabase)) return;

        $objDatabase->debug = 0;
        self::$log_adodb = false;
    }


    static function disable_trace()
    {
        if (!self::$enable_trace) return;

        //self::_log('XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX DISABLING TRACE XXXXXXXXXXXXXXX');
        self::$enable_trace = 0;
    }


    static function enable_time()
    {
        if (!self::$enable_time) {
            //self::_log('XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX ENABLING TIME XXXXXXXXXXXXXXXXX');
            self::$enable_time = 1;
            self::$start_time = microtime(true);
            self::$last_time  = microtime(true);
        }
    }


    static function disable_time()
    {
        if (!self::$enable_time) return;

        //self::_log('XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX DISABLING TIME XXXXXXXXXXXXXXXX');
        self::$enable_time = 0;
    }


    static function enable_dump()
    {
        if (self::$enable_dump) return;

        //self::_log('XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX ENABLING DUMP XXXXXXXXXXXXXXXXX');
        self::$enable_dump = 1;
    }


    static function disable_dump()
    {
        if (!self::$enable_dump) return;

        //self::_log('XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX DISABLING DUMP XXXXXXXXXXXXXXXX');
        self::$enable_dump = 0;
    }


    static function enable_msg()
    {
        if (self::$enable_msg) return;

        //self::_log('XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX ENABLING MSG XXXXXXXXXXXXXXXXXX');
        self::$enable_msg = 1;
    }


    static function disable_msg()
    {
        if (!self::$enable_msg) return;

        //self::_log('XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX DISABLING MSG XXXXXXXXXXXXXXXXX');
        self::$enable_msg = 0;
    }


    static function enable_error_reporting()
    {
        self::$log_php = E_ALL;// | E_STRICT;
        error_reporting(self::$log_php);
        ini_set('display_errors', 1);

        if (!self::$firephp) {
            set_error_handler('DBG::phpErrorHandler');
        }
    }


    static function disable_error_reporting()
    {
        self::$log_php = 0;
        error_reporting(0);
        ini_set('display_errors', 0);
    }


    static function _cleanfile($f)
    {
        return substr($f, self::$fileskiplength);
    }


    static function trace($level=0)
    {
        if (self::$enable_trace) {
            $callers = debug_backtrace();
            $f = self::_cleanfile($callers[$level]['file']);
            $l = $callers[$level]['line'];
            self::_log("TRACE:  $f : $l");
        }
    }


    static function calltrace()
    {
        if (self::$enable_trace) {
            $level = 1;
            $callers = debug_backtrace();
            $c = isset($callers[$level]['class']) ? $callers[$level]['class'] : null;
            $f = $callers[$level]['function'];
            self::trace($level);
            $sf = self::_cleanfile($callers[$level]['file']);
            $sl = $callers[$level]['line'];
            self::_log("        ".(empty($c) ? $c : "$c::$f")." FROM $sf : $sl");
        }
    }


    static function dump($val)
    {
        if (!self::$enable_dump) return;
        if (self::$log_firephp) {
            self::$firephp->log($val);
            return;
        }
        ob_start();
        print_r($val);
        $out = ob_get_clean();
        $out = str_replace("\n", "\n        ", $out);
        if (!self::$log_file) {
            // we're logging directly to the browser
            self::_log('DUMP:   <p><pre>'.htmlentities($out, ENT_QUOTES, CONTREXX_CHARSET).'</pre></p>');
        } else {
            self::_log('DUMP:   '.$out);
        }
    }


    static function stack()
    {
        if (!self::$log_file && !self::$log_firephp) echo '<pre>';
        $callers = debug_backtrace();
        self::_log("TRACE:  === STACKTRACE BEGIN ===");
        $err = error_reporting(E_ALL ^ E_NOTICE);
        foreach ($callers as $c) {
            $file  = self::_cleanfile($c['file']);
            $line  = $c['line'];
            $class = isset($c['class']) ? $c['class'] : null;
            $func  = $c['function'];
            self::_log("        $file : $line (".(empty($class) ? $func : "$class::$func").")");
        }
        error_reporting($err);
        self::_log("        === STACKTRACE END ====");
        if (!self::$log_file && !self::$log_firephp) echo '</pre>';
    }


    static function msg($message)
    {
        if (self::$enable_msg) {
            self::_log('LOGMSG: '.$message);
        }
    }


    public static function phpErrorHandler($errno, $errstr, $errfile, $errline)
    {
        // this error handler methode is only used if we are logging to a file
        $suppressed = '';

        if (self::$log_php & $errno) {
            if (!error_reporting()) {
                $suppressed = ' (suppressed by script)';
            }

            switch ($errno) {
                case E_ERROR:
                    $type = 'FATAL ERROR';
                    break;
                case E_WARNING:
                    $type = 'WARNING';
                    break;
                case E_PARSE:
                    $type = 'PARSE ERROR';
                    break;
                case E_NOTICE:
                    $type = 'NOTICE';
                    break;
                case E_STRICT:
                    $type = 'STRICT';
                    break;
                default:
                    $type = $errno;
                    break;
            }

            if (self::$log_file) {
                self::_log("(php): $type$suppressed: $errstr in $errfile on line $errline");
            }
            if (!self::$log_file) {
                self::_log("<p><strong>$type</strong>$suppressed: $errstr in <strong>$errfile</strong> on line <strong>$errline</strong></p>");
            }
        }
    }


    static function log($text, $firephp_action='log', $additional_args=null)
    {
        self::_log($text, $firephp_action, $additional_args);
    }


    private static function _log($text, $firephp_action='log', $additional_args=null)
    {
        if (self::$log_firephp
            && method_exists(self::$firephp, $firephp_action)) {
            self::$firephp->$firephp_action($additional_args, $text);
        } elseif (self::$dbg_fh) {
            fputs(self::$dbg_fh, $text."\n");
        } else {
            echo $text;
        }
    }

    public static function setSQLQueryCache($msg)
    {
        self::$sql_query_cache = $msg;
    }

    public static function getSQLQueryCache()
    {
        return self::$sql_query_cache;
    }
}

function DBG_log_adodb($msg)
{
    $error = preg_match('#^[0-9]+:#', $msg);
    if ($error || !(DBG::getMode() & DBG_ADODB_ERROR) || DBG::getMode() & DBG_ADODB) {
        if ($error) {
            DBG::log(
                DBG::getMode() & DBG_LOG_FILE || DBG::getMode() & DBG_LOG_FIREPHP
                    ? html_entity_decode(strip_tags(DBG::getSQLQueryCache()), ENT_QUOTES, CONTREXX_CHARSET) : DBG::getSQLQueryCache(), 'error');
        }

        DBG::log(
            DBG::getMode() & DBG_LOG_FILE || DBG::getMode() & DBG_LOG_FIREPHP
                ? html_entity_decode(strip_tags($msg), ENT_QUOTES, CONTREXX_CHARSET)
                : $msg, $error
                    ? 'info'
                    : (preg_match('#\(mysql\):\s(UPDATE|DELETE|INSERT)#', $msg)
                        ? 'info' : 'log'));
    }
    DBG::setSQLQueryCache($msg);
}

?>
