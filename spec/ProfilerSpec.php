<?php

namespace spec\Sharils;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProfilerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Sharils\Profiler');
    }

    function it_returns_report() {
        $this->setUp('class C{}; $c = new C;');
        $this->profile([
            'method_exists($c, "asdf")',
            'is_callable([$c, "asdf"])'
        ])->shouldBeLikeObjects([
            (object) [
                'snippet' => 'method_exists($c, "asdf")',
                'filename' => '/tmp/php-profiler-6393db8fc3419cdbe69bb936052d55c3',
                'time' => 0.56867098808289,
            ],
            (object) [
                'snippet' => 'is_callable([$c, "asdf"])',
                'filename' => '/tmp/php-profiler-73d65b5e2fe1987efce58f48f88bf229',
                'time' => 0.82373595237732,
            ]
        ]);
    }

    public function getMatchers()
    {
        return [
            'beLikeObjects' => function ($subject, $expected) {
                $subject = array_map(function ($subject, $expected) {
                    $subject->time = $expected->time;
                    return $subject;
                }, $subject, $expected);
                return $subject == $expected;
            }
        ];
    }
}
