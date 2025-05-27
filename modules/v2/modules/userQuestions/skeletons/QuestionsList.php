<?php

namespace app\modules\v2\modules\userQuestions\skeletons;

use app\modules\v2\common\skeletons\CommonList;

class QuestionsList extends CommonList
{
    /**
     * QuestionsList constructor
     *
     * @param string $type
     * @param string $attribute_name
     * @param array $elements
     * @param int $totalCount
     * @param int $page
     * @param int $limit
     */
    public function __construct(string $type, string $attribute_name, array $elements, int $totalCount = 0, int $page, int $limit)
    {
        $list = [];
        foreach ($elements as $element) {
            if ($type == 'all') {
                $list[] = [
                    'id' => $element['id'],
                    'create_date' => $element['create_date'],
                    'login' => $element['login'],
                    'f_fio' => $element['f_fio'],
                    'i_fio' => $element['i_fio'],
                    'o_fio' => $element['o_fio'],
                    'question' => $element['question'],
                    'answer' => $element['answer'],
                    'keywords' => $element['keywords']
                ];
            } elseif ($type == 'basic') {
                $list[] = [
                    'create_date' => $element['create_date_short'],
                    'question' => $element['question'],
                    'answer' => $element['answer']
                ];
            } elseif ($type == 'answer') {
                $list[] = [
                    'answer' => $element['answer']
                ];
            }
        }
        parent::__construct($attribute_name, $list, $totalCount, $page, $limit);
    }
}
