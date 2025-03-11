<?php


namespace app\modules\v2\modules\pets\controllers;

use app\modules\v2\modules\BaseController;
use SoapClient;

// use SoapServer;
use yii\httpclient\Client;

//use yii\httpclient\Response;
use yii\web\Response;
use app\common\components\pdfGenerator\PdfVisitGeneratorHelper;
use app\common\components\FileService;
use app\common\helpers\DateHelper;
use app\common\models\VisitStatus;
use app\common\validators\FullTrimValidator;
use app\common\validators\PGFilterEscapesValidator;
use app\common\validators\PGFilterQuotesValidator;
use app\models\db\Contacts;
use app\models\db\IdentificationTypes;
use app\models\db\ContactTypes;
use app\models\db\PetOwners;
use app\models\db\PetRabiesVaccination;
use app\models\db\PetOtherVaccinations;
use app\models\db\PetOwnersHistory;
use app\models\db\FiasAddresses;
use app\models\db\PetOwnerType;
use app\models\db\Pets;
use app\models\db\PetsToOwner;
use app\models\db\Visits;
use app\models\db\VisitServiceTmc;
use app\modules\mdm\models\Pet;
use app\modules\soap\models\VisitsGovServices;
use app\modules\v1\models\FileResource;
use app\modules\v2\modules\pets\skeletons\pets\PetsList;
use app\modules\v2\modules\visit\models\BillModel;
use yii\base\BaseObject;
use yii\base\DynamicModel;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\ArrayExpression;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;
use DateTime;
use yii\helpers\FileHelper;
use Yii;

class ChedController extends BaseController
{
    // const BASE_URL_SOAP_CLIENT = 'https://stage-doc-upload.mos.ru/custom-api-2.0/CustomWebService2';//ПРЕДПРОД
    // const BASE_URL_REST_CLIENT = "https://stage-doc-upload.mos.ru/custom-api-2.0/rest/api/document/";//ПРЕДПРОД
    // const AUTHCODE = 'dmV0YXM6RmpzaWkyam5ybWZsd2wyLTEy=';//ПРЕДПРОД

    const BASE_URL_SOAP_CLIENT = 'https://doc-upload.mos.ru/custom-api-2.0/CustomWebService2';//ПРОД
    const BASE_URL_REST_CLIENT = "https://doc-upload.mos.ru/custom-api-2.0/rest/api/document/";//ПРОД
    // const AUTHCODE = 'dmV0YXM6Tj0lZUY0NVw7TyVtUz1MdGJsL2U=';//ПРОД

    // const BASE_URL_SOAP_CLIENT = 'https://doc-upload2-stage.mos.ru/custom-api-2.0/CustomWebService2';//ПРОД
    // const BASE_URL_REST_CLIENT = "https://doc-upload2-stage.mos.ru/custom-api-2.0/rest/api/document/";//ПРОД
    // const AUTHCODE = 'dmV0YXNfZ3VfZG9jczo0WFAnYWJ6aFAkeWtQS3ZLaWpBMQ==';//ПРОД new

    const TEXT_HEADERS = [
        'Accept-Encoding' => 'gzip,deflate',
        'Content-Type' => 'text/xml;charset=UTF-8',
        'Host' => 'doc-upload.mos.ru',
        'Connection' => 'Keep-Alive',
        'User-Agent' => 'Apache-HttpClient/4.5.5 (Java/11.0.8)',
        'Authorization' => 'Basic dmV0YXM6Tj0lZUY0NVw7TyVtUz1MdGJsL2U=',
    ];

    const IMAGE_HEADERS = [
        'Content-Type' => 'image/png',
        'Host' => 'doc-upload.mos.ru',
        'Connection' => 'Keep-Alive',
        'User-Agent' => 'Apache-HttpClient/4.5.5 (Java/11.0.8)',
        'Authorization' => 'Basic dmV0YXM6Tj0lZUY0NVw7TyVtUz1MdGJsL2U=',
    ];

    const PDF_HEADERS = [
        'Accept-Encoding' => 'gzip,deflate',
        'Content-Type' => 'application/pdf',
        'Host' => 'doc-upload.mos.ru',
        'Connection' => 'Keep-Alive',
        'User-Agent' => 'Apache-HttpClient/4.5.5 (Java/11.0.8)',
        'Authorization' => 'Basic dmV0YXM6Tj0lZUY0NVw7TyVtUz1MdGJsL2U=',
    ];

    const PDF_UPLOAD_FOLDER = '@app/web/upload/pdf/superservice';
    const PDF_URL_UPLOAD_FOLDER = '/upload/pdf/superservice';

    const PHOTO_UPLOAD_FOLDER = '@app/web/upload/photo/superservice';
    const PHOTO_URL_UPLOAD_FOLDER = '/upload/photo/superservice';

    public function actionTest($some)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_XML;

        return $some;
    }


    // http://localhost:8888/v2/pets/test/send

    public function actionSend($file_obj)
    {

        $client = new Client([
            'baseUrl' => self::BASE_URL_SOAP_CLIENT,
            'requestConfig' => [
                'format' => Client::FORMAT_JSON
            ],
            'responseConfig' => [
                'format' => Client::FORMAT_JSON
            ],
        ]);
        $response = $client->createRequest()
            ->setMethod('POST')
            ->setContent('<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:cus="http://cloud.mos.ru/customWebService2/"><soapenv:Header/><soapenv:Body><cus:CreateDocument><Document>TG9yZW0gaXBzdW0gZG9sb3Igc2l0IGFtZXQgY29uc2VjdGV0dXIgYWRpcGlzaWNpbmcgZWxpdC4gUmVwZWxsYXQsIG1pbmltYT8gQW5pbWkgcG9ycm8gdWxsYW0gaXRhcXVlPyBPcHRpbyBwb3NzaW11cyBkaXN0aW5jdGlvIG51bXF1YW0sIGRvbG9yIHByYWVzZW50aXVtIGJsYW5kaXRpaXMgcG9ycm8gaXBzdW0hIFJlbSBudWxsYSBmYWNpbGlzIGlsbHVtLCBxdWFlcmF0IHJlY3VzYW5kYWUgZW5pbT8KSGFydW0gZG9sb3JlIHBvc3NpbXVzIGxhYm9yZSBkb2xvcmVtcXVlIHJlcGVsbGF0IHZvbHVwdGFzIHF1YWUgdm9sdXB0YXRlIGZhY2lsaXMgZW5pbSBzaW50IG1hZ25pIGNvcnBvcmlzIHJlcGVsbGVuZHVzIGFsaWFzIGxpYmVybyBkb2xvciByYXRpb25lIGZhY2VyZSwgcXVpIGxhdWRhbnRpdW0gc2l0IGRlc2VydW50IGFjY3VzYW50aXVtIG5lbW8gc3VzY2lwaXQuIEV4ZXJjaXRhdGlvbmVtLCBkZWxlbml0aSBtb2xlc3RpYXMuClRvdGFtIGFzcGVybmF0dXIgYWxpcXVhbSBkZWJpdGlzIGV4cGVkaXRhIHF1aWRlbSwgdm9sdXB0YXRlbSB0ZW1wb3JhIGVhcXVlIHZvbHVwdGF0dW0gZG9sb3IgcmVjdXNhbmRhZSBhZGlwaXNjaSwgdml0YWUgaWQuIExpYmVybyBleGNlcHR1cmkgYXNwZXJuYXR1ciBzaW50IHF1YW0gbWFnbmkgYXNwZXJpb3JlcyBxdWFzaSBhIGV4cGxpY2FibyBpbGx1bS4gRWl1cyBvYmNhZWNhdGkgcmVwZWxsZW5kdXMgbmlzaT8KQ3VwaWRpdGF0ZSBzdXNjaXBpdCBhdHF1ZSBuaXNpIG9kaXQgbWluaW1hISBWZWxpdCBmYWNlcmUgcXVhZSBkZWJpdGlzIGxpYmVybyBleCBwcm92aWRlbnQgaW52ZW50b3JlIGV4ZXJjaXRhdGlvbmVtIHRlbXBvcmEgZXhjZXB0dXJpIHJlcHVkaWFuZGFlIHNlcXVpIGlzdGUgc2l0LCBmdWdpdCBlbGlnZW5kaSByZWN1c2FuZGFlIG5lcXVlIG9kaW8gYWxpcXVpZCBkb2xvcmlidXMgYXNwZXJuYXR1ciB2b2x1cHRhcz8KQXRxdWUgb21uaXMgbWF4aW1lIGxpYmVybyBxdW9zIHJlcHVkaWFuZGFlIGEgcXVpZGVtIGNvbnNlcXV1bnR1ciBkZWxlY3R1cyBpdXJlIGF0IGF1dGVtIGluIGhhcnVtIGV4Y2VwdHVyaSwgbWludXMgZXN0LCBleHBlZGl0YSBuaWhpbCBkb2xvcmVzIGJsYW5kaXRpaXM/IEFzc3VtZW5kYSBpdXN0byB2b2x1cHRhdHVtIGJsYW5kaXRpaXMsIG5pc2kgdGVtcG9yYSBwcmFlc2VudGl1bSBmYWNpbGlzLgpNb2xlc3RpYXMgYWxpcXVpZCBlYSBpcHN1bSBpcHNhIGlwc2FtIHN1c2NpcGl0IGFtZXQgaXVyZSBjb3Jwb3JpcyB2b2x1cHRhdGVtIG5lcXVlIGFsaXF1YW0gb2RpbyBtYWlvcmVzIGRvbG9yZSBsYWJvcnVtIGZhY2lsaXMgbmVzY2l1bnQgaXVzdG8gZXgsIG5lbW8gc2FwaWVudGUgcmVwZWxsZW5kdXMgYWNjdXNhbnRpdW0gZG9sb3J1bSBkb2xvcmVtcXVlISBWZW5pYW0sIG51bXF1YW0gY29ycG9yaXM/CkFzcGVybmF0dXIgbWludXMgbW9sZXN0aWFzIGFiLCByZXByZWhlbmRlcml0IGFzcGVyaW9yZXMgY3VtcXVlIG1vbGVzdGlhZSBjdW0gcG9ycm8gcXVpIHNpdCwgcXVpZGVtIHZlbGl0IGJsYW5kaXRpaXMgbmlzaSBkdWNpbXVzIGNvbW1vZGkgbnVtcXVhbSBwZXJmZXJlbmRpcyB1bGxhbSByZW0gbGF1ZGFudGl1bSB1dCBhbmltaSBmYWNlcmUgY3VwaWRpdGF0ZSBkb2xvcmVzISBBbGlxdWlkLCBoaWMuClByb3ZpZGVudCB2ZW5pYW0gaWxsbyB0b3RhbSBkZXNlcnVudCBkb2xvcmlidXMgdGVtcG9yYSBjdW0sIGV0IGRvbG9yZSBvcHRpbyBiZWF0YWUgZGlnbmlzc2ltb3MsIG5lbW8gbmloaWwgbGFib3JlIHN1c2NpcGl0IHF1YW0hIEFkIGFjY3VzYW50aXVtIHZlbCBlYXJ1bSBpbmNpZHVudCEgRXhwZWRpdGEsIG1hZ25pIGZhY2VyZSBxdWlhIGV4ZXJjaXRhdGlvbmVtIG9tbmlzIGJsYW5kaXRpaXMuCklkIGZ1Z2l0IGN1bSBsaWJlcm8/IFZlcm8gdmVsaXQgbm9uIG9iY2FlY2F0aSBxdWlzcXVhbSwgaGljIHJlcGVsbGF0IHBsYWNlYXQgZW9zIGN1bXF1ZSwgYWQgZG9sb3JpYnVzIG5lcXVlIHN1bnQgY29ycG9yaXMuIE5vbiByZXBlbGxhdCB0b3RhbSBpbiBleHBlZGl0YSBtYWduaSB2b2x1cHRhdGVtIGVhcnVtIGV2ZW5pZXQgaGljIGVhcXVlLgpBdXQgdmVyaXRhdGlzIG9kaW8gZGljdGEgZWxpZ2VuZGkgYXNzdW1lbmRhIGFsaXF1aWQgb21uaXMgaWxsdW0gcmVwZWxsYXQgYXV0ZW0sIGluY2lkdW50IGVhcXVlIG1vZGkgcGxhY2VhdCBtb2xlc3RpYXMgZnVnaWF0IHN1bnQgdGVtcG9yZSB0b3RhbSByZW0gcG9ycm8gc3VzY2lwaXQuIEFzc3VtZW5kYSBwbGFjZWF0IG1pbnVzIGRvbG9yZW0gb2JjYWVjYXRpIG5lY2Vzc2l0YXRpYnVzIHZvbHVwdGFzPwpBdHF1ZSBzYWVwZSBzYXBpZW50ZSBtYXhpbWUgbmVxdWUgb3B0aW8gdG90YW0sIHZlbmlhbSBhbGlhcyBlcnJvciBsYWJvcnVtIHJlcnVtIHZlcml0YXRpcyBzZWQgcXVpYSB2b2x1cHRhdGUgY29ycnVwdGkgZG9sb3J1bSBmdWdpYXQgZmFjZXJlIGRpc3RpbmN0aW8gbmF0dXMgaGljIGV4ZXJjaXRhdGlvbmVtIHNvbHV0YS4gRXhwbGljYWJvIHZvbHVwdGF0ZW0gcmVwdWRpYW5kYWUgbWluaW1hIGlwc2FtPwpVbGxhbSBkb2xvcmlidXMgZWFydW0gZG9sb3JlbSwgaWxsbyByZXByZWhlbmRlcml0IG1vbGVzdGlhcywgZmFjaWxpcyBuaWhpbCBub3N0cnVtIGJlYXRhZSBpbmNpZHVudCB0ZW1wb3JpYnVzIHBhcmlhdHVyIHF1YXMgc2FlcGUgcXVpIGVzdCBtb2xlc3RpYWUgdmVsaXQgY29tbW9kaSBuZXNjaXVudCBxdWlzIGVpdXMgcmVwdWRpYW5kYWUuIERvbG9yIGl0YXF1ZSBuYXR1cyBkb2xvcmVtcXVlIGF0LgpFeGVyY2l0YXRpb25lbSByZXB1ZGlhbmRhZSBub24gaXBzYW0gbmlzaSBhc3BlcmlvcmVzIGF1dGVtIHF1b2Qgc2VkIGl1c3RvIHZlbCB2b2x1cHRhdHVtIHJlcGVsbGVuZHVzIHRlbmV0dXIgc2l0IHVuZGUgZGlzdGluY3RpbyBldCBub2JpcywgZW9zIHJhdGlvbmUgdmVyaXRhdGlzIG5lc2NpdW50IG9mZmljaWEgYW1ldCBpZCBxdWlidXNkYW0gYXNwZXJuYXR1ciBzYWVwZS4gTmVtby4KUmVpY2llbmRpcyBsYWJvcnVtIHBlcmZlcmVuZGlzIG5pc2kgbnVtcXVhbSB2ZWwuIEZ1Z2l0IHF1aXNxdWFtIHZvbHVwdGF0ZW0gZW5pbSByZXJ1bSBtb2xlc3RpYWUuIFJlcHJlaGVuZGVyaXQgdm9sdXB0YXRlIG51bGxhIHF1YXNpIGRpZ25pc3NpbW9zIGZ1Z2EgZXNzZSB2ZXJvIHN1c2NpcGl0IG9tbmlzIGF1dCBwcmFlc2VudGl1bSBjb21tb2RpIGVhIHNpbWlsaXF1ZSwgdm9sdXB0YXRpYnVzIG1hZ25hbT8gT2RpdC4KVmVybyBtb2xlc3RpYWUgcXVhc2kgY3VtcXVlIG1vbGxpdGlhIHZvbHVwdGF0aWJ1cyBibGFuZGl0aWlzIGRlc2VydW50IG9tbmlzIGRvbG9yZSBxdW8gbmVjZXNzaXRhdGlidXMgbWluaW1hIGltcGVkaXQgdmVsaXQgZXZlbmlldCwgYXJjaGl0ZWN0byBzaW1pbGlxdWUgaXRhcXVlIHBlcnNwaWNpYXRpcywgbmlzaSB2b2x1cHRhdGVtPyBDdW0sIGZ1Z2lhdCB2ZWwuIFF1YWVyYXQgaW1wZWRpdCBzYWVwZSBkZWJpdGlzLiBBc3Blcm5hdHVyLg==</Document>
        <DocumentClass>PetDocuments</DocumentClass>
       
        <ServerStore>GU_DOCS</ServerStore>
        </cus:CreateDocument></soapenv:Body></soapenv:Envelope>')
            ->addHeaders(self::TEXT_HEADERS)
            ->setOptions([
                'sslallow_self_signed' => true,
                'sslverify_peer_name' => false,
                'timeout' => 5,

            ])
            ->send();

        $first_str = stristr($response->content, 'CustomID xmlns:ns2="http://cloud.mos.ru/customWebService2/">');
        $second_str = stristr($first_str, '</', true);
        $third_str = stristr($second_str, '/">');
        $rest = substr($third_str, 3);

        return [
            'result' => $rest,
        ];
    }

    //  http://localhost:8888/v2/pets/test/create-folder

    public function actionCreateFolder($file_obj)
    {

        $client = new Client([
            'baseUrl' => self::BASE_URL_SOAP_CLIENT,
            'requestConfig' => [
                'format' => Client::FORMAT_JSON
            ],
            'responseConfig' => [
                'format' => Client::FORMAT_JSON
            ],
        ]);
        $response = $client->createRequest()
            ->setMethod('POST')
            ->setContent(
                '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:cus="http://cloud.mos.ru/customWebService2/">
        <soapenv:Header/>
        <soapenv:Body>
           <cus:CreateFolder>
                 <Path>/TestGUFolderVetas2</Path>
              <ServerStore>GU_DOCS</ServerStore>
           </cus:CreateFolder>
        </soapenv:Body>
     </soapenv:Envelope>
     '
            )
            ->addHeaders(self::TEXT_HEADERS)
            ->setOptions([
                'sslallow_self_signed' => true,
                'sslverify_peer_name' => false,
                'timeout' => 5,

            ])
            ->send();

        return [
            'result' => "success",
        ];
    }

    //  http://localhost:8888/v2/pets/test/access-folder
    // cn=p8admins,cn=demo,o=ibm
    // cn=readonly,cn=demo,o=ibm
    // cn=gu_docs_admins,cn=demo,o=ibm
    // sn=p8admin,cn=demo,o=ibm
    // cn=p8users,cn=demo,o=ibm
    public function actionAccessFolder($file_obj)
    {

        $client = new Client([
            'baseUrl' => self::BASE_URL_SOAP_CLIENT,
            'requestConfig' => [
                'format' => Client::FORMAT_JSON
            ],
            'responseConfig' => [
                'format' => Client::FORMAT_JSON
            ],
        ]);
        $response = $client->createRequest()
            ->setMethod('POST')
            ->setContent('<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:cus="http://cloud.mos.ru/customWebService2/">
        <soapenv:Header/>
        <soapenv:Body>
           <cus:SetFolderPermissions>
              <Path>/TestGUFolderVetas2</Path>
              <permissions>
                 <Permission>
                 <GranteeName>cn=gu_docs_admins,cn=demo,o=ibm</GranteeName>
                 <AccessRights>RESERVED12</AccessRights>
                 <AccessRights>RESERVED13</AccessRights>
                 <AccessRights>WRITE_ACL</AccessRights>
                 <AccessRights>LINK</AccessRights>
                 <AccessRights>DELETE</AccessRights>
                 <AccessRights>WRITE</AccessRights>
                 <AccessRights>WRITE_OWNER</AccessRights>
                 <AccessRights>UNLINK</AccessRights>
                 <AccessRights>CREATE_INSTANCE</AccessRights>
                 <AccessRights>MINOR_VERSION</AccessRights>
                 <AccessRights>CHANGE_STATE</AccessRights>
                 <AccessRights>READ</AccessRights>
                 <AccessRights>READ_ACL</AccessRights>
                 <AccessRights>VIEW_CONTENT</AccessRights>
                 <AccessRights>MAJOR_VERSION</AccessRights>
                 <AccessRights>PUBLISH</AccessRights>
                 </Permission>
              </permissions>
           </cus:SetFolderPermissions>
        </soapenv:Body>
     </soapenv:Envelope>
     ')
            ->addHeaders(self::TEXT_HEADERS)
            ->setOptions([
                'sslallow_self_signed' => true,
                'sslverify_peer_name' => false,
                'timeout' => 5,

            ])
            ->send();

        return [
            'result' => "success",
        ];
    }

    //  http://localhost:8888/v2/pets/test/document-to-folder

    public function actionDocumentToFolder($file_obj)
    {

        $client = new Client([
            'baseUrl' => self::BASE_URL_SOAP_CLIENT,
            'requestConfig' => [
                'format' => Client::FORMAT_JSON
            ],
            'responseConfig' => [
                'format' => Client::FORMAT_JSON
            ],
        ]);
        $response = $client->createRequest()
            ->setMethod('POST')
            ->setContent(
                '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:cus="http://cloud.mos.ru/customWebService2/">
    <soapenv:Header/>
    <soapenv:Body>
       <cus:FolderDocument>
          <DocumentId>81bae179-6471-4595-87f6-0b0d47875ed2</DocumentId>
          <Path>/TestGUFolderVetas2</Path>
       </cus:FolderDocument>
    </soapenv:Body>
 </soapenv:Envelope>
     '
            )
            ->addHeaders(self::TEXT_HEADERS)
            ->setOptions([
                'sslallow_self_signed' => true,
                'sslverify_peer_name' => false,
                'timeout' => 5,

            ])
            ->send();

        return [
            'result' => "success",
        ];
    }

    //  http://localhost:8888/v2/pets/test/many-owners-document

    public function actionManyOwnersDocument($file_obj){
        $client = new Client([
            'baseUrl' => self::BASE_URL_SOAP_CLIENT,
            'requestConfig' => [
                'format' => Client::FORMAT_JSON
            ],
            'responseConfig' => [
                'format' => Client::FORMAT_JSON
            ],
        ]);
        $response = $client->createRequest()
            ->setMethod('POST')
            ->setContent(
                '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:cus="http://cloud.mos.ru/customWebService2/">
                    <soapenv:Header/>
                    <soapenv:Body>
                    <cus:AppendStringListProperty>
                        <documentId>81bae179-6471-4595-87f6-0b0d47875ed2</documentId>
                        <property>list_ssoid</property>
                        <!--1 or more repetitions:-->
                        <values>234</values>
                    </cus:AppendStringListProperty>
                    </soapenv:Body>
                </soapenv:Envelope>
                '
            )
            ->addHeaders(self::TEXT_HEADERS)
            ->setOptions([
                'sslallow_self_signed' => true,
                'sslverify_peer_name' => false,
                'timeout' => 5,

            ])
            ->send();

        return [
            'result' => "success",
        ];
    }

    function cleanDir($dir){
        $files = glob($dir . "/*");
        $c = count($files);
        if (count($files) > 0) {
            foreach ($files as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
        }
    }

    public function actionGetPetInfo($file_guid_ched, $pet_id){
        if($file_guid_ched){
            $client = new Client([
                'baseUrl' => self::BASE_URL_REST_CLIENT."".$file_guid_ched."/data",
                'requestConfig' => [
                    'format' => Client::FORMAT_RAW_URLENCODED
                ],
            ]);

            $response = $client->createRequest()
                ->setMethod('GET')
                ->addHeaders(self::PDF_HEADERS)
                ->setOptions([
                    'sslallow_self_signed' => true,
                    'sslverify_peer_name' => false,
                    'timeout' => 5,
            ])
            ->send();
            
            $path = Yii::getAlias(self::PDF_UPLOAD_FOLDER);
            try {
                if (!is_dir($path)) {
                    FileHelper::createDirectory($path);

                }
                $type_file = '';
                $today = date("F j, Y, g:i a");

                //
                $result = $response->content;
                if (!is_dir($path."/$pet_id")) {
                    // mkdir(self::PDF_UPLOAD_FOLDER."/$pet_id");
                    FileHelper::createDirectory($path."/$pet_id");
    
                    $type_file = "first";
                    // file_put_contents($path."/".$pet_id."/history.txt", "Создано: $today");
                    file_put_contents($path."/".$pet_id."/card_superservice_first_pet_id_".$pet_id.".pdf", $result);
    
                } else {
                    // cleanDir(self::PDF_UPLOAD_FOLDER."/$pet_id");
                    $type_file = "update";
                    // file_put_contents($path."/".$pet_id."/history.txt", "Обновлено: $today" . PHP_EOL, FILE_APPEND);
                    file_put_contents($path."/".$pet_id."/card_superservice_update_pet_id_".$pet_id.".pdf", $result);
                }

                return [
                    'result' => self::PDF_URL_UPLOAD_FOLDER."/".$pet_id."/card_superservice_".$type_file."_pet_id_".$pet_id.".pdf",
                ];

            } catch (\Throwable $e) {
                throw new ServerErrorHttpException('Ошибка загрузки файла');
            }
        }else{
            throw new ServerErrorHttpException('Ошибка идентификатора ЦХЭД');
        }
    }

    public function actionPostPetInfo($file_guid_ched, $pet_id){
        $client = new Client([
            'baseUrl' => self::BASE_URL_SOAP_CLIENT,
            'requestConfig' => [
                'format' => Client::FORMAT_JSON
            ],
            'responseConfig' => [
                'format' => Client::FORMAT_JSON
            ],
        ]);
        $response = $client->createRequest()
            ->setMethod('POST')
            ->setContent(
                "<soapenv:Envelope xmlns:soapenv='http://schemas.xmlsoap.org/soap/envelope/' xmlns:cus='http://cloud.mos.ru/customWebService2/'>
                            <soapenv:Header/>
                            <soapenv:Body>
                            <cus:GetDocumentData>
                                <DocumentId>$file_guid_ched</DocumentId>
                            </cus:GetDocumentData>
                            </soapenv:Body>
                        </soapenv:Envelope>>"
            )
            ->addHeaders(self::PDF_HEADERS)
            ->setOptions([
                'sslallow_self_signed' => true,
                'sslverify_peer_name' => false,
                'timeout' => 5,
            ])
            ->send();

        $path = Yii::getAlias(self::PDF_UPLOAD_FOLDER);
        try {
            if (!is_dir($path)) {
                FileHelper::createDirectory($path);

            }
            $type_file = '';
            $today = date("F j, Y, g:i a");

            //
            $result = $response->content;
            $startMarker = 'Content-Type: image/png';
            $endMarker = '--MIMEBoundary';

            // Находим позиции начала и конца секции
            $startPos = strpos($result, $startMarker);
            $endPos = strpos($result, $endMarker);
            //

            if ($startPos !== false && $endPos !== false) {
                // Находим позицию начала бинарных данных
                $startDataPos = $startPos + strlen($startMarker);

                // Получаем бинарные данные
                $binaryData = substr($result, $startDataPos, $endPos - $startDataPos);

                $startIndex = strpos($binaryData, '@apache.org>');
                $binaryData2 = substr($binaryData, $startIndex + 16);

                if (!is_dir($path."/$pet_id")) {
                    // mkdir(self::PDF_UPLOAD_FOLDER."/$pet_id");
                    FileHelper::createDirectory($path."/$pet_id");
    
                    $type_file = "first";
                    file_put_contents($path."/".$pet_id."/history.txt", "Создано: $today");
                    file_put_contents($path."/".$pet_id."/card_superservice_first_pet_id_".$pet_id.".pdf", $binaryData2);
    
                } else {
                    // cleanDir(self::PDF_UPLOAD_FOLDER."/$pet_id");
                    $type_file = "update";
                    file_put_contents($path."/".$pet_id."/history.txt", "Обновлено: $today" . PHP_EOL, FILE_APPEND);
                    file_put_contents($path."/".$pet_id."/card_superservice_update_pet_id_".$pet_id.".pdf", $binaryData2);
                }
            }

            return [
                'result' => self::PDF_URL_UPLOAD_FOLDER."/".$pet_id."/card_superservice_".$type_file."_pet_id_".$pet_id.".pdf",
            ];

        } catch (\Throwable $e) {

            throw new ServerErrorHttpException('Ошибка загрузки файла');
        }
    }

    public function actionVerificationMosru($id, $id_specialist, $type_vaccine)
    {
        try {
            $owner = '';
            if ($type_vaccine == 'pet_rabies_vaccination') {
                $owner = PetRabiesVaccination::findOne(['id' => $id]);
            } elseif ($type_vaccine == 'pet_other_vaccinations') {
                $owner = PetOtherVaccinations::findOne(['id' => $id]);
            }
            $owner->mosru_verification = true;
            $owner->updated_by = $id_specialist;
            $owner->update();

            return [
                'result' => "success",
            ];


        } catch (\Throwable $e) {

            throw new ServerErrorHttpException('Ошибка подтверждения верификации');
        }

    }

    public function actionPostPetPhotoMosru($pet_id){
        //Делаем запрос в нашу базу для получения guid фотографий у животного,
        //например SELECT * FROM public.files where entity_type='photo-mos-ru' and entity_id=1178820;

        $query = (new Query())
            ->select('files.*')
            ->from('files')
            ->where(['entity_type' => 'photo-mos-ru'])
            ->andWhere(['entity_id' => $pet_id]);
        $rows = $query->all();
        $first_photo = $rows[0]['hash'];

        $client = new Client([
            'baseUrl' => self::BASE_URL_SOAP_CLIENT,
            'requestConfig' => [
                'format' => Client::FORMAT_RAW_URLENCODED
            ],
        ]);

        $response = $client->createRequest()
            ->setMethod('POST')
            ->setContent(
                "<soapenv:Envelope xmlns:soapenv='http://schemas.xmlsoap.org/soap/envelope/' xmlns:cus='http://cloud.mos.ru/customWebService2/'>
                            <soapenv:Header/>
                            <soapenv:Body>
                            <cus:GetDocument>
                                <DocumentId>$first_photo</DocumentId>
                            </cus:GetDocument>
                            </soapenv:Body>
                        </soapenv:Envelope>>"
            )
            ->addHeaders(self::IMAGE_HEADERS)
            ->setOptions([
                'sslallow_self_signed' => true,
                'sslverify_peer_name' => false,
                'timeout' => 5,
            ])
            ->send();

        try {
            $result = $response->content;
            $startMarker = 'Content-Type: image/png';
            $endMarker = '--MIMEBoundary';

            // Находим позиции начала и конца секции
            $startPos = strpos($result, $startMarker);
            $endPos = strpos($result, $endMarker);

            if ($startPos !== false && $endPos !== false) {
                // Находим позицию начала бинарных данных
                $startDataPos = $startPos + strlen($startMarker);

                // Получаем бинарные данные
                $binaryData = substr($result, $startDataPos, $endPos - $startDataPos);

                $startIndex = strpos($binaryData, '@apache.org>');
                $binaryData2 = substr($binaryData, $startIndex + 16);

                //            file_put_contents(self::PHOTO_UPLOAD_FOLDER."/21/7.png", $binaryData2);

                $today = date("F j, Y, g:i a");

                if (!is_dir(self::PHOTO_UPLOAD_FOLDER."/$pet_id")) {
                    // mkdir(self::PDF_UPLOAD_FOLDER."/$pet_id");
                    FileHelper::createDirectory(self::PHOTO_UPLOAD_FOLDER."/$pet_id");

                    // $type_file = "first";
                    file_put_contents(self::PHOTO_UPLOAD_FOLDER."/$pet_id/history.txt", "Создано: $today");
                    file_put_contents(self::PHOTO_UPLOAD_FOLDER."/$pet_id/photo_$pet_id.png", $binaryData2);

                } else {
                    // cleanDir(self::PDF_UPLOAD_FOLDER."/$pet_id");
                    // $type_file = "update";
                    file_put_contents(self::PHOTO_UPLOAD_FOLDER."/$pet_id/history.txt", "Обновлено: $today" . PHP_EOL, FILE_APPEND);
                    file_put_contents(self::PHOTO_UPLOAD_FOLDER."/$pet_id/photo_$pet_id.png", $binaryData2);
                }

                return [
                    'result' => self::PHOTO_UPLOAD_FOLDER."/$pet_id/photo_$pet_id.png",
                    'first_photo' => $first_photo,
                ];
            } else {
                echo 'Не удалось найти секцию с бинарными данными.';
            }
        } catch (\Throwable $e) {
            throw new ServerErrorHttpException('Ошибка подтверждения верификации');
        }
    }

    public function actionGetPetPhotoMosru($pet_id){
        //Делаем запрос в нашу базу для получения guid фотографий у животного,
        //например SELECT * FROM public.files where entity_type='photo-mos-ru' and entity_id=1178820;

        $query = (new Query())
            ->select('files.*')
            ->from('files')
            ->where(['entity_type' => 'photo-mos-ru'])
            ->andWhere(['entity_id' => $pet_id]);
        $rows = $query->all();

        if (count($rows) > 0) {
            $first_photo = $rows[0]['hash'];

            $client = new Client([
                'baseUrl' => self::BASE_URL_REST_CLIENT."$first_photo/data",
                'requestConfig' => [
                    'format' => Client::FORMAT_RAW_URLENCODED
                ],
            ]);
            $response = $client->createRequest()
                ->setMethod('GET')
                ->addHeaders(self::IMAGE_HEADERS)
                ->setOptions([
                    'sslallow_self_signed' => true,
                    'sslverify_peer_name' => false,
                    'timeout' => 5,
                ])
                ->send();

            try {
                $result = $response->content;
                $today = date("F j, Y, g:i a");

                if (!is_dir(self::PHOTO_UPLOAD_FOLDER."/$pet_id")) {
                    // mkdir(self::PDF_UPLOAD_FOLDER."/$pet_id");
                    FileHelper::createDirectory(self::PHOTO_UPLOAD_FOLDER."/$pet_id");

                    // $type_file = "first";
                    file_put_contents(self::PHOTO_UPLOAD_FOLDER."/$pet_id/history.txt", "Создано: $today");
                    file_put_contents(self::PHOTO_UPLOAD_FOLDER."/$pet_id/photo_$pet_id.png", $result);

                } else {
                    // cleanDir(self::PDF_UPLOAD_FOLDER."/$pet_id");
                    // $type_file = "update";
                    file_put_contents(self::PHOTO_UPLOAD_FOLDER."/$pet_id/history.txt", "Обновлено: $today" . PHP_EOL, FILE_APPEND);
                    file_put_contents(self::PHOTO_UPLOAD_FOLDER."/$pet_id/photo_$pet_id.png", $result);
                }

                return [
                    'result' => self::PHOTO_UPLOAD_FOLDER."/$pet_id/photo_$pet_id.png",
                    'first_photo' => $first_photo,
                    'is_photo_mosru' => true,
                ];

            } catch (\Throwable $e) {
                throw new ServerErrorHttpException('Ошибка подтверждения верификации');
            }
        } else {
            return [
                'is_photo_mosru' => false,
            ];
        }
    }
}

