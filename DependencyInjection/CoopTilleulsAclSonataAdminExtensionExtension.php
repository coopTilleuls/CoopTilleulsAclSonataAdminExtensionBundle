<?php

/*
 * This file is part of the CoopTilleulsAclSonataAdminExtensionBundle package.
 *
 * (c) La Coopérative des Tilleuls <contact@les-tilleuls.coop>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CoopTilleuls\Bundle\AclSonataAdminExtensionBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * {@inheritdoc}
 *
 * @author Kévin Dunglas <kevin@les-tilleuls.coop>
 */
class CoopTilleulsAclSonataAdminExtensionExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $container
            ->getDefinition('coop_tilleuls_acl_sonata_admin_extension.acl.extension')
            ->replaceArgument(
                0,
                new Reference(
                    interface_exists('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface')
                        ? 'security.token_storage'
                        : 'security.context'
                )
            )
        ;  
    }
}
