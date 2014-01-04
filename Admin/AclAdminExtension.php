<?php

namespace CoopTilleuls\Bundle\AclSonataAdminExtensionBundle\Admin;

use Sonata\AdminBundle\Admin\AdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;

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
     * @param SecurityContextInterface $securityContext
     * @param Connection               $databaseConnection
     */
    public function __construct(SecurityContextInterface $securityContext, Connection $databaseConnection)
    {
        $this->securityContext = $securityContext;
        $this->databaseConnection = $databaseConnection;
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
        if (!$admin->isAclEnabled() || !$this->securityContext->getToken() || $admin->isGranted(sprintf($admin->getSecurityHandler()->getBaseRole($admin), 'ADMIN'))) {
            return;
        }

        // Retrieve current logged user SecurityIdentity
        $user = $this->securityContext->getToken()->getUser();
        $securityIdentity = UserSecurityIdentity::fromAccount($user);

        // Get identity ACL identifier
        $identifier = sprintf('%s-%s', $securityIdentity->getClass(), $securityIdentity->getUsername());

        $identityStmt = $this->databaseConnection->prepare('SELECT id FROM acl_security_identities WHERE identifier = :identifier');
        $identityStmt->bindValue('identifier', $identifier);
        $identityStmt->execute();

        $identityId = $identityStmt->fetchColumn();

        // Get class ACL identifier
        $classType = $admin->getClass();
        $classStmt = $this->databaseConnection->prepare('SELECT id FROM acl_classes WHERE class_type = :classType');
        $classStmt->bindValue('classType', $classType);
        $classStmt->execute();

        $classId = $classStmt->fetchColumn();

        if ($identityId && $classId) {
            $entriesStmt = $this->databaseConnection->prepare('SELECT object_identifier FROM acl_entries AS ae JOIN acl_object_identities AS aoi ON ae.object_identity_id = aoi.id WHERE ae.class_id = :classId AND ae.security_identity_id = :identityId AND (:view = ae.mask & :view OR :operator = ae.mask & :operator OR :master = ae.mask & :master OR :owner = ae.mask & :owner)');
            $entriesStmt->bindValue('classId', $classId);
            $entriesStmt->bindValue('identityId', $identityId);
            $entriesStmt->bindValue('view', MaskBuilder::MASK_VIEW);
            $entriesStmt->bindValue('operator', MaskBuilder::MASK_OPERATOR);
            $entriesStmt->bindValue('master', MaskBuilder::MASK_MASTER);
            $entriesStmt->bindValue('owner', MaskBuilder::MASK_OWNER);
            $entriesStmt->execute();

            $ids = array();
            foreach ($entriesStmt->fetchAll() as $row) {
                $ids[] = $row['object_identifier'];
            }

            if (count($ids)) {
                $query
                    ->andWhere('o.id IN (:ids)')
                    ->setParameter('ids', $ids)
                ;

                return;
            }
        }

        // Display an empty list
        $query->andWhere('1 = 2');
    }
}
