
CREATE TABLE `attribute_list` (
  `attribute_id` varchar(8) NOT NULL,
  `attribute_name` varchar(32) NOT NULL,
  `color` varchar(7) NOT NULL
);

CREATE TABLE `attribute_prefix` (
  `attribute_id` varchar(8) NOT NULL,
  `prefix` varchar(32) NOT NULL
);

CREATE TABLE `login_auth_group` (
  `auth_group` varchar(16) NOT NULL,
  `auth_name` varchar(32) NOT NULL
);

INSERT INTO `login_auth_group` (`auth_group`, `auth_name`) VALUES
('admin', 'login_users_mgmt'),
('admin', 'record_user_pass'),
('admin', 'setting_mgmt'),
('admin', 'users_mgmt'),
('default', 'record_user_pass');

CREATE TABLE `login_auth_list` (
  `auth_name` varchar(32) NOT NULL,
  `description` varchar(256) NOT NULL
);

INSERT INTO `login_auth_list` (`auth_name`, `description`) VALUES
('login_users_mgmt', '管理ユーザーの管理'),
('record_user_pass', '通過情報の登録'),
('setting_mgmt', '各種設定の管理'),
('users_mgmt', 'ユーザーの管理');

CREATE TABLE `login_tokens` (
  `token_id` varchar(64) NOT NULL,
  `token` varchar(64) NOT NULL,
  `login_user_id` varchar(64) NOT NULL,
  `valid_date` datetime NOT NULL
);

CREATE TABLE `login_users` (
  `login_user_id` varchar(64) NOT NULL,
  `login_id` varchar(32) NOT NULL,
  `login_user_name` varchar(64) NOT NULL,
  `password` varchar(256) NOT NULL,
  `auth_group` varchar(16) DEFAULT NULL
);

INSERT INTO `login_users` (`login_user_id`, `login_id`, `login_user_name`, `password`, `auth_group`) VALUES
('loginuseridguchimina', 'minamiguchi', '南口和生', '$2y$10$PgR.bZmnvRYi8GKYg0TL7eiKPCCfeWKjIHWUhtswXl/nWXGN1vLJC', 'admin');

CREATE TABLE `setting` (
  `id` varchar(64) NOT NULL,
  `value` varchar(128) NOT NULL,
  `description` varchar(256) NOT NULL
);

INSERT INTO `setting` (`id`, `value`, `description`) VALUES
('log_ticket_fetch_max', '10000', 'チケット情報のデータ取得数'),
('log_user_fetch_max', '10000', 'ユーザー情報のデータ取得数'),
('log_user_pass_fetch_max', '10000', '受付通過情報のデータ取得数'),
('ticket_prefix', 'T', 'チケットの先頭文字列　※複数ある場合は、コンマ \",\" で区切る'),
('use_ticket', '1', '0:チケットを使用しない / 1:チケットを使用する　※チケットをユーザーIDと紐付けることで、チケットで入退場管理ができるようになる。　※使用する場合は、ticket_prefixに値を使用する'),
('user_count_red_rate', '100', 'ダッシュボードの表示画面にて、定員に対する割合がこの値(%)を超えると、赤色に変化する'),
('user_count_yellow_rate', '80', 'ダッシュボードの表示画面にて、定員に対する割合がこの値(%)を超えると、黄色に変化する');

CREATE TABLE `setting_area` (
  `area_id` varchar(8) NOT NULL,
  `area_name` varchar(64) NOT NULL,
  `capacity` int NOT NULL DEFAULT '0',
  `hide` tinyint(1) NOT NULL DEFAULT '0',
  `color` varchar(8) NOT NULL DEFAULT '#444'
);

INSERT INTO `setting_area` (`area_id`, `area_name`, `capacity`, `hide`, `color`) VALUES
('P000', 'エリア外', 0, 1, '#444'),
('P001', 'エリア1', 100, 0, '#444'),
('P002', 'エリア2', 50, 0, '#444');

CREATE TABLE `setting_gate` (
  `gate_id` varchar(8) NOT NULL,
  `gate_name` varchar(64) NOT NULL,
  `in_area` varchar(8) NOT NULL,
  `out_area` varchar(8) NOT NULL,
  `can_make_ticket` tinyint(1) NOT NULL DEFAULT '0'
);

INSERT INTO `setting_gate` (`gate_id`, `gate_name`, `in_area`, `out_area`, `can_make_ticket`) VALUES
('G001', '受付＋チケット', 'P001', 'P000', 1),
('G002', '受付1', 'P001', 'P000', 0),
('G003', '受付2', 'P002', 'P001', 0);

CREATE TABLE `tickets` (
  `ticket_id` varchar(32) NOT NULL,
  `user_id` varchar(32) DEFAULT NULL,
  `time` datetime DEFAULT NULL
);

CREATE TABLE `users` (
  `user_id` varchar(32) NOT NULL,
  `area_id` varchar(8) NOT NULL,
  `time` datetime NOT NULL,
  `attribute_id` varchar(8) DEFAULT NULL
);

CREATE TABLE `users_pass` (
  `user_id` varchar(32) NOT NULL,
  `in_area` varchar(8) NOT NULL,
  `out_area` varchar(8) NOT NULL,
  `time` datetime NOT NULL
);


ALTER TABLE `attribute_list`
  ADD PRIMARY KEY (`attribute_id`);

ALTER TABLE `attribute_prefix`
  ADD PRIMARY KEY (`attribute_id`,`prefix`);

ALTER TABLE `login_auth_group`
  ADD PRIMARY KEY (`auth_group`,`auth_name`);

ALTER TABLE `login_auth_list`
  ADD PRIMARY KEY (`auth_name`);

ALTER TABLE `login_tokens`
  ADD PRIMARY KEY (`token_id`);

ALTER TABLE `setting`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `setting_area`
  ADD PRIMARY KEY (`area_id`);

ALTER TABLE `setting_gate`
  ADD PRIMARY KEY (`gate_id`);

ALTER TABLE `tickets`
  ADD PRIMARY KEY (`ticket_id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);
COMMIT;