<?php

use yii\db\Migration;

/**
 * Class m180604_092601_add_vaccines_table
 */
class m180604_092601_add_vaccines_table extends Migration
{
    const VACCINES_TABLE = 'vaccines';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(self::VACCINES_TABLE, [
                'id' => $this->primaryKey(),
                'id_tmc_type' => $this->integer()->comment('Ссылка на справочник Типов ТМЦ'),
                'id_manufactured' => $this->integer()->comment('Ссылка на справочник производителей'),
                'id_representation' => $this->integer()->comment('Ссылка на справочник представителей'),
                'id_measure' => $this->integer()->comment('Ссылка на справочник единиц измерений'),
                'id_active_substance' => $this->integer()->comment('Ссылка на справочник Активных веществ'),
                'id_active_substance_measure' => $this->integer()->comment('Ссылка на справочник единиц измерений'),
                'id_file_packaging_image' => $this->integer()->comment('Ссылка на реестр файлов'),
                'name' => $this->string(255)->unique()->notNull()->comment('Название'),
                'form' => $this->string(255)->notNull()->comment('Лекарственная форма'),
                'form_description' => $this->string(255)->comment('Описание лекарственной формы'),
                'unit' => $this->string(255)->comment('Содержание активных веществ'),
                'active_substance_unit' => $this->string(255)
                    ->comment('Количество активного вещества в указанном объеме препарата'),
                'excipients' => $this->string(255)->comment('Вспомогательные вещества'),
                'packaging' => $this->text()->comment('Описанеие упаковки'),
                'basis' => $this->text()->comment('Основание описание препарата')
            ]
        );

        $this->addForeignKey(
            'fk-' . self::VACCINES_TABLE . '-id_tmc_type',
            self::VACCINES_TABLE,
            'id_tmc_type',
            'tmc_types',
            'id',
            'NO ACTION',
            'NO ACTION'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(self::VACCINES_TABLE);
    }

}
