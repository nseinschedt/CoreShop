<?php
/**
 * CoreShop.
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) CoreShop GmbH (https://www.coreshop.org)
 * @license    https://www.coreshop.org/license     GNU General Public License version 3 (GPLv3)
 */

declare(strict_types=1);

namespace CoreShop\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use CoreShop\Bundle\ThemeBundle\Service\ThemeResolverInterface;
use Webmozart\Assert\Assert;

final class ThemeContext implements Context
{
    public function __construct(private ThemeResolverInterface $themeResolver)
    {
    }

    /**
     * @Then /^the current theme name should be "([^"]+)"$/
     */
    public function currentThemeNameIs(string $currentThemeName): void
    {
        $theme = $this->themeResolver->resolveTheme();

        Assert::same($theme, $currentThemeName);
    }
}
