<?php

namespace App\Game\Generators;

use App\Models\Host\Size;
use App\Models\Host\Water;

/**
 * 1 - water
 * 2 - ground
 * 3 - hill
 * 4 - mountain
 */
class CellGenerator
{
    public function run(Size $size, Water $water): array
    {
        $map = [];

        $width = match ($size) {
            Size::x64 => 64,
            Size::x128 => 128,
            Size::x256 => 256,
        };
        $height = $width;

        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $map[$x][$y] = 2;
            }
        }

        $hillsAreasCount = match ($size) {
            Size::x64 => random_int(20, 40),
            Size::x128 => random_int(80, 120),
            Size::x256 => random_int(200, 400),
        };

        for ($i = 0; $i < $hillsAreasCount; $i++) {
            $x = random_int(0, $width-1);
            $y = random_int(0, $height-1);

            for ($j = 0; $j < 20; $j++) {
                [$px, $py] = [$x, $y];
                $map[$px][$py] = 3;
                
                while (random_int(1, 100) > 5) {
                    if (random_int(1, 2) == 1) {
                        $px += random_int(-1, 1);
                    } else {
                        $py += random_int(-1, 1);
                    }
                    $map[$px][$py] = 3;
                }
            }
        }


        $mountainAreasCount = match ($size) {
            Size::x64 => random_int(5, 10),
            Size::x128 => random_int(20, 40),
            Size::x256 => random_int(70, 180),
        };

        for ($i = 0; $i < $mountainAreasCount; $i++) {
            $x = random_int(0, $width-1);
            $y = random_int(0, $height-1);
    
            $map[$px][$py] = 4;

            for ($j = 0; $j < 4; $j++) {
                [$px, $py] = [$x, $y];
                
                while (random_int(1, 100) > 2) {
                    if (random_int(1, 2) == 1) {
                        $px += random_int(-1, 1);
                    } else {
                        $py += random_int(-1, 1);
                    }
                    $map[$px][$py] = 4;
                }
            }
        }


        $waterAreasCount = match ($size) {
            Size::x64 => match ($water) {
                Water::Low => random_int(1, 2),
                Water::Medium => random_int(2, 4),
                Water::High => random_int(4, 6),
            },
            Size::x128 => match ($water) {
                Water::Low => random_int(4, 8),
                Water::Medium => random_int(16, 24),
                Water::High => random_int(32, 48),
            },
            Size::x256 => match ($water) {
                Water::Low => random_int(10, 16),
                Water::Medium => random_int(40, 80),
                Water::High => random_int(70, 120),
            },
        };

        for ($i = 0; $i < $waterAreasCount; $i++) {
            $x = random_int(0, $width-1);
            $y = random_int(0, $height-1);

            $map[$x][$y] = 1;
            for ($j = 0; $j < 80; $j++) {
                [$px, $py] = [$x, $y];
                
                while (random_int(1, 100) > 2) {
                    if (random_int(1, 2) == 1) {
                        $px += random_int(-1, 1);
                    } else {
                        $py += random_int(-1, 1);
                    }
                    $map[$px][$py] = 1;
                }
            }
        }

        // $riversCount = match ($size) {
        //     Size::x64 => match ($water) {
        //         Water::Low => random_int(1, 2),
        //         Water::Medium => random_int(2, 4),
        //         Water::High => random_int(8, 12),
        //     },
        //     Size::x128 => match ($water) {
        //         Water::Low => random_int(4, 6),
        //         Water::Medium => random_int(8, 12),
        //         Water::High => random_int(12, 20),
        //     },
        //     Size::x256 => match ($water) {
        //         Water::Low => random_int(10, 16),
        //         Water::Medium => random_int(20, 30),
        //         Water::High => random_int(30, 40),
        //     },
        // };

        // $riverLength = match ($size) {
        //     Size::x64 => 64,
        //     Size::x128 => 128,
        //     Size::x256 => 256,
        // };

        // for ($i = 0; $i < $riversCount; $i++) {
        //     $x = random_int(0, $width-1);
        //     $y = random_int(0, $height-1);

        //     $map[$x][$y] = 1;
        //     $riverWidth = random_int(2, 4);
        //     $direction = random_int(1, 4);

        //     [$px, $py] = [$x, $y];

        //     $max = $riverLength;

        //     while ($max > 0) {
        //         $max--;
        //         if (random_int(1, 100) < 40) {
        //             if ($riverWidth == 4) {
        //                 $riverWidth--;
        //             } else if ($riverWidth == 0) {
        //                 $riverWidth++;
        //             } else {
        //                 $riverWidth += random_int(-1, 1);
        //             }
        //         }
        //         if (random_int(1, 100) < 40) {
        //             $direction = [
        //                 1 => [2,4],
        //                 2 => [1,3],
        //                 3 => [2,4],
        //                 4 => [1,3]
        //             ][$direction][random_int(0,1)];
        //         }

        //         switch ($direction) {
        //             case 1:
        //                 // top -> bottom
        //                 $py++;

        //                 $left = $px - floor($riverWidth / 2);
        //                 $right = $px + floor($riverWidth / 2);

        //                 while ($left <= $right) {
        //                     if (key_exists($left, $map) && key_exists($right, $map)) {
        //                         $map[$left][$py] = 1;
        //                     }
        //                     $left++;
        //                 }
        //             break;
        //             case 2:
        //                 // right -> left
        //                 $px--;

        //                 $top = $py - floor($riverWidth / 2);
        //                 $bottom = $py + floor($riverWidth / 2);

        //                 while ($top <= $bottom) {
        //                     if (key_exists($top, $map) && key_exists($bottom, $map)) {
        //                         $map[$px][$top] = 1;
        //                     }
        //                     $top++;
        //                 }
        //             break;
        //             case 3:
        //                 // bottom -> top
        //                 $py--;

        //                 $left = $px - floor($riverWidth / 2);
        //                 $right = $px + floor($riverWidth / 2);

        //                 while ($left <= $right) {
        //                     if (key_exists($left, $map) && key_exists($right, $map)) {
        //                         $map[$left][$py] = 1;
        //                     }
        //                     $left++;
        //                 }
        //             break;
        //             case 4:
        //                 // left -> right
        //                 $px++;

        //                 $top = $py - floor($riverWidth / 2);
        //                 $bottom = $py + floor($riverWidth / 2);

        //                 while ($top <= $bottom) {
        //                     if (key_exists($top, $map) && key_exists($bottom, $map)) {
        //                         $map[$px][$top] = 1;
        //                     }
        //                     $top++;
        //                 }
        //             break;
        //         }
        //     }
        // }


        return $map;
    }
}

