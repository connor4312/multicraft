create unique index `idx_usr_uq_name` on `user`(`name`);
drop index `idx_usr_name` on `user`;
create unique index `idx_usr_uq_email` on `user`(`email`(128));
drop index `idx_usr_email` on `user`;
