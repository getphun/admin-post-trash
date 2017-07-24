<?php
/**
 * admin-post-trash config file
 * @package admin-post-trash
 * @version 0.0.1
 * @upgrade true
 */

return [
    '__name' => 'admin-post-trash',
    '__version' => '0.0.1',
    '__git' => 'https://github.com/getphun/admin-post-trash',
    '__files' => [
        'modules/admin-post-trash'  => [ 'install', 'remove', 'update' ],
        'theme/admin/post/trash'    => [ 'install', 'remove', 'update' ]
    ],
    '__dependencies' => [
        'admin-post'
    ],
    '_services' => [],
    '_autoload' => [
        'classes' => [
            'AdminPostTrash\\Controller\\PostController' => 'modules/admin-post-trash/controller/PostController.php'
        ],
        'files' => []
    ],
    
    '_routes' => [
        'admin' => [
            'adminPostTrash' => [
                'rule' => '/post/trash',
                'handler' => 'AdminPostTrash\\Controller\\Post::index'
            ],
            'adminPostTrashRestore' => [
                'rule' => '/post/trash/:id/restore',
                'handler' => 'AdminPostTrash\\Controller\\Post::restore'
            ],
            'adminPostTrashRemove' => [
                'rule' => '/post/trash/:id/remove',
                'handler' => 'AdminPostTrash\\Controller\\Post::remove'
            ]
        ]
    ],
    
    'admin' => [
        'menu' => [
            'post' => [
                'label' => 'Post',
                'icon' => 'newspaper-o',
                'order' => 10,
                'submenu' => [
                    'post-trash' => [
                        'label' => 'Trash',
                        'perms' => 'read_post_trash',
                        'target' => 'adminPostTrash',
                        'order' => 100,
                        'separator' => true
                    ]
                ]
            ]
        ]
    ],
    
    'formatter' => [
        'post-trash' => [
            'created'   => 'date',
            'updated'   => 'date',
            'deleted'   => 'date',
            'published' => 'date',
            'user'      => [
                'type'      => 'object',
                'model'     => 'User\\Model\\User'
            ],
            'deleter'   => [
                'type'      => 'object',
                'model'     => 'User\\Model\\User'
            ]
        ]
    ]
];