<?php

use yii\db\Migration;

/**
 * Class m221212_143000_create_reports_functions
 */
class m221212_143000_create_reports_functions extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
      // $this->execute("DROP FUNCTION get_fullservicesreport");
      // $this->execute("DROP FUNCTION get_fullservicesvol2detailreport");
      // $this->execute("DROP FUNCTION get_fullvisitsreport");
      // $this->execute("DROP FUNCTION get_fullvisitsvol2report");
      // $this->execute("DROP FUNCTION get_vetservicesreport");

      $this->execute("
        CREATE OR REPLACE FUNCTION get_fullservicesreport(startdate DATE, enddate DATE, areas TEXT, districts TEXT, VisitTypes TEXT, statuses TEXT, organizations TEXT, specialists text, services TEXT) 
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

        BEGIN

          CREATE TEMP TABLE tt_organizations ON COMMIT DROP AS
          SELECT
            unnest(string_to_array(organizations, ',')) :: INTEGER id;

          CREATE TEMP TABLE tt_areas ON COMMIT DROP AS
          SELECT
            unnest(string_to_array(areas, ',')) :: INTEGER id;

          CREATE TEMP TABLE tt_districts ON COMMIT DROP AS
          SELECT
            unnest(string_to_array(districts, ',')) :: INTEGER id;

          CREATE TEMP TABLE tt_statuses ON COMMIT DROP AS
          SELECT
            unnest(string_to_array(statuses, ',')) :: VARCHAR(255) status;

          CREATE TEMP TABLE tt_visittypes ON COMMIT DROP AS
          SELECT
            unnest(string_to_array(VisitTypes, ',')) :: VARCHAR(32) TypeName;

          CREATE TEMP TABLE tt_specialists ON COMMIT DROP AS
          SELECT
            unnest(string_to_array(specialists, ',')) :: INTEGER UserId;

          CREATE TEMP TABLE tt_services ON COMMIT DROP AS
          SELECT
            unnest(string_to_array(services, ',')) :: INTEGER id;



          RETURN QUERY
          SELECT
            ROW_NUMBER() OVER (
            ORDER BY substr(COALESCE(v.fact_start_dttm, v.start_dttm, v.created_at) :: TEXT, 1, 7) ASC) :: INTEGER AS id,
            substr(COALESCE(v.fact_start_dttm, v.start_dttm, v.created_at) :: TEXT, 1, 7) AS date,
            gs.name AS service_name,
            SUM(CASE WHEN st.type = 'LIVE_QUEUE' THEN vgs0.total ELSE 0 END) :: INTEGER AS total_lq,
            SUM(CASE WHEN st.type = 'PHONE_APPOINTMENT' THEN vgs0.total ELSE 0 END) :: INTEGER AS total_phone,
            SUM(CASE WHEN st.type = 'WORKDAY' THEN vgs0.total ELSE 0 END) :: INTEGER AS total_workday,
            SUM(CASE WHEN st.type = 'MOSRU_APPOINTMENT' AND (NOT (m.service_number ILIKE '%-9000005-%' OR m.service_number ILIKE '0002%') OR m.service_number ISNULL) THEN vgs0.total ELSE 0 END) :: INTEGER AS total_mosru,
            SUM(CASE WHEN st.type = 'MOSRU_APPOINTMENT' AND (m.service_number ILIKE '%-9000005-%' OR m.service_number ILIKE '0002%') THEN vgs0.total ELSE 0 END) :: INTEGER AS total_mpgu,
            SUM(CASE WHEN st.type = 'AMBULANCE' THEN vgs0.total ELSE 0 END) :: INTEGER AS total_ambulance,
            SUM(CASE WHEN st.type = 'MOSRU_CALL_TO_HOME' THEN vgs0.total ELSE 0 END) :: INTEGER AS total_home_mosru,
            SUM(CASE WHEN st.type = 'VACCINATION_STATION' THEN vgs0.total ELSE 0 END) :: INTEGER AS total_vacc_station,
            SUM(CASE WHEN st.type = 'DETOUR' THEN vgs0.total ELSE 0 END) :: INTEGER AS total_detour,
            SUM(CASE WHEN st.type = 'SHELTER' THEN vgs0.total ELSE 0 END) :: INTEGER AS total_shelter
          FROM visits v
            INNER JOIN (SELECT
                rf.id_visit,
                rf.id_service,
                rf.id_species,
                SUM(rf.count) AS total
              FROM (SELECT
                  vgs.id_visit,
                  vgs.id_service,
                  p.id_species,
                  count(p.id) AS count
                FROM public.visits_gov_services vgs
                  LEFT JOIN visit_pets vp
                    ON vp.id_visit = vgs.id_visit
                  LEFT JOIN pets p
                    ON p.id = vp.id_pet
                WHERE vgs.id_pet IS NULL
                GROUP BY vgs.id_visit,
                        vgs.id_service,
                        p.id_species)
              rf
              WHERE rf.id_species IS NOT NULL
              GROUP BY rf.id_visit,
                      rf.id_service,
                      rf.id_species)
            vgs0
              ON vgs0.id_visit = v.id
            LEFT JOIN gov_services gs
              ON vgs0.id_service = gs.id
            LEFT JOIN etp.message_v2 m
              ON m.visit_id = v.id
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
          WHERE COALESCE(v.fact_start_dttm, v.start_dttm) :: date BETWEEN startdate AND enddate
          AND EXISTS (SELECT
              1
            FROM tt_visittypes tvt
            WHERE v.type = tvt.TypeName

            UNION ALL
            SELECT
              1
            WHERE VisitTypes IS NULL)


          AND EXISTS (SELECT
              1
            FROM tt_organizations fo
            WHERE fo.id = v.id_organization

            UNION ALL
            SELECT
              1
            WHERE organizations IS NULL)
          AND EXISTS (SELECT
              1
            FROM tt_areas ta
            WHERE fa.id_area = ta.id

            UNION ALL
            SELECT
              1
            WHERE areas IS NULL)
          AND EXISTS (SELECT
              1
            FROM tt_districts fd
            WHERE fd.id = fa.id_district

            UNION ALL
            SELECT
              1
            WHERE districts IS NULL)
          AND EXISTS (SELECT
              1
            FROM tt_specialists fs
            WHERE fs.UserId = sp.id_user

            UNION ALL
            SELECT
              1
            WHERE specialists IS NULL)
          AND EXISTS (SELECT
              1
            FROM tt_services fss
            WHERE fss.id = vgs0.id_service

            UNION ALL
            SELECT
              1
            WHERE services IS NULL)
          AND EXISTS (SELECT
              1
            FROM tt_statuses fsp
            WHERE fsp.status = v.status

            UNION ALL
            SELECT
              1
            WHERE statuses IS NULL)

          GROUP BY date,
                  gs.name
          ORDER BY date,
          gs.name;

        END;
        
         \$plpgsql\$;
      ");

      $this->execute("
        CREATE OR REPLACE FUNCTION get_fullservicesvol2detailreport(startdate DATE, enddate DATE, areas TEXT, districts TEXT, VisitTypes TEXT, channels TEXT, organizations TEXT, species TEXT,specialists text, services TEXT) 
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
          
        BEGIN

          CREATE TEMP TABLE tt_organizations ON COMMIT DROP AS
          SELECT
            unnest(string_to_array(organizations, ',')) :: INTEGER id;

          CREATE TEMP TABLE tt_areas ON COMMIT DROP AS
          SELECT
            unnest(string_to_array(areas, ',')) :: INTEGER id;

          CREATE TEMP TABLE tt_districts ON COMMIT DROP AS
          SELECT
            unnest(string_to_array(districts, ',')) :: INTEGER id;

          CREATE TEMP TABLE tt_species ON COMMIT DROP AS
          SELECT
            unnest(string_to_array(species, ',')) :: VARCHAR(20) SpecName;

          CREATE TEMP TABLE tt_visittypes ON COMMIT DROP AS
          SELECT
            unnest(string_to_array(VisitTypes, ',')) :: VARCHAR(32) TypeName;

          CREATE TEMP TABLE tt_specialists ON COMMIT DROP AS
          SELECT
            unnest(string_to_array(specialists, ',')) :: INTEGER UserId;

          CREATE TEMP TABLE tt_services ON COMMIT DROP AS
          SELECT
            unnest(string_to_array(services, ',')) :: INTEGER id;

          CREATE TEMP TABLE tt_channels ON COMMIT DROP AS
          SELECT
            unnest(string_to_array(channels, ',')) :: INTEGER id;

          UPDATE tt_species
          SET SpecName = 'HORSE'
          WHERE SpecName = 'OTHER';

          RETURN QUERY
          SELECT
            ROW_NUMBER() OVER (
            ORDER BY substr(COALESCE(v.fact_start_dttm, v.start_dttm, v.created_at) :: TEXT, 1, 7) ASC) :: INTEGER AS id,
            substr(COALESCE(v.fact_start_dttm, v.start_dttm, v.created_at) :: TEXT, 1, 7) AS date,
            a.name AS area_name,
            o.short_name,
            (CASE WHEN v.channel = 1 THEN 'Направление' ELSE st.description END) :: TEXT AS channel,
            gs.name AS service_name,
            SUM(CASE WHEN v.status IS NOT NULL THEN vgs0.total ELSE 0 END) :: INTEGER AS total_services,
            SUM(CASE WHEN v.status = 'F' THEN vgs0.total ELSE 0 END) :: INTEGER AS total_f,
            SUM(CASE WHEN v.status = 'A' THEN vgs0.total ELSE 0 END) :: INTEGER AS total_a,
            SUM(CASE WHEN v.status = 'N' THEN vgs0.total ELSE 0 END) :: INTEGER AS total_n,
            SUM(CASE WHEN v.status = 'W' THEN vgs0.total ELSE 0 END) :: INTEGER AS total_w,
            SUM(CASE WHEN v.status = 'T' THEN vgs0.total ELSE 0 END) :: INTEGER AS total_t,
            SUM(CASE WHEN v.status = 'C' THEN vgs0.total ELSE 0 END) :: INTEGER AS total_c,
            SUM(CASE WHEN v.status = 'D' THEN vgs0.total ELSE 0 END) :: INTEGER AS total_d
          FROM visits v
            INNER JOIN (SELECT
                rf.id_visit,
                rf.id_service,
                rf.id_species,
                SUM(rf.count) AS total
              FROM (SELECT
                  vgs.id_visit,
                  vgs.id_service,
                  p.id_species,
                  count(p.id) AS count
                FROM public.visits_gov_services vgs
                  LEFT JOIN visit_pets vp
                    ON vp.id_visit = vgs.id_visit
                  LEFT JOIN pets p
                    ON p.id = vp.id_pet
                WHERE vgs.id_pet IS NULL
                GROUP BY vgs.id_visit,
                        vgs.id_service,
                        p.id_species)
              rf
              WHERE rf.id_species IS NOT NULL
              GROUP BY rf.id_visit,
                      rf.id_service,
                      rf.id_species)
            vgs0
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
          WHERE COALESCE(v.fact_start_dttm, v.start_dttm) :: date BETWEEN startdate AND enddate
          AND EXISTS (SELECT
              1
            FROM tt_visittypes tvt
            WHERE v.type = tvt.TypeName

            UNION ALL
            SELECT
              1
            WHERE VisitTypes IS NULL)
          AND EXISTS (SELECT
              1
            FROM tt_channels tc
            WHERE v.channel = tc.id

            UNION ALL
            SELECT
              1
            WHERE channels IS NULL)

          AND EXISTS (SELECT
              1
            FROM tt_organizations fo
            WHERE fo.id = v.id_organization

            UNION ALL
            SELECT
              1
            WHERE organizations IS NULL)
          AND EXISTS (SELECT
              1
            FROM tt_areas ta
            WHERE fa.id_area = ta.id

            UNION ALL
            SELECT
              1
            WHERE areas IS NULL)
          AND EXISTS (SELECT
              1
            FROM tt_districts fd
            WHERE fd.id = fa.id_district

            UNION ALL
            SELECT
              1
            WHERE districts IS NULL)
          AND EXISTS (SELECT
              1
            FROM tt_specialists fs
            WHERE fs.UserId = sp.id_user

            UNION ALL
            SELECT
              1
            WHERE specialists IS NULL)
          AND EXISTS (SELECT
              1
            FROM tt_services fss
            WHERE fss.id = vgs0.id_service

            UNION ALL
            SELECT
              1
            WHERE services IS NULL)
          AND EXISTS (SELECT
              1
            FROM tt_species fsp
            WHERE fsp.SpecName = COALESCE(s.tech_name, 'HORSE')

            UNION ALL
            SELECT
              1
            WHERE species IS NULL)

          GROUP BY date,
                  gs.name,
                  a.name,
                  o.short_name,
                  st.description,
                  v.channel
          ORDER BY date,
          a.name,
          o.short_name,
          st.description,
          gs.name;

        END;

         \$plpgsql\$;
      ");

      $this->execute("
        CREATE OR REPLACE FUNCTION get_fullvisitsreport (startdate DATE, enddate DATE, organizations TEXT,areas TEXT, districts TEXT)
        RETURNS TABLE (
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
          SELECT
            unnest(string_to_array(organizations, ',')) :: INTEGER id;

          CREATE TEMP TABLE tt_areas ON COMMIT DROP AS
          SELECT
            unnest(string_to_array(areas, ',')) :: INTEGER id;

          CREATE TEMP TABLE tt_districts ON COMMIT DROP AS
          SELECT
            unnest(string_to_array(districts, ',')) :: INTEGER id;

          RETURN QUERY
          SELECT
            o.id AS id_organization,
            o.short_name,
            o.id_fias_address,
            fa.id_area,
            a.name AS area_name,
            fa.id_district,
            d.name AS dist_name,
            SUM(CASE WHEN v.channel = 2 THEN 1 ELSE 0 END) :: INTEGER AS MOSRU_APPOINTMENT_created,
            SUM(CASE WHEN v.channel = 2 AND v.status = 'T' THEN 1 ELSE 0 END) :: INTEGER AS MOSRU_APPOINTMENT_transfered,
            SUM(CASE WHEN v.channel = 2 AND v.status = 'A' THEN 1 ELSE 0 END) :: INTEGER AS MOSRU_APPOINTMENT_cancelled,
            SUM(CASE WHEN v.channel = 2 AND v.status = 'F' THEN 1 ELSE 0 END) :: INTEGER AS MOSRU_APPOINTMENT_finished,

            SUM(CASE WHEN v.channel = 3 THEN 1 ELSE 0 END) :: INTEGER AS PHONE_APPOINTMENT_created,
            SUM(CASE WHEN v.channel = 3 AND v.status = 'T' THEN 1 ELSE 0 END) :: INTEGER AS PHONE_APPOINTMENT_transfered,
            SUM(CASE WHEN v.channel = 3 AND v.status = 'A' THEN 1 ELSE 0 END) :: INTEGER AS PHONE_APPOINTMENT_cancelled,
            SUM(CASE WHEN v.channel = 3 AND v.status = 'F' THEN 1 ELSE 0 END) :: INTEGER AS PHONE_APPOINTMENT_finished,

            SUM(CASE WHEN v.channel = 4 THEN 1 ELSE 0 END) :: INTEGER AS LIVE_QUEUE_created,
            SUM(CASE WHEN v.channel = 4 AND v.status = 'T' THEN 1 ELSE 0 END) :: INTEGER AS LIVE_QUEUE_transfered,
            SUM(CASE WHEN v.channel = 4 AND v.status = 'A' THEN 1 ELSE 0 END) :: INTEGER AS LIVE_QUEUE_cancelled,
            SUM(CASE WHEN v.channel = 4 AND v.status = 'F' THEN 1 ELSE 0 END) :: INTEGER AS LIVE_QUEUE_finished,

            SUM(CASE WHEN v.channel = 1 THEN 1 ELSE 0 END) :: INTEGER AS WORKDAY_created,
            SUM(CASE WHEN v.channel = 1 AND v.status = 'T' THEN 1 ELSE 0 END) :: INTEGER AS WORKDAY_transfered,
            SUM(CASE WHEN v.channel = 1 AND v.status = 'A' THEN 1 ELSE 0 END) :: INTEGER AS WORKDAY_cancelled,
            SUM(CASE WHEN v.channel = 1 AND v.status = 'F' THEN 1 ELSE 0 END) :: INTEGER AS WORKDAY_finished,

            SUM(CASE WHEN v.channel = 9 THEN 1 ELSE 0 END) :: INTEGER AS CALL_TO_HOME_created,
            SUM(CASE WHEN v.channel = 9 AND v.status = 'T' THEN 1 ELSE 0 END) :: INTEGER AS CALL_TO_HOME_transfered,
            SUM(CASE WHEN v.channel = 9 AND v.status = 'A' THEN 1 ELSE 0 END) :: INTEGER AS CALL_TO_HOME_cancelled,
            SUM(CASE WHEN v.channel = 9 AND v.status = 'F' THEN 1 ELSE 0 END) :: INTEGER AS CALL_TO_HOME_finished,

            SUM(CASE WHEN v.channel = 14 THEN 1 ELSE 0 END) :: INTEGER AS MOSRU_CALL_TO_HOME_created,
            SUM(CASE WHEN v.channel = 14 AND v.status = 'T' THEN 1 ELSE 0 END) :: INTEGER AS MOSRU_CALL_TO_HOME_transfered,
            SUM(CASE WHEN v.channel = 14 AND v.status = 'A' THEN 1 ELSE 0 END) :: INTEGER AS MOSRU_CALL_TO_HOME_cancelled,
            SUM(CASE WHEN v.channel = 14 AND v.status = 'F' THEN 1 ELSE 0 END) :: INTEGER AS MOSRU_CALL_TO_HOME_finished,

            SUM(CASE WHEN v.channel = 10 THEN 1 ELSE 0 END) :: INTEGER AS AMBULANCE_created,
            SUM(CASE WHEN v.channel = 10 AND v.status = 'T' THEN 1 ELSE 0 END) :: INTEGER AS AMBULANCE_transfered,
            SUM(CASE WHEN v.channel = 10 AND v.status = 'A' THEN 1 ELSE 0 END) :: INTEGER AS AMBULANCE_cancelled,
            SUM(CASE WHEN v.channel = 10 AND v.status = 'F' THEN 1 ELSE 0 END) :: INTEGER AS AMBULANCE_finished,

            SUM(CASE WHEN v.channel = 11 THEN 1 ELSE 0 END) :: INTEGER AS VACCINATION_STATION_created,
            SUM(CASE WHEN v.channel = 11 AND v.status = 'T' THEN 1 ELSE 0 END) :: INTEGER AS VACCINATION_STATION_transfered,
            SUM(CASE WHEN v.channel = 11 AND v.status = 'A' THEN 1 ELSE 0 END) :: INTEGER AS VACCINATION_STATION_cancelled,
            SUM(CASE WHEN v.channel = 11 AND v.status = 'F' THEN 1 ELSE 0 END) :: INTEGER AS VACCINATION_STATION_finished,

            SUM(CASE WHEN v.channel = 13 THEN 1 ELSE 0 END) :: INTEGER AS SHELTER_created,
            SUM(CASE WHEN v.channel = 13 AND v.status = 'T' THEN 1 ELSE 0 END) :: INTEGER AS SHELTER_transfered,
            SUM(CASE WHEN v.channel = 13 AND v.status = 'A' THEN 1 ELSE 0 END) :: INTEGER AS SHELTER_cancelled,
            SUM(CASE WHEN v.channel = 13 AND v.status = 'F' THEN 1 ELSE 0 END) :: INTEGER AS SHELTER_finished,

            SUM(CASE WHEN v.channel = 12 THEN 1 ELSE 0 END) :: INTEGER AS DETOUR_created,
            SUM(CASE WHEN v.channel = 12 AND v.status = 'T' THEN 1 ELSE 0 END) :: INTEGER AS DETOUR_transfered,
            SUM(CASE WHEN v.channel = 12 AND v.status = 'A' THEN 1 ELSE 0 END) :: INTEGER AS DETOUR_cancelled,
            SUM(CASE WHEN v.channel = 12 AND v.status = 'F' THEN 1 ELSE 0 END) :: INTEGER AS DETOUR_finished
          FROM organizations o
            LEFT JOIN fias_addresses fa
              ON fa.id = o.id_fias_address
            LEFT JOIN areas a
              ON a.id = fa.id_area
            LEFT JOIN districts d
              ON d.id = fa.id_district
            LEFT JOIN visits v
              ON o.id = v.id_organization
          WHERE COALESCE(v.fact_start_dttm, v.start_dttm) :: DATE BETWEEN startdate AND enddate
          AND EXISTS (SELECT
              1
            FROM tt_organizations fo
            WHERE fo.id = v.id_organization

            UNION ALL
            SELECT
              1
            WHERE organizations IS NULL)
          AND EXISTS (SELECT
              1
            FROM tt_areas ta
            WHERE fa.id_area = ta.id

            UNION ALL
            SELECT
              1
            WHERE areas IS NULL)
          AND EXISTS (SELECT
              1
            FROM tt_districts fd
            WHERE fd.id = fa.id_district

            UNION ALL
            SELECT
              1
            WHERE districts IS NULL)
          GROUP BY o.id,
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

        DECLARE _mosruChannelId INTEGER;

        BEGIN

          _mosruChannelId := (SELECT s.id FROM shift_type s WHERE s.TYPE = 'MOSRU_APPOINTMENT');

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

          CREATE TEMP TABLE tt_total_pets ON COMMIT DROP AS
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
            COUNT(1) :: INTEGER AS total_visits,
            SUM(CASE WHEN visits.status = 'F' THEN 1 ELSE 0 END) :: INTEGER AS total_finished
          FROM visits 
            LEFT JOIN organizations
              ON organizations.id = visits.id_organization
            LEFT JOIN fias_addresses
              ON fias_addresses.id = organizations.id_fias_address
            LEFT JOIN areas
              ON areas.id = fias_addresses.id_area
            LEFT JOIN districts
              ON districts.id = fias_addresses.id_district
            LEFT JOIN visit_pets vp 
              ON vp.id_visit = visits.id
            LEFT JOIN pets
              ON pets.id = vp.id_pet
            LEFT JOIN species
              ON species.id = pets.id_species
            LEFT JOIN etp.status_log
              ON etp.status_log.visit_id = visits.id
              AND etp.status_log.etp_status IN ('1053', '8021')
          WHERE COALESCE(visits.fact_start_dttm, visits.start_dttm) :: DATE BETWEEN startDate AND endDate
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
          
          
          CREATE TEMP TABLE tt_visits ON COMMIT DROP AS 
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
          WHERE COALESCE(visits.fact_start_dttm, visits.start_dttm) :: DATE BETWEEN startDate AND endDate
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


          RETURN QUERY
            SELECT  
              COALESCE(tv.id,tp.id) id,
              COALESCE(tv.id_area,tp.id_area) id_area,
              COALESCE(tv.area_name,tp.area_name) AS area_name,
              COALESCE(tv.id_district,tp.id_district) id_district ,
              COALESCE(tv.dist_name,tp.dist_name) AS dist_name,
              COALESCE(tv.short_name,tp.short_name) short_name,
              COALESCE(tv.spec_name,tp.spec_name) spec_name,
              COALESCE(tp.total_visits,0) total_visits,
              COALESCE(tp.total_finished,0) total_finished,
              COALESCE(tv.cancelled_by_clinic,0) cancelled_by_clinic,
              COALESCE(tv.cancelled_by_owner,0) cancelled_by_owner,
              COALESCE(tv.transferred_not_mosru,0) transferred_not_mosru,
              COALESCE(tv.transferred_by_clinic_mosru,0) transferred_by_clinic_mosru,
              COALESCE(tv.transferred_by_owner_mosru,0) transferred_by_owner_mosru 
          FROM tt_visits  tv        
          FULL JOIN tt_total_pets tp
              ON tp.id = tv.id
          AND  tp.id_area = tv.id_area
          AND  tp.id_district = tv.id_district 
          AND tp.spec_name = tv.spec_name;
        END;
          
          \$plpgsql\$;
        ");


      $this->execute("
        CREATE OR REPLACE FUNCTION get_vetservicesreport(startdate date, enddate date, organizations text, visit_types text, services text, service_types text, specialists text, channels text) 
        RETURNS TABLE(id_service integer, type_id integer, type_name text, name text, id_organization integer, total_paid integer, total_free integer, total_blind integer, total_veteran integer, total_disabled integer, total_orphan integer, total_large_family integer, total_veteran_of_labour integer, total_rabies integer, total_f1 integer, total_f4 integer, total_ts integer, price numeric, total_amount integer, short_name text, area_name text)
        LANGUAGE PLPGSQL
        AS \$plpgsql\$ 
          
        BEGIN  
 
          CREATE TEMP TABLE tt_organizations ON COMMIT DROP AS
          SELECT unnest(string_to_array(organizations, ',')) :: INTEGER id;

          CREATE TEMP TABLE tt_visittypes ON COMMIT DROP AS
          SELECT unnest(string_to_array(visit_types, ',')) :: VARCHAR(32) TypeName;

          CREATE TEMP TABLE tt_specialists ON COMMIT DROP AS
          SELECT unnest(string_to_array(specialists, ',')) :: INTEGER UserId;

          CREATE TEMP TABLE tt_services ON COMMIT DROP AS
          SELECT unnest(string_to_array(services, ',')) :: INTEGER id;

          CREATE TEMP TABLE tt_channels ON COMMIT DROP AS
          SELECT unnest(string_to_array(channels, ',')) :: INTEGER id;

          CREATE TEMP TABLE tt_service_types ON COMMIT DROP AS
          SELECT unnest(string_to_array(service_types, ',')) :: INTEGER id;
          
          RETURN QUERY 
          SELECT 
              t.id_service::INTEGER,
              t.type_id::INTEGER,
              t.type_name::TEXT,
              t.name::TEXT,
              t.id_organization::INTEGER,
              sum(t.total_paid)::INTEGER as total_paid,
              sum(t.total_free)::INTEGER as total_free,
              sum(t.total_blind)::INTEGER as total_blind,
              sum(t.total_veteran)::INTEGER as total_veteran,
              sum(t.total_disabled)::INTEGER as total_disabled,
              sum(t.total_orphan)::INTEGER as total_orphan,
              sum(t.total_large_family)::INTEGER as total_large_family,
              sum(t.total_veteran_of_labour)::INTEGER as total_veteran_of_labour,
              sum(t.total_rabies)::INTEGER as total_rabies,
              sum(t.total_f1)::INTEGER as total_f1,
              sum(t.total_f4)::INTEGER as total_f4,
              sum(t.total_ts)::INTEGER as total_ts,
              t.price::NUMERIC,
              sum(t.total_amount)::INTEGER as total_amount,
              t.short_name::TEXT,
              t.area_name::TEXT
          FROM (
                SELECT 
                  vgs.id_service,
                  st.id as type_id,
                  st.name as type_name,
                  gs.name,
                  v.id_organization,
                  o.short_name,
                  coalesce(a.name, 'Округ не указан') as area_name,
                  vgs.price,
                  sum(case when vgs.price_with_discount != 0 then coalesce(vgs.count, 1) else 0 end) as total_paid,
                  sum(vgs.price_with_discount * coalesce(vgs.count, 1)) as total_amount,
                  sum(case when (vgs.apply_discount = true and vgs.price_with_discount = 0.00 and vgs.price != vgs.price_with_discount) or vgs.price = 0.00 then coalesce(vgs.count, 1) else 0 end) as total_free,
                  sum(coalesce(rab.total_rabies, 0)) as total_rabies,
                  sum(case WHEN v.is_blind = true AND vgs.apply_discount = True then coalesce(vgs.count, 1) else 0 end) as total_blind,
                  sum(case when v.is_veteran = true AND vgs.apply_discount = True then coalesce(vgs.count, 1) else 0 end) as total_veteran,
                  sum(case when v.is_disabled = TRUE AND vgs.apply_discount = True then coalesce(vgs.count, 1) else 0 end) as total_disabled,
                  sum(case when v.is_orphan = true AND vgs.apply_discount = True then coalesce(vgs.count, 1) else 0 end) as total_orphan,
                  sum(case when v.is_large_family = TRUE AND vgs.apply_discount = True  then coalesce(vgs.count, 1) else 0 end) as total_large_family,
                  sum(case WHEN v.is_veteran_of_labour = TRUE AND vgs.apply_discount = True then coalesce(vgs.count, 1) else 0 end) as total_veteran_of_labour,
                  sum(coalesce(vsd.total_f1, 0)) as total_f1,
                  sum(coalesce(vsd.total_f4, 0)) as total_f4,
                  sum(coalesce(vsd.total_ts, 0)) AS total_ts
                FROM  visits_gov_services vgs    
                  LEFT JOIN gov_services gs
                    ON vgs.id_service = gs.id
                  LEFT JOIN service_types st 
                    ON gs.id_service_type = st.id
                  LEFT JOIN visits v 
                    ON vgs.id_visit = v.id
                  left JOIN organizations o 
                    ON v.id_organization = o.id
                  LEFT JOIN visits_specialists vs
                    ON vs.id_visit = v.id
                  left JOIN users u
                    ON u.id = vs.id_specialist
                  LEFT JOIN fias_addresses fa 
                    ON fa.id = o.id_fias_address
                  LEFT JOIN areas a 
                    ON a.id = fa.id_area
                  LEFT JOIN (
                            SELECT
                                vgs.id,
                                count(vt.id) AS total_rabies
                              FROM  visits_gov_services vgs 
                              INNER JOIN visits v
                                  ON vgs.id_visit = v.id
                              INNER JOIN visit_pets vtp
                                  ON vtp.id_visit = v.id
                              left JOIN visit_service_tmc_pet vsp
                                  ON vsp.id_pet = vtp.id_pet 
                                AND vsp.id_visits_gov_service = vgs.id
                              left JOIN visit_service_tmc vt
                                  ON vsp.id_visit_service_tmc = vt.id 
                                  and vt.type_tmc = 'vaccine'
                              left JOIN tmc.tmc_to_diseases ttd
                                  ON ttd.id_tmc = vt.id_tmc
                              left JOIN tmc.tmc tmc
                                  ON tmc.id = vt.id_tmc
                              left JOIN diseases d
                                  ON ttd.id_disease = d.id
                              WHERE d.name ilike '%бешенство%' 
                                AND tmc.name ilike '%рабикан%'
                              GROUP BY  vgs.id
                  )rab 
                    ON rab.id = vgs.id
                  left JOIN (
                          select
                              vgs.id,
                              count(distinct CASE WHEN d.name = 'ветеринарная справка' THEN vgs.id END) as total_f4,
                              count(distinct CASE WHEN d.name = 'ветеринарный сертификат' THEN vgs.id END) as total_ts,
                              count(distinct CASE WHEN d.name = 'ветеринарное свидетельство' THEN vgs.id END) as total_f1
                          FROM visits_gov_services vgs
                          LEFT JOIN visit_service_param_values vspv 
                            ON vgs.id = vspv.id_visitservice
                          INNER JOIN dictionaries d
                            ON vspv.dict_value = d.id
                          WHERE d.type = 'vsdtypes'
                          GROUP BY vgs.id   
                  ) vsd 
                    ON vsd.id = vgs.id
                  LEFT JOIN visit_price vp 
                    ON v.id = vp.id_visit
                  LEFT JOIN discount d 
                    ON d.id = vp.id_discount
                WHERE coalesce(v.fact_start_dttm::date, lower(v.time_range)::date) BETWEEN startdate AND  enddate
                  AND v.status =  'F'
                  AND  EXISTS (
                          SELECT 1
                          FROM tt_visittypes tvt
                          WHERE v.type = tvt.TypeName
                          
                          UNION ALL
                          SELECT 1
                          WHERE visit_types is NULL 
                )
                  AND EXISTS (
                        SELECT  1
                        FROM tt_specialists fs
                        WHERE fs.UserId = u.id
                    
                        UNION ALL
                        SELECT 1
                          WHERE specialists  IS NULL 
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
                        FROM tt_services fss
                        WHERE fss.id = vgs.id_service
                    
                        UNION ALL
                        SELECT 1
                        WHERE services  IS NULL 
                      )  
                And EXISTS ( 
                        SELECT 1
                        FROM tt_service_types fsp 
                        WHERE fsp.id = st.id
            
                        UNION ALL
                        SELECT 1
                        WHERE service_types is NULL 
                )
                AND EXISTS( 
                        SELECT 1
                        FROM tt_channels tc
                        WHERE v.channel =  tc.id
                
                        UNION ALL
                        SELECT  1
                        WHERE channels is NULL 
                )
                  GROUP BY gs.name,
                          vgs.id_service,
                          v.id_organization,
                          vgs.price,
                          st.id,
                          o.short_name,
                          a.name,
                          st.name,
                          rab.total_rabies,
                          vsd.total_f1,
                          vsd.total_f4,
                          vsd.total_ts
                -- order BY a.name,
                --          o.short_name,
                --          st.name
                        
          )t

          GROUP BY 
            t.name,
            t.id_organization, 
            t.id_service,
            t.price,
            t.type_id,
            t.type_name,
            t.short_name,
            t.area_name
          ORDER BY 
            t.area_name,
            t.short_name,
            t.name;
          
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
        $this->execute("DROP FUNCTION get_fullservicesreport");
        $this->execute("DROP FUNCTION get_fullservicesvol2detailreport");
        $this->execute("DROP FUNCTION get_fullvisitsreport");
        $this->execute("DROP FUNCTION get_fullvisitsvol2report");
        $this->execute("DROP FUNCTION get_vetservicesreport");
    }

}
