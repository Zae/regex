<?php

namespace spec\Zae\Regex;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Zae\Regex\Regex;
use Zae\Regex\RegularExpressions;

class RegexSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('/here(goes)the[regex]/i');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Zae\Regex\Regex');
    }

    function it_can_match_regular_expressions()
    {
        $this->test('heregoesthexeger')->shouldReturn(true);
        $this->test('someotherrandomstring')->shouldReturn(false);
    }

    function it_can_exec_regular_expressions()
    {
        $this->exec('heregoesthexeger')->shouldReturn(['heregoesthex', 'goes']);
        $this->exec('someotherrandomstring')->shouldReturn(null);
    }

    function it_supports_custom_delimiters()
    {
        $this->beConstructedWith('#test#');

        $this->test('test')->shouldReturn(true);
        $this->test('something')->shouldReturn(false);
    }

    function it_supports_global_matches()
    {
        $this->beConstructedWith('/(test)/g');

        $this->exec('test')->shouldReturn([['test'], ['test']]);
        $this->exec('testtesttest')->shouldReturn([['test', 'test', 'test'], ['test', 'test', 'test']]);
    }

    function it_supports_replacing()
    {
        $this->replace('heregoestheregex', 'thatworks$1')->shouldReturn('thatworksgoesegex');
        $this->replace('someotherthing', 'thatworks$1')->shouldReturn('someotherthing');
    }

    function it_supports_global_matches_in_replacement()
    {
        $this->beConstructedWith('/test(some)string/ig');

        $this->replace('testsomestring', 'yes$1true')->shouldReturn('yessometrue');
        $this->replace('testsomestringsomethingelsetestsomestring', 'yes$1true')->shouldReturn('yessometruesomethingelseyessometrue');
    }

    function it_splits_strings()
    {
        $this->beConstructedWith('/[\s,]+/');

        $this->split('hypertext language, programming')->shouldReturn(['hypertext', 'language', 'programming']);
    }

    function it_splits_strings_more()
    {
        $this->beConstructedWith('/\|/');

        $this->split('hypertext|language|programming')->shouldReturn(['hypertext', 'language', 'programming']);
    }

    function it_supports_filtering()
    {
        $this->filter('heregoestheregex', 'thatworks$1')->shouldReturn('thatworksgoesegex');
        $this->filter('someotherthing', 'thatworks$1')->shouldReturn(null);
    }

    function it_supports_global_matches_in_filtering()
    {
        $this->beConstructedWith('/test(some)string/ig');

        $this->filter('testsomestring', 'yes$1true')->shouldReturn('yessometrue');
        $this->filter('testsomestringsomethingelsetestsomestring', 'yes$1true')->shouldReturn(null);
    }

    function it_can_automatically_quote_patterns()
    {
        $this->quote('some[text]')->shouldReturn('some\[text\]');
    }

    function it_can_show_errors()
    {
        $this->getLastError()->shouldReturn(PREG_NO_ERROR);
    }

    function it_supports_callables_as_replacement()
    {
        $this->beConstructedWith('|(\d{2}/\d{2}/)(\d{4})|');

        $this->replace("April fools day is 04/01/2002\nLast christmas was 12/24/2001\n", function ($matches)
        {
            return $matches[1].($matches[2]+1);
        })
        ->shouldReturn("April fools day is 04/01/2003\nLast christmas was 12/24/2002\n");
    }

    function it_supports_grep()
    {
        $this->beConstructedWith('/^(\d+)?\.\d+$/');

        $this->grep(["1.0", 1.1, 1, 2, 2.3, 4])->shouldReturn(["1.0", 1.1, 4 => 2.3]);
    }
}
