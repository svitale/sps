create
ALGORITHM = UNDEFINED
VIEW `VwTubeAndLocations`
AS 
select
items.id, locations.id_container, items.id_uuid,
items.id_study, items.id_subject, items.quant_init,
items.quant_cur, items.sample_type, items.id_visit,
items.date_collection, items.shipment_type, items.date_visit,
items.unit, items.timestamp, items.divY,
items.divX, items.destination, items.share,
locations.freezer, locations.id as locations_id,
locations.subdiv4 as subdivx,
locations.subdiv5 as subdivy
from items
inner join VwLocations_active as locations on items.id = locations.id_item
where
items.type = "tube";

create
ALGORITHM = UNDEFINED
VIEW `VwBoxAndLocations`
AS 
select
items.id, locations.id_container, items.id_uuid,
items.id_study, items.id_subject, items.quant_init,
items.quant_cur, items.sample_type, items.id_visit,
items.date_collection, items.shipment_type, items.date_visit,
items.unit, items.timestamp, items.divY,
items.divX, items.destination, items.share,
locations.freezer, locations.id as locations_id,
locations.subdiv3 as subdiv
from items
join VwLocations_active as locations on items.id = locations.id_item
where
items.type = "box";


create
ALGORITHM = UNDEFINED
VIEW `VwRackAndLocations`
AS 
select
items.id, locations.id_container, items.id_uuid,
items.id_study, items.id_subject, items.quant_init,
items.quant_cur, items.sample_type, items.id_visit,
items.date_collection, items.shipment_type, items.date_visit,
items.unit, items.timestamp, items.divY,
items.divX, items.destination, items.share,
locations.freezer, locations.id as locations_id,
locations.subdiv2 as subdiv
from items
join VwLocations_active as locations on items.id = locations.id_item
where
items.type = "rack";

create
ALGORITHM = UNDEFINED
VIEW `VwShelfAndLocations`
AS 
select
items.id, locations.id_container, items.id_uuid,
items.id_study, items.id_subject, items.quant_init,
items.quant_cur, items.sample_type, items.id_visit,
items.date_collection, items.shipment_type, items.date_visit,
items.unit, items.timestamp, items.divY,
items.divX, items.destination, items.share,
locations.freezer, locations.id as locations_id,
locations.subdiv1 as subdiv
from items
join VwLocations_active as locations on items.id = locations.id_item
where
items.type = "shelf";