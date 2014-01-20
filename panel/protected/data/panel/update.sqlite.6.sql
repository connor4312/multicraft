alter table `server_config` add `user_name` integer not null default 1;
create index `idx_usr_name` on `user`(`name`);
