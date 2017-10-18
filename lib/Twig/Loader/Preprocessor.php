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
        $realSource = parent::getSourceContext($name);
        $srcCode = $realSource->getCode();

        // Kill all comments
        $repCnt = 0;
        $srcCode = preg_replace( '/\{#.*?#\}/s', '', $srcCode, -1, $repCnt );

        // Preprocess the input directive
        $matches = array();
        if( preg_match_all( '/\{%\s*input\s*[\'"](.*?)[\'"]\s*%\}/', $srcCode, $matches  ) ) {
          $this->dependencies = array_merge( $this->dependencies, $matches[1] );
          // Go on and replace the source
          foreach( $matches[1] as $file ) {
            $srcInclude = parent::getSourceContext($file)->getCode();
            $srcCode = preg_replace( '/\{%\s*input\s*[\'"]'.$file.'[\'"]\s*%\}/', $srcInclude, $srcCode, -1, $repCnt );
          }
        } else {
          // Nothing to do
          return $realSource;
        }

        if( $this->callback ) {
          return new Twig_Source(
              call_user_func($this->callback, $srcCode), $realSource->getName(), $realSource->getPath()
          );
        } else {
          return new Twig_Source($srcCode, $realSource->getName(), $realSource->getPath());
        }

    }
}
