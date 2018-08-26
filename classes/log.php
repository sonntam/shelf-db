<?php

namespace {

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

    public static function LogSQLQuery(string $query) {
      Log::Debug("SQL query:\n$query");
      return true;
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
        $logLine = (ConfigFile\Log::$logDateTime == true ? date('c') . " " : "" )
          . $level . Log::LOG_LEVEL_SUFFIX . Log::BuildTracePrefix(2)
          . Log::LOG_TEXT_PREFIX . $text . "\n";
        Log::$buffer = Log::$buffer. $logLine;

        if( ConfigFile\Log::$enableFileLogging ) {
          $filename = joinPaths(  absRoot(), ConfigFile\Log::$loggingDir, 'shelfdblog_'.date("Y-m-d").'.log' );
  	      file_put_contents( $filename, $logLine, FILE_APPEND);
        }
      }
    }

    public static function FetchLogContent()
    {
      $ret = Log::$buffer;
      Log::$buffer = "";
      return $ret;
    }

    public static function ShutdownPhpFunction() {
      $error = error_get_last();
      if ( $error["type"] == E_ERROR )
        LogPhpError( $error["type"], $error["message"], $error["file"], $error["line"], null );
    }

    public static function FormatPhpError($errno, $errstr, $errfile, $errline, $errcontext)
    {
      $logLine = "";

      if( Log::LOGLEVEL["error"] <= Log::LOGLEVEL[ConfigFile\Log::$logLevel] )
      {
        $logLine = (ConfigFile\Log::$logDateTime == true ? date('c') . " " : "" )
          . "PHP ERROR [$errno] " . Log::LOG_LEVEL_SUFFIX . Log::BuildTracePrefixPhpErrorHandler($errfile, $errline, $errcontext)
          . Log::LOG_TEXT_PREFIX . $errstr . "\n";
        Log::$buffer = Log::$buffer. $logLine;
      }
      return $logLine;

    }

    public static function LogPhpError($errno, $errstr, $errfile, $errline, $errcontext)
    {
      $logLine = Log::FormatPhpError( $errno, $errstr, $errfile, $errline, $errcontext );

      // Log to file if enabled
      if( ConfigFile\Log::$enableFileLogging && $logLine != "" ) {
        $filename = joinPaths(  absRoot(), ConfigFile\Log::$loggingDir, 'shelfdblog_'.date("Y-m-d").'.log' );
	      file_put_contents( $filename, $logLine , FILE_APPEND);
      }

      Log::PrintTerminationMessage("Shelf-DB Critical error",
        "Critical error...",
        "A critical error occured in file ". $errfile ." on line ". $errline .":\n\n".
        $errstr."\n\nHere is the full log:\n\n".Log::FetchLogContent()
      );
      exit;
    }

    public static function LogPhpException( Throwable $e ) {
      /*Log::print_messages_without_template('Part-DB: Schwerwiegender Fehler!', "§WOW",
                                      '<font color="red"><strong>Es ist ein schwerwiegender Fehler aufgetreten:'.
                                      '<br><br>'.nl2br($e->getMessage()).'</strong><br><br>'.
                                      '(Exception wurde geworfen in '.$e->getFile().', Zeile '.$e->getLine().')</font>');
                                    */
      //exit;
      Log::LogPhpError($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine(), null );
    }

    private static function BuildTracePrefix($step) {
      $tr = debug_backtrace();

      $fcnStep = ( $step + 1 >= count($tr) ? $step : $step + 1 );
      return $tr[$step]["file"].":".$tr[$step]["line"].":".$tr[$fcnStep]["function"]."()";
    }

    private static function BuildTracePrefixPhpErrorHandler($errfile, $errline, $errcontext) {
      return $errfile.":".$errline;
    }

    public static function PrintTerminationMessage($title, $subtitle, $message)
    {
        echo
            '<html>'.
            '<head>'.
            '<title>'.htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'</title>'.
            '<meta http-equiv="content-type" content="text/html; charset=utf-8">
                <style type="text/css">
                body {
                  background-color: #ffffff;
                  font-family: sans-serif;
                  font-size: 1em;
                  margin: 0;
                }
                .wrapper {
                  display: grid;
                  width: 100vw;
                  height: 100vh;
                  grid-gap: 0;
                  grid-template-columns: 1fr;
                  grid-template-rows: 4em 3em 1fr;
                }
                .header {
                  background-color: #2980B9;
                  font-size: 2em;
                  font-weight: bold;
                  grid-column: 1;
                  grid-row: 1;
                  color: #fff;
                  padding: 12px;
                  text-shadow: 0 0.1em 0 #000;
                }
                .subheader {
                  background-color: #2980B9;
                  font-size: 1.5em;
                  grid-column: 1;
                  grid-row: 2;
                  color: #fff;
                  padding: 12px;
                }
                .inner {
                  grid-column: 1;
                  grid-row: 3;
                  padding: 12px;
                }
                </style>'.
              '</head>'.
              '<body>'.
                '<div class=wrapper>
                  <div class=header>
                    Shelf-DB
                  </div>
                  <div class=subheader>'.
                    htmlspecialchars($subtitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').
                  '</div>
                  <div class=inner>'.
                    nl2br(htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')).
                  '</div>
                </div>'.
              '</body>'.
              '</html>';
    }
  }

  // Set php error handlers
  set_error_handler("Log::LogPhpError");
  set_exception_handler("Log::LogPhpException");

}

 ?>
