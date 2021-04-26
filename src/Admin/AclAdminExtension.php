<?php

/*
 * This file is part of the CoopTilleulsAclSonataAdminExtensionBundle package.
 *
 * (c) La Coopérative des Tilleuls <contact@les-tilleuls.coop>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CoopTilleuls\Bundle\AclSonataAdminExtensionBundle\Admin;

use Doctrine\DBAL\Connection;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\Role\RoleInterface;

/**
 * Admin extension filtering the list.
 *
 * @author Kévin Dunglas <kevin@les-tilleuls.coop>
 */
class AclAdminExtension extends AbstractAdminExtension
{
    protected $tokenStorage;
    protected $databaseConnection;
    protected $roleHierarchy;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        Connection $databaseConnection,
        RoleHierarchyInterface $roleHierarchy
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->databaseConnection = $databaseConnection;
        $this->roleHierarchy = $roleHierarchy;
    }

    /**
     * Filters with ACL.
     *
     * @param AdminInterface      $admin
     * @param ProxyQueryInterface $query
     * @param string              $context
     *
     * @throws \RuntimeException
     */
    public function configureQuery(AdminInterface $admin, ProxyQueryInterface $query, $context = 'list')
    {
        // Don't filter for admins and for not ACL enabled classes and for command cli
        if (
            !$admin->isAclEnabled()
            || !$this->tokenStorage->getToken()
            || $admin->isGranted(sprintf($admin->getSecurityHandler()->getBaseRole($admin), 'ADMIN'))
        ) {
            return;
        }

        // Retrieve current logged user SecurityIdentity
        $user = $this->tokenStorage->getToken()->getUser();
        $userSecurityIdentity = UserSecurityIdentity::fromAccount($user);

        // Retrieve current logged user roles
        $userRoles = $user->getRoles();

        // Find child roles
        $roles = array();
        foreach ($userRoles as $userRole) {
            if (!$userRole instanceof RoleInterface && !$userRole instanceof Role) {
                $userRole = new Role($userRole);
            }

            $roles[] = $userRole;
        }

        $reachableRoles = $this->roleHierarchy->getReachableRoles($roles);

        // Get identity ACL user identifier
        $identifiers[] = sprintf('%s-%s', $userSecurityIdentity->getClass(), $userSecurityIdentity->getUsername());

        // Get identities ACL roles identifiers
        foreach ($reachableRoles as $reachableRole) {
            $role = $reachableRole->getRole();
            if (!in_array($role, $identifiers, true)) {
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
        if (method_exists($classStmt, 'executeQuery')) {
            $classStmt->executeQuery();
        } else {
            $classStmt->execute();
        }

        $statement = $classStmt->getWrappedStatement();
        if (method_exists($statement, 'fetchOne')) {
            $classId = $statement->fetchOne();
        } else {
            $classId = $statement->fetchColumn();
        }

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
                    MaskBuilder::MASK_OWNER,
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
                    \PDO::PARAM_INT,
                )
            );

            $ids = array();
            $rows = method_exists($entriesStmt, 'fetchAllAssociative') ? $entriesStmt->fetchAllAssociative()  : $entriesStmt->fetchAll();
            foreach ($rows as $row) {
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
