create table if not exists `server_config` (
    `server_id`         integer not null primary key,
    `ip_auth_role`      varchar(16) not null default 'guest',
    `give_role`         varchar(16) not null default 'mod',
    `tp_role`           varchar(16) not null default 'mod',
    `summon_role`       varchar(16) not null default 'mod',
    `chat_role`         varchar(16) not null default 'user'
) default charset=utf8;
create table if not exists `user` (
    `id`                integer not null primary key auto_increment,
    `name`              varchar(128) not null,
    `password`          varchar(128) not null,
    `email`             varchar(256) not null
) default charset=utf8;
create table if not exists `user_player` (
    `user_id`           integer not null,
    `player_id`         integer not null primary key,
    foreign key (`user_id`) references `user` (`id`) on delete cascade on update cascade
) default charset=utf8;
create table if not exists `user_server` (
    `user_id`           integer not null,
    `server_id`         integer not null,
    `role`              varchar(16) not null,
    primary key (`user_id`, `server_id`),
    foreign key (`user_id`) references `user` (`id`) on delete cascade on update cascade
) default charset=utf8;
insert ignore into `user` (`id`, `name`, `password`, `email`) values(1, 'admin', '$1$jzz7gjyU$joMbjEumpueirScK1Z4E90', 'admin@localhost.local');
