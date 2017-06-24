<?php

namespace {

  require_once(__DIR__.'/config.php');

  class Log {

    private const LOG_LEVEL_SUFFIX = " - ";
    private const LOG_TEXT_PREFIX = " => ";
    private static $buffer = "";

    private const LOGLEVEL = array( "none" => 0, "error" => 1, "warning" => 2, "info" => 3, "debug" => 4);

    public static function Warning(string $text) {
      Log::LogWithLevelTrace("WARNING",$text);
    }

    public static function WarningSQLQuery(string $query, mysqli $sqlobj) {
      Log::Warning("SQL query:\n$query\nresulted in an error.\n".
        "Error #".$sqlobj->errno." -> ".$sqlobj->error
      );
    }

    public static function Error(string $text) {
      Log::LogWithLevelTrace("ERROR",$text);
    }

    public static function Debug(string $text) {
      Log::LogWithLevelTrace("DEBUG",$text);
    }

    public static function Info(string $text) {
      Log::LogWithLevelTrace("INFO",$text);
    }

    public static function DumpToLogFile() {
      file_put_contents( "log.txt", Log::FetchLogContent() );
    }

    private static function LogWithLevelTrace(string $level, string $text)
    {
      if( Log::LOGLEVEL[strtolower($level)] <= Log::LOGLEVEL[ConfigFile\Log::$logLevel] )
      {
        Log::$buffer = Log::$buffer. (ConfigFile\Log::$logDateTime == true ? date('c') . " " : "" )
          . $level . Log::LOG_LEVEL_SUFFIX . Log::BuildTracePrefix(2)
          . Log::LOG_TEXT_PREFIX . $text . "\n";
      }
    }

    public static function FetchLogContent()
    {
      $ret = Log::$buffer;
      Log::$buffer = "";
      return $ret;
    }

    public static function LogPhpError($errno, $errstr, $errfile, $errline, $errcontext)
    {
      if( Log::LOGLEVEL["error"] <= Log::LOGLEVEL[ConfigFile\Log::$logLevel] )
      {
        Log::$buffer = Log::$buffer. (ConfigFile\Log::$logDateTime == true ? date('c') . " " : "" )
          . "PHP ERROR [$errno] " . Log::LOG_LEVEL_SUFFIX . Log::BuildTracePrefixPhpErrorHandler($errfile, $errline, $errcontext)
          . Log::LOG_TEXT_PREFIX . $errstr . "\n";
      }
    }

    private static function BuildTracePrefix($step) {
      $tr = debug_backtrace();
      return $tr[$step]["file"].":".$tr[$step]["line"].":".$tr[$step+1]["function"]."()";
    }

    private static function BuildTracePrefixPhpErrorHandler($errfile, $errline, $errcontext) {
      return $errfile.":".$errline;
    }

  }

  // Set php error handlers
  set_error_handler("Log::LogPhpError");

}

 ?>
