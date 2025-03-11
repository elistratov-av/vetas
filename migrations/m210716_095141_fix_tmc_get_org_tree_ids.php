<?php

use app\commands\migrate\Migration;

/**
 * Class m210716_095141_fix_tmc_get_org_tree_ids
 */
class m210716_095141_fix_tmc_get_org_tree_ids extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
CREATE OR REPLACE FUNCTION tmc.get_org_tree_ids(org_id integer) RETURNS TABLE(id integer) AS $$
    DECLARE
        gos_vet_nadzor_ID constant integer := 445;
        mos_vet_union_ID  constant integer := 456;
        parent_ssbz_ID integer;
    BEGIN
        ---
        --- Эта функция нужна для работы с иерархией организаций в плане их взаимодействия с ТМЦ
        ---
        --- Все ТМЦ поставляются из Мосветобъединения в ССБЖ, каждый из которых является "головой" в своем округе.
        --- А из ССБЖ уже распределяются по дочерним/внучатым организациям.
        --- Соответвенно для техников ТМЦ, необходимо видеть весь округ + Мосветобъединение
        --- Так же некоторый ф-ционал будет доступен и врачам в пределах округа
        ---  (например, запросить себе ТМЦ у родительской организации)
        ---
        --- При этом, мосветобъединение хочет видеть все свои дочерние.
        --- А комитет - всех...
        ---
        --- UPD: выяснилось что балансы есть так же и у организаций,
        --  которые не входят в иерархию "Комитет > Мосветобъединение > СББЖ"
        --- Им показываем их же организацию
        ---
        case org_id
            when gos_vet_nadzor_ID then
                --- Комитет видит себя, мосветобъединение, и все ниже мосветобъединения
                RETURN QUERY
                    SELECT org_tree.id
                    FROM tmc.org_tree
                    WHERE
                        (path[1] = gos_vet_nadzor_ID AND path[2] = mos_vet_union_ID) OR org_tree.id = gos_vet_nadzor_ID;
            when mos_vet_union_ID then
                --- Мосветобъединение видит себя и все ниже
                RETURN QUERY
                    SELECT org_tree.id
                    FROM tmc.org_tree
                    WHERE
                        (path[1] = gos_vet_nadzor_ID AND path[2] = mos_vet_union_ID);
            else
                --- Для остальных выясняем id их сббж
                SELECT path[3]
                INTO parent_ssbz_ID
                FROM tmc.org_tree
                WHERE org_tree.id = org_id
                LIMIT 1;

                --- Если организация не входит в иерархию ССБЖ
                --- отдаем им список только с их организацией
                IF parent_ssbz_ID IS NULL THEN
                    RETURN QUERY
                        SELECT org_id AS id;
                ELSE 
                    --- Если в иерахии - показываем все дочерниее этого ССБЖ + мосветобъединение
                    RETURN QUERY
                        SELECT org_tree.id
                        FROM tmc.org_tree
                        WHERE
                            (path[1] = gos_vet_nadzor_ID AND path[2] = mos_vet_union_ID AND path[3] = parent_ssbz_ID)
                           OR org_tree.id = mos_vet_union_ID;
                END IF;               
            end case;
    END;
$$ LANGUAGE plpgsql;
SQL;

        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $sql = <<<SQL
CREATE OR REPLACE FUNCTION tmc.get_org_tree_ids(org_id integer) RETURNS TABLE(id integer) AS $$
    DECLARE
        gos_vet_nadzor_ID constant integer := 445;
        mos_vet_union_ID  constant integer := 456;
        parent_ssbz_ID integer;
    BEGIN
        ---
        --- Эта функция нужна для работы с иерархией организаций в плане их взаимодействия с ТМЦ
        ---
        --- Все ТМЦ поставляются из Мосветобъединения в ССБЖ, каждый из которых является "головой" в своем округе.
        --- А из ССБЖ уже распределяются по дочерним/внучатым организациям.
        --- Соответвенно для техников ТМЦ, необходимо видеть весь округ + Мосветобъединение
        --- Так же некоторый ф-ционал будет доступен и врачам в пределах округа
        ---  (например, запросить себе ТМЦ у родительской организации)
        ---
        --- При этом, мосветобъединение хочет видеть все свои дочерние.
        --- А комитет - всех...
        ---
        case org_id
            when gos_vet_nadzor_ID then
                --- Комитет видит себя, мосветобъединение, и все ниже мосветобъединения
                RETURN QUERY
                    SELECT org_tree.id
                    FROM tmc.org_tree
                    WHERE
                        (path[1] = gos_vet_nadzor_ID AND path[2] = mos_vet_union_ID) OR org_tree.id = gos_vet_nadzor_ID;
            when mos_vet_union_ID then
                --- Мосветобъединение видит себя и все ниже
                RETURN QUERY
                    SELECT org_tree.id
                    FROM tmc.org_tree
                    WHERE
                        (path[1] = gos_vet_nadzor_ID AND path[2] = mos_vet_union_ID);
            else
                --- Для остальных выясняем id их сббж
                SELECT path[3]
                INTO parent_ssbz_ID
                FROM tmc.org_tree
                WHERE org_tree.id = org_id
                LIMIT 1;

                --- И показываем все дочерниее этого ССБЖ + мосветобъединение
                RETURN QUERY
                    SELECT org_tree.id
                    FROM tmc.org_tree
                    WHERE
                        (path[1] = gos_vet_nadzor_ID AND path[2] = mos_vet_union_ID AND path[3] = parent_ssbz_ID)
                       OR org_tree.id = mos_vet_union_ID;
            end case;
    END;
$$ LANGUAGE plpgsql;
SQL;

        $this->execute($sql);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210716_095141_fix_tmc_get_org_tree_ids cannot be reverted.\n";

        return false;
    }
    */
}
