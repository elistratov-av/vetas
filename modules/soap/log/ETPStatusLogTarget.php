<?php

namespace app\modules\soap\log;

use yii\helpers\VarDumper;
use yii\log\DbTarget;
use yii\log\LogRuntimeException;

class ETPStatusLogTarget extends DbTarget
{
    public $logTable = 'etp.status_log';

    /**
     * @throws LogRuntimeException
     * @throws \yii\db\Exception
     */
    public function export()
    {
        if ($this->db->getTransaction()) {
            // create new database connection, if there is an open transaction
            // to ensure insert statement is not affected by a rollback
            $this->db = clone $this->db;
        }

        $tableName = $this->db->quoteTableName($this->logTable);
        $sql = "INSERT INTO $tableName ([[log_time]], [[service_number]], [[visit_id]], [[etp_status]], [[message]])
                VALUES (:log_time, :service_number, :visit_id, :etp_status, :message)";
        $command = $this->db->createCommand($sql);
        foreach ($this->messages as $message) {
            list($text, $level, $category, $timestamp) = $message;
            if (!is_string($text)) {
                // exceptions may not be serializable if in the call stack somewhere is a Closure
                if ($text instanceof \Throwable || $text instanceof \Exception) {
                    $text = (string) $text;
                } else {
                    $text = VarDumper::export($text);
                }
            }
            list($service_number, $visit_id, $etp_status, $message) = explode('|', $text);
            if ($command->bindValues([
                    ':log_time' => date('Y-m-d H:i:s', $timestamp),
                    ':service_number' => $service_number,
                    ':visit_id' => (!empty($visit_id)) ? $visit_id : null,
                    ':etp_status' => $etp_status,
                    ':message' => (!empty($message)) ? $message : null
                ])->execute() > 0) {
                continue;
            }
            throw new LogRuntimeException('Unable to export log through database!');
        }
    }
}
