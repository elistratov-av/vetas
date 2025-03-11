<?php

namespace app\modules\admin\widgets;

use yii\grid\GridView;

class ExtendedGridView extends GridView
{
    public $tableHeader = '';

    /**
     * Renders the table header.
     * @return string the rendering result.
     */
    public function renderTableHeader()
    {
        return "<thead>\n" . $this->tableHeader . "\n</thead>";
    }
}
