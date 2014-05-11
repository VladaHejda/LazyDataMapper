[INSTALLATION](DOC/1.Installation.md) ● [DOCUMENTATION](#documentation)

------------------------------------------------------------------------------------------------------------------------

LazyDataMapper
==============

**LazyDataMapper** is [DataMapper](http://en.wikipedia.org/wiki/Data_mapper_pattern) tool for **PHP**.
It is tool for making application [data models](http://en.wikipedia.org/wiki/Data_model).

LazyDataMapper does not build or execute SQL queries or anything similar. You are the one who do this.
He just gives you the suggestions to make **efficient universal** data queries by yourself.

LazyDataMapper aims to following targets:

- completely cover the Entity from the data source structure
- the most simplest Entity control API as is possible
- allow to call efficient data [CRUD](http://en.wikipedia.org/wiki/Create,_read,_update_and_delete) queries (SQL or another)
at one place

#### Example

After you integrate LazyDataMapper into your app, you'll access data just like following. You won't care about
SQLs anymore.

```php
$seller = $sellerFacade->getById(666);

?>
<p>Name: <?php echo $seller->name; ?></p>
<p>Profit: <?php echo $seller->profit('EUR'); ?> €</p>

<ul>
<?php foreach ($seller->sales as $sale) { ?>
	<li>
		<?php
		echo $sale->date->format('d.m.Y');
		foreach ($sale->products as $product) {
			echo " $product->name";
		}
		?>
	</li>
<?php } ?>
</ul>
```

#### Recommendations

*LazyDataMapper gets entities according to integer ID. In databases, it should be the unique integer
[primary key](http://en.wikipedia.org/wiki/Unique_key).
So it is strongly recommended that every Entity based table have it. In another data storages, it should be
similar.*

## Documentation

- [Installation](DOC/1.Installation.md)
- [Classname conventions and customization](DOC/2.Classname-conventions-and-customization.md)
- [Entity reading](DOC/3.Entity-reading.md)
- [Entity modifying](DOC/4.Entity-modifying.md)
- [Entity creation and removal](DOC/5.Entity-creation-and-removal.md)
- [EntityCollection](DOC/6.EntityCollection.md)
- [Restrictor](DOC/7.Restrictor.md)
- [Behind the curtain](DOC/8.Behind-the-curtain.md)
- [Aggregating data queries](DOC/9.Aggregating-data-queries.md)
