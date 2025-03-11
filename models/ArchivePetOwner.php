<?

namespace app\models;

use app\models\db\PetOwners;
use Codeception\Command\Console;
use Exception;
use yii\db\ActiveRecord;
use Yii;

class ArchivePetOwner extends ActiveRecord
{

    public static function tableName()
    {
        return 'archive.pet_owners';
    }

    public function rules()
    {
        return [
            [['id', 'f_fio', 'i_fio', 'result_id'], 'required']
        ];
    }

    /**
     * Перенос владельца в архив
     *
     * @param PetOwners $petOwner - Модель основного владельца
     * @param PetOwners $newOwner - Модель нового владельца
     * @return ArchivePetOwner|false - Модель архивного владельца или false в случае неудачи
     */
    public static function archivePetOwner(PetOwners $petOwner, PetOwners $newOwner)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $archiveOwner = new self();
            foreach ($petOwner->attributes as $attribute => $value) {
                if ($archiveOwner->hasAttribute($attribute)) {
                    $archiveOwner->$attribute = $value;
                }
            }
            $archiveOwner->result_id = $newOwner->id;
            if ($archiveOwner->save() && $petOwner->delete()) {
                $transaction->commit();
                return $archiveOwner;
            } else {
                throw new Exception('Ошибка при сохранении в архив или удалении владельца.');
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error("Ошибка архивирования владельца: " . $e->getMessage());
            return false;
        }
    }
}
