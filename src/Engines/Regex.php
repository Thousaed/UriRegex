<?php
declare(strict_types=1);
namespace Thousaed\UriRegex\Engines;

class Regex 
{
  protected int $length;
  
  public function __construct(
    protected array $parsed_tokens,
    protected array $config,
  ) {
    $this->length = count($parsed_tokens);
  }
  
  public static function ToRegex(array $parsed_tokens, array $config)
  {
    return (new Regex($parsed_tokens, $config))->generate();
  }
  
  protected function generate()
  {
    $trail      = $this->config['trail'] ?? true;
    $contraint  = $this->config['contraint'] ?? true;
    $sensitive  = ($this->config['sensitive'] ?? true) ? 'i' : '';
    $capture_pattern = ! ($this->config['capturePattern'] ?? false) ? '?:' : '';
    $segment    = [];
    
    foreach ($this->parsed_tokens as $key => $current) {
      if (gettype($current) == 'string') {
        array_push($segment, $this->escapeString($current));
        continue;
      }
      
      if ($current->name != 'wild') {
        $capture = (is_string($current->name) 
          ? '(?<' . $current->name . '>' 
          : '(' . $capture_pattern) . $current->pattern . ')';
        
        if ($current->optional) {
          $capture = '(?:' 
            . $this->escapeString($current->prefix) 
            . $capture
            . '?)?';
        } else {
          if ($current->prefix)
            array_push($segment, $this->escapeString($current->prefix));
        }
        
        array_push($segment, $capture);
        continue;
      }
      
      array_push($segment, $current->pattern);
    }
    
    if ($trail) {
      $last_segment = &$segment[count($segment) - 1];
      if ($last_segment == '\/') {
        $last_segment = $last_segment . '?';
      }
      else 
        array_push($segment, '\/?');
    }
    
    $string_segment = implode($segment);
    if ($contraint) {
      $string_segment = $this->addContraint($string_segment, $contraint);
    }
    
    return '/(?:' . $string_segment . ')/' . $sensitive;
  }
  
  protected function escapeString(string $data): string
  {
    $escape = '/[\\[\\]:\\/?&=#\\(\\){^$}\\.<\\*\\+>]/';
    return preg_replace_callback($escape, fn($match) => '\\' . $match[0], $data);
  }
  
  protected function addContraint(string $path, string | bool $contraint)
  {
    switch ($contraint) {
      case 'start':
        $path = '^' . $path;
        break;
      case 'end':
        $path = $path . '$';
        break;
      case true: 
        $path = '^' . $path . '$';
        break;
      case false:
        $path = $path;
        break;
      default:
        throw new Exception('Invalid contraint option \'' . $contraint . '\'');
    };
    return $path;
  }
}