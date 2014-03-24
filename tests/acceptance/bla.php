<?php

class AddToSuggestor
{
	public function getWrapped($group = NULL, $alias = NULL) {}
}

use LazyDataMapper\Suggestor,
	LazyDataMapper\DataHolder;

class ProductMapper implements LazyDataMapper\IMapper
{

	public function getById($id, Suggestor $s, DataHolder $h = NULL)
	{
		$q = "SELECT p.product_id, s.sale_id, customer_id, "
				. $s->getWrapped(NULL, 'p')
				. ($s->sales ? ", " . $s->sales->getWrapped(NULL, 's') : ' ')
				. ($s->sales->customer ? ", " . $s->sales->customer->getWrapped() : ' ')
			. "FROM product p "
				. ($s->sales ? "LEFT JOIN sale s USING product_id " : ' ')
				. ($s->sales->customer ? "LEFT JOIN customer USING customer_id " : ' ')
			. "WHERE product_id = ?"
		;

		// possible result query:
		$q = "
			SELECT s.sale_id, customer_id,
				p.name, p.price,
				, s.date
				, ico, returns
			FROM product p
				LEFT JOIN sale s USING product_id
				LEFT JOIN customer USING customer_id
			WHERE product_id = ?
		";

		$result = $this->pdo->prepare($q)->execute([$id]);

		// possible result data:
		$data = [
			[ 'name' => 'TV', 'price' => 199,
			  'sale_id' =>     1, 'date' => '2014-02-15',
			  'customer_id' => 3, 'ico' => '12345', 'returns' => 2, ],

			[ 'name' => 'TV', 'price' => 199,
			  'sale_id' =>     2, 'date' => '2014-02-24',
			  'customer_id' => 7, 'ico' => '678910', 'returns' => 1, ],

			[ 'name' => 'TV', 'price' => 199,
			  'sale_id' =>     3, 'date' => '2014-03-06',
			  'customer_id' => 3, 'ico' => '12345', 'returns' => 2, ],

			[ 'name' => 'TV', 'price' => 199,
			  'sale_id' =>     4, 'date' => '2014-07-22',
			  'customer_id' => 8, 'ico' => '99995', 'returns' => 1, ],
		];

		$product = $sales = $customers = [];
		while ($row = $result->fetch()) {

			if (!isset($product)) {
				$product = [
					'name' => $row['name'], 'price' => $row['price']
				];
			}



			/****** 1.    kdybych chtěl zachovat hierarchii: ******/
			if (!isset($sales[$row['product_id']])) {
				$sales[$row['product_id']] = [];
			}
			$sales  [$row['product_id']]  [$row['sale_id']]     =  ['date' => $row['date']];

			/****** 2.   anebo vrátim flat array kde se sice vyvaruju duplicit,
					ale budou se muset zjišťovat (znovu) idéčka: ******/

			$sales[$row['sale_id']] = [
				'date' => $row['date']
			];
			/************************************************/

			/*
			3.   nebo použít první způsob a duplicitní záznamy agregovat (zkrátka tam zůstanou jen jednou)
				 jenže to je příliš sožitý
			*/


			$customers[$row['customer_id']] = [
				'ico' => $row['ico'], 'returns' => $row['returns']
			];
		}

		// metoda $h->setIds() je možná nadbytečná - lze to vzít z setParams()
		// při hierarchicky uspořádaných parametrech by nešlo idéčka nastavit jako array_keys()

		return $h->setParams($product)
			->sales->setIds(array_keys($sales))->setParams($sales)
			->customers->setIds(array_keys($customers))->setParams($customers);
	}
}
