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

namespace CoreShop\Behat\Page\Pimcore;

use Behat\Mink\Element\DocumentElement;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Session;

abstract class AbstractPimcoreTabPage implements PimcoreTabPageInterface
{
    protected ?DocumentElement $document = null;

    protected ?NodeElement $tabElement = null;

    public function __construct(protected Session $session, protected array | \ArrayAccess $minkParameters = [])
    {
    }

    abstract protected function getLayoutId(): string;

    protected function getDocument(): DocumentElement
    {
        if (null === $this->document) {
            $this->document = new DocumentElement($this->session);
        }

        return $this->document;
    }

    protected function getTabElement(): NodeElement
    {
        if (null === $this->tabElement) {
            $this->tabElement = $this->getDocument()->find('css', '#pimcore_panel_tabs #' . $this->getLayoutId());
        }

        return $this->tabElement;
    }

    public function isActiveOpen(): bool
    {
        return !str_contains($this->getTabElement()->getAttribute('class'), 'x-hidden-offsets');
    }

    public function makeActive(): void
    {
        $element = $this->getTabElement();
        $id = $element->getAttribute('id');

        $this->session->executeScript(sprintf('Ext.getCmp(\'%s\').activate()', $id));
    }

    public function close(): void
    {
        $element = $this->getTabElement();
        $id = $element->getAttribute('id');

        $this->session->executeScript(sprintf('Ext.getCmp(\'%s\').destroy()', $id));
    }

    protected function extjsComponentQuery(string $query, string $componentId = null): NodeElement
    {
        $js = "Elements.DOMPath.xPath(Ext.getCmp('" . ($componentId ?? $this->getLayoutId()) . "').query('" . $query . "')[0].el.dom, true)";

        $xpath = $this->session->evaluateScript($js);

        if (!$this->getDocument()->has('xpath', $xpath)) {
            throw new ElementNotFoundException($this->session, 'Element add-button not found', 'xpath', $js);
        }

        return $this->getDocument()->find('xpath', $xpath);
    }

    protected function extsDocumentQuery(string $query): NodeElement
    {
        $js = "Elements.DOMPath.xPath(Ext.ComponentQuery.query('" . $query . "')[0].el.dom, true)";

        $xpath = $this->session->evaluateScript($js);

        if (!$this->getDocument()->has('xpath', $xpath)) {
            throw new ElementNotFoundException($this->session, 'Element add-button not found', 'xpath', $js);
        }

        return $this->getDocument()->find('xpath', $xpath);
    }
}
