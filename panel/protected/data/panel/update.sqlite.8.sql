create table if not exists `mysql_db` ( 
    `server_id`         integer not null primary key,
    `name`              text not null, 
    `password`          text not null
);
alter table `server_config` add `display_ip` text not null default '';
alter table `server_config` add `user_players` integer not null default 0;
alter table `user` add `reset_hash` text not null default '';
create index `idx_usr_email` on `user`(`email`);
create index `idx_usrsv_user` on `user_server`(`user_id`);
create index `idx_usrsv_role` on `user_server`(`role`);
create index `idx_usrsv_server` on `user_server`(`server_id`);
