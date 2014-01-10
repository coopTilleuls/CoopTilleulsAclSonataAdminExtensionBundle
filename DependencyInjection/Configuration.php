<?php

/*
 * (c) La Coopérative des Tilleuls <contact@les-tilleuls.coop>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 *
 * (c) JUILLARD YOANN <juillard.yoann@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE2.
 */

namespace MrGreenStuff\Bundle\AclSonataAdminExtensionBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 *
 * @author Kévin Dunglas <kevin@les-tilleuls.coop>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $treeBuilder->root('mr_green_stuff_acl_sonata_admin_extension');

        return $treeBuilder;
    }
}
