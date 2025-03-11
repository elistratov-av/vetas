<?php

use app\commands\migrate\Migration;
use app\models\db\DocumentTypes as Model;

/**
 * Class m220216_093830_add_documents
 */
class m220216_093830_add_documents extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('public.document_types', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'type' => $this->string()->notNull(),
            'place_type' => $this->string()->notNull(),
        ]);

        $types = Model::TYPES;
        $places = Model::GROUPS;

        $this->execute("INSERT INTO 
        document_types(name, type, place_type)
        VALUES ('" . $types[Model::TYPE_ACT_CATCH] . "', '" . Model::TYPE_ACT_CATCH . "', '" . Model::GROUP_CARD . "'), 
        ('" . $types[Model::TYPE_ACT_ARRIVE_CATCH] . "', '" . Model::TYPE_ACT_ARRIVE_CATCH . "', '" . Model::GROUP_CARD . "'), 
        ('" . $types[Model::TYPE_COURT_DECISION] . "', '" . Model::TYPE_COURT_DECISION . "', '" . Model::GROUP_CARD . "'), 
        ('" . $types[Model::TYPE_REFUSE_OWNER] . "', '" . Model::TYPE_REFUSE_OWNER . "', '" . Model::GROUP_CARD . "'), 
        ('" . $types[Model::TYPE_WORK_ORDER] . "', '" . Model::TYPE_WORK_ORDER . "', '" . Model::GROUP_CARD . "'), 
        ('" . $types[Model::TYPE_ACT_RETURN_TO_OWNER] . "', '" . Model::TYPE_ACT_RETURN_TO_OWNER . "', '" . Model::GROUP_RETURN . "'), 
        ('" . $types[Model::TYPE_QUESTIONNAIRE] . "', '" . Model::TYPE_QUESTIONNAIRE . "', '" . Model::GROUP_NEW_OWNER . "'), 
        ('" . $types[Model::TYPE_CONTRACT] . "', '" . Model::TYPE_CONTRACT . "', '" . Model::GROUP_NEW_OWNER . "'), 
        ('" . $types[Model::TYPE_ACT_DEATH] . "', '" . Model::TYPE_ACT_DEATH . "', '" . Model::GROUP_DEATH . "'), 
        ('" . $types[Model::TYPE_OTHER] . "', '" . Model::TYPE_OTHER . "', '" . Model::GROUP_DEATH . "'), 
        ('" . $types[Model::TYPE_OTHER] . "', '" . Model::TYPE_OTHER . "', '" . Model::GROUP_NEW_OWNER . "'), 
        ('" . $types[Model::TYPE_OTHER] . "', '" . Model::TYPE_OTHER . "', '" . Model::GROUP_RETURN . "'), 
        ('" . $types[Model::TYPE_OTHER] . "', '" . Model::TYPE_OTHER . "', '" . Model::GROUP_CARD . "');
        ");


        $this->createTable('public.documents', [
            'id' => $this->primaryKey(),
            'file_id' => $this->integer()->notNull(),
            'type_id' => $this->integer()->notNull(),
            'status' => $this->string()->notNull(),
            'number' => $this->string()->notNull(),
            'date' => $this->date()->notNull(),
            'name' => $this->string()->notNull(),
            'created_by' => $this->integer()->notNull(),
            'created_date' => $this->dateTime()->notNull()
        ]);

        $this->addForeignKey(
            'fk-documents-file_id',
            'documents',
            'file_id',
            'files',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-documents-type_id',
            'documents',
            'type_id',
            'document_types',
            'id'
        );

        $this->addForeignKey(
            'fk-documents-created_by',
            'documents',
            'created_by',
            'users',
            'id'
        );

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-documents-file_id', 'documents');
        $this->dropForeignKey('fk-documents-type_id', 'documents');
        $this->dropForeignKey('fk-documents-created_by', 'documents');
        $this->dropTable('public.documents');

        $this->dropTable('public.document_types');
    }

}
