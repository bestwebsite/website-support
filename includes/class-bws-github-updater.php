<?php
if ( ! defined( 'ABSPATH' ) ) {
			exit;
}

class BWS_GitHub_Updater {
	private $owner;
	private $repo;
	private $plugin_basename;
	private $plugin_file;
	private $cache_key = 'bws_github_release_v1';
	private $cache_ttl = 6 * HOUR_IN_SECONDS;

	public function __construct( $owner, $repo, $plugin_file ) {
		$this->owner = (string) $owner;
		$this->repo  = (string) $repo;
		$this->plugin_file = (string) $plugin_file;
		$this->plugin_basename = plugin_basename( $plugin_file );

		add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'inject_update' ] );
		add_filter( 'plugins_api', [ $this, 'plugins_api' ], 20, 3 );
		add_filter( 'upgrader_post_install', [ $this, 'post_install' ], 10, 3 );
	}

	private function get_release() {
		$cached = get_transient( $this->cache_key );
		if ( is_array( $cached ) && ! empty( $cached['tag'] ) ) {
			return $cached;
		}

		$url = sprintf( 'https://api.github.com/repos/%s/%s/releases/latest', rawurlencode( $this->owner ), rawurlencode( $this->repo ) );

		$response = wp_remote_get(
			$url,
			[
				'timeout' => 12,
				'headers' => [
					'Accept'     => 'application/vnd.github+json',
					'User-Agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . home_url(),
				],
			]
		);

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );
		if ( $code < 200 || $code >= 300 || empty( $body ) ) {
			return null;
		}

		$data = json_decode( $body, true );
		if ( ! is_array( $data ) ) {
			return null;
		}

		$tag = isset( $data['tag_name'] ) ? (string) $data['tag_name'] : '';
		$tag = ltrim( $tag, 'vV' );

		$zip_url = '';
		// Prefer a release asset if present (recommended), otherwise fall back to GitHub zipball.
		if ( ! empty( $data['assets'] ) && is_array( $data['assets'] ) ) {
			foreach ( $data['assets'] as $asset ) {
				if ( ! is_array( $asset ) ) {
					continue;
				}
				$name = isset( $asset['name'] ) ? (string) $asset['name'] : '';
				$dl   = isset( $asset['browser_download_url'] ) ? (string) $asset['browser_download_url'] : '';
				if ( $dl && preg_match( '/\.zip$/i', $name ) ) {
					$zip_url = $dl;
					break;
				}
			}
		}

		if ( ! $zip_url && ! empty( $data['zipball_url'] ) ) {
			$zip_url = (string) $data['zipball_url'];
		}

		$release = [
			'tag'         => $tag,
			'zip_url'     => $zip_url,
			'html_url'    => isset( $data['html_url'] ) ? (string) $data['html_url'] : '',
			'published'   => isset( $data['published_at'] ) ? (string) $data['published_at'] : '',
			'body'        => isset( $data['body'] ) ? (string) $data['body'] : '',
			'name'        => isset( $data['name'] ) ? (string) $data['name'] : '',
		];

		set_transient( $this->cache_key, $release, $this->cache_ttl );

		return $release;
	}

	public function inject_update( $transient ) {
		if ( ! is_object( $transient ) ) {
			$transient = new stdClass();
		}

		if ( empty( $transient->checked ) || ! is_array( $transient->checked ) ) {
			return $transient;
		}

		if ( ! isset( $transient->checked[ $this->plugin_basename ] ) ) {
			return $transient;
		}

		$release = $this->get_release();
		if ( ! $release || empty( $release['tag'] ) || empty( $release['zip_url'] ) ) {
			return $transient;
		}

		$current = $transient->checked[ $this->plugin_basename ];
		$remote  = $release['tag'];

		if ( version_compare( $remote, $current, '<=' ) ) {
			return $transient;
		}

		$update = (object) [
			'slug'        => dirname( $this->plugin_basename ),
			'plugin'      => $this->plugin_basename,
			'new_version' => $remote,
			'url'         => $release['html_url'] ? $release['html_url'] : sprintf( 'https://github.com/%s/%s', $this->owner, $this->repo ),
			'package'     => $release['zip_url'],
		];

		$transient->response[ $this->plugin_basename ] = $update;

		return $transient;
	}

	public function plugins_api( $result, $action, $args ) {
		if ( 'plugin_information' !== $action || empty( $args->slug ) ) {
			return $result;
		}

		$our_slug = dirname( $this->plugin_basename );
		if ( $args->slug !== $our_slug ) {
			return $result;
		}

		$release = $this->get_release();

		$info = (object) [
			'name'          => 'Best Website Support',
			'slug'          => $our_slug,
			'version'       => $release && ! empty( $release['tag'] ) ? $release['tag'] : BWS_VERSION,
			'author'        => '<a href="https://bestwebsite.com">Best Website</a>',
			'homepage'      => $release && ! empty( $release['html_url'] ) ? $release['html_url'] : sprintf( 'https://github.com/%s/%s', $this->owner, $this->repo ),
			'requires'      => '5.8',
			'tested'        => get_bloginfo( 'version' ),
			'requires_php'  => '7.4',
			'sections'      => [
				'description' => 'Client admin cleanup, branding, login customization, and built-in support tools for managed WordPress sites.',
				'changelog'   => $release && ! empty( $release['body'] ) ? wp_kses_post( $release['body'] ) : '',
			],
		];

		return $info;
	}

	public function post_install( $response, $hook_extra, $result ) {
		if ( empty( $hook_extra['plugin'] ) || $hook_extra['plugin'] !== $this->plugin_basename ) {
			return $response;
		}

		global $wp_filesystem;

		$proper_destination = trailingslashit( $result['local_destination'] ) . dirname( $this->plugin_basename );
		$source             = isset( $result['destination'] ) ? (string) $result['destination'] : '';

		if ( ! $source || ! $wp_filesystem || ! $wp_filesystem->is_dir( $source ) ) {
			return $response;
		}

		// If the destination already matches, nothing to do.
		if ( untrailingslashit( $source ) === untrailingslashit( $proper_destination ) ) {
			return $response;
		}

		// Move extracted folder into the expected plugin folder name.
		$wp_filesystem->move( $source, $proper_destination, true );

		$result['destination'] = $proper_destination;

		// Ensure plugin remains active after update.
		$activate = is_plugin_active( $this->plugin_basename );
		if ( $activate ) {
			activate_plugin( $this->plugin_basename );
		}

		return $response;
	}
}
