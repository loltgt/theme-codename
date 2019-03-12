<?php
/**
 * Default inline customizer script minified template part
 *
 * @package theme
 * @subpackage codename
 * @version 1.0
 */

namespace theme;


?>

<script type='text/javascript'>
jQuery(document).ready(function(t){wp.customize("theme_settings[brand_name]",function(e){e.bind(function(e){t(".custom-logo").length&&t(".custom-logo").text(e)})}),wp.customize.selectiveRefresh.bind("partial-content-rendered",function(e){var o=wp.customize("theme_settings[bst_selected]")(),s=t("#bootswatch-theme-css").detach();o&&bootswatch_themes&&o in bootswatch_themes&&s.attr("href",bootswatch_themes[o].css).appendTo("head")})});
</script>
