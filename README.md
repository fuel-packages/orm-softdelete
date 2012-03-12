# FuelPHP ORM Including Soft Delete
## !! Still in development  as of 2012/03/12- The extension isn't finished yet !!

If you work in finance, healthcare or other "critical" fields, sometimes you're expected to retain data even though the user "deletes" it. This fork supplies that ability by allowing the ORM to "softly" delete a row from the database, that is, mark a column in that row that specify's that row is now "deleted", without actually removing it. That's what this fork is all about. Check out the code below to see how to use the soft delete model and feature. Submit pull requests or issues if you find bugs!

### Quickstart

* Clone this repo into your `fuel\packages\` directory. (You'll want to delete the original orm folder in there) 
* Ensure the orm package is being loaded in your `APPPATH/config/config.php` file.
* Extend your models with `\Orm\Softdelete\Model` instead of `\Orm\Model`
* If you want to use unix timestamps (1331063441) (Default)
  * Add a field called `deleted_at INT DEFAULT 0` to any table in the database that you're using `Softdelete\Model` on.
* If you wish to use mysql timestamps (YYYY-MM-DD HH:MM:SS)
  * Add a field called `deleted_at TIMESTAMP DEFAULT None` to any table in the database that you're using `Softdelete\Model` on.



### Features
A few features of the soft delete model:

* You can still use everything in the normal ORM as usual. No interruptions there, drop this on any code already using the ORM.
* Completely respects `cascade_save` and `cascade_delete` on all relations if the relatied models are instances of `\Orm\Softdelete\Model`.

### Quick Use Example

```php
class Model_Patient extends \Orm\Softdelete\Model{}

$patient = Model_Patient::forge(
  array(
    'first_name' => 'John',
    'last_name' => 'Doe',
    'pin' => 1234567890,
    'family_doctor' => 'Dr. Seuss'
    'insurance_number' => 0987654321,
  )
);

$patient->save();

// The delete method has been over-ridden by the soft delete for this model
// This effectively sets $_soft_delete_property to either a mysql or unix timestamp in the row
$patient->delete();

```

### Example Model

```php

<?php
/**
 * Let's use a healthcare example
 */
class Model_Patient extends \Orm\Softdelete\Model
{

    /**
     * Set this field as the column you want to use to mark the row as deleted
     * Default : 'deleted_at'
     */
    protected static $_soft_delete_column = 'deleted_at'; // default
    
    /**
     * Choose whether to use a mysql timestamp (YYYY-MM-DD HH:MM:SS) or a unix timestamp (1331063441) 
     * @see http://en.wikipedia.org/wiki/Unix_time
     */
    protected static $_mysql_timestamp = false; // default
    
    // other
    protected static $_observers = array('Orm\\Observer_CreatedAt', 'Orm\\Observer_UpdatedAt');
        
    protected static $_properties = array(
      'first_name',
      'last_name',
      'pin',
      'family_doctor',
      'insurance_number',
    );
  
}

```
