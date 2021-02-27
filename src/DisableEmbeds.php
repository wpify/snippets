<?php

namespace Wpify\Tools;

use Wpify\Core\Abstracts\AbstractComponent;

class DisableEmbeds extends AbstractComponent
{
  public function setup()
  {
    remove_action('rest_api_init', 'wp_oembed_register_route');
    remove_action('wp_head', 'wp_oembed_add_discovery_links');
    remove_action('wp_head', 'wp_oembed_add_host_js');

    remove_filter('oembed_dataparse', 'wp_filter_oembed_result', 10);
    remove_filter('pre_oembed_result', 'wp_filter_pre_oembed_result', 10);

    add_action('wp_footer', [$this, 'deregister_scripts']);

    add_filter('embed_oembed_discover', '__return_false');
    add_filter('tiny_mce_plugins', [$this, 'tiny_mce_plugin']);
    add_filter('rewrite_rules_array', [$this, 'rewrites']);
  }

  public function tiny_mce_plugin($plugins)
  {
    return array_diff($plugins, array('wpembed'));
  }

  public function rewrites($rules)
  {
    foreach ($rules as $rule => $rewrite) {
      if (false !== strpos($rewrite, 'embed=true')) {
        unset($rules[$rule]);
      }
    }

    return $rules;
  }

  public function deregister_scripts()
  {
    wp_dequeue_script('wp-embed');
  }
}
