<?php

/*
Name:    Dev4Press\v41\Services\GEOIP\GEOJSIO
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

class GEOJSIO extends Locator {
	protected $_multi_ip_call = true;
	protected $_url = 'https://get.geojs.io/v1/ip/geo.json?ip=';

	public static function instance( $ips = array() ) {
		static $geoplugin_ips = array();

		if ( empty( $ips ) ) {
			$ips = array( IP::visitor() );
		}

		sort( $ips );

		$key = join( '-', $ips );

		if ( ! isset( $geoplugin_ips[ $key ] ) ) {
			$geoplugin_ips[ $key ] = new GEOJSIO( $ips );
		}

		return $geoplugin_ips[ $key ];
	}

	protected function url( $ips ) {
		$ips = (array) $ips;

		return $this->_url . join( ',', $ips );
	}

	protected function process( $raw ) {
		$convert = array(
			'ip'             => 'ip',
			'continent_code' => 'continent_code',
			'country_code'   => 'country_code',
			'country'        => 'country_name',
			'region'         => 'region_name',
			'city'           => 'city',
			'latitude'       => 'latitude',
			'longitude'      => 'longitude',
			'timezone'       => 'time_zone'
		);

		$code = array(
			'status' => 'active'
		);

		foreach ( $raw as $key => $value ) {
			if ( isset( $convert[ $key ] ) ) {
				$real          = $convert[ $key ];
				$code[ $real ] = $value;
			}
		}

		return new Location( $code );
	}
}
