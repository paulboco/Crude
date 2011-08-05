<?php

namespace Crude;

/**
 * Stencil class
 */
class Stencil {

	/**
	 * Get stencil data.
	 *
	 * @return  mixed   returns the array or string on success or false on failure
	 */
	public static function get($stencil_name, $key = null)
	{
		$stencil_path = STENCILSPATH.$stencil_name.DS.'config.php';

		if ( ! File::file_exists($stencil_path))
		{
			return false;
		}

		if ( ! $stencil_data = \Fuel::load($stencil_path))
		{
			return false;
		}

		// return value if key found
		if (isset($key))
		{
			if (isset($stencil_data[$key]))
			{
				return $stencil_data[$key];
			}
			else
			{
				return false;
			}
		}

		// build inflections replacement array
		$tbl_singular = Table::get('crud.TBL_SINGULAR');
		$tbl_plural   = Table::get('crud.TBL_PLURAL');

		if ($tbl_prefix = Table::get('crud.TBL_PREFIX'))
		{
			$pfx_path = str_replace('_', DS, $tbl_prefix);
			$tbl_singular = str_replace($tbl_prefix, $pfx_path, $tbl_singular);
			$tbl_plural = str_replace($tbl_prefix, $pfx_path, $tbl_plural);
		}

		$inflections = array(
			':SINGULAR' => $tbl_singular,
			':PLURAL'   => $tbl_plural,
		);

		// make the replacements
		foreach ($stencil_data['files'] as $key => $value)
		{
			$stencil_data['files'][$key]['output_path'] = str_replace(array_keys($inflections), array_values($inflections), $value['output_path']);
		}

		return $stencil_data;
	}

	public static function get_options()
	{
		$options = array();

		$stencil_dirs = File::read_dir(STENCILSPATH, 1);

		foreach ($stencil_dirs as $dir => $null)
		{
			if ($data = self::get($dir))
			{
				$options[$dir] = $data['name'];
			}
		}

		return $options;
	}

	public static function file_header($file_path)
	{
		// get all table data
		$data = Table::get_all_data();

		$date = date("F j, Y, g:i a");

		return  <<<HEADER
/**
 * Generated by Crude CRUD generator for Fuel on
 * {$date}
 *
 * @file      {$file_path}
 * @database  {$data['DB_NAME']}
 * @type      {$data['DB_TYPE']}
 * @table     {$data['TBL_NAME']}
 * @stencil   {$data['STENCIL_NAME']}
 */

HEADER;
	}

}