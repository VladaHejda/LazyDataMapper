<?php

namespace Shelter;

/**
 * @todo some analyzer (etc?) to check that requests are not redundant
 * (e.g. addresses like "product/detail/123?whatever" and ""product/detail/456" have to have equal key)
 */
interface IRequestKey
{

	/**
	 * @return string
	 */
	function getKey();
}
