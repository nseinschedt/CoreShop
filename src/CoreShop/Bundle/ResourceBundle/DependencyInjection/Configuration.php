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

namespace CoreShop\Bundle\ResourceBundle\DependencyInjection;

use CoreShop\Bundle\ResourceBundle\Controller\ResourceController;
use CoreShop\Bundle\ResourceBundle\CoreShopResourceBundle;
use CoreShop\Bundle\ResourceBundle\Pimcore\PimcoreRepository;
use CoreShop\Component\Resource\Factory\Factory;
use CoreShop\Component\Resource\Factory\PimcoreFactory;
use CoreShop\Component\Resource\Translation\Provider\TranslationLocaleProviderInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('core_shop_resource');
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();

        $this->addResourcesSection($rootNode);
        $this->addTranslationsSection($rootNode);
        $this->addDriversSection($rootNode);
        $this->addPimcoreResourcesSection($rootNode);

        return $treeBuilder;
    }

    private function addResourcesSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('resources')
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('driver')->defaultValue(CoreShopResourceBundle::DRIVER_DOCTRINE_ORM)->end()
                            ->variableNode('options')->end()
                            ->scalarNode('templates')->cannotBeEmpty()->end()
                            ->arrayNode('classes')
                                ->isRequired()
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('model')->isRequired()->cannotBeEmpty()->end()
                                    ->scalarNode('interface')->cannotBeEmpty()->end()
                                    ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                    ->scalarNode('repository')->cannotBeEmpty()->end()
                                    ->scalarNode('factory')->defaultValue(Factory::class)->end()
                                ->end()
                            ->end()
                            ->arrayNode('translation')
                                ->children()
                                    ->variableNode('options')->end()
                                    ->arrayNode('classes')
                                        ->isRequired()
                                        ->addDefaultsIfNotSet()
                                        ->children()
                                            ->scalarNode('model')->isRequired()->cannotBeEmpty()->end()
                                            ->scalarNode('interface')->cannotBeEmpty()->end()
                                            ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                            ->scalarNode('repository')->cannotBeEmpty()->end()
                                            ->scalarNode('factory')->defaultValue(Factory::class)->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('pimcore')
                    ->useAttributeAsKey('name')
                        ->arrayPrototype()
                            ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('path')
                                    ->useAttributeAsKey('name')
                                    ->prototype('scalar')->end()
                                ->end()
                                ->arrayNode('classes')
                                    ->children()
                                        ->scalarNode('model')->isRequired()->cannotBeEmpty()->end()
                                        ->scalarNode('interface')->cannotBeEmpty()->end()
                                        ->scalarNode('repository')->cannotBeEmpty()->defaultValue(PimcoreRepository::class)->end()
                                        ->scalarNode('factory')->defaultValue(PimcoreFactory::class)->end()
                                        ->scalarNode('install_file')->end()
                                        ->scalarNode('type')->defaultValue(CoreShopResourceBundle::PIMCORE_MODEL_TYPE_OBJECT)->end()
                                        ->arrayNode('pimcore_controller')
                                            ->useAttributeAsKey('name')
                                            ->prototype('scalar')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addTranslationsSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('translation')
                    ->canBeDisabled()
                    ->children()
                        ->scalarNode('locale_provider')->defaultValue(TranslationLocaleProviderInterface::class)->cannotBeEmpty()->end()
                ->end()
            ->end();
    }

    private function addDriversSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('drivers')
                    ->defaultValue([CoreShopResourceBundle::DRIVER_DOCTRINE_ORM])
                    ->enumPrototype()->values(CoreShopResourceBundle::getAvailableDrivers())->end()
                ->end()
            ->end();
    }

    private function addPimcoreResourcesSection(ArrayNodeDefinition $node): void
    {
        $node->children()
            ->arrayNode('pimcore_admin')
                ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('js')
                        ->useAttributeAsKey('name')
                        ->prototype('scalar')->end()
                    ->end()
                    ->arrayNode('css')
                        ->useAttributeAsKey('name')
                        ->prototype('scalar')->end()
                    ->end()
                    ->arrayNode('editmode_js')
                        ->useAttributeAsKey('name')
                        ->prototype('scalar')->end()
                    ->end()
                    ->arrayNode('editmode_css')
                        ->useAttributeAsKey('name')
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
            ->end()
        ->end();
    }
}
