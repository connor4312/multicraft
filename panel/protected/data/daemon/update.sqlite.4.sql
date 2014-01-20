create table if not exists `schedule` (
    `id`                integer primary key autoincrement,
    `server_id`         integer not null,
    `scheduled_ts`      integer not null,
    `last_run_ts`       integer not null default '0',
    `interval`          integer not null default '0',
    `name`              text not null default '',
    `command`           integer not null,
    `run_for`           integer not null default '0',
    `status`            integer not null default '0'
);
alter table `server` add `announce_save` integer not null default 1;
alter table `server` add `kick_delay` integer not null default 3000;
