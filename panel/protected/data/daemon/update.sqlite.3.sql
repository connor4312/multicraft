create table if not exists `ftp_user` (
    `id`                integer not null primary key autoincrement,
    `name`              text not null,
    `password`          text not null
);
create table if not exists `ftp_user_server` (
    `user_id`           integer not null,
    `server_id`         integer not null,
    `perms`             text not null default 'elr',
    primary key (`user_id`, `server_id`),
    foreign key(`user_id`) references `ftp_user`(`id`) on delete cascade on update cascade,
    foreign key(`server_id`) references `server`(`id`) on delete cascade on update cascade
);
