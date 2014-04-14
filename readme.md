LazyDataMapper
===

**LazyDataMapper** is [DataMapper](http://en.wikipedia.org/wiki/Data_mapper_pattern) tool for **PHP**.
It is tool for making application [data models](http://en.wikipedia.org/wiki/Data_model).

LazyDataMapper aims to following targets:

- completely cover the Entity from the data source structure
- the most simplest Entity control API as is possible
- allow to call efficient data [CRUD](http://en.wikipedia.org/wiki/Create,_read,_update_and_delete) queries (SQL or another)
at one place

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
- [Aggregating data queries](DOC/8.Aggregating-data-queries.md)
