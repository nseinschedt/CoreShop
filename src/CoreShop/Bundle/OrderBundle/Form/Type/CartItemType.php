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

namespace CoreShop\Bundle\OrderBundle\Form\Type;

use CoreShop\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use CoreShop\Component\Order\Model\OrderItemInterface;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

final class CartItemType extends AbstractResourceType
{
    public function __construct(
        string $dataClass,
        array $validationGroups,
        private DataMapperInterface $dataMapper
    ) {
        parent::__construct($dataClass, $validationGroups);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $data = $event->getData();

            if (!$data instanceof OrderItemInterface) {
                return;
            }

            $event->getForm()->add('quantity', QuantityType::class, [
                'html5' => true,
                'label' => 'coreshop.ui.quantity',
                'disabled' => (bool)$data->getIsGiftItem(),
            ]);
        });

        $builder->setDataMapper($this->dataMapper);
    }

    public function getBlockPrefix(): string
    {
        return 'coreshop_cart_item';
    }
}
