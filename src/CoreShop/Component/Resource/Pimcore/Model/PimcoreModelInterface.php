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

namespace CoreShop\Component\Resource\Pimcore\Model;

use CoreShop\Component\Resource\Model\ResourceInterface;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\Element\ElementInterface;

interface PimcoreModelInterface extends ResourceInterface, ElementInterface
{
    /**
     * @param string $key
     */
    public function setKey($key);

    /**
     * @return string
     */
    public function getKey();

    /**
     * @param bool $published
     */
    public function setPublished($published);

    /**
     * @return bool
     */
    public function getPublished();

    /**
     * @return bool
     */
    public function isPublished();

    /**
     * @param ElementInterface $parent
     */
    public function setParent($parent);

    /**
     * @return ElementInterface|null
     */
    public function getParent();

    /**
     * @return mixed
     */
    public function getObjectVar($field);

    /**
     * @return mixed
     */
    public function save();

    /**
     * @return mixed
     */
    public function delete();

    /**
     * @return ClassDefinition
     */
    public function getClass();
}
