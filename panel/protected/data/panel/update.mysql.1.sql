create table if not exists `version` (
    `id`                integer not null primary key,
    `version`           integer not null default 0
) default charset=utf8;
alter table `server_config` add `user_jar` integer not null default 0;
