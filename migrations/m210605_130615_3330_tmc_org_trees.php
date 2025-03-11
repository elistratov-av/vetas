<?php

use app\commands\migrate\Migration;

/**
 * Class m210605_130615_3330_tmc_org_trees
 */
class m210605_130615_3330_tmc_org_trees extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
CREATE VIEW tmc.org_tree(id, level, parent_id, root_id, short_name, path) AS
    ---
    --- Иерархия организаций начиная с комитета
    ---
    WITH RECURSIVE
         org_hierarchy(id, level, parent_id, root_id, short_name, path) AS (
            SELECT o.id,
               0                 AS level,
               o.parent_id,
               o.id              AS root_id,
               o.short_name,
               array[o.id]::INTEGER[] AS path
        FROM public.organizations o
        WHERE o.id = 445
        UNION ALL
        SELECT o.id,
               oh.level + 1                   AS level,
               o.parent_id,
               oh.root_id,
               o.short_name,
               oh.path  || o.id AS path
        FROM public.organizations o,
             org_hierarchy oh
        WHERE o.parent_id = oh.id
    )
SELECT
       org_hierarchy.id,
       org_hierarchy.level,
       org_hierarchy.parent_id,
       org_hierarchy.root_id,
       org_hierarchy.short_name,
       org_hierarchy.path
    
FROM org_hierarchy;
SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON VIEW tmc.org_tree IS 'Иерархия организаций начиная с комитета'");

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

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('DROP FUNCTION tmc.get_org_tree_ids(integer)');
        $this->execute("DROP VIEW tmc.org_tree");
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210605_130615_3330_tmc_org_trees cannot be reverted.\n";

        return false;
    }
    */
}
