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

class HasMany extends \Orm\HasMany
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