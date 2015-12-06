<?php namespace Zae\Regex;

/**
 * @author      Ezra Pool <ezra@tsdme.nl>
 * @license		MIT
 */

/**
 * Consistent interface for regular expressions
 *
 * @package Zae\Regex
 */
interface RegularExpressions
{
	/**
	 * Test if the pattern is found in the provided subject
	 *
	 * @param string $subject
	 *
	 * @return bool
	 */
	public function test($subject);

	/**
	 * Searches subject for a match to the regular expression given in pattern.
	 *
	 * Supports ALL PCRE flags.
	 *
	 * @see preg_match
	 *
	 * @param string $subject
	 *
	 * @return null|array returns an array with the matches, null if the pattern was not found. $matches[0] will
	 *                    contain the text that matched the full pattern, $matches[1] will have the text that matched
	 *                    the first captured parenthesized subpattern, and so on.
	 */
	public function exec($subject);

	/**
	 * Searches subject for matches to pattern and replaces them with replacement.
	 *
	 * It's possible to provide a callback as replacement, this will be called for all matches in the string.
	 *
	 * Supports ALL PCRE flags.
	 *
	 * @see preg_replace
	 *
	 * @param string          $subject
	 * @param string|callable $replacement The callback should return the replacement string.
	 *
	 * @return string
	 */
	public function replace($subject, $replacement);

	/**
	 * Split a subject using the provided pattern
	 *
	 * @see preg_split
	 *
	 * @param string $subject
	 *
	 * @return array
	 */
	public function split($subject);

	/**
	 * filter() is identical to replace() except it only returns the (possibly transformed) subjects where there was a
	 * match.
	 *
	 * @see replace
	 * @see preg_filter
	 *
	 * @param string $subject
	 * @param string $replacement
	 *
	 * @return string|null
	 */
	public function filter($subject, $replacement);

	/**
	 * quote() takes str and puts a backslash in front of every character that is part of the regular expression
	 * syntax. This is useful if you have a run-time string that you need to match in some text and the string may
	 * contain special regex characters.
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
	public static function quote($pattern, $delimiter = null);

	/**
	 * Returns the error code of the last PCRE regex execution
	 *
	 * @see preg_last_error
	 *
	 * @return int
	 */
	public static function getLastError();

	/**
	 * Returns the array consisting of the elements of the input array that match the given pattern.
	 *
	 * @see preg_grep
	 *
	 * @param array $subject
	 *
	 * @return array
	 */
	public function grep(array $subject);
}