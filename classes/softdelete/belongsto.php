<?php 

namespace Orm\Softdelete;

class BelongsTo extends \Orm\BelongsTo
{
	public function delete($model_from, $model_to, $parent_deleted, $cascade)
	{
		if ($parent_deleted)
		{
			return;
		}

		// Delete the parent model
		$cascade = is_null($cascade) ? $this->cascade_delete : (bool) $cascade;
		if ($cascade and ! empty($model_to))
		{
			$model_to->delete();
		}
	}
}