-- homestead.contacts_add_record definition

CREATE TABLE `contacts_add_record` (
                                       `record_id` varchar(36) NOT NULL,
                                       `main_code` varchar(36) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
                                       `accept_code` varchar(36) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
                                       `remarks` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
                                       `status` enum('pending','agree','refuse','ignore') CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
                                       `created_at` datetime DEFAULT NULL,
                                       `updated_at` datetime DEFAULT NULL,
                                       PRIMARY KEY (`record_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='好友申请表';


-- homestead.contacts_friend definition

CREATE TABLE `contacts_friend` (
                                   `id` int NOT NULL AUTO_INCREMENT,
                                   `main_code` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '我的uid',
                                   `accept_code` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '朋友uid',
                                   `remarks` varchar(20) NOT NULL DEFAULT '' COMMENT '备注',
                                   `topping` tinyint(1) NOT NULL DEFAULT '0' COMMENT '置顶',
                                   `disturb` tinyint(1) NOT NULL DEFAULT '0' COMMENT '不提示消息',
                                   `star` tinyint(1) NOT NULL DEFAULT '0' COMMENT '星标朋友',
                                   `created_at` datetime NOT NULL COMMENT '创建时间',
                                   `updated_at` datetime NOT NULL,
                                   PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1223 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- homestead.contacts_recent definition

CREATE TABLE `contacts_recent` (
                                   `send_code` varchar(36) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
                                   `accept_code` varchar(36) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
                                   `content_id` int DEFAULT NULL,
                                   `create_at` datetime DEFAULT NULL,
                                   `update_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='当前消息列表';


-- homestead.`group` definition

CREATE TABLE `group` (
                         `code` varchar(13) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '主键',
                         `nickname` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '群组名称',
                         `head_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '群组头像',
                         `introduction` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '群组简介',
                         `member_num` int NOT NULL DEFAULT '1' COMMENT '群组人数',
                         `extra` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '附加属性',
                         `created_at` datetime NOT NULL COMMENT '创建时间',
                         `updated_at` datetime NOT NULL COMMENT '更新时间',
                         PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='群组表';


-- homestead.group_member definition

CREATE TABLE `group_member` (
                                `id` int NOT NULL AUTO_INCREMENT,
                                `code` varchar(13) NOT NULL COMMENT '群号',
                                `m_code` varchar(36) NOT NULL COMMENT 'member code',
                                `type` enum('leader','admin','member') NOT NULL,
                                `extra` varchar(1024) DEFAULT NULL COMMENT '扩展字段',
                                `created_at` datetime NOT NULL,
                                `updated_at` datetime NOT NULL,
                                PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='群成员';


-- homestead.group_member_join_record definition

CREATE TABLE `group_member_join_record` (
                                            `id` int NOT NULL AUTO_INCREMENT,
                                            PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- homestead.`member` definition

CREATE TABLE `member` (
                          `code` varchar(32) NOT NULL COMMENT 'uid',
                          `username` varchar(11) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户名',
                          `email` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '邮箱',
                          `password` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '密码',
                          `salt` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
                          `head_image` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '头像',
                          `nickname` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '昵称',
                          `autograph` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '签名',
                          `created_at` datetime DEFAULT NULL COMMENT '创建时间',
                          `updated_at` datetime DEFAULT NULL COMMENT '上次登录时间',
                          PRIMARY KEY (`code`),
                          KEY `member_username_IDX` (`username`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- homestead.message definition

CREATE TABLE `message` (
                           `msg_id` varchar(36) NOT NULL,
                           `content` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
                           `main_code` varchar(36) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
                           `accept_type` enum('personal','group') NOT NULL,
                           `accept_code` varchar(36) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
                           `content_type` enum('text','picture','video') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
                           `created_at` datetime NOT NULL,
                           `updated_at` datetime DEFAULT NULL,
                           PRIMARY KEY (`msg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='消息内容';


-- homestead.message_index definition

CREATE TABLE `message_index` (
                                 `msg_id` varchar(36) NOT NULL,
                                 `main_code` varchar(36) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
                                 `accept_code` varchar(36) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
                                 `read_state` enum('read','unread') NOT NULL,
                                 `created_at` datetime NOT NULL,
                                 `updated_at` datetime DEFAULT NULL,
                                 PRIMARY KEY (`msg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='消息索引表';


-- homestead.message_session_list definition

CREATE TABLE `message_session_list` (
                                        `session_id` int NOT NULL AUTO_INCREMENT COMMENT '会话列表id',
                                        `topping` tinyint NOT NULL DEFAULT '0' COMMENT '是否置顶',
                                        `session_type` enum('group','personal') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '会话类型(群聊，单聊)',
                                        `main_code` varchar(32) NOT NULL COMMENT '我的uid',
                                        `accept_code` varchar(32) NOT NULL COMMENT '接收到uid',
                                        `online` tinyint NOT NULL DEFAULT '0' COMMENT '在线状态 0不在1在线',
                                        `unread` int NOT NULL DEFAULT '0' COMMENT '未读消息数量',
                                        `last_message` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
                                        `last_message_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
                                        `last_time` datetime NOT NULL,
                                        `created_at` datetime NOT NULL,
                                        `updated_at` datetime NOT NULL,
                                        PRIMARY KEY (`session_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='消息会话列表';