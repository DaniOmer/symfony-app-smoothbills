Sure, here's the revised documentation using attributes instead of annotations:

## Using Gedmo Timestampable for Automatic Management of `created_at` and `updated_at`

The Gedmo Timestampable extension allows for automatic management of `created_at` and `updated_at` fields in Doctrine entities. This simplifies the process of tracking when entities are created and updated.

### Installation

First, ensure that the Gedmo Timestampable extension is installed in your Symfony project. You can install it using Composer:

```bash
composer require gedmo/doctrine-extensions
composer require stof/doctrine-extensions-bundle
```

### Add the extensions to your mapping

```yaml
# config/packages/doctrine.yaml
doctrine:
    orm:
        entity_managers:
            default:config/packages/stof_doctrine_extensions.yamlconfig/packages/stof_doctrine_extensions.yaml
                mappings:
                    gedmo_translatable:
                        type: annotation
                        prefix: Gedmo\Translatable\Entity
                        dir: "%kernel.project_dir%/vendor/gedmo/doctrine-extensions/src/Translatable/Entity"
                        alias: GedmoTranslatable
                        is_bundle: false

                    // Add more extensions...
```

### Enabling Timestampable

```yaml
# config/packages/stof_doctrine_extensions.yaml
stof_doctrine_extensions:
    default_locale: en_US
    orm:
        default:
            tree: true
            timestampable: true
```

### Importing Attributes

Import the Gedmo attributes at the top of your entity class:

```php
use Gedmo\Mapping\Annotation as Gedmo;
```

### Adding Timestampable Attributes

To enable automatic management of `created_at` and `updated_at` fields, add the `Timestampable` attribute to your entity properties:

```php
#[Gedmo\Timestampable(on: 'create')]
```

-   `on: 'create'`: This specifies that the `created_at` field should be automatically set when the entity is first created.

```php
#[Gedmo\Timestampable(on: 'update')]
```

-   `on: 'update'`: This specifies that the `updated_at` field should be automatically updated whenever the entity is modified and persisted.

### Example Usage

Here's an example entity class `MyEntity` demonstrating the usage of Gedmo Timestampable:

```php
use Gedmo\Mapping\Annotation as Gedmo;

class MyEntity
{
    // Other entity properties...

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private $createdAt;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(name: 'updated_at', type: 'datetime')]
    private $updatedAt;

    // Getter and setter methods for other properties...
}
```

### Notes

-   Ensure that the entity class is correctly configured with Doctrine mappings and attributes.
-   Gedmo Timestampable automatically manages the `created_at` and `updated_at` fields, reducing the need for manual updates in your application code.
