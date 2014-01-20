create table if not exists `schedule` (
    `id`                integer primary key auto_increment,
    `server_id`         integer not null,
    `scheduled_ts`      integer not null,
    `last_run_ts`       integer not null default '0',
    `interval`          integer not null default '0',
    `name`              varchar(64) not null default '',
    `command`           integer not null,
    `run_for`           integer not null default '0',
    `status`            integer not null default '0'
) default charset=utf8;
alter table `server` add `announce_save` integer not null default 1;
alter table `server` add `kick_delay` integer not null default 3000;
