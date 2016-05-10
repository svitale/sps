select 'activity from 2014-09-29 to 2015-02-10';
select 'incoming';
select count(*) as num,sample_type,shipment_type from batch_quality where id_study = 'CRIC' and shipment_type != "" and date_receipt > '2014-09-29' and import_source = '44' group by sample_type,shipment_type order by sample_type,shipment_type;
select 'stored';
select count(*) as num,sample_type,shipment_type from items where id_study = 'CRIC' and type = 'tube'  and timestamp > '2014-09-29' group by sample_type,shipment_type order by sample_type,shipment_type;
select 'total';
select sum(num) from (select count(*) num,name_site from items left join locations on items.id = locations.id_item left join sites on sites.id = locations.id_site where type = 'tube' and id_study = 'CRIC' and share = '1' and consumed  != 1 and sample_type != 'Nail' and (id_site is null or id_site != 4) group by id_site) distrib;
select 'total by site';
select count(*) num,name_site from items left join locations on items.id = locations.id_item left join sites on sites.id = locations.id_site where type = 'tube' and id_study = 'CRIC' and share = '1' and consumed  != 1 and sample_type != 'Nail' and (id_site != 4 or id_site is null) group by id_site;
select 'in transit by destination';
select count(*) num,name_site,items.destination from items left join locations on items.id = locations.id_item left join sites on sites.id = locations.id_site where type = 'tube' and id_study = 'CRIC' and name_site = 'in transit' and share = '1' and consumed  != 1 and sample_type != 'Nail' and (id_site != 4 or id_site is null) group by id_site,items.destination
