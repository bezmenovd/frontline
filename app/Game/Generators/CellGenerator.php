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

        // ground
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $map[$x][$y] = 2;
            }
        }

        // hills
        $hillsAreasCount = match ($size) {
            Size::x64 => random_int(20, 40),
            Size::x128 => random_int(80, 120),
            Size::x256 => random_int(200, 400),
        };

        $hillsPoints = [];
        $hillsPointsIdx = -1;

        for ($i = 0; $i < $hillsAreasCount; $i++) {
            if (random_int(1, 100) < 80 && isset($hillsPoints[$hillsPointsIdx]) && is_array($hillsPoints[$hillsPointsIdx])) {
                $x = $hillsPoints[$hillsPointsIdx][0] + [random_int(5,10), random_int(-10,-5)][random_int(0, 1)];
                $x = max($x, 0);
                $x = min($x, $width-1);
                $y = $hillsPoints[$hillsPointsIdx][1] + [random_int(5,10), random_int(-10,-5)][random_int(0, 1)];
                $y = max($y, 0);
                $y = min($y, $width-1);
            } else {
                $x = random_int(0, $width-1);
                $y = random_int(0, $height-1);
            }
            $hillsPointsIdx++;
            $hillsPoints[] = [$x, $y];

            for ($j = 0; $j < random_int(20, 40); $j++) {
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

        // mountains
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                if (in_array($map[$x][$y], [3,4]) 
                    && ($x <= 0 || in_array($map[$x-1][$y], [3,4]))
                    && ($x >= $width-1 || in_array($map[$x+1][$y], [3,4]))
                    && ($y <= 0 || in_array($map[$x][$y-1], [3,4]))
                    && ($y >= $height-1 || in_array($map[$x][$y+1], [3,4]))
                ) {
                    if (random_int(1,100) < 80) {
                        $map[$x][$y] = 4;
                    }
                }
            }
        }

        $hillsPoints = [];
        $hillsPointsIdx = -1;

        // hills again
        for ($i = 0; $i < $hillsAreasCount; $i++) {
            if (random_int(1, 100) < 95 && isset($hillsPoints[$hillsPointsIdx]) && is_array($hillsPoints[$hillsPointsIdx])) {
                $x = $hillsPoints[$hillsPointsIdx][0] + [random_int(5,10), random_int(-10,-5)][random_int(0, 1)];
                $x = max($x, 0);
                $x = min($x, $width-1);
                $y = $hillsPoints[$hillsPointsIdx][1] + [random_int(5,10), random_int(-10,-5)][random_int(0, 1)];
                $y = max($y, 0);
                $y = min($y, $width-1);
            } else {
                $x = random_int(0, $width-1);
                $y = random_int(0, $height-1);
            }

            for ($j = 0; $j < random_int(20, 40); $j++) {
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

        // ocean and seas
        $waterAreasCount = match ($size) {
            Size::x64 => 1,
            Size::x128 => 4,
            Size::x256 => 16,
        } * match ($water) {
            Water::Low => random_int(10, 20),
            Water::Medium => random_int(40, 80),
            Water::High => random_int(50, 100),
        };

        $waterPoints = [];
        $waterPointsIdx = -1;

        for ($i = 0; $i < $waterAreasCount; $i++) {
            if (random_int(1, 500) < 495 && isset($waterPoints[$waterPointsIdx]) && is_array($waterPoints[$waterPointsIdx])) {
                $x = $waterPoints[$waterPointsIdx][0] + [random_int(2,8), random_int(-8,-2)][random_int(0, 1)];
                $x = max($x, 0);
                $x = min($x, $width-1);
                $y = $waterPoints[$waterPointsIdx][1] + [random_int(2,8), random_int(-8,-2)][random_int(0, 1)];
                $y = max($y, 0);
                $y = min($y, $width-1);
            } else {
                $x = random_int(0, $width-1);
                $y = random_int(0, $height-1);
            }
            $waterPointsIdx++;
            $waterPoints[] = [$x, $y];

            for ($j = 0; $j < random_int(20, 40); $j++) {
                [$px, $py] = [$x, $y];
                $map[$px][$py] = 1;
                
                while (random_int(1, 100) > 5) {
                    if (random_int(1, 2) == 1) {
                        $px += random_int(-1, 1);
                    } else {
                        $py += random_int(-1, 1);
                    }
                    $map[$px][$py] = 1;
                }
            }
        }

        // smoothing
        for ($i = 0; $i < 5; $i++) {
            for ($x = 1; $x < $width-1; $x++) {
                for ($y = 1; $y < $height-1; $y++) {
                    $neighbors = [
                        $map[$x-1][$y-1],
                        $map[$x-1][$y],
                        $map[$x-1][$y+1],
                        $map[$x][$y-1],
                        $map[$x][$y],
                        $map[$x][$y+1],
                        $map[$x+1][$y-1],
                        $map[$x+1][$y],
                        $map[$x+1][$y+1],
                    ];

                    $counts = [];
                    foreach ($neighbors as $type) {
                        $counts[$type] = isset($counts[$type]) ? $counts[$type] + 1 : 1;
                    }

                    $maxType = array_keys($counts)[0];
                    foreach ($counts as $type => $count) {
                        if ($count > $counts[$maxType]) {
                            $maxType = $type;
                        }
                    }

                    if ($counts[$maxType] > 6) {
                        $map[$x][$y] = $maxType;
                    }
                }
            }
        }

        // rivers
        $riversCount = match ($size) {
            Size::x64 => match ($water) {
                Water::Low => random_int(0, 5),
                Water::Medium => random_int(5, 10),
                Water::High => random_int(10, 15),
            },
            Size::x128 => match ($water) {
                Water::Low => random_int(0, 10),
                Water::Medium => random_int(10, 15),
                Water::High => random_int(15, 20),
            },
            Size::x256 => match ($water) {
                Water::Low => random_int(0, 10),
                Water::Medium => random_int(10, 20),
                Water::High => random_int(20, 30),
            },
        } * 0.6;

        $riverLength = match ($size) {
            Size::x64 => 32,
            Size::x128 => 64,
            Size::x256 => 128,
        };

        for ($i = 0; $i < $riversCount; $i++) {
            [$x, $y] = $waterPoints[random_int(0, count($waterPoints)-1)];

            $map[$x][$y] = 1;
            $direction = random_int(1, 4);

            while (random_int(0, $riverLength*10) > 10) {
                if (random_int(0, 100) < 50) {
                    $direction = [
                        1 => [2,8],
                        2 => [1,3],
                        3 => [2,4],
                        4 => [5,3],
                        5 => [4,6],
                        6 => [5,7],
                        7 => [6,8],
                        8 => [7,1],
                    ][$direction][random_int(0, 1)];
                }

                $x = match($direction) {
                    1 => $x+1,
                    2 => $x+1,
                    3 => $x,
                    4 => $x-1,
                    5 => $x-1,
                    6 => $x-1,
                    7 => $x,
                    8 => $x+1,
                };
                $y = match($direction) {
                    1 => $y,
                    2 => $y+1,
                    3 => $y+1,
                    4 => $y+1,
                    5 => $y,
                    6 => $y-1,
                    7 => $y-1,
                    8 => $y-1,
                    default => $y
                };

                if ($x < 0 || $x >= $width || $y < 0 || $y >= $height) {
                    break;
                }

                $map[$x][$y] = 1;
            }
        }


        foreach ($map as $i => $row) {
            if (count($row) > $height) {
                $map[$i] = array_slice($row, 0, $height);
            }
        }

        return $map;
    }
}

