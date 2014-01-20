create table if not exists `bgplugin` (
    `name`              varchar(128) not null,
    `version`           varchar(32) not null,
    `installed_ts`      int(11) not null,
    `installed_files`   text not null,
    `server_id`         int(11) not null,
    `disabled`          tinyint(4) not null,
    primary key  (`server_id`,`name`)
) default charset=utf8;
create table if not exists `move_status` (
  `server_id` int(11) not null,
  `src_dmn` int(11) not null,
  `dst_dmn` int(11) not null,
  `status` varchar(32) not null,
  `message` text not null,
  primary key (`server_id`)
) default charset=utf8;
