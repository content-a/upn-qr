<?php

namespace UpnQr\Models;

use Webklex\PHPIMAP\Attachment;
use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Exceptions\ConnectionFailedException;
use Webklex\PHPIMAP\Message;

class ReadInboxTransaction {
    private $client;

    // Save all parsed transactions.
    private $bonusTransactions;

    public function __construct($config) {
        $this->client = new ClientManager();
        $this->client = $this->client->make([
            'host'          => $config["host"],
            'port'          => $config["port"],
            'validate_cert' => true,
            'encryption'    => 'ssl',
            'username'      => $config["username"],
            'password'      => $config["password"],
            'protocol'      => 'imap'
        ]);


        $this->bonusTransactions = [];
    }

    /**
     * Check connection.
     *
     * @return bool
     */
    public function checkConnection(){
        try {
            $this->client->connect();
        }
        catch (ConnectionFailedException $e){
            return false;
        }

        return true;
    }

    /**
     * Read transactions from inbox.
     *
     * @return BankTransaction[]
     */
    public function read(){
        $this->client->connect();

        $folders = $this->client->getFolders();

        // Go through each mail folder.
        foreach($folders as $folder){

            // Retrieve unseen messages.
            $messages = $folder->query()->unseen()->leaveUnread()->get();

            foreach($messages as $message){
                $attachments = $message->getAttachments();

                // Go through xml attachments.
                foreach($attachments as $attachment){
                    if (strpos($attachment->getMimeType(), 'xml') == false)
                        continue;

                    $content = utf8_encode($attachment->getContent());

                    // Remove last line from file, which contains parse error.
                    $content = substr($content, 0, strrpos($content, "\n"));

                    // We need this enabled to recognize parse errors.
                    libxml_use_internal_errors(true);
                    $xml = simplexml_load_string($content);

                    // There was parse error.
                    if(!isset($xml->BkToCstmrStmt))
                        throw new \Exception("Napaka pri branju datoteke: {$attachment->getName()} pri mailu: {$message->getSubject()}");

                    // Go through each transaction.
                    foreach ($xml->BkToCstmrStmt->Stmt->Ntry as $row){


                        //  Paid by physical (regular) person.
                        if (!isset($row->NtryDtls->TxDtls->RltdPties->Dbtr))
                            continue;

                        // Reference does not exist
                        if (!isset($row->NtryDtls->TxDtls->RmtInf->Strd->CdtrRefInf->Ref))
                            continue;


                        // Retrieve name.
                        $name = $row->NtryDtls->TxDtls->RltdPties->Dbtr->Nm;

                        // Retrieve reference.
                        $reference = $row->NtryDtls->TxDtls->RmtInf->Strd->CdtrRefInf->Ref;

                        // Retrieve amount.
                        $amount = $row->Amt;

                        // Add transaction data to list.
                        $transaction = new BankTransaction($name, $reference, $amount);

                        // Add purpose if not empty.
                        if(isset($row->NtryDtls->TxDtls->RmtInf->Strd->AddtlRmtInf) && !empty($row->NtryDtls->TxDtls->RmtInf->Strd->AddtlRmtInf))
                            $transaction->setPurpose($row->NtryDtls->TxDtls->RmtInf->Strd->AddtlRmtInf);

                        $this->bonusTransactions[] = $transaction;


                    }
                }

                // After successfull read mark message as seen.
                $message->setFlag(['Seen']);
            }
        }

        return $this->getBonusTransactions();
    }


    /**
     * Get saved transactions.
     *
     * @return BankTransaction[]
     */
    public function getBonusTransactions(){
        return $this->bonusTransactions;
    }
}
