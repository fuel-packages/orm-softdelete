<?php 
/**
 * FuelPHP ORM Softdelete Extension Package
 * 
 * Used to allow the FuelPHP ORM package to "soft" delete rows
 * by tagging a property in the database with a timestamp value 
 * to signify when the row was considered "deleted".
 * 
 * @author 			@JesseOBrien & @spencerdeinum on github
 * @copyright 	2012 Jesse O'Brien & Spencer Deinum
 * @license 		MIT License
 * @link 				https://github.com/JesseObrien/orm
 * 
 */

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