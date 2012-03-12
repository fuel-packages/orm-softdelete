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

class ManyMany extends \Orm\ManyMany
{
	public function delete($model_from, $models_to, $parent_deleted, $cascade)
	{
		if ( ! $parent_deleted)
		{
			return;
		}

		// Delete all relationship entries for the model_from
		$query = \DB::delete($this->table_through);
		reset($this->key_from);
		foreach ($this->key_through_from as $key)
		{
			$query->where($key, '=', $model_from->{current($this->key_from)});
			next($this->key_from);
		}
		$query->execute(call_user_func(array($model_from, 'connection')));

		$cascade = is_null($cascade) ? $this->cascade_delete : (bool) $cascade;
		if ($cascade and ! empty($model_to))
		{
			foreach ($models_to as $m)
			{
				$m->delete();
			}
		}
	}
}