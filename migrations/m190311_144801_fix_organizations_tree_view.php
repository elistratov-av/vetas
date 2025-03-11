<?php

use app\commands\migrate\Migration;

/**
 * Class m190311_144801_fix_organizations_tree_view
 */
class m190311_144801_fix_organizations_tree_view extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
CREATE OR REPLACE VIEW public.organizations_tree AS 
 WITH RECURSIVE org_tree(id, level, parent_id, root_id, short_name, pricelist_id, path) AS (
         SELECT o.id,
            0 AS level,
            o.parent_id,
            o.id AS root_id,
            o.short_name,
            p.id AS pricelist_id,
            '/'::text || o.id AS path
           FROM organizations o
             LEFT JOIN pricelists p ON p.id_organization = o.id
          WHERE o.parent_id = 0
        UNION ALL
         SELECT o.id,
            ot.level + 1 AS level,
            o.parent_id,
            ot.root_id,
            o.short_name,
            ot.pricelist_id,
            (ot.path || '/'::text) || o.id AS path
           FROM organizations o,
            org_tree ot
          WHERE o.parent_id = ot.id
        )
 SELECT org_tree.id,
    org_tree.level,
    org_tree.parent_id,
    org_tree.root_id,
    org_tree.short_name,
    org_tree.pricelist_id,
    org_tree.path
   FROM org_tree
  ORDER BY org_tree.id;
SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON VIEW public.organizations_tree
  IS 'Дерево организаций'");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }
}
