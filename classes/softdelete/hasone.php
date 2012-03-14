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

class HasOne extends \Orm\HasOne
{
	public function delete($model_from, $model_to, $parent_deleted, $cascade)
	{
		if( ! $parent_deleted)
		{
			return;
		}

		// Do a cascading delete on the related models
		$cascade = is_null($cascade) ? $this->cascade_delete : (bool) $cascade;
		if ($cascade and ! empty($model_to))
		{
			$model_to->delete();
		}
	}

	public function restore($model_from, $model_to, $parent_restored, $cascade)
	{
		if( ! $parent_restored )
		{
			return;
		}

		// Do a cascading restore on related models
		$cascade = is_null($cascade) ? $this->cascade_restore : (bool) $cascade;
		if ($cascade and ! empty($model_to))
		{
			$model_to->restore();
		}
	}
	
}