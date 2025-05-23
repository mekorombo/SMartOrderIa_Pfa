<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Attributes;

use Attribute;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final readonly class RequiresPhp
{
    /**
     * @var non-empty-string
     */
    private string $versionRequirement;

    /**
     * @param non-empty-string $versionRequirement
     */
    public function __construct(string $versionRequirement)
    {
        $this->versionRequirement = $versionRequirement;
    }

    /**
     * @return non-empty-string
     */
    public function versionRequirement(): string
    {
        return $this->versionRequirement;
    }
}
