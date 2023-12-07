<?php

namespace App\Helpers;

class Paginator
{
  public function paginate(array $items, string $currentPage, int $limit): ?array

  {
    $totalItems = count($items);
    $offset = ($currentPage - 1) * $limit;

    $totalPages = ceil($totalItems / $limit);

    $itemsReverse = \array_reverse($items);

    $newItems = array_slice($itemsReverse, $offset, $limit);

    return [
      $newItems,
      $totalPages
    ];
  }
}
