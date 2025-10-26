<?php

namespace App\Helpers;

class NumberToWords
{
    private static $ones = [
        '', 'یک', 'دو', 'سه', 'چهار', 'پنج', 'شش', 'هفت', 'هشت', 'نه',
        'ده', 'یازده', 'دوازده', 'سیزده', 'چهارده', 'پانزده', 'شانزده', 'هفده', 'هجده', 'نوزده'
    ];

    private static $tens = [
        '', '', 'بیست', 'سی', 'چهل', 'پنجاه', 'شصت', 'هفتاد', 'هشتاد', 'نود'
    ];

    private static $hundreds = [
        '', 'یکصد', 'دویست', 'سیصد', 'چهارصد', 'پانصد', 'ششصد', 'هفتصد', 'هشتصد', 'نهصد'
    ];

    private static $thousands = [
        '', 'هزار', 'میلیون', 'میلیارد', 'تریلیون', 'کوادریلیون', 'کوینتیلیون', 'سکستیلیون', 'سپتیلیون', 'اکتیلیون', 'نونیلیون', 'دسیلیون'
    ];

    public static function convert($number)
    {
        // Convert to string to handle large numbers
        $number = (string) $number;
        
        if ($number == '0') {
            return 'صفر';
        }

        if (strpos($number, '-') === 0) {
            return 'منفی ' . self::convert(substr($number, 1));
        }

        $result = '';
        $thousandIndex = 0;

        while (self::compareNumbers($number, '0') > 0) {
            $group = self::modulo($number, '1000');
            if (self::compareNumbers($group, '0') > 0) {
                $groupText = self::convertGroup($group);
                if ($thousandIndex > 0) {
                    $groupText .= ' ' . self::$thousands[$thousandIndex];
                }
                if ($result != '') {
                    $result = $groupText . ' و ' . $result;
                } else {
                    $result = $groupText;
                }
            }
            $number = self::divide($number, '1000');
            $thousandIndex++;
        }

        return $result;
    }

    private static function convertGroup($number)
    {
        $result = '';

        // Hundreds
        $hundred = self::divide($number, '100');
        if (self::compareNumbers($hundred, '0') > 0) {
            $result .= self::$hundreds[(int) $hundred];
        }

        // Tens and ones
        $remainder = self::modulo($number, '100');
        if (self::compareNumbers($remainder, '0') > 0) {
            if ($result != '') {
                $result .= ' و ';
            }

            $remainderInt = (int) $remainder;
            if ($remainderInt < 20) {
                $result .= self::$ones[$remainderInt];
            } else {
                $ten = intval($remainderInt / 10);
                $one = $remainderInt % 10;
                $result .= self::$tens[$ten];
                if ($one > 0) {
                    $result .= ' و ' . self::$ones[$one];
                }
            }
        }

        return $result;
    }

    private static function compareNumbers($a, $b)
    {
        // Simple string comparison for numbers
        $a = ltrim($a, '0') ?: '0';
        $b = ltrim($b, '0') ?: '0';
        
        if (strlen($a) > strlen($b)) return 1;
        if (strlen($a) < strlen($b)) return -1;
        
        return strcmp($a, $b);
    }

    private static function modulo($a, $b)
    {
        // Simple modulo for string numbers
        $a = ltrim($a, '0') ?: '0';
        $b = ltrim($b, '0') ?: '0';
        
        if (strlen($a) < 3) {
            return (string) ((int) $a % (int) $b);
        }
        
        // For larger numbers, use a simple approach
        $result = '';
        $carry = 0;
        
        for ($i = 0; $i < strlen($a); $i++) {
            $digit = (int) $a[$i] + $carry * 10;
            $result .= (string) intval($digit / (int) $b);
            $carry = $digit % (int) $b;
        }
        
        return (string) $carry;
    }

    private static function divide($a, $b)
    {
        // Simple division for string numbers
        $a = ltrim($a, '0') ?: '0';
        $b = ltrim($b, '0') ?: '0';
        
        if (strlen($a) < 3) {
            return (string) intval((int) $a / (int) $b);
        }
        
        // For larger numbers, use a simple approach
        $result = '';
        $carry = 0;
        
        for ($i = 0; $i < strlen($a); $i++) {
            $digit = (int) $a[$i] + $carry * 10;
            $result .= (string) intval($digit / (int) $b);
            $carry = $digit % (int) $b;
        }
        
        return ltrim($result, '0') ?: '0';
    }

    public static function formatWithSeparators($number)
    {
        // Handle large numbers with bcmath
        if (is_string($number) && strlen($number) > 15) {
            // For very large numbers, use manual formatting
            $formatted = '';
            $number = strrev($number);
            for ($i = 0; $i < strlen($number); $i++) {
                if ($i > 0 && $i % 3 == 0) {
                    $formatted .= ',';
                }
                $formatted .= $number[$i];
            }
            return strrev($formatted);
        }
        return number_format($number, 0, '.', ',');
    }

    public static function formatWithSeparatorsAndWords($number)
    {
        $formatted = self::formatWithSeparators($number);
        $words = self::convert($number);
        return [
            'formatted' => $formatted,
            'words' => $words
        ];
    }

    public static function convertToToman($number)
    {
        // Convert Rial to Toman (divide by 10)
        $toman = self::divide($number, '10');
        return self::convert($toman);
    }

    public static function formatWithSeparatorsAndWordsWithToman($number)
    {
        $formatted = self::formatWithSeparators($number);
        $words = self::convert($number);
        $tomanWords = self::convertToToman($number);
        $tomanAmount = self::formatWithSeparators(self::divide($number, '10'));
        
        return [
            'formatted' => $formatted,
            'words' => $words,
            'toman_words' => $tomanWords,
            'toman_amount' => $tomanAmount
        ];
    }
}
