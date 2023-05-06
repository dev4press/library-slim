<?php

/*
Name:    Dev4Press\v41\Services\GEOIP\GEOPlugin
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

namespace Dev4Press\v41\Service\GEOIP;

use Dev4Press\v41\Core\Helpers\IP;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GEOPlugin extends Locator {
	protected $_url = 'http://www.geoplugin.net/json.gp?ip=';

	/** @return GEOPlugin */
	public static function instance( $ips = array() ) {
		static $geoplugin_ips = array();

		if ( empty( $ips ) ) {
			$ips = array( IP::visitor() );
		}

		sort( $ips );

		$key = join( '-', $ips );

		if ( ! isset( $geoplugin_ips[ $key ] ) ) {
			$geoplugin_ips[ $key ] = new GEOPlugin( $ips );
		}

		return $geoplugin_ips[ $key ];
	}

	protected function url( $ips ) {
		return $this->_url . $ips;
	}

	protected function process( $raw ) {
		$convert = array(
			'ip'            => 'ip',
			'countryCode'   => 'country_code',
			'countryName'   => 'country_name',
			'regionCode'    => 'region_code',
			'regionName'    => 'region_name',
			'city'          => 'city',
			'latitude'      => 'latitude',
			'longitude'     => 'longitude',
			'continentCode' => 'continent_code'
		);

		$code = array(
			'status' => 'active'
		);

		foreach ( $raw as $key => $value ) {
			$ck = substr( $key, 10 );

			if ( isset( $convert[ $ck ] ) ) {
				$real          = $convert[ $ck ];
				$code[ $real ] = $value;
			}
		}

		return new Location( $code );
	}
}
