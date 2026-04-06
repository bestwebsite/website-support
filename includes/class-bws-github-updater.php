<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GitHub Releases updater for the public plugin repository.
 */
class BWS_GitHub_Updater {
	private $owner;
	private $repo;
	private $plugin_file;
	private $plugin_basename;
	private $release_cache_key;
	private $failure_cache_key;

	public function __construct( $owner, $repo, $plugin_file ) {
		$this->owner            = (string) $owner;
		$this->repo             = (string) $repo;
		$this->plugin_file      = $plugin_file;
		$this->plugin_basename  = plugin_basename( $plugin_file );
		$this->release_cache_key = 'bws_gh_release_' . md5( $this->owner . '/' . $this->repo );
		$this->failure_cache_key = 'bws_gh_release_fail_' . md5( $this->owner . '/' . $this->repo );

		add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'inject_update' ] );
		add_filter( 'plugins_api', [ $this, 'plugins_api' ], 20, 3 );
	}

	private function api_url() {
		return sprintf( 'https://api.github.com/repos/%s/%s/releases/latest', rawurlencode( $this->owner ), rawurlencode( $this->repo ) );
	}

	private function get_release() {
		$cached = get_site_transient( $this->release_cache_key );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		if ( get_site_transient( $this->failure_cache_key ) ) {
			return null;
		}

		$response = wp_remote_get(
			$this->api_url(),
			[
				'timeout' => 15,
				'headers' => [
					'Accept'     => 'application/vnd.github+json',
					'User-Agent' => 'BestWebsiteSupport/' . ( defined( 'BWS_VERSION' ) ? BWS_VERSION : '1.0.7' ),
				],
			]
		);

		if ( is_wp_error( $response ) ) {
			set_site_transient( $this->failure_cache_key, 1, 15 * MINUTE_IN_SECONDS );
			return null;
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );
		if ( 200 !== $code || empty( $body ) ) {
			set_site_transient( $this->failure_cache_key, 1, 15 * MINUTE_IN_SECONDS );
			return null;
		}

		$data = json_decode( $body, true );
		if ( ! is_array( $data ) || empty( $data['tag_name'] ) ) {
			set_site_transient( $this->failure_cache_key, 1, 15 * MINUTE_IN_SECONDS );
			return null;
		}

		delete_site_transient( $this->failure_cache_key );
		set_site_transient( $this->release_cache_key, $data, 30 * MINUTE_IN_SECONDS );
		return $data;
	}

	private function normalize_version( $tag ) {
		return ltrim( (string) $tag, 'vV' );
	}

	private function find_download_url( $release ) {
		if ( ! is_array( $release ) ) {
			return null;
		}

		if ( ! empty( $release['assets'] ) && is_array( $release['assets'] ) ) {
			foreach ( $release['assets'] as $asset ) {
				$name = isset( $asset['name'] ) ? (string) $asset['name'] : '';
				$url  = isset( $asset['browser_download_url'] ) ? (string) $asset['browser_download_url'] : '';

				if ( 'website-support.zip' === $name && '' !== $url ) {
					return $url;
				}
			}
		}

		return ! empty( $release['zipball_url'] ) ? (string) $release['zipball_url'] : null;
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

		$transient->response[ $this->plugin_basename ] = (object) [
			'slug'        => dirname( $this->plugin_basename ),
			'plugin'      => $this->plugin_basename,
			'new_version' => $latest,
			'tested'      => get_bloginfo( 'version' ),
			'url'         => ! empty( $release['html_url'] ) ? (string) $release['html_url'] : 'https://github.com/' . $this->owner . '/' . $this->repo,
			'package'     => $download,
		];

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

		$latest       = $this->normalize_version( $release['tag_name'] );
		$download     = $this->find_download_url( $release );
		$published_at = ! empty( $release['published_at'] ) ? gmdate( 'Y-m-d', strtotime( $release['published_at'] ) ) : '';
		$changelog    = '';
		$local_file   = BWS_PLUGIN_DIR . 'CHANGELOG.md';
		if ( file_exists( $local_file ) ) {
			$changelog = wp_kses_post( wpautop( esc_html( file_get_contents( $local_file ) ) ) );
		}

		$info              = new stdClass();
		$info->name        = 'Best Website Support';
		$info->slug        = dirname( $this->plugin_basename );
		$info->version     = $latest;
		$info->author      = 'Best Website';
		$info->author_profile = 'https://bestwebsite.com';
		$info->homepage    = 'https://bestwebsite.com';
		$info->download_link = $download;
		$info->last_updated  = $published_at;
		$info->sections = [
			'description' => 'Best Website client admin cleanup, branding, login customization, performance hardening, and built-in support tools for managed WordPress sites.',
			'changelog'   => $changelog,
		];
		$info->banners = [
			'low' => BWS_PLUGIN_URL . 'assets/social/website-support-banner.svg',
		];

		return $info;
	}
}
