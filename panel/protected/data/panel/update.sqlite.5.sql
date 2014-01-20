alter table `server_config` add `user_schedule` integer not null default 1;
create table if not exists `command_cache` (
    `server_id`         integer not null,
    `command`           integer not null,
    `ts`                integer not null,
    `data`              text not null,
    primary key (`server_id`, `command`)
);
alter table `config_file` add `dir` text not null default '';
insert or ignore into `config_file` (`name`, `description`, `file`, `options`, `type`, `enabled`, `dir`) values('{file}','Plugin config file: {dir}{file}','[^.]+\.(txt|yml|csv)','','',1,'plugins/*/');
