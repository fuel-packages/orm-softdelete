<?php

namespace Orm\Softdelete;

class HasOne extends \Orm\HasOne
{
	public function delete($model_from, $models_to, $parent_deleted, $cascade)
	{
		if( ! $parent_deleted)
		{
			return;
		}

		// Do a cascading delete on the related models
		$cascade = is_null($cascade) ? $this->cascade_delete : (bool) $cascade;
		if ($cascade and ! empty($models_to))
		{
			foreach ($models_to as $m)
			{
				$m->delete();
			}
		}
	}
}