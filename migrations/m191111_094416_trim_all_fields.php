<?php

use app\commands\migrate\Migration;

/**
 * Class m191111_094416_trim_all_fields
 */
class m191111_094416_trim_all_fields extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("DROP FUNCTION IF EXISTS public.full_trim(VARCHAR)");

        $sql = <<<SQL
CREATE OR REPLACE FUNCTION public.full_trim(VARCHAR) RETURNS varchar AS $$
DECLARE
    attr ALIAS FOR $1;
BEGIN
        attr = replace(attr, 'amp;','');
        attr = replace(attr, '&nbsp;',' '); --неразрывный пробел
        attr = replace(attr, '&thinsp;',' '); --\u2009 тонкий
        attr = replace(attr, '&#8239;',' '); --узкий пробел
        attr = replace(attr, '&hairsp;',' '); --\u200A волосяной
        attr = replace(attr, '&#8203;',' '); --\u200B без ширины, при необходимости переносит слово
        attr = replace(attr, '&shy;',' '); --\u00AD без ширины, при необходимости переносит слово, добавляя к нему дефис
        attr = replace(attr, '&NoBreak;',' '); --\u2060 без ширины, неразрывный
        attr = replace(attr, '&emsp;',' '); --\u2003 равен 1em, то есть размеру кегеля
        attr = replace(attr, '&numsp;',' '); --\u2007 равен ширине цифры, если все цифры одинаковой ширины, неразрывный
        attr = replace(attr, '&puncsp;',' '); --\u2008 равен ширине запятой
        attr = replace(attr, '&blank;',' '); --\u2423 обозначение символа
        attr = regexp_replace(attr, '/\s{2,}/g',' ');
        attr = trim(attr);
  RETURN attr;
END;
$$ LANGUAGE plpgsql VOLATILE;
SQL;

        $this->execute($sql);

        $sql = <<<SQL
UPDATE active_substances 
SET name = full_trim(name), name_en = full_trim(name_en)
WHERE id > 0
SQL;
        $this->execute($sql);

        $sql = <<<SQL
UPDATE balance_drugs 
SET inventory_number = full_trim(inventory_number)
WHERE id > 0
SQL;
        $this->execute($sql);

        $sql = <<<SQL
UPDATE balance_equipments 
SET inventory_number = full_trim(inventory_number), manufactured_number = full_trim(manufactured_number)
WHERE id > 0
SQL;
        $this->execute($sql);

        $sql = <<<SQL
UPDATE balance_exp_materials
SET inventory_number = full_trim(inventory_number)
WHERE id > 0
SQL;
        $this->execute($sql);

        $sql = <<<SQL
UPDATE balance_vaccines
SET inventory_number = full_trim(inventory_number)
WHERE id > 0
SQL;
        $this->execute($sql);

        $sql = <<<SQL
UPDATE breeds
SET name = full_trim(name)
WHERE id > 0
SQL;
        $this->execute($sql);

        $sql = <<<SQL
UPDATE cabinet_types
SET name = full_trim(name), description = full_trim(description)
WHERE id > 0
SQL;
        $this->execute($sql);

        $sql = <<<SQL
UPDATE change_request
SET description = full_trim(description)
WHERE id > 0
SQL;
        $this->execute($sql);

        $sql = <<<SQL
UPDATE descriptions 
SET description = full_trim(description)
WHERE id > 0
SQL;
        $this->execute($sql);

//         $sql = <<<SQL
// UPDATE descriptions_templates
// SET caption = full_trim(caption), template = full_trim(template)
// WHERE id > 0
// SQL;
//         $this->execute($sql);

        $sql = <<<SQL
UPDATE discount 
SET name = full_trim(name)
WHERE id > 0
SQL;
        $this->execute($sql);

        $sql = <<<SQL
UPDATE descriptions 
SET description = full_trim(description)
WHERE id > 0
SQL;
        $this->execute($sql);

        $sql = <<<SQL
UPDATE exp_materials 
SET description = full_trim(description)
WHERE id > 0
SQL;
        $this->execute($sql);

        $sql = <<<SQL
UPDATE faq 
SET question = full_trim(question), answer = full_trim(answer)
WHERE id > 0
SQL;
        $this->execute($sql);

        $sql = <<<SQL
UPDATE help 
SET caption = full_trim(caption), text = full_trim(text)
WHERE id > 0
SQL;
        $this->execute($sql);

        $sql = <<<SQL
UPDATE help_links 
SET text = full_trim(text)
WHERE id > 0
SQL;
        $this->execute($sql);

        $sql = <<<SQL
UPDATE measures 
SET description = full_trim(description)
WHERE id > 0
SQL;
        $this->execute($sql);

        $sql = <<<SQL
UPDATE org_types 
SET description = full_trim(description)
WHERE id > 0
SQL;
        $this->execute($sql);

        $sql = <<<SQL
UPDATE organizations 
SET chief_name = full_trim(chief_name), chief_position = full_trim(chief_position)
WHERE id > 0
SQL;
        $this->execute($sql);

        $sql = <<<SQL
UPDATE pet_identification 
SET identification_code = full_trim(identification_code)
WHERE id > 0
SQL;
        $this->execute($sql);

        $sql = <<<SQL
UPDATE pet_owners 
SET f_fio = full_trim(f_fio), i_fio = full_trim(i_fio), o_fio = full_trim(o_fio), jur_name = full_trim(jur_name)
WHERE id > 0
SQL;
        $this->execute($sql);

        $sql = <<<SQL
UPDATE pets 
SET name = full_trim(name), characteristics = full_trim(characteristics)
WHERE id > 0
SQL;
        $this->execute($sql);

        $sql = <<<SQL
UPDATE quarantines 
SET threatened_area = full_trim(threatened_area), comments = full_trim(comments)
WHERE id > 0
SQL;
        $this->execute($sql);

        $sql = <<<SQL
UPDATE reg_certificates 
SET number = full_trim(number)
WHERE id > 0
SQL;
        $this->execute($sql);

        $sql = <<<SQL
UPDATE reg_expire_reasons 
SET name = full_trim(name), description = full_trim(description)
WHERE id > 0
SQL;
        $this->execute($sql);

        $sql = <<<SQL
UPDATE service_measures 
SET description = full_trim(description)
WHERE id > 0
SQL;
        $this->execute($sql);

        $sql = <<<SQL
UPDATE service_types 
SET description = full_trim(description)
WHERE id > 0
SQL;
        $this->execute($sql);

        $sql = <<<SQL
UPDATE shelter_guests 
SET arrival_comment = full_trim(arrival_comment), departure_comment = full_trim(departure_comment)
WHERE id > 0
SQL;
        $this->execute($sql);

//         $sql = <<<SQL
// UPDATE shifts
// SET name = full_trim(name)
// WHERE id > 0
// SQL;
//         $this->execute($sql);

        $sql = <<<SQL
UPDATE specialists_update_reasons 
SET description = full_trim(description)
WHERE id > 0
SQL;
        $this->execute($sql);

        $sql = <<<SQL
UPDATE specializations 
SET name = full_trim(name), description = full_trim(description)
WHERE id > 0
SQL;
        $this->execute($sql);

        $sql = <<<SQL
UPDATE species 
SET name = full_trim(name), description = full_trim(description)
WHERE id > 0
SQL;
        $this->execute($sql);

        $sql = <<<SQL
UPDATE users 
SET f_fio = full_trim(f_fio), i_fio = full_trim(i_fio), o_fio = full_trim(o_fio)
WHERE id > 0
SQL;
        $this->execute($sql);

        $sql = <<<SQL
UPDATE violation 
SET comment = full_trim(comment)
WHERE id_violation > 0
SQL;
        $this->execute($sql);

        $sql = <<<SQL
UPDATE violation_admin_rights 
SET full_name = full_trim(full_name), description= full_trim(description)
WHERE "violation_admin_rights"."id_ARV" > 0
SQL;
        $this->execute($sql);

        $sql = <<<SQL
UPDATE violation_history 
SET description = full_trim(description)
WHERE id_change > 0
SQL;
        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("DROP FUNCTION IF EXISTS public.full_trim(VARCHAR)");
    }
}
