<?php
declare(strict_types=1);

namespace Thousaed\UriRegex\Util;

class DuplicateOffset
{
  public static function Filter(array $assoc_offset_array): array
  {
    $offsets = [];
    
    $filtered = array_filter($assoc_offset_array, function ($item) use(&$offsets) {
      $in_arr = in_array($item[1], $offsets);
      $offsets[] = $item[1];
      return ! $in_arr;
    });
    
    return $filtered;
  }
}