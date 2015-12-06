<?php namespace Zae\Regex;

/**
 * @author Ezra Pool <ezra@tsdme.nl>
 * @license MIT
 */

use Illuminate\Support\Collection;

/**
 * Consistent interface for regular expressions
 *
 * @package Zae\Regex
 */
class Regex implements RegularExpressions
{
    private $pattern;

    const RGX_FLAGS_PATTERN = '/\/[^\/]*$/';

    const FLAG_GLOBAL = 'g';
    const FLAG_CASE_INSENSITIVE = 'i';

	/**
     * Regex constructor.
     *
     * @param string $pattern
     */
    public function __construct($pattern)
    {
        $this->pattern = $pattern;
    }

	/**
     * Test if the pattern is found in the provided subject
     *
     * @param string $subject
     *
     * @return bool
     */
    public function test($subject)
    {
        return (boolean)$this->match($subject);
    }

	/**
     * Searches subject for a match to the regular expression given in pattern.
     *
     * Supports ALL PCRE flags.
     *
     * @see preg_match
     *
     * @param string $subject
     *
     * @return null|array returns an array with the matches, null if the pattern was not found. $matches[0] will contain the text that matched the full pattern, $matches[1] will have the text that matched the first captured parenthesized subpattern, and so on.
     */
    public function exec($subject)
    {
        return $this->match($subject);
    }

	/**
     * Searches subject for matches to pattern and replaces them with replacement.
     *
     * It's possible to provide a callback as replacement, this will be called for all matches in the string.
     *
     * Supports ALL PCRE flags.
     *
     * @see preg_replace
     *
     * @param string $subject
     * @param string|callable $replacement The callback should return the replacement string.
     *
     * @return string
     */
    public function replace($subject, $replacement)
    {
        $func = (is_callable($replacement)) ? 'preg_replace_callback' : 'preg_replace';

        return $this->replaceOrFilter($subject, $replacement, $func);
    }

	/**
     * Split a subject using the provided pattern
     *
     * @see preg_split
     *
     * @param string $subject
     *
     * @return array
     */
    public function split($subject)
    {
        return preg_split($this->pattern, $subject);
    }

	/**
     * filter() is identical to replace() except it only returns the (possibly transformed) subjects where there was a match.
     *
     * @see replace
     * @see preg_filter
     *
     * @param string $subject
     * @param string $replacement
     *
     * @return string|null
     */
    public function filter($subject, $replacement)
    {
        return $this->replaceOrFilter($subject, $replacement, 'preg_filter');
    }

	/**
     * quote() takes str and puts a backslash in front of every character that is part of the regular expression syntax. This is useful if you have a run-time string that you need to match in some text and the string may contain special regex characters.
     *
     * The special regular expression characters are: . \ + * ? [ ^ ] $ ( ) { } = ! < > | : -
     *
     * @see preg_quote
     *
     * @param      $pattern
     * @param null $delimiter
     *
     * @return string
     */
    public static function quote($pattern, $delimiter = NULL)
    {
        return preg_quote($pattern, $delimiter);
    }

	/**
     * Returns the error code of the last PCRE regex execution
     *
     * @see preg_last_error
     *
     * @return int
     */
    public static function getLastError()
    {
        return preg_last_error();
    }

	/**
     * Returns the array consisting of the elements of the input array that match the given pattern.
     *
     * @see preg_grep
     *
     * @param array $subject
     *
     * @return array
     */
    public function grep(array $subject)
    {
        return preg_grep($this->pattern, $subject);
    }

    /**
     * Returns the pattern as a string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->pattern;
    }

	/**
	 * Makes sure that the pattern
	 *
	 * @return array
	 */
	function __sleep()
	{
		return ['pattern'];
	}

	/**
     * Wrapper function around preg_match and preg_match_all to provide a single API for regexes with the
     * global flag and without.
     *
     * @param string $subject
     *
     * @return null|array
     */
    private function match($subject)
    {
        if ($this->patternContainsFlag(self::FLAG_GLOBAL)) {
            $pattern = $this->patternSansFlag(self::FLAG_GLOBAL);

            $match = preg_match_all($pattern, $subject, $matches);
        } else {
            $match = preg_match($this->pattern, $subject, $matches);
        }

        return $match ? $matches : null;
    }

	/**
     * Get the flags only part of the pattern.
     *
     * @return null|string
     */
    private function getFlags()
    {
        if (preg_match(static::RGX_FLAGS_PATTERN, $this->pattern, $matches)) {
            return $matches[0];
        }

        return null;
    }

    /**
     * Special function to handle global matches by doing them separate in order.
     *
     * Supports the functions 'preg_match' and 'preg_filter'
     *
     * @param string $subject
     * @param string $replacement
     * @param array $matches
     * @param string $pattern
     * @param callable $func
     *
     * @return string|null
     */
    private function replaceOrFilter_all($subject, $replacement, array $matches, $pattern, $func)
    {
        return (new Collection($matches[0]))->map(function ($match) use ($pattern, $replacement, $func) {
            return $func($pattern, $replacement, $match);
        })->reduce(function ($subject, $replacement) use ($pattern, $func) {
            return $func($pattern, $replacement, $subject);
        }, $subject);
    }

    /**
     * Function to make replace and filter regexes consistent by providing support for the global flag via the same
     * API.
     *
     * @param string $subject
     * @param string $replacement
     * @param callable $func
     *
     * @return string|null
     */
    private function replaceOrFilter($subject, $replacement, $func)
    {
        if ($this->patternContainsFlag(self::FLAG_GLOBAL)) {
            $pattern = $this->patternSansFlag(self::FLAG_GLOBAL);

            $matches = (new static($this->pattern))->exec($subject);

            return $this->replaceOrFilter_all($subject, $replacement, $matches, $pattern, $func);
        }

        return $func($this->pattern, $replacement, $subject);
    }

    /**
     * Get the flags part of the pattern without the provided flag.
     *
     * @param string $flag
     *
     * @return string
     */
    private function patternSansFlag($flag)
    {
        $flags = $this->getFlags();

        $newFlags = str_replace($flag, '', $flags);

        return str_replace($flags, $newFlags, $this->pattern);
    }

    /**
     * Check if the pattern contains the provided flag.
     *
     * @param string $flag
     *
     * @return bool
     */
    private function patternContainsFlag($flag)
    {
        return str_contains($this->getFlags(), $flag);
    }
}
