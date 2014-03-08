Entity
===

### Wrappers

As mentioned sooner, Entity works even if it is empty. So that means that only parameters from `ParamMap` can be obtained:

```php
class Product extends \LazyDataMapper\Entity
{
}

class ProductParamMap extends \LazyDataMapper\ParamMap
{
	protected $map = [
		'name', 'price', 'count',
	];
}

$product = $productFacade->getById(3);

// works:
echo $product->name;
echo $product->price;
echo $product->count;
// but:
echo $product->somethingElse; // will throw Exception
```

But in Entity you can create so-called **wrappers**, which can make any kind of result from base parameters:

```php
class Product extends \LazyDataMapper\Entity
{
	protected function getSumPrice()
	{
		return $this->price * $this->count;
	}
}

$product = $productFacade->getById(3);

echo $product->sumPrice; // when price is 15 and count is 3, results in 45
```

You can *wrap* even existing parameter (you will get the base value in first argument):

```php
class Product extends \LazyDataMapper\Entity
{
	protected function getPrice($price)
	{
		return "$price €";
	}
}

$product = $productFacade->getById(3);

echo $product->price; // results in something like "120 €"
```

You can even accept arguments in wrappers:

```php
class Product extends \LazyDataMapper\Entity
{
	protected function getPrice($price, $currency = 'USD')
	{
		switch ($currency) {
			case 'USD':
				return $price;
			case 'EUR':
				return $price * $this->getCourse('EUR');
		}
	}

	private function getCourse($currency)
	{
		// ...
	}
}

$product = $productFacade->getById(3);

echo $product->price; // result e.g. 12
echo $product->price('EUR'); // result e.g. 8.4
```

If you need base value of wrapped parameter use `getClear()`:

```php
class Product extends \LazyDataMapper\Entity
{
	protected function getPrice($price)
	{
		return "$price €";
	}

	protected function getVAT()
	{
		$price = $this->getClear('price'); // returns price without "€"
		return $price * 0.2;
	}
}
```

### Private parameters

If there is a parameter which you need in wrapper, but you don't want to provide it in Entity,
set it as private:

```php
class ProductParamMap extends \LazyDataMapper\ParamMap
{
	protected $map = [
		'name', 'price', 'spread'
	];
}

class Product extends \LazyDataMapper\Entity
{
	protected $privateParams = ['spread'];

	protected function getPrice($price)
	{
		return $price + $this->getClear('spread');
	}
}

$product = $productFacade->getById(3);

echo $product->price; // when price is 120 and spread is 30, returns 150
echo $product->spread; // throws Exception
```

### Entity hierarchy

Wrapper can return another entity:

```php
namespace Shop;

class Seller extends \LazyDataMapper\Entity
{
}

class Product extends \LazyDataMapper\Entity
{
	protected function getSeller()
	{
		return $this->getDescendant('Shop\Seller', 'seller_id');
	}
}

$product = $productFacade->getById(3);

echo $product->seller->name; // returns for example "John Doe"
```

Or EntityContainer:

```php
class Product extends \LazyDataMapper\Entity
{
	protected function getSimilarProducts()
	{
		$similarProducts = $this->getClear('similar'); // returns something like "5|6|11|18|24"
		$similarProducts = explode('|', $similarProducts);
		return $this->getDescendant(self::SELF, $similarProducts);
	}
}

$product = $productFacade->getById(3);

foreach ($product->similarProducts as $similarProduct) {
	echo $similarProduct->name;
}
```

If id of descendant Entity is on some complicated place, use third argument:

```php
class Product extends \LazyDataMapper\Entity
{
	protected function getLastCustomer()
	{
		$customers = $this->getClear('customers'); // returns something like "8|14|27"
		preg_match('/|([0-9]+)$/', $customers, $m);
		$lastId = $m[1];
		return $this->getDescendant('Shop\Seller', 'last_seller', $lastId);
	}
}
```

*Actually, in this case the second argument in `getDescendant()` can be whatever (not "last_seller"), it is just
key for internal purposes. But notice that in previous cases it must be as it is written.*
