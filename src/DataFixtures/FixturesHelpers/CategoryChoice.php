<?php

namespace App\DataFixtures\FixturesHelpers;

use App\Entity\Category;


class CategoryChoice
{
  public function choice(string $trick, array $categories): ?Category
  {
    switch ($trick) {
      case "Mute": {
          return $categories[0];
        }
      case "Indy": {
          return $categories[0];
        }
      case "Backflip": {
          return $categories[2];
        }
      case "Frontflip": {
          return $categories[2];
        }
      case "360": {
          return $categories[1];
        }
      case "720": {
          return $categories[1];
        }
      case "Misty": {
          return $categories[3];
        }
      case "Tail slide": {
          return $categories[4];
        }
      case "Method air": {
          return $categories[6];
        }
      case "Backside air": {
          return $categories[6];
        }
    }
  }
}
