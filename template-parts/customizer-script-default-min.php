<?php
/**
 * Default inline customizer script minified template part
 *
 * @package theme
 * @subpackage codename
 * @version 2.0
 */

namespace theme;


?>

<script type='text/javascript'>
jQuery(document).ready(function(t){wp.customize.selectiveRefresh.bind("partial-content-rendered",function(e){var o=wp.customize("theme_settings[bst_selected]")(),s=t("#bootswatch-theme-css").detach();o&&bootswatch_themes&&o in bootswatch_themes&&s.attr("href",bootswatch_themes[o].css).appendTo("head")})});
</script>
