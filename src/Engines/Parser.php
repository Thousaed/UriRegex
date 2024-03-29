<?php
declare(strict_types=1);

namespace Thousaed\UriRegex\Engines;
use Exception;

class Parser
{
  protected int $length;
  protected int $idx = 0;
  
  public function __construct(
    protected array $tokens, 
    protected array $config
  ) 
  {
    $this->length = count($tokens);
  }
  
  public static function Parse(array $tokens, array $config): array
  {
    return (new Parser($tokens, $config))->generate();
  }
  
  protected function generate() {
    //configs
    $default_pattern = $this->config['pattern'] ?? '[^#?\\/]+';
    $ignore_space    = $this->config['ignore_space'] ?? false;
    
    $parse_list = [];
    
    while ($this->idx < $this->length) {
      $char     = $this->should_take('CHAR');
      $open     = $this->should_take('OPEN');
      $pattern  = $this->should_take('PATTERN');
      
      if ($open || $pattern) {
        $prefix = $char ?? '';
        if ($prefix && $prefix != '/') {
          array_push($parse_list, $prefix);
          $prefix = '';
        }
        
        $variables = [
          'prefix'   => $prefix,
          'name'     => $open ? $this->must_take('STRING', $ignore_space) : 0,
          'pattern'  => $this->should_take('PATTERN', $ignore_space) ?? $pattern ?? $default_pattern,
          'optional' => $pattern ? $this->should_take('OPTIONAL', $ignore_space) : ''
        ];
        
        if ($open) {
          $this->must_take('CLOSE', $ignore_space);
          $variables['optional'] = $this->should_take('OPTIONAL', $ignore_space) ?? '';
        }
        
        array_push($parse_list, (object) $variables);
        continue;
      }
      
      $paths = $char 
      ?? $this->should_take('STRING')
      ?? $this->should_take('SPACE')
      ?? $this->should_take('ESC_CHAR');
      
      if ($paths) {
        array_push($parse_list, $paths);
        continue;
      }
      
      if ($this->should_take('WILD')) {
        array_push($parse_list, (object) [ 
          'name'     => 'wild', 
          'pattern'  => '(?:[^#?\\/]*)',
        ]);
        continue;
      }
      
      break;
    }
      
    $this->must_take('END');
    return $parse_list;
  }

  private function should_take (string $name, bool $ignore_space = false): ?string {
    $idx = $this->idx;
    while ($idx < $this->length && $this->tokens[$idx]->name == 'space' && $ignore_space) {
      $idx ++;
    }
    
    if ($idx < $this->length && $this->tokens[$idx]->name == $name) {
      $this->idx = $idx;
      return $this->tokens[$this->idx ++]->value;
    }
    
    $this->idx = $idx;
    return null;
  }

  private function must_take (string $name, bool $ignore_space = false): Error | string {
    $value = '';
    if ($this->idx < $this->length && is_null($value = $this->should_take($name, $ignore_space))) {
      $current = $this->tokens[$this->idx];
      throw new Exception('Unexcepted "' . $current->value . '" at index ' . $current->idx . '. expecting ' . $name);
    }
    
    return $value;
  }
}