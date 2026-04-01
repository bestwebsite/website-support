<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Minimal GitHub Releases updater for a public plugin repo.
 * - Checks latest release via GitHub API
 * - Prefers an uploaded asset named "website-support.zip"
 * - Falls back to release zipball_url if asset missing
 */
class BWS_GitHub_Updater {
	private $owner;
	private $repo;
	private $plugin_file;
	private $plugin_basename;

	public function __construct( $owner, $repo, $plugin_file ) {
		$this->owner = (string) $owner;
		$this->repo  = (string) $repo;
		$this->plugin_file = $plugin_file;
		$this->plugin_basename = plugin_basename( $plugin_file );

		add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'inject_update' ] );
		add_filter( 'plugins_api', [ $this, 'plugins_api' ], 20, 3 );
	}

	private function api_url() {
		return sprintf( 'https://api.github.com/repos/%s/%s/releases/latest', rawurlencode( $this->owner ), rawurlencode( $this->repo ) );
	}

	private function get_release() {
		$cache_key = 'bws_gh_release_' . md5( $this->owner . '/' . $this->repo );
		$cached = get_site_transient( $cache_key );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		$response = wp_remote_get( $this->api_url(), [
			'timeout' => 15,
			'headers' => [
				'Accept' => 'application/vnd.github+json',
				'User-Agent' => 'BestWebsiteSupport/' . ( defined( 'BWS_VERSION' ) ? BWS_VERSION : '0.0.0' ),
			],
		] );

		if ( is_wp_error( $response ) ) {
			return null;
		}
		$code = (int) wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );
		if ( 200 !== $code || empty( $body ) ) {
			return null;
		}
		$data = json_decode( $body, true );
		if ( ! is_array( $data ) || empty( $data['tag_name'] ) ) {
			return null;
		}

		// Cache for 10 minutes.
		set_site_transient( $cache_key, $data, 10 * MINUTE_IN_SECONDS );
		return $data;
	}

	private function normalize_version( $tag ) {
		$tag = (string) $tag;
		return ltrim( $tag, 'vV' );
	}

	private function find_download_url( $release ) {
		if ( ! is_array( $release ) ) {
			return null;
		}
		// Prefer a named asset.
		if ( ! empty( $release['assets'] ) && is_array( $release['assets'] ) ) {
			foreach ( $release['assets'] as $asset ) {
				$name = isset( $asset['name'] ) ? (string) $asset['name'] : '';
				if ( 'website-support.zip' === $name && ! empty( $asset['browser_download_url'] ) ) {
					return (string) $asset['browser_download_url'];
				}
			}
		}
		// Fallback.
		if ( ! empty( $release['zipball_url'] ) ) {
			return (string) $release['zipball_url'];
		}
		return null;
	}

	public function inject_update( $transient ) {
		if ( ! is_object( $transient ) ) {
			$transient = new stdClass();
		}
		if ( empty( $transient->checked ) || ! is_array( $transient->checked ) ) {
			return $transient;
		}

		$current = isset( $transient->checked[ $this->plugin_basename ] ) ? (string) $transient->checked[ $this->plugin_basename ] : ( defined( 'BWS_VERSION' ) ? BWS_VERSION : '' );
		$release = $this->get_release();
		if ( ! $release ) {
			return $transient;
		}

		$latest = $this->normalize_version( $release['tag_name'] );
		if ( '' === $latest || version_compare( $latest, $current, '<=' ) ) {
			return $transient;
		}

		$download = $this->find_download_url( $release );
		if ( ! $download ) {
			return $transient;
		}

		$package = [
			'slug'        => dirname( $this->plugin_basename ),
			'plugin'      => $this->plugin_basename,
			'new_version' => $latest,
			'url'         => ! empty( $release['html_url'] ) ? (string) $release['html_url'] : '',
			'package'     => $download,
		];

		$transient->response[ $this->plugin_basename ] = (object) $package;
		return $transient;
	}

	public function plugins_api( $result, $action, $args ) {
		if ( 'plugin_information' !== $action || empty( $args->slug ) ) {
			return $result;
		}
		if ( dirname( $this->plugin_basename ) !== $args->slug ) {
			return $result;
		}

		$release = $this->get_release();
		if ( ! $release ) {
			return $result;
		}

		$latest = $this->normalize_version( $release['tag_name'] );
		$info = new stdClass();
		$info->name = 'Best Website Support';
		$info->slug = dirname( $this->plugin_basename );
		$info->version = $latest;
		$info->author = 'Best Website';
		$info->homepage = 'https://bestwebsite.com';
		$info->sections = [
			'description' => 'Best Website client admin cleanup, branding, login customization, and built-in support tools for managed WordPress sites.',
			'changelog'   => '',
		];
		return $info;
	}
}
