ALTER TABLE  `results_raw` ADD  `project_name` VARCHAR( 32 ) NULL DEFAULT NULL COMMENT  'Project Name' AFTER `id_rungroup` ,
ADD INDEX (  `project_name` ) ;
ALTER TABLE  `locations` ADD INDEX (  `id_site` ) ;

