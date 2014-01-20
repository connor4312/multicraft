alter table `user` add `global_role` varchar(16) not null default 'none';
alter table `user` add `api_key` varchar(40) not null default '';
