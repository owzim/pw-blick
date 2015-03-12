<?php

namespace owzim\Blick\utils;

class str
{
    /**
     * Format a string, replacing {key1}, {key2} or {0}, {1} etc. with values from
     * given array `$values`
     *
     * Example with numeric array
     * ```
     * str::format(
     *     'Hello {0}, you look {1}!',
     *     [Jane', 'stunning']
     * );
     * // returns 'Hello Jane, you look stunning!'
     * ```
     *
     * Example with assoc array
     * ```
     * str::format(
     *     'Hello {name}, you look {compliment}!',
     *     ['name' => 'Jane', 'compliment' => 'stunning']
     * );
     * // returns 'Hello Jane, you look stunning!'
     * ```
     *
     * @param  string $string
     * @param  array  $values
     * @return string The formatted string
     */
    public static function format($string, array $values)
    {
        foreach ($values as $key => $value) {
            $string = str_replace('{' . $key. '}', $value, $string);
        }
        return $string;
    }
}
