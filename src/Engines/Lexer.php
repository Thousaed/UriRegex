<?php
declare(strict_types=1);

namespace Thousaed\UriRegex\Engines;

use Thousaed\UriRegex\Exceptions\UnclosedScopeException;
use Thousaed\UriRegex\Exceptions\UnfoundPatternException;
use Thousaed\UriRegex\Exceptions\BadPatternException;
use Exception;

class Lexer
{
  protected array $tokens  = [];
  protected string $string = '';
  protected int $idx       = 0;
  protected int $length;
  
  public function __construct(protected string $source)
  {
    $this->length  = strlen($source);
    $this->transform();
  }
  
  public static function Tokenize(string $source): array
  {
    return (new Lexer($source))->getTokens();
  }
  
  public function getTokens(): array
  {
    return $this->tokens;
  }
  
  private function transform(): void
  {
    while ($this->idx < $this->length) {
      $current = $this->source[$this->idx];
      
      if ($current == '(') {
        $pattern = '';
        $scope   = 1;
        $idx   = $this->idx + 1;
        
        if ($this->source[$idx] == '?') {
          throw new BadPatternException('pattern delimeter can not be treated as group at index ' . $idx);
        }
        
        while ($idx < $this->length) {
          $current = $this->source[$idx];
          
          if ($current == '(') $scope ++;
          else if ($current == ')') {
            $scope --;
            if ($scope == 0) break;
          }
          
          $pattern .= $current;
          $idx ++;
        }
        
        //ScopeNotClosed
        if ($scope)
          throw new UnclosedScopeException('pattern delimiter does not end properly at index ' . $idx);
          
        if ($pattern) {
          $this->pushToken('PATTERN', $pattern, $this->idx);
          $this->idx = $idx + 1;
          continue;
        }
        
        // PatternNotFound
        throw new UnfoundPatternException('pattern not found at index ' . $this->idx);
      }
      
      if ($current == '\\') {
        $this->pushToken('ESC_CHAR', idx: $this->idx ++, value: $this->source[$this->idx ++]);
        continue;
      }
      
      if ($current == '{') {
        $this->pushToken('OPEN', $current, $this->idx ++);
        continue;
      }
      
      if ($current == '}') {
        $this->pushToken('CLOSE', $current, $this->idx ++);
        continue;
      }
      
      if ($current == '*') {
        $this->pushToken('WILD', $current, $this->idx ++);
        continue;
      }
      
      if ($current == '?') {
        $this->pushToken('OPTIONAL', $current, $this->idx ++);
        continue;
      }
      
      if (ctype_alpha($current) || ord($current) == 95) {
        $this->string .= $current;
        $this->idx ++;
        continue;
      }
      
      $this->pushToken('CHAR', $current, $this->idx ++);
    }
    
    $this->pushToken('END', '', $this->idx);
  }
  
  private function pushToken(string $name, string $value, int $idx): void
  {
    $this->pushString();
    $entries = [ 'name' => $name, 'value' => $value, 'idx' => $idx ];
    array_push($this->tokens, (object) $entries);
  }
  
  private function pushString(): void
  {
    if (strlen($this->string)) {
      $str = $this->string;
      $this->string = '';
      $this->pushToken('STRING', $str, $this->idx - strlen($str));
    }
  }
  
}