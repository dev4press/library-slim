<?php

/*
Name:    Dev4Press\v40\Core\Quick\Misc
Version: v4.0
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

namespace Dev4Press\v40\Core\Quick;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Misc {
	public static function get_regex_error( $error_code ) : string {
		if ( is_bool( $error_code ) ) {
			return 'OK';
		}

		$errors = array_flip( get_defined_constants( true )['pcre'] );

		if ( isset( $errors[ $error_code ] ) ) {
			return $errors[ $error_code ];
		}

		return 'UNKNOWN_ERROR';
	}

	public static function php_ini_size_value( $name ) {
		$ini = ini_get( $name );

		if ( $ini === false ) {
			return 0;
		}

		$ini  = trim( $ini );
		$last = strtoupper( $ini[ strlen( $ini ) - 1 ] );
		$ini  = absint( substr( $ini, 0, strlen( $ini ) - 1 ) );

		switch ( $last ) {
			case 'G':
				$ini = $ini * GB_IN_BYTES;
				break;
			case 'M':
				$ini = $ini * MB_IN_BYTES;
				break;
			case 'K':
				$ini = $ini * KB_IN_BYTES;
				break;
		}

		return $ini;
	}
}