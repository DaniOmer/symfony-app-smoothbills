# How to Add UUID to Entities Using `UuidTypeTrait`

## Step 1: Add the Trait to Your Entity

I create a UuidTypeTrait to easily add the UUID field to our entities.
To do so, simply use the `UuidTypeTrait` in the entity class.

### Example

Here's how you can integrate the `UuidTypeTrait` into an entity named `User`:

```php
namespace App\Entity;

use App\Trait\UuidTypeTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class User
{
    use UuidTypeTrait { __construct as private UuidConstruct; }

    public function __construct()
    {
        $this->UuidConstruct();
        // Additional constructor logic here
    }

}
```

### Key Points

1. **Use the Trait:**

    ```php
    use UuidTypeTrait { __construct as private UuidConstruct; }
    ```

2. **Call the Trait Constructor:**
   In the entity's constructor, call the trait's constructor:
    ```php
    public function __construct()
    {
        $this->UuidConstruct();
        // Additional constructor logic here
    }
    ```

That's it! Your entity now has a UUID field that is automatically generated and accessible via the provided methods.
