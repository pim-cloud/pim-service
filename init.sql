-- im.contacts_add_record definition

CREATE TABLE `contacts_add_record` (
                                       `record_id` varchar(36) NOT NULL,
                                       `send_code` varchar(36) CHARACTER SET utf8 NOT NULL,
                                       `accept_code` varchar(36) CHARACTER SET utf8 NOT NULL,
                                       `remarks` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
                                       `status` enum('pending','agree','refuse','ignore') CHARACTER SET utf8 DEFAULT NULL,
                                       `created_at` datetime DEFAULT NULL,
                                       `updated_at` datetime DEFAULT NULL,
                                       PRIMARY KEY (`record_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='好友申请表';


-- im.contacts_friend definition

CREATE TABLE `contacts_friend` (
                                   `id` int(11) NOT NULL AUTO_INCREMENT,
                                   `main_code` varchar(32) CHARACTER SET utf8 NOT NULL COMMENT '我的uid',
                                   `friend_code` varchar(32) CHARACTER SET utf8 NOT NULL COMMENT '朋友uid',
                                   `remarks` varchar(20) NOT NULL,
                                   `created_at` datetime NOT NULL COMMENT '创建时间',
                                   `updated_at` datetime DEFAULT NULL,
                                   PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;


-- im.contacts_recent definition

CREATE TABLE `contacts_recent` (
                                   `send_code` varchar(36) CHARACTER SET utf8 NOT NULL,
                                   `accept_code` varchar(36) CHARACTER SET utf8 DEFAULT NULL,
                                   `content_id` int(11) DEFAULT NULL,
                                   `create_at` datetime DEFAULT NULL,
                                   `update_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='当前消息列表';


-- im.`group` definition

CREATE TABLE `group` (
                         `code` varchar(13) COLLATE utf8mb4_bin NOT NULL COMMENT '主键',
                         `nickname` varchar(30) COLLATE utf8mb4_bin NOT NULL COMMENT '群组名称',
                         `head_image` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT '群组头像',
                         `introduction` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT '群组简介',
                         `member_num` int(11) NOT NULL DEFAULT '1' COMMENT '群组人数',
                         `extra` varchar(1024) COLLATE utf8mb4_bin DEFAULT NULL COMMENT '附加属性',
                         `created_at` datetime NOT NULL COMMENT '创建时间',
                         `updated_at` datetime NOT NULL COMMENT '更新时间',
                         PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='群组表';


-- im.group_member definition

CREATE TABLE `group_member` (
                                `id` int(11) NOT NULL AUTO_INCREMENT,
                                `code` varchar(13) NOT NULL COMMENT '群号',
                                `m_code` varchar(36) NOT NULL COMMENT 'member code',
                                `type` enum('leader','admin','member') NOT NULL,
                                `extra` varchar(1024) DEFAULT NULL COMMENT '扩展字段',
                                `created_at` datetime NOT NULL,
                                `updated_at` datetime NOT NULL,
                                PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COMMENT='群成员';


-- im.group_member_join_record definition

CREATE TABLE `group_member_join_record` (
                                            `id` int(11) NOT NULL AUTO_INCREMENT,
                                            PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- im.`member` definition

CREATE TABLE `member` (
                          `code` varchar(32) NOT NULL COMMENT 'uid',
                          `username` varchar(11) CHARACTER SET utf8 NOT NULL COMMENT '用户名',
                          `email` varchar(50) CHARACTER SET utf8 DEFAULT NULL COMMENT '邮箱',
                          `password` varchar(32) CHARACTER SET utf8 DEFAULT NULL COMMENT '密码',
                          `salt` varchar(32) CHARACTER SET utf8 DEFAULT NULL,
                          `head_image` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT '头像',
                          `nickname` varchar(20) CHARACTER SET utf8 DEFAULT NULL COMMENT '昵称',
                          `autograph` varchar(50) CHARACTER SET utf8 DEFAULT NULL COMMENT '签名',
                          `created_at` datetime DEFAULT NULL COMMENT '创建时间',
                          `updated_at` datetime DEFAULT NULL COMMENT '上次登录时间',
                          PRIMARY KEY (`code`),
                          KEY `member_username_IDX` (`username`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- im.message definition

CREATE TABLE `message` (
                           `msg_id` varchar(36) NOT NULL,
                           `content` varchar(500) CHARACTER SET utf8 NOT NULL,
                           `send_code` varchar(36) CHARACTER SET utf8 NOT NULL,
                           `accept_type` enum('personal','group') NOT NULL,
                           `accept_code` varchar(36) CHARACTER SET utf8 NOT NULL,
                           `content_type` enum('text','picture','video') CHARACTER SET utf8 NOT NULL,
                           `created_at` datetime NOT NULL,
                           `updated_at` datetime DEFAULT NULL,
                           PRIMARY KEY (`msg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='消息内容';


-- im.message_index definition

CREATE TABLE `message_index` (
                                 `msg_id` varchar(36) NOT NULL,
                                 `send_code` varchar(36) CHARACTER SET utf8 NOT NULL,
                                 `accept_code` varchar(36) CHARACTER SET utf8 NOT NULL,
                                 `read_state` enum('read','unread') NOT NULL,
                                 `created_at` datetime NOT NULL,
                                 `updated_at` datetime DEFAULT NULL,
                                 PRIMARY KEY (`msg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='消息索引表';


-- im.message_session_list definition

CREATE TABLE `message_session_list` (
                                        `session_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '会话列表id',
                                        `topping` enum('yes','no') NOT NULL DEFAULT 'no',
                                        `session_type` enum('group','personal') DEFAULT NULL COMMENT '会话类型(群聊，单聊)',
                                        `main_code` varchar(32) NOT NULL COMMENT '我的uid',
                                        `accept_code` varchar(32) NOT NULL COMMENT '接收到uid',
                                        `disturb_status` enum('yes','no') NOT NULL DEFAULT 'no' COMMENT '是否屏蔽消息，不提示。',
                                        `on_line_status` enum('online','offline') NOT NULL DEFAULT 'online' COMMENT '在线状态',
                                        `unread` int(11) NOT NULL DEFAULT '0' COMMENT '未读消息数量',
                                        `last_message` varchar(500) DEFAULT NULL,
                                        `last_message_type` varchar(20) DEFAULT NULL,
                                        `last_time` datetime DEFAULT NULL,
                                        `created_at` datetime NOT NULL,
                                        `updated_at` datetime NOT NULL,
                                        PRIMARY KEY (`session_id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COMMENT='消息会话列表';