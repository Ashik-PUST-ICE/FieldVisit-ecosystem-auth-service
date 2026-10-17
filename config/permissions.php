<?php

/*
|--------------------------------------------------------------------------
| Permission catalogue
|--------------------------------------------------------------------------
|
| Mirrors the real routes of the ecosystem services so that the role editor
| only offers actions that actually exist:
|
|   auth     -> auth-service      (/api/v1: settings/*, security-settings, ...)
|   business -> business-service  (/api/v1: locations, outlets, visits, ...)
|
| Shape: section => group => module => [action => label].
| The seeder turns this into the permission name "section.group.module.action"
| and stores "group-module" in the permissions.group column, which is what
| PermissionService::getList() splits to build the category/group tree for
| the Flutter role-permissions sheet. Group and module keys must therefore
| contain NO dashes (underscores only).
|
*/

return [
    'auth' => [
        'settings' => [
            'roles' => [
                'index' => 'List',
                'create' => 'Create',
                'update' => 'Update',
                'delete' => 'Delete',
                'show' => 'Show',
                'assign' => 'Assign Permissions',
                'status' => 'Toggle Status',
            ],
            'permissions' => [
                'index' => 'List',
                'update' => 'Update',
                'show' => 'Show',
            ],
            'companies' => [
                'index' => 'List',
                'create' => 'Create',
                'update' => 'Update',
                'delete' => 'Delete',
                'show' => 'Show',
            ],
            'users' => [
                'index' => 'List',
                'create' => 'Create',
                'update' => 'Update',
                'delete' => 'Delete',
                'show' => 'Show',
                'status' => 'Toggle Status',
            ],
        ],
        'account' => [
            'security_settings' => [
                'show' => 'View',
                'change_pin' => 'Change PIN',
                'update_mnp' => 'Update MNP',
                'update_otp_channel' => 'Update OTP Channel',
                'update_biometric' => 'Update Biometric',
                'update_randomize_pin_keyboard' => 'Randomize PIN Keyboard',
            ],
            'storage_settings' => [
                'show' => 'View',
                'update' => 'Update',
                'test' => 'Test Connection',
            ],
        ],
    ],
    'business' => [
        'masters' => [
            'locations' => [
                'index' => 'List',
                'create' => 'Create',
                'update' => 'Update',
                'delete' => 'Delete',
                'show' => 'Show',
                'bulk' => 'Bulk Create',
            ],
            'outlets' => [
                'index' => 'List',
                'create' => 'Create',
                'update' => 'Update',
                'delete' => 'Delete',
                'show' => 'Show',
                'regenerate_qr' => 'Regenerate QR',
                'download_qr' => 'Download QR',
                'deactivate_qr' => 'Deactivate QR',
                'verify_qr' => 'Verify QR',
            ],
            'products' => [
                'index' => 'List',
                'create' => 'Create',
                'update' => 'Update',
                'delete' => 'Delete',
                'show' => 'Show',
            ],
            'product_categories' => [
                'index' => 'List',
                'create' => 'Create',
                'update' => 'Update',
                'delete' => 'Delete',
                'show' => 'Show',
            ],
            'units' => [
                'index' => 'List',
                'create' => 'Create',
                'update' => 'Update',
                'delete' => 'Delete',
                'show' => 'Show',
            ],
            'competitors' => [
                'index' => 'List',
                'create' => 'Create',
                'update' => 'Update',
                'delete' => 'Delete',
                'show' => 'Show',
            ],
        ],
        'field_operations' => [
            'outlet_assignments' => [
                'index' => 'List',
                'create' => 'Create',
                'update' => 'Update',
                'delete' => 'Delete',
                'show' => 'Show',
            ],
            'visits' => [
                'index' => 'List',
                'create' => 'Create',
                'update' => 'Update',
                'delete' => 'Delete',
                'show' => 'Show',
                'start' => 'Start Visit',
                'sync' => 'Sync Offline',
                'history' => 'View History',
                'verify_location' => 'Verify Location',
                'complete' => 'Complete Visit',
                'upload_photo' => 'Upload Photo',
                'add_competitor' => 'Add Competitor',
                'remove_competitor' => 'Remove Competitor',
                'add_product' => 'Add Product',
                'remove_product' => 'Remove Product',
            ],
            'beats' => [
                'index' => 'List',
                'create' => 'Create',
                'update' => 'Update',
                'delete' => 'Delete',
                'show' => 'Show',
                'today' => "Today's Beat",
            ],
            'beat_outlets' => [
                'index' => 'List',
                'create' => 'Assign',
                'delete' => 'Remove',
            ],
            'orders' => [
                'index' => 'List',
                'create' => 'Create',
                'update' => 'Update',
                'delete' => 'Delete',
                'show' => 'Show',
                'deliver' => 'Mark Delivered',
                'cancel' => 'Cancel',
            ],
            'order_items' => [
                'index' => 'List',
                'create' => 'Create',
                'update' => 'Update',
                'delete' => 'Delete',
                'show' => 'Show',
            ],
        ],
        'analytics' => [
            'dashboard' => [
                'view' => 'View',
            ],
            'reports' => [
                'visits' => 'Visits Report',
                'orders' => 'Orders Report',
                'officer_performance' => 'Officer Performance',
            ],
            'kpis' => [
                'index' => 'List',
                'create' => 'Create',
                'update' => 'Update',
                'delete' => 'Delete',
                'show' => 'Show',
                'summary' => 'View Summary',
            ],
            'map' => [
                'outlets' => 'Outlet Map',
                'nearby' => 'Nearby Outlets',
            ],
        ],
        'communications' => [
            'notifications' => [
                'index' => 'List',
                'mark_as_read' => 'Mark as Read',
                'mark_all_as_read' => 'Mark All as Read',
                'unread_count' => 'Unread Count',
            ],
        ],
    ],
];