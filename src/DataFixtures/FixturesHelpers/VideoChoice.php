<?php

namespace App\DataFixtures\FixturesHelpers;

class VideoChoice
{
  public function choice($trick)
  {
    switch ($trick) {
      case "Mute": {
          return "https://www.youtube.com/embed/k6aOWf0LDcQ?si=fenCYMkQ0c_FDu5W";
        }
      case "Indy": {
          return "https://www.youtube.com/embed/6yA3XqjTh_w?si=zdIzphZKn_4psxfA";
        }
      case "Backflip": {
          return "https://www.youtube.com/embed/SlhGVnFPTDE?si=O2-Oids2wTGGy-6_";
        }
      case "Frontflip": {
          return "https://www.youtube.com/embed/BVeAbNIHktE?si=3p1JV77ma07BMIQY";
        }
      case "360": {
          return "https://www.youtube.com/embed/grXpguVaqls?si=5Em-cjhqYyTvqC4I";
        }
      case "720": {
          return "https://www.youtube.com/embed/4JfBfQpG77o?si=if9L7X7tvoOswfnW";
        }
      case "Misty": {
          return "https://www.youtube.com/embed/hPuVJkw1MmI?si=Dz_3jSZEOyXoY32J";
        }
      case "Tail slide": {
          return "https://www.youtube.com/embed/HRNXjMBakwM?si=Du1VGwbAeME7vfkU";
        }
      case "Method air": {
          return "https://www.youtube.com/embed/qMsN26DBLVo?si=VweNtvkgm7sxXTgx";
        }
      case "Backside air": {
          return "https://www.youtube.com/embed/_CN_yyEn78M?si=WvfqKdLEi0IEm831";
        }
    }
  }
}
