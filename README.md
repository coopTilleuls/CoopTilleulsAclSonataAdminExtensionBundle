# ACL extension for Sonata Admin

### This bundle is a fork of [CoopTilleulsAclSonataAdminExtensionBundle](https://github.com/coopTilleuls/CoopTilleulsAclSonataAdminExtensionBundle)

This bundle provides ACL list filtering for [SonataAdminBundle](https://github.com/sonata-project/SonataAdminBundle).
When enabled, list screens only display data the logged in user has right to view.

This bundle is a good complementary of the SonataAdminBundle [ACL editor](http://sonata-project.org/bundles/admin/master/doc/reference/security.html#acl-editor).

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/d7d70442-b52c-4072-8e03-45e6a47e1ca2/mini.png)](https://insight.sensiolabs.com/projects/d7d70442-b52c-4072-8e03-45e6a47e1ca2)

## Install

Be sure that SonataAdminBundle is working and has [ACL enabled](http://sonata-project.org/bundles/admin/master/doc/reference/security.html#acl-and-friendsofsymfony-userbundle).

Install this bundle using composer:

```
composer require mrgreenstuff/acl-sonata-admin-extension-bundle
```

Register the bundle in your AppKernel:

```php
// app/AppKernel.php

public function registerBundles()
{
    return array(
        // ...
        new MrGreenStuff\Bundle\AclSonataAdminExtensionBundle\MrGreenStuffAclSonataAdminExtensionBundle(),
        // ...
    );
}
```

## Enable

This extension is automatically enabled for all admins.

##Special case (Master ACL Entity)

TODO

## Credits

Created by [Kévin Dunglas](http://dunglas.fr) for [La Coopérative des Tilleuls](http://les-tilleuls.coop).

Enhanced by JUILLARD Yoann
