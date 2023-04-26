<?php
namespace ITRocks;

class Cache
{

	//---------------------------------------------------------------------------------------- $cache
	/** Cache subdirectory, relative to the $home directory */
	public string $cache;

	//----------------------------------------------------------------------------------------- $home
	/** Your project home directory */
	public string $home;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $home  string Your project home directory
	 * @param $cache string Cache subdirectory, relative to the $home directory
	 */
	public function __construct(string $home, string $cache)
	{
		$this->cache = $cache;
		$this->home  = $home;
	}

}
