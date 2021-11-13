
CREATE TABLE `attribute_list` (
  `attribute_id` varchar(8)  NOT NULL,
  `attribute_name` varchar(32)  NOT NULL
) ;

CREATE TABLE `attribute_prefix` (
  `attribute_id` varchar(8)  NOT NULL,
  `prefix` varchar(32)  NOT NULL
) ;


CREATE TABLE `login_auth_group` (
  `auth_group` varchar(16)  NOT NULL,
  `auth_name` varchar(32)  NOT NULL
) ;

--
-- テーブルのデータのダンプ `login_auth_group`
--

INSERT INTO `login_auth_group` (`auth_group`, `auth_name`) VALUES
('admin', 'all_reception'),
('admin', 'log_watcher'),
('admin', 'login_users_mgmt'),
('admin', 'setting_mgmt'),
('admin', 'users_mgmt');

CREATE TABLE `login_auth_list` (
  `auth_name` varchar(32)  NOT NULL,
  `description` varchar(256)  NOT NULL
) ;

INSERT INTO `login_auth_list` (`auth_name`, `description`) VALUES
('all_reception', '全受付で使用可'),
('log_mgmt', 'ログの監視'),
('login_users_mgmt', '管理ユーザーの管理'),
('setting_mgmt', '各種設定の管理'),
('users_mgmt', '参加者の管理');

CREATE TABLE `login_range_gate` (
  `login_user_id` varchar(64)  NOT NULL,
  `gate_id` varchar(8)  NOT NULL
) ;

INSERT INTO `login_range_gate` (`login_user_id`, `gate_id`) VALUES
('854df866-4314-434f-b5c1-669b61bcc909', 'G234'),
('854df866-4314-434f-b5c1-669b61bcc909', 'G434');

CREATE TABLE `login_tokens` (
  `id` int NOT NULL,
  `token_id` varchar(64)  NOT NULL,
  `token` varchar(64)  NOT NULL,
  `login_user_id` varchar(64)  NOT NULL,
  `valid_date` datetime NOT NULL
) ;


CREATE TABLE `login_users` (
  `login_user_id` varchar(64)  NOT NULL,
  `login_id` varchar(32)  NOT NULL,
  `login_user_name` varchar(64)  NOT NULL,
  `password` varchar(256)  NOT NULL,
  `auth_group` varchar(16) CHARACTER SET utf8mb4  DEFAULT NULL
) ;


INSERT INTO `login_users` (`login_user_id`, `login_id`, `login_user_name`, `password`, `auth_group`) VALUES
('854df866-4314-434f-b5c1-669b61bcc909', 'test', 'test_user', '$2y$10$k.nPqlT6ZJK3.5chu7v9Ce4e9jGWhVKD4qfN.uGM37AhqPdhXbktm', 'default'),
('loginuseridguchimina', 'minamiguchi', '南口和生', '$2y$10$CFEI/6cMrrL33zEYZHvGQu/Ioct1AP4we5RlRCaJ8C.qTVpf9T1sC', 'admin');


CREATE TABLE `setting` (
  `id` varchar(64)  NOT NULL,
  `value` varchar(128)  NOT NULL,
  `description` varchar(256)  NOT NULL
) ;

CREATE TABLE `setting_area` (
  `area_id` varchar(8)  NOT NULL,
  `area_name` varchar(64)  NOT NULL,
  `capacity` int NOT NULL DEFAULT '0',
  `hide` tinyint(1) NOT NULL DEFAULT '0',
  `color` varchar(8)  NOT NULL DEFAULT '#444'
) ;

CREATE TABLE `setting_gate` (
  `gate_id` varchar(8)  NOT NULL,
  `gate_name` varchar(64)  NOT NULL,
  `in_area` varchar(8)  NOT NULL,
  `out_area` varchar(8)  NOT NULL,
  `can_make_ticket` tinyint(1) NOT NULL DEFAULT '0'
) ;


CREATE TABLE `tickets` (
  `ticket_id` varchar(32)  NOT NULL,
  `user_id` varchar(32)  DEFAULT NULL,
  `time` datetime DEFAULT NULL
) ;



CREATE TABLE `users` (
  `user_id` varchar(32)  NOT NULL,
  `area_id` varchar(8)  NOT NULL,
  `timea` datetime NOT NULL,
  `attribute_id` varchar(8)  DEFAULT NULL
) ;


CREATE TABLE `users_pass` (
  `id` int NOT NULL,
  `user_id` varchar(32)  NOT NULL,
  `in_area` varchar(8)  NOT NULL,
  `out_area` varchar(8)  NOT NULL,
  `time` datetime NOT NULL
) ;