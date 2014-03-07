Classname conventions and customization
===

The class responsible for loading model services and constructing classnames is
[`EntityServiceAccessor`](https://github.com/VladaHejda/LazyDataMapper/blob/master/LazyDataMapper/EntityServiceAccessor.php).

In default, this class supposes following naming of model services. To change it, extend `EntityServiceAccessor`
and override touched methods:

- Entity is calculated from Facade classname: `Any\Namespace\ProductFacade` → `Any\Namespace\Product`.
To change this, override method `getEntityClass()`.

- Mapper is calculated from Entity classname: `Any\Namespace\Product` → `Any\Namespace\ProductMapper`.
To change classname calculating, override method `getMapperClass()`, to change the way of creating new Mapper
(e.g. to give dependencies, load service by your framework, etc.) override method `createMapper()`

- `ParamMap` and `Checker` are managed similarly:
`Any\Namespace\Product` → `Any\Namespace\ProductParamMap`,
`Any\Namespace\Product` → `Any\Namespace\ProductChecker`.

- EntityContainer is calculated from Entity classname by adding "s" (or "es" following "y" changed to "i") at the end.
`Any\Namespace\Product` → `Any\Namespace\Products` or `Any\Namespace\Story` → `Any\Namespace\Stories`.
But this is very simple pluralization, for much better results see
[PHP class for pluralizing english words](https://gist.github.com/VladaHejda/8775965) or implement your own method.

- If you have your own [`Identifier`](https://github.com/VladaHejda/LazyDataMapper/blob/master/DOC/Identifier.md),
you have to override method `composeIdentifier()`.

### Example

```php
class Product extends \LazyDataMapper\Entity
{
}

```

```php
namespace Product;

class Facade extends \LazyDataMapper\Facade
{
}

```

```php
namespace Product;

class ParamMap extends \LazyDataMapper\ParamMap
{
	protected $map = [
		'name', 'price', 'count',
	];
}

```

Mapper needs `PDO`:

```php
namespace Product;

class Mapper implements \LazyDataMapper\IMapper
{
	/** @var \PDO */
	private $pdo;

	public function __construct(\PDO $pdo)
	{
		$this->pdo = $pdo;
	}

	// ...
}

```

So we must override method `createMapper()` in `EntityServiceAccessor` to maintain dependency injection:

```php
class MyEntityServiceAccessor extends \LazyDataMapper\EntityServiceAccessor
{
	/** @var \PDO */
	protected $pdo;

	public function __construct(\PDO $pdo)
	{
		$this->pdo = $pdo;
	}

	public function getEntityClass(\LazyDataMapper\Facade $facade)
	{
		return substr(get_class($facade), 0, -7);
	}

	protected function getParamMapClass($entityClass)
	{
		return $entityClass . '\ParamMap';
	}

	protected function getMapperClass($entityClass)
	{
		return $entityClass . '\Mapper';
	}

	protected function getCheckerClass($entityClass)
	{
		return $entityClass . '\Checker';
	}

	protected function createMapper($mapper)
	{
		return new $mapper($this->pdo);
	}
}

```
