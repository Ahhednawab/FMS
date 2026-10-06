<?php

namespace App\Services;

/**
 * Amount to English words for invoices, e.g.
 * 62560 -> "Rupees Sixty-Two Thousand Five Hundred and Sixty Only".
 *
 * Written by hand on purpose: PHP's intl extension (NumberFormatter) is not
 * installed on this server, so its spell-out formatter is unavailable.
 */
class AmountInWords
{
    private const UNITS = [
        0 => 'Zero', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four',
        5 => 'Five', 6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
        10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen',
        15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen',
    ];

    private const TENS = [
        2 => 'Twenty', 3 => 'Thirty', 4 => 'Forty', 5 => 'Fifty',
        6 => 'Sixty', 7 => 'Seventy', 8 => 'Eighty', 9 => 'Ninety',
    ];

    private const SCALES = [
        ['value' => 1000000000, 'name' => 'Billion'],
        ['value' => 1000000, 'name' => 'Million'],
        ['value' => 1000, 'name' => 'Thousand'],
    ];

    /**
     * Full invoice wording: "Rupees ... Only", with paisa when the amount
     * carries a fractional part.
     */
    public static function rupees(mixed $amount): string
    {
        $amount = round((float) $amount, 2);
        $negative = $amount < 0;
        $amount = abs($amount);

        $rupees = (int) floor($amount);
        $paisa = (int) round(($amount - $rupees) * 100);

        if ($paisa === 100) { // rounding carried into the rupees
            $rupees++;
            $paisa = 0;
        }

        $words = 'Rupees ' . self::words($rupees);

        if ($paisa > 0) {
            $words .= ' and Paisa ' . self::words($paisa);
        }

        return ($negative ? 'Minus ' : '') . $words . ' Only';
    }

    /** Whole number in words: 62560 -> "Sixty-Two Thousand Five Hundred and Sixty". */
    public static function words(int $number): string
    {
        if ($number === 0) {
            return self::UNITS[0];
        }

        $parts = [];

        foreach (self::SCALES as $scale) {
            if ($number >= $scale['value']) {
                $parts[] = self::underThousand(intdiv($number, $scale['value'])) . ' ' . $scale['name'];
                $number %= $scale['value'];
            }
        }

        if ($number > 0) {
            $parts[] = self::underThousand($number);
        }

        return implode(' ', $parts);
    }

    /** 1-999, the way an invoice reads it: 560 -> "Five Hundred and Sixty". */
    private static function underThousand(int $number): string
    {
        $words = [];

        if ($number >= 100) {
            $words[] = self::UNITS[intdiv($number, 100)] . ' Hundred';
            $number %= 100;

            if ($number > 0) {
                $words[] = 'and';
            }
        }

        if ($number > 0) {
            $words[] = self::underHundred($number);
        }

        return implode(' ', $words);
    }

    private static function underHundred(int $number): string
    {
        if ($number < 20) {
            return self::UNITS[$number];
        }

        $tens = self::TENS[intdiv($number, 10)];
        $unit = $number % 10;

        return $unit === 0 ? $tens : $tens . '-' . self::UNITS[$unit];
    }
}
