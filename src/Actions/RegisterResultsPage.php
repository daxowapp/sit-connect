<?php

namespace SIT\Search\Actions;

use SIT\Search\Services\Hook;

class RegisterResultsPage extends Hook
{
    public static array $hooks = ['template_redirect'];

    public static int $priority = 10;

    public function __invoke()
    {
        // Ensure shortcodes are processed on the results page
        if (is_page('results')) {
            add_filter('the_content', function($content) {
                if (is_page('results')) {
                    return do_shortcode($content);
                }
                return $content;
            }, 10);
        }
    }
}
