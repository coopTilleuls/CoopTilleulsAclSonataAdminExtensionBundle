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

When the PR [#1597](https://github.com/sonata-project/SonataAdminBundle/pull/1597) of SonataAdminBundle will be merged, the extension will be automatically enabled for all admins.

For now, you can manually enabled the extension in your `config.yml` file.

```yaml
sonata_admin:
        extensions:
            coop_tilleuls_acl_sonata_admin_extension.acl.extension:
                admins:
                    - acme.demo.admin.article # Replace this line with your own admin id
                    - acme.demo.admin.comment
```

## TODO

* Test with other DBMSs than MySQL
* Write tests

## Credits

Created by [Kévin Dunglas](http://dunglas.fr) for [La Coopérative des Tilleuls](http://les-tilleuls.coop).
