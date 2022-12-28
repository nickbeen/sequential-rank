<?php

namespace NickBeen\SequentialRank;

class SequentialRank
{
    private array|string|null $order = null;

    public function __construct(private array $array)
    {
    }

    /**
     * Order data by provided array or enumeration.
     */
    public function orderBy(array|string|null $order): array
    {
        $this->order = $order;

        $this->convertToOrder();

        $this->sort();

        return $this->array;
    }

    /**
     * Convert array values to sequential ranks.
     */
    private function convertToOrder(): void
    {
        array_walk_recursive($this->array, function (&$array, $key, $type) {
            switch (true) {
                case is_array($type):
                    $array = $type[$array];

                    break;
                case enum_exists($type ?? ''):
                    $array = $type::tryFrom($array)->order();

                    break;
                default:
                    break;
            }
        }, $this->order ?? null);
    }

    /**
     * Sort array by natural sort with hyphened separations.
     */
    private function sort(): void
    {
        usort($this->array, function (array $a, array $b) {
            return strnatcmp(
                implode('-', $a),
                implode('-', $b)
            );
        });
    }

    /**
     * Return array with Sequential Ranks.
     */
    public function get(): array
    {
        $new_array = [];

        foreach ($this->array as $array) {
            $new_array_bit = [];

            foreach ($array as $item) {
                $new_array_bit[] = $item;
            }

            $new_array[] = implode('-', $new_array_bit);
        }

        return $new_array;
    }
}
