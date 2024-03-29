<?php
declare(strict_types=1);
namespace Thousaed\UriRegex;

use Thousaed\UriRegex\Engines\Lexer;
use Thousaed\UriRegex\Engines\Parser;
use Thousaed\UriRegex\Engines\Regex;
use Thousaed\UriRegex\Util\DuplicateOffset;

require __DIR__ . '/../vendor/autoload.php';

/**
 * The object is responsible for generating a regular expression from a given input string.
 * It's provides methods for matching and testing the generated regular expression with an 
 * actual URI object or string.
 * 
 * **config**
 * - sensitive
 *   If set to true the match will be case sensitive.
 * 
 * - ignore_space
 *   If set to true spaces found on the param and pattern block will
 *   be ignored.
 * 
 * - pattern
 *   This is a default pattern that's set for wildcards or named params
 *   that does not have a pattern.
 * 
 * - trail
 *   This option includes a optional trailing slash to the end of the 
 *   pattern matcher if one is one present else makes it optuonal
 * 
 * - capturePattern
 *   This includes pattern's to the array of parameter returned as a result
 *   of matching the expression
*/
class Pattern 
{
  protected string $regex;
  /**
   * @param {string} $source
   *  A string pattern that matches URI string,
   * 
   * @param {array} $config 
   *  An optional array of config option with possible options.
  */
  public function __construct(string $source, array $config = []) 
  {
    
    $parsed       = Parser::Parse(Lexer::Tokenize($source), $config);
    $this->regex  = Regex::ToRegex($parsed, $config);
  }
  
  /**
   * @param {string} $target
   * The URI of which the regular expression will be compared against.
   * 
   * @returns {bool}
  */
  public function test(string $target): bool
  {
    return (bool) preg_match($this->regex, $target);
  }
  
    /**
   * @param {string} $target
   * The URI of which the regular expression will be compared against.
   * 
   * @returns {array}
  */
  public function match(string $target)
  {
    preg_match($this->regex, $target, $match, PREG_OFFSET_CAPTURE);
    $match = array_map(fn($arr) => $arr[0], array_merge(DuplicateOffset::Filter($match)));
    return $match;
  }
}  

