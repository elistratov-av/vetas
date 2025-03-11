<?

namespace app\models;

use app\models\db\Pets;
use yii\helpers\Console;
use Exception;
use yii\db\ActiveRecord;
use Yii;

class ArchivePet extends ActiveRecord
{

    public static function tableName()
    {
        return 'archive.pets';
    }

    public function rules()
    {
        return [
            [['id'], 'required']
        ];
    }

    /**
     * Перенос питомца в архив
     *
     * @param Pet $pet - Модель основного питомца
     * @param Pet $newPet - Модель нового питомца
     * @return ArchivePet|false - Модель архивного питомца или false в случае неудачи
     */
    public static function archivePet(Pets $pet, Pets $newPet)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $archivePet = new self();
            foreach ($pet->attributes as $attribute => $value) {
                if ($archivePet->hasAttribute($attribute)) {
                    $archivePet->$attribute = $value;
                }
            }
            $archivePet->result_id = $newPet->id;
            if ($archivePet->save() && $pet->delete()) {
                $transaction->commit();
                return $archivePet;
            } else {
                Console::error("Ошибки: " . print_r($pet->getErrors(), true));
                Console::error("Ошибки: " . print_r($archivePet->getErrors(), true));
                throw new Exception('Ошибка при сохранении в архив или удалении питомца.');
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            Console::error("Ошибка архивирования питомца: " . $e->getMessage());
            Yii::error("Ошибка архивирования питомца: " . $e->getMessage());
            return false;
        }
    }
}
