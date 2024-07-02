## Using TimestampableTrait for Automatic Management of `created_at` and `updated_at`

The `TimestampableTrait` provides a reusable solution for automatic management of `created_at` and `updated_at` fields in Doctrine entities. This simplifies the process of tracking when entities are created and updated.

### Installation

Ensure that your Symfony project has the necessary dependencies installed to use the `TimestampableTrait`. If not already installed, you can add it to your project via Composer:

```bash
composer require vendor/package
```

### Enabling TimestampableTrait

To enable automatic management of timestamps in your entities, utilize the `TimestampableTrait`:

```php
use App\Trait\TimestampableTrait;
```

### Using TimestampableTrait in Entities

To use `TimestampableTrait`, include it in your entity class:

```php
class MyEntity
{
    use TimestampableTrait;

    // Other entity properties and methods...
}
```

The `TimestampableTrait` will automatically manage the `created_at` and `updated_at` fields for instances of `MyEntity`.

### Example Usage

Here's an example entity class `MyEntity` demonstrating the usage of `TimestampableTrait`:

```php
use App\Trait\TimestampableTrait;

class MyEntity
{
    use TimestampableTrait;

    // Other entity properties and methods...
}
```

### Notes

-   Ensure that the entity class is correctly configured with the necessary dependencies and attributes.
-   `TimestampableTrait` automates the management of `created_at` and `updated_at` fields, reducing the need for manual updates in your application code.
-   To learn about how this was implemented, see the [documentation](/docs/timestampable_doc.md).
