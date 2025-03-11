<?php

use app\commands\migrate\Migration;

/**
 * Class m210325_124224_violaitions_change_statuses_comment
 */
class m210325_124224_violaitions_change_statuses_comment extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropCommentFromColumn(
            'violation',
            'state'
        );

        $this->addCommentOnColumn(
            'violation',
            'state',
            'Состояние нарушения: N - новое, W – в работе, C - отменено, F - завершено, V - на проверке'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropCommentFromColumn(
            'violation',
            'state'
        );

        $this->addCommentOnColumn(
            'violation',
            'state',
            'Состояние нарушения: N - новое, W – в работе, C - отменено, F - завершено'
        );
    }
}
