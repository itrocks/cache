<?php
namespace ITRocks\Cache;

use Exception;
use ITRocks\Cache;
use php_user_filter;

class Include_Filter extends php_user_filter
{

	//-------------------------------------------------------------------------------------------- ID
	const ID = 'itrocks/autoload-cache';

	//--------------------------------------------------------------------------------------- $active
	public static bool $active = true;

	//--------------------------------------------------------------------------- $cache_subdirectory
	protected static string $cache_subdirectory;

	//------------------------------------------------------------------------------------ $file_name
	protected static ?string $file_name = null;

	//------------------------------------------------------------------------ $home_directory_length
	protected static int $home_directory_length;

	//------------------------------------------------------------------------------------------ file
	public function file(string $file_name) : string
	{
		if (!str_starts_with($file_name, static::$home_directory_length)) {
			return $file_name;
		}
		$cache_file_name = substr($file_name, 0, static::$home_directory_length)
			. '/' . static::$cache_subdirectory
			. '/' . str_replace(['/', '\\'], '-', substr($file_name, static::$home_directory_length + 1));
		if (file_exists($cache_file_name)) {
			static::$file_name = $cache_file_name;
			return 'php://filter/read=' . self::ID . '/resource=' . $file_name;
		}
		return $file_name;
	}

	//---------------------------------------------------------------------------------------- filter
	/**
	 * @param $in  resource
	 * @param $out resource
	 */
	public function filter(mixed $in, mixed $out, &$consumed, bool $closing) : int
	{
		while ($bucket = stream_bucket_make_writeable($in)) {
			$consumed = $bucket->datalen;
			if (!isset(static::$file_name)) {
				continue;
			}
			$bucket->data    = file_get_contents(static::$file_name);
			$bucket->datalen = strlen($bucket->data);
			stream_bucket_append($out, $bucket);
			static::$file_name = null;
		}
		return PSFS_PASS_ON;
	}

	//-------------------------------------------------------------------------------------- register
	/** @throws Exception */
	public static function register(Cache $cache) : bool
	{
		static::$cache_subdirectory    = $cache->cache;
		static::$home_directory_length = strlen($cache->home);
		return stream_filter_register(self::ID, static::class)
			or throw new Exception('Failed to register include filter');
	}

}
