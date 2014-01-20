create table if not exists `server_config` (
    `server_id`         integer not null primary key,
    `ip_auth_role`      text not null default 'guest',
    `give_role`         text not null default 'mod',
    `tp_role`           text not null default 'mod',
    `summon_role`       text not null default 'mod',
    `chat_role`         text not null default 'user'
);
create table if not exists `user` (
    `id`                integer not null primary key autoincrement,
    `name`              text not null,
    `password`          text not null,
    `email`             text not null
);
create table if not exists `user_player` (
    `user_id`           integer not null,
    `player_id`         integer not null primary key,
    foreign key (`user_id`) references `user` (`id`) on delete cascade on update cascade
);
create table if not exists `user_server` (
    `user_id`           integer not null,
    `server_id`         integer not null,
    `role`              text not null,
    primary key (`user_id`, `server_id`),
    foreign key (`user_id`) references `user` (`id`) on delete cascade on update cascade
);
insert or ignore into `user` (`id`, `name`, `password`, `email`) values(1, 'admin', '$1$jzz7gjyU$joMbjEumpueirScK1Z4E90', 'admin@localhost.local');
