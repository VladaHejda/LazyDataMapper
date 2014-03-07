EntityContainer
===

Often you will need to get not one, but sum of Entities. And you don't want to get one after the other.

This is the occasion for [`EntityContainer`](https://github.com/VladaHejda/LazyDataMapper/blob/master/LazyDataMapper/EntityContainer.php)!

### add one to your existing model:

```php
class Products extends \LazyDataMapper\EntityContainer
{
}
```

*If the classname is not the plural version of Entity classname, see how to solve it at
[Classname conventions and customization](https://github.com/VladaHejda/LazyDataMapper/blob/master/DOC/Classname-conventions-and-customization.md).*

### then you need to gain data range in Mapper:

```php
use LazyDataMapper\ISuggestor,
	LazyDataMapper\DataHolder;

class Mapper implements \LazyDataMapper\IMapper
{
	// ...

	public function getByIdsRange(array $ids, ISuggestor $suggestor)
	{
		$params = $suggestor->getParamNames();
		$columns = '`' . implode('`,`', $params) . '`';
		$in = implode(',', array_fill(0, count($ids), '?'));
		$statement = $this->pdo->prepare("SELECT id, $columns FROM product WHERE id IN ($in)");
		$statement->execute($ids);
		$holder = new DataHolder($suggestor, $ids);
		$params = array_flip($params);
		while ($row = $statement->fetch()) {
			$data = array_intersect_key($row, $params);
			$holder->setParams([$row['id'] => $data]);
		}
		return $holder;
	}

	// ...
}
```

### done, you can create one

```php
$products = $this->productFacade->getByIdsRange([2, 5, 6]);

foreach ($products as $i => $product) {
	echo ($i+1) . '.';
	echo $product->name;
	echo ' ';
	echo $product->count . 'x';
	echo "\n";
}
```

will result to something similar to:

```
1. Calculator 5x
2. Notebook 1x
3. TV 17x
```
