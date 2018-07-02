<?php
/**
 * Created by PhpStorm.
 * User: Artem
 * Date: 02.07.2018
 * Time: 16:18
 */

namespace TeamSpeakBackaup\lib;


class FileManager {

	public static function remove( $rmPath ) {
		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $rmPath ),
			\RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ( $iterator as $path ) {
			if ( $path->isDir() ) {
				rmdir( (string) $path );
			} else {
				unlink( (string) $path );
			}
		}
		rmdir( $rmPath );
	}

	public static function create( $path ) {
		mkdir( $path );
	}
}