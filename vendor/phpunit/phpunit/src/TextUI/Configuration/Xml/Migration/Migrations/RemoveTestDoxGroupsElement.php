<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\XmlConfiguration;

use DOMDocument;
use DOMElement;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class RemoveTestDoxGroupsElement implements Migration
{
    public function migrate(DOMDocument $document): void
    {
        $node = $document->getElementsByTagName('testdoxGroups')->item(0);

        if (!$node instanceof DOMElement || $node->parentNode === null) {
            return;
        }

        $node->parentNode->removeChild($node);
    }
}
