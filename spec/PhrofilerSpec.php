<?php

namespace spec\Sharils;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PhrofilerSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('Sharils\Phrofiler');
    }

    public function it_returns_report()
    {
        $this->setUp('class C{}; $c = new C;');
        $this->profile([
            'method_exists($c, "asdf")',
            'is_callable([$c, "asdf"])'
        ])->shouldBeLikeObjects([
            (object) [
                'snippet' => 'method_exists($c, "asdf")',
                'timeFilename' => '/tmp/php-phrofiler-time-5efbc207d3658fa41610a683c5495893',
                'wholeFilename' => '/tmp/php-phrofiler-whole-5efbc207d3658fa41610a683c5495893',
                'time' => 0.56867098808289,
            ],
            (object) [
                'snippet' => 'is_callable([$c, "asdf"])',
                'timeFilename' => '/tmp/php-phrofiler-time-7283ed75d96b5dd92e2f7b7d03caa985',
                'wholeFilename' => '/tmp/php-phrofiler-whole-7283ed75d96b5dd92e2f7b7d03caa985',
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
