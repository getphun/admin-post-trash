INSERT IGNORE INTO `user_perms` ( `name`, `group`, `role`, `about` ) VALUES
    ( 'read_post_trash',    'Post Trash', 'Management',   'Allow user to view all posts in trash' ),
    ( 'restore_post_trash', 'Post Trash', 'Management',   'Allow user to restore post from trash' ),
    ( 'remove_post_trash',  'Post Trash', 'Management',   'Allow user to remove any post in trash' );