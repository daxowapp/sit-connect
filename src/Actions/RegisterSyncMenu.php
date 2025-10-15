<?php

namespace SIT\Search\Actions;

use SIT\Search\Services\Hook;

class RegisterSyncMenu extends Hook
{
    public static array $hooks = ['admin_menu'];

    public static int $priority = 10;

    public function __invoke()
    {
        add_submenu_page(
            'sit-connect',
            'Zoho Sync',
            'Zoho Sync',
            'manage_options',
            'sit-connect-sync',
            array($this, 'syncPage')
        );
    }

    public function syncPage()
    {
        // Load the sync template
        \SIT\Search\Services\Template::render('admin/sync');
    }
}
