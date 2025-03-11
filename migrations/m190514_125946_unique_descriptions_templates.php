<?php

use app\commands\migrate\Migration;

/**
 * Class m190514_125946_unique_descriptions_templates
 */
class m190514_125946_unique_descriptions_templates extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        /*
         * Удаляем возможные неуникальные по пользователям
         */
        $sql ="
DELETE FROM
    descriptions_templates a
        USING descriptions_templates b
WHERE
        a.id > b.id
    AND 
        a.caption = b.caption
    AND
        a.id_user = b.id_user
    AND
        a.public = b.public
    AND
      a.public = false
;";

        $this->execute($sql);

        /*
         * Удаляем возможные неуникальные по пользователям
         */
        $sql ="
DELETE FROM
    descriptions_templates a
        USING descriptions_templates b
WHERE
        a.id > b.id
    AND 
        a.caption = b.caption
    AND
        a.id_organization = b.id_organization
    AND
        a.public = b.public
    AND
      a.public = true
;";

        $this->execute($sql);

        $this->execute("
        CREATE UNIQUE INDEX partial_uniq_descriptions_templates_caption_id_organization_public
        ON descriptions_templates (caption, id_organization) 
        WHERE public = TRUE;");

        $this->execute("
        CREATE UNIQUE INDEX partial_uniq_descriptions_templates_caption_id_user_private
        ON descriptions_templates (caption, id_user) 
        WHERE public = FALSE;");

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex(
            'partial_uniq_descriptions_templates_caption_id_organization_public',
            'descriptions_templates'
        );

        $this->dropIndex(
            'partial_uniq_descriptions_templates_caption_id_user_private',
            'descriptions_templates'
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190514_125946_unique_descriptions_templates cannot be reverted.\n";

        return false;
    }
    */
}
