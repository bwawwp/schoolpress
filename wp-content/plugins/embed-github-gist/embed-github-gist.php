<?php
/*
Plugin Name: Embed GitHub Gist
Plugin URI: http://wordpress.org/extend/plugins/embed-github-gist/
Description: Embed GitHub Gists
Author: Dragonfly Development
Author URI: http://dflydev.com/
Version: 0.13
License: MIT - http://opensource.org/licenses/mit
*/

// TODO Option for default ttl
// TODO Option to bypass cache
// TODO Option to select css loading location preference
// TODO Shortcode attribute to control inline vs. js preference
// TODO Implement admin interface to control options

/**
 * Default ttl for cache.
 * @var int
 */
if ( !defined('EMBED_GISTHUB_DEFAULT_TTL') ) {
	define('EMBED_GISTHUB_DEFAULT_TTL', 86400);	// 60*60*24 (1 day)
}

if ( !defined('EMBED_GISTHUB_INLINE_HTML') ) {
	define('EMBED_GISTHUB_INLINE_HTML', false);
}

if ( !defined('EMBED_GISTHUB_BYPASS_CACHE') ) {
	define('EMBED_GISTHUB_BYPASS_CACHE', false);
}

/**
 * Build a cache key
 * @param int $id GitHub Gist ID
 * @param string $bump Bump value to force cache expirey.
 */
function embed_github_gist_build_cache_key($id, $bump = null, $file=null) {
    $key = 'embed_github_gist-' . $id;
    if ( $bump ) $key .= '-' . $bump;
    if ( $file ) $key .= '-' . $file;
    return $key;
}

/**
 * Bypass cache?
 */
function embed_github_gist_bypass_cache() {
    return EMBED_GISTHUB_BYPASS_CACHE;
}

/**
 * Prefer inline HTML over JS?
 */
function embed_github_gist_prefer_inline_html() {
    return EMBED_GISTHUB_INLINE_HTML;
}

/**
 * Gets content from GitHub Gist
 * 
 * @param int $id GitHub Gist ID
 * @param int $ttl How long to cache (in seconds)
 * @param string $bump Bump value to force cache expirey.
 * @param string $file Name of file
 */
function embed_github_gist($id, $ttl = null, $bump = null, $file = null) {
	$gist = '';

	if ( !class_exists('WP_Http') ) {
		require_once ABSPATH.WPINC.'/class-http.php';
	}

    $key = embed_github_gist_build_cache_key($id, $bump, $file);
    if ( embed_github_gist_bypass_cache() || false === ( $gist = get_transient($key) ) ) {
    	$http = new WP_Http;
        $args = array('sslverify' => false);
        if (defined('EMBED_GISTHUB_USERNAME') && defined('EMBED_GISTHUB_PASSWORD')) {
            $args['headers'] = array( 'Authorization' => 'Basic '.base64_encode(EMBED_GISTHUB_USERNAME.':'.EMBED_GISTHUB_PASSWORD) );
        }
        $result = $http->request('https://api.github.com/gists/' . $id, $args);
        if ( is_wp_error($result) ) {
            echo $result->get_error_message();
        }
        $json = json_decode($result['body'], true);
        if (200 != $result['response']['code']) {
            $html = '<div>Could not embed GitHub Gist '.$id;
            if (isset($json['message'])) {
                $html .= ': '. $json['message'];
            }
            $html .= '</div>';

            return $html;
        };

        $files = array();
        foreach ($json['files'] as $name => $fileInfo) {
            if ($file === null) {
                $files[$name] = $fileInfo;
            } else {
                if ($file == $name) {
                    $files[$name] = $fileInfo;
                    break;
                }
            }
        }

        $gist = '';

        if (count($files)) {
            if ( embed_github_gist_prefer_inline_html() ) {
                foreach ($files as $name => $fileInfo) {
                    $language = strtolower($fileInfo['language']);
                    $gist .= '<pre><code class="language-'.$language.' '.$language.'">';
                    $gist .= htmlentities($fileInfo['content']);
                    $gist .= '</code></pre>';
                }
            } else {
                $urlExtra = $file ? '?file='.$file : '';
                $gist .= '<script src="https://gist.github.com/'.$id.'.js'.$urlExtra.'"></script>';
                $gist .= '<noscript>';
                foreach ($files as $name => $fileInfo) {
                    $language = strtolower($fileInfo['language']);
                    $gist .= '<pre><code class="language-'.$language.' '.$language.'">';
                    $gist .= htmlentities($fileInfo['content']);
                    $gist .= '</code></pre>';
                }
                $gist .= '</noscript>';
            }
        }
        
        unset($result, $http);
        
        if ( ! embed_github_gist_bypass_cache() ) {
            if ( ! $ttl ) $ttl = EMBED_GISTHUB_DEFAULT_TTL;
            set_transient($key, $gist, $ttl);
        }
    }

    return $gist;
}

/**
 * Shortcode handler
 * @param array $atts Attributes
 * @param mixed $content
 */
function handle_embed_github_gist_shortcode($atts, $content = null) {
    extract(shortcode_atts(array(
        'id' => null,
        'file' => null,
        'ttl' => null,
        'bump' => null,
    ), $atts));

    if ( ! $id ) {
        if ( $content ) {
            if ( preg_match('/\s*https?.+\/(\d+)/', $content, $matches) ) {
                $id = $matches[1];
            }
        }
    }
    return $id ? embed_github_gist($id, $ttl, $bump, $file) : $content;
}

/**
 * Init the plugin.
 */
function handle_embed_github_gist_init() {
    add_shortcode('gist', 'handle_embed_github_gist_shortcode');
}

/**
 * Detects if available posts contains a gist shortcode inside
 * 
 * @author oncletom
 */
function embed_github_gist_post_candidate()
{
	global $posts;
	
	foreach ($posts as $p) {
		if (preg_match('/\[gist[^\]]*\]/siU', $p->post_content)) {
			return true;
		}
	}

	return false;
}

add_action('init', 'handle_embed_github_gist_init');

if ( !is_admin()) {
	add_action('wp', 'embed_github_gist_post_candidate');
}
