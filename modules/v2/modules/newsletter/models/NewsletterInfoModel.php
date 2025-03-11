<?php

namespace app\modules\v2\modules\newsletter\models;

use app\common\components\inform\events\NewsletterInfoEvent;
use app\common\components\inform\events\SubscriptionEventInterface;
use app\common\models\NewsletterInfoStatus;
use app\common\models\UserModel;
use app\models\db\Contacts;
use app\models\db\ContactTypes;
use app\models\db\NewsletterInfo;
use app\models\db\NewsletterType;
use app\models\db\Visits;
use app\modules\admin\models\Organization;
use app\modules\v2\common\skeletons\CommonList;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

class NewsletterInfoModel
{
    public function all(): array
    {
        $response = [];

        foreach (NewsletterInfo::find()->all() as $newsLetter) {
            $response[] = $this->getNewsletterInfoResponse($newsLetter);
        }

        return $response;
    }

    /**
     * @throws BadRequestHttpException
     */
    public function create($data): array
    {
        /* @var $user UserModel */
        $user = \Yii::$app->user->getIdentity();
        $newsletter = new NewsletterInfo();

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['user_id'] = $user->id;

        if (empty($data['mailing_date'])) {
            $data['mailing_date'] = date('Y-m-d H:i:s');
        }

        $data['status'] = NewsletterInfoStatus::NOT_SENT;
        $data['type'] = !empty($data['type']) && $data['type'] == NewsletterType::TYPE_EVENT_LABEL
            ? NewsletterType::TYPE_EVENT
            : NewsletterType::TYPE_USER;

        $newsletter->setAttributes($data);
        $this->setMailingTimeOption($newsletter, $data);

        if (!$newsletter->save()) {
            $errors = $newsletter->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при создании' : implode("\n", array_values($errors)));
        }

        if (!$newsletter->all_organizations) {
            $organizationIds = !empty($data['organizations']) && is_array($data['organizations']) ? $data['organizations'] : [];

            foreach ($organizationIds as $organizationId) {
                if (!Organization::findOne($organizationId)) {
                    continue;
                }

                \Yii::$app->db->createCommand()->insert(
                    'newsletter_info_organization',
                    [
                        'organization_id' => $organizationId,
                        'newsletter_info_id' => $newsletter->id,
                    ]
                )->execute();
            }
        }

        $this->checkStatusForSending($newsletter);

        return $this->getNewsletterInfoResponse($newsletter);
    }

    /**
     * @throws BadRequestHttpException
     */
    public function edit($data): ?array
    {
        if (!$newsletter = NewsletterInfo::findOne($data['id'])) {
            return null;
        }

        if ($newsletter->status != NewsletterInfoStatus::NOT_SENT) {
            return null;
        }

        if (!empty($data['type'])) {
            switch ($data['type']) {
                case NewsletterType::TYPE_EVENT_LABEL: $data['type'] = NewsletterType::TYPE_EVENT; break;
                case NewsletterType::TYPE_USER_LABEL: $data['type'] = NewsletterType::TYPE_USER; break;
                case NewsletterType::TYPE_RECEPTION_LABEL: $data['type'] = NewsletterType::TYPE_RECEPTION; break;
            }
        }

        $newsletter->setAttributes($data);
        $this->setMailingTimeOption($newsletter, $data);

        if (!$newsletter->save()) {
            $errors = $newsletter->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при редактировании ' : implode("\n", array_values($errors)));
        }

        $this->checkStatusForSending($newsletter);

        return $this->getNewsletterInfoResponse($newsletter);
    }

    public function delete($id): bool
    {
        $newsletter = NewsletterInfo::findOne($id);

        if (!$newsletter->delete()) {
            $errors = $newsletter->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при удалении справки' : implode("\n", array_values($errors)));
        }

        return true;
    }

    public function get($id): array
    {
        $newsLetter = NewsletterInfo::findOne($id);

        if (empty($newsLetter)) {
            throw new NotFoundHttpException('Указанный элемент не найден');
        }

        return $this->getNewsletterInfoResponse($newsLetter);
    }

    public function search($status, $name, $mailing_from, $mailing_upto, $organizations, $page, $limit, $actual_mailing_from, $actual_mailing_upto): CommonList
    {
        $filter = ['and'];

        if (!empty($status)) {
            $filter[] = ['status' => $status];
        }

        if (!empty($name)) {
            $filter[] = ['ilike', 'name', $name];
        }

        if (!empty($mailing_from)) {
            $filter[] = ['>=', 'mailing_date', $mailing_from . " 00:00:00"];
        }

        if (!empty($mailing_upto)) {
            $filter[] = ['<=', 'mailing_date', $mailing_upto . " 23:59:59"];
        }

        if (!empty($actual_mailing_from)) {
            $filter[] = ['>=', 'actual_mailing_date', $actual_mailing_from . " 00:00:00"];
        }

        if (!empty($actual_mailing_upto)) {
            $filter[] = ['<=', 'actual_mailing_date', $actual_mailing_upto . " 23:59:59"];
        }

        $page = empty($page) ? 1 : $page;
        $limit = empty($limit) ? 10 : $limit;

        $query = NewsletterInfo::find()
            ->andFilterWhere($filter)
            ->orderBy('name ASC');

        if (!empty($organizations) && is_array($organizations)) {
            $query->joinWith('organizations');
            $query->andWhere(['IN', 'organizations.id', $organizations]);
        }

        $count = clone $query;
        $query = $query
            ->limit($limit)
            ->offset(($page - 1) * $limit);
        $newsLetters = [];

        foreach ($query->all() as $newsLetter) {
            $newsLetters[] = $this->getNewsletterInfoResponse($newsLetter);
        }

        return new CommonList(
            'newsletter_info', $newsLetters,
            $count->count(), $page, $limit
        );
    }

    private function getNewsletterInfoResponse(NewsletterInfo $newsletterInfo): array
    {
        $response = $newsletterInfo->toArray();
        $type = $newsletterInfo->getType()->one();
        $user = $newsletterInfo->getUser()->one();
        $response['type'] = $type ? $type->toArray() : null;
        $response['user'] = $user ? $user->toArray(['id', 'fullname']) : null;
        $response['organizations'] = [];

        $response['mailing_time_option'] = $newsletterInfo->mailing_time_option == NewsletterInfo::MAILING_TIME_OPTION_DATE
            ? 'by_date'
            : 'now';

        foreach ($newsletterInfo->getOrganizations()->all() as $organization) {
            $response['organizations'][] = $organization->toArray(['id', 'name']);
        }

        return $response;
    }

    private function setMailingTimeOption(NewsletterInfo $newsletter, array $data)
    {
        $mailingTimeOption = NewsletterInfo::MAILING_TIME_OPTION_NOW;

        if (!empty($data['mailing_time_option']) && $data['mailing_time_option'] == 'by_date') {
            $mailingTimeOption = NewsletterInfo::MAILING_TIME_OPTION_DATE;
        }

        $newsletter->mailing_time_option = $mailingTimeOption;
    }

    private function checkStatusForSending(NewsletterInfo $newsletterInfo)
    {
        if ($newsletterInfo->status != NewsletterInfoStatus::NOT_SENT) {
            return;
        }

        if ($newsletterInfo->mailing_time_option == NewsletterInfo::MAILING_TIME_OPTION_NOW) {
            $this->send($newsletterInfo);
        }
    }

    protected function contactTypeEmail()
    {
        return ContactTypes::findOne([
            'name' => 'Электронная почта',
            'type' => ContactTypes::TYPE_EMAIL,
            'entity_type' => ContactTypes::ENTITY_TYPE_PET_OWNER,
        ]);
    }

    public function send(NewsletterInfo $newsletter)
    {
        $contactType = ContactTypes::findOne(['type' => ContactTypes::TYPE_EMAIL]);

        $organizations = $newsletter->all_organizations ? Organization::find()->all() : $newsletter->organizations;

        foreach ($organizations as $organization) {
            $organization = Organization::findOne($organization->id);

            if ($newsletter->type == NewsletterType::TYPE_EVENT) {
                foreach ($organization->getSpecialists()->all() as $specialist) {
                    $contacts = [new Contacts([
                        'id_contact_type' => $contactType->id,
                        'name' => $specialist->getUser()->one()->email,
                    ])];
                    \Yii::$app->trigger(SubscriptionEventInterface::EVENT_NAME, new NewsletterInfoEvent([
                        'contacts' => $contacts,
                        'newsletter' => $newsletter,
                    ]));
                }
            } elseif ($newsletter->type == NewsletterType::TYPE_USER) {
                $ownerIds = [];
                $visits = Visits::find()
                    ->select('id_owner')
                    ->andWhere(['id_organization' => $organization->id])
                    ->andWhere('id_owner is not null')
                    ->andWhere(['>', 'start_dttm', (new \DateTime())->format('Y-m-d 00:00:00')])
                    ->all();

                $contactTypeEmail = $this->contactTypeEmail();
                foreach ($visits as $visit) {

                    if (!$owner = $visit->getOwner()->one()) {
                        continue;
                    }

                    if (in_array($owner->id, $ownerIds)) {
                        continue;
                    }

                    $query = Contacts::find()
                        ->where([
                            'id_contact_type' => $contactTypeEmail->id,
                            'entity_type' => ContactTypes::ENTITY_TYPE_PET_OWNER,
                            'entity_id' => $owner->id,
                        ]);
                    if (!empty($this->owner->Email)) {
                        $query->andWhere(['name' => $this->owner->Email]);
                    }

                    $contact = $query->one();

                    if ($contact === null) {
                        continue;
                    }

                    \Yii::$app->trigger(SubscriptionEventInterface::EVENT_NAME, new NewsletterInfoEvent([
                        'contacts' => [$contact],
                        'newsletter' => $newsletter,
                    ]));
                }
            }
        }
    }
}
