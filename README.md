# Sequential Rank

[![Latest version](https://img.shields.io/packagist/v/nickbeen/sequential-rank)](https://packagist.org/packages/nickbeen/sequential-rank)
[![Build status](https://img.shields.io/github/workflow/status/nickbeen/sequential-rank/Run%20tests)](https://packagist.org/packages/nickbeen/sequential-rank)
[![Total downloads](https://img.shields.io/packagist/dt/nickbeen/sequential-rank)](https://packagist.org/packages/nickbeen/sequential-rank)
[![PHP Version](https://img.shields.io/packagist/php-v/nickbeen/sequential-rank)](https://packagist.org/packages/nickbeen/sequential-rank)
[![License](https://img.shields.io/packagist/l/nickbeen/sequential-rank)](https://packagist.org/packages/nickbeen/sequential-rank)

Simple ranking system for ordering results by sequence. Usually you'll need to sort your results alphabetically or chronologically (or even manually), but what if you need to order specific fields based on their exact order. Doable when working with justs integers or a handful of possible sequences, but can get quite complicated with dozens of possible sequences. Be sure to check the [example](#example) if the concept sounds too vague.

Sequential Ranking is a simple ranking system that relies on ordering instructions provided by you. While number based ranking systems require updating many -if not all- models, ranking systems such as Sequential Rank only need to update the re-ordered model.

## Requirements

- PHP 8.1 or higher

## Installation

Install the library into your project with Composer.

```
composer require nickbeen/sequential-rank
```

## Usage

The library requires the data to be injected into the constructor as an array. You can provide instructions for ordening with either an array or an enum. While `get()` returns the sequential ranks, you can also just call `orderBy()` to get your original array in the correct order.

### Order by array

The most common way to provide instructions for ordering is to use an associative array. When working with databases, constructing an array is the most straight-forward choice.

```php
$order = [
    'red' => 1,
    'blue' => 2,
    'yellow' => 3,
];

$array = [
    ['yellow', 'blue', 'red'],
    ['red', 'blue', 'yellow'],
];

$seqRank = new SequentialRank($array);
$seqRank->orderBy($order);
$result = $seqRank->get();
```

```php
$result = [
    '1-2-3', // red, blue, yellow
    '3-2-1', // yellow, blue, red
];
```

### Order by enum

Another way to provide instructions for ordering is to use an enumeration. Enums allows you to bundle both the values and the provided order into one file. Useful when you want your application instead of your database to keep tabs of the desired order.

Your enum must provide an `order()` method containing the order you want. This implementation ensures your data and order stay decoupled to remove any friction when adding or updating the model.

```php
enum Colors: string {
    case RED = 'red';
    case BLUE = 'blue';
    case YELLOW = 'yellow';
    
    public function order(): int {
        return match ($this) {
            self::RED => 1,
            self::BLUE => 2,
            self::YELLOW => 3,
        }
    }
}
```

```php
$array = [
    ['yellow', 'blue', 'red'],
    ['red', 'blue', 'yellow'],
];

$seqRank = new SequentialRank($array);
$seqRank->orderBy(Colors::class);
$result = $seqRank->get();
```

```php
$result = [
    '1-2-3', // red, blue, yellow
    '3-2-1', // yellow, blue, red
];
```

### Order without provided order

If no order is provided, the results will be ordered by [natural sort](https://en.wikipedia.org/wiki/Natural_sort_order). In short this means strings will be ordered in alphabetical order while multi-digit numbers are treated atomically. This is only useful when your values and display order are identical.

```php
$array = [
    ['yellow', 'blue', 'red'],
    ['red', 'blue', 'yellow'],
];

$seqRank = new SequentialRank($array);
$seqRank->orderBy(null)
$result = $seqRank->get();
```

```php
$result = [
    'red-blue-yellow',
    'yellow-blue-red',
];
```

## Example

We need to document a list of attacks of a video game. Each attack has its own input consisting of a sequence of buttons. To keep things readable, we need to display the list of attacks in a specific order. First, we need to document which buttons could exist in the list of attacks, and while we're at it, decide on a display order for these buttons.

```php
enum Buttons: string {
    case FORWARD = 'forward';
    case DOWN = 'down';
    case BACK = 'back';
    case UP = 'up';
    case X = 'x';
    case Y = 'y';
    case A = 'a';
    case B = 'b';
    
    public function order(): int {
        return match ($this) {
            self::FORWARD => 10,
            self::DOWN => 20,
            self::BACK => 30,
            self::UP => 40,
            self::X => 50,
            self::Y => 60,
            self::A => 70,
            self::B => 80
        }
    }
}
```

Notice how we increased the order by 10 for each button. This allows us to add new buttons in the future without needing to reorganise and re-order the whole list of attacks. If you expect to frequently add new fields, you should use bigger gaps. Since the whole ranking system is driven by natural sorting, you can also use more complicated values to e.g. categorize values like `a-10` or `d1-a4`.

Anyway, now let's think of a list of attacks containing any of these 8 buttons.

```php
$attacks = [
    ['down', 'forward', 'y'],
    ['forward', 'down', 'y'],
    ['up', 'x'],
    ['x', 'y', 'a'],
    ['forward', 'forward', 'b'],
    ['back', 'a', 'y'],
    ['forward', 'back', 'x'],
    ['back', 'x', 'down', 'y', 'a'],
];
```

Let's start using Sequential Rank to reorder the list of attacks to be more readable.

```php
$seqRank = new SequentialRank($attacks);
$seqRank->orderBy(Buttons::class);
$result = $seqRank->get();
```

We're calling `get()` to get the actual Sequential Ranks returned to us.

```php
$result = [
    '10-10-80', // forward, forward, b
    '10-20-60', // forward, down, y
    '10-30-50', // forward, back, x
    '20-10-60', // down, forward, y
    '30-50-20-60-70', // back, x, down, y ,a
    '30-70-60', // back, a, y
    '40-50', // up, x
    '50-60-70', // x, y, a
];
```

When working with a database, you can save the Sequential Rank with the attack data and instruct your database to order by Sequential Rank to get what we want.

```sql
SELECT id, buttons, sequential_rank
FROM attacks
ORDER BY sequential_rank
```

| id  | buttons | sequential_rank |
| --- | ------- | --------------- |
| 5 | forward, forward, b | 10-10-80 |
| 2 | forward, down, y | 10-20-60 |
| 7 | forward, back, x | 10-30-50 |
| 1 | down, forward, y | 20-10-60 |
| 8 | back, x, down, y ,a | 30-50-20-60-70 |
| 6 | back, a, y | 30-70-60 |
| 3 | up, x | 40-50 |
| 4 | x, y, a | 50-60-70 |

## FAQ

### What are the pitfalls of Sequential Rank?
A poorly constructed order (e.g. 1,2,3,4) can force you to reorganize your order and recalculate the Sequential Ranks of all your models. One could debate if a string-based field is less performant than an integer based id, but putting an index on the Sequential Rank in your database will definitely help. Lastly, Sequential Rank is not battle-tested.

If you need something more robust, find a package that incorporates Lexorank, the ranking system that drives the drag 'n drop ordering in [JIRA](https://en.wikipedia.org/wiki/Jira_(software)). Lexorank offers better tools for easy reordering, but however require you to calculate the exact positions due to its agnostic state.

### How should I store Sequential Ranks in my database?
I recommend using a VARCHAR(255) column unless you're absolutely positively able to predict the future changes to the order and structure of your data.

## License

This library is licensed under the MIT License (MIT). See the [LICENSE](LICENSE.md) for more details.
