alter table `server_config` add `visible` integer not null default 1;
create table if not exists `config_file` (
    `id`                integer primary key autoincrement not null,
    `name`              text not null,
    `description`       text not null,
    `file`              text not null,
    `options`           text not null,
    `type`              text not null default '',
    `enabled`           integer not null default 1
);
insert or ignore into `config_file` values(1,'Server Settings','Main Minecraft server settings configuration file.','server.properties','{"spawn-monsters":{"name":"Spawn Monsters","select":"bool"},"pvp":{"name":"Player vs Player","select":"bool"},"hellworld":{"name":"Hell World","select":"bool"},"online-mode":{"name":"Online Mode","select":"bool"},"spawn-animals":{"name":"Spawn Animals","select":"bool"},"server-ip":{"visible":false},"server-port":{"visible":false},"max-players":{"visible":false},"level-name":{"visible":false},"spawn-protection":{"name":"Protected Spawn Size","nocreate":true},"white-list":{"name":"Whitelisting","select":"bool"},"*":{"visible":true}}','properties',1);
insert or ignore into `config_file` values(3,'Operators','List of users with operator access to the Minecraft server.','ops.txt','','',1);
insert or ignore into `config_file` values(4,'Banned IPs','List of banned IP addresses for this server','banned-ips.txt','','',1);
insert or ignore into `config_file` values(5,'Banned Players','List of banned player names for this server.','banned-players.txt','','',1);
insert or ignore into `config_file` values(6,'Whitelisted Players','List of players that are allowed to connect. Note that this functionality is already provided by Multicraft.','white-list.txt','','',1);
