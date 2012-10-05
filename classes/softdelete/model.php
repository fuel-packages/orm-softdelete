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

class Model extends \Orm\Model
{

	protected static $_valid_relations = array(
		'belongs_to'    => 'Orm\Softdelete\BelongsTo',
		'has_one'       => 'Orm\Softdelete\HasOne',
		'has_many'      => 'Orm\Softdelete\HasMany',
		'many_many'     => 'Orm\Softdelete\ManyMany',
	);

	protected static $_soft_delete_property = 'deleted_at';
	protected static $_mysql_timestamp = false;

	protected $_override_delete = false;

	/**
	 * Check to see if this object is soft-deleted
	 * return bool
	 */
	public function is_soft_deleted(){
		// @TODO check for mysql vs timestamp
		return (bool) $this->{static::$_soft_delete_property} !== 0;
	}

	public function override_delete( $delete = true ){
		$this->_override_delete = $delete;
		return $this;
	}

	public static function find($id = null, array $options = array() )
	{
		// @TODO Add check for mysql date vs timestamp

		if( ! empty( $options['include_deleted'] ) )
		{
			return parent::find( $id, $options );
		}
		else
		{
			$options = array();
			$options['where'] = array( array( static::$_soft_delete_property, NULL ) );
			return parent::find( $id, $options );
		}		
	}

	/**
	 * Soft-delete this object
	 * return \Softdelete\Model
	 */
	public function delete( $cascade = null, $use_transaction = false ){

		// if the object is frozen, return
		if( $this->frozen() or $this->is_new() )
		{
			return $this;
		}

		if( $this->_override_delete === true )
		{
			parent::delete($cascade, $use_transaction);
			return;
		}

		// @TODO Not sure if this is the right way to do a transaction, check ORM package
		if($use_transaction)
		{
			$db = \Database_Connection::instance(static::connection());
			$db->start_transaction();
		}

		try
		{
			// Launch observer
			$this->observe('before_delete');
			$this->observe('before_softdelete');

			// Call delete on each related object, specifying "parent deleted" as false
			$this->freeze();
			foreach($this->relations() as $rel_name => $rel)
			{
				$rel->delete($this, $this->{$rel_name}, false, is_array($cascade) ? in_array($rel_name, $cascade) : $cascade);
			}
			$this->unfreeze();

			// Set the soft-deleted property to a mysql time or timestmap
			$this->{static::$_soft_delete_property} = static::$_mysql_timestamp ? \Date::forge()->format('mysql') : \Date::forge()->get_timestamp();
			$this->save();

			$this->freeze();
			// Call delete on each related object, specifying "parent deleted" as true
			foreach($this->relations() as $rel_name => $rel)
			{
				$rel->delete($this, $this->{$rel_name}, true, is_array($cascade) ? in_array($rel_name, $cascade) : $cascade);
			}
			$this->unfreeze();

			// Remove this object from the runtime cache
			if (array_key_exists(get_called_class(), static::$_cached_objects)
				and array_key_exists(static::implode_pk($this), static::$_cached_objects[get_called_class()]))
			{
				unset(static::$_cached_objects[get_called_class()][static::implode_pk($this)]);
			}

			// Call the observers
			$this->observe('after_delete');
			$this->observe('after_softdelete');

			// If transactions are being used, commit this one
			$use_transaction and $db->commit_transaction();
		}
		catch( \Exception $e )
		{
			$use_transaction and $db->rollback_transaction();
			throw $e;
		}

		return $this;
	}

	/**
	 * Restore this object from being deleted
	 * return \Softdelete\Model
	 */
	public function restore($cascade = null, $use_transaction = false)
	{
		// If the object is frozen, return
		if( $this->frozen() )
		{
			return $this;
		}

		try
		{
			$this->freeze();
			foreach($this->relations() as $rel_name => $rel)
			{
				$rel->restore($this, $this->{$rel_name}, false, is_array($cascade) ? in_array($rel_name, $cascade) : $cascade);
			}
			$this->unfreeze();

			if( $this->is_soft_deleted() )
			{
				$this->observe('before_restore');
				// @TODO this might need to be null
				$this->{static::$_soft_delete_property} = NULL;
				$this->save();
			}

			$this->freeze();
			foreach($this->relations() as $rel_name => $rel)
			{
				$rel->restore($this, $this->{$rel_name}, true, is_array($cascade) ? in_array($rel_name, $cascade) : $cascade);
			}
			$this->unfreeze();

			$this->observe('after_restore');
		}
		catch( \Exception $e )
		{

			throw $e;
		}

		return $this;
	}

	/**
	 * Alias for restore()
	 * return \Softdelete\Model
	 */
	public function undelete()
	{
		return $this->restore();
	}

}