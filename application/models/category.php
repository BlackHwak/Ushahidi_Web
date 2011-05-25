<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Model for Categories of reported Incidents
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team <team@ushahidi.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @module     Category Model  
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */

class Category_Model extends ORM_Tree
{	
	/**
	 * One-to-many relationship definition
	 * @var array
	 */
	protected $has_many = array('incident' => 'incident_category', 'category_lang');
	
	/**
	 * Database table name
	 * @var string
	 */
	protected $table_name = 'category';
	
	/**
	 * Name of the child table for this model - recursive
	 * @var string
	 */ 
	protected $children = "category";
	
	/**
	 * Validates and optionally saves a category record from an array
	 *
	 * @param array $array Values to check
	 * @param bool $save Saves the record when validation succeeds
	 * @return bool
	 */
	public function validate(array & $array, $save = FALSE)
	{
		// Set up validation
		$array = Validation::factory($array)
					->pre_filter('trim', TRUE)
					->add_rules('parent_id','required', 'numeric')
					->add_rules('category_title','required', 'length[3,80]')
					->add_rules('category_description','required')
					->add_rules('category_color','required', 'length[6,6]');
		
		
		// Validation checks where parent_id > 0
		if ($array->parent_id > 0)
		{
			if ( ! empty($this->id) AND ($this->id == $array->parent_id))
			{
				// Error
				Kohana::log('error', 'The parent id and category id are the same!');
				$array->add_error('parent_id', 'same');
			}
			else
			{
				// Ensure parent_id exists in the DB
				$array->add_callbacks('parent_id', 'Category_Model::is_valid_category');
			}
		}
		
		// Pass on validation to parent and return
		return parent::validate($array, $save);
	}
	
	/**
	 * Gets the list of categories from the database as an array
	 *
	 * @param int $category_id Database id of the category
	 * @param string $local Localization to use
	 * @return mixed
	 */
	public static function categories($category_id = NULL, $locale='en_US')
	{
		$categories = (empty($category_id) OR ! self::is_valid_category($category_id))
			? ORM::factory('category')->where('locale', $locale)->find_all()
			: ORM::factory('category')->where('id', $category_id)->find_all();
		
		// To hold the return values
		$cats = array();
		
		foreach($categories as $category)
		{
			$cats[$category->id]['category_id'] = $category->id;
			$cats[$category->id]['category_title'] = $category->category_title;
			$cats[$category->id]['category_color'] = $category->category_color;
			$cats[$category->id]['category_image'] = $category->category_image;
			$cats[$category->id]['category_image_thumb'] = $category->category_image_thumb;
		}
		
		return $cats;
	}

	/**
	 * Checks if the specified category ID is of type INT and exists in the database
	 *
	 * @param	int	$category_id 
	 * @return	bool
	 */
	public static function is_valid_category($category_id)
	{
		return (preg_match('/^[1-9](\d*)$/', $category_id) > 0)
				? self::factory('category', $category_id)->loaded
				: FALSE;
	}
	
}
