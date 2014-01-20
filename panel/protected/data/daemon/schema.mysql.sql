create table if not exists `server` ( 
    `id`                integer not null primary key auto_increment,
    `name`              varchar(128) not null, 
    `ip`                varchar(128) not null default '',
    `port`              integer not null default 25565,
    `dir`               varchar(128) not null,
    `world`             varchar(128) not null default '',
    `players`           integer not null,
    `memory`            integer not null default 0,
    `start_memory`      integer not null default 0,
    `jarfile`           varchar(128) not null default '',
    `autostart`         varchar(128) not null default '1',
    `default_level`     integer not null default 10,
    `daemon_id`         integer not null default 1
) default charset=utf8;
create table if not exists `player` (
    `id`                integer not null primary key auto_increment,
    `server_id`         integer not null,
    `name`              varchar(128) not null,
    `level`             integer not null default 0,
    `lastseen`          varchar(128) not null default '',
    `banned`            varchar(128) not null default '',
    `op`                varchar(128) not null default '',
    `status`            varchar(128) not null default '',
    `ip`                varchar(128) not null default '',
    `previps`           varchar(128) not null default '',
    `quitreason`        varchar(128) not null default '',
    unique (`server_id`, `name`),
    foreign key(`server_id`) references `server`(`id`) on delete cascade on update cascade
) default charset=utf8;
create table if not exists `command` (
    `id`                integer not null primary key auto_increment,
    `server_id`         integer not null,
    `name`              varchar(128) not null, `level` integer not null default 0,
    `prereq`            integer not null default 0,
    `chat`              varchar(128) not null default '',
    `response`          varchar(128) not null default '',
    `run`               varchar(128) not null default '',
    unique (`server_id`, `name`)
) default charset=utf8;
create table if not exists `setting` (
    `key`               varchar(128) not null primary key,
    `value`             varchar(128) not null
) default charset=utf8;
create table if not exists `daemon` ( 
    `id`                integer not null primary key,
    `name`              varchar(128) not null, 
    `ip`                varchar(128) not null default '',
    `port`              integer not null default 25465,
    `token`             varchar(128) not null
) default charset=utf8;
insert ignore into `setting` (`key`, `value`) values('dbVersion', '2');
