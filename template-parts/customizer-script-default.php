<?php
/**
 * Default inline customizer script template part
 *
 * @package theme
 * @subpackage codename
 * @version 2.0
 */

namespace theme;


?>

<script type='text/javascript'>
jQuery(document).ready(function($) {
  wp.customize.selectiveRefresh.bind('partial-content-rendered', function(placement) {
    var selected = wp.customize('theme_settings[bst_selected]')();
    var stylesheet = $('#bootswatch-theme-css').detach();

    if (selected && bootswatch_themes && selected in bootswatch_themes) {
      stylesheet.attr('href', bootswatch_themes[selected].css).appendTo('head');
    }
  });
});
</script>
