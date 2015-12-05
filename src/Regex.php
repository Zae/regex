<?php namespace Zae\Regex;

use Illuminate\Support\Collection;

/**
 * Class Regex
 *
 * @package Zae\Regex
 */
class Regex
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
     * @param string $subject
     *
     * @return bool
     */
    public function test($subject)
    {
        return (boolean)$this->match($subject);
    }

	/**
     * @param string $subject
     *
     * @return null|array
     */
    public function exec($subject)
    {
        return $this->match($subject);
    }

	/**
     * @param string $subject
     * @param string $replacement
     *
     * @return string
     */
    public function replace($subject, $replacement)
    {
        return $this->replaceOrFilter($subject, $replacement, 'preg_replace');
    }

	/**
     * @param string $subject
     *
     * @return array
     */
    public function split($subject)
    {
        return preg_split($this->pattern, $subject);
    }

	/**
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
     * @param string $flag
     *
     * @return bool
     */
    private function patternContainsFlag($flag)
    {
        return str_contains($this->getFlags(), $flag);
    }
}
