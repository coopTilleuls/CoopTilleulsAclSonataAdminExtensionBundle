<?php

/*
 * (c) La Coopérative des Tilleuls <contact@les-tilleuls.coop>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace CoopTilleuls\Bundle\AclSonataAdminExtensionBundle\Admin;

use Sonata\AdminBundle\Admin\AdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\RoleHierarchy;
use Symfony\Component\Security\Core\Role\RoleInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;

/**
 * Admin extension filtering the list
 *
 * @author Kévin Dunglas <kevin@les-tilleuls.coop>
 */
class AclAdminExtension extends AdminExtension
{
    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;
    /**
     * @var Connection
     */
    protected $databaseConnection;

    /**
     * @var RoleHierarchy
     */
    protected $roleHierarchy;

    /**
     * @param SecurityContextInterface $securityContext
     * @param Connection               $databaseConnection
     * @param array                    $roleHierarchy
     */
    public function __construct(SecurityContextInterface $securityContext, Connection $databaseConnection, $roleHierarchy)
    {
        $this->securityContext = $securityContext;
        $this->databaseConnection = $databaseConnection;
        $this->roleHierarchy = new RoleHierarchy($roleHierarchy);
    }

    /**
     * Filters with ACL
     *
     * @param  AdminInterface      $admin
     * @param  ProxyQueryInterface $query
     * @param  string              $context
     * @throws \RuntimeException
     */
    public function configureQuery(AdminInterface $admin, ProxyQueryInterface $query, $context = 'list')
    {
        // Don't filter for admins and for not ACL enabled classes and for command cli
        if (
            !$admin->isAclEnabled()
            || !$this->securityContext->getToken()
            || $admin->isGranted(sprintf($admin->getSecurityHandler()->getBaseRole($admin), 'ADMIN'))
        ) {
            return;
        }

        // Retrieve current logged user SecurityIdentity
        $user = $this->securityContext->getToken()->getUser();
        $userSecurityIdentity = UserSecurityIdentity::fromAccount($user);

        // Retrieve current logged user roles
        $userRoles = $user->getRoles();

        // Find child roles
        $roles = array();
        foreach ($userRoles as $userRole) {
            $roles[] = ($userRole instanceof RoleInterface) ? $userRole : new Role($userRole);
        }

        $reachableRoles = $this->roleHierarchy->getReachableRoles($roles);

        // Get identity ACL user identifier
        $identifiers[] = sprintf('%s-%s', $userSecurityIdentity->getClass(), $userSecurityIdentity->getUsername());

        // Get identities ACL roles identifiers
        foreach ($reachableRoles as $reachableRole) {
            $role = $reachableRole->getRole();
            if (!in_array($role, $identifiers)) {
                $identifiers[] = $role;
            }
        }

        $identityStmt = $this->databaseConnection->executeQuery(
            'SELECT id FROM acl_security_identities WHERE identifier IN (?)',
            array($identifiers),
            array(Connection::PARAM_STR_ARRAY)
        );

        $identityIds = array();
        foreach ($identityStmt->fetchAll() as $row) {
            $identityIds[] = $row['id'];
        }

        // Get class ACL identifier
        $classType = $admin->getClass();
        $classStmt = $this->databaseConnection->prepare('SELECT id FROM acl_classes WHERE class_type = :classType');
        $classStmt->bindValue('classType', $classType);
        $classStmt->execute();

        $classId = $classStmt->fetchColumn();

        if (!empty($identityIds) && $classId) {
            $entriesStmt = $this->databaseConnection->executeQuery(
                'SELECT DISTINCT object_identifier FROM acl_entries AS ae JOIN acl_object_identities AS aoi ON ae.object_identity_id = aoi.id WHERE ae.class_id = ? AND ae.security_identity_id IN (?) AND (? = ae.mask & ? OR ? = ae.mask & ? OR ? = ae.mask & ? OR ? = ae.mask & ?)',
                array(
                    $classId,
                    $identityIds,
                    MaskBuilder::MASK_VIEW,
                    MaskBuilder::MASK_VIEW,
                    MaskBuilder::MASK_OPERATOR,
                    MaskBuilder::MASK_OPERATOR,
                    MaskBuilder::MASK_MASTER,
                    MaskBuilder::MASK_MASTER,
                    MaskBuilder::MASK_OWNER,
                    MaskBuilder::MASK_OWNER
                ),
                array(
                    \PDO::PARAM_INT,
                    Connection::PARAM_INT_ARRAY,
                    \PDO::PARAM_INT,
                    \PDO::PARAM_INT,
                    \PDO::PARAM_INT,
                    \PDO::PARAM_INT,
                    \PDO::PARAM_INT,
                    \PDO::PARAM_INT,
                    \PDO::PARAM_INT,
                    \PDO::PARAM_INT
                )
            );

            $ids = array();
            foreach ($entriesStmt->fetchAll() as $row) {
                $ids[] = $row['object_identifier'];
            }

            if (count($ids)) {
                $query
                    ->andWhere('o IN (:ids)')
                    ->setParameter('ids', $ids)
                ;

                return;
            }
        }

        // Display an empty list
        $query->andWhere('1 = 2');
    }
}
