<?php

/*
Name:    Dev4Press\v41\Services\GEOIP\Locator
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

abstract class Locator {
	protected $_multi_ip_call = false;
	protected $_url = '';

	protected $_expire = 14;
	protected $_user_agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:70.0) Gecko/20100101 Firefox/70.0';

	public $ips = '';

	/** @var Location[] */
	protected $_data = array();

	public function __construct( $ips ) {
		$this->ips = (array) $ips;
	}

	public function expire( $expire ) {
		$this->_expire = $expire;

		return $this;
	}

	public function ua( $user_agent ) {
		$this->_user_agent = $user_agent;

		return $this;
	}

	public function run() {
		$_not_found = array();

		foreach ( $this->ips as $ip ) {
			if ( IP::is_private( $ip ) ) {
				$this->_data[ $ip ] = new Location( array( 'status' => 'private', 'ip' => $ip ) );
			} else {
				$key = $this->_key( $ip );

				if ( $this->_expire > 0 ) {
					$cache = get_site_transient( $key );

					if ( is_null( $cache ) || empty( $cache ) ) {
						$_not_found[] = $ip;
					} else {
						$data               = json_decode( $cache );
						$this->_data[ $ip ] = new Location( $data );
					}
				} else {
					$_not_found[] = $ip;
				}
			}
		}

		if ( ! empty( $_not_found ) ) {
			if ( count( $_not_found ) > 1 && ! $this->_multi_ip_call ) {
				foreach ( $_not_found as $ip ) {
					$url = $this->url( $ip );

					$this->_remote( $url );
				}
			} else {
				if ( count( $_not_found ) == 1 ) {
					$url = $this->url( $_not_found[0] );
				} else {
					$url = $this->url( $_not_found );
				}

				$this->_remote( $url );
			}

			foreach ( $_not_found as $ip ) {
				if ( ! isset( $this->_data[ $ip ] ) ) {
					$this->_data[ $ip ] = false;
				}
			}
		}
	}

	protected function _key( $ip ) {
		return 'd4p_geoip_' . $ip;
	}

	protected function _remote( $url ) {
		$_remote_args = array(
			'httpversion' => '1.1',
			'user-agent'  => $this->_user_agent
		);

		$raw = wp_remote_get( $url, $_remote_args );

		if ( ! is_wp_error( $raw ) && $raw['response']['code'] == '200' ) {
			$raw = (array) json_decode( $raw['body'] );

			if ( ! is_object( $raw ) && ! is_array( $raw ) ) {

			} else {
				if ( is_object( $raw ) ) {
					$raw = array( $raw );
				}

				foreach ( $raw as $item ) {
					$data = $this->process( $item );

					if ( $this->_expire > 0 ) {
						$key = $this->_key( $data->ip );

						set_site_transient( $key, json_encode( $data ), $this->_expire * DAY_IN_SECONDS );
					}

					$this->_data[ $data->ip ] = $data;
				}
			}
		}
	}

	abstract protected function url( $ips );

	/** @return Location */
	abstract protected function process( $raw );
}
