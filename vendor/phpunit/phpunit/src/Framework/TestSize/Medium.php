<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\TestSize;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @immutable
 */
final readonly class Medium extends Known
{
    public function isMedium(): true
    {
        return true;
    }

    public function isGreaterThan(TestSize $other): bool
    {
        return $other->isSmall();
    }

    public function asString(): string
    {
        return 'medium';
    }
}
