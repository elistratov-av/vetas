<?php

namespace app\common\components;

use app\models\db\admin\SecuritySettings;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Claim\Factory as ClaimFactory;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Parsing\Decoder;
use Lcobucci\JWT\Parsing\Encoder;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\ValidationData;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\web\IdentityInterface;


class Jwt extends Component
{
    const ALG = 'RS256';

    public $timeToLife;

    public $publicKeyFile;

    public $privateKeyFile;

    /**
     * @inheritDoc
     */
    public function init()
    {
        $this->timeToLife = ArrayHelper::getValue(Yii::$app->params, 'jwt_token_ttl', 86400);

        // Получаем время жизни токена из БД.
        $settings = SecuritySettings::findOne(['id' => SecuritySettings::DEFAULT_ID]);
        $this->timeToLife = $settings ? $settings->jwt_token_ttl : $this->timeToLife;
    }

    /**
     * @param Encoder|null $encoder
     * @param ClaimFactory|null $claimFactory
     * @return Builder
     */
    public function getBuilder(Encoder $encoder = null, ClaimFactory $claimFactory = null)
    {
        return new Builder($encoder, $claimFactory);
    }

    /**
     * @param Decoder|null $decoder
     * @param ClaimFactory|null $claimFactory
     * @return Parser
     */
    public function getParser(Decoder $decoder = null, ClaimFactory $claimFactory = null)
    {
        return new Parser($decoder, $claimFactory);
    }

    /**
     * @param \app\common\models\UserModel $account
     * @return \Lcobucci\JWT\Token
     * @throws InvalidConfigException
     */
    public function createToken(IdentityInterface $account)
    {
        if (empty($this->privateKeyFile)) {
            throw new InvalidConfigException('Can not find a private key file');
        }

        return $this->getBuilder()
            ->setIssuedAt(time())
            ->setNotBefore(time())
            ->setExpiration(time() + $this->timeToLife)
            ->set('uid', $account->getId())
            ->set('pas', $account->password)
            ->set('auth_key', $account->auth_key)
            ->set('id_specialist', $account->id_specialist)
            ->sign(new Sha256(), new Key('file://'.$this->privateKeyFile))
            ->getToken();

    }

    /**
     * @param null $currentTime
     * @return ValidationData
     */
    public function getValidationData($currentTime = null)
    {
        return new ValidationData($currentTime);
    }

    /**
     * @param Token $token
     * @param null $currentTime
     * @return bool
     */
    public function validateToken(Token $token, $currentTime = null)
    {
        $data = $this->getValidationData($currentTime);

        return $token->validate($data);
    }

    /**
     * @param Token $token
     * @return bool
     * @throws InvalidConfigException
     */
    public function verifyToken(Token $token)
    {
        if (empty($this->publicKeyFile)) {
            throw new InvalidConfigException('Can not find a public key file');
        }

        if (self::ALG !== $token->getHeader('alg')) {
            throw new \InvalidArgumentException('Algorithm not supported');
        }

        return $token->verify(new Sha256(), new Key('file://'.$this->publicKeyFile));
    }

    /**
     * @param $token
     * @param bool $validate
     * @param bool $verify
     * @return Token|null
     * @throws InvalidConfigException
     */
    public function loadToken($token, $validate = true, $verify = true)
    {
        try {
            $token = $this->getParser()->parse((string)$token);
        } catch (\RuntimeException $exception) {
            Yii::warning('Invalid token provided: '.$exception->getMessage(), 'jwt');

            return null;
        } catch (\InvalidArgumentException $exception) {
            Yii::warning('Invalid token provided: '.$exception->getMessage(), 'jwt');

            return null;
        }

        if ($validate && !$this->validateToken($token)) {
            return null;
        }

        if ($verify && !$this->verifyToken($token)) {
            return null;
        }

        return $token;
    }
}
