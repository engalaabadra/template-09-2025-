<?php

return [
    'main_role' => 'superadmin',

    'roles_structure' => [
        'superadmin' => [
            'users' => 'read, trash, restore, restore-all, show, create, store, edit, update, destroy, destroy-many, force-delete, force-delete-many',
            'roles' => 'read, trash, restore, restore-all, show, create, store, edit, update, destroy, destroy-many, force-delete, force-delete-many',
            'countries' => 'read, trash, restore, restore-all, show, create, store, edit, update, destroy, destroy-many, force-delete, force-delete-many',
            'banners' => 'read, trash, restore, restore-all, show, create, store, edit, update, destroy, destroy-many, force-delete, force-delete-many',
            'boards' => 'read, trash, restore, restore-all, show, create, store, edit, update, destroy, destroy-many, force-delete, force-delete-many',
            'chats' => 'read, trash, restore, restore-all, show, create, store, edit, update, destroy, destroy-many, force-delete, force-delete-many',
            'contacts' => 'read, trash, restore, restore-all, show, create, store, edit, update, destroy, destroy-many, force-delete, force-delete-many',
            'notifications' => 'read',
            'orders' => 'read, trash, restore, restore-all, show, destroy, destroy-many, force-delete, force-delete-many',
            'reviews' => 'read, show, destroy, destroy-many, force-delete, force-delete-many'
            
        ],

        'admin' => [
            'countries' => 'read, trash, restore, restore-all, show, create, store, edit, update, destroy, destroy-many, force-delete, force-delete-many',
            'banners' => 'read, trash, restore, restore-all, show, create, store, edit, update, destroy, destroy-many, force-delete, force-delete-many',
            'boards' => 'read, trash, restore, restore-all, show, create, store, edit, update, destroy, destroy-many, force-delete, force-delete-many',
            'chats' => 'read, trash, restore, restore-all, show, create, store, edit, update, destroy, destroy-many, force-delete, force-delete-many',
            'contacts' => 'read, trash, restore, restore-all, show, create, store, edit, update, destroy, destroy-many, force-delete, force-delete-many',
            'notifications' => 'read',
            'orders' => 'read, trash, restore, restore-all, show, destroy, destroy-many, force-delete, force-delete-many',
            // 'reviews' => 'read, show, destroy, destroy-many, force-delete, force-delete-many'

        ],


        'user' => [
            'countries' => 'read, show',
            'banners' => 'read',
            'boards' => 'read',
            'chats' => 'read, trash, restore, restore-all, show, create, store, edit, update, destroy, destroy-many, force-delete, force-delete-many',
            'contacts' => 'read',
            'notifications' => 'read',
            'orders' => 'read, trash, restore, restore-all, show, destroy, destroy-many, force-delete, force-delete-many',
            // 'reviews' => 'read, trash, restore, restore-all, show, create, store, edit, update, destroy, destroy-many, force-delete, force-delete-many'

        ],   
    ],

    'create_users' => true, // Set to false to skip user creation
];
