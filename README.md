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

## Special case (Master ACL Entity)
#### Enhancement By JUILLARD YOANN

### Application example :

#### 3 Tables : Shop, Product and Country
- Between this tables relation ManyToOne (1 Country have N Shop) (1 Shop have N products). It should be work on all relation types but it's not tested.

#### 4 Users :

- Admin (SUPER_ADMIN)
- MainManager (NOT SUPER ADMIN !)
- EnglandManager
- FranceManager

#### Behavior expected :

- MainManager have OPERATOR ACL on all Countries so he can access to all shop and products of the matching country (even if ACL record for him not exists but because they have ACL access to the parent or the grand parent in this case all countries)
- EnglandManager or FranceManager can acces to all shop and products of the matching coutry (even if the products or shop has been created by MainManager or the SUPER_ADMIN without ACLs for this users but because they have ACL acces to the parent or the grand parent in this case only one country)
- Admin keep SUPER_ADMIN role (normal behavior)
    
### Configuration :
- Create method : getMasterACLclass() on your sonata admin classes (only classes where you want to enabled the behavior). This methos must return a string off master entity ACL like :
    
```php
//In Shop and Product admin classes
public function getMasterACLclass(){
    return 'Acme\DemoBundle\Entity\Country';
}
```
    
- Create method getPathToMasterACL() on your sonata admin classes (only classes where you want to enabled the behavior). This methos must return a array like :
    
```php
//In Shop admin class
public function getPathToMasterACL(){
    return  array(
                array('coutry','c')
                );
}
//Where 'country' is the property name of the Shop entity who made the relation with Country Entity and 'c' a unique identifier (IMPORTANT the unique shortcut identifier CANNOT BE 'o' because 'o' is the default identifier of Sonata Admin)
    
//In Product admin class
public function getPathToMasterACL(){
    return  array(
                 array('shop','s'),
                 array('coutry','c')
                );
}
//BE CAREFULL WHITH ORDER IN ARRAY IT MUST BE parent->grandParent->grandGrandParent... untill the MASTER ACL CLASS DEFINED ABOVE
```


## Credits

Created by [Kévin Dunglas](http://dunglas.fr) for [La Coopérative des Tilleuls](http://les-tilleuls.coop).

Enhanced by JUILLARD Yoann
