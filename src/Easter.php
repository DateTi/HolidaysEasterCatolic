<?php
declare(strict_types=1);

namespace DateTi\Holidays\Easter\Catholic;

use DateTi\Holidays\EasterHolidayInterface;
use DateTime;

class Easter implements EasterHolidayInterface
{
    public function getMonday(int $year): DateTime
    {
        return self::monday($year);
    }

    public function getEaster(int $year): DateTime
    {
        return self::easter($year);
    }

    public function getGoodFriday(int $year): DateTime
    {
        return self::goodFriday($year);
    }

    public static function monday(int $year): DateTime
    {
        $easter = self::getEaster($year);
        $day = self::createDateTime($easter);
        $day->modify('+1 day');
        return $day;
    }

    public static function easter(int $year): DateTime
    {
        [$s1, $s2, $d, $e, $a] = self::getCalculableVars($year);
        return self::calculate($year, $s1, $s2, $d, $e, $a);
    }

    public static function goodFriday(int $year): DateTime
    {
        $easter = self::getEaster($year);
        $day = self::createDateTime($easter);
        $day->modify('-2 day');
        return $day;
    }

    private static function between(int $number, int $lowNumber, int $upNumber): bool
    {
        return ($number >= $lowNumber && $number <= $upNumber);
    }

    private static function createDateTime(DateTime $dateTime): DateTime
    {
        return new DateTime($dateTime->format('Y-m-d H:i:s.u'));
    }

    private static function getCalculableVars($year): array
    {
        [$a, $b, $c] = self::getCyclesVar($year);
        [$m, $n] = self::getEasterVar($year);
        $d = (((19 * $a) + $m) % 30);
        $e = (($n + (2 * $b) + (4 * $c) + (6 * $d)) % 7);

        $s1 = (22 + $d + $e);
        $s2 = ($d + $e - 9);

        return [$s1, $s2, $d, $e, $a];
    }

    private static function getCyclesVar(int $year): array
    {
        $a = ($year % 19); // cyklus stejnych dnu
        $b = ($year % 4); // cyklus prestupnych roku
        $c = ($year % 7); // dorovnani dne v tydnu

        return [$a, $b, $c];
    }

    private static function getEasterVar(int $year): array
    {
        if (self::between($year, 1800, 1899)) {
            return [23, 4];
        }

        if (self::between($year, 1900, 2099)) {
            return [24, 5];
        }

        return [1, 1];
    }

    private static function calculate(int $year, int $s1, int $s2, int $d, int $e, int $a): DateTime
    {
        $date = null;
        self::calculate1($date, $year, $s1);
        self::calculate2($date, $year, $s2, $d, $e, $a);
        self::calculate3($date, $year, $s2);
        self::calculate4($date, $year, $s2);

        return new DateTime($date ?? date('Y-m-d', easter_date($year)));
    }

    private static function calculate1(& $date, int $year, int $s1): void
    {
        if ($s1 >= 22 && $s1 <= 31) {
            $date = $year . '-03-' . $s1;
        }
    }

    private static function calculate2(& $date, int $year, int $s2, int $d, int $e, int $a): void
    {
        if ($date === null && self::checkCalculate2($s2, $d, $e, $a)) {
            $date = $year . '-04-18';
        }
    }

    private static function checkCalculate2(int $s2, int $d, int $e, int $a): bool
    {
        return ($s2 === 25 && $d === 28 && $e === 6 && $a > 10);
    }

    private static function calculate3(& $date, int $year, int $s2): void
    {
        if ($date === null && $s2 <= 25) {
            $date = $year . '-04-';
            if ($s2 <= 9) {
                $date .= '0';
            }
            $date .= $s2;
        }
    }

    private static function calculate4(& $date, int $year, int $s2): void
    {
        if ($date === null && $s2 > 25) {
            $date = $year . '-04-' . ($s2 - 7);
        }
    }
}
