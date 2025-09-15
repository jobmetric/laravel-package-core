[Back To README.md](https://github.com/jobmetric/laravel-package-core/blob/master/README.md)

# Introduction to Has Dynamic Relations Trait

`HasDynamicRelations` allows you to define polymorphic relations dynamically and flexibly. This `Trait` helps you manage different relationships between models without directly changing the package code and enables users to define the required relationships in their applications.

## Usage

This is a simple example

Consider the Tag class, which has a taggable field, and now consider the following examples

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;use JobMetric\PackageCore\Models\HasDynamicRelations;

class Tag extends Model
{
    use HasDynamicRelations;
}
```

### Define Relationships in Other Models

To define dynamic relationships in other models, you need to set up the desired relationships dynamically when the models boot.

#### Example: `Post` Model

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tag;

class Post extends Model
{
    protected static function boot()
    {
        parent::boot();

        Tag::addDynamicRelation('posts', function ($model) {
            return $model ? $model->morphedByMany(Post::class, 'taggable') : (new Post)->morphedByMany(Tag::class, 'taggable');
        });
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
```

#### Example: `Product` Model

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tag;

class Product extends Model
{
    protected static function boot()
    {
        parent::boot();

        Tag::addDynamicRelation('products', function ($model) {
            return $model ? $model->morphedByMany(Product::class, 'taggable') : (new Product)->morphedByMany(Tag::class, 'taggable');
        });
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
```

### Usage in Controllers and Other Places

Now you can use these dynamic relationships in your application:

```php
// Getting all tags with posts
$tags = Tag::with('posts')->get();

// Getting all tags with products
$tags = Tag::with('products')->get();

// Adding a tag to a post
$post = Post::find(1);
$tag = Tag::find(1);
$post->tags()->attach($tag);

// Adding a tag to a product
$product = Product::find(1);
$product->tags()->attach($tag);

// Getting posts related to a tag
$posts = $tag->posts;

// Getting products related to a tag
$products = $tag->products;
```

### Conclusion

Using `HasDynamicRelations` and the methods described above, you can define polymorphic relationships between models dynamically. This approach ensures that your package remains flexible and usable across different projects without requiring frequent changes to the package code.

- [Resource Resolve Event](https://github.com/jobmetric/laravel-package-core/blob/master/docs/resource-resolve-event.md)
