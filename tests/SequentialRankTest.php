<?php

namespace NickBeen\SequentialRank\Tests;

use NickBeen\SequentialRank\SequentialRank;
use PHPUnit\Framework\TestCase;

class SequentialRankTest extends TestCase
{
    public array $order = [
        'red' => 1,
        'blue' => 2,
        'yellow' => 3,
    ];

    public array $array = [
        ['yellow', 'blue', 'red'],
        ['blue', 'yellow', 'red'],
        ['yellow', 'red', 'blue'],
        ['red', 'yellow', 'blue'],
    ];

    public function test_if_it_can_order_by_array()
    {
        $expected = [
            ['1', '3', '2'],
            ['2', '3', '1'],
            ['3', '1', '2'],
            ['3', '2', '1'],
        ];

        $seqRank = new SequentialRank($this->array);
        $results = $seqRank->orderBy($this->order);

        self::assertEquals($expected, $results);

        $expected = [
            '1-3-2',
            '2-3-1',
            '3-1-2',
            '3-2-1',
        ];

        $results = $seqRank->get();

        self::assertEquals($expected, $results);
    }

    public function test_if_it_can_order_by_enum()
    {
        $expected = [
            ['1', '3', '2'],
            ['2', '3', '1'],
            ['3', '1', '2'],
            ['3', '2', '1'],
        ];

        $seqRank = new SequentialRank($this->array);
        $results = $seqRank->orderBy(Order::class);

        self::assertEquals($expected, $results);

        $expected = [
            '1-3-2',
            '2-3-1',
            '3-1-2',
            '3-2-1',
        ];

        $results = $seqRank->get();

        self::assertEquals($expected, $results);
    }

    public function test_if_it_can_order_without_provided_order()
    {
        $expected = [
            ['blue', 'yellow', 'red'],
            ['red', 'yellow', 'blue'],
            ['yellow', 'blue', 'red'],
            ['yellow', 'red', 'blue'],
        ];

        $seqRank = new SequentialRank($this->array);
        $results = $seqRank->orderBy(null);

        self::assertEquals($expected, $results);

        $expected = [
            'blue-yellow-red',
            'red-yellow-blue',
            'yellow-blue-red',
            'yellow-red-blue',
        ];

        $results = $seqRank->get();

        self::assertEquals($expected, $results);
    }
}
