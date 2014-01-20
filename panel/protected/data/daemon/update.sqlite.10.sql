create table if not exists `bgplugin` (
    `name`              text not null,
    `version`           text not null,
    `installed_ts`      integer not null,
    `installed_files`   text not null,
    `server_id`         integer not null,
    `disabled`          integer not null,
    primary key  (`server_id`,`name`)
);
create table if not exists `move_status` (
  `server_id` int(11) not null,
  `src_dmn` int(11) not null,
  `dst_dmn` int(11) not null,
  `status` text not null,
  `message` text not null,
  primary key (`server_id`)
);
