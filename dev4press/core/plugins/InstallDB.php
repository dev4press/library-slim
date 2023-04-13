<?php

/*
Name:    Dev4Press\v41\Core\Plugins\InstallDB
Version: v4.1
Author:  Milan Petrovic
Email:   support@dev4press.com
Website: https://www.dev4press.com/

== Copyright ==
Copyright 2008 - 2023 Milan Petrovic (email: support@dev4press.com)

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>
*/

namespace Dev4Press\v41\Core\Plugins;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class InstallDB {
	protected $prefix = '';
	protected $tables = array();

	public function __construct() {
	}

	/** @return static */
	public static function instance() {
		static $instance = array();

		if ( ! isset( $instance[ static::class ] ) ) {
			$instance[ static::class ] = new static();
		}

		return $instance[ static::class ];
	}

	public function install() {
		$query   = '';
		$collate = $this->collate();

		foreach ( $this->tables as $obj ) {
			$table = $this->table( $obj );

			$query .= "CREATE TABLE " . $table . " (" . $obj['data'] . ") " . $collate . ";" . D4P_EOL;
		}

		return $this->delta( $query );
	}

	public function check() {
		$result = array();

		foreach ( $this->tables as $obj ) {
			$table = $this->table( $obj );
			$count = $obj['columns'];

			if ( $this->wpdb()->get_var( "SHOW TABLES LIKE '$table'" ) == $table ) {
				$columns = $this->wpdb()->get_results( "SHOW COLUMNS FROM $table" );

				if ( $count != count( $columns ) ) {
					$result[ $table ] = array(
						"status" => "error",
						"msg"    => __( "Some columns are missing.", "d4plib" )
					);
				} else {
					$result[ $table ] = array( "status" => "ok" );
				}
			} else {
				$result[ $table ] = array( "status" => "error", "msg" => __( "Table is missing.", "d4plib" ) );
			}
		}

		return $result;
	}

	public function truncate() {
		foreach ( $this->tables as $obj ) {
			$this->wpdb()->query( "TRUNCATE TABLE " . $this->table( $obj ) );
		}
	}

	public function drop() {
		foreach ( $this->tables as $obj ) {
			$this->wpdb()->query( "DROP TABLE IF EXISTS " . $this->table( $obj ) );
		}
	}

	private function delta( $query ) {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		return dbDelta( $query );
	}

	protected function table( $obj ) {
		return $this->prefix_wpdb( $obj['scope'] ) . $this->prefix_plugin() . $obj['name'];
	}

	protected function prefix_plugin() {
		return empty( $this->prefix ) ? '' : $this->prefix . '_';
	}

	protected function prefix_wpdb( $scope = 'blog' ) {
		return $scope == 'network' ? $this->wpdb()->base_prefix : $this->wpdb()->prefix;
	}

	protected function collate() {
		$charset_collate = '';

		if ( ! empty( $this->wpdb()->charset ) ) {
			$charset_collate = "default CHARACTER SET " . $this->wpdb()->charset;
		}

		if ( ! empty( $this->wpdb()->collate ) ) {
			$charset_collate .= " COLLATE " . $this->wpdb()->collate;
		}

		return $charset_collate;
	}

	/** @return \wpdb */
	protected function wpdb() {
		global $wpdb;

		return $wpdb;
	}
}
