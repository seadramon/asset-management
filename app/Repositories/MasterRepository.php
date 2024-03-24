<?php
namespace Asset\Repositories;

use Asset\Models\Master;
use Asset\Repositories\Interfaces\MasterRepositoryInterface;

/**
 * 
 */
class MasterRepository implements MasterRepositoryInterface
{
	public function getBagian()
	{
		return Master::bagian()->get();
	}

	public function selectBagian()
	{
		return $this->getBagian()->pluck('name', 'id')->toArray();
	}
}
