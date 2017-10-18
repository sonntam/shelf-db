<?php

/**
 * Twig Preprocessor loader that allows adding custom text filters for template strings.
 *
 * For instance, you can make Twig produce more readable output by stripping leading
 * spaces in lines with single control structure or comment:
 *
 * $loader = new Twig_Loader_Preprocessor($realLoader,
 *     function ($template) {
 *         return preg_replace('/^[ \t]*(\{([#%])[^}]*(?2)\})$/m', '$1', $template);
 *     }
 * );
 *
 * See also twig issue #1005: https://github.com/fabpot/Twig/issues/1005
 *
 * @author Igor Tarasov <tarasov.igor@gmail.com>
 */
class Twig_Loader_Preprocessor extends Twig_Loader_Filesystem
{
    private $callback;
    private $dependencies;

    /**
     * Constructor
     *
     * Callback should accept template string as the only argument and return the result
     *
     * @param Twig_LoaderInterface $loader A loader that does real loading of templates
     * @param callable $callback The processing callback
     */
    public function __construct($paths = array(), $rootPath = null, $callback = null)
    {
        parent::__construct($paths, $rootPath);

        $this->callback     = $callback;

        // Use this to store dependent files (caching! TODO)
        $this->dependencies = array();
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceContext($name)
    {
      // Get code
      $srcContext = parent::getSourceContext($name);
      $srcCode    = $srcContext->getCode();

      // Apply preprocessor
      $this->updateDependencies($name);
      $srcCode = $this->applyPreprocessor($name, $srcCode);

      if( $this->callback ) {
        return new Twig_Source(
            call_user_func($this->callback, $srcCode), $srcContext->getName(), $srcContext->getPath()
        );
      } else {
        return new Twig_Source($srcCode, $srcContext->getName(), $srcContext->getPath());
      }
    }

    private function applyPreprocessor( $name, $srcCode ) {

      foreach( $this->dependencies[$name] as $file ) {
        $srcInclude = parent::getSourceContext($file)->getCode();
        $srcCode = preg_replace( '/\{%\s*input\s*[\'"]'.$file.'[\'"]\s*%\}/', $srcInclude, $srcCode, 1, $repCnt );
        if( $repCnt !== 1 ) {
          throw new Exception("Unknown error during input operation in $name < $file");
        }
      }

      return $srcCode;
    }

    private function updateDependencies($basename, $name = null, $root = array()) {

      if( array_key_exists( $basename, $this->dependencies ) && !empty($this->dependencies[$basename]) && empty($name) )
        return;

      if( empty($name) ) $name = $basename;

      // Initialize
      if( empty($root) ) $root = array($basename);

      if( !array_key_exists( $basename, $this->dependencies ) ) $this->dependencies[$basename] = array();

      $srcCode = parent::getSourceContext($name)->getCode();

      // Kill all comments
      $repCnt = 0;
      $srcCode = preg_replace( '/\{#.*?#\}/s', '', $srcCode, -1, $repCnt );

      // Find input statements
      if( preg_match_all( '/\{%\s*input\s*[\'"](.*?)[\'"]\s*%\}/', $srcCode, $matches  ) ) {
        $this->dependencies[$basename] = array_merge( $this->dependencies[$basename], $matches[1] );

        foreach( $matches[1] as $file ) {
          // Check for circulary dependencies
          if( in_array( $file, $root ) ) {
            throw new Exception("Error while using input statement for $name due to circular dependency.");
          }

          // Recursive call
          $newRoot = $root;
          array_push($newRoot, $file);
          $this->updateDependencies($basename, $file, $newRoot);
        }
      }
    }

    public function isFresh($name, $time)
    {
      $fresh = parent::isFresh($name, $time);

      if( array_key_exists( $name, $this->dependencies ) ) {
          foreach( $this->dependencies[$name] as $dep ) {
            $fresh = $fresh & parent::isFresh($dep, $time);
          }
      } else {
        $this->updateDependencies($name);
        return $this->isFresh($name,$time);
      }

      return $fresh;
    }
}
