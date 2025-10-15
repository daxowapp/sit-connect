<?php

namespace SIT\Search\Actions;

/**
 * Inject Custom Colors into Frontend
 * Applies user-defined colors from settings
 */
class InjectCustomColors
{
    public static $hooks = ['wp_head'];
    public static $priority = 999;
    public static $arguments = 0;

    public function __invoke()
    {
        $primary_color = get_option('sit_connect_primary_color', '#AA151B');
        $primary_dark_color = get_option('sit_connect_primary_dark_color', '#8B1116');
        $secondary_color = get_option('sit_connect_secondary_color', '#F1BF00');
        $accent_color = get_option('sit_connect_accent_color', '#C29900');

        // Calculate lighter shade for primary color
        $primary_light_color = $this->adjustBrightness($primary_color, 30);

        ?>
        <style id="sit-connect-custom-colors">
            :root {
                /* Apply Primary Colors */
                --apply-primary: <?php echo esc_attr($primary_color); ?> !important;
                --apply-primary-dark: <?php echo esc_attr($primary_dark_color); ?> !important;
                --apply-secondary: <?php echo esc_attr($secondary_color); ?> !important;
                --apply-accent: <?php echo esc_attr($accent_color); ?> !important;
                
                /* University Primary Colors */
                --uni-primary: <?php echo esc_attr($primary_color); ?> !important;
                --uni-primary-light: <?php echo esc_attr($primary_light_color); ?> !important;
                --uni-primary-dark: <?php echo esc_attr($primary_dark_color); ?> !important;
                --uni-secondary: <?php echo esc_attr($secondary_color); ?> !important;
                --uni-accent: <?php echo esc_attr($accent_color); ?> !important;
                --uni-gradient: linear-gradient(135deg, <?php echo esc_attr($primary_color); ?> 0%, <?php echo esc_attr($primary_dark_color); ?> 100%) !important;
                
                /* Program Page Colors */
                --programPage-primary: <?php echo esc_attr($primary_color); ?> !important;
                --programPage-primary-dark: <?php echo esc_attr($primary_dark_color); ?> !important;
                --programPage-secondary: <?php echo esc_attr($secondary_color); ?> !important;
                --programPage-accent: <?php echo esc_attr($accent_color); ?> !important;
                --programPage-gradient: linear-gradient(135deg, <?php echo esc_attr($primary_color); ?> 0%, <?php echo esc_attr($primary_dark_color); ?> 100%) !important;
                
                /* Program Archive Page Colors */
                --ProgramArchivePage-primary: <?php echo esc_attr($primary_color); ?> !important;
                --ProgramArchivePage-primary-dark: <?php echo esc_attr($primary_dark_color); ?> !important;
                --ProgramArchivePage-primary-light: <?php echo esc_attr($primary_light_color); ?> !important;
                --ProgramArchivePage-secondary: <?php echo esc_attr($secondary_color); ?> !important;
                --ProgramArchivePage-accent: <?php echo esc_attr($accent_color); ?> !important;
            }
        </style>
        <?php
    }

    /**
     * Adjust brightness of a hex color
     * 
     * @param string $hex Hex color code
     * @param int $steps Steps to adjust (-255 to 255)
     * @return string Adjusted hex color
     */
    private function adjustBrightness($hex, $steps)
    {
        // Remove # if present
        $hex = str_replace('#', '', $hex);

        // Convert to RGB
        if (strlen($hex) == 3) {
            $hex = str_repeat(substr($hex, 0, 1), 2) . str_repeat(substr($hex, 1, 1), 2) . str_repeat(substr($hex, 2, 1), 2);
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        // Adjust
        $r = max(0, min(255, $r + $steps));
        $g = max(0, min(255, $g + $steps));
        $b = max(0, min(255, $b + $steps));

        // Convert back to hex
        return '#' . str_pad(dechex($r), 2, '0', STR_PAD_LEFT) . str_pad(dechex($g), 2, '0', STR_PAD_LEFT) . str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
    }
}
