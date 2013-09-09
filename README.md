# ACL extension for Sonata Admin

This bundle provides ACL list filtering for [SonataAdminBundle](https://github.com/sonata-project/SonataAdminBundle).
When enabled, list screens only display data the logged in user has right to view.

This bundle is a good complementary of the SonataAdminBundle [ACL editor](http://sonata-project.org/bundles/admin/master/doc/reference/security.html#acl-editor).

## Install

Be sure that SonataAdminBundle is working and has [ACL enabled](http://sonata-project.org/bundles/admin/master/doc/reference/security.html#acl-and-friendsofsymfony-userbundle).

Install this bundle using composer:

```
composer require tilleuls/acl-sonata-admin-extension-bundle
```

Register the bundle in your AppKernel:

```php
// app/AppKernel.php

public function registerBundles()
{
    return array(
        // ...
        new CoopTilleuls\Bundle\AclSonataAdminExtensionBundle\CoopTilleulsAclSonataAdminExtensionBundle(),
        // ...
    );
}
```

## Enable

This extension is automatically enabled for all admins.

## TODO

* Test with other DBMSs than MySQL
* Write tests

## Credits

Created by [Kévin Dunglas](http://dunglas.fr) for [La Coopérative des Tilleuls](http://les-tilleuls.coop).
