<?php

use yii\db\Migration;

/**
 * Class m221128_203000_create_reports_functions
 */
class m221128_203000_create_reports_functions extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("
        CREATE OR REPLACE FUNCTION get_FullServicesReport(startdate DATE, enddate DATE, areas TEXT, districts TEXT, VisitTypes TEXT, statuses TEXT, organizations TEXT, specialists text, services TEXT) 
        RETURNS TABLE
        ( id INTEGER,
          date text, 
          service_name TEXT,  
          total_lq INTEGER,
          total_phone INTEGER,
          total_workday INTEGER,
          total_mosru INTEGER,
          total_mpgu INTEGER,
          total_ambulance INTEGER,
          total_home_mosru INTEGER,
          total_vacc_station INTEGER,
          total_detour INTEGER,
          total_shelter INTEGER 
        )
        LANGUAGE PLPGSQL
        AS \$plpgsql\$ 
          
        --DECLARE
        --  _mosruChannelId INTEGER;
        BEGIN 
        
        CREATE TEMP TABLE tt_organizations ON COMMIT DROP AS
        SELECT unnest(string_to_array(organizations, ',')) :: INTEGER id;
        
        CREATE TEMP TABLE tt_areas ON COMMIT DROP AS
        SELECT unnest(string_to_array(areas, ',')) :: INTEGER id;
        
        CREATE TEMP TABLE tt_districts ON COMMIT DROP AS
        SELECT unnest(string_to_array(districts, ',')) :: INTEGER id;
        
        CREATE TEMP TABLE tt_statuses ON COMMIT DROP AS
        SELECT unnest(string_to_array(statuses, ',')) :: varchar(255) status;
        
        CREATE TEMP TABLE tt_visittypes ON COMMIT DROP AS
        SELECT unnest(string_to_array(VisitTypes, ',')) :: VARCHAR(32) TypeName;
        
        CREATE TEMP TABLE tt_specialists ON COMMIT DROP AS
        SELECT unnest(string_to_array(specialists, ',')) :: INTEGER UserId;
        
        CREATE TEMP TABLE tt_services ON COMMIT DROP AS
        SELECT unnest(string_to_array(services, ',')) :: INTEGER id;
         
         
        
        RETURN QUERY
        SELECT 
          row_number() over (order by substr(coalesce(v.fact_start_dttm, v.start_dttm, v.created_at)::text, 1, 7) asc)::INTEGER as id,
          substr(coalesce(v.fact_start_dttm, v.start_dttm, v.created_at)::text, 1, 7) as date,
          gs.name as service_name,
            sum(case when st.type = 'LIVE_QUEUE' then vgs0.total else 0 end)::INTEGER as total_lq,
            sum(case when st.type = 'PHONE_APPOINTMENT' then vgs0.total else 0 end)::INTEGER  as total_phone,
            sum(case when st.type = 'WORKDAY' then vgs0.total else 0 end)::INTEGER  as total_workday,
            sum(case when st.type = 'MOSRU_APPOINTMENT' and (not (m.service_number ilike '%-9000005-%' or m.service_number ilike '0002%') or m.service_number isnull) then vgs0.total else 0 end)::INTEGER as total_mosru,
            sum(case when st.type = 'MOSRU_APPOINTMENT' and (m.service_number ilike '%-9000005-%' or m.service_number ilike '0002%')  then vgs0.total else 0 end)::INTEGER  as total_mpgu,
            sum(case when st.type = 'AMBULANCE' then vgs0.total else 0 end)::INTEGER  as total_ambulance,
            sum(case when st.type = 'MOSRU_CALL_TO_HOME' then vgs0.total else 0 end)::INTEGER  as total_home_mosru,
            sum(case when st.type = 'VACCINATION_STATION' then vgs0.total else 0 end)::INTEGER  as total_vacc_station,
            sum(case when st.type = 'DETOUR' then vgs0.total else 0 end)::INTEGER  as total_detour,
            sum(case when st.type = 'SHELTER' then vgs0.total else 0 end)::INTEGER  as total_shelter
        FROM visits v
        INNER JOIN
        ( 
              SELECT 
                  rf.id_visit,
                  rf.id_service,
                  rf.id_species,
                  sum(rf.count) AS total
              FROM           
               (
                    SELECT    
                       vgs.id_visit,
                       vgs.id_service,
                       p.id_species,
                       count(p.id) AS count
                    FROM public.visits_gov_services vgs
                    LEFT JOIN visit_pets vp 
                        ON vp.id_visit = vgs.id_visit
                    LEFT JOIN pets p 
                        ON p.id = vp.id_pet
                    WHERE vgs.id_pet is NULL
                    GROUP BY vgs.id_visit, vgs.id_service, p.id_species
                ) rf
              WHERE rf.id_species IS not NULL
              group BY rf.id_visit, rf.id_service, rf.id_species
         ) vgs0
            ON vgs0.id_visit = v.id
        LEFT JOIN gov_services gs 
           ON vgs0.id_service = gs.id
        LEFT JOIN etp.message_v2 m
            on m.visit_id = v.id
        LEFT JOIN organizations o 
          ON o.id = v.id_organization
        LEFT JOIN fias_addresses fa 
          ON fa.id = o.id_fias_address
        LEFT JOIN areas a 
          ON a.id = fa.id_area
        LEFT JOIN districts d 
          ON d.id = fa.id_district
        LEFT JOIN shift_type st 
          ON st.id = v.channel
        LEFT JOIN visits_specialists vs 
          ON vs.id_visit = v.id
        LEFT JOIN specialists sp 
          ON sp.id = vs.id_specialist
        LEFT JOIN users u 
          ON u.id = sp.id_user
        WHERE coalesce(v.fact_start_dttm, v.start_dttm, v.created_at)::date BETWEEN startdate AND enddate  
        AND  EXISTS (
                  SELECT 1
                  FROM tt_visittypes tvt
                  WHERE v.type = tvt.TypeName
                  
                  UNION ALL
                  SELECT 1
                  WHERE VisitTypes is NULL 
         )
         
         
          AND EXISTS (
                    SELECT 1 
                    FROM tt_organizations fo
                    WHERE fo.id = v.id_organization
                
                    UNION ALL
                    SELECT  1
                    WHERE organizations IS NULL
           )
          AND EXISTS (
                    SELECT  1
                    FROM tt_areas ta
                    WHERE fa.id_area = ta.id
                
                    UNION ALL
                    SELECT  1
                    WHERE areas IS NULL
          )
          AND EXISTS (
                    SELECT  1
                    FROM tt_districts fd
                    WHERE fd.id = fa.id_district
                
                    UNION ALL
                    SELECT 1
                    WHERE districts  IS NULL
          )
          AND EXISTS (
                    SELECT  1
                    FROM tt_specialists fs
                    WHERE fs.UserId = sp.id_user
                
                    UNION ALL
                    SELECT 1
                    WHERE specialists  IS NULL 
          )  
           AND EXISTS (
                    SELECT  1
                    FROM tt_services fss
                    WHERE fss.id = vgs0.id_service
                
                    UNION ALL
                    SELECT 1
                    WHERE services  IS NULL 
          )  
          And EXISTS ( 
                    SELECT 1
                    FROM tt_statuses fsp 
                    WHERE fsp.status = v.status
        
                    UNION ALL
                    SELECT 1
                    WHERE statuses is NULL 
             )
        
        GROUP BY 
            date,
            gs.name
        ORDER BY
            date,
            gs.name;
        END;
        
         \$plpgsql\$;
        ");

        $this->execute("
        CREATE OR REPLACE FUNCTION get_FullServicesVol2DetailReport(startdate DATE, enddate DATE, areas TEXT, districts TEXT, VisitTypes TEXT, channels TEXT, organizations TEXT, species TEXT,specialists text, services TEXT) 
        RETURNS TABLE
        ( id INTEGER,
          date text,
          area_name CHARACTER VARYING,  
          short_name CHARACTER VARYING,
          channel TEXT,
          service_name TEXT,  
          total_services INTEGER,
          total_f INTEGER,
          total_a INTEGER,
          total_n INTEGER,
          total_w INTEGER,
          total_t INTEGER,
          total_c INTEGER,
          total_d INTEGER 
        )
        LANGUAGE PLPGSQL
        AS \$plpgsql\$ 
          
        --DECLARE
        --  _mosruChannelId INTEGER;
        BEGIN 
        
        CREATE TEMP TABLE tt_organizations ON COMMIT DROP AS
        SELECT unnest(string_to_array(organizations, ',')) :: INTEGER id;
        
        CREATE TEMP TABLE tt_areas ON COMMIT DROP AS
        SELECT unnest(string_to_array(areas, ',')) :: INTEGER id;
        
        CREATE TEMP TABLE tt_districts ON COMMIT DROP AS
        SELECT unnest(string_to_array(districts, ',')) :: INTEGER id;
        
        CREATE TEMP TABLE tt_species ON COMMIT DROP AS
        SELECT unnest(string_to_array(species, ',')) :: VARCHAR(20) SpecName;
        
        CREATE TEMP TABLE tt_visittypes ON COMMIT DROP AS
        SELECT unnest(string_to_array(VisitTypes, ',')) :: VARCHAR(32) TypeName;
        
        CREATE TEMP TABLE tt_specialists ON COMMIT DROP AS
        SELECT unnest(string_to_array(specialists, ',')) :: INTEGER UserId;
        
        CREATE TEMP TABLE tt_services ON COMMIT DROP AS
        SELECT unnest(string_to_array(services, ',')) :: INTEGER id;
        
        CREATE TEMP TABLE tt_channels ON COMMIT DROP AS
        SELECT unnest(string_to_array(channels, ',')) :: INTEGER id;
        
        UPDATE tt_species SET 
          SpecName = 'HORSE'
        WHERE SpecName = 'OTHER'; 
        
        RETURN QUERY
        SELECT 
            row_number() over (order by substr(coalesce(v.fact_start_dttm, v.start_dttm, v.created_at)::text, 1, 7) asc)::INTEGER as id,
            substr(coalesce(v.fact_start_dttm, v.start_dttm, v.created_at)::text, 1, 7) as date,
            a.name as area_name,
            o.short_name,
            (case when v.channel = 1 then 'Направление' else st.description END) ::text as channel,
            gs.name as service_name,
            sum(case when v.status is not null then vgs0.total else 0 END) ::INTEGER as total_services,
            sum(case when v.status = 'F' then vgs0.total else 0 end)::INTEGER as total_f,
            sum(case when v.status = 'A' then vgs0.total else 0 end)::INTEGER as total_a,
            sum(case when v.status = 'N' then vgs0.total else 0 end)::INTEGER as total_n,
            sum(case when v.status = 'W' then vgs0.total else 0 end)::INTEGER as total_w,
            sum(case when v.status = 'T' then vgs0.total else 0 end)::INTEGER as total_t,
            sum(case when v.status = 'C' then vgs0.total else 0 end)::INTEGER as total_c,
            sum(case when v.status = 'D' then vgs0.total else 0 end)::INTEGER AS total_d
        FROM visits v
        INNER JOIN
        ( 
              SELECT 
                  rf.id_visit,
                  rf.id_service,
                  rf.id_species,
                  sum(rf.count) AS total
              FROM           
               (
                    SELECT    
                       vgs.id_visit,
                       vgs.id_service,
                       p.id_species,
                       count(p.id) AS count
                    FROM public.visits_gov_services vgs
                    LEFT JOIN visit_pets vp 
                        ON vp.id_visit = vgs.id_visit
                    LEFT JOIN pets p 
                        ON p.id = vp.id_pet
                    WHERE vgs.id_pet is NULL
                    GROUP BY vgs.id_visit, vgs.id_service, p.id_species
                ) rf
              WHERE rf.id_species IS not NULL
              group BY rf.id_visit, rf.id_service, rf.id_species
         ) vgs0
            ON vgs0.id_visit = v.id
        LEFT JOIN gov_services gs 
           ON vgs0.id_service = gs.id
        LEFT JOIN organizations o 
          ON o.id = v.id_organization
        LEFT JOIN fias_addresses fa 
          ON fa.id = o.id_fias_address
        LEFT JOIN areas a 
          ON a.id = fa.id_area
        LEFT JOIN districts d 
          ON d.id = fa.id_district
        LEFT JOIN shift_type st 
          ON st.id = v.channel
        LEFT JOIN species s 
          ON s.id = vgs0.id_species
        LEFT JOIN visits_specialists vs 
          ON vs.id_visit = v.id
        LEFT JOIN specialists sp 
          ON sp.id = vs.id_specialist
        LEFT JOIN users u 
          ON u.id = sp.id_user
        WHERE coalesce(v.fact_start_dttm, v.start_dttm, v.created_at)::date BETWEEN startdate AND enddate  
        AND  EXISTS (
                  SELECT 1
                  FROM tt_visittypes tvt
                  WHERE v.type = tvt.TypeName
                  
                  UNION ALL
                  SELECT 1
                  WHERE VisitTypes is NULL 
         )
          AND EXISTS( 
                  SELECT 1
                  FROM tt_channels tc
                  WHERE v.channel =  tc.id
          
                  UNION ALL
                  SELECT  1
                  WHERE channels is NULL 
           )
         
          AND EXISTS (
                    SELECT 1 
                    FROM tt_organizations fo
                    WHERE fo.id = v.id_organization
                
                    UNION ALL
                    SELECT  1
                    WHERE organizations IS NULL
           )
          AND EXISTS (
                    SELECT  1
                    FROM tt_areas ta
                    WHERE fa.id_area = ta.id
                
                    UNION ALL
                    SELECT  1
                    WHERE areas IS NULL
          )
          AND EXISTS (
                    SELECT  1
                    FROM tt_districts fd
                    WHERE fd.id = fa.id_district
                
                    UNION ALL
                    SELECT 1
                    WHERE districts  IS NULL
          )
          AND EXISTS (
                    SELECT  1
                    FROM tt_specialists fs
                    WHERE fs.UserId = sp.id_user
                
                    UNION ALL
                    SELECT 1
                    WHERE specialists  IS NULL 
          )  
           AND EXISTS (
                    SELECT  1
                    FROM tt_services fss
                    WHERE fss.id = vgs0.id_service
                
                    UNION ALL
                    SELECT 1
                    WHERE services  IS NULL 
          )  
          And EXISTS ( 
                    SELECT 1
                    FROM tt_species fsp 
                    WHERE fsp.SpecName = coalesce(s.tech_name,'HORSE')
        
                    UNION ALL
                    SELECT 1
                    WHERE species is NULL 
             )
        
        GROUP BY 
            date,
            gs.name,
            a.name,
            o.short_name,
            st.description,
            v.channel
        ORDER BY
            date  ,            
            a.name  ,           
            o.short_name  ,          
            st.description , 
            gs.name;
        
        END;
         \$plpgsql\$;
        ");



        $this->execute("
        CREATE OR REPLACE FUNCTION get_fullvisitsvol2report (startdate DATE, enddate DATE, organizationslist TEXT, areaslist TEXT, districtslist TEXT, specieslist TEXT) RETURNS TABLE (
            id INTEGER,
            id_area INTEGER,
            area_name CHARACTER VARYING,
            id_district INTEGER,
            dist_name CHARACTER VARYING,
            short_name CHARACTER VARYING,
            spec_name CHARACTER VARYING,
            total_visits INTEGER,
            total_finished INTEGER,
            cancelled_by_clinic INTEGER,
            cancelled_by_owner INTEGER,
            transferred_not_mosru INTEGER,
            transferred_by_clinic_mosru INTEGER,
            transferred_by_owner_mosru INTEGER
          )
          LANGUAGE PLPGSQL
          AS \$plpgsql\$
          DECLARE
            _mosruChannelId INTEGER;
          
          BEGIN
          
            _mosruChannelId := (SELECT
                s.id
              FROM shift_type s
              WHERE s.TYPE = 'MOSRU_APPOINTMENT');
          
            CREATE TEMP TABLE tt_organizations ON COMMIT DROP AS
            SELECT
              unnest(string_to_array(organizationsList, ',')) :: INTEGER id;
          
            CREATE TEMP TABLE tt_areas ON COMMIT DROP AS
            SELECT
              unnest(string_to_array(areasList, ',')) :: INTEGER id;
          
            CREATE TEMP TABLE tt_districts ON COMMIT DROP AS
            SELECT
              unnest(string_to_array(districtsList, ',')) :: INTEGER id;
          
            CREATE TEMP TABLE tt_species ON COMMIT DROP AS
            SELECT
              unnest(string_to_array(speciesList, ',')) :: VARCHAR(20) SpecName;
          
          
            RETURN QUERY
            SELECT
              organizations.id,
              fias_addresses.id_area,
              areas.name AS area_name,
              fias_addresses.id_district,
              districts.name AS dist_name,
              organizations.short_name,
              (
              CASE WHEN species.tech_name = 'CAT' THEN 'Кошки' :: VARCHAR(30)
              ELSE
              CASE WHEN species.tech_name = 'DOG' THEN 'Собаки' :: VARCHAR(30)
              ELSE 'Иные животные' :: VARCHAR(30) END
              END
              ) AS spec_name,
          
              COUNT(DISTINCT visits.id) :: INTEGER AS total_visits,
              SUM(CASE WHEN visits.status = 'F' THEN 1 ELSE 0 END) :: INTEGER AS total_finished,
              COUNT(DISTINCT CASE WHEN visits.status = 'A' AND (visits.cancel_initiator != 'OWNER' OR visits.cancel_initiator IS NULL) THEN visits.id END) :: INTEGER AS cancelled_by_clinic,
              COUNT(DISTINCT CASE WHEN visits.status = 'A' AND visits.cancel_initiator = 'OWNER' THEN visits.id END) :: INTEGER AS cancelled_by_owner,
              SUM(CASE WHEN visits.status = 'T' AND visits.channel <> _mosruChannelId THEN 1 ELSE 0 END) :: INTEGER AS transferred_not_mosru,
              SUM(CASE WHEN visits.status = 'C' AND etp.status_log.etp_status = '1053' AND visits.channel = _mosruChannelId THEN 1 ELSE 0 END) :: INTEGER AS transferred_by_clinic_mosru,
              SUM(CASE WHEN visits.status = 'C' AND etp.status_log.etp_status = '8021' AND visits.channel = _mosruChannelId THEN 1 ELSE 0 END) :: INTEGER AS transferred_by_owner_mosru
            FROM visits
              LEFT JOIN organizations
                ON organizations.id = visits.id_organization
              LEFT JOIN fias_addresses
                ON fias_addresses.id = organizations.id_fias_address
              LEFT JOIN areas
                ON areas.id = fias_addresses.id_area
              LEFT JOIN districts
                ON districts.id = fias_addresses.id_district
              LEFT JOIN pets
                ON pets.id = visits.id_pet
              LEFT JOIN species
                ON species.id = pets.id_species
              LEFT JOIN etp.status_log
                ON etp.status_log.visit_id = visits.id
                AND etp.status_log.etp_status IN ('1053', '8021')
            WHERE COALESCE(visits.fact_start_dttm, visits.start_dttm, visits.created_at) :: DATE BETWEEN startDate AND endDate
            AND EXISTS (SELECT
                1
              FROM tt_organizations fo
              WHERE fo.id = organizations.id
          
              UNION ALL
              SELECT
                1
              WHERE organizationsList IS NULL)
            AND EXISTS (SELECT
                1
              FROM tt_areas fa
              WHERE fa.id = areas.id
          
              UNION ALL
              SELECT
                1
              WHERE areasList IS NULL)
            AND EXISTS (SELECT
                1
              FROM tt_districts fd
              WHERE fd.id = districts.id
          
              UNION ALL
              SELECT
                1
              WHERE districtsList IS NULL)
            AND EXISTS (SELECT
                1
              FROM tt_species ts
              WHERE ts.SpecName = (
              CASE WHEN species.tech_name = 'CAT' THEN 'Кошки'
              ELSE
              CASE WHEN species.tech_name = 'DOG' THEN 'Собаки'
              ELSE 'Иные животные' END
              END
              )
          
              UNION ALL
              SELECT
                1
              WHERE speciesList IS NULL)
          
            GROUP BY organizations.id,
                     fias_addresses.id_area,
                     areas.name,
                     fias_addresses.id_district,
                     districts.name,
                     visits.id_organization,
                     organizations.short_name,
                     spec_name
            ORDER BY area_name,
            dist_name,
            organizations.short_name,
            spec_name;
          END;
          
          \$plpgsql\$;
        ");

        $this->execute("
        CREATE OR REPLACE FUNCTION get_FullVisitsReport (startdate DATE, enddate DATE, organizations TEXT,areas TEXT, districts TEXT) RETURNS TABLE (
            id_organization INTEGER,
            short_name CHARACTER VARYING,
            id_fias_address INTEGER,
            id_area INTEGER,
            area_name CHARACTER VARYING,
            id_district INTEGER,
            dist_name CHARACTER VARYING, 
               mosru_appointment_created  INTEGER,
            mosru_appointment_transfered INTEGER, 
            mosru_appointment_cancelled INTEGER,
            mosru_appointment_finished INTEGER,
            phone_appointment_created INTEGER,
            phone_appointment_transfered INTEGER,
            phone_appointment_cancelled INTEGER,
            phone_appointment_finished INTEGER,
            live_queue_created	 INTEGER,
            live_queue_transfered	 INTEGER,
            live_queue_cancelled	 INTEGER,
            live_queue_finished	 INTEGER,
            workday_created	 INTEGER,
            workday_transfered	 INTEGER,
            workday_cancelled	 INTEGER,
            workday_finished	 INTEGER,
            call_to_home_created	 INTEGER,
            call_to_home_transfered	 INTEGER,
            call_to_home_cancelled	 INTEGER,
            call_to_home_finished	 INTEGER,
            mosru_call_to_home_created	 INTEGER,
            mosru_call_to_home_transfered	 INTEGER,
            mosru_call_to_home_cancelled	 INTEGER,
            mosru_call_to_home_finished	 INTEGER,
            ambulance_created	 INTEGER,
            ambulance_transfered	 INTEGER,
            ambulance_cancelled	 INTEGER,
            ambulance_finished	 INTEGER,
            vaccination_station_created	 INTEGER,
            vaccination_station_transfered	 INTEGER,
            vaccination_station_cancelled	 INTEGER,
            vaccination_station_finished	 INTEGER,
            shelter_created	 INTEGER,
            shelter_transfered	 INTEGER,
            shelter_cancelled	 INTEGER,
            shelter_finished	 INTEGER,
            detour_created	 INTEGER,
            detour_transfered	 INTEGER,
            detour_cancelled	 INTEGER,
            detour_finished INTEGER
          )
          LANGUAGE PLPGSQL
          AS \$plpgsql\$
          BEGIN
          
          CREATE TEMP TABLE tt_organizations ON COMMIT DROP AS
          SELECT unnest(string_to_array(organizations, ',')) :: INTEGER id;
          
          CREATE TEMP TABLE tt_areas ON COMMIT DROP AS
          SELECT unnest(string_to_array(areas, ',')) :: INTEGER id;
          
          CREATE TEMP TABLE tt_districts ON COMMIT DROP AS
          SELECT unnest(string_to_array(districts, ',')) :: INTEGER id;
          
           RETURN QUERY
           SELECT 
            o.id as id_organization,
            o.short_name,
            o.id_fias_address,
            fa.id_area,
            a.name as area_name,
            fa.id_district,
            d.name AS dist_name, 
            sum(case when v.channel = 2 then 1 else 0 END)::INTEGER as MOSRU_APPOINTMENT_created,
            sum(case when v.channel = 2 and v.status = 'T' then 1 else 0 end)::INTEGER as MOSRU_APPOINTMENT_transfered,
            sum(case when v.channel = 2 and v.status = 'C' then 1 else 0 end)::INTEGER as MOSRU_APPOINTMENT_cancelled,
            sum(case when v.channel = 2 and v.status = 'F' then 1 else 0 end)::INTEGER AS MOSRU_APPOINTMENT_finished, 
          
            sum(case when v.channel = 3 then 1 else 0 end)::INTEGER as PHONE_APPOINTMENT_created,
            sum(case when v.channel = 3 and v.status = 'T' then 1 else 0 end)::INTEGER as PHONE_APPOINTMENT_transfered,
            sum(case when v.channel = 3 and v.status = 'C' then 1 else 0 end)::INTEGER as PHONE_APPOINTMENT_cancelled,
            sum(case when v.channel = 3 and v.status = 'F' then 1 else 0 end)::INTEGER as PHONE_APPOINTMENT_finished,
          
            sum(case when v.channel = 4 then 1 else 0 end)::INTEGER as LIVE_QUEUE_created,
            sum(case when v.channel = 4 and v.status = 'T' then 1 else 0 end)::INTEGER as LIVE_QUEUE_transfered,
            sum(case when v.channel = 4 and v.status = 'C' then 1 else 0 end)::INTEGER as LIVE_QUEUE_cancelled,
            sum(case when v.channel = 4 and v.status = 'F' then 1 else 0 end)::INTEGER as LIVE_QUEUE_finished,
            
            sum(case when v.channel = 1 then 1 else 0 end)::INTEGER as WORKDAY_created,
            sum(case when v.channel = 1 and v.status = 'T' then 1 else 0 end)::INTEGER as WORKDAY_transfered,
            sum(case when v.channel = 1 and v.status = 'C' then 1 else 0 end)::INTEGER as WORKDAY_cancelled,
            sum(case when v.channel = 1 and v.status = 'F' then 1 else 0 end)::INTEGER as WORKDAY_finished,
            
            sum(case when v.channel = 9 then 1 else 0 end)::INTEGER as CALL_TO_HOME_created,
            sum(case when v.channel = 9 and v.status = 'T' then 1 else 0 end)::INTEGER as CALL_TO_HOME_transfered,
            sum(case when v.channel = 9 and v.status = 'C' then 1 else 0 end)::INTEGER as CALL_TO_HOME_cancelled,
            sum(case when v.channel = 9 and v.status = 'F' then 1 else 0 end)::INTEGER as CALL_TO_HOME_finished,
          
            sum(case when v.channel = 14 then 1 else 0 end)::INTEGER as MOSRU_CALL_TO_HOME_created,
            sum(case when v.channel = 14 and v.status = 'T' then 1 else 0 end)::INTEGER as MOSRU_CALL_TO_HOME_transfered,
            sum(case when v.channel = 14 and v.status = 'C' then 1 else 0 end)::INTEGER as MOSRU_CALL_TO_HOME_cancelled,
            sum(case when v.channel = 14 and v.status = 'F' then 1 else 0 end)::INTEGER as MOSRU_CALL_TO_HOME_finished,
          
            sum(case when v.channel = 10 then 1 else 0 end)::INTEGER as AMBULANCE_created,
            sum(case when v.channel = 10 and v.status = 'T' then 1 else 0 end)::INTEGER as AMBULANCE_transfered,
            sum(case when v.channel = 10 and v.status = 'C' then 1 else 0 end)::INTEGER as AMBULANCE_cancelled,
            sum(case when v.channel = 10 and v.status = 'F' then 1 else 0 end)::INTEGER as AMBULANCE_finished,
            
            sum(case when v.channel = 11 then 1 else 0 end)::INTEGER as VACCINATION_STATION_created,
            sum(case when v.channel = 11 and v.status = 'T' then 1 else 0 end)::INTEGER as VACCINATION_STATION_transfered,
            sum(case when v.channel = 11 and v.status = 'C' then 1 else 0 end)::INTEGER as VACCINATION_STATION_cancelled,
            sum(case when v.channel = 11 and v.status = 'F' then 1 else 0 end)::INTEGER as VACCINATION_STATION_finished,
           
            sum(case when v.channel = 13 then 1 else 0 end)::INTEGER as SHELTER_created,
            sum(case when v.channel = 13 and v.status = 'T' then 1 else 0 end)::INTEGER as SHELTER_transfered,
            sum(case when v.channel = 13 and v.status = 'C' then 1 else 0 end)::INTEGER as SHELTER_cancelled,
            sum(case when v.channel = 13 and v.status = 'F' then 1 else 0 end)::INTEGER as SHELTER_finished,  
             
            sum(case when v.channel = 12 then 1 else 0 end)::INTEGER as DETOUR_created,
            sum(case when v.channel = 12 and v.status = 'T' then 1 else 0 end)::INTEGER as DETOUR_transfered,
            sum(case when v.channel = 12 and v.status = 'C' then 1 else 0 end)::INTEGER as DETOUR_cancelled,
            sum(case when v.channel = 12 and v.status = 'F' then 1 else 0 end)::INTEGER AS DETOUR_finished
            FROM organizations o
            LEFT JOIN fias_addresses fa 
              ON fa.id = o.id_fias_address
            LEFT JOIN  areas a
              ON a.id = fa.id_area
            left JOIN districts d
              on d.id = fa.id_district
            LEFT JOIN visits v 
              ON o.id = v.id_organization
            WHERE coalesce(v.fact_start_dttm, v.start_dttm, v.created_at)::DATE BETWEEN startdate  AND enddate
             AND EXISTS (
                      SELECT 1 
                      FROM tt_organizations fo
                      WHERE fo.id = v.id_organization
                  
                      UNION ALL
                      SELECT  1
                      WHERE organizations IS NULL
             )
            AND EXISTS (
                      SELECT  1
                      FROM tt_areas ta
                      WHERE fa.id_area = ta.id
                  
                      UNION ALL
                      SELECT  1
                      WHERE areas IS NULL
            )
            AND EXISTS (
                      SELECT  1
                      FROM tt_districts fd
                      WHERE fd.id = fa.id_district
                  
                      UNION ALL
                      SELECT 1
                      WHERE districts  IS NULL
            )
            GROUP BY 
              o.id,
              fa.id_area,
              area_name,
              fa.id_district,
              dist_name,
              o.short_name,
              o.id_fias_address
          ORDER BY area_name,
                   dist_name,
                   o.short_name;
                 
          END;
           \$plpgsql\$;
        ");

        #$this->execute("CREATE INDEX IF NOT EXISTS etp_message_v2_visitid ON etp.message_v2 (visit_id) INCLUDE (service_number);");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("DROP FUNCTION get_FullServicesReport");
        $this->execute("DROP FUNCTION get_FullServicesVol2DetailReport");
        $this->execute("DROP FUNCTION get_FullVisitsReport");
        $this->execute("DROP FUNCTION get_fullvisitsvol2report");
    }

}
