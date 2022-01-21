<?php

class PluginTest extends TestCase
{
    public function test_plugin_installed() {
        activate_plugin( 'disciple-tools-prayer-requests/disciple-tools-prayer-requests.php' );

        $this->assertContains(
            'disciple-tools-prayer-requests/disciple-tools-prayer-requests.php',
            get_option( 'active_plugins' )
        );
    }
}
