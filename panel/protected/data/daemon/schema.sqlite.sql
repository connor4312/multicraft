create table if not exists `server` ( 
                `id`                integer not null primary key autoincrement,
                `name`              text not null, 
                `ip`                text not null default '',
                `port`              integer not null default 25565,
                `dir`               text not null,
                `world`             text not null default '',
                `players`           integer not null,
                `memory`            integer not null default 0,
                `start_memory`      integer not null default 0,
                `jarfile`           text not null default '',
                `autostart`         text not null default '1',
                `default_level`     integer not null default 10,
                `daemon_id`         integer not null default 1
);
create table if not exists `player` (
                `id`                integer not null primary key autoincrement,
                `server_id`         integer not null,
                `name`              text not null,
                `level`             integer not null default 0,
                `lastseen`          text not null default '',
                `banned`            text not null default '',
                `op`                text not null default '',
                `status`            text not null default '',
                `ip`                text not null default '',
                `previps`           text not null default '',
                `quitreason`        text not null default '',
                unique (`server_id`, `name`),
                foreign key(`server_id`) references `server`(`id`) on delete cascade on update cascade
);
create table if not exists `command` (
                `id`                integer not null primary key autoincrement,
                `server_id`         integer not null,
                `name`              text not null, `level` integer not null default 0,
                `prereq`            integer not null default 0,
                `chat`              text not null default '',
                `response`          text not null default '',
                `run`               text not null default '',
                unique (`server_id`, `name`)
);
create table if not exists `setting` (
                `key`               text not null primary key,
                `value`             text not null
);
create table if not exists `daemon` ( 
                `id`                integer not null primary key,
                `name`              text not null, 
                `ip`                text not null default '',
                `port`              integer not null default 25465,
                `token`             text not null
);
insert or ignore into `setting` (`key`, `value`) values('dbVersion', '2');
