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

namespace CoreShop\Bundle\IndexBundle\Form\Type\Interpreter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Valid;

final class NestedInterpreterType extends AbstractType
{
    /**
     * @param string[] $validationGroups
     */
    public function __construct(protected array $validationGroups)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('interpreters', InterpreterCollectionType::class, [
                'constraints' => [
                    new Valid(['groups' => $this->validationGroups]),
                ],
            ]);
    }

    public function getBlockPrefix(): string
    {
        return 'coreshop_index_interpreter_nested';
    }
}
